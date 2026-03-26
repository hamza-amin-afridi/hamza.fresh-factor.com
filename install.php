<?php
/**
 * Database Installer - Run this once to create all tables
 * Access: http://localhost/Fruitable/ChatGpt/hamza.fresh-factor.com/install.php
 */

require_once 'includes/db_connect.php';

echo "<h1>Fresh-Factor Database Installer</h1>";
echo "<pre>";

try {
    // Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(191) UNIQUE NOT NULL,
        phone_number VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        profile_image VARCHAR(255) DEFAULT 'default_user.png',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'blocked') DEFAULT 'active'
    )");
    echo "✓ Users table created\n";

    // Admins Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        profile_image VARCHAR(255) DEFAULT 'default_admin.png',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Admins table created\n";

    // Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        status ENUM('active', 'inactive') DEFAULT 'active'
    )");
    echo "✓ Categories table created\n";

    // Farms Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS farms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        max_products INT DEFAULT 9,
        status ENUM('active', 'inactive') DEFAULT 'active'
    )");
    echo "✓ Farms table created\n";

    // Products Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        farm_id INT NULL,
        name VARCHAR(255) NOT NULL,
        short_description VARCHAR(150),
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        image_url VARCHAR(255),
        stock_status ENUM('in_stock', 'out_of_stock') DEFAULT 'in_stock',
        active_status ENUM('active', 'inactive') DEFAULT 'active',
        is_kit BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL
    )");
    echo "✓ Products table created\n";
    
    // Add short_description column if it doesn't exist (for existing installations)
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN short_description VARCHAR(150) AFTER name");
        echo "✓ Added short_description column to existing products table\n";
    } catch (PDOException $e) {
        // Column probably already exists
    }

    // Orders Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        shipping_address TEXT,
        admin_note TEXT,
        customer_name VARCHAR(255),
        customer_phone VARCHAR(20),
        customer_email VARCHAR(255),
        customer_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Orders table created\n";

    // Product Images Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    echo "✓ Product images table created\n";

    // Order Items Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        price_at_purchase DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    )");
    echo "✓ Order items table created\n";

    // Insert Initial Data
    $pdo->exec("INSERT IGNORE INTO categories (id, name) VALUES 
        (1, 'Fresh and Organic'), 
        (2, 'Fresh Grow'), 
        (3, 'Fresh Indoor')");
    echo "✓ Categories inserted\n";

    $pdo->exec("INSERT IGNORE INTO farms (id, name, description) VALUES 
        (1, 'Dirab Farm', 'Located in the heart of Riyadh, Dirab Farm specializes in organic leafy greens and seasonal vegetables using traditional sustainable methods.'),
        (2, 'Hannan Farm', 'A premium organic farm known for its diverse range of fresh produce and commitment to zero-pesticide farming.')");
    echo "✓ Farms inserted\n";

    // Insert Default Admin (password: Hamza@123)
    $stmt = $pdo->prepare("INSERT IGNORE INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute(['hamzaamin', password_hash('Hamza@123', PASSWORD_DEFAULT)]);
    echo "✓ Default admin created (username: hamzaamin, password: Hamza@123)\n";

    // Insert Sample Products
    $pdo->exec("INSERT IGNORE INTO products (category_id, farm_id, name, description, price, image_url, stock_status) VALUES 
        (1, 1, 'Organic Lettuce', 'Crisp and fresh organic lettuce harvested daily from Dirab Farm.', 12.00, 'assets/products/lettuce.jpg', 'in_stock'),
        (1, 1, 'Fresh Spinach', 'Nutrient-rich organic spinach leaves, perfect for salads and cooking.', 8.50, 'assets/products/spinach.jpg', 'in_stock'),
        (1, 1, 'Organic Tomatoes', 'Vine-ripened organic tomatoes with superior flavor.', 15.00, 'assets/products/tomatoes.jpg', 'in_stock'),
        (1, 2, 'Organic Cucumbers', 'Crunchy and hydrating cucumbers from Hannan Farm.', 10.00, 'assets/products/cucumbers.jpg', 'in_stock'),
        (1, 2, 'Bell Peppers Mix', 'Colorful mix of organic red, yellow, and green bell peppers.', 18.00, 'assets/products/peppers.jpg', 'in_stock')");
    echo "✓ Sample products inserted\n";

    echo "\n========================================\n";
    echo "DATABASE SETUP COMPLETE!\n";
    echo "========================================\n\n";
    echo "You can now:\n";
    echo "- Access user website: <a href='index.php'>index.php</a>\n";
    echo "- Login as admin: username=hamzaamin, password=Hamza@123\n";
    echo "\nDELETE this install.php file after setup for security.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
