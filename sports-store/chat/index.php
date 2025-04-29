<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlash("Please login to access chat", "error");
    redirect("/auth/login.php");
}

// Get chat history
try {
    if (isAdmin()) {
        // For admin, get all conversations grouped by user
        $stmt = $pdo->prepare("
            SELECT 
                u.id as user_id,
                u.name as user_name,
                m.message,
                m.created_at,
                m.is_read,
                (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM users u
            LEFT JOIN messages m ON (
                m.id = (
                    SELECT id FROM messages 
                    WHERE (sender_id = u.id AND receiver_id = ?) 
                    OR (sender_id = ? AND receiver_id = u.id)
                    ORDER BY created_at DESC 
                    LIMIT 1
                )
            )
            WHERE u.role = 'user'
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
        $conversations = $stmt->fetchAll();
    } else {
        // For regular users, get conversation with admin
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   CASE 
                       WHEN m.sender_id = ? THEN 'sent'
                       ELSE 'received'
                   END as type
            FROM messages m
            WHERE (sender_id = ? AND receiver_id = (SELECT id FROM users WHERE role = 'admin' LIMIT 1))
            OR (sender_id = (SELECT id FROM users WHERE role = 'admin' LIMIT 1) AND receiver_id = ?)
            ORDER BY m.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
        $messages = array_reverse($stmt->fetchAll());

        // Mark messages as read
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE receiver_id = ? AND is_read = 0
        ");
        $stmt->execute([$_SESSION['user_id']]);
    }
} catch (PDOException $e) {
    setFlash("Error loading chat history", "error");
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8"><?php echo isAdmin() ? 'Customer Messages' : 'Chat with Support'; ?></h1>

        <?php if (isAdmin()): ?>
        <!-- Admin View: List of Conversations -->
        <div class="bg-white rounded-lg shadow-md">
            <?php if (!empty($conversations)): ?>
                <div class="divide-y">
                    <?php foreach ($conversations as $conv): ?>
                        <a href="/chat/conversation.php?user_id=<?php echo $conv['user_id']; ?>" 
                           class="block p-4 hover:bg-gray-50 transition duration-150">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($conv['user_name']); ?></h3>
                                    <?php if ($conv['message']): ?>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?php echo htmlspecialchars(substr($conv['message'], 0, 100)); ?>...
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <?php echo date('M j, Y H:i', strtotime($conv['created_at'])); ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500 mt-1">No messages yet</p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="bg-red-600 text-white text-xs px-2 py-1 rounded-full">
                                        <?php echo $conv['unread_count']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="p-4 text-gray-500">No conversations yet</p>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- User View: Chat Interface -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Chat Messages -->
            <div id="chat-messages" class="h-96 overflow-y-auto p-4 space-y-4">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="flex <?php echo $message['type'] === 'sent' ? 'justify-end' : 'justify-start'; ?>">
                            <div class="max-w-xs lg:max-w-md <?php echo $message['type'] === 'sent' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-800'; ?> rounded-lg px-4 py-2">
                                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                <p class="text-xs <?php echo $message['type'] === 'sent' ? 'text-red-100' : 'text-gray-500'; ?> mt-1">
                                    <?php echo date('M j, Y H:i', strtotime($message['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500">No messages yet. Start a conversation!</p>
                <?php endif; ?>
            </div>

            <!-- Message Input -->
            <div class="border-t p-4">
                <form id="chat-form" class="flex space-x-4">
                    <input type="hidden" id="receiver_id" value="<?php echo isAdmin() ? $user_id : $admin_id; ?>">
                    <textarea id="message" 
                            class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:border-red-600"
                            placeholder="Type your message..."
                            rows="2"></textarea>
                    <button type="submit" 
                            class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                        Send
                    </button>
                </form>
            </div>
        </div>

        <script>
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message');
        const receiverId = document.getElementById('receiver_id').value;

        // Scroll to bottom of chat
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Handle message submission
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            
            if (!message) return;

            try {
                const response = await fetch('/chat/send-message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        receiver_id: receiverId,
                        message: message
                    }),
                });

                if (response.ok) {
                    messageInput.value = '';
                    // Reload page to show new message
                    window.location.reload();
                } else {
                    alert('Error sending message. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error sending message. Please try again.');
            }
        });

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Check for new messages every 10 seconds
        setInterval(async () => {
            try {
                const response = await fetch('/chat/check-messages.php');
                const data = await response.json();
                
                if (data.hasNewMessages) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error checking messages:', error);
            }
        }, 10000);
        </script>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
