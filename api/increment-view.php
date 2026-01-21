<?php
// api/increment-view.php - View count එක වැඩි කරන්න
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ad_id'])) {
    $ad_id = intval($_POST['ad_id']);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $conn = getDBConnection();

    $check_query = "SELECT COUNT(*) as count FROM ad_views 
                    WHERE ad_id = ? AND ip_address = ? 
                    AND viewed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("is", $ad_id, $ip_address);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] == 0) {
        // නව view
        $insert_query = "INSERT INTO ad_views (ad_id, ip_address) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("is", $ad_id, $ip_address);
        $stmt->execute();
        
        // View count 
        $update_query = "UPDATE advertisements SET view_count = view_count + 1 WHERE ad_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $ad_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'View recorded']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Already viewed recently']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>