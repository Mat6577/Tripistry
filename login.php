<?php
// 1. Start the session and include the database connection
session_start();
include 'Config/db.php';

$error_message = '';

// 2. Check if the user is submitting the form (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // Securely fetch user using prepared statement to prevent SQL Injection
        $stmt = $pdo->prepare('SELECT userId, password, password_hash, type FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Verify the password against the secure hash stored in the DB
        if ($user && password_verify($password, $user['password_hash'])) {
            error_log('logged in');
            $_SESSION['user_id'] = $user['userId'];
            $_SESSION['role'] = $user['type']; // Store 'Traveller' or 'Agency'

            // Redirect to their respective, distinct dashboards based on role
            if ($user['type'] === 'agency') {
                header('Location: agency/dashboard.php');
            } else if ($user['type'] === 'traveller') {
                header('Location: traveller/dashboard.php');
            } else {
                echo "Something went wrong :( ...";
            }
            exit;
        } else {
            // Friendly error message if credentials don't match
            $error_message = 'Invalid email or password.';
        }
    } else {
        $error_message = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tripistry - Login</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Login to Tripistry</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-box" style="color: red; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Sign In</button>
        </form>
        
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>