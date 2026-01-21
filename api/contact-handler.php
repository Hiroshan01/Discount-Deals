<?php
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_id = isset($_POST['ad_id']) ? intval($_POST['ad_id']) : 0;
    $sender_name = sanitizeInput($_POST['sender_name'] ?? '');
    $sender_email = sanitizeInput($_POST['sender_email'] ?? '');
    $sender_phone = sanitizeInput($_POST['sender_phone'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    // Validation
    if (empty($ad_id) || empty($sender_name) || empty($sender_email) || empty($message)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }
    
    if (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Please enter a valid email address.'
        ]);
        exit;
    }
    
    $conn = getDBConnection();
    
    // Insert into database
    $stmt = $conn->prepare(
        "INSERT INTO contact_messages (ad_id, sender_name, sender_email, sender_phone, message) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $ad_id, $sender_name, $sender_email, $sender_phone, $message);
    
    if ($stmt->execute()) {
        // Send email to seller (optional - requires email configuration)
        // sendEmailToSeller($ad_id, $sender_name, $sender_email, $message);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Your message has been sent successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred while sending the message.'
        ]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request.'
    ]);
}

// Email sending function (optional)
function sendEmailToSeller($ad_id, $sender_name, $sender_email, $message) {
    // Get seller's email from advertisement
    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "SELECT s.business_email, s.business_name, a.title 
         FROM advertisements a 
         INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
         WHERE a.ad_id = ?"
    );
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && !empty($result['business_email'])) {
        $to = $result['business_email'];
        $subject = "New Message - " . $result['title'];
        $body = "Dear {$result['business_name']},\n\n";
        $body .= "You have received a new message from {$sender_name} ({$sender_email}):\n\n";
        $body .= "{$message}\n\n";
        $body .= "Thank you,\nDiscount Deals Team";
        
        $headers = "From: noreply@discountdeals.lk\r\n";
        $headers .= "Reply-To: {$sender_email}\r\n";
        
        // Use PHP mail() function
        // mail($to, $subject, $body, $headers);
    }
    
    $stmt->close();
    $conn->close();
}
?>