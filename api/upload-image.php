<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    // Error checking
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred during upload.'
        ]);
        exit;
    }
    
    // File size checking
    if ($file['size'] > MAX_FILE_SIZE) {
        echo json_encode([
            'success' => false,
            'message' => 'File is too large. Maximum 5MB.'
        ]);
        exit;
    }
    
    // File extension checking
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type. Only JPG, PNG, GIF are allowed.'
        ]);
        exit;
    }
    
    // Generate unique filename
    $new_filename = uniqid('img_', true) . '.' . $file_ext;
    $upload_path = UPLOAD_DIR . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Optional: Create thumbnail
        // createThumbnail($upload_path, 300, 300);
        
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'filename' => $new_filename,
            'url' => asset_url('images/uploads/' . $new_filename)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while saving the image.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
}

// Function to create thumbnail (optional)
function createThumbnail($source, $width, $height) {
    $image_info = getimagesize($source);
    $image_type = $image_info[2];
    
    // Load source image
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    // Original dimensions
    $orig_width = imagesx($image);
    $orig_height = imagesy($image);
    
    // Resize while maintaining aspect ratio
    $ratio = min($width / $orig_width, $height / $orig_height);
    $new_width = $orig_width * $ratio;
    $new_height = $orig_height * $ratio;
    
    // Create thumbnail
    $thumbnail = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, 
                       $new_width, $new_height, $orig_width, $orig_height);
    
    // Save thumbnail
    $thumbnail_path = str_replace('.', '_thumb.', $source);
    
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail, $thumbnail_path, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail, $thumbnail_path, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbnail, $thumbnail_path);
            break;
    }
    
    // Cleanup
    imagedestroy($image);
    imagedestroy($thumbnail);
    
    return true;
}
?>