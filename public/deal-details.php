<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('public/browse-deals.php');
}

$ad_id = intval($_GET['id']);
$deal = getAdvertisement($ad_id);

if (!$deal) {
    redirect('public/browse-deals.php');
}

// View count update
$conn = getDBConnection();
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_id = $_SESSION['user_id'] ?? null;

// View record  add
$stmt = $conn->prepare("INSERT INTO ad_views (ad_id, user_id, ip_address) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $ad_id, $user_id, $ip_address);
$stmt->execute();

// View count
$stmt = $conn->prepare("UPDATE advertisements SET view_count = view_count + 1 WHERE ad_id = ?");
$stmt->bind_param("i", $ad_id);
$stmt->execute();

$stmt->close();
$conn->close();

$page_title = htmlspecialchars($deal['title']) . ' - Discount Deals';

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="browse-deals.php">Browser</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($deal['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <div class="card mb-4">
                <?php if ($deal['image_path']): ?>
                <img src="<?php echo asset_url('images/uploads/' . $deal['image_path']); ?>" class="card-img-top"
                    alt="<?php echo htmlspecialchars($deal['title']); ?>"
                    style="max-height: 400px; object-fit: contain;">
                <?php endif; ?>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2><?php echo htmlspecialchars($deal['title']); ?></h2>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($deal['category']); ?></span>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-danger fs-5">
                                <?php echo round($deal['discount_percentage']); ?>% OFF
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="text-muted text-decoration-line-through fs-5">
                            <?php echo formatPrice($deal['original_price']); ?>
                        </span><br>
                        <strong class="text-success fs-3">
                            <?php echo formatPrice($deal['discounted_price']); ?>
                        </strong>
                        <p class="text-muted small mt-2">
                            Your Saving:
                            <?php echo formatPrice($deal['original_price'] - $deal['discounted_price']); ?>
                        </p>
                    </div>

                    <hr>

                    <h5>Details</h5>
                    <p><?php echo nl2br(htmlspecialchars($deal['description'])); ?></p>

                    <hr>

                    <div class="row">
                        <?php if ($deal['quantity_available'] > 0): ?>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-box"></i> Quantity:</strong><br>
                            <span class="text-success"><?php echo $deal['quantity_available']; ?> items</span>
                        </div>
                        <?php endif; ?>

                        <?php if ($deal['location']): ?>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-map-marker-alt"></i> Location:</strong><br>
                            <?php echo htmlspecialchars($deal['location']); ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($deal['expiry_date']): ?>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-calendar"></i> Expire:</strong><br>
                            <span
                                class="text-danger"><?php echo date('Y-m-d', strtotime($deal['expiry_date'])); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-eye"></i> View:</strong><br>
                            <?php echo $deal['view_count']; ?> views
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Seller Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-store"></i> Seller Info</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo htmlspecialchars($deal['business_name']); ?></h6>

                    <?php if ($deal['business_phone']): ?>
                    <p class="mb-2">
                        <i class="fas fa-phone"></i>
                        <a href="tel:<?php echo $deal['business_phone']; ?>">
                            <?php echo htmlspecialchars($deal['business_phone']); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($deal['phone'])): ?>
                    <p class="mb-2">
                        <i class="fas fa-mobile-alt"></i>
                        <a href="tel:<?php echo $deal['phone']; ?>">
                            <?php echo htmlspecialchars($deal['phone']); ?>
                        </a>
                        <small class="text-muted">(For Advertisement)</small>
                    </p>
                    <?php endif; ?>

                    <?php if ($deal['business_email']): ?>
                    <p class="mb-2">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo $deal['business_email']; ?>">
                            <?php echo htmlspecialchars($deal['business_email']); ?>
                        </a>
                    </p>
                    <?php endif; ?>

                    <hr>

                    <!-- Action Buttons -->
                    <?php if ($deal['external_url']): ?>
                    <a href="<?php echo htmlspecialchars($deal['external_url']); ?>" target="_blank"
                        class="btn btn-primary w-100 mb-2" onclick="incrementClickCount(<?php echo $ad_id; ?>)">
                        <i class="fas fa-shopping-cart"></i> Now Order
                    </a>
                    <?php endif; ?>

                    <a href="contact-seller.php?ad_id=<?php echo $ad_id; ?>" class="btn btn-success w-100">
                        <i class="fas fa-envelope"></i>Call Seller
                    </a>
                </div>
            </div>

            <!-- Share Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-share-alt"></i> Distribute</h6>
                </div>
                <div class="card-body">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(base_url('public/deal-details.php?id=' . $ad_id)); ?>"
                        target="_blank" class="btn btn-primary btn-sm me-2">
                        <i class="fab fa-facebook"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(base_url('public/deal-details.php?id=' . $ad_id)); ?>&text=<?php echo urlencode($deal['title']); ?>"
                        target="_blank" class="btn btn-info btn-sm">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function incrementClickCount(adId) {
    fetch('../api/increment-click.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'ad_id=' + adId
    });
}
</script>

<?php include '../includes/footer.php'; ?>