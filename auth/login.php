<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Login - DS- Discount Deals';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    $result = loginUser($email, $password);
    
    if ($result['success']) {
        // User type redirect
        if ($result['user_type'] === 'admin') {
            redirect('admin/dashboard.php');
        } elseif ($result['user_type'] === 'seller') {
            redirect('seller/dashboard.php');
        } else {
            redirect('user/dashboard.php');
        }
    } else {
        $error = $result['message'];
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Login</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>

                    <hr>
                    <p class="text-center mb-0">
                        Do you have an account?
                        <a href="register.php">Registration</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>