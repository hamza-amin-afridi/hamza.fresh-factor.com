<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $is_local = (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'], true));
    if ($is_local) {
        die("Database error: " . htmlspecialchars($e->getMessage()) . " (Code: " . (int)$e->getCode() . ")");
    }
    die("A temporary server error occurred. Please try again later.");
}

// Global functions
function redirect($url) {
    header("Location: $url");
    exit();
}

// Session helpers
function ensure_session_started() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Language / i18n
function current_lang() {
    return 'en';
}

function is_rtl() {
    return false;
}

function html_lang_attr() {
    return 'en';
}

function html_dir_attr() {
    return 'ltr';
}

function t($key) {
    $lang = 'en';

    static $dict = [
        'en' => [
            'home' => 'Home',
            'about_us' => 'About Us',
            'products' => 'Products',
            'categories' => 'Categories',
            'cart' => 'Cart',
            'help' => 'Help',
            'sign_in' => 'Sign In',
            'sign_up' => 'Sign Up',
            'logout' => 'Logout',
            'language' => 'Language',
            'add_to_cart' => 'Add to Cart',
            'out_of_stock' => 'Out of Stock',
            'quantity' => 'Quantity:',
            'search_products_placeholder' => 'Search products...',
            'all' => 'All',
            'our_products' => 'Our Products',
            'shop_by_category' => 'Shop by Category',
            'discover_categories_subtitle' => 'Discover our wide variety of fresh, organic products and gardening solutions.',
            'fresh_grow_kits' => 'Fresh Grow Kits',
            'fresh_grow_kits_subtitle' => 'Complete solutions for your home gardening journey.',
            'fresh_indoor_plants' => 'Fresh Indoor Plants',
            'fresh_indoor_plants_subtitle' => 'Bring nature into your home with our handpicked indoor plants.',
            'shopping_cart' => 'Shopping Cart',
            'cart_empty_title' => 'Your cart is empty',
            'cart_empty_subtitle' => "Looks like you haven't added anything yet.",
            'start_shopping' => 'Start Shopping',
            'checkout' => 'Checkout',
            'shipping_information' => 'Shipping Information',
            'help_center_title' => 'How can we help you?',
            'delivery_info' => 'Delivery Info',
            'delivery_info_desc' => 'Track your orders and view shipping policies.',
            'faqs' => 'FAQs',
            'faqs_desc' => 'Find answers to common questions.',
            'order_success_title' => 'Order Placed Successfully!',
            'order_success_subtitle' => 'Thank you for your order. We will contact you shortly to confirm delivery details.',
            'order_details' => 'Order Details',
            'order_items' => 'Order Items',
            'total' => 'Total',
            'continue_shopping' => 'Continue Shopping',
            'view_my_orders' => 'View My Orders',
            'print_receipt' => 'Print Receipt',
            'back' => 'Back',
            'subtotal' => 'Subtotal',
            'shipping' => 'Shipping',
            'vat' => 'VAT (0%)',
            'receipt' => 'RECEIPT',
            'billed_to' => 'Billed To',
            'shipping_address' => 'Shipping Address',
            'product_name' => 'Product Name',
            'qty' => 'Qty',
            'price' => 'Price',
            'subtotal_col' => 'Subtotal',

            'added_to_cart' => 'Added to cart!',
            'invalid_session_token_refresh' => 'Invalid session token. Please refresh the page and try again.',
            'product_out_of_stock' => 'Product is out of stock!',

            'hero_title' => 'Pure. Fresh. Organic.',
            'hero_subtitle' => "Premium farm-fresh organic products and indoor greenery delivered to your doorstep in Saudi Arabia.",
            'shop_now' => 'Shop Now',
            'explore_categories' => 'Explore Categories',

            'homepage_categories_title' => 'Shop by Category',
            'homepage_categories_subtitle' => 'Explore our wide range of organic and fresh products.',
            'view_all_categories' => 'View All Categories',
            'category_label' => 'Category',
            'explore_farms' => 'Explore Farms',
            'view_kits' => 'View Kits',
            'shop_indoor' => 'Shop Indoor',

            'our_collection' => 'Our Collection',
            'featured_products' => 'Our Featured Products',
            'featured_products_subtitle' => 'Handpicked organic goodness from our local farms, delivered straight to your doorstep.',
            'explore_all_products' => 'Explore All Products',

            'order_summary' => 'Order Summary',
            'place_order' => 'Place Order',
            'back_to_cart' => 'Back to Cart',

            'full_name' => 'Full Name',
            'phone_number' => 'Phone Number',
            'email' => 'Email',
            'optional' => 'Optional',
            'order_message' => 'Order Message',
            'any_special_instructions' => 'Any special instructions...',
            'payment_method' => 'Payment Method',
            'cash_on_delivery' => 'Cash on Delivery',

            'about_section_title' => 'About Fresh-Factor',
            'years_of_purity' => 'Years of Purity',
            'about_intro' => "At Fresh-Factor, we believe everyone deserves access to the purest, most nutrient-rich produce nature has to offer. Our journey began with a simple mission: to bridge the gap between local organic farms and your dinner table.",
            'certified_organic_title' => '100% Certified Organic',
            'certified_organic_desc' => 'Every product is sourced from farms that strictly follow organic cultivation practices.',
            'sustainable_farming_title' => 'Sustainable Farming',
            'sustainable_farming_desc' => 'We support regenerative agriculture to protect our soil and environment for future generations.',
            'farm_to_door_title' => 'Farm-to-Door in 24h',
            'farm_to_door_desc' => 'Harvested at peak ripeness and delivered to your doorstep within 24 hours to ensure maximum freshness.',
            'partner_farms' => 'Partner Farms',
            'happy_customers' => 'Happy Customers',
            'our_philosophy' => 'Our Philosophy',
            'philosophy_tagline' => 'Growing a Greener Future, One Home at a Time.',
            'our_vision' => 'Our Vision',
            'our_vision_desc' => "To become Saudi Arabia's most trusted destination for organic living, where every household has easy access to healthy produce and vibrant indoor greenery.",
            'our_mission' => 'Our Mission',
            'our_mission_desc' => 'To empower local farmers, promote biodiversity, and deliver uncompromising quality to our customers through an innovative and sustainable supply chain.',
            'footer_tagline' => 'Your premium destination for organic farm products in Saudi Arabia.',

            'simple_steps' => 'Simple steps',
            'how_it_works' => 'How it works',
            'how_it_works_subtitle' => 'From browsing to delivery in three easy steps.',

            'step1_title' => 'Browse & add to cart',
            'step1_desc' => 'Explore our organic produce, grow kits, and indoor plants. Add what you need to your cart.',
            'step2_title' => 'Checkout securely',
            'step2_desc' => 'Enter your delivery details. Pay on delivery (cash) for a hassle-free experience.',
            'step3_title' => 'Receive at your door',
            'step3_desc' => 'We deliver across Saudi Arabia. Fresh, packed with care, right to you.',

            'badge_organic_title' => '100% Organic',
            'badge_organic_desc' => 'Strictly farm-to-table',
            'badge_delivery_title' => 'Fast Delivery',
            'badge_delivery_desc' => 'Across Saudi Arabia',
            'badge_payment_title' => 'Secure Payment',
            'badge_payment_desc' => 'Encrypted transactions',
            'badge_support_title' => '24/7 Support',
            'badge_support_desc' => 'Dedicated help center',

            'stats_happy_customers' => 'Happy Customers',
            'stats_organic_products' => 'Organic Products',
            'stats_partner_farms' => 'Partner Farms',
            'stats_satisfaction_rate' => 'Satisfaction Rate',

            'our_partners' => 'Our Partners',
            'trusted_local_farms' => 'Trusted Local Farms',
            'trusted_local_farms_subtitle' => 'We partner with the best organic farms in Saudi Arabia to bring you the freshest produce.',
            'explore_products' => 'Explore Products',

            'dirab_farm_desc' => 'Located in the heart of Riyadh, Dirab Farm specializes in organic leafy greens and seasonal vegetables using traditional sustainable methods.',
            'hannan_farm_desc' => 'A premium organic farm known for its diverse range of fresh produce and commitment to zero-pesticide farming.',

            'testimonials' => 'Testimonials',
            'what_customers_say' => 'What Our Customers Say',
            'testimonial_1' => "The freshest vegetables I've ever ordered online! The delivery was quick and the produce was still crisp and full of flavor.",
            'testimonial_2' => 'Fresh-Factor has become my go-to for organic groceries. Their grow kits helped me start my own herb garden at home!',
            'testimonial_3' => 'Excellent quality indoor plants. The Peace Lily I ordered arrived healthy and beautifully packaged. Highly recommend!',

            'faq' => 'FAQ',
            'frequently_asked_questions' => 'Frequently Asked Questions',
            'faq_q1' => 'How fresh are your products?',
            'faq_a1' => 'Our products are harvested daily from our partner farms and delivered within 24 hours to ensure maximum freshness and nutritional value.',
            'faq_q2' => 'Do you offer same-day delivery?',
            'faq_a2' => 'Yes! For orders placed before 2 PM in Riyadh, we offer same-day delivery. For other cities, delivery typically takes 1-2 business days.',
            'faq_q3' => 'What payment methods do you accept?',
            'faq_a3' => 'We accept Cash on Delivery (COD), credit/debit cards, Apple Pay, and STC Pay for your convenience.',
            'faq_q4' => 'Can I return products if not satisfied?',
            'faq_a4' => "Absolutely! We have a 100% satisfaction guarantee. If you're not happy with any product, contact us within 24 hours of delivery for a full refund or replacement.",
            'faq_q5' => 'Are your products certified organic?',
            'faq_a5' => 'Yes! All our farm products are certified organic by Saudi Organic Farming Authority. We maintain strict quality standards and regular farm inspections.',

            'join_community' => 'Join Our Community',
            'community_subtitle' => 'Follow us on social media for daily tips, recipes, and exclusive offers!',

            'ready_to_experience_freshness' => 'Ready to Experience Freshness?',
            'cta_subtitle' => 'Join thousands of satisfied customers who have made the switch to healthier, organic living. First-time customers get 10% off their first order!',
            'create_account' => 'Create Account',

            'quick_links' => 'Quick Links',
            'help_center' => 'Help Center',
            'contact_us' => 'Contact Us',
            'newsletter' => 'Newsletter',
            'enter_your_email' => 'Enter your email',
            'subscribe' => 'Subscribe',
            'footer_about_long' => 'Your premium destination for organic farm products and indoor plants in Saudi Arabia. We believe in purity and sustainability.',
        ],
    ];

    if (isset($dict[$lang][$key])) return $dict[$lang][$key];
    if (isset($dict['en'][$key])) return $dict['en'][$key];
    return $key;
}

function lang_switch_url($targetLang) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '';
    $qs = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
    return $path . $qs;
}

function checkAdmin() {
    ensure_session_started();
    if (!isset($_SESSION['admin_id'])) {
        redirect('login.php');
    }
}

function checkUser() {
    ensure_session_started();
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// CSRF helpers
function generate_csrf_token() {
    ensure_session_started();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    ensure_session_started();
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
