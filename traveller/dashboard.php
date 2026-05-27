<?php
// traveller/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Core architecture links
include __DIR__ . '/../Config/db.php';

// 2. Strict protection gateway
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || (strtolower($_SESSION['role']) !== 'traveller')) {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = 'Traveller';

try {
    $userStmt = $pdo->prepare("SELECT name FROM traveller WHERE userID = :user_id");
    $userStmt->execute(['user_id' => $user_id]);
    $travellerProfile = $userStmt->fetch();
    if ($travellerProfile && !empty($travellerProfile['name'])) {
        $user_name = $travellerProfile['name'];
    }
} catch(PDOException $e){}

// 3. Fetch all packages JOINED with the specific agency's phone number
try {
    // We join 'phonenumber' on the 'agencyID' found in the 'package' table
    $stmt = $pdo->query("
        SELECT 
            p.packID, p.price, p.description, p.country, p.image_path, p.type, p.duration, 
            pn.phoneNumber as agency_phone,
            IFNULL(AVG(r.rating), 0) as avg_rating, 
            COUNT(r.rating) as review_count 
        FROM package p 
        LEFT JOIN review r ON p.packID = r.packageID 
        LEFT JOIN phonenumber pn ON p.agencyID = pn.userID
        GROUP BY p.packID 
        ORDER BY p.packID DESC
    ");
    $packages = $stmt->fetchAll();
} 
catch(PDOException $e){
    die("Database Error: " . $e->getMessage());
}

// 4. Smart Path Resolver Function
function resolve_dashboard_image($path){
    if(empty($path)) return '';
    if(stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0) return $path;
    $path = str_replace('\\', '/', $path);
    if(strpos($path, '../') === 0) return $path;
    if (strpos($path, 'public/') === 0) return '../' . $path;
    if (strpos($path, 'uploads/') === 0) return '../public/' . $path;
    return '../public/uploads/' . ltrim($path, '/');
}

include __DIR__ . '/../components/header.php';
?>

<div class="container" style="margin-top: 30px; max-width: 1200px; margin-left: auto; margin-right: auto; padding: 0 15px; margin-bottom: 50px;">
    <div style="margin-bottom: 30px;">
        <h2 style="color: #0f172a; margin: 0;">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <p style="color: #64748b; margin: 5px 0 0 0;">Explore our latest tailored vacation packages and destinations.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
        <?php if (count($packages) > 0): ?>
            <?php foreach ($packages as $pkg): ?>
                <div class="package-card" style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; flex-direction: column;">
                    
                    <div style="width: 100%; height: 210px; background: #f1f5f9; position: relative;">
                        <?php 
                        $resolved_image = resolve_dashboard_image($pkg['image_path']);
                        if (!empty($resolved_image)): 
                        ?>
                            <img src="<?php echo htmlspecialchars($resolved_image); ?>" alt="Destination Image" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8; background: #f8fafc; font-weight: bold;">
                                🌅 No Image Available
                            </div>
                        <?php endif; ?>
                        
                        <span style="position: absolute; top: 12px; right: 12px; background: rgba(15, 23, 42, 0.85); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75em; font-weight: bold;">
                            ⏱️ <?php echo htmlspecialchars($pkg['duration']); ?> Days
                        </span>
                    </div>

                    <div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div style="margin-bottom: 20px;">
                            <h3 style="margin: 0 0 8px 0; color: #0f172a;">📍 <?php echo htmlspecialchars($pkg['country']); ?></h3>
                            
                            <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                <span style="color: #eab308; letter-spacing: 2px;">
                                    <?php 
                                    $starCount = round($pkg['avg_rating']);
                                    echo str_repeat('★', $starCount) . str_repeat('☆', 5 - $starCount); 
                                    ?>
                                </span>
                                <span style="color: #64748b; font-size: 0.8em; font-weight: 600;">
                                    <?php if ($pkg['review_count'] > 0): ?>
                                        <?php echo number_format($pkg['avg_rating'], 1); ?> (<?php echo $pkg['review_count']; ?> reviews)
                                    <?php else: ?>
                                        <span style="color: #2563eb;">New!</span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <span style="display: inline-block; background: #f1f5f9; color: #475569; font-size: 0.75em; font-weight: bold; padding: 3px 8px; border-radius: 4px; margin-bottom: 12px;">
                                <?php echo htmlspecialchars(str_replace('_', ' ', $pkg['type'])); ?>
                            </span>

                            <p style="color: #475569; font-size: 0.9em; margin: 0 0 15px 0; line-height: 1.5;">
                                <?php echo htmlspecialchars($pkg['description']); ?>
                            </p>

                            <?php if (!empty($pkg['agency_phone'])): ?>
                                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                                    <span style="display: block; font-size: 0.7em; color: #166534; font-weight: bold; margin-bottom: 2px;">Need info? Call Agent:</span>
                                    <a href="tel:<?php echo htmlspecialchars($pkg['agency_phone']); ?>" style="color: #15803d; font-weight: bold; text-decoration: none; font-size: 0.95em;">
                                        📞 <?php echo htmlspecialchars($pkg['agency_phone']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                            <div>
                                <span style="display: block; font-size: 0.75em; color: #64748b; font-weight: bold;">Total Price</span>
                                <span style="font-size: 1.2em; font-weight: bold; color: #16a34a;">R<?php echo number_format($pkg['price'], 2); ?></span>
                            </div>
                            
                            <form action="bookings.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="package_id" value="<?php echo $pkg['packID']; ?>">
                                <button type="submit" style="background: #2563eb; color: white; padding: 8px 15px; border-radius: 4px; border: none; font-weight: bold; font-size: 0.9em; cursor: pointer;">
                                     Book Deal →
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; padding: 50px; text-align: center; color: #64748b; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                <h3>No vacation packages available right now.</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>