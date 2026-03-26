<?php
require_once 'includes/db_connect.php';
ensure_session_started();

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: products.php");
    exit();
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.active_status = 'active'");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    header("Location: products.php");
    exit();
}

// Multiple images: main + product_images
$all_images = [];
if (!empty($product['image_url'])) $all_images[] = $product['image_url'];
try {
    $stmt2 = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY display_order, id");
    $stmt2->execute([$id]);
    while ($row = $stmt2->fetch()) $all_images[] = $row['image_url'];
} catch (PDOException $e) { /* table may not exist */ }
if (empty($all_images)) $all_images[] = 'assets/placeholder.png';

// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (isset($_POST['csrf_token']) && verify_csrf_token($_POST['csrf_token'])) {
        $qty = max(1, min(50, (int)($_POST['quantity'] ?? 1)));
        $stmt = $pdo->prepare("SELECT stock_status FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p && $p['stock_status'] === 'in_stock') {
            if (!isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id] = 0;
            $_SESSION['cart'][$id] += $qty;
            header("Location: product.php?id=" . $id . "&added=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F9FBE7; }
        .thumb { cursor: pointer; border: 2px solid transparent; }
        .thumb:hover, .thumb.active { border-color: #1B5E20; }
        #lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 100; align-items: center; justify-content: center; padding: 2rem; }
        #lightbox.open { display: flex; }
        #lightbox img { max-width: 90%; max-height: 90%; object-fit: contain; }
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

    <?php if (isset($_GET['added'])): ?>
        <div class="container mx-auto px-6 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"><?php echo htmlspecialchars(t('added_to_cart')); ?></div>
        </div>
    <?php endif; ?>

    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <div class="bg-white rounded-3xl shadow-lg overflow-hidden mb-4">
                    <img id="mainImg" src="<?php echo htmlspecialchars(asset_url(!empty($all_images[0]) ? $all_images[0] : 'assets/placeholder.png')); ?>" class="w-full aspect-square object-cover cursor-zoom-in" alt="<?php echo htmlspecialchars($product['name']); ?>" onclick="openLightbox(this.src)">
                </div>
                <?php if (count($all_images) > 1): ?>
                    <div class="flex gap-2 flex-wrap">
                        <?php foreach ($all_images as $i => $url): ?>
                            <img src="<?php echo htmlspecialchars(asset_url(!empty($url) ? $url : 'assets/placeholder.png')); ?>" class="thumb w-20 h-20 object-cover rounded-lg <?php echo $i === 0 ? 'active' : ''; ?>" alt="" onclick="setMain('<?php echo htmlspecialchars(asset_url(!empty($url) ? $url : 'assets/placeholder.png')); ?>', this)">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-bold uppercase mb-2"><?php echo htmlspecialchars($product['category_name']); ?></p>
                <h1 class="text-4xl font-bold text-[#1B5E20] mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="text-3xl font-bold text-[#1B5E20] mb-6"><?php echo $product['price']; ?> SAR</p>
                <p class="text-gray-600 mb-8 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></p>
                <?php if ($product['stock_status'] === 'in_stock'): ?>
                    <form method="POST" class="flex flex-wrap gap-4 items-center">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                        <label class="font-medium"><?php echo htmlspecialchars(t('quantity')); ?></label>
                        <input type="number" name="quantity" value="1" min="1" max="50" class="w-24 px-3 py-2 border rounded-xl">
                        <button type="submit" name="add_to_cart" class="px-8 py-4 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition">
                            <i class="fas fa-shopping-basket mr-2"></i> <?php echo htmlspecialchars(t('add_to_cart')); ?>
                        </button>
                    </form>
                <?php else: ?>
                    <button disabled class="px-8 py-4 bg-gray-200 text-gray-500 font-bold rounded-xl cursor-not-allowed"><?php echo htmlspecialchars(t('out_of_stock')); ?></button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="lightbox" onclick="closeLightbox()">
        <img id="lightboxImg" src="" alt="" onclick="event.stopPropagation()">
    </div>

    <!-- Footer -->
    <footer class="bg-[#1B5E20] text-white py-12 mt-16">
        <div class="container mx-auto px-6 text-center">
            <h3 class="text-2xl font-bold mb-4">Fresh-Factor</h3>
            <p class="text-green-100 text-sm max-w-xl mx-auto mb-6"><?php echo htmlspecialchars(t('footer_tagline')); ?></p>
            <p class="text-sm text-green-200">&copy; <?php echo date('Y'); ?> Fresh-Factor. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function setMain(url, el) {
            document.getElementById('mainImg').src = url;
            document.querySelectorAll('.thumb').forEach(function(t){ t.classList.remove('active'); });
            if (el) el.classList.add('active');
        }
        function openLightbox(src) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('lightbox').classList.add('open');
        }
        function closeLightbox() { document.getElementById('lightbox').classList.remove('open'); }
    </script>
</body>
</html>
