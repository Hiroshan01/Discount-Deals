<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo base_url(); ?>">
            <img src="<?php echo asset_url('images/defaults/logo.png'); ?>" alt="Discount Deals Logo" height="32"
                onerror="this.src='<?php echo asset_url('images/defaults/placeholder.png'); ?>'">
            <span>Discount Deals</span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- User login check -->
                <?php if (isLoggedIn()): ?>

                <!-- User Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> My Account
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <!-- Seller check -->
                        <?php if (isSeller()): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo base_url('seller/dashboard.php'); ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>

                        <!-- Admin check -->
                        <?php elseif (isAdmin()): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo base_url('admin/dashboard.php'); ?>">
                                <i class="fas fa-cog"></i> Admin Panel
                            </a>
                        </li>

                        <!-- Normal user -->
                        <?php else: ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo base_url('user/dashboard.php'); ?>">
                                <i class="fas fa-user"></i> My Account
                            </a>
                        </li>
                        <?php endif; ?>

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo base_url('auth/logout.php'); ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- If not logged in -->
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('auth/login.php'); ?>">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('auth/register.php'); ?>">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>