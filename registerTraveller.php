<?php
session_start();
include 'Config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role = 'traveller';
    $streetAddress = isset($_POST['address1']) ? trim($_POST['address1']) : '';
    if (!empty($_POST['address2'])) {
        $streetAddress .= ', ' . trim($_POST['address2']);
    }
    $town = isset($_POST['town']) ? trim($_POST['town']) : '-';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $provinceOrState = isset($_POST['provinceOrState']) ? trim($_POST['provinceOrState']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
    $idNumber = isset($_POST['idNo']) ? trim($_POST['idNo']) : '';

    try {
        $dob = !empty($_POST['dob']) ? new DateTime($_POST['dob']) : null;
    } catch (Exception $e) {
        $dob = null;
    }

    if ($dob) {
        $dob->setTime(0, 0, 0);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
    
        if ($dob > $today) {
            $message = "Date of birth cannot be in the future.";
        }
    }


    if (empty($email) || empty($password) || empty($city) || empty($provinceOrState) || empty($country) || empty($name) || empty($surname) || empty($idNumber) || empty($dob)) {
        if (empty($message)) {
            $message = "Please complete all required fields.";
        }
    }

    if ($message === '' && !empty($email) && !empty($password) && in_array($role, ['traveller', 'agency'])) {
        // Securely hash the password string before database ingestion
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, type, streetAddress, town, city, provinceOrState, country, password_hash) VALUES (:email, :role, :streetAddress, :town, :city, :provinceOrState, :country, :password_hash)");
            $stmt->execute([
                'email' => $email,
                'password_hash' => $password_hash,
                'role' => $role,
                'streetAddress' => $streetAddress,
                'town' => $town,
                'city' => $city,
                'provinceOrState' => $provinceOrState,
                'country'=> $country
            ]);

            //get userId
            $stmt2 = $pdo->prepare('SELECT userId FROM users WHERE email = :email');
            $stmt2->execute(['email' => $email]);
            $user = $stmt2->fetch();

            if ($user) {
                $stmt3 = $pdo->prepare("INSERT INTO traveller (userID, name, surname, idNumber, dateOfBirth) VALUES (:userId, :name, :surname, :idNumber, :dob);");
                $stmt3->execute([
                    'userId' => $user['userId'],
                    'name' => $name,
                    'surname' => $surname,
                    'idNumber' => $idNumber,
                    'dob' => $dob ? $dob->format('Y-m-d') : null
                ]);

                //get rid of "-"
                $stmnt4 = $pdo->prepare("UPDATE users SET town = NULL WHERE town = '-';");
                $stmnt4->execute();                

                header("Location: login.php?registration=success");
            } else {
                header("Location: login.php?registration=failed");
                $message = "Unknown error. Please try again later";
            }
            exit;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            $message = "Registration failed: Account may already exist. <br>".$e->getMessage();
        }
    } else if ($message === '') {
        $message = "Please complete all fields with accurate selections.";
    }
}
include 'components/header.php';
?>
<div class="form-card">
    <h2>Create your Tripistry Account</h2>
    <?php if (!empty($message)): ?>
        <p class="error-msg"><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="registerTraveller.php" method="POST">
        <label>Fields marked with a * are required</label>

        <label><b>General Information</b></label>

        <label for="name" id="name">Name*</label>
        <input type="text" id="name" name="name" required>
        
        <label for="surname" id="surname">Surname*</label>
        <input type="text" id="surname" name="surname" required>
        
        <label for="idNo" id="idNo">ID Number*</label>
        <input type="text" id="idNo" name="idNo" required>     
        
        <label for="dob" id="dob">Date Of birth*</label>
        <input type="date" id="dob" name="dob" required>  

        <label for="email">Email Address*</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password*</label>
        <input type="password" id="password" name="password" required>

        <label><b>Address (for billing statements and contracts)</b></label>
        <label for="address1">Address Line 1*</label>
        <input type="text" id="address1" name="address1" required>

        <label for="address2">Address Line 2</label>
        <input type="text" id="address2" name="address2">

        <label for="town">Town</label>
        <input type="text" id="town" name="town">

        <label for="city">City*</label>
        <input type="text" id="city" name="city" required>

        <label for="provinceOrState">Province/State*</label>
        <input type="text" id="provinceOrState" name="provinceOrState" required>
    
        <label for="country">Country*</label>
        <select id="country" name="country" required>
            <?php include 'Components/countryList.php'; ?>
        </select>

        <label for="role">You are signing up as a</label>
        <select id="role" name="role" required disabled>
            <option value="traveller selected">Traveller (Browse & Book)</option>
        </select>

        <div>
            <a href = "register.php" class = "registerLink">Change Account Types</a>
        </div>        

        <button type="submit" class="btn">Register</button>

        <div>
            <a href = "login.php" class = "registerLink">Already have an account?</a>
        </div>
    </form>
</div>
<?php include 'components/footer.php'; ?>