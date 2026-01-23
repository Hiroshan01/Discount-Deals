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

<style>
/* Hero Section Enhancements */
.hero-section {
    background-image: url('<?php echo asset_url('images/defaults/hero-bg.png'); ?>');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    position: relative;
    min-height: 500px;
    display: flex;
    align-items: center;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;

    z-index: 1;
}

.hero-section .container {
    position: relative;
    z-index: 2;
}

.hero-title {
    font-weight: 900;
    color: #ffffff;
    text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
    animation: fadeInDown 1s ease-in-out;
}

.hero-subtitle {
    font-weight: 600;
    color: #f8f9fa;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    font-size: 1.4rem;
    animation: fadeInUp 1.2s ease-in-out;
}

.hero-button {
    font-weight: bold;
    padding: 15px 40px;
    border-radius: 50px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    animation: fadeInUp 1.4s ease-in-out;
}

.hero-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Features Section Enhancements */
.features-section {
    background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
}

.feature-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.feature-card .card-body {
    padding: 2.5rem 1.5rem;
}

.feature-icon {
    transition: all 0.3s ease;
}

.feature-card:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
}

/* Section Titles */
.section-title {
    font-weight: 800;
    color: #212529;
    position: relative;
    padding-bottom: 15px;
    margin-bottom: 3rem;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;

    border-radius: 2px;
}

/* Deal Cards Enhancement */
.deal-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.deal-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.deal-card img {
    transition: transform 0.3s ease;
}

.deal-card:hover img {
    transform: scale(1.05);
}

.discount-badge {
    font-size: 1rem;
    font-weight: bold;
    padding: 8px 15px;
    border-radius: 20px;
}

/* Partners Section */
.partners-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 4rem 0;
}

.partner-logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 120px;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.partner-logo-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.partner-logo-container img {
    max-width: 100%;
    max-height: 80px;
    object-fit: contain;

    opacity: 0.7;
    transition: all 0.3s ease;
}

.partner-logo-container:hover img {
    filter: grayscale(0%);
    opacity: 1;
}

/* Stats Section */
.stats-section {
    background: linear-gradient(135deg, #198754 0%, #0d6efd 100%);
    color: white;
    padding: 3rem 0;
}

.stat-item {
    text-align: center;
    padding: 1rem;
}

.stat-number {
    font-size: 3rem;
    font-weight: 900;
    display: block;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1.1rem;
    font-weight: 600;
    opacity: 0.95;
}
</style>

<!-- Hero Section -->
<section class="hero-section text-white py-5">
    <div class="container text-center">
        <h1 class="display-3 hero-title mb-4">
            <img src="<?php echo asset_url('images/defaults/logo.png'); ?>" alt="Discount Deals Logo"
                class="img-fluid mb-3" style="height: 100px; width: auto;"
                onerror="this.src='<?php echo asset_url('images/defaults/placeholder.png'); ?>'">
            <br>Discount Deals
        </h1>

        <p class="hero-subtitle mb-4">Find affordable food options while reducing food waste.</p>
        <p class="lead text-white-50 mb-5">Save money, save the planet - one deal at a time!</p>

        <a href="<?php echo base_url('public/browse-deals.php'); ?>" class="btn btn-light btn-lg hero-button">
            Browse Deals <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Active Deals</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Partner Stores</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <span class="stat-number">10K+</span>
                    <span class="stat-label">Happy Customers</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <span class="stat-number">70%</span>
                    <span class="stat-label">Average Savings</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <h2 class="section-title text-center">Our Services</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-leaf fa-4x text-success mb-4 feature-icon"></i>
                        <h4 class="fw-bold mb-3">Reduce Food Waste</h4>
                        <p class="text-muted">Connect surplus food from sellers with customers, helping the environment
                            one meal at a time.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-store fa-4x text-primary mb-4 feature-icon"></i>
                        <h4 class="fw-bold mb-3">Support Businesses</h4>
                        <p class="text-muted">Low-cost promotion platform for small and medium-sized businesses to reach
                            more customers.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-tags fa-4x text-warning mb-4 feature-icon"></i>
                        <h4 class="fw-bold mb-3">Affordable Prices</h4>
                        <p class="text-muted">Get quality food at significantly lower prices with discounts up to 70%
                            off.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Deals Section -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="section-title text-center">Latest Deals</h2>
        <div class="row">
            <?php if (empty($latest_deals)): ?>
            <div class="col-12 text-center">
                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                <p class="text-muted fs-5">No deals available right now. Check back soon!</p>
            </div>
            <?php else: ?>
            <?php foreach (array_slice($latest_deals, 0, 6) as $deal): ?>
            <div class="col-md-4 mb-4">
                <div class="card deal-card h-100">
                    <?php if ($deal['image_path']): ?>
                    <div style="overflow: hidden; height: 220px;">
                        <img src="<?php echo asset_url('images/uploads/' . $deal['image_path']); ?>"
                            class="card-img-top" alt="<?php echo htmlspecialchars($deal['title']); ?>"
                            style="height: 220px; object-fit: cover; width: 100%;">
                    </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-success discount-badge mb-3">
                            <i class="fas fa-percent me-1"></i><?php echo $deal['discount_percentage']; ?>% OFF
                        </span>
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($deal['title']); ?></h5>
                        <p class="card-text text-muted">
                            <i class="fas fa-store me-1"></i><?php echo htmlspecialchars($deal['business_name']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="text-muted text-decoration-line-through d-block"
                                    style="font-size: 0.9rem;">
                                    <?php echo formatPrice($deal['original_price']); ?>
                                </span>
                                <strong class="text-success fs-4">
                                    <?php echo formatPrice($deal['discounted_price']); ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3">
                        <a href="<?php echo base_url('public/deal-details.php?id=' . $deal['ad_id']); ?>"
                            class="btn btn-primary w-100 fw-bold">
                            View Details <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (count($latest_deals) > 6): ?>
        <div class="text-center mt-5">
            <a href="<?php echo base_url('public/browse-deals.php'); ?>" class="btn btn-success btn-lg px-5 fw-bold">
                View All Deals <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Partners Section -->

<section class="partners-section">
    <div class="container">
        <h2 class="section-title text-center">Our Trusted Partners</h2>
        <p class="text-center text-muted mb-5 fs-5">Join top brands in reducing food waste and saving money</p>

        <div class="row align-items-center">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-logo-container">
                    <?php if (file_exists('assets/images/partners/pns.jpg')): ?>
                    <img src="<?php echo asset_url('images/partners/pns.jpg'); ?>" alt="Perera & Sons">
                    <?php else: ?>
                    <div class="placeholder-logo">Perera & Sons</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-logo-container">
                    <?php if (file_exists('assets/images/partners/keells.png')): ?>
                    <img src="<?php echo asset_url('images/partners/keells.png'); ?>" alt="Keells">
                    <?php else: ?>
                    <div class="placeholder-logo">Keells</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-logo-container">
                    <?php if (file_exists('assets/images/partners/cargill.webp')): ?>
                    <img src="<?php echo asset_url('images/partners/cargill.webp'); ?>" alt="Cargills">
                    <?php else: ?>
                    <div class="placeholder-logo">Cargills</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-logo-container">
                    <?php if (file_exists('assets/images/partners/Arpico.jpg')): ?>
                    <img src="<?php echo asset_url('images/partners/Arpico.jpg'); ?>" alt="Arpico">
                    <?php else: ?>
                    <div class="placeholder-logo">Arpico</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-logo-container">
                    <?php if (file_exists('assets/images/partners/cake.png')): ?>
                    <img src="<?php echo asset_url('images/partners/cake.png'); ?>" alt="Food City">
                    <?php else: ?>
                    <div class="placeholder-logo">Cake House</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="partner-logo-container">
                    <?php if (file_exists('assets/images/partners/priya.png')): ?>
                    <img src="<?php echo asset_url('images/partners/priya.png'); ?>" alt="Food City">
                    <?php else: ?>
                    <div class="placeholder-logo">Priya Hotel</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-4">Ready to Start Saving?</h2>
        <p class="lead mb-4">Join thousands of smart shoppers who save money while helping the environment</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?php echo base_url('public/browse-deals.php'); ?>" class="btn btn-light btn-lg px-5 fw-bold">
                <i class="fas fa-search me-2"></i>Browse Deals
            </a>
            <a href="<?php echo base_url('auth/login.php'); ?>" class="btn btn-outline-light btn-lg px-5 fw-bold">
                <i class="fas fa-user-plus me-2"></i>Sign Up Free
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>