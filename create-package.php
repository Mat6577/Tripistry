<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$msg      = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price']    ?? 0);
    $duration    = intval($_POST['duration']   ?? 0);
    $agency_id   = $_SESSION['user_id'];

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed    = ['image/jpeg', 'image/png', 'image/webp'];
        $mime       = mime_content_type($_FILES['image']['tmp_name']);
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
        if (!empty($title) && !empty($destination) && !empty($description) && $price > 0 && $duration > 0) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO package (description, country, price, duration, agencyId, image_path)
                    VALUES (:title, :destination, :price, :duration, :agency_id, :image_path)
                ");
                $stmt->execute([
                        'title'       => $title,
                        'destination' => $destination,
                        'price'       => $price,
                        'duration'    => $duration,
                        'agency_id'   => $agency_id,
                        'image_path'  => $image_path
                ]);
                $msg      = "Package successfully published to the platform!";
                $msg_type = "success";
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

include '../components/header.php';
?>

    <link rel="stylesheet" href="css/create-package.css">

    <div class="create-container">

        <div class="create-header">
            <h2>Curate New Travel Package</h2>
            <p>Fill in the details below to publish a new package to the platform.</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="notification-box <?php echo $msg_type; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form action="create-package.php" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="title">Package Name / Title</label>
                    <input type="text" id="title" name="title"
                           placeholder="e.g. Soweto Heritage Explorer"
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="destination">Target Destination</label>
                    <input type="text" id="destination" name="destination"
                           placeholder="e.g. South Africa"
                           value="<?php echo htmlspecialchars($_POST['destination'] ?? ''); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="description">Package Description</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Describe what makes this package special..."
                              required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <hr class="form-divider">

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Base Cost (ZAR)</label>
                        <input type="number" id="price" name="price"
                               step="0.01" min="1"
                               placeholder="e.g. 4500"
                               value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                               required>
                        <span class="input-hint">Amount in South African Rand</span>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration (Nights)</label>
                        <input type="number" id="duration" name="duration"
                               min="1"
                               placeholder="e.g. 5"
                               value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>"
                               required>
                        <span class="input-hint">Number of nights included</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Package Banner Image (optional)</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    <span class="input-hint">JPG, PNG or WEBP. Recommended: 1200×400px</span>
                </div>

                <button type="submit" class="btn">Publish Package</button>

            </form>
        </div>

    </div>

<?php include '../components/footer.php'; ?>