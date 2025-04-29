<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Fetch featured products
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE stock > 0 
    ORDER BY created_at DESC 
    LIMIT 8
");
$stmt->execute();
$featured_products = $stmt->fetchAll();
?>

<!-- Welcome Banner -->
<div class="bg-red-600 text-white rounded-lg shadow-lg mb-8 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="container mx-auto px-6 py-12 relative">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to Sports Store</h1>
        <p class="text-xl mb-8">Your one-stop destination for premium sports equipment and apparel.</p>
        <a href="#categories" class="bg-white text-red-600 px-6 py-3 rounded-lg font-bold hover:bg-red-100 transition duration-300">
            Start Shopping
        </a>
    </div>
</div>

<!-- Categories Section -->
<section id="categories" class="mb-12">
    <h2 class="text-3xl font-bold mb-6">Shop by Category</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Running -->
        <a href="/category/running" class="group">
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                    <div class="p-6 flex items-center justify-center bg-red-600 group-hover:bg-red-700 transition duration-300">
                        <i class="fas fa-running text-4xl text-white"></i>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xl font-bold text-gray-800">Running</h3>
                    <p class="text-gray-600">Shoes, apparel, and accessories</p>
                </div>
            </div>
        </a>

        <!-- Football -->
        <a href="/category/football" class="group">
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                    <div class="p-6 flex items-center justify-center bg-red-600 group-hover:bg-red-700 transition duration-300">
                        <i class="fas fa-futbol text-4xl text-white"></i>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xl font-bold text-gray-800">Football</h3>
                    <p class="text-gray-600">Boots, jerseys, and equipment</p>
                </div>
            </div>
        </a>

        <!-- Futsal -->
        <a href="/category/futsal" class="group">
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                    <div class="p-6 flex items-center justify-center bg-red-600 group-hover:bg-red-700 transition duration-300">
                        <i class="fas fa-futbol text-4xl text-white"></i>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xl font-bold text-gray-800">Futsal</h3>
                    <p class="text-gray-600">Indoor shoes and gear</p>
                </div>
            </div>
        </a>

        <!-- Badminton -->
        <a href="/category/badminton" class="group">
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                    <div class="p-6 flex items-center justify-center bg-red-600 group-hover:bg-red-700 transition duration-300">
                        <i class="fas fa-table-tennis text-4xl text-white"></i>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-xl font-bold text-gray-800">Badminton</h3>
                    <p class="text-gray-600">Rackets, shoes, and accessories</p>
                </div>
            </div>
        </a>
    </div>
</section>

<!-- Featured Products -->
<section class="mb-12">
    <h2 class="text-3xl font-bold mb-6">Featured Products</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($featured_products as $product): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
            <?php if ($product['image']): ?>
            <img src="/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 class="w-full h-48 object-cover">
            <?php else: ?>
            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                <i class="fas fa-image text-gray-400 text-4xl"></i>
            </div>
            <?php endif; ?>
            
            <div class="p-4">
                <h3 class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($product['category']); ?></p>
                <div class="flex justify-between items-center">
                    <span class="text-red-600 font-bold"><?php echo formatPrice($product['price']); ?></span>
                    <a href="/product.php?id=<?php echo $product['id']; ?>" 
                       class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-300">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
