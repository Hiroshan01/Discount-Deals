<?php
// user/view-deals.php - View All Deals for User
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$page_title = 'View Deals - Discount Deals';
$user_id = $_SESSION['user_id'];

// Get user's favorite categories
$conn = getDBConnection();
$fav_categories_query = "SELECT a.category, COUNT(*) as view_count 
                        FROM ad_views av 
                        INNER JOIN advertisements a ON av.ad_id = a.ad_id 
                        WHERE av.user_id = ? 
                        GROUP BY a.category 
                        ORDER BY view_count DESC";
$stmt = $conn->prepare($fav_categories_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$fav_categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get all approved deals
$deals_query = "SELECT a.*, s.business_name 
               FROM advertisements a 
               INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
               WHERE a.status = 'approved' 
               ORDER BY a.created_at DESC";
$result = $conn->query($deals_query);
$all_deals = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-tags"></i> All Deals</h2>
            <p class="text-muted">Find the best deals for you</p>
        </div>
    </div>

    <!-- Favorite Categories -->
    <?php if (!empty($fav_categories)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6><i class="fas fa-heart text-danger"></i> Your Favorite Categories:</h6>
                    <?php foreach ($fav_categories as $cat): ?>
                    <a href="../public/browse-deals.php?category=<?php echo $cat['category']; ?>"
                        class="btn btn-sm btn-outline-success me-2 mb-2">
                        <?php echo ucfirst($cat['category']); ?>
                        <span class="badge bg-success"><?php echo $cat['view_count']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" onclick="filterDeals('all')">
                    All
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="filterDeals('meals')">
                    Meals
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="filterDeals('bakery')">
                    Bakery
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="filterDeals('beverages')">
                    Beverages
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="filterDeals('desserts')">
                    Desserts
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="filterDeals('snacks')">
                    Snacks
                </button>
            </div>
        </div>
    </div>

    <!-- Deals Grid -->
    <div class="row" id="dealsContainer">
        <?php if (empty($all_deals)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                No deals available at the moment.
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($all_deals as $deal): ?>
        <div class="col-md-4 mb-4 deal-item" data-category="<?php echo $deal['category']; ?>">
            <div class="card h-100">
                <?php if ($deal['image_path']): ?>
                <div style="position: relative;">
                    <img src="<?php echo asset_url('images/uploads/' . $deal['image_path']); ?>" class="card-img-top"
                        alt="<?php echo htmlspecialchars($deal['title']); ?>" style="height: 200px; object-fit: cover;">
                    <span class="discount-badge">
                        <?php echo round($deal['discount_percentage']); ?>% OFF
                    </span>
                </div>
                <?php endif; ?>

                <div class="card-body">
                    <span class="badge bg-primary mb-2"><?php echo $deal['category']; ?></span>
                    <h5 class="card-title"><?php echo htmlspecialchars($deal['title']); ?></h5>
                    <p class="card-text text-muted">
                        <i class="fas fa-store"></i> <?php echo htmlspecialchars($deal['business_name']); ?>
                    </p>
                    <?php if ($deal['location']): ?>
                    <p class="card-text text-muted small">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($deal['location']); ?>
                    </p>
                    <?php endif; ?>

                    <div class="mb-2">
                        <span class="text-muted text-decoration-line-through">
                            <?php echo formatPrice($deal['original_price']); ?>
                        </span><br>
                        <strong class="text-success fs-5">
                            <?php echo formatPrice($deal['discounted_price']); ?>
                        </strong>
                    </div>

                    <?php if ($deal['quantity_available'] > 0): ?>
                    <p class="text-success small mb-0">
                        <i class="fas fa-check-circle"></i> Quantity: <?php echo $deal['quantity_available']; ?>
                    </p>
                    <?php endif; ?>
                </div>

                <div class="card-footer bg-white">
                    <a href="../public/deal-details.php?id=<?php echo $deal['ad_id']; ?>"
                        class="btn btn-success btn-sm w-100">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
    font-size: 14px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
function filterDeals(category) {
    const deals = document.querySelectorAll('.deal-item');

    deals.forEach(deal => {
        if (category === 'all' || deal.dataset.category === category) {
            deal.style.display = 'block';
            // Add fade-in animation
            deal.style.animation = 'fadeIn 0.5s';
        } else {
            deal.style.display = 'none';
        }
    });

    // Update active button
    document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>

<?php include '../includes/footer.php'; ?>