<?php
session_start();
include 'Config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role']; // Should resolve to either 'Traveller' or 'Agency'

    if (in_array($role, ['Traveller', 'Agency'])) {

        if ($role === 'Agency') {
            header("Location: registerAgency.php");
        } else if ($role === 'Traveller') {
            header("Location: registerTraveller.php");
        } 
    } else {
        $message = "Please select an account type.";
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
        <label for="role">Account Type</label>
        <select id="role" name="role" required>
            <option value="Traveller">Traveller (Browse & Book)</option>
            <option value="Agency">Travel Agency (Curate & Sell)</option>
        </select>

        <button type="submit" class="btn">Next Step</button>
    </form>
</div>
<?php include 'components/footer.php'; ?>