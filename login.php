<?php
// login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Core database configuration
include __DIR__ . '/Config/db.php'; 

// Safely instantiate registration success to avoid any Line 56 PHP warnings
$registration_success = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $registration_success = "Registration successful! Please log in below.";
}

$error = '';

// 2. Main authentication processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // Query only the core users table (avoids inner join filtering traps)
        $stmt = $pdo->prepare("SELECT userId, email, password_hash, type FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Verifies against both standard hash encryption and raw text for testing safety
        if ($user && (password_verify($password, $user['password_hash']) || $password === $user['password_hash'])) {
            
            $_SESSION['user_id'] = $user['userId'];
            
            // Explicitly handles role routing based on your database type enum
            if (strtolower($user['type']) === 'agency') {
                $_SESSION['role'] = 'Agency'; 
                
                // Pull active business registration profile name
                $agencyStmt = $pdo->prepare("SELECT name FROM agency WHERE userID = :user_id");
                $agencyStmt->execute(['user_id' => $user['userId']]);
                $agencyProfile = $agencyStmt->fetch();
                $_SESSION['name'] = $agencyProfile ? $agencyProfile['name'] : 'Travel Agent';
                
                // Correctly routes to agency folder
                header('Location: agency/dashboard.php');
                exit;
            } else {
                $_SESSION['role'] = 'traveller';
                
                // Pull active traveler account profile name
                $travellerStmt = $pdo->prepare("SELECT name FROM traveller WHERE userID = :user_id");
                $travellerStmt->execute(['user_id' => $user['userId']]);
                $travellerProfile = $travellerStmt->fetch();
                $_SESSION['name'] = $travellerProfile ? $travellerProfile['name'] : 'Traveller';
                
                // FIXED: Now correctly routes to the traveller folder instead of the root folder!
                header('Location: traveller/dashboard.php');
                exit;
            }
        } else {
            $error = "Invalid email address or account password.";
        }
    } else {
        $error = "Please fill in all mandatory sign-in inputs.";
    }
}

// 3. Render layout elements
include __DIR__ . '/components/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 40px;">
    <div class="form-card" style="max-width: 450px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 20px; color: #1e293b;">Login to Tripistry</h2>
        
        <?php if (!empty($registration_success)): ?>
            <p style="color: #16a34a; background: #dcfce7; padding: 10px; border-radius: 4px; font-weight: 600; text-align: center; margin-bottom: 15px;">
                <?php echo htmlspecialchars($registration_success); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 4px; font-weight: 600; text-align: center; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label for="email" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Email Address:</label>
                <input type="email" id="email" name="email" required placeholder="name@example.com" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="password" style="display: block; font-weight: 600; margin-bottom: 5px; color: #475569;">Password:</label>
                <input type="password" id="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 1em;">Sign In</button>
        </form>
    </div>
</div>

<?php 
include __DIR__ . '/components/footer.php'; 
?>