<?php
define('baseurl',  'http://localhost/discount-deals/');
define('asseturl', 'http://localhost/discount-deals/assets/');
define('BASE_URL', 'http://localhost:8000/');

// Site settings
define('SITE_NAME', 'Discount Deals');
define('SITE_URL', 'http://localhost/discount-deals/');
define('SITE_EMAIL', 'info@discountdeals.lk');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../assets/images/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination settings
define('ITEMS_PER_PAGE', 12);

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone set 
date_default_timezone_set('Asia/Colombo');

// Error reporting (development mode)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper functions
function base_url($path = '') {
    return BASE_URL . $path;
}

function asset_url($path = '') {
    return BASE_URL . 'assets/' . $path;
}

function redirect($url) {
    header("Location: " . base_url($url));
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function isSeller() {
    return getUserType() === 'seller';
}

function isAdmin() {
    return getUserType() === 'admin';
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>