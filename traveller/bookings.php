<?php
session_start();
include '../config/db.php';

// Enforce strict access control 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'traveller') {
    header('Location: ../login.php?error=unauthorized');
}

$user_id = $_SESSION['user_id'];

// Retrieve historical data rows using secure parameterized execution queries [cite: 123, 128]
$stmt = $pdo->prepare("SELECT b.bookingID as booking_id, b.bookingDate, p.description, p.country, p.price FROM booking b JOIN package p ON b.packageId = p.packID WHERE b.travellerId = :uid ORDER BY b.bookingId DESC;");
$stmt->execute(['uid' => $user_id]);
$my_bookings = $stmt->fetchAll();

include '../components/header.php';
?>

<h2>My Confirmed Bookings</h2>
<p>Keep track of your upcoming curated holiday reservations below.</p>

<div class="booking-list" style="margin-top: 20px;">
    <?php if (count($my_bookings) > 0): ?>
        <?php foreach ($my_bookings as $book): ?>
            <div style="background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0 0 5px 0; color:#1e293b;"><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p style="margin: 0; color:#64748b;">📍 Destination: <?php echo htmlspecialchars($book['destination']); ?></p>
                    <small style="color: #94a3b8;">Processed on: <?php echo htmlspecialchars($book['booking_date']); ?></small>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 1.3rem; font-weight: 700; color: #16a34a;">R<?php echo htmlspecialchars(number_format($book['price'], 2)); ?></span>
                    <p style="margin:5px 0 0 0;"><span style="background: #dcfce7; color: #15803d; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">Confirmed</span></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; background: white; padding: 40px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <p style="color:#64748b; font-size: 1.1rem;">You haven't made any package bookings yet.</p>
            <a href="dashboard.php" class="btn">Explore Vacation Deals</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../components/footer.php'; ?>