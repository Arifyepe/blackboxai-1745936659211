</main>
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Sports Store</h3>
                    <p class="text-gray-400">Your one-stop shop for all sports equipment and apparel.</p>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Categories</h4>
                    <ul class="space-y-2">
                        <li><a href="/category/running" class="text-gray-400 hover:text-white">Running</a></li>
                        <li><a href="/category/football" class="text-gray-400 hover:text-white">Football</a></li>
                        <li><a href="/category/futsal" class="text-gray-400 hover:text-white">Futsal</a></li>
                        <li><a href="/category/badminton" class="text-gray-400 hover:text-white">Badminton</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Customer Service</h4>
                    <ul class="space-y-2">
                        <li><a href="/contact" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="/chat" class="text-gray-400 hover:text-white">Live Chat</a></li>
                        <li><a href="/shipping" class="text-gray-400 hover:text-white">Shipping Info</a></li>
                        <li><a href="/returns" class="text-gray-400 hover:text-white">Returns</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Connect With Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Sports Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Chat Widget -->
    <?php if(isset($_SESSION['user_id'])): ?>
    <div id="chat-widget" class="fixed bottom-4 right-4">
        <button onclick="toggleChat()" class="bg-red-600 text-white rounded-full p-4 shadow-lg hover:bg-red-700">
            <i class="fas fa-comments"></i>
        </button>
        <div id="chat-window" class="hidden fixed bottom-20 right-4 w-80 bg-white rounded-lg shadow-xl">
            <div class="p-4 bg-red-600 text-white rounded-t-lg">
                <h3 class="font-bold">Chat with Us</h3>
            </div>
            <div id="chat-messages" class="h-96 overflow-y-auto p-4">
                <!-- Messages will be loaded here -->
            </div>
            <div class="p-4 border-t">
                <form id="chat-form" class="flex">
                    <input type="text" id="message" class="flex-1 border rounded-l px-4 py-2 focus:outline-none focus:border-red-600" placeholder="Type your message...">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function toggleChat() {
        const chatWindow = document.getElementById('chat-window');
        chatWindow.classList.toggle('hidden');
    }

    // Basic chat functionality - to be enhanced with AJAX
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const message = document.getElementById('message').value;
        if (message.trim()) {
            // Send message to server (to be implemented)
            document.getElementById('message').value = '';
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>
