<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Search - Discount Deals';
$search_query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$results = [];

if ($search_query) {
    $conn = getDBConnection();
    
    $query = "SELECT a.*, s.business_name 
              FROM advertisements a 
              INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
              WHERE a.status = 'approved' 
              AND (a.title LIKE ? OR a.description LIKE ? OR s.business_name LIKE ?)
              ORDER BY a.created_at DESC";
    
    $search_param = '%' . $search_query . '%';
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $conn->close();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2><i class="fas fa-search"></i> Search</h2>

            <!-- Search Form -->
            <form method="GET" action="" class="mb-4">
                <div class="input-group input-group-lg">
                    <input type="text" name="q" class="form-control" placeholder="Advertisement, finding business..."
                        value="<?php echo htmlspecialchars($search_query); ?>" required>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-search"></i> search
                    </button>
                </div>
            </form>

            <?php if ($search_query): ?>
            <p class="text-muted">
                "<?php echo htmlspecialchars($search_query); ?>" For result<?php echo count($results); ?>ක්
            </p>

            <?php if (empty($results)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Not found search result.
            </div>
            <?php else: ?>
            <div class="list-group">
                <?php foreach ($results as $deal): ?>
                <a href="deal-details.php?id=<?php echo $deal['ad_id']; ?>"
                    class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($deal['title']); ?></h5>
                        <span class="badge bg-success">
                            <?php echo round($deal['discount_percentage']); ?>% OFF
                        </span>
                    </div>
                    <p class="mb-1 text-muted">
                        <i class="fas fa-store"></i> <?php echo htmlspecialchars($deal['business_name']); ?>
                    </p>
                    <div>
                        <span class="text-muted text-decoration-line-through">
                            <?php echo formatPrice($deal['original_price']); ?>
                        </span>
                        <strong class="text-success ms-2">
                            <?php echo formatPrice($deal['discounted_price']); ?>
                        </strong>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>