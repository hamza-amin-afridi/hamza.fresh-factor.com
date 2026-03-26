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
        $success_msg = "Added to cart!";
        
        // Redirect to same page to clear POST data and show success
        header("Location: farms.php?success=1");
        exit();
    } else {
        $error_msg = "Product is out of stock!";
    }
}

$success_msg = isset($_GET['success']) ? "Added to cart!" : "";

$farms = $pdo->query("SELECT * FROM farms WHERE status = 'active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh & Organic Farms - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; background-color: #F9FBE7; }</style>
</head>
<body class="bg-[#F9FBE7]">
    <!-- Header (simplified for demo, should be included from a header.php) -->
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
        <h1 class="text-4xl font-bold text-[#1B5E20] mb-4 text-center">Fresh & Organic Farms</h1>
        <p class="text-gray-600 text-center mb-12">Explore our premium partner farms delivering nature's best.</p>

        <?php if (isset($success_msg)): ?>
            <div class="max-w-md mx-auto mb-8 p-4 bg-green-100 text-green-700 rounded-xl text-center shadow-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <?php foreach ($farms as $farm): ?>
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-green-100 flex flex-col">
                    <div class="relative h-64">
                        <img src="<?php echo htmlspecialchars(asset_url($farm['image'] ?: 'assets/placeholder_farm.png')); ?>" class="w-full h-full object-cover" alt="<?php echo htmlspecialchars($farm['name']); ?>">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-8">
                            <h2 class="text-3xl font-bold text-white"><?php echo $farm['name']; ?></h2>
                        </div>
                    </div>
                    <div class="p-8 flex-grow">
                        <p class="text-gray-600 mb-8 leading-relaxed"><?php echo $farm['description']; ?></p>
                        
                        <h3 class="font-bold text-[#1B5E20] mb-6 border-b pb-2 flex items-center">
                            <i class="fas fa-leaf mr-2 text-[#43A047]"></i> Farm Products
                        </h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php 
                            $stmt = $pdo->prepare("SELECT * FROM products WHERE farm_id = ? AND active_status = 'active' LIMIT ?");
                            $stmt->bindValue(1, $farm['id'], PDO::PARAM_INT);
                            $stmt->bindValue(2, $farm['max_products'], PDO::PARAM_INT);
                            $stmt->execute();
                            $products = $stmt->fetchAll();

                            foreach ($products as $p): 
                            ?>
                            <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-[#43A047]/40 hover:shadow-md transition group flex flex-col">
                                <a href="product.php?id=<?php echo $p['id']; ?>" class="block focus:outline-none focus:ring-2 focus:ring-[#43A047] focus:ring-offset-2 rounded-2xl">
                                    <div class="relative h-40 mb-0 overflow-hidden">
                                        <img src="<?php echo htmlspecialchars(asset_url($p['image_url'] ?: 'assets/placeholder.png')); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="<?php echo htmlspecialchars($p['name']); ?>">
                                        <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-full font-bold text-[#1B5E20] shadow-sm text-xs">
                                            <?php echo $p['price']; ?> SAR
                                        </div>
                                        <?php if ($p['stock_status'] == 'out_of_stock'): ?>
                                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                                <span class="text-white text-[10px] font-bold px-2 py-1 bg-red-600 rounded"><?php echo htmlspecialchars(t('out_of_stock')); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-bold text-sm text-gray-800 mb-1 line-clamp-1 group-hover:text-[#1B5E20] transition"><?php echo htmlspecialchars($p['name']); ?></h4>
                                        <p class="text-gray-500 text-xs line-clamp-2"><?php echo !empty($p['short_description']) ? htmlspecialchars($p['short_description']) : htmlspecialchars(substr($p['description'], 0, 90)); ?></p>
                                    </div>
                                </a>

                                <div class="px-4 pb-4 mt-auto">
                                    <?php if ($p['stock_status'] == 'in_stock'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" name="add_to_cart" class="w-full py-3 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition">
                                            <?php echo htmlspecialchars(t('add_to_cart')); ?>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="w-full py-3 bg-gray-200 text-gray-500 font-bold rounded-xl cursor-not-allowed">
                                        <?php echo htmlspecialchars(t('out_of_stock')); ?>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($products)): ?>
                                <p class="col-span-full text-center text-gray-400 py-8 italic">No products available for this farm yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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
