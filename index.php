<?php
session_start();
include 'Components/header.php';
?>
<div class="hero-section">
    <h1>Welcome to Tripistry</h1>
    <p>Compare tailored holiday packages from premium agencies or curate your own group expeditions seamlessly.</p>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="cta-buttons">
            <a href="login.php" class="btn">Sign In</a>
            <a href="register.php" class="btn btn-secondary">Create Account</a>
        </div>
    <?php else: ?>
        <p>You are logged in! Go to your corresponding dashboard above to manage your actions.</p>
    <?php endif; ?>
</div>
<?php include 'Components/footer.php'; ?>