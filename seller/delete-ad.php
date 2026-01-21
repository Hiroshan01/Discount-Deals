<?php
// seller/delete-ad.php - Delete Advertisement
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('auth/login.php');
}

if (!isset($_GET['id'])) {
    redirect('seller/manage-ads.php');
}

$ad_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$conn = getDBConnection();

// Get seller_id
$stmt = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();
$seller_id = $seller['seller_id'];

// Get advertisement (verify ownership)
$stmt = $conn->prepare("SELECT * FROM advertisements WHERE ad_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $ad_id, $seller_id);
$stmt->execute();
$ad = $stmt->get_result()->fetch_assoc();

if (!$ad) {
    $_SESSION['error'] = 'Advertisement not found or you do not have permission to delete it.';
    redirect('seller/manage-ads.php');
}

// Delete image file if exists
if ($ad['image_path'] && file_exists(UPLOAD_DIR . $ad['image_path'])) {
    unlink(UPLOAD_DIR . $ad['image_path']);
}

// Delete advertisement
$stmt = $conn->prepare("DELETE FROM advertisements WHERE ad_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $ad_id, $seller_id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Advertisement deleted successfully.';
} else {
    $_SESSION['error'] = 'An error occurred while deleting the advertisement.';
}

$stmt->close();
$conn->close();

redirect('seller/manage-ads.php');
?>