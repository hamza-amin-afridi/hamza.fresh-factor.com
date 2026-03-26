<?php
require_once 'includes/db_connect.php';
ensure_session_started();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    <!-- About Section -->
    <div class="container mx-auto px-6 py-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1593113598332-cd288d649433?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="rounded-3xl shadow-2xl w-full h-[500px] object-cover" alt="About Fresh Grow">
                <div class="absolute -bottom-8 -right-8 bg-[#1B5E20] text-white p-8 rounded-2xl shadow-xl hidden md:block">
                    <p class="text-4xl font-bold">10+</p>
                    <p class="text-sm font-medium opacity-80 uppercase tracking-wider"><?php echo htmlspecialchars(t('years_of_purity')); ?></p>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-[#1B5E20] mb-8"><?php echo htmlspecialchars(t('about_section_title')); ?></h3>
                <p class="text-gray-600 mb-6 text-lg leading-relaxed">
                    <?php echo htmlspecialchars(t('about_intro')); ?>
                </p>
                <div class="space-y-4 mb-8">
                    <div class="flex items-start">
                        <div class="bg-green-100 p-2 rounded-lg mr-4 mt-1">
                            <i class="fas fa-check text-[#43A047]"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars(t('certified_organic_title')); ?></h4>
                            <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('certified_organic_desc')); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-green-100 p-2 rounded-lg mr-4 mt-1">
                            <i class="fas fa-heart text-[#43A047]"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars(t('sustainable_farming_title')); ?></h4>
                            <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('sustainable_farming_desc')); ?></p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-green-100 p-2 rounded-lg mr-4 mt-1">
                            <i class="fas fa-truck text-[#43A047]"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars(t('farm_to_door_title')); ?></h4>
                            <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('farm_to_door_desc')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-8 py-8 border-t border-gray-100">
                    <div>
                        <h5 class="text-3xl font-bold text-[#1B5E20]">50+</h5>
                        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('partner_farms')); ?></p>
                    </div>
                    <div>
                        <h5 class="text-3xl font-bold text-[#1B5E20]">10k+</h5>
                        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('happy_customers')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission & Vision -->
    <section class="bg-white py-20">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <span class="text-[#43A047] font-bold uppercase tracking-widest text-sm mb-4 block"><?php echo htmlspecialchars(t('our_philosophy')); ?></span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-12"><?php echo htmlspecialchars(t('philosophy_tagline')); ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 text-left">
                    <div class="bg-[#F9FBE7] p-10 rounded-3xl">
                        <div class="w-14 h-14 bg-[#1B5E20] text-white rounded-2xl flex items-center justify-center mb-6 text-2xl">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars(t('our_vision')); ?></h3>
                        <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars(t('our_vision_desc')); ?></p>
                    </div>
                    <div class="bg-[#F9FBE7] p-10 rounded-3xl">
                        <div class="w-14 h-14 bg-[#1B5E20] text-white rounded-2xl flex items-center justify-center mb-6 text-2xl">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars(t('our_mission')); ?></h3>
                        <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars(t('our_mission_desc')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
