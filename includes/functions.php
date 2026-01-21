<?php 
require_once __DIR__ . '/../config/database.php';


#Register User
function registerUser($username, $email, $password, $full_name, $phone, $user_type = 'buyer') {
    $conn = getDBConnection();
    
    // Password hash 
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, phone, user_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password_hash, $full_name, $phone, $user_type);
    
    $result = $stmt->execute();
    $user_id = $conn->insert_id;
    
    $stmt->close();
    $conn->close();
    
    return $result ? $user_id : false;
}

#Login User
function loginUser($email, $password) {
    $conn = getDBConnection();
    
    // Using Email find user
    $stmt = $conn->prepare("SELECT user_id, username, password_hash, user_type, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {  // User found
        $user = $result->fetch_assoc();
        
        // Account activtion check
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is not activated.'];
        }
        
        // Password correct  verify
        if (password_verify($password, $user['password_hash'])) {
            // Session  data save 
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            return ['success' => true, 'user_type' => $user['user_type']];
        }
    }
    
    return ['success' => false, 'message' => 'Password Or Email is incorrect.'];
}

#Get Advertisment
function getAdvertisement($ad_id) {
    $conn = getDBConnection();
    
    $query = "SELECT a.*, s.business_name, s.business_phone, s.business_email, s.website_url, u.full_name 
              FROM advertisements a 
              INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
              INNER JOIN users u ON s.user_id = u.user_id 
              WHERE a.ad_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ad = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $ad;
}

#Get All Advertisements(Filter Option)
function getAllAdvertisements($filters = []) {
    $conn = getDBConnection();
    
    $query = "SELECT a.*, s.business_name, s.logo_image 
              FROM advertisements a 
              INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
              WHERE a.status = 'approved'";
    
    $params = [];
    $types = '';
    
    if (!empty($filters['category'])) {
        $query .= " AND a.category = ?";
        $params[] = $filters['category'];
        $types .= 's';
    }
    
    if (!empty($filters['location'])) {
        $query .= " AND a.location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
        $types .= 's';
    }
    
    $query .= " ORDER BY a.created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ads = [];
    while ($row = $result->fetch_assoc()) {
        $ads[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $ads;
}

//Format Currency
function formatPrice($price) {
    return 'LKR ' . number_format($price, 2);
}

#Time ago
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just Now';
    if ($diff < 3600) return floor($diff / 60) . ' Before Minutes';
    if ($diff < 86400) return floor($diff / 3600) . ' Before Hours';
    if ($diff < 604800) return floor($diff / 86400) . ' Before Days';
    
    return date('Y-m-d', $time);
}

?>