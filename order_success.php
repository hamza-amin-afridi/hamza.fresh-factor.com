<?php
require_once 'includes/db_connect.php';
ensure_session_started();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: profile.php");
    exit();
}

// Fetch order items
$stmt = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; background-color: #F9FBE7; }</style>
</head>
<body class="bg-[#F9FBE7]">
    <nav class="sticky top-0 bg-white shadow-md z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-[#1B5E20]">Fresh-Factor</a>
            <div class="flex items-center space-x-6">
                <a href="index.php" class="text-gray-700 hover:text-[#43A047]"><?php echo htmlspecialchars(t('home')); ?></a>
                <a href="logout.php" class="text-gray-500 hover:text-red-600"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-16">
        <div class="max-w-2xl mx-auto text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-3xl text-[#43A047]"></i>
            </div>
            <h1 class="text-4xl font-bold text-[#1B5E20] mb-4"><?php echo htmlspecialchars(t('order_success_title')); ?></h1>
            <p class="text-gray-600 mb-8"><?php echo htmlspecialchars(t('order_success_subtitle')); ?></p>
            
            <div class="bg-white rounded-3xl p-8 shadow-lg border border-green-50 mb-8 text-left">
                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4"><?php echo htmlspecialchars(t('order_details')); ?></h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Order ID:</span>
                        <span class="font-bold text-[#1B5E20]">#ORD-<?php echo $order['id']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Customer Name:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Phone:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if (!empty($order['customer_email'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Email:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Shipping Address:</span>
                        <span class="font-medium text-right max-w-xs"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
                    </div>
                    <?php if (!empty($order['customer_message'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Message:</span>
                        <span class="font-medium text-right max-w-xs"><?php echo nl2br(htmlspecialchars($order['customer_message'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="border-t pt-4 mt-4">
                        <h3 class="font-bold text-gray-800 mb-4"><?php echo htmlspecialchars(t('order_items')); ?></h3>
                        <?php foreach ($order_items as $item): ?>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600"><?php echo htmlspecialchars($item['product_name']); ?> x <?php echo $item['quantity']; ?></span>
                            <span class="font-medium"><?php echo $item['price_at_purchase'] * $item['quantity']; ?> SAR</span>
                        </div>
                        <?php endforeach; ?>
                        <div class="border-t pt-4 mt-4 flex justify-between font-bold text-xl text-[#1B5E20]">
                            <span><?php echo htmlspecialchars(t('total')); ?></span>
                            <span><?php echo $order['total_amount']; ?> SAR</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-center space-x-4">
                <a href="index.php" class="px-8 py-3 bg-[#1B5E20] text-white font-bold rounded-xl hover:bg-[#43A047] transition shadow-lg"><?php echo htmlspecialchars(t('continue_shopping')); ?></a>
                <a href="profile.php" class="px-8 py-3 bg-white text-[#1B5E20] font-bold rounded-xl hover:bg-gray-50 transition shadow"><?php echo htmlspecialchars(t('view_my_orders')); ?></a>
            </div>
        </div>
    </div>
</html>
