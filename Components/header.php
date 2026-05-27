<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dynamically determine base path prefix depending on current directory level
// to prevent broken relative navigation paths across subdirectories
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path_prefix = (in_array($current_dir, ['traveller', 'agency', 'gemini_insights'])) ? '../' : './';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripistry - Travel Platform</title>
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>public/css/styles.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="<?php echo $path_prefix; ?>index.php">✈️ Tripistry</a>
            </div>
            <ul class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    // Normalize role checks application-wide to remain case-insensitive
                    $user_role = strtolower($_SESSION['role'] ?? ''); 
                    if ($user_role === 'agency'): 
                    ?>
                        <li><a href="<?php echo $path_prefix; ?>agency/dashboard.php">Agency Dashboard</a></li>
                        <li><a href="<?php echo $path_prefix; ?>agency/create-package.php">Create Package</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $path_prefix; ?>traveller/dashboard.php">Browse Packages</a></li>
                        <li><a href="<?php echo $path_prefix; ?>traveller/bookings.php">My Bookings</a></li>
                    <?php endif; ?>
                    <li class="user-status">Logged in as: <strong><?php echo htmlspecialchars(($_SESSION['name'] ?? 'User') . " (" . ucfirst($user_role) . ")"); ?></strong></li>
                    <li><a href="<?php echo $path_prefix; ?>logout.php" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $path_prefix; ?>login.php">Login</a></li>
                    <li><a href="<?php echo $path_prefix; ?>register.php" class="btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>