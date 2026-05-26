<?php
     require dirname(__DIR__) . '/vendor/autoload.php';
     
     use Dotenv\Dotenv;

     // Load environment variables from .env file
     $dotenv = Dotenv::createImmutable(dirname(__DIR__));
     $dotenv->load();

     // Retrieve environment variables
     $host = $_ENV['DB_HOST'];
     $db = $_ENV['DB_DATABASE'];
     $user = $_ENV['DB_USER'];
     $pass = $_ENV['DB_PASS'];
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