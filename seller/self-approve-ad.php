<?php
// seller/self-approve-ad.php - Self Approve Advertisement
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

// Get seller profile (check if verified)
$stmt = $conn->prepare("SELECT seller_id, is_verified FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();

if (!$seller['is_verified']) {
    $_SESSION['error'] = 'Enter your BR or NIC number to approve your advertisements.';
    redirect('seller/verify-business.php');
}

$seller_id = $seller['seller_id'];

// Get advertisement (verify ownership and status)
$stmt = $conn->prepare("SELECT * FROM advertisements WHERE ad_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $ad_id, $seller_id);
$stmt->execute();
$ad = $stmt->get_result()->fetch_assoc();

if (!$ad) {
    $_SESSION['error'] = 'Advertisement not found or you do not have permission to approve it.';
    redirect('seller/manage-ads.php');
}

if ($ad['status'] != 'pending') {
    $_SESSION['error'] = 'This advertisement is already ' . $ad['status'] . '.';
    redirect('seller/manage-ads.php');
}

// Self-approve the advertisement
$stmt = $conn->prepare("UPDATE advertisements 
                         SET status = 'approved', 
                             self_approved = TRUE, 
                             approved_by = 'seller' 
                         WHERE ad_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $ad_id, $seller_id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Advertisement approved successfully! It is now live.';
} else {
    $_SESSION['error'] = 'An error occurred while approving.';
}

$stmt->close();
$conn->close();

redirect('seller/manage-ads.php');
?>