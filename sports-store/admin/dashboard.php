<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    setFlash("Unauthorized access", "error");
    redirect("/");
}

// Get statistics
$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'total_products' => 0,
    'low_stock' => 0
];

try {
    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();

    // Pending orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $stmt->fetchColumn();

    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $stats['total_products'] = $stmt->fetchColumn();

    // Low stock products (less than 5)
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 5");
    $stats['low_stock'] = $stmt->fetchColumn();

    // Recent orders
    $stmt = $pdo->query("
        SELECT o.*, u.name as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll();

    // Low stock products
    $stmt = $pdo->query("
        SELECT * FROM products 
        WHERE stock < 5 
        ORDER BY stock ASC 
        LIMIT 5
    ");
    $low_stock_products = $stmt->fetchAll();

} catch (PDOException $e) {
    setFlash("Error fetching dashboard data", "error");
}
?>

<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-shopping-cart text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Total Orders</h3>
                    <p class="text-2xl font-bold"><?php echo $stats['total_orders']; ?></p>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Pending Orders</h3>
                    <p class="text-2xl font-bold"><?php echo $stats['pending_orders']; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-box text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Total Products</h3>
                    <p class="text-2xl font-bold"><?php echo $stats['total_products']; ?></p>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Low Stock Items</h3>
                    <p class="text-2xl font-bold"><?php echo $stats['low_stock']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">Recent Orders</h2>
            </div>
            <div class="p-6">
                <?php if (!empty($recent_orders)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left">
                                    <th class="pb-4">Order ID</th>
                                    <th class="pb-4">Customer</th>
                                    <th class="pb-4">Amount</th>
                                    <th class="pb-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr class="border-t">
                                    <td class="py-4">#<?php echo $order['id']; ?></td>
                                    <td class="py-4"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td class="py-4"><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td class="py-4">
                                        <span class="px-2 py-1 rounded text-sm 
                                            <?php echo $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                                    'bg-gray-100 text-gray-800'); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <a href="/admin/orders.php" class="text-red-600 hover:text-red-700">View all orders →</a>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No recent orders found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">Low Stock Products</h2>
            </div>
            <div class="p-6">
                <?php if (!empty($low_stock_products)): ?>
                    <div class="space-y-4">
                        <?php foreach ($low_stock_products as $product): ?>
                        <div class="flex items-center justify-between border-b pb-4">
                            <div>
                                <h3 class="font-bold"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-sm text-gray-500">Category: <?php echo ucfirst($product['category']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-red-600">Stock: <?php echo $product['stock']; ?></p>
                                <a href="/admin/edit-product.php?id=<?php echo $product['id']; ?>" 
                                   class="text-sm text-red-600 hover:text-red-700">Update stock →</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4">
                        <a href="/admin/products.php" class="text-red-600 hover:text-red-700">Manage all products →</a>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No low stock products found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 flex flex-wrap gap-4">
        <a href="/admin/add-product.php" 
           class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-300">
            <i class="fas fa-plus mr-2"></i> Add New Product
        </a>
        <a href="/admin/orders.php" 
           class="bg-gray-800 text-white px-6 py-3 rounded-lg hover:bg-gray-900 transition duration-300">
            <i class="fas fa-list mr-2"></i> View All Orders
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
