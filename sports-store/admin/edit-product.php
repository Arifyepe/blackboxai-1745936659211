<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    setFlash("Unauthorized access", "error");
    redirect("/");
}

// Check if product ID is provided
if (!isset($_GET['id'])) {
    setFlash("Product ID is required", "error");
    redirect("/admin/products.php");
}

$product_id = (int)$_GET['id'];
$errors = [];

// Fetch product details
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlash("Product not found", "error");
        redirect("/admin/products.php");
    }
} catch (PDOException $e) {
    setFlash("Error fetching product details", "error");
    redirect("/admin/products.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = sanitize($_POST['name']);
    $category = sanitize($_POST['category']);
    $subcategory = sanitize($_POST['subcategory']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $description = sanitize($_POST['description']);

    // Validate input
    if (empty($name)) {
        $errors[] = "Product name is required";
    }

    if (empty($category)) {
        $errors[] = "Category is required";
    }

    if (empty($subcategory)) {
        $errors[] = "Subcategory is required";
    }

    if ($price === false || $price <= 0) {
        $errors[] = "Valid price is required";
    }

    if ($stock === false || $stock < 0) {
        $errors[] = "Valid stock quantity is required";
    }

    // Handle image upload
    $image_name = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['image'], '../assets/images/products');
        if (is_string($upload_result) && strpos($upload_result, 'Error') === false) {
            // Delete old image if exists
            if ($product['image'] && file_exists('../assets/images/products/' . $product['image'])) {
                unlink('../assets/images/products/' . $product['image']);
            }
            $image_name = $upload_result;
        } else {
            $errors[] = $upload_result;
        }
    }

    // If no errors, update product
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, category = ?, subcategory = ?, price = ?, 
                    stock = ?, description = ?, image = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $name, $category, $subcategory, $price, 
                $stock, $description, $image_name, $product_id
            ]);
            
            setFlash("Product updated successfully", "success");
            redirect("/admin/products.php");
        } catch (PDOException $e) {
            $errors[] = "Error updating product. Please try again.";
        }
    }
}
?>

<div class="container mx-auto px-4">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Edit Product</h1>
            <a href="/admin/products.php" class="text-red-600 hover:text-red-700">
                <i class="fas fa-arrow-left mr-2"></i> Back to Products
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="name" class="block text-gray-700 font-bold mb-2">Product Name</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($product['name']); ?>"
                           class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                           required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="category" class="block text-gray-700 font-bold mb-2">Category</label>
                        <select id="category" name="category" 
                                class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                                required>
                            <option value="">Select Category</option>
                            <option value="running" <?php echo $product['category'] === 'running' ? 'selected' : ''; ?>>Running</option>
                            <option value="football" <?php echo $product['category'] === 'football' ? 'selected' : ''; ?>>Football</option>
                            <option value="futsal" <?php echo $product['category'] === 'futsal' ? 'selected' : ''; ?>>Futsal</option>
                            <option value="badminton" <?php echo $product['category'] === 'badminton' ? 'selected' : ''; ?>>Badminton</option>
                        </select>
                    </div>

                    <div>
                        <label for="subcategory" class="block text-gray-700 font-bold mb-2">Subcategory</label>
                        <select id="subcategory" name="subcategory" 
                                class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                                required>
                            <option value="">Select Subcategory</option>
                            <option value="shoes" <?php echo $product['subcategory'] === 'shoes' ? 'selected' : ''; ?>>Shoes</option>
                            <option value="jersey" <?php echo $product['subcategory'] === 'jersey' ? 'selected' : ''; ?>>Jersey</option>
                            <option value="socks" <?php echo $product['subcategory'] === 'socks' ? 'selected' : ''; ?>>Socks</option>
                            <option value="shorts" <?php echo $product['subcategory'] === 'shorts' ? 'selected' : ''; ?>>Shorts</option>
                            <option value="accessories" <?php echo $product['subcategory'] === 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="block text-gray-700 font-bold mb-2">Price (Rp)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($product['price']); ?>"
                               class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                               required>
                    </div>

                    <div>
                        <label for="stock" class="block text-gray-700 font-bold mb-2">Stock</label>
                        <input type="number" id="stock" name="stock" min="0"
                               value="<?php echo htmlspecialchars($product['stock']); ?>"
                               class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                               required>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div>
                    <label for="image" class="block text-gray-700 font-bold mb-2">Product Image</label>
                    <?php if ($product['image']): ?>
                        <div class="mb-2">
                            <img src="/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                 alt="Current product image"
                                 class="h-32 w-32 object-cover rounded">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600">
                    <p class="text-sm text-gray-500 mt-1">Maximum file size: 5MB. Supported formats: JPG, JPEG, PNG, GIF</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
