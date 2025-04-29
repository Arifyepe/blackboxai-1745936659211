<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }

    if (empty($address)) {
        $errors[] = "Address is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already registered";
    }

    // If no errors, create user
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, address)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt->execute([$name, $email, $hashed_password, $phone, $address]);
            
            // Set success message and redirect to login
            setFlash("Registration successful! Please login.", "success");
            redirect("/auth/login.php");
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-red-600 text-white py-4 px-6">
        <h2 class="text-2xl font-bold">Create Account</h2>
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
            <label for="name" class="block text-gray-700 font-bold mb-2">Full Name</label>
            <input type="text" id="name" name="name" 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                   required>
        </div>

        <div>
            <label for="email" class="block text-gray-700 font-bold mb-2">Email Address</label>
            <input type="email" id="email" name="email"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                   required>
        </div>

        <div>
            <label for="phone" class="block text-gray-700 font-bold mb-2">Phone Number</label>
            <input type="tel" id="phone" name="phone"
                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                   required>
        </div>

        <div>
            <label for="address" class="block text-gray-700 font-bold mb-2">Address</label>
            <textarea id="address" name="address" rows="3"
                      class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                      required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
        </div>

        <div>
            <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
            <input type="password" id="password" name="password"
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                   required>
        </div>

        <div>
            <label for="confirm_password" class="block text-gray-700 font-bold mb-2">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:border-red-600"
                   required>
        </div>

        <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition duration-300">
            Create Account
        </button>
    </form>

    <div class="px-6 pb-6 text-center">
        <p class="text-gray-600">
            Already have an account? 
            <a href="/auth/login.php" class="text-red-600 hover:text-red-700">Login here</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
