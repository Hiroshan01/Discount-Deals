<?php
// api/filter-deals.php - AJAX filter deals
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = isset($_POST['category']) ? sanitizeInput($_POST['category']) : '';
    $location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
    $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
    $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 999999;
    $sort_by = isset($_POST['sort_by']) ? sanitizeInput($_POST['sort_by']) : 'latest';
    
    $conn = getDBConnection();
    
    $query = "SELECT a.*, s.business_name, s.logo_image 
              FROM advertisements a 
              INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
              WHERE a.status = 'approved'";
    
    $params = [];
    $types = '';
    
    if ($category) {
        $query .= " AND a.category = ?";
        $params[] = $category;
        $types .= 's';
    }
    
    if ($location) {
        $query .= " AND a.location LIKE ?";
        $params[] = '%' . $location . '%';
        $types .= 's';
    }
    
    if ($min_price > 0 || $max_price < 999999) {
        $query .= " AND a.discounted_price BETWEEN ? AND ?";
        $params[] = $min_price;
        $params[] = $max_price;
        $types .= 'dd';
    }
    
    // Sorting
    switch ($sort_by) {
        case 'price_low':
            $query .= " ORDER BY a.discounted_price ASC";
            break;
        case 'price_high':
            $query .= " ORDER BY a.discounted_price DESC";
            break;
        case 'discount':
            $query .= " ORDER BY (a.original_price - a.discounted_price) DESC";
            break;
        default:
            $query .= " ORDER BY a.created_at DESC";
    }
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $deals = [];
    while ($row = $result->fetch_assoc()) {
        $deals[] = [
            'ad_id' => $row['ad_id'],
            'title' => $row['title'],
            'category' => $row['category'],
            'business_name' => $row['business_name'],
            'original_price' => $row['original_price'],
            'discounted_price' => $row['discounted_price'],
            'discount_percentage' => round($row['discount_percentage']),
            'image_path' => $row['image_path'],
            'location' => $row['location'],
            'quantity_available' => $row['quantity_available']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'deals' => $deals,
        'count' => count($deals)
    ]);
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
}
?>