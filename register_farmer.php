<?php
session_start();
include('db_connect.php');

$errors = array();  // Initialize error array

$name = $age = $address = $phone = $whatsapp = $email = $password = $created_at = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = trim($_POST['name']);
  $age = trim($_POST['age']);
  $address = trim($_POST['address']);
  $phone = trim($_POST['phone']);
  $whatsapp = trim($_POST['whatsapp']);
  $email = trim($_POST['emailid']);
  $passw = trim($_POST['pwsd']);
  $confirmpass = trim($_POST['confirmpassword']);

  // Validations
  if (empty($name)) {
    $errors['name'] = "Name is required.";
  } elseif (is_numeric($name)) {
    $errors['name'] = "Please enter a valid name.";
  }

  if (empty($age)) {
    $errors['age'] = "Age is required.";
  } elseif (!is_numeric($age) || $age <= 0 || $age >= 120) {
    $errors['age'] = "Please enter a valid age.";
  }

  if (empty($address)) {
    $errors['address'] = "Address is required.";
  }

  if (empty($phone)) {
    $errors['phone'] = "Phone number is required.";
  } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
    $errors['phone'] = "Phone number must be exactly 10 digits.";
  }

  if (empty($whatsapp)) {
    $errors['whatsapp'] = "WhatsApp number is required.";
  } elseif (!preg_match("/^[0-9]{10}$/", $whatsapp)) {
    $errors['whatsapp'] = "WhatsApp number must be exactly 10 digits.";
  }

  if (empty($email)) {
    $errors['emailid'] = "Email is required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['emailid'] = "Invalid email format.";
  }

  if (empty($passw)) {
    $errors['pwsd'] = "Password is required.";
  } elseif (strlen($passw) < 6) {
    $errors['pwsd'] = "Password must be at least 6 characters.";
  }

  if (empty($confirmpass)) {
    $errors['confirmpassword'] = "Please confirm your password.";
  } elseif ($passw !== $confirmpass) {
    $errors['confirmpassword'] = "Passwords do not match.";
  }

  // Check for duplicate email
  if (empty($errors)) {
    $checkEmail = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
      $errors['emailid'] = "Email is already registered.";
    }
    $checkEmail->close();
  }

  // Insert if valid
  if (empty($errors)) {
    $hashed_password = password_hash($passw, PASSWORD_DEFAULT);

    $sql = "INSERT INTO farmers (name, age, address, phone, whatsapp, email, password, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $name, $age, $address, $phone, $whatsapp, $email, $hashed_password, $created_at);

    if ($stmt->execute()) {
      $_SESSION['farmer_id'] = $stmt->insert_id;
      echo '<script>alert("Registration successful. Now you can login."); window.location="login.php";</script>';
    } else {
      echo "Database error: " . $stmt->error;
    }

    $stmt->close();
  }

  $conn->close();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Farmer Registration | RiceLink</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      height: 100vh;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .container {
      display: flex;
      width: 100%;
    }

    .left-end {
      width: 60%;
    }

    .left-end h1 {
      margin: 50px auto 10px 250px;
    }

    .subheading {
      font-size: 16px;
      color: #555;
      margin: auto 80px 30px 250px;
    }

    .signup-container {
      margin-left: 180px;
      margin-top: 70px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    input {
      width: 60%;
      border: none;
      border-bottom: 1px solid #ccc;
      padding: 20px 5px 8px;
      font-size: 14px;
      outline: none;
    }

    .check-box {
      display: flex;
      align-items: center;
      font-size: 13px;
      color: #8d8a8a;
      width: 60%;
      margin-top: 10px;
      gap: 8px;
    }

    .password-toggle {
      position: relative;
      width: 60%;
    }

    .password-toggle i {
      position: absolute;
      right: 10px;
      top: 35%;
      cursor: pointer;
    }

    .btn-signup {
      margin-top: 20px;
      padding: 10px 20px;
      border-radius: 25px;
      background-color: #1d4d2d;
      border: none;
      color: white;
    }

    .btn-signup:hover {
      background-color: #3a724c;
    }

    .right-end {
      width: 40%;
      background: url(images/1.jpg);
      background-size: cover;
      min-height: 100vh;
    }

    .content {
      align-items: center;
      background-color: rgba(235, 231, 225, .8);
      width: 400px;
      margin-top: 230px;
      margin-left: 70px;
      border-radius: 20px;
    }

    .content h1 {
      text-align: center;
      padding-top: 15px;
    }

    .content p {
      text-align: center;
      font-size: 17px;
    }

    .btn-signin {
      margin-top: 10px;
      padding: 10px 20px;
      border-radius: 25px;
      background-color: #1d4d2d;
      border: none;
      color: white;
      margin-left: 150px;
      margin-bottom: 10px;
    }

    .btn-signin:hover {
      background-color: #3a724c;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="left-end">
      <h1>Create Account</h1>
      <p class="subheading">Join RiceLink to connect directly with buyers, manage your rice listings, and grow your business with ease.</p>
      <div class="signup-container">
        <form id="signupForm" action="register.php" method="POST" onsubmit="return validateForm();">
          <input type="text" name="full_name" placeholder="Full Name" required />
          <input type="number" name="age" placeholder="Age" required />
          <input type="text" name="address" placeholder="Address" required />
          <input type="tel" name="phone_number" placeholder="Phone Number" required />
          <input type="tel" name="whatsapp_number" placeholder="WhatsApp Number" required />
          <input type="email" name="email" placeholder="Email" required />

          <div class="password-toggle">
            <input type="password" name="password" id="password" placeholder="Password" required />
            <i class="fas fa-eye" onclick="togglePassword('password')"></i>
          </div>

          <div class="password-toggle">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required />
            <i class="fas fa-eye" onclick="togglePassword('confirm_password')"></i>
          </div>

          <div class="check-box">
            <input type="checkbox" id="chckbox" required />
            <label for="chckbox">I accept terms and conditions & privacy policy</label>
          </div>

          <button class="btn-signup" type="submit">Sign Up</button>
        </form>
      </div>
    </div>

    <div class="right-end">
      <div class="content">
        <h1>Welcome Back!</h1>
        <p>If you already have an account, login here</p>
        <button class="btn-signin" onclick="window.location.href='login_farmer.html';">Sign In</button>
      </div>
    </div>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      input.type = input.type === "password" ? "text" : "password";
    }

    function validateForm() {
      const form = document.forms["signupForm"];
      const age = form["age"].value;
      const phone = form["phone_number"].value;
      const whatsapp = form["whatsapp_number"].value;
      const password = form["password"].value;
      const confirmPassword = form["confirm_password"].value;

      const phonePattern = /^[0-9]{10}$/;

      if (age < 18 || age > 100) {
        alert("Age must be between 18 and 100.");
        return false;
      }
      if (!phonePattern.test(phone)) {
        alert("Phone number must be 10 digits.");
        return false;
      }
      if (!phonePattern.test(whatsapp)) {
        alert("WhatsApp number must be 10 digits.");
        return false;
      }
      if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return false;
      }

      return true;
    }
  </script>
</body>

</html>