<?php
// 1. Start the session and include the database connection
session_start();
include 'Config/db.php';
include 'components/header.php';

if (isset($_SESSION['user_id'])){       //logout user
    session_destroy();
}

$error_message = '';
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error_message = 'Unauthorized access. Please log in with the correct account type.';
}

if( isset($_GET['registration']) && $_GET['registration'] === "success"){
    echo "<script>alert('Registration successful!');</script>";
}

// 2. Check if the user is submitting the form (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // Securely fetch user using prepared statement to prevent SQL Injection
        $stmt = $pdo->prepare('SELECT userId, password_hash, type, country FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Verify the password against the secure hash stored in the DB
        if ($user && password_verify($password, $user['password_hash'])) {
            error_log('logged in');
            $_SESSION['user_id'] = $user['userId'];
            $_SESSION['role'] = strtolower($user['type']); // Store 'traveller' or 'agency'
            $_SESSION['country'] = $user['country'];

            // Redirect to their respective, distinct dashboards based on role
            if ($user['type'] === 'agency') {
                $stmt2 = $pdo->prepare('SELECT name FROM agency WHERE userID = :userid');
                $stmt2->execute(['userid' => $user['userId']]);
                $user2 = $stmt2->fetch();
                $_SESSION['name'] = $user2['name'];

                header('Location: agency/dashboard.php');
            } else if ($user['type'] === 'traveller') {
                $stmt2 = $pdo->prepare('SELECT name FROM traveller WHERE userID = :userid');
                $stmt2->execute(['userid' => $user['userId']]);
                $user2 = $stmt2->fetch();

                $_SESSION['name'] = $user2['name'];
                
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
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

    <div>

        <form class= "form-card" action="login.php" method="POST">
            <h2 style="text-align: center;">Login to Tripistry</h2>

            <?php if (!empty($error_message)): ?>
                <div class="error-box" style="color: red; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>   

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Sign In</button>

            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>

    </div>
<?php include 'components/footer.php'; ?>
</body>
</html>