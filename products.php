<?php
require_once 'includes/db_connect.php';
ensure_session_started();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error_msg = "Invalid session token. Please refresh the page and try again.";
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
            
            // Redirect to same page to clear POST data and show success
            header("Location: products.php?success=1" . (isset($_GET['category']) ? "&category=".$_GET['category'] : ""));
            exit();
        } else {
            $error_msg = "Product is out of stock!";
        }
    }
}

$success_msg = isset($_GET['success']) ? "Added to cart!" : "";

$category_filter = isset($_GET['category']) ? $_GET['category'] : null;
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.active_status = 'active'";
$params = [];

if ($category_filter) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

if ($search_query) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$products = [];
$query_with_order = $query . " ORDER BY p.id DESC";
$stmt = $pdo->prepare($query_with_order);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();
} catch (PDOException $e) {
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Fresh-Factor</title>
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
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
            <h1 class="text-4xl font-bold text-[#1B5E20] mb-6 md:mb-0"><?php echo htmlspecialchars(t('our_products')); ?></h1>
            
            <form action="products.php" method="GET" class="flex w-full md:w-auto">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="<?php echo htmlspecialchars(t('search_products_placeholder')); ?>" class="px-6 py-3 rounded-l-xl border-y border-l focus:border-[#43A047] outline-none w-full md:w-64">
                <button type="submit" class="px-6 py-3 bg-[#1B5E20] text-white rounded-r-xl hover:bg-[#43A047] transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="flex flex-wrap gap-4 mb-12">
            <a href="products.php" class="px-6 py-2 rounded-full font-semibold transition <?php echo !$category_filter ? 'bg-[#1B5E20] text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?>"><?php echo htmlspecialchars(t('all')); ?></a>
            <?php foreach ($categories as $cat): ?>
                <a href="products.php?category=<?php echo $cat['id']; ?>" 
                   class="px-6 py-2 rounded-full font-semibold transition <?php echo $category_filter == $cat['id'] ? 'bg-[#1B5E20] text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?>">
                    <?php echo $cat['name']; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($products as $p): ?>
                <div class="bg-white rounded-3xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 group flex flex-col h-full border border-gray-50">
                    <a href="product.php?id=<?php echo $p['id']; ?>" class="block focus:outline-none focus:ring-2 focus:ring-[#43A047] focus:ring-offset-2">
                        <div class="h-56 sm:h-64 overflow-hidden relative">
                            <img src="<?php echo htmlspecialchars(asset_url($p['image_url'] ?: 'assets/placeholder.png')); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="<?php echo htmlspecialchars($p['name']); ?>">
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
                        <?php if ($p['stock_status'] == 'in_stock'): ?>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                                <button type="submit" name="add_to_cart" class="w-full py-3 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition">
                                    Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled class="w-full py-3 bg-gray-200 text-gray-400 font-bold rounded-xl cursor-not-allowed">
                                Out of Stock
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($products)): ?>
                <div class="col-span-full py-20 text-center">
                    <div class="text-6xl mb-6">🔍</div>
                    <h3 class="text-2xl font-bold text-gray-800">No products found</h3>
                    <p class="text-gray-500">Try adjusting your search or filters.</p>
                </div>
            <?php endif; ?>
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
