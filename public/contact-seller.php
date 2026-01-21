<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['ad_id'])) {
    redirect('public/browse-deals.php');
}

$ad_id = intval($_GET['ad_id']);
$deal = getAdvertisement($ad_id);

if (!$deal) {
    redirect('public/browse-deals.php');
}

$page_title = 'Contact Seller - Discount Deals';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_name = sanitizeInput($_POST['sender_name']);
    $sender_email = sanitizeInput($_POST['sender_email']);
    $sender_phone = sanitizeInput($_POST['sender_phone']);
    $message = sanitizeInput($_POST['message']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO contact_messages (ad_id, sender_name, sender_email, sender_phone, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $ad_id, $sender_name, $sender_email, $sender_phone, $message);
    
    if ($stmt->execute()) {
        $success = 'Your message has been sent successfully! The seller will contact you soon.';
    } else {
        $error = 'An error occurred while sending the message. Please try again.';
    }
    
    $stmt->close();
    $conn->close();
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-envelope"></i> Contact Seller</h4>
                </div>
                <div class="card-body">
                    <!-- Deal Info -->
                    <div class="alert alert-info">
                        <h6><?php echo htmlspecialchars($deal['title']); ?></h6>
                        <p class="mb-0 small">Seller: <?php echo htmlspecialchars($deal['business_name']); ?></p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <a href="deal-details.php?id=<?php echo $ad_id; ?>">Back to advertisement</a>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="sender_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="sender_email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="sender_phone" class="form-control" placeholder="077-1234567">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="5" required
                                placeholder="Write your inquiry here..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                        <a href="deal-details.php?id=<?php echo $ad_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Help</h6>
                </div>
                <div class="card-body">
                    <p><strong>Direct Contact:</strong></p>

                    <?php if ($deal['business_phone']): ?>
                    <p>
                        <i class="fas fa-phone text-success"></i>
                        <a href="tel:<?php echo $deal['business_phone']; ?>">
                            <?php echo htmlspecialchars($deal['business_phone']); ?>
                        </a>
                    </p>
                    <?php endif; ?>

                    <?php if ($deal['business_email']): ?>
                    <p>
                        <i class="fas fa-envelope text-success"></i>
                        <a href="mailto:<?php echo $deal['business_email']; ?>">
                            <?php echo htmlspecialchars($deal['business_email']); ?>
                        </a>
                    </p>
                    <?php endif; ?>

                    <hr>

                    <p class="small text-muted">
                        <i class="fas fa-shield-alt"></i> Your information is secure. Only the seller can see it.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>