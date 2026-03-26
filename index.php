<?php
require_once 'includes/db_connect.php';
ensure_session_started();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_msg = t('invalid_session_token_refresh');
    } else {
        $product_id = (int)$_POST['product_id'];
        $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        // Simple quantity guard
        if ($qty < 1) {
            $qty = 1;
        } elseif ($qty > 50) {
            $qty = 50;
        }
        
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
            $success_msg = t('added_to_cart');
            
            // Redirect to same page to clear POST data and show success (optional but recommended)
            header("Location: index.php?success=1");
            exit();
        } else {
            $error_msg = t('product_out_of_stock');
        }
    }
}
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();

$success_msg = isset($_GET['success']) ? t('added_to_cart') : "";
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh-Factor - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F9FBE7; }
        .hero-section { 
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1500651230702-0e2d8a49d4ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); 
            background-size: cover; 
            background-position: center; 
            height: 70vh; 
        }
        .category-card { transition: all 0.5s; }
        .category-card:hover { transform: translateY(-10px); }
    </style>
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
            
            <!-- Mobile Menu Toggle -->
            <button class="md:hidden text-gray-700 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </nav>
        <?php if ($success_msg): ?>
            <div class="container mx-auto px-6 mt-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $success_msg; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg) && $error_msg): ?>
            <div class="container mx-auto px-6 mt-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_msg; ?></span>
                </div>
            </div>
        <?php endif; ?>

    <!-- Hero -->
    <section class="hero-section flex items-center justify-center text-center text-white px-6">
        <div>
            <h1 class="text-5xl md:text-6xl font-bold mb-4"><?php echo htmlspecialchars(t('hero_title')); ?></h1>
            <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto"><?php echo htmlspecialchars(t('hero_subtitle')); ?></p>
            <div class="flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-4">
                <a href="products.php" class="px-8 py-4 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition shadow-lg"><?php echo htmlspecialchars(t('shop_now')); ?></a>
                <a href="categories.php" class="px-8 py-4 bg-white text-[#1B5E20] font-bold rounded-xl hover:bg-gray-100 transition shadow-lg"><?php echo htmlspecialchars(t('explore_categories')); ?></a>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section id="categories" class="container mx-auto px-6 py-20">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12">
            <div>
                <h2 class="text-3xl font-bold text-[#1B5E20] mb-2"><?php echo htmlspecialchars(t('homepage_categories_title')); ?></h2>
                <p class="text-gray-500"><?php echo htmlspecialchars(t('homepage_categories_subtitle')); ?></p>
            </div>
            <a href="products.php" class="text-[#1B5E20] font-bold hover:text-[#43A047] transition flex items-center mt-4 md:mt-0">
                <?php echo htmlspecialchars(t('view_all_categories')); ?> <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <?php 
            $i = 0;
            $btn_texts = [t('explore_farms'), t('view_kits'), t('shop_indoor')];
            foreach ($categories as $cat): 
                $link = match($cat['name']) {
                    'Fresh and Organic' => 'farms.php',
                    'Fresh Grow' => 'kits.php',
                    'Fresh Indoor' => 'indoor.php',
                    default => 'products.php?category=' . $cat['id']
                };
                $cat_image = !empty($cat['image']) ? $cat['image'] : 'assets/categories/fresh_and_organic.jpg';
                $cat_image_src = asset_url($cat_image);
            ?>
            <a href="<?php echo $link; ?>" class="category-card bg-white rounded-3xl shadow-lg overflow-hidden block group">
                <div class="h-72 overflow-hidden relative">
                    <img src="<?php echo htmlspecialchars($cat_image_src); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-60 group-hover:opacity-40 transition"></div>
                    <div class="absolute bottom-6 left-6 text-white">
                        <span class="text-xs font-bold uppercase tracking-wider opacity-80 mb-1 block"><?php echo htmlspecialchars(t('category_label')); ?></span>
                        <h3 class="text-2xl font-bold"><?php echo $cat['name']; ?></h3>
                    </div>
                </div>
                <div class="p-8 flex justify-between items-center bg-white group-hover:bg-green-50 transition">
                    <span class="text-gray-600 font-medium"><?php echo htmlspecialchars($btn_texts[$i] ?? t('shop_now')); ?></span>
                    <div class="w-10 h-10 bg-[#1B5E20] text-white rounded-full flex items-center justify-center group-hover:translate-x-2 transition">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </div>
                </div>
            </a>
            <?php $i++; endforeach; ?>
        </div>
    </section>

    <!-- Products -->
    <section class="container mx-auto px-6 py-20 bg-white rounded-[50px] shadow-sm">
        <div class="text-center mb-16">
            <span class="text-[#43A047] font-bold uppercase tracking-widest text-sm mb-4 block"><?php echo htmlspecialchars(t('our_collection')); ?></span>
            <h2 class="text-4xl font-bold text-[#1B5E20] mb-4"><?php echo htmlspecialchars(t('featured_products')); ?></h2>
            <p class="text-gray-500 max-w-2xl mx-auto"><?php echo htmlspecialchars(t('featured_products_subtitle')); ?></p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php 
            // Only show top 4 products on home page
            $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock_status = 'in_stock' AND p.active_status = 'active' LIMIT 4");
            $products = $stmt->fetchAll();
            foreach ($products as $p): 
            ?>
            <div class="bg-white rounded-3xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 group flex flex-col h-full border border-gray-50">
                <a href="product.php?id=<?php echo $p['id']; ?>" class="block focus:outline-none focus:ring-2 focus:ring-[#43A047] focus:ring-offset-2">
                    <div class="h-56 sm:h-64 overflow-hidden relative">
                        <img src="<?php echo htmlspecialchars(asset_url(!empty($p['image_url']) ? $p['image_url'] : 'assets/placeholder.png')); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-[#1B5E20] text-[10px] font-bold rounded-full uppercase tracking-wider shadow-sm">
                                <?php echo $p['category_name']; ?>
                            </span>
                        </div>
                        <div class="absolute top-4 right-4">
                            <div class="bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full font-bold text-[#1B5E20] shadow-sm text-sm">
                                <?php echo $p['price']; ?> SAR
                            </div>
                        </div>
                    </div>
                    <div class="p-6 flex flex-col">
                        <h3 class="font-bold text-gray-800 text-lg sm:text-xl mb-2 group-hover:text-[#1B5E20] transition line-clamp-1"><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p class="text-gray-500 text-sm sm:text-base mb-4 line-clamp-2"><?php echo !empty($p['short_description']) ? htmlspecialchars($p['short_description']) : htmlspecialchars(substr($p['description'], 0, 150)); ?></p>
                    </div>
                </a>
                
                <div class="px-6 pb-6 mt-auto">
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                        <button type="submit" name="add_to_cart" class="w-full py-3 bg-[#F9FBE7] text-[#1B5E20] font-bold rounded-xl hover:bg-[#1B5E20] hover:text-white transition-all duration-300 flex items-center justify-center group-button">
                            <i class="fas fa-shopping-basket mr-2 text-sm"></i> <?php echo htmlspecialchars(t('add_to_cart')); ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-16 text-center">
            <a href="products.php" class="inline-flex items-center px-10 py-4 bg-[#1B5E20] text-white font-bold rounded-2xl hover:bg-[#43A047] transition shadow-xl hover:-translate-y-1 duration-300">
                <?php echo htmlspecialchars(t('explore_all_products')); ?> <i class="fas fa-arrow-right ml-3"></i>
            </a>
        </div>
    </section>

    <!-- How it works -->
    <section class="container mx-auto px-6 py-20">
        <div class="text-center mb-14">
            <span class="text-[#43A047] font-bold uppercase tracking-widest text-sm"><?php echo htmlspecialchars(t('simple_steps')); ?></span>
            <h2 class="text-4xl font-bold text-[#1B5E20] mt-2 mb-4"><?php echo htmlspecialchars(t('how_it_works')); ?></h2>
            <p class="text-gray-500 max-w-xl mx-auto"><?php echo htmlspecialchars(t('how_it_works_subtitle')); ?></p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="text-center">
                <div class="w-20 h-20 rounded-full bg-[#1B5E20] text-white flex items-center justify-center text-2xl font-bold mx-auto mb-6">1</div>
                <h3 class="font-bold text-xl text-gray-800 mb-2"><?php echo htmlspecialchars(t('step1_title')); ?></h3>
                <p class="text-gray-500"><?php echo htmlspecialchars(t('step1_desc')); ?></p>
            </div>
            <div class="text-center">
                <div class="w-20 h-20 rounded-full bg-[#1B5E20] text-white flex items-center justify-center text-2xl font-bold mx-auto mb-6">2</div>
                <h3 class="font-bold text-xl text-gray-800 mb-2"><?php echo htmlspecialchars(t('step2_title')); ?></h3>
                <p class="text-gray-500"><?php echo htmlspecialchars(t('step2_desc')); ?></p>
            </div>
            <div class="text-center">
                <div class="w-20 h-20 rounded-full bg-[#1B5E20] text-white flex items-center justify-center text-2xl font-bold mx-auto mb-6">3</div>
                <h3 class="font-bold text-xl text-gray-800 mb-2"><?php echo htmlspecialchars(t('step3_title')); ?></h3>
                <p class="text-gray-500"><?php echo htmlspecialchars(t('step3_desc')); ?></p>
            </div>
        </div>
    </section>

    <!-- Trust Badges -->
    <section class="bg-white py-16">
        <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
            <div>
                <i class="fas fa-leaf text-4xl text-[#43A047] mb-4"></i>
                <h4 class="font-bold text-lg"><?php echo htmlspecialchars(t('badge_organic_title')); ?></h4>
                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('badge_organic_desc')); ?></p>
            </div>
            <div>
                <i class="fas fa-shipping-fast text-4xl text-[#43A047] mb-4"></i>
                <h4 class="font-bold text-lg"><?php echo htmlspecialchars(t('badge_delivery_title')); ?></h4>
                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('badge_delivery_desc')); ?></p>
            </div>
            <div>
                <i class="fas fa-shield-alt text-4xl text-[#43A047] mb-4"></i>
                <h4 class="font-bold text-lg"><?php echo htmlspecialchars(t('badge_payment_title')); ?></h4>
                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('badge_payment_desc')); ?></p>
            </div>
            <div>
                <i class="fas fa-headset text-4xl text-[#43A047] mb-4"></i>
                <h4 class="font-bold text-lg"><?php echo htmlspecialchars(t('badge_support_title')); ?></h4>
                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('badge_support_desc')); ?></p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-[#1B5E20] text-white py-16">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl md:text-5xl font-bold mb-2">10K+</div>
                    <p class="text-green-200"><?php echo htmlspecialchars(t('stats_happy_customers')); ?></p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold mb-2">50+</div>
                    <p class="text-green-200"><?php echo htmlspecialchars(t('stats_organic_products')); ?></p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold mb-2">15+</div>
                    <p class="text-green-200"><?php echo htmlspecialchars(t('stats_partner_farms')); ?></p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold mb-2">99%</div>
                    <p class="text-green-200"><?php echo htmlspecialchars(t('stats_satisfaction_rate')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Farms -->
    <section class="container mx-auto px-6 py-20">
        <div class="text-center mb-14">
            <span class="text-[#43A047] font-bold uppercase tracking-widest text-sm"><?php echo htmlspecialchars(t('our_partners')); ?></span>
            <h2 class="text-4xl font-bold text-[#1B5E20] mt-2 mb-4"><?php echo htmlspecialchars(t('trusted_local_farms')); ?></h2>
            <p class="text-gray-500 max-w-xl mx-auto"><?php echo htmlspecialchars(t('trusted_local_farms_subtitle')); ?></p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden group">
                <div class="h-64 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&q=80" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="Dirab Farm">
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-[#1B5E20] mb-2">Dirab Farm</h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(t('dirab_farm_desc')); ?></p>
                    <a href="farms.php" class="text-[#1B5E20] font-bold hover:text-[#43A047] flex items-center"><?php echo htmlspecialchars(t('explore_products')); ?> <i class="fas fa-arrow-right ml-2"></i></a>
                </div>
            </div>
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden group">
                <div class="h-64 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="Hannan Farm">
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold text-[#1B5E20] mb-2">Hannan Farm</h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(t('hannan_farm_desc')); ?></p>
                    <a href="farms.php" class="text-[#1B5E20] font-bold hover:text-[#43A047] flex items-center"><?php echo htmlspecialchars(t('explore_products')); ?> <i class="fas fa-arrow-right ml-2"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="bg-[#F9FBE7] py-20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-14">
                <span class="text-[#43A047] font-bold uppercase tracking-widest text-sm"><?php echo htmlspecialchars(t('testimonials')); ?></span>
                <h2 class="text-4xl font-bold text-[#1B5E20] mt-2 mb-4"><?php echo htmlspecialchars(t('what_customers_say')); ?></h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-3xl shadow-md">
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 mb-6">"<?php echo htmlspecialchars(t('testimonial_1')); ?>"</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-[#1B5E20] rounded-full flex items-center justify-center text-white font-bold">A</div>
                        <div class="ml-4">
                            <p class="font-bold text-gray-800">Ahmed Al-Rashid</p>
                            <p class="text-sm text-gray-500">Riyadh</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-md">
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 mb-6">"<?php echo htmlspecialchars(t('testimonial_2')); ?>"</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-[#43A047] rounded-full flex items-center justify-center text-white font-bold">S</div>
                        <div class="ml-4">
                            <p class="font-bold text-gray-800">Sarah Al-Otaibi</p>
                            <p class="text-sm text-gray-500">Jeddah</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-md">
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 mb-6">"<?php echo htmlspecialchars(t('testimonial_3')); ?>"</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-[#1B5E20] rounded-full flex items-center justify-center text-white font-bold">M</div>
                        <div class="ml-4">
                            <p class="font-bold text-gray-800">Mohammed Al-Harbi</p>
                            <p class="text-sm text-gray-500">Dammam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="container mx-auto px-6 py-20">
        <div class="text-center mb-14">
            <span class="text-[#43A047] font-bold uppercase tracking-widest text-sm"><?php echo htmlspecialchars(t('faq')); ?></span>
            <h2 class="text-4xl font-bold text-[#1B5E20] mt-2 mb-4"><?php echo htmlspecialchars(t('frequently_asked_questions')); ?></h2>
        </div>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <button class="w-full px-6 py-4 text-left font-bold text-gray-800 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <?php echo htmlspecialchars(t('faq_q1')); ?>
                    <i class="fas fa-chevron-down text-[#1B5E20]"></i>
                </button>
                <div class="px-6 pb-4 text-gray-600 hidden">
                    <?php echo htmlspecialchars(t('faq_a1')); ?>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <button class="w-full px-6 py-4 text-left font-bold text-gray-800 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <?php echo htmlspecialchars(t('faq_q2')); ?>
                    <i class="fas fa-chevron-down text-[#1B5E20]"></i>
                </button>
                <div class="px-6 pb-4 text-gray-600 hidden">
                    <?php echo htmlspecialchars(t('faq_a2')); ?>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <button class="w-full px-6 py-4 text-left font-bold text-gray-800 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <?php echo htmlspecialchars(t('faq_q3')); ?>
                    <i class="fas fa-chevron-down text-[#1B5E20]"></i>
                </button>
                <div class="px-6 pb-4 text-gray-600 hidden">
                    <?php echo htmlspecialchars(t('faq_a3')); ?>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <button class="w-full px-6 py-4 text-left font-bold text-gray-800 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <?php echo htmlspecialchars(t('faq_q4')); ?>
                    <i class="fas fa-chevron-down text-[#1B5E20]"></i>
                </button>
                <div class="px-6 pb-4 text-gray-600 hidden">
                    <?php echo htmlspecialchars(t('faq_a4')); ?>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <button class="w-full px-6 py-4 text-left font-bold text-gray-800 flex justify-between items-center" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <?php echo htmlspecialchars(t('faq_q5')); ?>
                    <i class="fas fa-chevron-down text-[#1B5E20]"></i>
                </button>
                <div class="px-6 pb-4 text-gray-600 hidden">
                    <?php echo htmlspecialchars(t('faq_a5')); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Instagram/Social Proof -->
    <section class="bg-[#1B5E20] text-white py-16">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars(t('join_community')); ?></h2>
            <p class="text-green-100 mb-8 max-w-xl mx-auto"><?php echo htmlspecialchars(t('community_subtitle')); ?></p>
            <div class="flex justify-center space-x-6 mb-8">
                <a href="#" class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition text-2xl"><i class="fab fa-instagram"></i></a>
                <a href="#" class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition text-2xl"><i class="fab fa-twitter"></i></a>
                <a href="#" class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition text-2xl"><i class="fab fa-facebook"></i></a>
                <a href="#" class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition text-2xl"><i class="fab fa-snapchat"></i></a>
            </div>
            <p class="text-sm text-green-200">@freshfactor.sa | #FreshFactor #OrganicLiving</p>
        </div>
    </section>

    <!-- Why choose us / CTA -->
    <section class="bg-[#F9FBE7] py-20">
        <div class="container mx-auto px-6 text-center max-w-3xl">
            <h2 class="text-3xl md:text-4xl font-bold text-[#1B5E20] mb-6"><?php echo htmlspecialchars(t('ready_to_experience_freshness')); ?></h2>
            <p class="text-gray-600 text-lg mb-10"><?php echo htmlspecialchars(t('cta_subtitle')); ?></p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="products.php" class="px-10 py-4 bg-[#1B5E20] text-white font-bold rounded-2xl hover:bg-[#43A047] transition shadow-xl inline-block"><?php echo htmlspecialchars(t('shop_now')); ?></a>
                <a href="signup.php" class="px-10 py-4 bg-white text-[#1B5E20] font-bold rounded-2xl border-2 border-[#1B5E20] hover:bg-[#1B5E20] hover:text-white transition inline-block"><?php echo htmlspecialchars(t('create_account')); ?></a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#1B5E20] text-white py-16">
        <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">
            <div>
                <h3 class="text-2xl font-bold mb-6">Fresh-Factor</h3>
                <p class="text-green-100 text-sm leading-relaxed"><?php echo htmlspecialchars(t('footer_about_long')); ?></p>
            </div>
            <div>
                <h4 class="font-bold mb-6"><?php echo htmlspecialchars(t('quick_links')); ?></h4>
                <ul class="space-y-4 text-green-100 text-sm">
                    <li><a href="index.php" class="hover:underline"><?php echo htmlspecialchars(t('home')); ?></a></li>
                    <li><a href="about.php" class="hover:underline"><?php echo htmlspecialchars(t('about_us')); ?></a></li>
                    <li><a href="products.php" class="hover:underline"><?php echo htmlspecialchars(t('products')); ?></a></li>
                    <li><a href="help.php" class="hover:underline"><?php echo htmlspecialchars(t('help_center')); ?></a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-6"><?php echo htmlspecialchars(t('contact_us')); ?></h4>
                <ul class="space-y-4 text-green-100 text-sm">
                    <li><i class="fas fa-envelope mr-2"></i> info@fresh-factor.com</li>
                    <li><i class="fas fa-phone mr-2"></i> 053 97 53 768</li>
                    <li><i class="fas fa-map-marker-alt mr-2"></i> Riyadh KSA</li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-6"><?php echo htmlspecialchars(t('newsletter')); ?></h4>
                <form class="flex flex-col space-y-4">
                    <input type="email" placeholder="<?php echo htmlspecialchars(t('enter_your_email')); ?>" class="px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-green-200 outline-none">
                    <button class="px-6 py-3 bg-white text-[#1B5E20] font-bold rounded-xl hover:bg-green-50 transition"><?php echo htmlspecialchars(t('subscribe')); ?></button>
                </form>
            </div>
        </div>
        <div class="container mx-auto px-6 mt-12 pt-8 border-t border-white/10 text-center text-sm text-green-200 space-y-2">
            <p>&copy; <?php echo date('Y'); ?> Fresh-Factor. All rights reserved.</p>
            <p><a href="https://wa.me/923011203538?text=Hey%20Hamza%20Amin%20!%20I%20need%20a%20quotation%20for%20website%20development%20" target="_blank" rel="noopener" class="text-green-200 hover:text-white transition font-semibold" style="text-shadow: 0 0 8px rgba(255,255,255,0.6);">Developed by Hamza Amin Afridi</a></p>
        </div>
    </footer>

</body>
</html>
