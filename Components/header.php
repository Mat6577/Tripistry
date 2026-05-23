<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripistry - Travel Platform</title>
    <link rel="stylesheet" href="/tripistry/public/css/styles.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="/tripistry/index.php">✈️ Tripistry</a>
            </div>
            <ul class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'Agency'): ?>
                        <li><a href="/tripistry/agency/dashboard.php">Agency Dashboard</a></li>
                        <li><a href="/tripistry/agency/create-package.php">Create Package</a></li>
                    <?php else: ?>
                        <li><a href="/tripistry/traveller/dashboard.php">Browse Packages</a></li>
                        <li><a href="/tripistry/traveller/bookings.php">My Bookings</a></li>
                    <?php endif; ?>
                    <li class="user-status">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></li>
                    <li><a href="/tripistry/logout.php" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="/tripistry/login.php">Login</a></li>
                    <li><a href="/tripistry/register.php" class="btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="container">