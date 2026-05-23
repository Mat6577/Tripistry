<?php
    // $password = 'Alpen_Garmisch4';
    // echo $password;
    // echo '<br>';

    // echo "password hashed: ";
    // $password_hash = password_hash($password, PASSWORD_BCRYPT);
    // echo $password_hash . '<br>';

    // echo "password stored in db: "; 
    // echo '$2y$10$mnbvcxzqwertyuioplkjgfdsaqasdfghjklpoiuytrewqmnbv<br>';
    // echo 'match = ' . ($password_hash == '$2y$10$mnbvcxzqwertyuioplkjgfdsaqasdfghjklpoiuytrewqmnbv'?'true':'false');

    // migrate_passwords.php

    // 1. Establish your database connection
    include 'Config/db.php';
    
    // 3. Fetch all users (selecting id, plain password, and your password_hash field)
    // NOTE: Change `password` or `password_hash` to match your exact column names
    $stmt = $pdo->query("SELECT `userId`, `password`, `password_hash` FROM `users`");
    $users = $stmt->fetchAll();

    // 4. Prepare the UPDATE statement once outside the loop for high performance
    $updateStmt = $pdo->prepare("UPDATE `users` SET `password_hash` = :newHash WHERE `userId` = :id");

    if ($users) {
        $updatedCount = 0;

        // 5. Process each user row
        foreach ($users as $userRow) {
            $userId        = $userRow['userId'];
            $plainPassword = $userRow['password']; // Taking the raw string from your password field

            // Safety Check: Skip if this row has already been hashed before
            if (strpos($plainPassword, '$2y$') === 0) {
                continue;
            }

            // 6. Generate the real cryptographic BCrypt hash
            $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

            // 7. Execute the update using safe parameter binding
            $updateStmt->execute([
                'newHash' => $hashedPassword,
                'id'      => $userId
            ]);

            $updatedCount++;
        }

        echo "<h3>PDO Migration Complete!</h3>";
        echo "Successfully hashed and updated " . $updatedCount . " records in the password_hash column.";
    } else {
        echo "Error retrieving records: " . mysqli_error($conn);
    }

    // // Close connection
    // mysqli_close($conn);

?>