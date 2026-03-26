<?php
require_once 'includes/db_connect.php';
ensure_session_started();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Order ID missing");
}

$order_id = $_GET['id'];

// Fetch order details
$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.phone_number FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found");
}

// Security check: only the owner or an admin can see the receipt
if (!isset($_SESSION['admin_id']) && $_SESSION['user_id'] != $order['user_id']) {
    die("Unauthorized access");
}

// Fetch order items
$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attr()); ?>" dir="<?php echo htmlspecialchars(html_dir_attr()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #ORD-<?php echo $order_id; ?> - Fresh-Factor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; padding: 2rem; }
        @media print {
            body { background-color: white; padding: 0; }
            .no-print { display: none; }
            .print-container { box-shadow: none; border: none; width: 100%; max-width: 100%; }
        }
        .receipt-container { background: white; max-width: 800px; margin: 0 auto; padding: 3rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="no-print container mx-auto px-6 py-6 flex justify-between items-center">
        <button onclick="window.print()" class="px-6 py-2 bg-[#1B5E20] text-white rounded-lg hover:bg-[#43A047] transition">
            <i class="fas fa-print mr-2"></i>
            <?php echo htmlspecialchars(t('print_receipt')); ?>
        </button>
        <button onclick="window.history.back()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"><?php echo htmlspecialchars(t('back')); ?></button>
    </div>

    <div class="receipt-container print-container">
        <!-- Header -->
        <div class="flex justify-between items-start border-b pb-8 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#1B5E20]">FRESH-FACTOR</h1>
                <p class="text-gray-500 text-sm mt-1">Premium Organic & Indoor Products</p>
                <div class="mt-4 text-sm text-gray-600">
                    <p>Riyadh, Saudi Arabia</p>
                    <p>Contact: 053 97 53 768</p>
                    <p>Email: info@fresh-factor.com</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars(t('receipt')); ?></h2>
                <p class="text-gray-500 text-sm mt-1">Order ID: #ORD-<?php echo $order_id; ?></p>
                <p class="text-gray-500 text-sm italic">Date: <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                <div class="mt-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase 
                        <?php echo $order['status'] == 'delivered' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'; ?>">
                        <?php echo $order['status']; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="grid grid-cols-2 gap-12 mb-12">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3"><?php echo htmlspecialchars(t('billed_to')); ?></h3>
                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($order['customer_name'] ?? $order['full_name'] ?? '—'); ?></p>
                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($order['customer_email'] ?? $order['email'] ?? '—'); ?></p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['customer_phone'] ?? $order['phone_number'] ?? '—'); ?></p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3"><?php echo htmlspecialchars(t('shipping_address')); ?></h3>
                <p class="text-sm text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars((string)($order['shipping_address'] ?? ''))); ?></p>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full text-left mb-8">
            <thead>
                <tr class="border-b-2 border-gray-100">
                    <th class="py-4 font-bold text-gray-800"><?php echo htmlspecialchars(t('product_name')); ?></th>
                    <th class="py-4 font-bold text-gray-800 text-center"><?php echo htmlspecialchars(t('qty')); ?></th>
                    <th class="py-4 font-bold text-gray-800 text-right"><?php echo htmlspecialchars(t('price')); ?></th>
                    <th class="py-4 font-bold text-gray-800 text-right"><?php echo htmlspecialchars(t('subtotal_col')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="py-4 text-gray-700"><?php echo htmlspecialchars($item['name'] ?: 'Unknown Product'); ?></td>
                    <td class="py-4 text-gray-700 text-center"><?php echo $item['quantity']; ?></td>
                    <td class="py-4 text-gray-700 text-right"><?php echo number_format($item['price_at_purchase'], 2); ?> SAR</td>
                    <td class="py-4 font-semibold text-gray-800 text-right"><?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?> SAR</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end">
            <div class="w-64 space-y-3">
                <div class="flex justify-between text-gray-600">
                    <span><?php echo htmlspecialchars(t('subtotal')); ?></span>
                    <span><?php echo number_format($order['total_amount'], 2); ?> SAR</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span><?php echo htmlspecialchars(t('shipping')); ?></span>
                    <span>0.00 SAR</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span><?php echo htmlspecialchars(t('vat')); ?></span>
                    <span>0.00 SAR</span>
                </div>
                <div class="flex justify-between border-t pt-3 text-xl font-bold text-[#1B5E20]">
                    <span><?php echo htmlspecialchars(t('total')); ?></span>
                    <span><?php echo number_format($order['total_amount'], 2); ?> SAR</span>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="mt-16 pt-8 border-t text-center">
            <?php if (!empty($order['admin_note'])): ?>
                <div class="mb-6 p-4 bg-gray-50 rounded-lg text-left">
                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Note from Fresh-Factor</h4>
                    <p class="text-sm text-gray-600 italic">"<?php echo htmlspecialchars($order['admin_note']); ?>"</p>
                </div>
            <?php endif; ?>
            <p class="text-gray-500 text-sm"><?php echo htmlspecialchars(t('order_success_subtitle')); ?></p>
            <p class="text-xs text-gray-400 mt-2">This is a computer-generated receipt.</p>
        </div>
        
        <!-- Signature Area for Print -->
        <div class="mt-12 hidden print:block">
            <div class="flex justify-between items-end">
                <div class="text-center">
                    <div class="w-48 border-b border-gray-300 mb-2"></div>
                    <p class="text-xs text-gray-500 tracking-widest uppercase">Customer Signature</p>
                </div>
                <div class="text-center">
                    <div class="w-48 border-b border-gray-300 mb-2"></div>
                    <p class="text-xs text-gray-500 tracking-widest uppercase">Authorized Signature</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
