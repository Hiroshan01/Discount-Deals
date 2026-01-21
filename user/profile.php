<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$page_title = 'Profile - Discount Deals';
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$conn = getDBConnection();

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = sanitizeInput($_POST['full_name']);
        $phone = sanitizeInput($_POST['phone']);
        $email = sanitizeInput($_POST['email']);
        
        // Check if email already exists (for other users)
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            $error = 'This email address is already in use.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, email = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $full_name, $phone, $email, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Profile updated successfully!';
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'An error occurred while updating the profile.';
            }
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current_password, $user['password_hash'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $new_hash, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = 'Password changed successfully!';
                    } else {
                        $error = 'An error occurred while changing the password.';
                    }
                } else {
                    $error = 'The new password must be at least 6 characters long.';
                }
            } else {
                $error = 'The new passwords do not match.';
            }
        } else {
            $error = 'The current password is incorrect.';
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
        <div class="col-md-3">
            <!-- Sidebar -->
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-5x text-success mb-3"></i>
                    <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge bg-success"><?php echo ucfirst($user['user_type']); ?></span>
                </div>
            </div>

            <div class="card mt-3">
                <div class="list-group list-group-flush">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="view-deals.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-list"></i> View Deals
                    </a>
                    <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control"
                                    value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required
                                    value="<?php echo htmlspecialchars($user['full_name']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required
                                    value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control"
                                    value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Account Created On</label>
                            <input type="text" class="form-control"
                                value="<?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?>" disabled>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-key"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <small class="text-muted">Must be at least 6 characters long</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>

                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-lock"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>