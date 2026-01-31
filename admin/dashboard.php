<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$page_title = 'Admin Dashboard - Discount Deals';

$conn = getDBConnection();

// Overall Statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total sellers
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'seller'");
$stats['total_sellers'] = $result->fetch_assoc()['count'];

// Total buyers
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'buyer'");
$stats['total_buyers'] = $result->fetch_assoc()['count'];

// Total advertisements
$result = $conn->query("SELECT COUNT(*) as count FROM advertisements");
$stats['total_ads'] = $result->fetch_assoc()['count'];

// Pending advertisements
$result = $conn->query("SELECT COUNT(*) as count FROM advertisements WHERE status = 'pending'");
$stats['pending_ads'] = $result->fetch_assoc()['count'];

// Approved advertisements
$result = $conn->query("SELECT COUNT(*) as count FROM advertisements WHERE status = 'approved'");
$stats['approved_ads'] = $result->fetch_assoc()['count'];

// Total views
$result = $conn->query("SELECT SUM(view_count) as total FROM advertisements");
$stats['total_views'] = $result->fetch_assoc()['total'] ?? 0;

// Total messages
$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
$stats['total_messages'] = $result->fetch_assoc()['count'];

// Recent pending ads
$pending_ads_query = "SELECT a.*, s.business_name, u.full_name 
                      FROM advertisements a 
                      INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
                      INNER JOIN users u ON s.user_id = u.user_id 
                      WHERE a.status = 'pending' 
                      ORDER BY a.created_at DESC 
                      LIMIT 5";
$pending_ads = $conn->query($pending_ads_query)->fetch_all(MYSQLI_ASSOC);

// Recent users
$recent_users_query = "
    SELECT 
        u.*,
        sp.nic_number,
        sp.br_number
    FROM users u
    LEFT JOIN seller_profiles sp ON u.user_id = sp.user_id
    ORDER BY u.created_at DESC
    LIMIT 5
";

$recent_users = $conn->query($recent_users_query)->fetch_all(MYSQLI_ASSOC);

// Category statistics
$category_stats_query = "SELECT category, COUNT(*) as count 
                        FROM advertisements 
                        WHERE status = 'approved' 
                        GROUP BY category 
                        ORDER BY count DESC";
$category_stats = $conn->query($category_stats_query)->fetch_all(MYSQLI_ASSOC);

$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid mt-4 mb-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
            <p class="text-muted">System management control center</p>
        </div>
    </div>

    <!-- Statistics Cards Row 1 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Total Users</h6>
                            <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-25">
                    <a href="manage-users.php" class="text-white text-decoration-none">
                        View details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Sellers</h6>
                            <h2 class="mb-0"><?php echo $stats['total_sellers']; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-store fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-25">
                    <a href="manage-users.php?type=seller" class="text-white text-decoration-none">
                        View details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Buyers</h6>
                            <h2 class="mb-0"><?php echo $stats['total_buyers']; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-25">
                    <a href="manage-users.php?type=buyer" class="text-white text-decoration-none">
                        View details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase">Pending Ads</h6>
                            <h2 class="mb-0"><?php echo $stats['pending_ads']; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-25">
                    <a href="approve-ads.php" class="text-white text-decoration-none">
                        Approve now <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 2 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h6>Total Advertisements</h6>
                    <h2><?php echo $stats['total_ads']; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Approved Ads</h6>
                    <h2><?php echo $stats['approved_ads']; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>Total Views</h6>
                    <h2><?php echo number_format($stats['total_views']); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Total Messages</h6>
                    <h2><?php echo $stats['total_messages']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Advertisements -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Ads Waiting for Approval</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_ads)): ?>
                    <p class="text-muted text-center">There are no ads waiting for approval.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Seller</th>
                                    <th>Category</th>
                                    <th>NIC</th>
                                    <th>BR Number</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_ads as $ad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ad['title']); ?></td>
                                    <td><?php echo htmlspecialchars($ad['business_name']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $ad['category']; ?></span></td>
                                    <td><?php echo date('Y-m-d', strtotime($ad['created_at'])); ?></td>
                                    <td>
                                        <a href="approve-ads.php?id=<?php echo $ad['ad_id']; ?>"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Review
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="approve-ads.php" class="btn btn-warning">
                        View all <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Category Statistics -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Ads by Category</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($category_stats)): ?>
                    <p class="text-muted text-center">No data available</p>
                    <?php else: ?>
                    <?php foreach ($category_stats as $cat): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?php echo ucfirst($cat['category']); ?></span>
                            <span class="badge bg-primary"><?php echo $cat['count']; ?></span>
                        </div>
                        <div class="progress">
                            <?php 
                                    $percentage = $stats['approved_ads'] > 0 
                                        ? ($cat['count'] / $stats['approved_ads']) * 100 
                                        : 0;
                                    ?>
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: <?php echo $percentage; ?>%">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-plus"></i> Recent Users</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_users)): ?>
                    <p class="text-muted text-center">No users found</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Registered Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span
                                            class="badge <?php echo $user['user_type'] == 'seller' ? 'bg-success' : 'bg-info'; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['nic_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user['br_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <span
                                            class="badge <?php echo $user['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $user['status']; ?>
                                        </span>
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