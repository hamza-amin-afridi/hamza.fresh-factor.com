<?php
require_once 'includes/db_connect.php';
ensure_session_started();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$total_amount = 0;
$cart_items = [];
$ids = array_keys($_SESSION['cart']);
if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $total_amount += $p['price'] * $qty;
        $cart_items[] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => $p['price'],
            'qty' => $qty
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid session token. Please refresh the page and try again.";
    } else {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'];
        $message = $_POST['message'] ?? '';
    
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status, customer_name, customer_phone, customer_email, customer_message) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?)");
            $stmt->execute([$user_id, $total_amount, $address, $name, $phone, $email, $message]);
            $order_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $stmt->execute([$order_id, $item['id'], $item['qty'], $item['price']]);
            }
            
            $pdo->commit();
            unset($_SESSION['cart']);
            header("Location: order_success.php?id=" . $order_id);
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Order failed: " . $e->getMessage());
            $error = "We couldn't place your order due to a server error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Fresh-Factor</title>
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
        <h1 class="text-3xl font-bold text-[#1B5E20] mb-8"><?php echo htmlspecialchars(t('checkout')); ?></h1>
        
        <form method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <!-- Shipping Info -->
            <div class="bg-white rounded-3xl p-10 shadow-lg border border-green-50">
                <h3 class="text-xl font-bold text-gray-800 mb-8 border-b pb-4"><?php echo htmlspecialchars(t('shipping_information')); ?></h3>
                <?php if (isset($error)): ?>
                    <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-xl">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo htmlspecialchars(t('full_name')); ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full px-4 py-3 border rounded-xl outline-none focus:border-[#43A047]" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo htmlspecialchars(t('phone_number')); ?> <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" required class="w-full px-4 py-3 border rounded-xl outline-none focus:border-[#43A047]" placeholder="05X XXX XXXX">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo htmlspecialchars(t('email')); ?> <span class="text-gray-400 text-xs">(<?php echo htmlspecialchars(t('optional')); ?>)</span></label>
                        <input type="email" name="email" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-[#43A047]" placeholder="your@email.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo htmlspecialchars(t('shipping_address')); ?> <span class="text-red-500">*</span></label>
                        <textarea name="address" required rows="3" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-[#43A047]"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo htmlspecialchars(t('order_message')); ?> <span class="text-gray-400 text-xs">(<?php echo htmlspecialchars(t('optional')); ?>)</span></label>
                        <textarea name="message" rows="2" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-[#43A047]" placeholder="<?php echo htmlspecialchars(t('any_special_instructions')); ?>"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo htmlspecialchars(t('payment_method')); ?></label>
                        <div class="p-4 border-2 border-[#1B5E20] bg-green-50 rounded-xl flex items-center">
                            <i class="fas fa-money-bill-wave text-[#1B5E20] mr-3"></i>
                            <span class="font-bold text-[#1B5E20]"><?php echo htmlspecialchars(t('cash_on_delivery')); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div>
                <div class="bg-white rounded-3xl p-10 shadow-lg border border-green-100 mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-8 border-b pb-4"><?php echo htmlspecialchars(t('order_summary')); ?></h3>
                    <div class="space-y-4 mb-8">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600"><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['qty']; ?></span>
                                <span class="font-bold"><?php echo $item['price'] * $item['qty']; ?> SAR</span>
                            </div>
                        <?php endforeach; ?>
                        <div class="border-t pt-4 flex justify-between font-bold text-xl text-[#1B5E20]">
                            <span><?php echo htmlspecialchars(t('total')); ?></span>
                            <span><?php echo $total_amount; ?> SAR</span>
                        </div>
                    </div>
                    <button type="submit" name="place_order" class="w-full py-4 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition shadow-lg">
                        <?php echo htmlspecialchars(t('place_order')); ?> (SAR <?php echo $total_amount; ?>)
                    </button>
                </div>
                <div class="text-center">
                    <a href="cart.php" class="text-sm text-gray-400 hover:text-[#1B5E20]"><i class="fas fa-arrow-left mr-2"></i><?php echo htmlspecialchars(t('back_to_cart')); ?></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-[#1B5E20] text-white py-12 mt-16">
        <div class="container mx-auto px-6 text-center">
            <h3 class="text-2xl font-bold mb-4">Fresh-Factor</h3>
            <p class="text-green-100 text-sm max-w-xl mx-auto mb-6"><?php echo htmlspecialchars(t('footer_tagline')); ?></p>
            <p class="text-sm text-green-200">&copy; <?php echo date('Y'); ?> Fresh-Factor. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
