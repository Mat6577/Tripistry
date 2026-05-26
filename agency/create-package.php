<?php
// agency/create-package.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Link database connection architecture
include __DIR__ . '/../Config/db.php';

// 2. Security Gate Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || (strtolower($_SESSION['role']) !== 'agency')) {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// 3. Process Package Creation Form Submission with File Attachment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = trim($_POST['country'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? 'ALL_INCLUSIVE';
    $duration = intval($_POST['duration'] ?? 1);
    
    $image_destination_path = null;

    // Check if an image file was selected and uploaded without structural errors
    if (isset($_FILES['package_image']) && $_FILES['package_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['package_image']['tmp_name'];
        $file_name = $_FILES['package_image']['name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Define clean, allowed extensions list
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Generate an absolute unique filename to avoid overriding duplicate file names
            $new_file_name = uniqid('pkg_', true) . '.' . $file_extension;
            
            // Relative server directory path mapping setup
            $upload_dir = __DIR__ . '/../public/uploads/';
            
            // Create folder automatically if it doesn't exist yet
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $dest_file_path = $upload_dir . $new_file_name;
            
            // Move file from temporary memory storage to your project uploads folder
            if (move_uploaded_file($file_tmp_path, $dest_file_path)) {
                // Save this path pointing to our internal system folder
                $image_destination_path = '../public/uploads/' . $new_file_name;
            } else {
                $error = "Failed to move uploaded image file onto the server storage folder.";
            }
        } else {
            $error = "Invalid file extension type. Allowed: JPG, JPEG, PNG, WEBP, and GIF.";
        }
    }

    // Only run insert statement if no image error was caught above
    if (empty($error)) {
        if (!empty($country) && !empty($price) && !empty($description) && !empty($duration)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO package (type, price, country, description, agencyID, image_path, duration) VALUES (:type, :price, :country, :description, :agency_id, :image_path, :duration)");
                
                $stmt->execute([
                    'type'        => $type,
                    'price'       => $price,
                    'country'     => $country,
                    'description' => $description,
                    'agency_id'   => $user_id,
                    'image_path'  => $image_destination_path,
                    'duration'    => $duration
                ]);

                header('Location: dashboard.php?success=1');
                exit;
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all required form fields.";
        }
    }
}

include __DIR__ . '/../components/header.php';
?>

<div class="container" style="margin-top: 30px; max-width: 600px; margin-left: auto; margin-right: auto; padding: 0 15px; margin-bottom: 40px;">
    <div class="form-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 0.9em;">← Back to Dashboard</a>
            <h2 style="color: #0f172a; margin: 10px 0 0 0;">Create Travel Package</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-weight: 600; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="create-package.php" method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 15px;">
                <label for="country" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Destination Country:</label>
                <input type="text" id="country" name="country" required placeholder="e.g. South Africa" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px; display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label for="price" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Price (ZAR):</label>
                    <input type="number" step="0.01" id="price" name="price" required placeholder="e.g. 8500.00" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div style="flex: 1;">
                    <label for="duration" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Duration (Days):</label>
                    <input type="number" id="duration" name="duration" min="1" required placeholder="e.g. 3" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="type" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Package Board Type:</label>
                <select id="type" name="type" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; background: white;">
                    <option value="ALL_INCLUSIVE">All Inclusive</option>
                    <option value="FULL_BOARD">Full Board</option>
                    <option value="HALF_BOARD">Half Board</option>
                    <option value="BED_AND_BREAKFAST">Bed & Breakfast</option>
                    <option value="SELF_CATERING">Self Catering</option>
                    <option value="ROOM_ONLY">Room Only</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="package_image" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Upload Local Photo from Device:</label>
                <input type="file" id="package_image" name="package_image" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; box-sizing: border-box;">
                <small style="color: #64748b; font-size: 0.8em; display: block; margin-top: 4px;">Supports JPG, PNG, WebP images right out of storage device folders.</small>
            </div>

            <div style="margin-bottom: 25px;">
                <label for="description" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Package Description Details:</label>
                <textarea id="description" name="description" rows="4" maxlength="500" required placeholder="Provide details about the package..." style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; resize: vertical; font-family: inherit;"></textarea>
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 12px; background: #16a34a; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 1em;">Publish Package</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
