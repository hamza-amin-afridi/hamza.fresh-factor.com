<?php
require_once 'includes/db_connect.php';
ensure_session_started();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone_number, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$full_name, $email, $phone, $hashed_password])) {
                $success = "Registration successful! Please sign in.";
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F9FBE7; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen py-12">
    <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-[#1B5E20] mb-2"><?php echo htmlspecialchars(t('sign_up')); ?></h1>
            <p class="text-gray-500 text-sm">Create a new account to get started.</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm text-center"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="full_name" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="text" name="phone" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none">
            </div>
            <button type="submit" name="signup" class="w-full py-3 bg-[#1B5E20] text-white font-semibold rounded-xl hover:bg-[#43A047] transition shadow-lg"><?php echo htmlspecialchars(t('sign_up')); ?></button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-600">
            Already have an account? <a href="login.php" class="text-[#1B5E20] font-semibold hover:underline"><?php echo htmlspecialchars(t('sign_in')); ?></a>
        </div>
    </div>
</body>
</html>
