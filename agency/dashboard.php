<?php
// agency/dashboard.php
session_start();
include '../config/db.php';

// Enforce strict interface and permission boundaries
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$agency_id = $_SESSION['user_id'];
$message = '';

// Handle package deletion (CRUD Requirement) securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $package_id = $_POST['package_id'] ?? 0;
    
    // Ensure the package strictly belongs to this agency before deleting
    $stmt = $pdo->prepare("DELETE FROM package WHERE packageID = :id AND agencyID = :agency_id");
    $stmt->execute(['id' => $package_id, 'agency_id' => $agency_id]);
    
    if ($stmt->rowCount() > 0) {
        $message = "Package successfully removed.";
    } else {
        $message = "Error: Package could not be deleted or unauthorized access.";
    }
}

// Fetch all packages curated specifically by this agency
$stmt = $pdo->prepare("SELECT * FROM package WHERE agencyId = :agency_id ORDER BY packageID DESC");
$stmt->execute(['agency_id' => $agency_id]);
$my_packages = $stmt->fetchAll();

include '../components/header.php';
?>

<div class="dashboard-header">
    <h2>Agency Dashboard</h2>
    <p>Manage your published vacation deals and client offerings.</p>
    <a href="create-package.php" class="btn">＋ Create New Package</a>
</div>

<?php if (!empty($message)): ?>
    <div class="notification-box" style="background: #e0f2fe; padding: 10px; border-radius: 4px; margin: 15px 0;">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<table class="data-table" style="width:100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <thead>
        <tr style="background: #1e293b; color: white; text-align: left;">
            <th style="padding: 12px;">Package Title</th>
            <th style="padding: 12px;">Destination</th>
            <th style="padding: 12px;">Price (ZAR)</th>
            <th style="padding: 12px;">Duration</th>
            <th style="padding: 12px; text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($my_packages) > 0): ?>
            <?php foreach ($my_packages as $pkg): ?>
                <tr style="border-bottom: 1px solid #cbd5e1;">
                    <td style="padding: 12px; font-weight: 600;">PACKAGE #<?php echo htmlspecialchars($pkg['packageID']); ?></td>
                    <td style="padding: 12px;">📍 <?php echo htmlspecialchars($pkg['country']); ?></td>
                    <td style="padding: 12px;">R<?php echo htmlspecialchars(number_format($pkg['price'], 2)); ?></td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($pkg['description']); ?> Days</td>
                    <td style="padding: 12px; text-align: center;">
                        <form action="dashboard.php" method="POST" onsubmit="return confirmDelete();" style="display:inline;">
                            <input type="hidden" name="package_id" value="<?php echo $pkg['packageID']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" style="background:#ef4444; color:white; border:none; padding: 6px 12px; border-radius:4px; cursor:pointer;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="padding: 20px; text-align: center; color: #64748b;">You haven't created any travel packages yet.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script src="../public/js/main.js"></script>
<?php include '../components/footer.php'; ?>