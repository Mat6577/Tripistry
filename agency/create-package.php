<?php
session_start();
include '../config/db.php';

// Enforce strict interface boundaries [cite: 30]
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agency') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $destination = trim($_POST['destination']);
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $agency_id = $_SESSION['user_id'];

    // Perform backend server-side data validation check [cite: 144]
    if (!empty($title) && !empty($destination) && $price > 0 && $duration > 0) {
        $stmt = $pdo->prepare("INSERT INTO package (description, country, price, agencyId) VALUES (:title, :destination, :price, :agency_id)");
        $stmt->execute([
            'title' => $title,
            'destination' => $destination,
            'price' => $price,
            'agency_id' => $agency_id
        ]);
        $msg = "Package successfully published to the platform!";
    } else {
        $msg = "Error: Invalid data entry values provided.";
    }
}
include '../components/header.php';
?>
<h2>Curate New Travel Package</h2>
<?php if (!empty($msg)): ?>
    <p class="notification-box"><?php echo htmlspecialchars($msg); ?></p>
<?php endif; ?>

<div class="form-card">
    <form action="create-package.php" method="POST">
        <label for="title">Package Package Name / Title</label>
        <input type="text" id="title" name="title" required>

        <label for="destination">Target Destination City/Country</label>
        <input type="text" id="destination" name="destination" required>

        <label for="price">Package Base Cost (ZAR)</label>
        <input type="number" id="price" name="price" step="0.01" min="1" required>

        <label for="duration">Trip Duration (In Days)</label>
        <input type="number" id="duration" name="duration" min="1" required>

        <button type="submit" class="btn">Publish Package</button>
    </form>
</div>
<?php include '../components/footer.php'; ?>