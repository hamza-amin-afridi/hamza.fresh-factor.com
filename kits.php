<?php
require_once 'includes/db_connect.php';
ensure_session_started();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Check stock
    $stmt = $pdo->prepare("SELECT stock_status FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $prod = $stmt->fetch();
    
    if ($prod && $prod['stock_status'] == 'in_stock') {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
        
        // Redirect to same page to clear POST data and show success
        header("Location: kits.php?success=1");
        exit();
    } else {
        $error_msg = "Product is out of stock!";
    }
}

$success_msg = isset($_GET['success']) ? "Added to cart!" : "";

// Handle add all to cart for kits
if (isset($_POST['add_kit_to_cart'])) {
    $kit_id = $_POST['kit_id'];
    
    // In a real app, a kit might be a collection of products. 
    // For this simplified version, we treat the kit as a single bundle product.
    $stmt = $pdo->prepare("SELECT id, stock_status FROM products WHERE id = ? AND is_kit = 1");
    $stmt->execute([$kit_id]);
    $kit = $stmt->fetch();
    
    if ($kit && $kit['stock_status'] == 'in_stock') {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$kit_id])) {
            $_SESSION['cart'][$kit_id] += 1;
        } else {
            $_SESSION['cart'][$kit_id] = 1;
        }
        $success_msg = "Kit added to cart!";
    } else {
        $error_msg = "Kit is out of stock!";
    }
}

// Fetch Fresh Grow Kits
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = (SELECT id FROM categories WHERE name = 'Fresh Grow') AND active_status = 'active'");
$stmt->execute();
$kits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh Grow Kits - Fresh-Factor</title>
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
            <button class="md:hidden text-gray-700 focus:outline-none" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" aria-label="Menu">
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
        <h1 class="text-4xl font-bold text-[#1B5E20] mb-4 text-center"><?php echo htmlspecialchars(t('fresh_grow_kits')); ?></h1>
        <p class="text-gray-600 text-center mb-12"><?php echo htmlspecialchars(t('fresh_grow_kits_subtitle')); ?></p>

        <?php if (isset($success_msg)): ?>
            <div class="max-w-md mx-auto mb-8 p-4 bg-green-100 text-green-700 rounded-xl text-center shadow-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-5xl mx-auto">
            <?php foreach ($kits as $kit): ?>
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-green-100 flex flex-col hover:shadow-2xl transition duration-300">
                    <a href="product.php?id=<?php echo $kit['id']; ?>" class="block focus:outline-none focus:ring-2 focus:ring-[#43A047] focus:ring-offset-2">
                        <div class="h-56 sm:h-64 overflow-hidden relative">
                            <img src="<?php echo htmlspecialchars(asset_url($kit['image_url'] ?: 'assets/placeholder_kit.png')); ?>" class="w-full h-full object-cover" alt="<?php echo htmlspecialchars($kit['name']); ?>">
                            <div class="absolute top-4 right-4 bg-[#1B5E20] text-white px-4 py-2 rounded-full font-bold text-sm shadow-lg">
                                <?php echo $kit['price']; ?> SAR
                            </div>
                        </div>
                        <div class="p-6 sm:p-10 flex-grow flex flex-col">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-3 sm:mb-4 line-clamp-1"><?php echo htmlspecialchars($kit['name']); ?></h2>
                            <p class="text-gray-600 text-sm sm:text-base mb-6 leading-relaxed line-clamp-3"><?php echo htmlspecialchars($kit['description']); ?></p>
                        </div>
                    </a>

                    <div class="px-6 sm:px-10 pb-8 flex-grow flex flex-col">
                        
                        <div class="bg-gray-50 rounded-2xl p-6 mb-8 border border-dashed border-green-200">
                            <h4 class="font-bold text-[#1B5E20] mb-3 text-sm">WHAT'S IN THE BOX?</h4>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li><i class="fas fa-check text-green-500 mr-2"></i> Premium Seeds</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i> Organic Potting Mix</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i> Eco-friendly Pots</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i> Detailed Care Guide</li>
                            </ul>
                            <?php if ($kit['stock_status'] == 'in_stock'): ?>
                                <form method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $kit['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="w-full py-3 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition">
                                        Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="w-full py-3 bg-gray-400 text-white font-bold rounded-xl cursor-not-allowed">
                                    OUT OF STOCK
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($kits)): ?>
                <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-dashed">
                    <p class="text-gray-400 italic">Fresh Grow Kits coming soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
