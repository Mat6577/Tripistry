<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'traveller') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$package_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$notification = '';

// Handle Booking Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    try {
        $stmt = $pdo->prepare("INSERT INTO booking (travellerId, packageId, bookingDate) VALUES (:user_id, :package_id, NOW())");
        $stmt->execute(['user_id' => $user_id, 'package_id' => $package_id]);
        $notification = "🎉 Booking successful! Your itinerary has been reserved.";
    } catch (\PDOException $e) {
        error_log("Booking Query Bug: " . $e->getMessage());
        $notification = "Critical error booking this itinerary reservation.";
    }
}

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO review (packageID, userID, rating, comment) VALUES (:package_id, :user_id, :rating, :comment)");
            $stmt->execute([
                'package_id' => $package_id,
                'user_id' => $user_id,
                'rating' => $rating,
                'comment' => $comment
            ]);
            $notification = "⭐ Review published successfully!";
        } catch (\PDOException $e) {
            $notification = "Error adding review data entry.";
        }
    }
}

// FETCH PACKAGE DETAILS & DYNAMIC AVERAGE RATING
$stmt = $pdo->prepare("
    SELECT 
        package.*, 
        COALESCE(AVG(review.rating), 0) AS average_rating, 
        COUNT(review.reviewID) AS total_reviews 
    FROM package 
    LEFT JOIN review ON package.packID = review.packageID 
    WHERE package.packID = :id 
    GROUP BY package.packID
");
$stmt->execute(['id' => $package_id]);
$package = $stmt->fetch();

if (!$package) {
    header("Location: dashboard.php");
    exit;
}

// Fetch all individual reviews for this package
$stmt = $pdo->prepare("SELECT r.*, u.email FROM review r JOIN users u ON r.userID = u.userId WHERE r.packageID = :id ORDER BY r.reviewID DESC");
$stmt->execute(['id' => $package_id]);
$reviews = $stmt->fetchAll();

include '../components/header.php';
?>

<div style="max-width: 820px; margin: 0 auto; padding: 20px;">
    <?php if (!empty($notification)): ?>
        <div style="padding: 12px; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; border-radius: 6px; margin-bottom: 20px; font-weight:600;">
            <?php echo htmlspecialchars($notification); ?>
        </div>
    <?php endif; ?>

    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <h1 style="margin-top: 0; margin-bottom: 5px; color: #1e293b;"><?php echo htmlspecialchars($package['description']); ?></h1>
        
        <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <span style="color:#eab308; font-size: 1.2rem;">
                <?php 
                $rounded_rating = round($package['average_rating']);
                echo str_repeat('★', $rounded_rating) . str_repeat('☆', 5 - $rounded_rating); 
                ?>
            </span>
            <span style="color: #64748b; font-size: 0.95rem; font-weight: 600;">
                (<?php echo $package['total_reviews']; ?> Reviews)
            </span>
        </div>

        <p style="font-size: 1.1rem; color: #475569; margin-top: 0;">📍 <strong>Target Country:</strong> <?php echo htmlspecialchars($package['country']); ?></p>
        <p style="font-size: 1.6rem; font-weight: 700; color: #16a34a; margin: 15px 0;">R<?php echo htmlspecialchars(number_format($package['price'], 2)); ?></p>

        <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 0 8px 8px 0; margin: 25px 0;">
            <h4 style="margin: 0 0 8px 0; color: #1e293b; display: flex; align-items: center; gap: 6px;">✨ Gemini Travel Insights</h4>
            <p id="insights" style="margin: 0; color: #475569; font-size: 0.95rem; font-style: italic;">Loading automated entry rules and local advisories dynamically...</p>
        </div>

        <form method="POST" action="package-view.php?id=<?php echo $package['packID']; ?>">
            <input type="hidden" name="action" value="book">
            <button type="submit" style="width: 100%; padding: 14px; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: background 0.2s;">Book This Package Now</button>
        </form>
    </div>

    <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h3>Platform Guest Reviews</h3>
        
        <form method="POST" action="package-view.php?id=<?php echo $package['packID']; ?>" style="margin-bottom: 25px; border-bottom: 2px dashed #e2e8f0; padding-bottom: 20px;">
            <input type="hidden" name="action" value="review">
            
            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.9rem;">Rating Score:</label>
            <div class="star-rating" style="width: fit-content; margin-bottom: 15px;">
                <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" title="5 stars">★</label>
                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">★</label>
                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">★</label>
                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">★</label>
                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">★</label>
            </div>

            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.9rem;">Comments:</label>
            <textarea name="comment" rows="3" style="width:100%; border:1px solid #cbd5e1; border-radius:4px; padding:8px; box-sizing:border-box;" required></textarea>
            
            <button type="submit" class="btn" style="background:#475569; color:white; border:none; padding:10px 20px; border-radius:4px; margin-top:10px; font-weight:600; cursor:pointer;">Post Review</button>
        </form>

        <div class="reviews-list">
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div style="border-bottom: 1px solid #e2e8f0; padding: 12px 0;">
                        <span style="color:#eab308;"> <?php echo str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?></span>
                        <strong style="margin-left: 10px; color:#475569; font-size:0.9rem;"><?php echo htmlspecialchars($rev['email']); ?></strong>
                        <p style="margin: 5px 0 0 0; color: #334155; font-size:0.95rem;"><?php echo htmlspecialchars($rev['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #94a3b8; font-style: italic; font-size:0.95rem;">No reviews published for this itinerary yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="scriptInsight.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        generateResponse("<?php echo htmlspecialchars($_SESSION['country'] ?? 'South Africa'); ?>", "<?php echo htmlspecialchars($package['country']); ?>");
    });
</script>

<?php include '../components/footer.php'; ?>