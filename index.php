<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'DS - Discount Deals';

// Latest deals
$latest_deals = getAllAdvertisements();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section -->
<section class="bg-success text-white py-5">
    <div class="container text-center">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo base_url(); ?>">
        </a>
        <h1 class="display-4">
            <img src="<?php echo asset_url('images/defaults/logo.png'); ?>" alt="Discount Deals Logo" class="img-fluid"
                style="height: 80px; width: auto;"
                onerror="this.src='<?php echo asset_url('images/defaults/placeholder.png'); ?>'">
            Discount Deals
        </h1>

        <p class="lead">Find affordable food options while reducing food waste.</p>
        <a href="<?php echo base_url('public/browse-deals.php'); ?>" class="btn btn-light btn-lg mt-3">
            Browse deals <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Our services</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-leaf fa-3x text-success mb-3"></i>
                        <h4>Reduce food waste</h4>
                        <p>Connect surplus food from sellers with customers.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-store fa-3x text-success mb-3"></i>
                        <h4>Support for businesses</h4>
                        <p>Low-cost promotion for small and medium-sized businesses.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-tags fa-3x text-success mb-3"></i>
                        <h4>Affordable prices</h4>
                        <p>Get quality food at lower prices.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Deals Section -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-4">Latest deals</h2>
        <div class="row">
            <?php if (empty($latest_deals)): ?>
            <div class="col-12 text-center">
                <p class="text-muted">No deals available right now.</p>
            </div>
            <?php else: ?>
            <?php foreach (array_slice($latest_deals, 0, 6) as $deal): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if ($deal['image_path']): ?>
                    <img src="<?php echo asset_url('images/uploads/' . $deal['image_path']); ?>" class="card-img-top"
                        alt="<?php echo htmlspecialchars($deal['title']); ?>" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-success"><?php echo $deal['discount_percentage']; ?>% OFF</span>
                        <h5 class="card-title mt-2"><?php echo htmlspecialchars($deal['title']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($deal['business_name']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted text-decoration-line-through">
                                    <?php echo formatPrice($deal['original_price']); ?>
                                </span><br>
                                <strong class="text-success">
                                    <?php echo formatPrice($deal['discounted_price']); ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo base_url('public/deal-details.php?id=' . $deal['ad_id']); ?>"
                            class="btn btn-primary btn-sm w-100">View details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (count($latest_deals) > 6): ?>
        <div class="text-center mt-4">
            <a href="<?php echo base_url('public/browse-deals.php'); ?>" class="btn btn-success">
                View more <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>