<?php
// seller/reports.php - Reports
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('auth/login.php');
}

$page_title = 'Reports - Discount Deals';
$user_id = $_SESSION['user_id'];

$conn = getDBConnection();

// Get seller_id
$stmt = $conn->prepare("SELECT seller_id, business_name FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();
$seller_id = $seller['seller_id'];

// Overall Statistics
$stats_query = "SELECT 
    COUNT(*) as total_ads,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_ads,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_ads,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_ads,
    SUM(view_count) as total_views,
    SUM(click_count) as total_clicks,
    AVG(view_count) as avg_views,
    AVG(click_count) as avg_clicks
    FROM advertisements WHERE seller_id = ?";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Top performing ads
$top_ads_query = "SELECT ad_id, title, view_count, click_count, discounted_price, category 
                  FROM advertisements 
                  WHERE seller_id = ? AND status = 'approved'
                  ORDER BY view_count DESC 
                  LIMIT 5";

$stmt = $conn->prepare($top_ads_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$top_ads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Category wise performance
$category_query = "SELECT 
    category,
    COUNT(*) as ad_count,
    SUM(view_count) as total_views,
    SUM(click_count) as total_clicks
    FROM advertisements 
    WHERE seller_id = ? 
    GROUP BY category 
    ORDER BY total_views DESC";

$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$category_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Recent contact messages
$messages_query = "SELECT cm.*, a.title 
                   FROM contact_messages cm
                   INNER JOIN advertisements a ON cm.ad_id = a.ad_id
                   WHERE a.seller_id = ?
                   ORDER BY cm.created_at DESC
                   LIMIT 10";

$stmt = $conn->prepare($messages_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-bar"></i> Reports and Statistics</h2>
            <p class="text-muted">Detailed reports for <?php echo htmlspecialchars($seller['business_name']); ?></p>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?php echo $stats['total_ads'] ?? 0; ?></h3>
                    <p class="mb-0">Total Ads</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?php echo $stats['approved_ads'] ?? 0; ?></h3>
                    <p class="mb-0">Approved Ads</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?php echo number_format($stats['total_views'] ?? 0); ?></h3>
                    <p class="mb-0">Total Views</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3><?php echo number_format($stats['total_clicks'] ?? 0); ?></h3>
                    <p class="mb-0">Total Clicks</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Most Popular Ads</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($top_ads)): ?>
                    <p class="text-muted text-center">No ads available</p>
                    <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Views</th>
                                <th>Clicks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_ads as $ad): ?>
                            <tr>
                                <td>
                                    <a href="edit-ad.php?id=<?php echo $ad['ad_id']; ?>">
                                        <?php echo htmlspecialchars($ad['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo $ad['category']; ?></td>
                                <td><span class="badge bg-info"><?php echo $ad['view_count']; ?></span></td>
                                <td><span class="badge bg-success"><?php echo $ad['click_count']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Performance by Category</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($category_stats)): ?>
                    <p class="text-muted text-center">No data available</p>
                    <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Ads</th>
                                <th>Views</th>
                                <th>Clicks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_stats as $cat): ?>
                            <tr>
                                <td><?php echo ucfirst($cat['category']); ?></td>
                                <td><?php echo $cat['ad_count']; ?></td>
                                <td><?php echo $cat['total_views']; ?></td>
                                <td><?php echo $cat['total_clicks']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Performance -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-calculator"></i> Average Performance</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h4 class="text-primary"><?php echo round($stats['avg_views'] ?? 0, 1); ?></h4>
                            <p class="text-muted">Average Views (per ad)</p>
                        </div>
                        <div class="col-md-4">
                            <h4 class="text-success"><?php echo round($stats['avg_clicks'] ?? 0, 1); ?></h4>
                            <p class="text-muted">Average Clicks (per ad)</p>
                        </div>
                        <div class="col-md-4">
                            <?php 
                            $ctr = 0;
                            if ($stats['total_views'] > 0) {
                                $ctr = ($stats['total_clicks'] / $stats['total_views']) * 100;
                            }
                            ?>
                            <h4 class="text-info"><?php echo round($ctr, 2); ?>%</h4>
                            <p class="text-muted">Click Through Rate (CTR)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Contact Messages -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-envelope"></i> Recent Contact Messages</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($messages)): ?>
                    <p class="text-muted text-center">No messages</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ad</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($msg['title']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['sender_name']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo $msg['sender_email']; ?>">
                                            <?php echo htmlspecialchars($msg['sender_email']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($msg['sender_phone']); ?></td>
                                    <td>
                                        <?php 
                                                $short_msg = substr($msg['message'], 0, 50);
                                                echo htmlspecialchars($short_msg);
                                                if (strlen($msg['message']) > 50) echo '...';
                                                ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></td>
                                    <td>
                                        <?php
                                                $status_badge = [
                                                    'new' => 'bg-warning',
                                                    'read' => 'bg-info',
                                                    'replied' => 'bg-success'
                                                ];
                                                ?>
                                        <span class="badge <?php echo $status_badge[$msg['status']]; ?>">
                                            <?php echo $msg['status']; ?>
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

    <!-- Export Options -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-download"></i> Export Reports</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Download your reports as PDF or CSV</p>
                    <button class="btn btn-danger" onclick="exportPDF()">
                        <i class="fas fa-file-pdf"></i> Export as PDF
                    </button>
                    <button class="btn btn-success" onclick="exportCSV()">
                        <i class="fas fa-file-csv"></i> Export as CSV
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportPDF() {
    alert('PDF export feature will be added in a future version!');
    // Future: Implement PDF generation using libraries like FPDF or TCPDF
}

function exportCSV() {
    alert('CSV export feature will be added in a future version!');
    // Future: Generate CSV file with statistics
}
</script>

<?php include '../includes/footer.php'; ?>