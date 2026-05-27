<?php
// agency/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Core architecture links
include __DIR__ . '/../Config/db.php';

// 2. Strict protection gateway
if (!isset($_SESSION['user_id']) || (strtolower($_SESSION['role']) !== 'agency')) {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if (isset($_GET['success']) && $_GET['success'] == 1) $message = "Package successfully created!";
if (isset($_GET['updated']) && $_GET['updated'] == 1) $message = "Package successfully updated!";

// 3. Process Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $package_id = $_POST['package_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM package WHERE packID = :id AND agencyID = :agency_id");
    $stmt->execute(['id' => $package_id, 'agency_id' => $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $message = "Package successfully removed.";
    } else {
        $message = "Error removing package.";
    }
}

// 4. Fetch Packages
$stmt = $pdo->prepare("SELECT packID, price, description, country, image_path FROM package WHERE agencyID = :agency_id ORDER BY packID DESC");
$stmt->execute(['agency_id' => $user_id]);
$my_packages = $stmt->fetchAll();

// 5. Smart Path Resolver Function to fix any broken image formats automatically
function resolve_dashboard_image($path) {
    if (empty($path)) return '';
    if (stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0) {
        return $path;
    }
    $path = str_replace('\\', '/', $path);
    if (strpos($path, '../') === 0) {
        return $path;
    }
    if (strpos($path, 'public/') === 0) {
        return '../' . $path;
    }
    if (strpos($path, 'uploads/') === 0) {
        return '../public/' . $path;
    }
    return '../public/uploads/' . ltrim($path, '/');
}

include __DIR__ . '/../components/header.php';
?>

<div class="container" style="margin-top: 30px; max-width: 1200px; margin-left: auto; margin-right: auto; padding: 0 15px; margin-bottom: 50px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h2 style="color: #0f172a; margin: 0;">Agency Dashboard</h2>
            <p style="color: #64748b; margin: 5px 0 0 0;">Manage your published vacation deals.</p>
        </div>
        <a href="create-package.php" class="btn" style="background: #16a34a; color: white; padding: 10px 18px; border-radius: 4px; text-decoration: none; font-weight: bold;">＋ Create New Package</a>
    </div>

    <?php if (!empty($message)): ?>
        <div style="background: #e0f2fe; padding: 12px; border-radius: 4px; margin-bottom: 20px; color: #0369a1; font-weight: bold;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <table style="width:100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <thead>
            <tr style="background: #1e293b; color: white; text-align: left;">
                <th style="padding: 14px;">Package ID</th>
                <th style="padding: 14px;">Image</th>
                <th style="padding: 14px;">Country</th>
                <th style="padding: 14px;">Price (ZAR)</th>
                <th style="padding: 14px;">Description</th>
                <th style="padding: 14px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($my_packages) > 0): ?>
                <?php foreach ($my_packages as $pkg): ?>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 14px; font-weight: bold;">#<?php echo htmlspecialchars($pkg['packID']); ?></td>
                        
                        <td style="padding: 14px; vertical-align: middle;">
                            <?php 
                            $resolved_image = resolve_dashboard_image($pkg['image_path']);
                            if (!empty($resolved_image)): 
                            ?>
                                <img src="<?php echo htmlspecialchars($resolved_image); ?>" alt="Package Image" style="width: 70px; height: 50px; object-fit: cover; border-radius: 4px; display: block;">
                            <?php else: ?>
                                <div style="width: 70px; height: 50px; background: #f1f5f9; text-align: center; line-height: 50px; font-size: 0.7em; color: #94a3b8;">No Image</div>
                            <?php endif; ?>
                        </td>

                        <td style="padding: 14px;">📍 <?php echo htmlspecialchars($pkg['country']); ?></td>
                        <td style="padding: 14px; font-weight: bold; color: #16a34a;">R<?php echo htmlspecialchars(number_format($pkg['price'], 2)); ?></td>
                        <td style="padding: 14px; font-size: 0.9em; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo htmlspecialchars($pkg['description']); ?>
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <a href="edit-package.php?id=<?php echo $pkg['packID']; ?>" style="background: #0284c7; color: white; padding: 6px 14px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 0.85em; margin-right: 5px;">Edit</a>

                            <form action="dashboard.php" method="POST" onsubmit="return confirm('Delete this package?');" style="display:inline;">
                                <input type="hidden" name="package_id" value="<?php echo $pkg['packID']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" style="background:#ef4444; color:white; border:none; padding: 6px 14px; border-radius:4px; cursor:pointer; font-weight:bold; font-size: 0.85em;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="padding: 30px; text-align: center;">No packages created yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>