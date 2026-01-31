<?php
// admin/manage-users.php - Manage Users
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$page_title = 'Manage Users - Admin';

// Filter
$user_type_filter = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$conn = getDBConnection();

// Build query
// Build query
$query = "
    SELECT u.*, sp.nic_number, sp.br_number
    FROM users u
    LEFT JOIN seller_profiles sp ON u.user_id = sp.user_id
    WHERE 1=1
";

$params = [];
$types = '';

if ($user_type_filter) {
    $query .= " AND user_type = ?";
    $params[] = $user_type_filter;
    $types .= 's';
}

if ($search) {
    $query .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle user status update
if (isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $new_status = sanitizeInput($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
    
    $_SESSION['success'] = 'User status has been updated.';
    redirect('admin/manage-users.php');
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Do not allow deleting admin users
    $check = $conn->query("SELECT user_type FROM users WHERE user_id = $user_id")->fetch_assoc();
    if ($check && $check['user_type'] != 'admin') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $_SESSION['success'] = 'User has been deleted.';
    } else {
        $_SESSION['error'] = 'Admin users cannot be deleted.';
    }
    redirect('admin/manage-users.php');
}

$stmt->close();
$conn->close();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-users"></i> Manage Users</h2>
            <p class="text-muted">View and manage all users</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filter & Search -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">User Type</label>
                            <select name="type" class="form-select">
                                <option value="">All</option>
                                <option value="buyer" <?php echo $user_type_filter == 'buyer' ? 'selected' : ''; ?>>
                                    Buyers
                                </option>
                                <option value="seller" <?php echo $user_type_filter == 'seller' ? 'selected' : ''; ?>>
                                    Sellers
                                </option>
                                <option value="admin" <?php echo $user_type_filter == 'admin' ? 'selected' : ''; ?>>
                                    Admins
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by name, email or username..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Users (<?php echo count($users); ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>NIC</th>
                                    <th>BR Number</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nic_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user['br_number'] ?? 'N/A'); ?></td>


                                    <td>
                                        <?php
                                            $badge_class = [
                                                'buyer' => 'bg-info',
                                                'seller' => 'bg-success',
                                                'admin' => 'bg-danger'
                                            ];
                                            ?>
                                        <span
                                            class="badge <?php echo $badge_class[$user['user_type']] ?? 'bg-secondary'; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <select name="status" class="form-select form-select-sm"
                                                onchange="this.form.submit()"
                                                <?php echo $user['user_type'] == 'admin' ? 'disabled' : ''; ?>>
                                                <option value="active"
                                                    <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>
                                                    Active
                                                </option>
                                                <option value="inactive"
                                                    <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>
                                                    Inactive
                                                </option>
                                                <option value="suspended"
                                                    <?php echo $user['status'] == 'suspended' ? 'selected' : ''; ?>>
                                                    Suspended
                                                </option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['user_type'] != 'admin'): ?>
                                        <a href="?delete=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>