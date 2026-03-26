<?php
require_once 'includes/db_connect.php';

// Update products to use actual existing images
$updates = [
    ['id' => 1, 'image' => 'assets/products/1772948243_0_fresh and organic.jpg', 'short_desc' => 'Fresh organic lettuce harvested daily from local farms. Perfect for salads and sandwiches.'],
    ['id' => 2, 'image' => 'assets/products/1772948243_1_fresh spanich.jpg', 'short_desc' => 'Nutrient-rich fresh spinach, perfect for healthy meals and smoothies.'],
];

foreach ($updates as $u) {
    $stmt = $pdo->prepare("UPDATE products SET image_url = ?, short_description = ? WHERE id = ?");
    $stmt->execute([$u['image'], $u['short_desc'], $u['id']]);
    echo "Updated product {$u['id']}<br>";
}

echo "<h2>Done!</h2>";
echo "<p><a href='index.php'>View Homepage</a></p>";
?>
