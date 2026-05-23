<?php
session_start();
include 'Config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Should resolve to either 'Traveller' or 'Agency'

    if (!empty($email) && !empty($password) && in_array($role, ['Traveller', 'Agency'])) {
        // Securely hash the password string before database ingestion
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password, password_hash, type) VALUES (:email, :password,:password_hash, :role)");
            $stmt->execute([
                'email' => $email,
                'password_hash' => $password_hash,
                'role' => strtolower($role),
                'password' => $password
            ]);
            header("Location: login.php?registration=success");
            exit;
        } catch (\PDOException $e) {
            $message = "Registration failed: Account may already exist.";
        }
    } else {
        $message = "Please complete all fields with accurate selections.";
    }
}
include 'components/header.php';
?>
<div class="form-card">
    <h2>Create your Tripistry Account</h2>
    <?php if (!empty($message)): ?>
        <p class="error-msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="role">Account Type</label>
        <select id="role" name="role" required>
            <option value="Traveller">Traveller (Browse & Book)</option>
            <option value="Agency">Travel Agency (Curate & Sell)</option>
        </select>

        <button type="submit" class="btn">Register</button>
    </form>
</div>
<?php include 'components/footer.php'; ?>