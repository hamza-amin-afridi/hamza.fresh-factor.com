<?php
require_once 'includes/db_connect.php';
ensure_session_started();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid session token. Please try again.";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_image'] = $user['profile_image'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password, or account blocked.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F9FBE7; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-[#1B5E20] mb-2"><?php echo htmlspecialchars(t('sign_in')); ?></h1>
            <p class="text-gray-500 text-sm">Welcome back! Please sign in to your account.</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none transition">
            </div>
            <button type="submit" name="signin" class="w-full py-3 bg-[#1B5E20] text-white font-semibold rounded-xl hover:bg-[#43A047] transition shadow-lg"><?php echo htmlspecialchars(t('sign_in')); ?></button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-600">
            Don't have an account? <a href="signup.php" class="text-[#1B5E20] font-semibold hover:underline"><?php echo htmlspecialchars(t('sign_up')); ?></a>
        </div>
        <div class="mt-4 text-center">
            <a href="index.php" class="text-sm text-gray-400 hover:text-gray-600">Back to Home</a>
        </div>
    </div>
</body>
</html>
