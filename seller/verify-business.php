<?php
// seller/verify-business.php - Business Verification
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('auth/login.php');
}

$page_title = 'Business Verification - Discount Deals';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$conn = getDBConnection();

// Get seller profile
$stmt = $conn->prepare("SELECT * FROM seller_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $br_number = sanitizeInput($_POST['br_number']);
    $nic_number = sanitizeInput($_POST['nic_number']);
    
    // Validation
    if (empty($br_number) && empty($nic_number)) {
        $error = 'BR number or NIC number is required.';
    } else {
        // Update seller profile
        $stmt = $conn->prepare("UPDATE seller_profiles 
                                   SET br_number = ?, nic_number = ?, is_verified = TRUE 
                                   WHERE user_id = ?");
        $stmt->bind_param("ssi", $br_number, $nic_number, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Business information verified successfully! Now you can self-approve your advertisements.';
            
            // Refresh seller data
            $stmt = $conn->prepare("SELECT * FROM seller_profiles WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $seller = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'An error occurred while updating.';
        }
    }
}

$stmt->close();
$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt"></i> Business Verification
                    </h4>
                </div>
                <div class="card-body">

                    <?php if ($seller['is_verified']): ?>
                    <!-- Already Verified -->
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Your business is verified!</h5>
                        <p>Now you can self-approve your advertisements.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>BR Number:</strong>
                                <?php echo $seller['br_number'] ? htmlspecialchars($seller['br_number']) : 'N/A'; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>NIC Number:</strong>
                                <?php echo $seller['nic_number'] ? htmlspecialchars($seller['nic_number']) : 'N/A'; ?>
                            </p>
                        </div>
                    </div>

                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Go to Dashboard
                    </a>

                    <?php else: ?>
                    <!-- Not Verified - Show Form -->
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Business Verification Required</h5>
                        <p>BR or NIC number is required to self-approve your advertisements.</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">
                                BR Number (Business Registration Number)
                            </label>
                            <input type="text" name="br_number" class="form-control" placeholder="BR/XXXXX/XXXX"
                                value="<?php echo htmlspecialchars($seller['br_number'] ?? ''); ?>">
                            <small class="text-muted">Business Registration Number</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                NIC Number (National Identity Card)
                            </label>
                            <input type="text" name="nic_number" class="form-control"
                                placeholder="XXXXXXXXXV or XXXXXXXXXXXX"
                                value="<?php echo htmlspecialchars($seller['nic_number'] ?? ''); ?>">
                            <small class="text-muted">National Identity Card Number</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Enter at least one of BR number or NIC number.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Verify Now
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                Do it Later
                            </a>
                        </div>
                    </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>