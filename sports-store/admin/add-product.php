<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    setFlash("Unauthorized access", "error");
    redirect("/");
}

$errors = [];

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
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['image'], '../assets/images/products');
        if (is_string($upload_result) && strpos($upload_result, 'Error') === false) {
            $image_name = $upload_result;
        } else {
            $errors[] = $upload_result;
        }
    }

    // If no errors, add product
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (name, category, subcategory, price, stock, description, image)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$name, $category, $subcategory, $price, $stock, $description, $image_name]);
            
            setFlash("Product added successfully", "success");
            redirect("/admin/products.php");
        } catch (PDOException $e) {
            $errors[] = "Error adding product. Please try again.";
        }
    }
}
?>

<div class="container mx-auto px-4">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Add New Product</h1>
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
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
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
                            <option value="running" <?php echo isset($_POST['category']) && $_POST['category'] === 'running' ? 'selected' : ''; ?>>Running</option>
                            <option value="football" <?php echo isset($_POST['category']) && $_POST['category'] === 'football' ? 'selected' : ''; ?>>Football</option>
                            <option value="futsal" <?php echo isset($_POST['category']) && $_POST['category'] === 'futsal' ? 'selected' : ''; ?>>Futsal</option>
                            <option value="badminton" <?php echo isset($_POST['category']) && $_POST['category'] === 'badminton' ? 'selected' : ''; ?>>Badminton</option>
                        </select>
                    </div>

                    <div>
                        <label for="subcategory" class="block text-gray-700 font-bold mb-2">Subcategory</label>
                        <select id="subcategory" name="subcategory" 
                                class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                                required>
                            <option value="">Select Subcategory</option>
                            <option value="shoes" <?php echo isset($_POST['subcategory']) && $_POST['subcategory'] === 'shoes' ? 'selected' : ''; ?>>Shoes</option>
                            <option value="jersey" <?php echo isset($_POST['subcategory']) && $_POST['subcategory'] === 'jersey' ? 'selected' : ''; ?>>Jersey</option>
                            <option value="socks" <?php echo isset($_POST['subcategory']) && $_POST['subcategory'] === 'socks' ? 'selected' : ''; ?>>Socks</option>
                            <option value="shorts" <?php echo isset($_POST['subcategory']) && $_POST['subcategory'] === 'shorts' ? 'selected' : ''; ?>>Shorts</option>
                            <option value="accessories" <?php echo isset($_POST['subcategory']) && $_POST['subcategory'] === 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="block text-gray-700 font-bold mb-2">Price (Rp)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0"
                               value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                               class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                               required>
                    </div>

                    <div>
                        <label for="stock" class="block text-gray-700 font-bold mb-2">Stock</label>
                        <input type="number" id="stock" name="stock" min="0"
                               value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>"
                               class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                               required>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div>
                    <label for="image" class="block text-gray-700 font-bold mb-2">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600">
                    <p class="text-sm text-gray-500 mt-1">Maximum file size: 5MB. Supported formats: JPG, JPEG, PNG, GIF</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
