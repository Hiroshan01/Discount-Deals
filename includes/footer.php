<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <!-- Footer content -->
        <div class="row">
            <div class="col-md-4">
                <h5>About Discount Deals</h5>
                <p>"Our goal is to provide affordable food options while minimizing food waste."</p>
            </div>
            <div class="col-md-4">
                <h5>Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo base_url('public/about.php'); ?>" class="text-white">About Us</a></li>
                    <li><a href="<?php echo base_url('public/browse-deals.php'); ?>">Browse deals</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Connection</h5>
                <p>
                    <i class="fas fa-envelope"></i> info@discountdeals.lk<br>
                    <i class="fas fa-phone"></i> 011-1234567
                </p>
            </div>
            <p class="text-center">
                &copy; <?php echo date('Y'); ?> Discount Deals
            </p>
        </div>
</footer>

<!-- JavaScript files load -->
<script src="<?php echo asset_url('js/jquery-3.6.0.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?php echo asset_url('js/main.js'); ?>"></script>
</body>

</html>