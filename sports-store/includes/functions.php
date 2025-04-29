<?php

// Function to sanitize user input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Function to generate random string for invoice numbers
function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

// Function to set flash message
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to redirect user
function redirect($path) {
    header("Location: $path");
    exit();
}

// Function to format price
function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Function to update cart count
function updateCartCount() {
    if (isset($_SESSION['cart'])) {
        $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
    } else {
        $_SESSION['cart_count'] = 0;
    }
}

// Function to check stock availability
function checkStock($product_id, $quantity) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    return $product && $product['stock'] >= $quantity;
}

// Function to update product stock
function updateStock($product_id, $quantity, $operation = 'subtract') {
    global $pdo;
    $sql = "UPDATE products SET stock = stock " . 
           ($operation === 'subtract' ? '-' : '+') . 
           " ? WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$quantity, $product_id]);
}

// Function to get user's unread message count
function getUnreadMessageCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// Function to validate image upload
function validateImage($file) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($filetype, $allowed)) {
        return 'Invalid file type. Allowed types: ' . implode(', ', $allowed);
    }
    
    if ($file['size'] > 5000000) { // 5MB limit
        return 'File is too large. Maximum size is 5MB.';
    }
    
    return true;
}

// Function to upload image
function uploadImage($file, $destination) {
    $validation = validateImage($file);
    if ($validation !== true) {
        return $validation;
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $destination . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return 'Failed to upload image.';
}

// Function to generate pagination links
function generatePagination($current_page, $total_pages, $url_pattern) {
    $links = [];
    
    if ($total_pages <= 1) return '';
    
    $links[] = $current_page > 1 
        ? "<a href='" . sprintf($url_pattern, $current_page - 1) . "' class='px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700'>&laquo; Previous</a>"
        : "<span class='px-3 py-2 bg-gray-300 text-gray-700 rounded'>&laquo; Previous</span>";
    
    for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
        if ($i == $current_page) {
            $links[] = "<span class='px-3 py-2 bg-red-600 text-white rounded'>$i</span>";
        } else {
            $links[] = "<a href='" . sprintf($url_pattern, $i) . "' class='px-3 py-2 bg-white text-red-600 rounded hover:bg-red-100'>$i</a>";
        }
    }
    
    $links[] = $current_page < $total_pages 
        ? "<a href='" . sprintf($url_pattern, $current_page + 1) . "' class='px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700'>Next &raquo;</a>"
        : "<span class='px-3 py-2 bg-gray-300 text-gray-700 rounded'>Next &raquo;</span>";
    
    return '<div class="flex gap-2 justify-center mt-4">' . implode('', $links) . '</div>';
}
