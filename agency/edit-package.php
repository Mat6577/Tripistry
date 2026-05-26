<?php
// agency/edit-package.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../Config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || (strtolower($_SESSION['role']) !== 'agency')) {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$user_id = $_SESSION['user_id'];
$package_id = intval($_GET['id'] ?? $_POST['packID'] ?? 0);
$error = '';

if ($package_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Fetch existing data to check old images properties
$stmt = $pdo->prepare("SELECT * FROM package WHERE packID = :package_id AND agencyID = :agency_id");
$stmt->execute(['package_id' => $package_id, 'agency_id' => $user_id]);
$package = $stmt->fetch();

if (!$package) {
    header('Location: dashboard.php');
    exit;
}

// Handle Update Request Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = trim($_POST['country'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? 'ALL_INCLUSIVE';
    $duration = intval($_POST['duration'] ?? 1);
    
    // Retain old image default layout if file picker path was untouched
    $image_destination_path = $package['image_path'];

    // Process new uploaded data string block
    if (isset($_FILES['package_image']) && $_FILES['package_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['package_image']['tmp_name'];
        $file_name = $_FILES['package_image']['name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_extension, $allowed_extensions)) {
            $new_file_name = uniqid('pkg_', true) . '.' . $file_extension;
            $upload_dir = __DIR__ . '/../public/uploads/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp_path, $upload_dir . $new_file_name)) {
                $image_destination_path = '../public/uploads/' . $new_file_name;
            } else {
                $error = "Failed to store selected picture image properties.";
            }
        } else {
            $error = "Invalid picture format extension selection.";
        }
    }

    if (empty($error)) {
        if (!empty($country) && !empty($price) && !empty($description) && !empty($duration)) {
            try {
                $stmt = $pdo->prepare("UPDATE package SET type = :type, price = :price, country = :country, description = :description, duration = :duration, image_path = :image_path WHERE packID = :package_id AND agencyID = :agency_id");
                
                $stmt->execute([
                    'type'        => $type,
                    'price'       => $price,
                    'country'     => $country,
                    'description' => $description,
                    'duration'    => $duration,
                    'image_path'  => $image_destination_path,
                    'package_id'  => $package_id,
                    'agency_id'   => $user_id
                ]);

                header('Location: dashboard.php?updated=1');
                exit;
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all required inputs.";
        }
    }
}

include __DIR__ . '/../components/header.php';
?>

<div class="container" style="margin-top: 30px; max-width: 600px; margin-left: auto; margin-right: auto; padding: 0 15px; margin-bottom: 40px;">
    <div class="form-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 0.9em;">← Cancel and Go Back</a>
            <h2 style="color: #0f172a; margin: 10px 0 0 0;">Modify Travel Package #<?php echo $package_id; ?></h2>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-weight: 600; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="edit-package.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="packID" value="<?php echo $package_id; ?>">

            <div style="margin-bottom: 15px;">
                <label for="country" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Destination Country:</label>
                <input type="text" id="country" name="country" required value="<?php echo htmlspecialchars($package['country']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px; display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label for="price" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Price (ZAR):</label>
                    <input type="number" step="0.01" id="price" name="price" required value="<?php echo htmlspecialchars($package['price']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div style="flex: 1;">
                    <label for="duration" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Duration (Days):</label>
                    <input type="number" id="duration" name="duration" min="1" required value="<?php echo htmlspecialchars($package['duration']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="type" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Package Board Type:</label>
                <select id="type" name="type" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; background: white;">
                    <?php 
                    $boards = ['ALL_INCLUSIVE', 'FULL_BOARD', 'HALF_BOARD', 'BED_AND_BREAKFAST', 'SELF_CATERING', 'ROOM_ONLY'];
                    foreach($boards as $b) {
                        $selected = ($package['type'] === $b) ? 'selected' : '';
                        $clean_name = ucwords(strtolower(str_replace('_', ' & ', $b)));
                        echo "<option value=\"$b\" $selected>$clean_name</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="package_image" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Replace Display Photo:</label>
                <?php if(!empty($package['image_path'])): ?>
                    <div style="margin-bottom: 8px;">
                        <span style="font-size:0.85em; color:#64748b; display:block; margin-bottom:3px;">Current Image Preview:</span>
                        <img src="<?php echo htmlspecialchars($package['image_path']); ?>" style="width:80px; height:55px; object-fit:cover; border-radius:4px; border:1px solid #cbd5e1;">
                    </div>
                <?php endif; ?>
                <input type="file" id="package_image" name="package_image" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; box-sizing: border-box;">
                <small style="color: #64748b; font-size: 0.8em;">Leave empty to keep current picture properties unchanged.</small>
            </div>

            <div style="margin-bottom: 25px;">
                <label for="description" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Package Description Details:</label>
                <textarea id="description" name="description" rows="4" maxlength="500" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; resize: vertical; font-family: inherit;"><?php echo htmlspecialchars($package['description']); ?></textarea>
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 12px; background: #0284c7; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 1em;">Update Package Properties</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
