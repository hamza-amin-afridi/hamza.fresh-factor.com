<?php
require_once 'includes/db_connect.php';
ensure_session_started();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Handle Profile Image Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile_image'])) {
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "assets/users/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $file_name = "user_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                if ($stmt->execute([$target_file, $user_id])) {
                    $_SESSION['user_image'] = $target_file;
                    $success = "Profile picture updated!";
                } else {
                    $error = "Database update failed.";
                }
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG & WEBP files are allowed.";
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            $success = "Password updated successfully!";
        } else {
            $error = "New passwords do not match!";
        }
    } else {
        $error = "Current password is incorrect!";
    }
}

// Fetch user orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; background-color: #F9FBE7; }</style>
</head>
<body class="bg-[#F9FBE7]">

    <!-- Header -->
    <nav class="sticky top-0 bg-white shadow-md z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-[#1B5E20]">Fresh-Factor</a>
            <div class="hidden md:flex items-center space-x-8 text-gray-700 font-medium">
                <a href="index.php" class="hover:text-[#43A047] transition"><?php echo htmlspecialchars(t('home')); ?></a>
                <a href="about.php" class="hover:text-[#43A047] transition"><?php echo htmlspecialchars(t('about_us')); ?></a>
                <a href="products.php" class="hover:text-[#43A047] transition"><?php echo htmlspecialchars(t('products')); ?></a>
                <a href="categories.php" class="hover:text-[#43A047] transition"><?php echo htmlspecialchars(t('categories')); ?></a>
                <a href="cart.php" class="hover:text-[#43A047] transition flex items-center">
                    <?php echo htmlspecialchars(t('cart')); ?> <span class="ml-2 bg-[#43A047] text-white text-xs px-2 py-0.5 rounded-full"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span>
                </a>
                <a href="help.php" class="hover:text-[#43A047] transition"><?php echo htmlspecialchars(t('help')); ?></a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center space-x-4 ml-4 pl-4 border-l border-gray-200">
                        <a href="profile.php" class="flex items-center space-x-2 text-gray-700 hover:text-[#1B5E20]">
                            <img src="<?php echo !empty($_SESSION['user_image']) ? $_SESSION['user_image'] : 'assets/default_user.png'; ?>" class="w-8 h-8 rounded-full border border-green-500 object-cover" alt="User">
                            <span class="hidden lg:inline font-medium"><?php echo explode(' ', $_SESSION['user_name'])[0]; ?></span>
                        </a>
                        <a href="logout.php" class="text-gray-500 hover:text-red-600 transition"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-4 ml-4">
                        <a href="login.php" class="px-5 py-2 text-[#1B5E20] font-semibold hover:bg-green-50 rounded-xl transition"><?php echo htmlspecialchars(t('sign_in')); ?></a>
                        <a href="signup.php" class="px-5 py-2 bg-[#1B5E20] text-white font-semibold rounded-xl hover:bg-[#43A047] transition shadow-md"><?php echo htmlspecialchars(t('sign_up')); ?></a>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Mobile menu button -->
            <button type="button" class="md:hidden text-gray-600 hover:text-[#1B5E20]" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" aria-label="Menu">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <div class="container mx-auto px-6 py-4 space-y-3">
                <a href="index.php" class="block text-gray-700 hover:text-[#43A047]"><?php echo htmlspecialchars(t('home')); ?></a>
                <a href="about.php" class="block text-gray-700 hover:text-[#43A047]"><?php echo htmlspecialchars(t('about_us')); ?></a>
                <a href="products.php" class="block text-gray-700 hover:text-[#43A047]"><?php echo htmlspecialchars(t('products')); ?></a>
                <a href="categories.php" class="block text-gray-700 hover:text-[#43A047]"><?php echo htmlspecialchars(t('categories')); ?></a>
                <a href="cart.php" class="block text-gray-700 hover:text-[#43A047]"><?php echo htmlspecialchars(t('cart')); ?></a>
                <a href="help.php" class="block text-gray-700 hover:text-[#43A047]"><?php echo htmlspecialchars(t('help')); ?></a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl p-8 shadow-lg text-center border border-green-50">
                    <div class="relative inline-block mb-4 group">
                        <img src="<?php echo $user_info['profile_image'] ?: 'assets/default_user.png'; ?>" class="w-32 h-32 rounded-full border-4 border-[#43A047] object-cover" alt="Profile">
                        <form method="POST" enctype="multipart/form-data" id="profileImageForm" class="absolute bottom-0 right-0">
                            <label for="profile_image_input" class="bg-[#1B5E20] text-white p-2 rounded-full shadow-lg hover:bg-[#43A047] transition cursor-pointer flex items-center justify-center w-10 h-10">
                                <i class="fas fa-camera text-xs"></i>
                            </label>
                            <input type="file" name="profile_image" id="profile_image_input" class="hidden" onchange="document.getElementById('profileImageForm').submit()">
                            <input type="hidden" name="update_profile_image" value="1">
                        </form>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($user_info['full_name']); ?></h2>
                    <p class="text-sm text-gray-500 mb-6"><?php echo htmlspecialchars($user_info['email']); ?></p>
                    <div class="border-t pt-6 space-y-2">
                        <a href="#orders" class="block py-2 text-[#1B5E20] font-semibold hover:bg-green-50 rounded-xl transition">My Orders</a>
                        <a href="#settings" class="block py-2 text-gray-600 hover:bg-gray-50 rounded-xl transition">Security Settings</a>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="lg:col-span-3 space-y-12">
                <!-- Orders Section -->
                <section id="orders" class="bg-white rounded-3xl p-10 shadow-lg border border-green-50">
                    <h3 class="text-2xl font-bold text-[#1B5E20] mb-8 flex items-center">
                        <i class="fas fa-shopping-bag mr-3 text-[#43A047]"></i> My Orders
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-600">Order ID</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-600">Date</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-600">Total</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-600">Status</th>
                                    <th class="py-4 px-6 text-sm font-semibold text-gray-600">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-4 px-6 font-medium text-gray-700">#ORD-<?php echo $order['id']; ?></td>
                                    <td class="py-4 px-6 text-sm text-gray-500"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td class="py-4 px-6 font-bold text-[#1B5E20]"><?php echo $order['total_amount']; ?> SAR</td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase
                                            <?php 
                                                echo match($order['status']) {
                                                    'delivered' => 'bg-green-100 text-green-700',
                                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                                    'cancelled' => 'bg-red-100 text-red-700',
                                                    default => 'bg-blue-100 text-blue-700'
                                                };
                                            ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <a href="order_receipt.php?id=<?php echo $order['id']; ?>" class="text-[#43A047] hover:underline text-sm font-semibold">View Receipt</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="5" class="py-12 text-center text-gray-400 italic">No orders yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Settings Section -->
                <section id="settings" class="bg-white rounded-3xl p-10 shadow-lg border border-green-50">
                    <h3 class="text-2xl font-bold text-[#1B5E20] mb-8 flex items-center">
                        <i class="fas fa-shield-alt mr-3 text-[#43A047]"></i> Security Settings
                    </h3>
                    
                    <?php if ($success): ?>
                        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-xl flex items-center shadow-sm">
                            <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-xl flex items-center shadow-sm">
                            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="max-w-md space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#43A047] outline-none">
                        </div>
                        <button type="submit" name="change_password" class="w-full py-4 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition shadow-lg">Update Password</button>
                    </form>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
