<?php
session_start();
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Store</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-red: #FF0000;
            --dark-red: #CC0000;
            --light-red: #FF4D4D;
        }
        body {
            font-family: 'Roboto', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Russo One', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-red-600 text-white">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <a href="/" class="text-2xl font-bold">Sports Store</a>
                <div class="hidden md:flex space-x-6">
                    <a href="/" class="hover:text-red-200">Home</a>
                    <div class="relative group">
                        <button class="hover:text-red-200">Categories</button>
                        <div class="absolute hidden group-hover:block w-48 bg-white text-gray-800 shadow-lg py-2 mt-2">
                            <a href="/category/running" class="block px-4 py-2 hover:bg-red-50">Running</a>
                            <a href="/category/football" class="block px-4 py-2 hover:bg-red-50">Football</a>
                            <a href="/category/futsal" class="block px-4 py-2 hover:bg-red-50">Futsal</a>
                            <a href="/category/badminton" class="block px-4 py-2 hover:bg-red-50">Badminton</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/cart" class="hover:text-red-200">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                            <span class="bg-white text-red-600 rounded-full px-2 py-1 text-xs"><?php echo $_SESSION['cart_count']; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="relative group">
                            <button class="hover:text-red-200">
                                <i class="fas fa-user"></i>
                            </button>
                            <div class="absolute hidden group-hover:block w-48 bg-white text-gray-800 shadow-lg py-2 mt-2 right-0">
                                <a href="/profile" class="block px-4 py-2 hover:bg-red-50">Profile</a>
                                <a href="/orders" class="block px-4 py-2 hover:bg-red-50">My Orders</a>
                                <a href="/chat" class="block px-4 py-2 hover:bg-red-50">Messages</a>
                                <a href="/auth/logout.php" class="block px-4 py-2 hover:bg-red-50">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/auth/login.php" class="hover:text-red-200">Login</a>
                        <a href="/auth/register.php" class="bg-white text-red-600 px-4 py-2 rounded hover:bg-red-100">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-8"><?php if(isset($_SESSION['flash'])): ?>
        <div class="mb-4 p-4 rounded <?php echo $_SESSION['flash']['type'] == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php 
            echo $_SESSION['flash']['message'];
            unset($_SESSION['flash']);
            ?>
        </div>
    <?php endif; ?>
