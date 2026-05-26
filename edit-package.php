<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$agency_id  = $_SESSION['user_id'];
$package_id = intval($_GET['id'] ?? 0);
$msg        = '';
$msg_type   = '';

// Fetch existing package — ensure it belongs to this agency
$stmt = $pdo->prepare("SELECT * FROM package WHERE packID = :id AND agencyId = :agency_id");
$stmt->execute(['id' => $package_id, 'agency_id' => $agency_id]);
$package = $stmt->fetch();

if (!$package) {
    header('Location: dashboard.php');
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update') {
        $title       = trim($_POST['title']       ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $price       = floatval($_POST['price']   ?? 0);
        $duration    = intval($_POST['duration']  ?? 0);

        // Handle image upload
        $image_path = $package['image_path'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $mime    = mime_content_type($_FILES['image']['tmp_name']);
            if (in_array($mime, $allowed)) {
                $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename   = 'pkg_' . uniqid() . '.' . $ext;
                $upload_dir = '../uploads/packages/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename);
                $image_path = 'uploads/packages/' . $filename;
            } else {
                $msg      = "Error: Only JPG, PNG, or WEBP images are allowed.";
                $msg_type = "error";
            }
        }

        if (empty($msg)) {
            if (!empty($title) && !empty($destination) && $price > 0 && $duration > 0) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE package
                        SET description = :title,
                            country     = :destination,
                            price       = :price,
                            duration    = :duration,
                            image_path  = :image_path
                        WHERE packID = :id AND agencyId = :agency_id
                    ");
                    $stmt->execute([
                        'title'       => $title,
                        'destination' => $destination,
                        'price'       => $price,
                        'duration'    => $duration,
                        'image_path'  => $image_path,
                        'id'          => $package_id,
                        'agency_id'   => $agency_id
                    ]);
                    $msg      = "Package updated successfully!";
                    $msg_type = "success";

                    // Refresh package data
                    $stmt = $pdo->prepare("SELECT * FROM package WHERE packID = :id AND agencyId = :agency_id");
                    $stmt->execute(['id' => $package_id, 'agency_id' => $agency_id]);
                    $package = $stmt->fetch();

                } catch (\PDOException $e) {
                    $msg      = "Database error: " . $e->getMessage();
                    $msg_type = "error";
                }
            } else {
                $msg      = "Error: Please fill in all required fields correctly.";
                $msg_type = "error";
            }
        }
    }

    if ($_POST['action'] === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM package WHERE packID = :id AND agencyId = :agency_id");
            $stmt->execute(['id' => $package_id, 'agency_id' => $agency_id]);
            header('Location: dashboard.php?deleted=1');
            exit;
        } catch (\PDOException $e) {
            $msg      = "Error deleting package: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}

include '../components/header.php';
?>

    <link rel="stylesheet" href="css/edit-package.css">

    <div class="edit-container">

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="edit-header">
            <h2>Edit Package</h2>
            <p>Update the details for this travel package.</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="notification-box <?php echo $msg_type; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <!-- Current Banner -->
        <?php if (!empty($package['image_path'])): ?>
            <div class="current-image">
                <img src="../<?php echo htmlspecialchars($package['image_path']); ?>" alt="Current banner">
                <span>Current banner image</span>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="form-card">
            <form action="edit-package.php?id=<?php echo $package_id; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">

                <div class="pkg-id-badge">
                    Editing: <strong>Package #<?php echo $package_id; ?></strong>
                    &nbsp;·&nbsp; <?php echo htmlspecialchars($package['country']); ?>
                </div>

                <div class="form-group">
                    <label for="title">Package Name / Title</label>
                    <input type="text" id="title" name="title"
                           placeholder="e.g. Soweto Heritage Explorer"
                           value="<?php echo htmlspecialchars($package['description']); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="destination">Target Destination</label>
                    <input type="text" id="destination" name="destination"
                           placeholder="e.g. South Africa"
                           value="<?php echo htmlspecialchars($package['country']); ?>"
                           required>
                </div>

                <hr class="form-divider">

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Base Cost (ZAR)</label>
                        <input type="number" id="price" name="price"
                               step="0.01" min="1"
                               value="<?php echo htmlspecialchars($package['price']); ?>"
                               required>
                        <span class="input-hint">Amount in South African Rand</span>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration (Nights)</label>
                        <input type="number" id="duration" name="duration"
                               min="1"
                               value="<?php echo htmlspecialchars($package['duration']); ?>"
                               required>
                        <span class="input-hint">Number of nights included</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Replace Banner Image (optional)</label>
                    <input type="file" id="image" name="image"
                           accept="image/jpeg,image/png,image/webp">
                    <span class="input-hint">Leave empty to keep current image. JPG, PNG or WEBP only.</span>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn">Save Changes</button>
                </div>

            </form>
        </div>

        <!-- Danger Zone -->
        <div class="danger-zone">
            <div class="danger-zone-info">
                <h4>🗑️ Delete This Package</h4>
                <p>Permanently remove this package from the platform. This cannot be undone.</p>
            </div>
            <form method="POST" action="edit-package.php?id=<?php echo $package_id; ?>"
                  onsubmit="return confirm('Permanently delete this package? This cannot be undone.');">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn-danger">Delete Package</button>
            </form>
        </div>

    </div>

<?php include '../components/footer.php'; ?>