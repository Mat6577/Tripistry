<?php
// traveller/book-package.php
session_start();
include '../Config/db.php';

// 1. Strict Security Gateway
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'traveller') {
    header('Location: ../login.php?error=unauthorized');
    exit;
}

// 2. Catch and validate the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $package_id = intval($_POST['package_id']); // Force integer for safety

    if ($package_id > 0) {
        try {
            // 3. Insert the exact package ID into the booking table
            // Utilizing the verified column mapping from your schema
            $stmt = $pdo->prepare("
                INSERT INTO booking (travellerId, packageId, bookingDate) 
                VALUES (:user_id, :package_id, NOW())
            ");
            
            $stmt->execute([
                'user_id' => $user_id,
                'package_id' => $package_id
            ]);

            // 4. Redirect straight to their bookings dashboard upon success
            header('Location: bookings.php?booking=success');
            exit;

        } catch (\PDOException $e) {
            error_log("Direct Booking Bug: " . $e->getMessage());
            // Redirect back to dashboard if it fails (e.g., duplicate booking)
            header('Location: dashboard.php?booking=error');
            exit;
        }
    }
}

// Fallback if accessed via GET instead of POST, or if ID is missing
header('Location: dashboard.php?booking=invalid');
exit;
?>