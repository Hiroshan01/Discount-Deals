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
<style>
.btn-facebook {
    background: linear-gradient(135deg, #1877F2 0%, #166FE5 100%);
    border-color: #1877F2;
    color: white;
    transition: all 0.3s ease;
}

.btn-facebook:hover {
    background: linear-gradient(135deg, #166FE5 0%, #1458C3 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(24, 119, 242, 0.4);
}
</style>

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

            <!-- Advertiser Social Media Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-globe me-2"></i>Visit Seller Pages</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php if (!empty($deal['facebook_url'])): ?>
                        <div class="col-12">
                            <a href="<?php echo htmlspecialchars($deal['facebook_url']); ?>" target="_blank"
                                class="btn btn-facebook w-100 p-3 text-start shadow-sm mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fab fa-facebook-f fa-lg me-3 text-white"></i>
                                    <div>
                                        <div class="fw-bold text-white">Facebook Page</div>
                                        <small class="opacity-90">Visit business page</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deal['external_url'])): ?>
                        <div class="col-12">
                            <a href="<?php echo htmlspecialchars($deal['external_url']); ?>" target="_blank"
                                class="btn btn-outline-primary w-100 p-3 text-start shadow-sm mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-globe fa-lg me-3"></i>
                                    <div>
                                        <div class="fw-bold">Official Website</div>
                                        <small>Visit business website</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deal['phone'])): ?>
                        <div class="col-12">
                            <a href="https://wa.me/94<?php echo preg_replace('/[^0-9]/', '', $deal['phone']); ?>?text=<?php echo urlencode($deal['title'] . ' - Check this deal!'); ?>"
                                target="_blank" class="btn btn-success w-100 p-3 text-start shadow-sm mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fab fa-whatsapp fa-lg me-3"></i>
                                    <div>
                                        <div class="fw-bold">WhatsApp Chat</div>
                                        <small><?php echo htmlspecialchars($deal['phone']); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($deal['instagram_url'])): ?>
                        <div class="col-12">
                            <a href="<?php echo htmlspecialchars($deal['instagram_url']); ?>" target="_blank"
                                class="btn btn-outline-danger w-100 p-3 text-start shadow-sm">
                                <div class="d-flex align-items-center">
                                    <i class="fab fa-instagram fa-lg me-3"></i>
                                    <div>
                                        <div class="fw-bold">Instagram</div>
                                        <small>Follow on Instagram</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if (empty($deal['facebook_url']) && empty($deal['website_url']) && empty($deal['whatsapp_number']) && empty($deal['instagram_url'])): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No social media links provided</p>
                        </div>
                        <?php endif; ?>
                    </div>
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