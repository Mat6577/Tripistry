<?php
// traveller/package-view.php
session_start();
include '../config/db.php';

// Enforce strict Traveller role access check 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'traveller') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$package_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$notification = '';

// 1. Process Booking Submissions Securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    try {
        $stmt = $pdo->prepare("INSERT INTO booking (user_id, package_id, booking_date) VALUES (:user_id, :package_id, NOW())");
        $stmt->execute(['user_id' => $user_id, 'package_id' => $package_id]);
        $notification = "🎉 Booking successful! Your itinerary has been reserved.";
    } catch (\PDOException $e) {
        $notification = "Error processing booking reservation.";
    }
}

// 2. Process Review & Rating Submissions Securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (userID, packageID, rating, comment) VALUES (:user_id, :package_id, :rating, :comment)");
        $stmt->execute(['user_id' => $user_id, 'package_id' => $package_id, 'rating' => $rating, 'comment' => $comment]);
        
        // // Recalculate average rating metadata field on packages table
        // $updateRating = $pdo->prepare("UPDATE package SET average_rating = (SELECT AVG(rating) FROM review WHERE packID = :pid) WHERE id = :pid");
        // $updateRating->execute(['pid' => $package_id]);

        //RATHER LEAVE THIS CALCULATED
        
        $notification = "Thank you! Your feedback has been added.";
    }
}

// 3. Fetch Package Details along with linked elements
$stmt = $pdo->prepare("SELECT p.*, u.email as agency_email FROM package p JOIN users u ON p.agencyID = u.userId WHERE p.packID = :id");
$stmt->execute(['id' => $package_id]);
$package = $stmt->fetch();

if (!$package) {
    die("Package not found.");
}

// Fetch user reviews associated with this package
$reviewStmt = $pdo->prepare("SELECT * from review WHERE packageID = :pid");
// $reviewStmt = $pdo->prepare("SELECT r.*, p.agencyID FROM review r JOIN package p ON r.userID = p.packID WHERE r.packageID = :pid ORDER BY r.reviewDate DESC");
$reviewStmt->execute(['pid' => $package_id]);
$reviews = $reviewStmt->fetchAll();

include '../components/header.php';
?>

<script src="scriptInsight.js"></script>


<div class="package-view-container" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <?php if (!empty($notification)): ?>
        <div class="notification-box" style="background: #f0fdf4; color: #166534; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($notification); ?>
        </div>
    <?php endif; ?>

    <div class="package-banner" style="margin-bottom: 25px;">
        <?php 
            // Fallback to a placeholder image if the column value happens to be empty
            $imgSrc = !empty($package['image_path']) ? $package['image_path'] : 'public/img/package-placeholder.png'; 
        ?>
        <img src="../<?php echo htmlspecialchars($imgSrc); ?>" 
             alt="<?php echo htmlspecialchars($package['description']); ?> Presentation Image" 
             style="width: 100%; max-height: 380px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    </div>

    <h2><?php echo htmlspecialchars($package['description']); ?></h2>
    <p style="font-size: 1.2rem; color: #0284c7;">📍 Destination: <strong><?php echo htmlspecialchars($package['country']); ?></strong></p>
    <p>Organized by: ✉️ <em><?php echo htmlspecialchars($package['agency_email']); ?></em></p>

    <div class="itinerary-details" style="margin: 25px 0; padding: 20px; background: #f8fafc; border-left: 4px solid #0284c7;">
        <h3>📋 Included Trip Architecture</h3>
        <p>✈️ <strong>Flight Segment:</strong> Standard Economy Return (Linked Dataset Allocation)</p>
        <p>🏨 <strong>Accommodation:</strong> Premium Partner Stay (<?php echo htmlspecialchars($package['duration']); echo $package['duration']>1?" Nights":" Night"; ?>)</p>
        <p>🍽️ <strong>Catering Context:</strong> Verified Top Attractions & Local Restaurant Vouchers</p>
        <p style="font-size: 1.5rem; margin-top:15px;">Cost Summary: <strong style="color:#0284c7;">R<?php echo htmlspecialchars(number_format($package['price'], 2)); ?></strong></p>
        
        <form action="package-view.php?id=<?php echo $package['packID']; ?>" method="POST" onsubmit="return confirmBooking();">
            <input type="hidden" name="action" value="book">
            <button type="submit" class="btn" style="font-size:1.1rem; padding: 10px 20px;">Book This Trip Now</button>
        </form>
    </div>

    <div>
        <h3>Location Insights (Powered by AI) ❇️</h3>
        
        <div id="insights" style="background: #f1f5f9; padding: 15px; border-radius: 6px; margin-bottom: 25px;">
            loading insights...
            <?php 
            $origin = $_SESSION['country'];
            $to = $package['country'];

            // Echoing the JavaScript function call
            echo "<script type='text/javascript'>
                generateResponse('$origin', '$to');
            </script>";
            ?>
        </div>
    </div>

    <div class="reviews-section" style="margin-top: 40px;">
        <h3>User Feedback & Ratings (⭐ <?php echo htmlspecialchars(number_format($package['average_rating'] ?? 0, 1)); ?>/5)</h3>
        
        <form action="package-view.php?id=<?php echo $package['packID']; ?>" method="POST" style="background: #f1f5f9; padding: 15px; border-radius: 6px; margin-bottom: 25px;">
            <input type="hidden" name="action" value="review">
            <label for="rating">Your Score:</label>
            <select name="rating" id="rating" style="width: auto; display: inline-block; margin-left: 10px;" required>
                <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                <option value="4">⭐⭐⭐⭐ (4/5)</option>
                <option value="3">⭐⭐⭐ (3/5)</option>
                <option value="2">⭐⭐ (2/5)</option>
                <option value="1">⭐ (1/5)</option>
            </select>

            <label for="comment">Tell us about your experience:</label>
            <textarea name="comment" id="comment" rows="3" style="width:100%; border: 1px solid #cbd5e1; border-radius:4px; padding:8px; box-sizing:border-box; margin-top:5px;" required></textarea>
            
            <button type="submit" class="btn" style="background:#475569; margin-top:10px;">Post Review</button>
        </form>

        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $rev): ?>
                <div style="border-bottom: 1px solid #e2e8f0; padding: 12px 0;">
                    <span style="color:#eab308;"> <?php echo str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?></span>
                    <strong style="margin-left: 10px;"><?php echo htmlspecialchars($rev['email']); ?></strong>
                    <p style="margin: 5px 0 0 0; color: #334155;"><?php echo htmlspecialchars($rev['comment']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#64748b;">No comments found for this agency package yet. Be the first!</p>
        <?php endif; ?>
    </div>
</div>

<script src="../public/js/main.js"></script>
<?php include '../components/footer.php'; ?>