<?php
require_once 'includes/db_connect.php';
ensure_session_started();

// Handle quantity updates and removals
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_qty'])) {
        $product_id = $_POST['product_id'];
        $new_qty = (int)$_POST['quantity'];
        if ($new_qty > 0) {
            $_SESSION['cart'][$product_id] = $new_qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    if (isset($_POST['remove_item'])) {
        unset($_SESSION['cart'][$_POST['product_id']]);
    }
}

$cart_items = [];
$total_amount = 0;
$out_of_stock_warning = false;

// Clean up orphaned cart items (products that no longer exist)
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $existing_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Remove cart items for deleted products
    foreach ($ids as $id) {
        if (!in_array($id, $existing_ids)) {
            unset($_SESSION['cart'][$id]);
        }
    }
}

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $total_amount += $subtotal;
        
        if ($p['stock_status'] == 'out_of_stock') {
            $out_of_stock_warning = true;
        }

        $cart_items[] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => $p['price'],
            'image' => $p['image_url'],
            'qty' => $qty,
            'subtotal' => $subtotal,
            'stock_status' => $p['stock_status']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; background-color: #F9FBE7; }</style>
</head>
<body class="bg-[#F9FBE7]">
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
            
            <!-- Mobile Menu Toggle -->
            <button class="md:hidden text-gray-700 focus:outline-none" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
                <i class="fas fa-bars text-2xl"></i>
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
        <h1 class="text-3xl font-bold text-[#1B5E20] mb-8"><?php echo htmlspecialchars(t('shopping_cart')); ?></h1>

        <?php if ($out_of_stock_warning): ?>
            <div class="mb-8 p-4 bg-red-100 text-red-700 rounded-xl flex items-center shadow-sm">
                <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
                <p class="font-medium text-sm">One or more items in your cart are out of stock. Please remove them to proceed to checkout.</p>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="bg-white rounded-3xl p-16 text-center shadow-lg">
                <div class="text-6xl mb-6">🛒</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars(t('cart_empty_title')); ?></h2>
                <p class="text-gray-500 mb-8"><?php echo htmlspecialchars(t('cart_empty_subtitle')); ?></p>
                <a href="products.php" class="px-8 py-4 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition"><?php echo htmlspecialchars(t('start_shopping')); ?></a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-6">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-green-50 flex items-center group">
                            <img src="<?php echo htmlspecialchars(!empty($item['image']) ? $item['image'] : 'assets/placeholder.png'); ?>" class="w-24 h-24 object-cover rounded-xl border" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="ml-6 flex-grow">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <form method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="remove_item" class="text-gray-400 hover:text-red-500 transition">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <p class="text-sm text-[#1B5E20] font-bold mb-4"><?php echo $item['price']; ?> SAR</p>
                                
                                <div class="flex items-center justify-between">
                                    <form method="POST" class="flex items-center bg-gray-50 rounded-lg px-2 py-1">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="update_qty" onclick="this.form.quantity.value--" class="p-1 text-gray-500 hover:text-[#1B5E20]"><i class="fas fa-minus text-xs"></i></button>
                                        <input type="number" name="quantity" value="<?php echo $item['qty']; ?>" class="w-12 text-center bg-transparent font-bold text-sm outline-none" readonly>
                                        <button type="submit" name="update_qty" onclick="this.form.quantity.value++" class="p-1 text-gray-500 hover:text-[#1B5E20]"><i class="fas fa-plus text-xs"></i></button>
                                        <input type="hidden" name="update_qty" value="1">
                                    </form>
                                    <p class="font-bold text-gray-800"><?php echo $item['subtotal']; ?> SAR</p>
                                </div>
                                
                                <?php if ($item['stock_status'] == 'out_of_stock'): ?>
                                    <p class="text-red-600 text-[10px] font-bold mt-2 uppercase">Out of Stock</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl p-8 shadow-lg border border-green-100 sticky top-32">
                        <h3 class="text-xl font-bold text-gray-800 mb-6">Order Summary</h3>
                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span><?php echo $total_amount; ?> SAR</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                <span class="text-green-600 font-bold uppercase text-xs">Calculated at next step</span>
                            </div>
                            <div class="border-t pt-4 flex justify-between font-bold text-xl text-gray-800">
                                <span>Total</span>
                                <span><?php echo $total_amount; ?> SAR</span>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-400 mb-2 uppercase">Promo Code</label>
                            <div class="flex">
                                <input type="text" placeholder="Enter code" class="flex-grow px-4 py-2 bg-gray-50 border rounded-l-xl outline-none focus:border-[#43A047]">
                                <button class="px-6 py-2 bg-gray-200 text-gray-700 font-bold rounded-r-xl hover:bg-gray-300 transition">Apply</button>
                            </div>
                        </div>

                        <a href="checkout.php" 
                           class="block w-full py-4 bg-[#1B5E20] text-white text-center font-bold rounded-xl hover:bg-[#43A047] transition shadow-lg <?php echo $out_of_stock_warning ? 'pointer-events-none opacity-50' : ''; ?>">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
