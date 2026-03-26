<?php
require_once 'includes/db_connect.php';

echo "<h1>Debug Product Data</h1>";

// Check products
$stmt = $pdo->query("SELECT * FROM products LIMIT 2");
$products = $stmt->fetchAll();

echo "<h2>Products in Database:</h2><pre>";
foreach ($products as $p) {
    print_r($p);
    echo "\n---\n";
}
echo "</pre>";

// Check if images exist
echo "<h2>Image Files Check:</h2>";
$image_dir = 'assets/products/';
if (is_dir($image_dir)) {
    $files = scandir($image_dir);
    echo "<p>Directory: $image_dir</p><ul>";
    foreach ($files as $f) {
        if ($f != '.' && $f != '..') {
            $full_path = $image_dir . $f;
            echo "<li>$f - " . (file_exists($full_path) ? "EXISTS" : "NOT FOUND") . " - " . (is_readable($full_path) ? "READABLE" : "NOT READABLE") . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>Directory $image_dir does NOT exist!</p>";
}

// Test image path
echo "<h2>Image Path Test:</h2>";
if (!empty($products[0]['image_url'])) {
    $test_path = $products[0]['image_url'];
    echo "<p>Database value: $test_path</p>";
    echo "<p>File exists: " . (file_exists($test_path) ? "YES" : "NO") . "</p>";
    echo "<p>Full server path: " . realpath($test_path) . "</p>";
    echo "<p>Try to display: <br><img src='$test_path' style='max-width:200px;border:2px solid red'></p>";
}

// Check current working directory
echo "<h2>Server Info:</h2>";
echo "<p>Current directory: " . getcwd() . "</p>";
echo "<p>Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
?>
