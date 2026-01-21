<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$page_title = 'Approve Advertisements - Admin';

$conn = getDBConnection();

// Handle approve/reject actions
if (isset($_POST['approve'])) {
    $ad_id = intval($_POST['ad_id']);
    $stmt = $conn->prepare("UPDATE advertisements SET status = 'approved' WHERE ad_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    
    // Log admin action
    $admin_id = $_SESSION['user_id'];
    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action_type, target_type, target_id, description) VALUES (?, 'approve', 'advertisement', ?, 'Advertisement approved')");
    $log_stmt->bind_param("ii", $admin_id, $ad_id);
    $log_stmt->execute();
    
    $_SESSION['success'] = 'Advertisement has been approved.';
}

if (isset($_POST['reject'])) {
    $ad_id = intval($_POST['ad_id']);
    $stmt = $conn->prepare("UPDATE advertisements SET status = 'rejected' WHERE ad_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    
    // Log admin action
    $admin_id = $_SESSION['user_id'];
    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action_type, target_type, target_id, description) VALUES (?, 'reject', 'advertisement', ?, 'Advertisement rejected')");
    $log_stmt->bind_param("ii", $admin_id, $ad_id);
    $log_stmt->execute();
    
    $_SESSION['success'] = 'Advertisement has been rejected.';
}

// Get pending advertisements
$pending_ads_query = "SELECT a.*, s.business_name, s.business_phone, s.business_email, u.full_name, u.email as seller_email 
                      FROM advertisements a 
                      INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
                      INNER JOIN users u ON s.user_id = u.user_id 
                      WHERE a.status = 'pending' 
                      ORDER BY a.created_at ASC";
$pending_ads = $conn->query($pending_ads_query)->fetch_all(MYSQLI_ASSOC);

// Get specific ad for review if ID provided
$review_ad = null;
if (isset($_GET['id'])) {
    $ad_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT a.*, s.business_name, s.business_phone, s.business_email, u.full_name, u.email as seller_email 
                           FROM advertisements a 
                           INNER JOIN seller_profiles s ON a.seller_id = s.seller_id 
                           INNER JOIN users u ON s.user_id = u.user_id 
                           WHERE a.ad_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $review_ad = $stmt->get_result()->fetch_assoc();
}

$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-check-circle"></i> Approve Advertisements</h2>
            <p class="text-muted">Review pending advertisements and approve or reject them</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($review_ad): ?>
    <!-- Review Advertisement Detail -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Review Advertisement</h5>
                </div>
                <div class="card-body">
                    <?php if ($review_ad['image_path']): ?>
                    <img src="<?php echo asset_url('images/uploads/' . $review_ad['image_path']); ?>"
                        class="img-fluid mb-3" style="max-height: 300px;">
                    <?php endif; ?>

                    <h4><?php echo htmlspecialchars($review_ad['title']); ?></h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Category:</strong>
                            <span class="badge bg-info"><?php echo $review_ad['category']; ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Location:</strong>
                            <?php echo htmlspecialchars($review_ad['location']); ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Original Price:</strong><br>
                            <span class="text-muted"><?php echo formatPrice($review_ad['original_price']); ?></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Discounted Price:</strong><br>
                            <span
                                class="text-success fs-5"><?php echo formatPrice($review_ad['discounted_price']); ?></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Discount Percentage:</strong><br>
                            <span class="badge bg-danger fs-6"><?php echo round($review_ad['discount_percentage']); ?>%
                                OFF</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p><?php echo nl2br(htmlspecialchars($review_ad['description'])); ?></p>
                    </div>

                    <?php if ($review_ad['quantity_available']): ?>
                    <p><strong>Quantity:</strong> <?php echo $review_ad['quantity_available']; ?></p>
                    <?php endif; ?>

                    <?php if ($review_ad['expiry_date']): ?>
                    <p><strong>Expiry Date:</strong> <?php echo $review_ad['expiry_date']; ?></p>
                    <?php endif; ?>

                    <?php if ($review_ad['external_url']): ?>
                    <p><strong>External URL:</strong>
                        <a href="<?php echo htmlspecialchars($review_ad['external_url']); ?>" target="_blank">
                            <?php echo htmlspecialchars($review_ad['external_url']); ?>
                        </a>
                    </p>
                    <?php endif; ?>

                    <hr>

                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="ad_id" value="<?php echo $review_ad['ad_id']; ?>">
                        <button type="submit" name="approve" class="btn btn-success btn-lg me-2">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="submit" name="reject" class="btn btn-danger btn-lg me-2">
                            <i class="fas fa-times"></i> Reject
                        </button>
                        <a href="approve-ads.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-store"></i> Seller Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Business Name:</strong><br>
                        <?php echo htmlspecialchars($review_ad['business_name']); ?></p>

                    <p><strong>Seller:</strong><br>
                        <?php echo htmlspecialchars($review_ad['full_name']); ?></p>

                    <p><strong>Email:</strong><br>
                        <a href="mailto:<?php echo $review_ad['seller_email']; ?>">
                            <?php echo htmlspecialchars($review_ad['seller_email']); ?>
                        </a>
                    </p>

                    <?php if ($review_ad['business_phone']): ?>
                    <p><strong>Phone:</strong><br>
                        <a href="tel:<?php echo $review_ad['business_phone']; ?>">
                            <?php echo htmlspecialchars($review_ad['business_phone']); ?>
                        </a>
                    </p>
                    <?php endif; ?>

                    <p><strong>Submitted On:</strong><br>
                        <?php echo date('Y-m-d H:i', strtotime($review_ad['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pending Advertisements List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Pending Advertisements (<?php echo count($pending_ads); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_ads)): ?>
                    <div class="alert alert-info text-center">
                        There are no advertisements waiting for approval.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Seller</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_ads as $ad): ?>
                                <tr>
                                    <td>
                                        <?php if ($ad['image_path']): ?>
                                        <img src="<?php echo asset_url('images/uploads/' . $ad['image_path']); ?>"
                                            alt="Ad"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #ddd; border-radius: 5px;">
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($ad['title']); ?></td>
                                    <td><?php echo htmlspecialchars($ad['business_name']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $ad['category']; ?></span></td>
                                    <td><?php echo formatPrice($ad['discounted_price']); ?></td>
                                    <td><span
                                            class="badge bg-danger"><?php echo round($ad['discount_percentage']); ?>%</span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($ad['created_at'])); ?></td>
                                    <td>
                                        <a href="?id=<?php echo $ad['ad_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>