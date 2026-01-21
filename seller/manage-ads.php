<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('auth/login.php');
}

$page_title = 'Manage Advertisements - Discount Deals';
$user_id = $_SESSION['user_id'];

// Get seller_id
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT seller_id FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();
$seller_id = $seller['seller_id'];

// Filter options
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$query = "SELECT * FROM advertisements WHERE seller_id = ?";
$params = [$seller_id];
$types = 'i';

if ($status_filter) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($search) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$ads = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-list"></i> Manage Advertisements</h2>
            <p class="text-muted">View and manage all your advertisements</p>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>
                                    Pending
                                </option>
                                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>
                                    Approved
                                </option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>
                                    Rejected
                                </option>
                                <option value="expired" <?php echo $status_filter == 'expired' ? 'selected' : ''; ?>>
                                    Expired
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by title or description..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="create-ad.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Advertisement
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Go to Dashboard
            </a>
        </div>
    </div>

    <!-- Advertisements Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo count($ads); ?> Advertisements</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($ads)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No advertisements found.
                        <a href="create-ad.php">Create your first advertisement</a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ads as $ad): ?>
                                <tr>
                                    <td>
                                        <?php if ($ad['image_path']): ?>
                                        <img src="<?php echo asset_url('images/uploads/' . $ad['image_path']); ?>"
                                            alt="Ad Image"
                                            style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                        <div
                                            style="width: 60px; height: 60px; background: #ddd; border-radius: 5px; 
                                                                display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ad['title']); ?></strong><br>
                                        <small class="text-muted">
                                            <?php echo timeAgo($ad['created_at']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($ad['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted text-decoration-line-through small">
                                            <?php echo formatPrice($ad['original_price']); ?>
                                        </span><br>
                                        <strong class="text-success">
                                            <?php echo formatPrice($ad['discounted_price']); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?php echo round($ad['discount_percentage']); ?>% OFF
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                                $status_badges = [
                                                    'pending' => 'bg-warning',
                                                    'approved' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'expired' => 'bg-secondary'
                                                ];
                                                $status_labels = [
                                                    'pending' => 'Pending',
                                                    'approved' => 'Approved',
                                                    'rejected' => 'Rejected',
                                                    'expired' => 'Expired'
                                                ];
                                                ?>
                                        <span class="badge <?php echo $status_badges[$ad['status']]; ?>">
                                            <?php echo $status_labels[$ad['status']]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-eye"></i> <?php echo $ad['view_count']; ?><br>
                                        <i class="fas fa-mouse-pointer"></i> <?php echo $ad['click_count']; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="../public/deal-details.php?id=<?php echo $ad['ad_id']; ?>"
                                                class="btn btn-info" title="View" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit-ad.php?id=<?php echo $ad['ad_id']; ?>" class="btn btn-primary"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete-ad.php?id=<?php echo $ad['ad_id']; ?>"
                                                class="btn btn-danger" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this advertisement?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>