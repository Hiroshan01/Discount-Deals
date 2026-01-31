<?php
// seller/edit-ad.php - Edit Advertisement

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check login + role
if (!isLoggedIn() || !isSeller()) {
    redirect('auth/login.php');
    exit;
}

// Require ad id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('seller/manage-ads.php');
    exit;
}

$ad_id   = (int) $_GET['id'];
$user_id = (int) $_SESSION['user_id'];

$conn = getDBConnection();
if (!$conn) {
    $_SESSION['error'] = 'Database connection failed.';
    redirect('seller/manage-ads.php');
    exit;
}

// Get seller_id for current user
$stmt = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$seller) {
    $_SESSION['error'] = 'Seller profile not found.';
    $conn->close();
    redirect('seller/manage-ads.php');
    exit;
}

$seller_id = (int) $seller['seller_id'];

// Get advertisement (verify ownership)
$stmt = $conn->prepare("SELECT * FROM advertisements WHERE ad_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $ad_id, $seller_id);
$stmt->execute();
$ad = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ad) {
    $_SESSION['error'] = 'Advertisement not found or you do not have permission to edit it.';
    $conn->close();
    redirect('seller/manage-ads.php');
    exit;
}

$page_title = 'Edit Advertisement - Discount Deals';
$error   = '';
$success = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title              = sanitizeInput($_POST['title'] ?? '');
    $description        = sanitizeInput($_POST['description'] ?? '');
    $category           = sanitizeInput($_POST['category'] ?? '');
    $original_price     = isset($_POST['original_price']) ? (float) $_POST['original_price'] : 0;
    $discounted_price   = isset($_POST['discounted_price']) ? (float) $_POST['discounted_price'] : 0;
    $quantity_available = isset($_POST['quantity_available']) ? (int) $_POST['quantity_available'] : 0;
    $expiry_date        = sanitizeInput($_POST['expiry_date'] ?? '');
    $location           = sanitizeInput($_POST['location'] ?? '');
    $external_url       = sanitizeInput($_POST['external_url'] ?? '');
    $phone              = sanitizeInput($_POST['phone'] ?? '');
    $facebook_url       = sanitizeInput($_POST['facebook_url'] ?? '');
    $instagram_url      = sanitizeInput($_POST['instagram_url'] ?? '');

    // Basic required checks (you can relax this if needed)
    if ($title === '' || $description === '' || $category === '' || $original_price <= 0 || $discounted_price <= 0) {
        $error = 'Please fill all required fields correctly.';
    } else {
        // Image upload (optional)
        $image_path = $ad['image_path']; // Keep existing by default

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed  = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed, true)) {
                $new_filename = uniqid('ad_', true) . '.' . $ext;
                $upload_path  = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $new_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if exists
                    if (!empty($image_path)) {
                        $old_path = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $image_path;
                        if (file_exists($old_path)) {
                            @unlink($old_path);
                        }
                    }
                    $image_path = $new_filename;
                } else {
                    $error = 'Image upload failed. Please try again.';
                }
            } else {
                $error = 'Invalid image type. Allowed: jpg, jpeg, png, gif.';
            }
        }

        // If no errors so far, run update
        if ($error === '') {
            $update_query = "UPDATE advertisements SET
                                title = ?,
                                description = ?,
                                category = ?,
                                original_price = ?,
                                discounted_price = ?,
                                quantity_available = ?,
                                expiry_date = ?,
                                location = ?,
                                phone = ?,
                                external_url = ?,
                                image_path = ?,
                                facebook_url = ?,
                                instagram_url = ?,
                                status = 'pending'
                             WHERE ad_id = ? AND seller_id = ?";

            $stmt = $conn->prepare($update_query);
            if ($stmt) {
                $stmt->bind_param(
                    "sssddisssssssii",
                    $title,
                    $description,
                    $category,
                    $original_price,
                    $discounted_price,
                    $quantity_available,
                    $expiry_date,
                    $location,
                    $phone,
                    $external_url,
                    $image_path,
                    $facebook_url,
                    $instagram_url,
                    $ad_id,
                    $seller_id
                );

                if ($stmt->execute()) {
                    $success = 'Advertisement updated successfully! Waiting for admin approval.';
                } else {
                    $error = 'An error occurred while updating the advertisement.';
                }
                $stmt->close();
            } else {
                $error = 'Failed to prepare update statement.';
            }

            // Refresh advertisement data if update went through
            if ($error === '') {
                $stmt = $conn->prepare("SELECT * FROM advertisements WHERE ad_id = ?");
                $stmt->bind_param("i", $ad_id);
                $stmt->execute();
                $ad = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
        }
    }
}

$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Advertisement</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                        <a href="manage-ads.php">View Advertisements</a>
                    </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required
                                value="<?php echo htmlspecialchars($ad['title']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="4"
                                required><?php echo htmlspecialchars($ad['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="meals" <?php echo $ad['category'] === 'meals' ? 'selected' : ''; ?>>
                                        Meals</option>
                                    <option value="bakery"
                                        <?php echo $ad['category'] === 'bakery' ? 'selected' : ''; ?>>Bakery</option>
                                    <option value="beverages"
                                        <?php echo $ad['category'] === 'beverages' ? 'selected' : ''; ?>>Beverages
                                    </option>
                                    <option value="desserts"
                                        <?php echo $ad['category'] === 'desserts' ? 'selected' : ''; ?>>Desserts
                                    </option>
                                    <option value="snacks"
                                        <?php echo $ad['category'] === 'snacks' ? 'selected' : ''; ?>>Snacks</option>
                                    <option value="other" <?php echo $ad['category'] === 'other' ? 'selected' : ''; ?>>
                                        Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity_available" class="form-control"
                                    value="<?php echo (int) $ad['quantity_available']; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Original Price (LKR) *</label>
                                <input type="number" name="original_price" class="form-control" step="0.01" required
                                    value="<?php echo htmlspecialchars($ad['original_price']); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discounted Price (LKR) *</label>
                                <input type="number" name="discounted_price" class="form-control" step="0.01" required
                                    value="<?php echo htmlspecialchars($ad['discounted_price']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control"
                                    value="<?php echo htmlspecialchars($ad['expiry_date']); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control"
                                    value="<?php echo htmlspecialchars($ad['location']); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control"
                                value="<?php echo htmlspecialchars($ad['phone']); ?>" placeholder="077-1234567">
                            <small class="text-muted">For contacting seller</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Website / Order Page (URL)</label>
                            <input type="url" name="external_url" class="form-control"
                                value="<?php echo htmlspecialchars($ad['external_url']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control"
                                value="<?php echo htmlspecialchars($ad['facebook_url'] ?? ''); ?>"
                                placeholder="https://www.facebook.com/yourpage">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control"
                                value="<?php echo htmlspecialchars($ad['instagram_url'] ?? ''); ?>"
                                placeholder="https://www.instagram.com/yourprofile">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <?php if (!empty($ad['image_path'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo asset_url('images/uploads/' . $ad['image_path']); ?>"
                                    class="img-thumbnail" style="max-width: 200px;" alt="Current advertisement image">
                                <p class="small text-muted">Selecting a new image will replace the current one.</p>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="manage-ads.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>