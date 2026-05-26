<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$agency_id = $_SESSION['user_id'];
$message   = '';
$msg_type  = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $package_id = intval($_POST['package_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM package WHERE packID = :id AND agencyID = :agency_id");
        $stmt->execute(['id' => $package_id, 'agency_id' => $agency_id]);
        if ($stmt->rowCount() > 0) {
            $message  = "Package successfully removed.";
            $msg_type = "success";
        } else {
            $message  = "Error: Package could not be deleted or unauthorized access.";
            $msg_type = "error";
        }
    } catch (\PDOException $e) {
        $message  = "Database error: " . $e->getMessage();
        $msg_type = "error";
    }
}

// Fetch packages
$stmt = $pdo->prepare("SELECT * FROM package WHERE agencyId = :agency_id ORDER BY packID DESC");
$stmt->execute(['agency_id' => $agency_id]);
$my_packages = $stmt->fetchAll();

// Stats
$total_packages = count($my_packages);

$rev_stmt = $pdo->prepare("SELECT COUNT(*) as total_bookings, SUM(p.price) as total_revenue FROM booking b JOIN package p ON b.packageId = p.packID WHERE p.agencyId = :agency_id");
$rev_stmt->execute(['agency_id' => $agency_id]);
$stats = $rev_stmt->fetch();

$avg_stmt = $pdo->prepare("SELECT AVG(r.rating) as avg_rating FROM review r JOIN package p ON r.packageID = p.packID WHERE p.agencyId = :agency_id");
$avg_stmt->execute(['agency_id' => $agency_id]);
$avg = $avg_stmt->fetch();

include '../components/header.php';
?>

    <link rel="stylesheet" href="css/dashboard.css">

    <div class="agency-container">

        <?php if (!empty($message)): ?>
            <div class="notification-box <?php echo $msg_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="agency-header">
            <div>
                <h2>Agency Dashboard</h2>
                <p>Manage your published vacation deals and client offerings.</p>
            </div>
            <a href="create-package.php" class="btn">＋ New Package</a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">📦</span>
                <div>
                    <span class="stat-number"><?php echo $total_packages; ?></span>
                    <span class="stat-label">Total Packages</span>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🧳</span>
                <div>
                    <span class="stat-number"><?php echo $stats['total_bookings'] ?? 0; ?></span>
                    <span class="stat-label">Total Bookings</span>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">💰</span>
                <div>
                    <span class="stat-number">R<?php echo number_format($stats['total_revenue'] ?? 0, 0); ?></span>
                    <span class="stat-label">Total Revenue</span>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⭐</span>
                <div>
                    <span class="stat-number"><?php echo $avg['avg_rating'] ? number_format($avg['avg_rating'], 1) : 'N/A'; ?></span>
                    <span class="stat-label">Avg. Rating</span>
                </div>
            </div>
        </div>

        <!-- Packages Table -->
        <div class="table-card">
            <div class="table-card-header">
                <h3>Your Packages</h3>
                <span class="table-count"><?php echo $total_packages; ?> total</span>
            </div>

            <?php if ($total_packages > 0): ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Destination</th>
                            <th>Description</th>
                            <th>Price (ZAR)</th>
                            <th>Duration</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($my_packages as $pkg): ?>
                            <tr>
                                <td class="pkg-id">#<?php echo $pkg['packID']; ?></td>
                                <td class="pkg-country">
                                    📍 <?php echo htmlspecialchars($pkg['country']); ?>
                                </td>
                                <td class="pkg-desc">
                                    <?php echo htmlspecialchars(mb_strimwidth($pkg['description'], 0, 60, '...')); ?>
                                </td>
                                <td class="pkg-price">R<?php echo number_format($pkg['price'], 2); ?></td>
                                <td class="pkg-duration">
                                    <?php echo $pkg['duration']; ?>
                                    <?php echo $pkg['duration'] > 1 ? 'Nights' : 'Night'; ?>
                                </td>
                                <td class="pkg-actions">
                                    <a href="edit-package.php?id=<?php echo $pkg['packID']; ?>" class="btn-edit">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Delete this package? This cannot be undone.');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="package_id" value="<?php echo $pkg['packID']; ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-empty">
                    <span class="empty-icon">📭</span>
                    <p>You haven't created any packages yet.</p>
                    <a href="create-package.php" class="btn">Create Your First Package</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="../public/js/main.js"></script>
<?php include '../components/footer.php'; ?>