<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    setFlash("Unauthorized access", "error");
    redirect("/");
}

// Handle delete product
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        setFlash("Product deleted successfully", "success");
    } catch (PDOException $e) {
        setFlash("Error deleting product", "error");
    }
    redirect("/admin/products.php");
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total products count
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$total_products = $stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Get products with pagination
try {
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlash("Error fetching products", "error");
    $products = [];
}
?>

<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Manage Products</h1>
        <a href="/admin/add-product.php" 
           class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
            <i class="fas fa-plus mr-2"></i> Add New Product
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if ($product['image']): ?>
                                            <img src="/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 class="h-10 w-10 object-cover rounded">
                                        <?php else: ?>
                                            <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: #<?php echo $product['id']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-sm rounded-full
                                        <?php
                                        switch($product['category']) {
                                            case 'running':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'football':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'futsal':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'badminton':
                                                echo 'bg-purple-100 text-purple-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($product['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php echo formatPrice($product['price']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm <?php echo $product['stock'] < 5 ? 'text-red-600 font-bold' : 'text-gray-900'; ?>">
                                        <?php echo $product['stock']; ?> units
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-3">
                                        <a href="/admin/edit-product.php?id=<?php echo $product['id']; ?>"
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="inline-block" 
                                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="delete_product" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No products found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <?php echo generatePagination($page, $total_pages, '/admin/products.php?page=%d'); ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
