<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$page_title = 'Manage Advertisements - Admin';

// Filters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$conn = getDBConnection();

// Build query
$query = "SELECT a.*, s.business_name, u.full_name 
          FROM advertisements a 
          INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
          INNER JOIN users u ON s.user_id = u.user_id 
          WHERE 1=1";

$params = [];
$types = '';

if ($status_filter) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($category_filter) {
    $query .= " AND a.category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

if ($search) {
    $query .= " AND (a.title LIKE ? OR a.description LIKE ? OR s.business_name LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$query .= " ORDER BY a.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$ads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle delete
if (isset($_GET['delete'])) {
    $ad_id = intval($_GET['delete']);
    
    // Get image path before deleting
    $ad_result = $conn->query("SELECT image_path FROM advertisements WHERE ad_id = $ad_id");
    $ad_data = $ad_result->fetch_assoc();
    
    // Delete image file
    if (!empty($ad_data['image_path']) && file_exists(UPLOAD_DIR . $ad_data['image_path'])) {
        unlink(UPLOAD_DIR . $ad_data['image_path']);
    }
    
    // Delete advertisement
    $stmt = $conn->prepare("DELETE FROM advertisements WHERE ad_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    
    // Log action
    $admin_id = $_SESSION['user_id'];
    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action_type, target_type, target_id, description) VALUES (?, 'delete', 'advertisement', ?, 'Advertisement deleted')");
    $log_stmt->bind_param("ii", $admin_id, $ad_id);
    $log_stmt->execute();
    
    $_SESSION['success'] = 'Advertisement has been deleted.';
    redirect('admin/manage-ads.php');
}

$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-ad"></i> Manage All Advertisements</h2>
            <p class="text-muted">View and manage all advertisements in the system</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>
                                    Pending</option>
                                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>
                                    Approved</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>
                                    Rejected</option>
                                <option value="expired" <?php echo $status_filter == 'expired' ? 'selected' : ''; ?>>
                                    Expired</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All</option>
                                <option value="meals" <?php echo $category_filter == 'meals' ? 'selected' : ''; ?>>Meals
                                </option>
                                <option value="bakery" <?php echo $category_filter == 'bakery' ? 'selected' : ''; ?>>
                                    Bakery</option>
                                <option value="beverages"
                                    <?php echo $category_filter == 'beverages' ? 'selected' : ''; ?>>Beverages</option>
                                <option value="desserts"
                                    <?php echo $category_filter == 'desserts' ? 'selected' : ''; ?>>Desserts</option>
                                <option value="snacks" <?php echo $category_filter == 'snacks' ? 'selected' : ''; ?>>
                                    Snacks</option>
                                <option value="other" <?php echo $category_filter == 'other' ? 'selected' : ''; ?>>Other
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by title, description or business name..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Advertisements Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Advertisements (<?php echo count($ads); ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Seller</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ads as $ad): ?>
                                <tr>
                                    <td><?php echo $ad['ad_id']; ?></td>
                                    <td>
                                        <?php if (!empty($ad['image_path'])): ?>
                                        <img src="<?php echo asset_url('images/uploads/' . $ad['image_path']); ?>"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ad['title']); ?></strong><br>
                                        <small
                                            class="text-muted"><?php echo date('Y-m-d', strtotime($ad['created_at'])); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($ad['business_name']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $ad['category']; ?></span></td>
                                    <td><?php echo formatPrice($ad['discounted_price']); ?></td>
                                    <td>
                                        <?php
                                            $status_badges = [
                                                'pending' => 'bg-warning',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'expired' => 'bg-secondary'
                                            ];
                                            ?>
                                        <span
                                            class="badge <?php echo $status_badges[$ad['status']] ?? 'bg-secondary'; ?>">
                                            <?php echo $ad['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-eye"></i> <?php echo $ad['view_count']; ?><br>
                                        <i class="fas fa-mouse-pointer"></i> <?php echo $ad['click_count']; ?>
                                    </td>
                                    <td>
                                        <a href="../public/deal-details.php?id=<?php echo $ad['ad_id']; ?>"
                                            class="btn btn-sm btn-info" target="_blank" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?delete=<?php echo $ad['ad_id']; ?>" class="btn btn-sm btn-danger"
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this advertisement?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>