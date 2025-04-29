<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $errors[] = "Both email and password are required";
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Initialize empty cart
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
                $_SESSION['cart_count'] = 0;
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect("/admin/dashboard.php");
            } else {
                redirect("/");
            }
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-red-600 text-white py-4 px-6">
        <h2 class="text-2xl font-bold">Login</h2>
    </div>

    <form method="POST" class="p-6 space-y-4">
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
            <label for="email" class="block text-gray-700 font-bold mb-2">Email Address</label>
            <input type="email" id="email" name="email"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                   required>
        </div>

        <div>
            <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
            <input type="password" id="password" name="password"
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                   required>
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="form-checkbox text-red-600">
                <span class="ml-2 text-gray-700">Remember me</span>
            </label>
            <a href="/auth/forgot-password.php" class="text-red-600 hover:text-red-700">
                Forgot Password?
            </a>
        </div>

        <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition duration-300">
            Login
        </button>
    </form>

    <div class="px-6 pb-6 text-center">
        <p class="text-gray-600">
            Don't have an account? 
            <a href="/auth/register.php" class="text-red-600 hover:text-red-700">Register here</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
