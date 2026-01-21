<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Registration - Discount Deals';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $user_type = sanitizeInput($_POST['user_type']);
    
    // Validation
    if ($password !== $confirm_password) {
        $error = 'Password is not Match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $user_id = registerUser($username, $email, $password, $full_name, $phone, $user_type);
        
        if ($user_id) {
            // Seller Profile Create
            if ($user_type === 'seller' && !empty($_POST['business_name'])) {
                $conn = getDBConnection();
                $business_name = sanitizeInput($_POST['business_name']);
                $business_type = sanitizeInput($_POST['business_type']);
                
                $stmt = $conn->prepare("INSERT INTO seller_profiles (user_id, business_name, business_type) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $business_name, $business_type);
                $stmt->execute();
                $stmt->close();
                $conn->close();
            }
            
            $success = 'Registration Successful! You can now login.';
        } else {
            $error = 'Registration Failed.';
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> Registration</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" id="registerForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User Name *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telephone Number</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Letters must be at least 6 characters long.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Acount Type *</label>
                            <select name="user_type" class="form-select" id="userType" required>
                                <option value="buyer">(Buyer)</option>
                                <option value="seller">(Seller)</option>
                            </select>
                        </div>

                        <!-- Seller Info fields -->
                        <div id="sellerFields" style="display: none;">
                            <hr>
                            <h5>Business Information</h5>

                            <div class="mb-3">
                                <label class="form-label">Business Name *</label>
                                <input type="text" name="business_name" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Business Type *</label>
                                <select name="business_type" class="form-select">
                                    <option value="restaurant">(Restaurant)</option>
                                    <option value="bakery">(Bakery)</option>
                                    <option value="cafe">(Cafe)</option>
                                    <option value="food_stall">(Food Stall)</option>
                                    <option value="other">(Other)</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-user-plus"></i> Registration
                        </button>
                    </form>

                    <hr>
                    <p class="text-center mb-0">
                        Do you have already an account?
                        <a href="login.php">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// When chnage user type seller info hide and show
document.getElementById('userType').addEventListener('change', function() {
    const sellerFields = document.getElementById('sellerFields');
    if (this.value === 'seller') {
        sellerFields.style.display = 'block';
    } else {
        sellerFields.style.display = 'none';
    }
});
</script>

<?php include '../includes/footer.php'; ?>


<?php
// auth/logout.php
require_once '../config/config.php';

session_destroy();
redirect('index.php');
?>