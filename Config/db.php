<?php
     $host = 'wheatley.cs.up.ac.za';
     $db   = 'u25176502_tripistry';
     $user = 'u25176502'; 
     $pass = 'M5RNWUCZ4Z6TTBWXRPAY5Y7RB2CZLDUD'; // Set your MariaDB password here
     $charset = 'utf8mb4';

     $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
     $options = [
     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::ATTR_EMULATE_PREPARES   => false, 
     ];

     try {
          $pdo = new PDO($dsn, $user, $pass, $options);
          $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          // echo "Connected successfully";
          error_log("Connected successfully");
     } catch (\PDOException $e) {
          // throw new \PDOException($e->getMessage(), (int)$e->getCode());
          echo $e;
     }
?>