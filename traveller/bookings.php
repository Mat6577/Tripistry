<?php
session_start();
include '../config/db.php';

// Enforce strict access control 
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'traveller') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$user_id = $_SESSION['user_id'];

// --- DIRECT BOOKING HANDLER ---
// If the user clicked "Book Deal" on the dashboard, this catches it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    $package_id = intval($_POST['package_id']);
    
    if ($package_id > 0){
        try {
            // Insert the booking securely
            $insertStmt = $pdo->prepare("INSERT INTO booking (travellerId, packageId, bookingDate) VALUES (:user_id, :package_id, NOW())");
            $insertStmt->execute(['user_id' => $user_id, 'package_id' => $package_id]);
            
            // Redirect to itself via GET to clear the POST data
            // (This prevents accidental double-bookings if they refresh the page)
            header("Location: bookings.php?success=1");
            exit;
        } catch (\PDOException $e) {
            error_log("Direct Booking Bug: " . $e->getMessage());
        }
    }
}

// --- CANCELLATION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $cancel_id = intval($_POST['cancel_booking_id']);
    
    if ($cancel_id > 0) {
        try {
            // SECURITY: We check BOTH the bookingID and the travellerId 
            // to prevent someone from maliciously deleting another user's trip.
            $deleteStmt = $pdo->prepare("DELETE FROM booking WHERE bookingID = :bid AND travellerId = :uid");
            $deleteStmt->execute([
                'bid' => $cancel_id,
                'uid' => $user_id
            ]);
            
            header("Location: bookings.php?cancelled=1");
            exit;
        } catch (\PDOException $e) {
            error_log("Cancellation Bug: " . $e->getMessage());
        }
    }
}
// ----------------------------
// ------------------------------

// Retrieve historical data rows using secure parameterized execution queries 
$stmt = $pdo->prepare("SELECT b.bookingID as booking_id, b.bookingDate as booking_date, p.description as title, p.country as destination, p.price 
FROM booking b 
JOIN package p ON b.packageId = p.packID 
WHERE b.travellerId = :uid 
ORDER BY b.bookingID DESC;");

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
                <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                    <span style="font-size: 1.3rem; font-weight: 700; color: #16a34a;">R<?php echo htmlspecialchars(number_format($book['price'], 2)); ?></span>
                    <span style="background: #dcfce7; color: #15803d; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">Confirmed</span>
                        <form action="bookings.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel this trip? This action cannot be undone.');">
                        <input type="hidden" name="cancel_booking_id" value="<?php echo $book['booking_id']; ?>">
                        <button type="submit" style="background: white; color: #ef4444; border: 1px solid #ef4444; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 0.75rem; cursor: pointer; transition: 0.2s;">
                        Cancel Booking
                    </button>
                 </form>
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