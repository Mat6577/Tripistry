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
                        <li><a href="./agency/dashboard.php">Agency Dashboard</a></li>
                        <li><a href="./agency/create-package.php">Create Package</a></li>
                    <?php else: ?>
                        <li><a href="dashboard.php">Browse Packages</a></li>
                        <li><a href="bookings.php">My Bookings</a></li>
                    <?php endif; ?>
                    <li class="user-status">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['name']." (".ucfirst($_SESSION['role']).")"); ?></strong></li>
                    <li><a href="logout.php" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="./login.php">Login</a></li>
                    <li><a href="./register.php" class="btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="container">