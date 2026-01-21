<?php


require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';


$page_title = 'Browse Deals - Discount Deals';


// Filters
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999;
$sort_by = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'latest';


// Build query
$conn = getDBConnection();
$query = "SELECT a.*, s.business_name, s.logo_image, s.business_phone, s.business_email 
          FROM advertisements a 
          INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
          WHERE a.status = 'approved'";


$params = [];
$types = '';


if ($category) {
    $query .= " AND a.category = ?";
    $params[] = $category;
    $types .= 's';
}


if ($location) {
    $query .= " AND a.location LIKE ?";
    $params[] = '%' . $location . '%';
    $types .= 's';
}


if ($min_price > 0 || $max_price < 999999) {
    $query .= " AND a.discounted_price BETWEEN ? AND ?";
    $params[] = $min_price;
    $params[] = $max_price;
    $types .= 'dd';
}


// Sorting
switch ($sort_by) {
    case 'price_low':
        $query .= " ORDER BY a.discounted_price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY a.discounted_price DESC";
        break;
    case 'discount':
        $query .= " ORDER BY (a.original_price - a.discounted_price) DESC";
        break;
    default:
        $query .= " ORDER BY a.created_at DESC";
}


$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$deals = $result->fetch_all(MYSQLI_ASSOC);


$stmt->close();
$conn->close();


include '../includes/header.php';
include '../includes/navbar.php';
?>


<div class="container mt-4 mb-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-tags"></i> Browse All Deals</h2>
            <p class="text-muted">Find affordable food options near you</p>
        </div>
    </div>


    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <option value="meals" <?php echo $category == 'meals' ? 'selected' : ''; ?>>Meals
                                </option>
                                <option value="bakery" <?php echo $category == 'bakery' ? 'selected' : ''; ?>>Bakery
                                </option>
                                <option value="beverages" <?php echo $category == 'beverages' ? 'selected' : ''; ?>>
                                    Beverages
                                </option>
                                <option value="desserts" <?php echo $category == 'desserts' ? 'selected' : ''; ?>>
                                    Desserts</option>
                                <option value="snacks" <?php echo $category == 'snacks' ? 'selected' : ''; ?>>Snacks
                                </option>
                                <option value="other" <?php echo $category == 'other' ? 'selected' : ''; ?>>Other
                                </option>
                            </select>
                        </div>


                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Colombo"
                                value="<?php echo htmlspecialchars($location); ?>">
                        </div>


                        <div class="col-md-2">
                            <label class="form-label">Min Price</label>
                            <input type="number" name="min_price" class="form-control" placeholder="0"
                                value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                        </div>


                        <div class="col-md-2">
                            <label class="form-label">Max Price</label>
                            <input type="number" name="max_price" class="form-control" placeholder="10000"
                                value="<?php echo $max_price < 999999 ? $max_price : ''; ?>">
                        </div>


                        <div class="col-md-2">
                            <label class="form-label">Sort By</label>
                            <select name="sort" class="form-select">
                                <option value="latest" <?php echo $sort_by == 'latest' ? 'selected' : ''; ?>>Latest
                                </option>
                                <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>
                                    Price: Low to High</option>
                                <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>
                                    Price: High to Low</option>
                                <option value="discount" <?php echo $sort_by == 'discount' ? 'selected' : ''; ?>>Highest
                                    Discount</option>
                            </select>
                        </div>


                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="browse-deals.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Results -->
    <div class="row mb-3">
        <div class="col-md-12">
            <p class="text-muted">Found <?php echo count($deals); ?> deals</p>
        </div>
    </div>


    <!-- Deals Grid -->
    <div class="row">
        <?php if (empty($deals)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No deals found. Please try adjusting your filters.
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($deals as $deal): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 deal-card">
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
                    <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($deal['category']); ?></span>
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
                        <span class="price-original">
                            <?php echo formatPrice($deal['original_price']); ?>
                        </span><br>
                        <strong class="price-discounted">
                            <?php echo formatPrice($deal['discounted_price']); ?>
                        </strong>
                    </div>


                    <?php if ($deal['quantity_available'] > 0): ?>
                    <p class="text-success small">
                        <i class="fas fa-check-circle"></i> Available: <?php echo $deal['quantity_available']; ?>
                    </p>
                    <?php endif; ?>
                </div>


                <div class="card-footer bg-white">
                    <a href="deal-details.php?id=<?php echo $deal['ad_id']; ?>" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<?php include '../includes/footer.php'; ?>