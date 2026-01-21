<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ad_id'])) {
    $ad_id = intval($_POST['ad_id']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE advertisements SET click_count = click_count + 1 WHERE ad_id = ?");
    $stmt->bind_param("i", $ad_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Click count updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>