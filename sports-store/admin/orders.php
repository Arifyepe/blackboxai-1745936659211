<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    setFlash("Unauthorized access", "error");
    redirect("/");
}

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        setFlash("Order status updated successfully", "success");
    } catch (PDOException $e) {
        setFlash("Error updating order status", "error");
    }
    redirect("/admin/orders.php");
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = $status_filter ? "WHERE o.status = ?" : "";

// Get total orders count
$count_sql = "SELECT COUNT(*) FROM orders o " . $where_clause;
$count_stmt = $pdo->prepare($count_sql);
if ($status_filter) {
    $count_stmt->execute([$status_filter]);
} else {
    $count_stmt->execute();
}
$total_orders = $count_stmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Get orders with pagination
try {
    $sql = "
        SELECT o.*, u.name as customer_name, u.phone, u.address,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o
        JOIN users u ON o.user_id = u.id
        $where_clause
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    if ($status_filter) {
        $stmt->execute([$status_filter, $limit, $offset]);
    } else {
        $stmt->execute([$limit, $offset]);
    }
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlash("Error fetching orders", "error");
    $orders = [];
}
?>

<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Manage Orders</h1>
        
        <!-- Status Filter -->
        <div class="flex space-x-2">
            <a href="/admin/orders.php" 
               class="px-4 py-2 rounded <?php echo !$status_filter ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                All
            </a>
            <a href="/admin/orders.php?status=pending" 
               class="px-4 py-2 rounded <?php echo $status_filter === 'pending' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                Pending
            </a>
            <a href="/admin/orders.php?status=paid" 
               class="px-4 py-2 rounded <?php echo $status_filter === 'paid' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                Paid
            </a>
            <a href="/admin/orders.php?status=shipped" 
               class="px-4 py-2 rounded <?php echo $status_filter === 'shipped' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                Shipped
            </a>
            <a href="/admin/orders.php?status=completed" 
               class="px-4 py-2 rounded <?php echo $status_filter === 'completed' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                Completed
            </a>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900">Order #<?php echo $order['id']; ?></div>
                                        <div class="text-gray-500">
                                            <?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?>
                                        </div>
                                        <div class="text-gray-500">
                                            <?php echo $order['item_count']; ?> items
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($order['phone']); ?></div>
                                        <div class="text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($order['address']); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo formatPrice($order['total_amount']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo ucfirst($order['payment_method']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-sm rounded-full
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
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <a href="/admin/order-details.php?id=<?php echo $order['id']; ?>"
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <form method="POST" class="inline-block">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" 
                                                    onchange="this.form.submit()"
                                                    class="text-sm border rounded px-2 py-1 focus:outline-none focus:border-red-600">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>

                                        <a href="/invoice/generate.php?id=<?php echo $order['id']; ?>" 
                                           target="_blank"
                                           class="text-gray-600 hover:text-gray-900">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No orders found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <?php 
        $url_pattern = '/admin/orders.php?page=%d';
        if ($status_filter) {
            $url_pattern .= '&status=' . urlencode($status_filter);
        }
        echo generatePagination($page, $total_pages, $url_pattern); 
        ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
