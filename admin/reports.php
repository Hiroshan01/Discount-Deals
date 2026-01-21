<?php
// admin/reports.php - System Reports
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$page_title = 'System Reports - Admin';

$conn = getDBConnection();

// Overall statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_sellers = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'seller'")->fetch_assoc()['count'];
$total_buyers = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'buyer'")->fetch_assoc()['count'];
$total_ads = $conn->query("SELECT COUNT(*) as count FROM advertisements")->fetch_assoc()['count'];
$approved_ads = $conn->query("SELECT COUNT(*) as count FROM advertisements WHERE status = 'approved'")->fetch_assoc()['count'];
$total_views = $conn->query("SELECT SUM(view_count) as total FROM advertisements")->fetch_assoc()['total'] ?? 0;
$total_clicks = $conn->query("SELECT SUM(click_count) as total FROM advertisements")->fetch_assoc()['total'] ?? 0;
$total_messages = $conn->query("SELECT COUNT(*) as count FROM contact_messages")->fetch_assoc()['count'];

// Top sellers by ads
$top_sellers_query = "SELECT s.business_name, COUNT(a.ad_id) as ad_count, SUM(a.view_count) as total_views 
                     FROM seller_profiles s 
                     LEFT JOIN advertisements a ON s.seller_id = a.seller_id 
                     GROUP BY s.seller_id 
                     ORDER BY ad_count DESC 
                     LIMIT 10";
$top_sellers = $conn->query($top_sellers_query)->fetch_all(MYSQLI_ASSOC);

// Category statistics
$category_stats = $conn->query("SELECT category, COUNT(*) as count, SUM(view_count) as views 
                                FROM advertisements 
                                GROUP BY category 
                                ORDER BY count DESC")->fetch_all(MYSQLI_ASSOC);

// Monthly user registrations (last 6 months)
$monthly_users_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                       FROM users 
                       WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                       GROUP BY month 
                       ORDER BY month DESC";
$monthly_users = $conn->query($monthly_users_query)->fetch_all(MYSQLI_ASSOC);

// Monthly advertisement creation
$monthly_ads_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                     FROM advertisements 
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                     GROUP BY month 
                     ORDER BY month DESC";
$monthly_ads = $conn->query($monthly_ads_query)->fetch_all(MYSQLI_ASSOC);

// Recent admin activities
$admin_logs_query = "SELECT al.*, u.username 
                    FROM admin_logs al 
                    INNER JOIN users u ON al.admin_id = u.user_id 
                    ORDER BY al.created_at DESC 
                    LIMIT 20";
$admin_logs = $conn->query($admin_logs_query)->fetch_all(MYSQLI_ASSOC);

$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-line"></i> System Reports</h2>
            <p class="text-muted">Comprehensive system statistics and reports</p>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-gradient-primary text-white"
                style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                <div class="card-body">
                    <h4>Summary Statistics</h4>
                    <div class="row text-center mt-3">
                        <div class="col-md-2">
                            <h3><?php echo $total_users; ?></h3>
                            <p>Users</p>
                        </div>
                        <div class="col-md-2">
                            <h3><?php echo $total_sellers; ?></h3>
                            <p>Sellers</p>
                        </div>
                        <div class="col-md-2">
                            <h3><?php echo $total_ads; ?></h3>
                            <p>Advertisements</p>
                        </div>
                        <div class="col-md-2">
                            <h3><?php echo $approved_ads; ?></h3>
                            <p>Approved</p>
                        </div>
                        <div class="col-md-2">
                            <h3><?php echo number_format($total_views); ?></h3>
                            <p>Views</p>
                        </div>
                        <div class="col-md-2">
                            <h3><?php echo number_format($total_clicks); ?></h3>
                            <p>Clicks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Sellers -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Sellers</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Business</th>
                                <th>Ads</th>
                                <th>Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_sellers as $seller): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($seller['business_name']); ?></td>
                                <td><span class="badge bg-primary"><?php echo $seller['ad_count']; ?></span></td>
                                <td><span class="badge bg-success"><?php echo $seller['total_views']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($top_sellers)): ?>
                            <tr>
                                <td colspan="3" class="text-muted text-center">No data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Category Statistics -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Category Statistics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Ads</th>
                                <th>Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_stats as $cat): ?>
                            <tr>
                                <td><?php echo ucfirst($cat['category']); ?></td>
                                <td><span class="badge bg-primary"><?php echo $cat['count']; ?></span></td>
                                <td><span class="badge bg-success"><?php echo $cat['views']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($category_stats)): ?>
                            <tr>
                                <td colspan="3" class="text-muted text-center">No data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Monthly User Registrations -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-plus"></i> Monthly User Registrations</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Registered Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly_users as $month): ?>
                            <tr>
                                <td><?php echo $month['month']; ?></td>
                                <td><span class="badge bg-primary"><?php echo $month['count']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($monthly_users)): ?>
                            <tr>
                                <td colspan="2" class="text-muted text-center">No data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monthly Advertisements -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-ad"></i> Monthly Advertisements</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>New Ads</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly_ads as $month): ?>
                            <tr>
                                <td><?php echo $month['month']; ?></td>
                                <td><span class="badge bg-success"><?php echo $month['count']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($monthly_ads)): ?>
                            <tr>
                                <td colspan="2" class="text-muted text-center">No data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Activity Logs -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Admin Activity Log</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>Target Type</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admin_logs as $log): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i', strtotime($log['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['username']); ?></td>
                                    <td>
                                        <?php
                                            $action_badges = [
                                                'approve' => 'bg-success',
                                                'reject' => 'bg-danger',
                                                'delete' => 'bg-warning',
                                                'suspend' => 'bg-secondary'
                                            ];
                                            $badge = $action_badges[$log['action_type']] ?? 'bg-info';
                                            ?>
                                        <span class="badge <?php echo $badge; ?>">
                                            <?php echo ucfirst($log['action_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['target_type']); ?></td>
                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($admin_logs)): ?>
                                <tr>
                                    <td colspan="5" class="text-muted text-center">No admin activity logged yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>