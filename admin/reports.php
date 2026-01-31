<?php
// admin/reports.php - Enhanced System Reports with Monthly Discount Analysis
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
                       ORDER BY month ASC";
$monthly_users = $conn->query($monthly_users_query)->fetch_all(MYSQLI_ASSOC);

// Monthly advertisement creation
$monthly_ads_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                     FROM advertisements 
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                     GROUP BY month 
                     ORDER BY month ASC";
$monthly_ads = $conn->query($monthly_ads_query)->fetch_all(MYSQLI_ASSOC);

// Recent admin activities
$admin_logs_query = "SELECT al.*, u.username 
                    FROM admin_logs al 
                    INNER JOIN users u ON al.admin_id = u.user_id 
                    ORDER BY al.created_at DESC 
                    LIMIT 20";
$admin_logs = $conn->query($admin_logs_query)->fetch_all(MYSQLI_ASSOC);

// Enhanced Monthly company discount statistics (last 6 months)
$monthly_discounts_query = "
    SELECT 
        DATE_FORMAT(a.created_at, '%Y-%m') as month,
        DATE_FORMAT(a.created_at, '%M %Y') as month_name,
        s.seller_id,
        s.business_name,
        COUNT(a.ad_id) as ad_count,
        ROUND(AVG(a.discount_percentage), 1) as avg_discount,
        ROUND(SUM(a.discount_percentage), 1) as total_discount,
        ROUND(SUM(a.original_price - a.discounted_price), 2) as total_savings
    FROM advertisements a 
    INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
    WHERE a.status = 'approved' 
    AND a.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    AND a.discount_percentage > 0
    GROUP BY s.seller_id, DATE_FORMAT(a.created_at, '%Y-%m')
    ORDER BY month DESC, total_discount DESC
";
$monthly_discounts = $conn->query($monthly_discounts_query)->fetch_all(MYSQLI_ASSOC);

// Current month top 10 companies by discount
$current_month_discounts_query = "
    SELECT 
        s.seller_id,
        s.business_name,
        COUNT(a.ad_id) as ad_count,
        ROUND(AVG(a.discount_percentage), 1) as avg_discount,
        ROUND(SUM(a.discount_percentage), 1) as total_discount,
        ROUND(SUM(a.original_price - a.discounted_price), 2) as total_savings,
        ROUND(SUM(a.discounted_price), 2) as total_sales_value
    FROM advertisements a 
    INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
    WHERE a.status = 'approved' 
    AND MONTH(a.created_at) = MONTH(NOW())
    AND YEAR(a.created_at) = YEAR(NOW())
    AND a.discount_percentage > 0
    GROUP BY s.seller_id
    ORDER BY total_discount DESC 
    LIMIT 10
";
$current_month_top = $conn->query($current_month_discounts_query)->fetch_all(MYSQLI_ASSOC);

// All-time top discount companies
$alltime_discounts_query = "
    SELECT 
        s.seller_id,
        s.business_name,
        COUNT(a.ad_id) as ad_count,
        ROUND(AVG(a.discount_percentage), 1) as avg_discount,
        ROUND(SUM(a.discount_percentage), 1) as total_discount,
        ROUND(SUM(a.original_price - a.discounted_price), 2) as total_savings
    FROM advertisements a 
    INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
    WHERE a.status = 'approved'
    AND a.discount_percentage > 0
    GROUP BY s.seller_id
    ORDER BY total_discount DESC 
    LIMIT 10
";
$alltime_top = $conn->query($alltime_discounts_query)->fetch_all(MYSQLI_ASSOC);

// Summary statistics for current month
$current_month_summary_query = "
    SELECT 
        COUNT(DISTINCT s.seller_id) as active_sellers,
        COUNT(a.ad_id) as total_ads,
        ROUND(AVG(a.discount_percentage), 1) as avg_discount,
        ROUND(SUM(a.original_price - a.discounted_price), 2) as total_savings
    FROM advertisements a 
    INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
    WHERE a.status = 'approved' 
    AND MONTH(a.created_at) = MONTH(NOW())
    AND YEAR(a.created_at) = YEAR(NOW())
    AND a.discount_percentage > 0
";
$current_month_summary = $conn->query($current_month_summary_query)->fetch_assoc();

// Month-over-month comparison
$mom_comparison_query = "
    SELECT 
        'current' as period,
        COUNT(a.ad_id) as ad_count,
        ROUND(AVG(a.discount_percentage), 1) as avg_discount,
        ROUND(SUM(a.original_price - a.discounted_price), 2) as total_savings
    FROM advertisements a 
    WHERE a.status = 'approved' 
    AND MONTH(a.created_at) = MONTH(NOW())
    AND YEAR(a.created_at) = YEAR(NOW())
    UNION ALL
    SELECT 
        'previous' as period,
        COUNT(a.ad_id) as ad_count,
        ROUND(AVG(a.discount_percentage), 1) as avg_discount,
        ROUND(SUM(a.original_price - a.discounted_price), 2) as total_savings
    FROM advertisements a 
    WHERE a.status = 'approved' 
    AND MONTH(a.created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
    AND YEAR(a.created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
";
$mom_data = $conn->query($mom_comparison_query)->fetch_all(MYSQLI_ASSOC);
$mom_current = $mom_data[0] ?? ['ad_count' => 0, 'avg_discount' => 0, 'total_savings' => 0];
$mom_previous = $mom_data[1] ?? ['ad_count' => 0, 'avg_discount' => 0, 'total_savings' => 0];

$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
.gradient-card {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    border: none;
    border-radius: 15px;
}

.stat-card {
    border: none;
    border-radius: 12px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.stat-card .card-body {
    padding: 1.5rem;
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.trend-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
}

.company-rank {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.rank-1 {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #fff;
}

.rank-2 {
    background: linear-gradient(135deg, #C0C0C0, #808080);
    color: #fff;
}

.rank-3 {
    background: linear-gradient(135deg, #CD7F32, #8B4513);
    color: #fff;
}

.rank-other {
    background: linear-gradient(135deg, #e9ecef, #dee2e6);
    color: #495057;
}

.chart-container {
    position: relative;
    height: 350px;
    margin-top: 1rem;
}

.progress-thin {
    height: 8px;
    border-radius: 10px;
}

.tab-content {
    padding: 2rem 1rem;
}

.nav-pills .nav-link {
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-pills .nav-link:hover {
    background-color: #f8f9fa;
}

.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
}

.metric-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
}

.comparison-arrow {
    font-size: 1.2rem;
    margin-left: 0.5rem;
}

.table-modern {
    border-radius: 10px;
    overflow: hidden;
}

.table-modern thead {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    color: white;
}

.table-modern tbody tr {
    transition: background-color 0.2s ease;
}

.table-modern tbody tr:hover {
    background-color: #f8f9fa;
}

.discount-badge {
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
}

.section-header {
    border-left: 4px solid #198754;
    padding-left: 1rem;
    margin-bottom: 1.5rem;
}
</style>

<div class="container-fluid mt-4 mb-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="fas fa-chart-line text-primary"></i> System Analytics Dashboard</h2>
                    <p class="text-muted mb-0">Comprehensive insights and performance metrics</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="row mb-4 g-3">
        <div class="col-lg-3 col-md-6">
            <div class="card stat-card text-white"
                style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="metric-label text-white-50">Total Users</div>
                            <div class="metric-value"><?php echo number_format($total_users); ?></div>
                            <small class="text-white-50">
                                <i class="fas fa-users me-1"></i>
                                <?php echo $total_sellers; ?> Sellers | <?php echo $total_buyers; ?> Buyers
                            </small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card text-white"
                style="background: linear-gradient(135deg, #20c997 0%, #0dcaf0 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="metric-label text-white-50">Advertisements</div>
                            <div class="metric-value"><?php echo number_format($total_ads); ?></div>
                            <small class="text-white-50">
                                <i class="fas fa-check-circle me-1"></i>
                                <?php echo $approved_ads; ?> Approved
                            </small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-ad"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card text-white"
                style="background: linear-gradient(135deg, #0dcaf0 0%, #198754 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="metric-label text-white-50">Total Views</div>
                            <div class="metric-value"><?php echo number_format($total_views); ?></div>
                            <small class="text-white-50">
                                <i class="fas fa-mouse-pointer me-1"></i>
                                <?php echo number_format($total_clicks); ?> Clicks
                            </small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card text-white"
                style="background: linear-gradient(135deg, #20c997 0%, #198754 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="metric-label text-white-50">Messages</div>
                            <div class="metric-value"><?php echo number_format($total_messages); ?></div>
                            <small class="text-white-50">
                                <i class="fas fa-envelope me-1"></i>
                                Contact Inquiries
                            </small>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Discount Analysis Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header gradient-card text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="fas fa-percentage me-2"></i>Monthly Discount Analytics</h4>
                            <small class="opacity-75">Company-wise performance tracking and trends</small>
                        </div>
                        <div>
                            <span class="badge bg-white text-primary px-3 py-2">
                                <i class="fas fa-calendar-alt me-2"></i>Last 6 Months
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Current Month Summary Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-building text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h3 class="mb-1 text-primary">
                                        <?php echo $current_month_summary['active_sellers'] ?? 0; ?></h3>
                                    <p class="mb-0 text-muted small">Active Companies</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-tags text-success mb-2" style="font-size: 2rem;"></i>
                                    <h3 class="mb-1 text-success">
                                        <?php echo $current_month_summary['total_ads'] ?? 0; ?></h3>
                                    <p class="mb-0 text-muted small">Active Discounts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-percent text-warning mb-2" style="font-size: 2rem;"></i>
                                    <h3 class="mb-1 text-warning">
                                        <?php echo $current_month_summary['avg_discount'] ?? 0; ?>%</h3>
                                    <p class="mb-0 text-muted small">Average Discount</p>
                                    <?php 
                                    $discount_change = $mom_current['avg_discount'] - $mom_previous['avg_discount'];
                                    if ($discount_change != 0): 
                                    ?>
                                    <small class="<?php echo $discount_change > 0 ? 'text-success' : 'text-danger'; ?>">
                                        <i class="fas fa-arrow-<?php echo $discount_change > 0 ? 'up' : 'down'; ?>"></i>
                                        <?php echo abs(round($discount_change, 1)); ?>% MoM
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-rupee-sign text-success mb-2" style="font-size: 2rem;"></i>
                                    <h3 class="mb-1 text-success">Rs
                                        <?php echo number_format($current_month_summary['total_savings'] ?? 0, 2); ?>
                                    </h3>
                                    <p class="mb-0 text-muted small">Customer Savings</p>
                                    <?php 
                                    $savings_change = (($mom_current['total_savings'] - $mom_previous['total_savings']) / max($mom_previous['total_savings'], 1)) * 100;
                                    if ($savings_change != 0): 
                                    ?>
                                    <small class="<?php echo $savings_change > 0 ? 'text-success' : 'text-danger'; ?>">
                                        <i class="fas fa-arrow-<?php echo $savings_change > 0 ? 'up' : 'down'; ?>"></i>
                                        <?php echo abs(round($savings_change, 1)); ?>% MoM
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs for Different Views -->
                    <ul class="nav nav-pills mb-4" id="discountTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="current-month-tab" data-bs-toggle="pill"
                                data-bs-target="#current-month" type="button" role="tab">
                                <i class="fas fa-calendar-day me-2"></i>This Month
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="trends-tab" data-bs-toggle="pill" data-bs-target="#trends"
                                type="button" role="tab">
                                <i class="fas fa-chart-line me-2"></i>6-Month Trends
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="alltime-tab" data-bs-toggle="pill" data-bs-target="#alltime"
                                type="button" role="tab">
                                <i class="fas fa-trophy me-2"></i>All-Time Leaders
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="company-breakdown-tab" data-bs-toggle="pill"
                                data-bs-target="#company-breakdown" type="button" role="tab">
                                <i class="fas fa-building me-2"></i>Company Breakdown
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="discountTabContent">
                        <!-- Current Month Tab -->
                        <div class="tab-pane fade show active" id="current-month" role="tabpanel">
                            <h5 class="section-header">Top 10 Companies - <?php echo date('F Y'); ?></h5>
                            <div class="table-responsive">
                                <table class="table table-modern table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="60">Rank</th>
                                            <th>Company Name</th>
                                            <th class="text-center">Active Ads</th>
                                            <th class="text-center">Avg Discount</th>
                                            <th class="text-center">Total Discount</th>
                                            <th class="text-end">Customer Savings</th>
                                            <th width="150">Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($current_month_top)): ?>
                                        <?php foreach ($current_month_top as $index => $company): 
                                                $rank = $index + 1;
                                                $rankClass = $rank <= 3 ? "rank-$rank" : "rank-other";
                                                $maxDiscount = $current_month_top[0]['total_discount'];
                                                $performance = ($company['total_discount'] / max($maxDiscount, 1)) * 100;
                                            ?>
                                        <tr>
                                            <td>
                                                <div class="company-rank <?php echo $rankClass; ?>">
                                                    <?php echo $rank; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong
                                                    class="text-dark"><?php echo htmlspecialchars($company['business_name']); ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-info rounded-pill"><?php echo $company['ad_count']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="discount-badge bg-success text-white">
                                                    <?php echo $company['avg_discount']; ?>%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <strong
                                                    class="text-primary"><?php echo number_format($company['total_discount'], 1); ?>%</strong>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">Rs
                                                    <?php echo number_format($company['total_savings'], 2); ?></strong>
                                            </td>
                                            <td>
                                                <div class="progress progress-thin">
                                                    <div class="progress-bar bg-gradient-success" role="progressbar"
                                                        style="width: <?php echo $performance; ?>%"
                                                        aria-valuenow="<?php echo $performance; ?>" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?php echo round($performance); ?>%</small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                No discount data available for this month
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Trends Tab -->
                        <div class="tab-pane fade" id="trends" role="tabpanel">
                            <h5 class="section-header">6-Month Discount Trends</h5>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="chart-container">
                                        <canvas id="discountTrendChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="chart-container">
                                        <canvas id="companySavingsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="chart-container">
                                        <canvas id="monthlyComparisonChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- All-Time Leaders Tab -->
                        <div class="tab-pane fade" id="alltime" role="tabpanel">
                            <h5 class="section-header">All-Time Top Performers</h5>
                            <div class="table-responsive">
                                <table class="table table-modern table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="60">Rank</th>
                                            <th>Company Name</th>
                                            <th class="text-center">Total Ads</th>
                                            <th class="text-center">Avg Discount</th>
                                            <th class="text-center">Cumulative Discount</th>
                                            <th class="text-end">Total Savings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($alltime_top)): ?>
                                        <?php foreach ($alltime_top as $index => $company): 
                                                $rank = $index + 1;
                                                $rankClass = $rank <= 3 ? "rank-$rank" : "rank-other";
                                            ?>
                                        <tr>
                                            <td>
                                                <div class="company-rank <?php echo $rankClass; ?>">
                                                    <?php if ($rank === 1): ?>
                                                    <i class="fas fa-crown"></i>
                                                    <?php else: ?>
                                                    <?php echo $rank; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong
                                                    class="text-dark"><?php echo htmlspecialchars($company['business_name']); ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-primary rounded-pill"><?php echo $company['ad_count']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="discount-badge bg-warning text-white">
                                                    <?php echo $company['avg_discount']; ?>%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <strong
                                                    class="text-primary"><?php echo number_format($company['total_discount'], 1); ?>%</strong>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">Rs
                                                    <?php echo number_format($company['total_savings'], 2); ?></strong>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                No historical data available
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Company Breakdown Tab -->
                        <div class="tab-pane fade" id="company-breakdown" role="tabpanel">
                            <h5 class="section-header">Monthly Company-wise Breakdown</h5>
                            <div class="table-responsive">
                                <table class="table table-modern table-hover table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Company</th>
                                            <th class="text-center">Ads</th>
                                            <th class="text-center">Avg %</th>
                                            <th class="text-center">Total %</th>
                                            <th class="text-end">Savings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($monthly_discounts)): ?>
                                        <?php 
                                            $currentMonth = '';
                                            foreach ($monthly_discounts as $data): 
                                                $isNewMonth = $currentMonth !== $data['month'];
                                                $currentMonth = $data['month'];
                                            ?>
                                        <tr <?php echo $isNewMonth ? 'class="table-active"' : ''; ?>>
                                            <td>
                                                <?php if ($isNewMonth): ?>
                                                <strong><?php echo $data['month_name']; ?></strong>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($data['business_name']); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?php echo $data['ad_count']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="text-success fw-bold"><?php echo $data['avg_discount']; ?>%</span>
                                            </td>
                                            <td class="text-center">
                                                <?php echo number_format($data['total_discount'], 1); ?>%
                                            </td>
                                            <td class="text-end">
                                                Rs <?php echo number_format($data['total_savings'] ?? 0, 2); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                No monthly breakdown data available
                                            </td>
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
    </div>

    <!-- Other Statistics Row -->
    <div class="row mb-4 g-4">
        <!-- Top Sellers -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i>Top Sellers by Ads</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Business</th>
                                    <th class="text-center">Ads</th>
                                    <th class="text-center">Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_sellers as $seller): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($seller['business_name']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo $seller['ad_count']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-success"><?php echo number_format($seller['total_views'] ?? 0); ?></span>

                                    </td>
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
        </div>

        <!-- Category Statistics -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-info me-2"></i>Category Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Ads</th>
                                    <th class="text-center">Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($category_stats as $cat): ?>
                                <tr>
                                    <td><?php echo ucfirst($cat['category']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo $cat['count']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-success"><?php echo number_format($cat['views']); ?></span>
                                    </td>
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
    </div>

    <!-- Monthly Trends Row -->
    <div class="row mb-4 g-4">
        <!-- Monthly User Registrations -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-plus text-primary me-2"></i>Monthly User Registrations</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="userRegistrationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Advertisements -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-ad text-success me-2"></i>Monthly Advertisements</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="monthlyAdsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Activity Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history text-secondary me-2"></i>Recent Admin Activities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>Target</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admin_logs as $log): ?>
                                <tr>
                                    <td><small><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></small>
                                    </td>
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
                                    <td><small><?php echo htmlspecialchars($log['target_type']); ?></small></td>
                                    <td><small><?php echo htmlspecialchars($log['description']); ?></small></td>
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

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default settings
    Chart.defaults.font.family = "'Segoe UI', 'Roboto', sans-serif";
    Chart.defaults.color = '#6c757d';

    // Monthly discount trend chart
    const monthlyData = <?php echo json_encode($monthly_discounts); ?>;

    if (monthlyData.length > 0) {
        const months = [...new Set(monthlyData.map(d => d.month))].reverse();
        const companies = [...new Set(monthlyData.map(d => d.business_name))];
        const topCompanies = companies.slice(0, 5);

        // Color palette
        const colors = [{
                border: '#198754',
                bg: 'rgba(25, 135, 84, 0.1)'
            },
            {
                border: '#f093fb',
                bg: 'rgba(240, 147, 251, 0.1)'
            },
            {
                border: '#4facfe',
                bg: 'rgba(79, 172, 254, 0.1)'
            },
            {
                border: '#43e97b',
                bg: 'rgba(67, 233, 123, 0.1)'
            },
            {
                border: '#fa709a',
                bg: 'rgba(250, 112, 154, 0.1)'
            }
        ];

        const datasets = topCompanies.map((company, index) => ({
            label: company,
            data: months.map(month => {
                const data = monthlyData.find(d => d.business_name === company && d
                    .month === month);
                return data ? parseFloat(data.total_discount) : 0;
            }),
            borderColor: colors[index].border,
            backgroundColor: colors[index].bg,
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 7,
            borderWidth: 3
        }));

        const ctx1 = document.getElementById('discountTrendChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    title: {
                        display: true,
                        text: 'Top 5 Companies - Discount Trends',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y.toFixed(1)}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Discount Percentage',
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month',
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Company Savings Pie Chart
        const currentMonthData = <?php echo json_encode($current_month_top); ?>;
        if (currentMonthData.length > 0) {
            const ctx2 = document.getElementById('companySavingsChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: currentMonthData.slice(0, 5).map(c => c.business_name),
                    datasets: [{
                        data: currentMonthData.slice(0, 5).map(c => c.total_savings),
                        backgroundColor: colors.map(c => c.border),
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 10,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Customer Savings Distribution',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: Rs ${context.parsed.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Monthly Comparison Bar Chart
        const monthComparison = {};
        monthlyData.forEach(d => {
            if (!monthComparison[d.month]) {
                monthComparison[d.month] = {
                    ads: 0,
                    savings: 0
                };
            }
            monthComparison[d.month].ads += parseInt(d.ad_count);
            monthComparison[d.month].savings += parseFloat(d.total_savings);
        });

        const ctx3 = document.getElementById('monthlyComparisonChart').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Active Discounts',
                    data: months.map(m => monthComparison[m]?.ads || 0),
                    backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    borderColor: '#198754',
                    borderWidth: 2,
                    yAxisID: 'y',
                }, {
                    label: 'Customer Savings (LKR)',
                    data: months.map(m => monthComparison[m]?.savings || 0),
                    backgroundColor: 'rgba(67, 233, 123, 0.8)',
                    borderColor: '#43e97b',
                    borderWidth: 2,
                    type: 'line',
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Monthly Activity & Savings Comparison',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Discounts'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Savings (LKR)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
    }

    // User Registration Chart
    const userMonthlyData = <?php echo json_encode($monthly_users); ?>;
    if (userMonthlyData.length > 0) {
        const ctx4 = document.getElementById('userRegistrationChart').getContext('2d');
        new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: userMonthlyData.map(d => d.month),
                datasets: [{
                    label: 'New Users',
                    data: userMonthlyData.map(d => d.count),
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: '#0d6efd',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `Users: ${context.parsed.y}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Monthly Ads Chart
    const adsMonthlyData = <?php echo json_encode($monthly_ads); ?>;
    if (adsMonthlyData.length > 0) {
        const ctx5 = document.getElementById('monthlyAdsChart').getContext('2d');
        new Chart(ctx5, {
            type: 'bar',
            data: {
                labels: adsMonthlyData.map(d => d.month),
                datasets: [{
                    label: 'New Ads',
                    data: adsMonthlyData.map(d => d.count),
                    backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    borderColor: '#198754',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `Ads: ${context.parsed.y}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>