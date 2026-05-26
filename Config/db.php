<?php
$host = 'wheatley.cs.up.ac.za';
$db   = 'u25176502_tripistry';
$user = 'u25176502'; 
$pass = 'M5RNWUCZ4Z6TTBWXRPAY5Y7RB2CZLDUD'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, 
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // error_log("Database connected successfully"); // Optional: Keep for your own logs
} 
catch (\PDOException $e) {
    // SECURITY FIX: Never echo the full $e object as it exposes your password.
    // Instead, log the detailed error and show a safe message to the user.
    error_log("DB Connection Error: " . $e->getMessage());
    die("Critical Error: Could not connect to the database. Please try again later.");
}
?>
