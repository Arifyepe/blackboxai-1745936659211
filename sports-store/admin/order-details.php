<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    setFlash("Unauthorized access", "error");
    redirect("/");
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    setFlash("Order ID is required", "error");
    redirect("/admin/orders.php");
}

$order_id = (int)$_GET['id'];

try {
    // Get order details with customer information
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as customer_name, u.email, u.phone, u.address
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        setFlash("Order not found", "error");
        redirect("/admin/orders.php");
    }

    // Get order items with product details
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.category, p.subcategory, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();

    // Get invoice if exists
    $stmt = $pdo->prepare("
        SELECT * FROM invoices 
        WHERE order_id = ?
    ");
    $stmt->execute([$order_id]);
    $invoice = $stmt->fetch();

} catch (PDOException $e) {
    setFlash("Error fetching order details", "error");
    redirect("/admin/orders.php");
}
?>

<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Order Details #<?php echo $order_id; ?></h1>
        <div class="flex space-x-4">
            <a href="/admin/orders.php" class="text-red-600 hover:text-red-700">
                <i class="fas fa-arrow-left mr-2"></i> Back to Orders
            </a>
            <?php if ($invoice): ?>
            <a href="/invoice/generate.php?id=<?php echo $order_id; ?>" 
               target="_blank"
               class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">
                <i class="fas fa-file-invoice mr-2"></i> View Invoice
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Summary -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600">Order Date</p>
                            <p class="font-medium"><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Payment Method</p>
                            <p class="font-medium"><?php echo ucfirst($order['payment_method']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Status</p>
                            <span class="px-2 py-1 text-sm rounded-full inline-block
                                <?php
                                switch($order['status']) {
                                    case 'pending':
                                        echo 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'paid':
                                        echo 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'shipped':
                                        echo 'bg-purple-100 text-purple-800';
                                        break;
                                    case 'completed':
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                    case 'cancelled':
                                        echo 'bg-red-100 text-red-800';
                                        break;
                                    default:
                                        echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-600">Total Amount</p>
                            <p class="font-medium"><?php echo formatPrice($order['total_amount']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="font-bold mb-4">Order Items</h3>
                    <div class="space-y-4">
                        <?php foreach ($order_items as $item): ?>
                        <div class="flex items-center border-b pb-4">
                            <div class="h-20 w-20 flex-shrink-0">
                                <?php if ($item['image']): ?>
                                <img src="/assets/images/products/<?php echo htmlspecialchars($item['image']); ?>"
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                     class="h-full w-full object-cover rounded">
                                <?php else: ?>
                                <div class="h-full w-full bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-2xl"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-medium"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <p class="text-sm text-gray-500">
                                    <?php echo ucfirst($item['category']); ?> / <?php echo ucfirst($item['subcategory']); ?>
                                </p>
                                <div class="flex justify-between mt-2">
                                    <p class="text-gray-600">
                                        <?php echo formatPrice($item['price']); ?> × <?php echo $item['quantity']; ?>
                                    </p>
                                    <p class="font-medium">
                                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold mb-4">Customer Information</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-600">Name</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Email</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order['email']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Phone</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Shipping Address</p>
                            <p class="font-medium"><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="p-6">
                    <h3 class="font-bold mb-4">Update Order Status</h3>
                    <form method="POST" action="/admin/orders.php">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <select name="status" 
                                class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600 mb-4">
                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" 
                                name="update_status"
                                class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
