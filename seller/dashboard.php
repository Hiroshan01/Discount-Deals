<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if logged in as seller
if (!isLoggedIn() || !isSeller()) {
    redirect('auth/login.php');
}

$page_title = 'Seller Dashboard - Discount Deals';
$user_id = $_SESSION['user_id'];

// Get seller profile
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();
$seller_id = $seller['seller_id'];

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_ads,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_ads,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_ads,
    SUM(view_count) as total_views,
    SUM(click_count) as total_clicks
    FROM advertisements WHERE seller_id = ?";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent ads
$recent_ads_query = "SELECT * FROM advertisements WHERE seller_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($recent_ads_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$recent_ads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-tachometer-alt"></i> Seller Dashboard</h2>
            <p class="text-muted">Welcome, <?php echo htmlspecialchars($seller['business_name']); ?>!</p>
            <hr>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Complete Advertisements</h6>
                    <h2><?php echo $stats['total_ads'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Approved advertisements</h6>
                    <h2><?php echo $stats['approved_ads'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6>Pending</h6>
                    <h2><?php echo $stats['pending_ads'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Total views</h6>
                    <h2><?php echo $stats['total_views'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <?php if (!$seller['is_verified']): ?>
    <!-- Verification Alert -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Business Verification Required
                </h5>
                <p>
                    Enter your BR or NIC number to self-approve your advertisements.
                    Currently, your advertisements are waiting for admin approval.
                </p>
                <hr>
                <a href="verify-business.php" class="btn btn-warning">
                    <i class="fas fa-shield-alt"></i> Verify Now
                </a>
                <button type="button" class="btn btn-light" data-bs-dismiss="alert">
                    Later
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>


    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick actions</h5>
                </div>
                <div class="card-body">
                    <a href="create-ad.php" class="btn btn-primary me-2">
                        <i class="fas fa-plus"></i> Create new advertisement
                    </a>
                    <a href="manage-ads.php" class="btn btn-secondary me-2">
                        <i class="fas fa-list"></i> Manage advertisements
                    </a>
                    <a href="reports.php" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> View reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Advertisements -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-ad"></i> Recent advertisements</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_ads)): ?>
                    <p class="text-center text-muted">No advertisements. <a href="create-ad.php">Create the first
                            advertisement</a>
                    </p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($recent_ads as $ad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ad['title']); ?></td>
                                    <td><?php echo htmlspecialchars($ad['category']); ?></td>
                                    <td><?php echo formatPrice($ad['discounted_price']); ?></td>
                                    <td>
                                        <?php
                                                $badge_class = [
                                                    'pending' => 'bg-warning',
                                                    'approved' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'expired' => 'bg-secondary'
                                                ];
                                                ?>
                                        <span
                                            class="badge <?php echo $badge_class[$ad['status']] ?? 'bg-secondary'; ?>">
                                            <?php echo $ad['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $ad['view_count']; ?></td>
                                    <td>
                                        <a href="edit-ad.php?id=<?php echo $ad['ad_id']; ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
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