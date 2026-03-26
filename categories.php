<?php
require_once 'includes/db_connect.php';
ensure_session_started();

$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Fresh-Factor</title>
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

    <div class="container mx-auto px-6 py-16">
        <h1 class="text-4xl font-bold text-[#1B5E20] mb-4 text-center"><?php echo htmlspecialchars(t('shop_by_category')); ?></h1>
        <p class="text-gray-600 text-center mb-12"><?php echo htmlspecialchars(t('discover_categories_subtitle')); ?></p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($categories as $cat): 
                $link = match($cat['name']) {
                    'Fresh and Organic' => 'farms.php',
                    'Fresh Grow' => 'kits.php',
                    'Fresh Indoor' => 'indoor.php',
                    default => 'products.php?category=' . $cat['id']
                };
                // In database.sql the column name is 'image', but in index.php it was referred to as 'image_url'.
                // Checking both common names for robustness.
                $cat_image = !empty($cat['image']) ? $cat['image'] : (!empty($cat['image_url']) ? $cat['image_url'] : 'assets/placeholder_cat.jpg');
            ?>
            <a href="<?php echo $link; ?>" class="bg-white rounded-[2rem] shadow-xl overflow-hidden block group transition-all duration-500 hover:-translate-y-2">
                <div class="h-80 overflow-hidden relative">
                    <img src="<?php echo htmlspecialchars(asset_url($cat_image)); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-40 transition"></div>
                    <div class="absolute bottom-8 left-8 text-white">
                        <h3 class="text-3xl font-bold mb-2"><?php echo $cat['name']; ?></h3>
                        <p class="text-gray-200 text-sm line-clamp-2"><?php echo $cat['description']; ?></p>
                    </div>
                </div>
                <div class="p-8 flex justify-between items-center bg-white group-hover:bg-green-50 transition">
                    <span class="text-[#1B5E20] font-bold">Explore Now</span>
                    <div class="w-12 h-12 bg-[#1B5E20] text-white rounded-full flex items-center justify-center group-hover:translate-x-2 transition shadow-lg">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-[#1B5E20] text-white py-16 mt-20">
        <div class="container mx-auto px-6 text-center">
            <h3 class="text-2xl font-bold mb-6">Fresh-Factor</h3>
            <p class="text-green-100 text-sm max-w-xl mx-auto mb-8">&copy; 2024 Fresh-Factor. All rights reserved. Your premium destination for organic farm products in Saudi Arabia.</p>
        </div>
    </footer>
</body>
</html>
