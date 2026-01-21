<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

// Redirect sellers and admins to their own dashboards
if (isSeller()) {
    redirect('seller/dashboard.php');
}
if (isAdmin()) {
    redirect('admin/dashboard.php');
}

$page_title = 'Dashboard - Discount Deals';
$user_id = $_SESSION['user_id'];

$conn = getDBConnection();

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's favorite categories (based on views)
$fav_categories_query = "SELECT a.category, COUNT(*) as view_count 
                        FROM ad_views av 
                        INNER JOIN advertisements a ON av.ad_id = a.ad_id 
                        WHERE av.user_id = ? 
                        GROUP BY a.category 
                        ORDER BY view_count DESC 
                        LIMIT 3";
$stmt = $conn->prepare($fav_categories_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$fav_categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get recently viewed deals
$recent_views_query = "SELECT a.*, s.business_name, av.viewed_at 
                      FROM ad_views av 
                      INNER JOIN advertisements a ON av.ad_id = a.ad_id 
                      INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
                      WHERE av.user_id = ? 
                      ORDER BY av.viewed_at DESC 
                      LIMIT 6";
$stmt = $conn->prepare($recent_views_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_views = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user statistics
$stats_query = "SELECT 
                COUNT(DISTINCT av.ad_id) as total_views,
                COUNT(DISTINCT DATE(av.viewed_at)) as active_days
                FROM ad_views av 
                WHERE av.user_id = ?";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recommended deals (based on favorite categories)
$recommended_deals = [];
if (!empty($fav_categories)) {
    $categories = array_column($fav_categories, 'category');
    $placeholders = str_repeat('?,', count($categories) - 1) . '?';
    
    $recommend_query = "SELECT a.*, s.business_name 
                       FROM advertisements a 
                       INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
                       WHERE a.status = 'approved' 
                       AND a.category IN ($placeholders) 
                       ORDER BY a.created_at DESC 
                       LIMIT 6";
    
    $stmt = $conn->prepare($recommend_query);
    $stmt->bind_param(str_repeat('s', count($categories)), ...$categories);
    $stmt->execute();
    $recommended_deals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$stmt->close();
$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-gradient-primary text-white"
                style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                <div class="card-body">
                    <h2><i class="fas fa-user-circle"></i> Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!
                    </h2>
                    <p class="mb-0">Welcome to your dashboard. From here you can view your favorite deals and manage
                        your profile.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-3x mb-2"></i>
                    <h3><?php echo $stats['total_views'] ?? 0; ?></h3>
                    <p class="mb-0">Ads Viewed</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-3x mb-2"></i>
                    <h3><?php echo $stats['active_days'] ?? 0; ?></h3>
                    <p class="mb-0">Active Days</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-heart fa-3x mb-2"></i>
                    <h3><?php echo count($fav_categories); ?></h3>
                    <p class="mb-0">Favorite Categories</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="../public/browse-deals.php" class="btn btn-primary me-2 mb-2">
                        <i class="fas fa-search"></i> Browse Deals
                    </a>
                    <a href="view-deals.php" class="btn btn-success me-2 mb-2">
                        <i class="fas fa-list"></i> Recommended Deals
                    </a>
                    <a href="profile.php" class="btn btn-secondary mb-2">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorite Categories -->
    <?php if (!empty($fav_categories)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-star"></i> Your Favorite Categories</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($fav_categories as $cat): ?>
                        <div class="col-md-4 mb-2">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6><?php echo ucfirst($cat['category']); ?></h6>
                                    <p class="text-muted mb-0">Viewed <?php echo $cat['view_count']; ?> times</p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recently Viewed Deals -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recently Viewed Deals</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_views)): ?>
                    <p class="text-muted text-center">You have not viewed any ads yet.</p>
                    <div class="text-center">
                        <a href="../public/browse-deals.php" class="btn btn-primary">
                            Start browsing deals now
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <?php foreach ($recent_views as $deal): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <?php if ($deal['image_path']): ?>
                                <img src="<?php echo asset_url('images/uploads/' . $deal['image_path']); ?>"
                                    class="card-img-top" alt="<?php echo htmlspecialchars($deal['title']); ?>"
                                    style="height: 150px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <span class="badge bg-success mb-2">
                                        <?php echo round($deal['discount_percentage']); ?>% OFF
                                    </span>
                                    <h6 class="card-title"><?php echo htmlspecialchars($deal['title']); ?></h6>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars($deal['business_name']); ?>
                                    </p>
                                    <p class="card-text">
                                        <span class="text-muted text-decoration-line-through small">
                                            <?php echo formatPrice($deal['original_price']); ?>
                                        </span><br>
                                        <strong class="text-success">
                                            <?php echo formatPrice($deal['discounted_price']); ?>
                                        </strong>
                                    </p>
                                    <p class="text-muted small">
                                        <i class="fas fa-clock"></i> <?php echo timeAgo($deal['viewed_at']); ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="../public/deal-details.php?id=<?php echo $deal['ad_id']; ?>"
                                        class="btn btn-sm btn-primary w-100">View again</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommended Deals -->
    <?php if (!empty($recommended_deals)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-thumbs-up"></i> Recommended Deals for You</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($recommended_deals as $deal): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <?php if ($deal['image_path']): ?>
                                <img src="<?php echo asset_url('images/uploads/' . $deal['image_path']); ?>"
                                    class="card-img-top" alt="<?php echo htmlspecialchars($deal['title']); ?>"
                                    style="height: 150px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <span class="badge bg-danger mb-2">
                                        <?php echo round($deal['discount_percentage']); ?>% OFF
                                    </span>
                                    <h6 class="card-title"><?php echo htmlspecialchars($deal['title']); ?></h6>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars($deal['business_name']); ?>
                                    </p>
                                    <p class="card-text">
                                        <strong class="text-success">
                                            <?php echo formatPrice($deal['discounted_price']); ?>
                                        </strong>
                                    </p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="../public/deal-details.php?id=<?php echo $deal['ad_id']; ?>"
                                        class="btn btn-sm btn-success w-100">View details</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>