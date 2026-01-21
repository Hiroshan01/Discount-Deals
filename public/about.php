<?php
// public/about.php - About Us Page
require_once '../config/config.php';

$page_title = 'About Us - Discount Deals';

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-md-12 text-center">
            <h1><i class="fas fa-info-circle text-success"></i> About Us</h1>
            <p class="lead text-muted">A platform serving the community while reducing food waste</p>
        </div>
    </div>

    <!-- Mission & Vision -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="text-success"><i class="fas fa-bullseye"></i> Our Mission</h3>
                    <p>
                        Discount Deals is an interactive online platform that connects food sellers
                        and budget-conscious consumers. Our main goal is to reduce food waste
                        and provide affordable food options.
                    </p>
                    <p>
                        We believe that everyone should have access to quality food at affordable
                        prices. Also, small and medium-sized businesses need a low-cost platform
                        to sell their surplus products.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="text-success"><i class="fas fa-eye"></i> Our Vision</h3>
                    <p>
                        To become Sri Lanka's leading food discount platform and create a
                        community-driven movement to significantly reduce food waste.
                    </p>
                    <ul>
                        <li>Reduce daily food waste</li>
                        <li>Provide affordable food to the community</li>
                        <li>Give small businesses a digital presence</li>
                        <li>Promote sustainable consumption</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="row mb-5">
        <div class="col-md-12">
            <h2 class="text-center mb-4"><i class="fas fa-cogs"></i> How It Works</h2>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-store fa-4x text-success"></i>
                    </div>
                    <h5>1. Sellers Register</h5>
                    <p>
                        Restaurants, bakeries, and food shops register their businesses and
                        advertise surplus or soon-to-expire food at discounted prices.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-search fa-4x text-success"></i>
                    </div>
                    <h5>2. Customers Discover</h5>
                    <p>
                        Users can search for deals by category, location, and price range.
                        All information is available in one place.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-handshake fa-4x text-success"></i>
                    </div>
                    <h5>3. Connect Directly</h5>
                    <p>
                        Customers can redirect to the seller's website or contact them directly.
                        We don't intervene in transactions.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Features -->
    <div class="row mb-5">
        <div class="col-md-12">
            <h2 class="text-center mb-4"><i class="fas fa-star"></i> Key Features</h2>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5><i class="fas fa-leaf text-success"></i> Eco-Friendly</h5>
                    <p>
                        Helps protect the environment by reducing food waste. Tons of food
                        are wasted daily, and our platform helps prevent that.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5><i class="fas fa-dollar-sign text-success"></i> Low Cost</h5>
                    <p>
                        No registration fees or monthly charges for sellers. They can advertise
                        their products through a simple, free platform.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5><i class="fas fa-users text-success"></i> For the Community</h5>
                    <p>
                        We believe in community-built solutions for the community. We help
                        small businesses grow and customers save money.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5><i class="fas fa-mobile-alt text-success"></i> Easy to Use</h5>
                    <p>
                        Our platform has a simple, easy-to-use interface that works well on
                        both mobile and desktop.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-5 text-center">
        <div class="col-md-12 mb-4">
            <h2><i class="fas fa-chart-line"></i> Our Impact</h2>
        </div>

        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h2>500+</h2>
                    <p>Registered Sellers</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h2>2000+</h2>
                    <p>Discount Listings</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h2>50,000+</h2>
                    <p>Happy Customers</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h2>10 Tons</h2>
                    <p>Food Waste Prevented</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team -->
    <div class="row mb-5">
        <div class="col-md-12 text-center mb-4">
            <h2><i class="fas fa-users"></i> Our Team</h2>
            <p class="text-muted">Students who created this project</p>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-user-circle fa-4x text-success mb-3"></i>
                    <h6>Arosha Perera</h6>
                    <p class="small text-muted">Project Leader</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-user-circle fa-4x text-success mb-3"></i>
                    <h6>Nelushika De Costa</h6>
                    <p class="small text-muted">Developer</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-user-circle fa-4x text-success mb-3"></i>
                    <h6>Nimedya Dananjanee</h6>
                    <p class="small text-muted">Designer</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-user-circle fa-4x text-success mb-3"></i>
                    <h6>Ashani Lakshika</h6>
                    <p class="small text-muted">Tester</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact -->
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-success text-white text-center">
                <div class="card-body">
                    <h3><i class="fas fa-envelope"></i> Contact Us</h3>
                    <p class="lead">
                        Have questions? Our team is ready to help you!
                    </p>
                    <p>
                        <i class="fas fa-envelope"></i> info@discountdeals.lk<br>
                        <i class="fas fa-phone"></i> 011-1234567
                    </p>
                    <a href="browse-deals.php" class="btn btn-light btn-lg mt-3">
                        <i class="fas fa-shopping-bag"></i> Start Browsing Deals
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>