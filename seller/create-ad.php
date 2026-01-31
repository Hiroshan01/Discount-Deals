<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('auth/login.php');
}

$page_title = 'Create New Advertisement - Discount Deals';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get seller_id
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();
$seller_id = $seller['seller_id'];
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    $original_price = floatval($_POST['original_price']);
    $discounted_price = floatval($_POST['discounted_price']);
    $quantity_available = intval($_POST['quantity_available']);
    $expiry_date = sanitizeInput($_POST['expiry_date']);
    $location = sanitizeInput($_POST['location']);
    $external_url = sanitizeInput($_POST['external_url']);
    $phone = sanitizeInput($_POST['phone']);
    $facebook_url = sanitizeInput($_POST['facebook_url']);
    $instagram_url = sanitizeInput($_POST['instagram_url']);
    
    // Image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = UPLOAD_DIR . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            }
        }
    }
    
    // Insert advertisement
    $insert_query = "INSERT INTO advertisements 
                     (seller_id, title, description, category, original_price, 
                      discounted_price, quantity_available, expiry_date, location, 
                      phone, external_url, facebook_url, instagram_url,image_path) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
    
    $stmt = $conn->prepare($insert_query);
    // Type string: i=seller_id, s=title, s=description, s=category, 
    // d=original_price, d=discounted_price, i=quantity_available, 
    // s=expiry_date, s=location, s=phone, s=external_url, s=image_path
    $stmt->bind_param("isssddisssssss", $seller_id, $title, $description, 
                      $category, $original_price, $discounted_price, 
                      $quantity_available, $expiry_date, $location, 
                      $phone, $external_url, $facebook_url, $instagram_url, $image_path);
    
    if ($stmt->execute()) {
        $success = 'Advertisement created successfully! Please wait for admin approval.';
    } else {
        $error = 'An error occurred while creating the advertisement.';
    }
    
    $stmt->close();
}

$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-plus"></i> Create new advertisement</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <a href="manage-ads.php">View advertisements</a>
                    </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required
                                placeholder="e.g.: Bread 50% OFF">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="4" required
                                placeholder="Write a full description about your product"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select...</option>
                                    <option value="meals">Meals</option>
                                    <option value="bakery">Bakery</option>
                                    <option value="beverages">Beverages</option>
                                    <option value="desserts">Desserts</option>
                                    <option value="snacks">Snacks</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity_available" class="form-control"
                                    placeholder="Available quantity">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Original price (LKR) *</label>
                                <input type="number" name="original_price" class="form-control" step="0.01" required
                                    placeholder="1000.00">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discounted price (LKR) *</label>
                                <input type="number" name="discounted_price" class="form-control" step="0.01" required
                                    placeholder="500.00">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry date</label>
                                <input type="date" name="expiry_date" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g.: Colombo 03">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" placeholder="07X-XXXXXXXXX">
                                <small class="text-muted">For contacting Seller</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Your website / order page (URL)</label>
                                <input type="url" name="external_url" class="form-control"
                                    placeholder="https://example.com/order">
                                <small class="text-muted">A link where customers can order or contact you</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Facebook Page</label>
                                <input type="url" name="facebook_url" class="form-control"
                                    placeholder="https://facebook.com/yourpage">
                                <small class="text-muted">Your business Facebook page</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instagram</label>
                                <input type="url" name="instagram_url" class="form-control"
                                    placeholder="https://instagram.com/yourpage">
                                <small class="text-muted">Your Instagram business page</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">JPG, PNG, or GIF (max 5MB)</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Create advertisement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>