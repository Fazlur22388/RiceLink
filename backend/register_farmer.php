<?php 
session_start();
include('db_connect.php');


$name = $age = $address = $phone = $whatsapp = $email = $password = $created_at =  ""; // Initialize variables

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 👇 CAPTCHA Verification - Add this early
    $recaptcha_secret = "6LdWfzErAAAAAAtqS9QQ6wKzghekK9S6BSIM53bz"; 
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $captcha = json_decode($verify);

    if (!$captcha->success) {
        $errors['captcha'] = "Please verify that you're not a robot.";
    }


    $name = trim($_POST['name']); 
    $age = trim($_POST['age']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $whatsapp = trim($_POST['whatsapp']);
    $email = trim($_POST['emailid']);
    $passw = trim($_POST['pwsd']);
    $confirmpass = trim($_POST['confirmpassword']);
    $created_at = date("Y-m-d H:i:s"); // better to auto-generate timestamp


    // Name Validation
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    }elseif (is_numeric($name)){
        $errors['name'] = "Please enter a valid name";
    }

    // Age Validation
    if (empty($age)) {
        $errors['age'] = "Age is required.";
    } elseif (!is_numeric($age) || $age <= 0 || $age >=120) {
        $errors['age'] = "Please enter a valid age.";
    }

    // Email Validation
    if (empty($email)) {
        $errors['emailid'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['emailid'] = "Invalid email format.";
    }

    // Password Validation
    if (empty($passw)) {
        $errors['pwsd'] = "Password cannot be empty.";
    } elseif (strlen($passw) < 6) {
        $errors['pwsd'] = "Password must be at least 6 characters.";
    }

    // Confirm Password
    if (empty($confirmpass)) {
        $errors['confirmpassword'] = "Please confirm your password.";
    } elseif ($passw !== $confirmpass) {
        $errors['confirmpassword'] = "Passwords do not match.";
    }

    // Phone Validation
    if (empty($phone)) {
        $errors['phone'] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = "Phone number must be exactly 10 digits.";
    }

    // WhatsApp Validation
    if (empty($whatsapp)) {
        $errors['whatsapp'] = "WhatsApp number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $whatsapp)) {
        $errors['whatsapp'] = "WhatsApp number must be exactly 10 digits.";
    }

    // Address Validation
    if (empty($address)) {
        $errors['address'] = "Address cannot be empty.";
    }

    // If no errors, proceed
    if (empty($errors)) {
        $hashed_password = password_hash($passw, PASSWORD_DEFAULT);

        // Insert query
        $sql = "INSERT INTO farmers (name, age, address, phone, whatsapp, email, password, created_at) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssssss", $name, $age, $address, $phone, $whatsapp, $email, $hashed_password, $created_at);

        if (!$stmt->execute()) {
            die("Execution failed: " . $stmt->error);
        } else {
            echo '<script>alert("Registration successful. Now you can login"); window.location="login.php";</script>';
        }

        $stmt->close();
    } 
    
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(22, 199, 96);
        }
        .container {
            width: 40%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px #0003;
        }
        h3 {
            color: rgb(22, 199, 96);
            text-align: center;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .error {
            color: red;
            font-size: 14px;
        }
        .btn {
            background: rgb(22, 199, 96);
            color: white;
            border: none;
            padding: 10px;
            margin-top: 15px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            border-radius: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

</head>
<body>

<div class="container">
    <h3>Sign Up</h3>
    <form method="post" action="">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" placeholder="Enter your name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    <span class="error"><?= $errors['name'] ?? '' ?></span>

    <label for="age">Age:</label>
    <input type="number" name="age" id="age" placeholder="Enter your age" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
    <span class="error"><?= $errors['age'] ?? '' ?></span>

    <label for="address">Address:</label>
    <textarea name="address" id="address" placeholder="Enter Address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
    <span class="error"><?= $errors['address'] ?? '' ?></span>

    <label for="phone">Phone Number:</label>
    <input type="text" name="phone" id="phone" placeholder="Enter Phone Number" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
    <span class="error"><?= $errors['phone'] ?? '' ?></span>

    <label for="whatsapp">WhatsApp Number:</label>
    <input type="text" name="whatsapp" id="whatsapp" placeholder="Enter WhatsApp Number" value="<?= htmlspecialchars($_POST['whatsapp'] ?? '') ?>">
    <span class="error"><?= $errors['whatsapp'] ?? '' ?></span>

    <label for="emailid">Email:</label>
    <input type="email" name="emailid" id="emailid" placeholder="Enter Email" value="<?= htmlspecialchars($_POST['emailid'] ?? '') ?>">
    <span class="error"><?= $errors['emailid'] ?? '' ?></span>

    <label for="pwsd">Password:</label>
    <input type="password" name="pwsd" id="pwsd" placeholder="Enter Password">
    <span class="error"><?= $errors['pwsd'] ?? '' ?></span>

    <label for="confirmpassword">Confirm Password:</label>
    <input type="password" name="confirmpassword" id="confirmpassword" placeholder="Confirm Password">
    <span class="error"><?= $errors['confirmpassword'] ?? '' ?></span>

    <!-- Add this inside your <form> -->
        <div class="g-recaptcha" data-sitekey="6LdWfzErAAAAADp0LpnvP845O4a7RmQhpvsB2Czx"></div>
        <input type="submit" value="Sign Up" name="signup" class="btn">
</form>

</div>

</body>
</html>
