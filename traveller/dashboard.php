<?php
session_start();
include '../config/db.php';

// Enforce strict interface boundaries [cite: 30]
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'traveller') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}


$sql = "SELECT * FROM package WHERE 1=1";
$params = [];

// Apply filtering parameters safely if they are provided [cite: 127]
if (!empty($_GET['destination'])) {
    $sql .= " AND destination LIKE :destination";
    $params['destination'] = '%' . $_GET['destination'] . '%';
}
if (!empty($_GET['max_price'])) {
    $sql .= " AND price <= :max_price";
    $params['max_price'] = $_GET['max_price'];
}

// Implement sorting mechanics [cite: 127]
$sort = $_GET['sort'] ?? 'price_asc';
if ($sort === 'rating_desc') {
    $sql .= " ORDER BY average_rating DESC";
} else {
    $sql .= " ORDER BY price ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();

include '../components/header.php';
?>
<h2>Traveller Dashboard - Explore Packages</h2>

<form method="GET" action="dashboard.php" class="filter-form">
    <input type="text" name="destination" placeholder="Search Destination..." value="<?php echo htmlspecialchars($_GET['destination'] ?? ''); ?>">
    <input type="number" name="max_price" placeholder="Max Budget (ZAR)" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
    
    <select name="sort">
        <option value="price_asc" <?php echo ($sort === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
        <option value="rating_desc" <?php echo ($sort === 'rating_desc') ? 'selected' : ''; ?>>Top Rated</option>
    </select>
    <button type="submit" class="btn">Filter</button>
</form>

<div class="package-grid">
    <?php if (count($packages) > 0): ?>
        <?php foreach ($packages as $pkg): ?>
            <div class="package-card">
                <h3><?php echo htmlspecialchars($pkg['description']); ?></h3>
                <p>📍 <strong>Destination:</strong> <?php echo htmlspecialchars($pkg['country']); ?></p>
                <p>💵 <strong>Price:</strong> R<?php echo htmlspecialchars($pkg['price']); ?></p>
                <p>⭐ <strong>Rating:</strong> <?php echo htmlspecialchars($pkg['average_rating'] ?? 'No reviews yet'); ?></p>
                <a href="package-view.php?id=<?php echo $pkg['packID']; ?>" class="btn">View Details</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No holiday packages matched your criteria. Try adjusting your filter choices.</p>
    <?php endif; ?>
</div>

<?php include '../components/footer.php'; ?>