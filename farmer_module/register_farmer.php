<?php
session_start();
include('../backend/db_connect.php');

$errors = [];
$created_at = date("Y-m-d H:i:s");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function clean($data, $conn) {
        return mysqli_real_escape_string($conn, trim($data));
    }

    $name = clean($_POST['name'], $conn);
    $age = intval($_POST['age']);
    $address = clean($_POST['address'], $conn);
    $city = clean($_POST['city'], $conn);
    $district = clean($_POST['district'], $conn);
    $phone = clean($_POST['phone_number'], $conn);
    $whatsapp = clean($_POST['whatsapp_number'], $conn);
    $email = clean($_POST['email'], $conn);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name)) $errors[] = "Name is required.";
    if ($age < 18 || $age > 100) $errors[] = "Age must be between 18 and 100.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($city)) $errors[] = "City is required.";
    if (empty($district)) $errors[] = "District is required.";
    if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Valid phone is required.";
    if (!preg_match('/^\d{10}$/', $whatsapp)) $errors[] = "Valid WhatsApp number required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    // Check email
    $check = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) $errors[] = "Email already exists.";
    $check->close();

    // Upload profile picture
    $profile_picture = "";
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../product_pics/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['profile_picture']['name']);
        $uploadPath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
            $profile_picture = $fileName;
        } else {
            $errors[] = "Image upload failed.";
        }
    } else {
        $errors[] = "Profile picture is required.";
    }

    // Insert to DB
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO farmers (name, age, address, city, district, phone, whatsapp, email, password, created_at, profile_picture)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssssss", $name, $age, $address, $city, $district, $phone, $whatsapp, $email, $hashed_password, $created_at, $profile_picture);

        if ($stmt->execute()) {
            $_SESSION['farmer_id'] = $stmt->insert_id;
            echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
        } else {
            echo "DB Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fix form errors');</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Farmer Registration | RiceLink</title>
    <style>
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }

      body {
        height: 100vh;
        overflow-y: hidden;
      }

      input,
      select,
      label,
      p {
        font-family: Arial, Helvetica, sans-serif;
      }

      input {
        width: 100%;
        border: none;
        border-bottom: 1px solid #ccc;
        padding: 20px 0 8px;
        font-size: 12px;
        text-transform: uppercase;
        outline: none;
      }

      .container-1 {
        display: flex;
        width: 100%;
      }

      .left-end {
        width: 60%;
      }

      .left-end h1 {
        margin: 90px auto 10px 280px;
        font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
          "Lucida Sans", Arial, sans-serif;
        font-weight: lighter;
      }

      .subheading {
        font-size: 15px;
        color: #555;
        margin: auto 120px 50px;
      }

      .signup-container {
        margin-left: 120px;
        display: flex;
        flex-direction: column;
      }

      .signup-container p {
        font-size: 14px;
        text-transform: uppercase;
        color: #3c3d41;
      }

      .box {
        display: flex;
        gap: 32px;
        padding: 12px 100px 12px 0;
      }

      .dob {
        display: flex;
        width: 600px;
      }

      .dob label,
      .farm-type label,
      .license label,
      .membership-label {
        padding: 20px 5px 8px;
        font-size: 12px;
        text-transform: uppercase;
        color: #797373;
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

      .btn-signup,
      .btn-signin {
        margin-top: 10px;
        padding: 10px 20px;
        border-radius: 25px;
        background-color: #1d4d2d;
        border: none;
        color: white;
      }

      .btn-signup:hover,
      .btn-signin:hover {
        background-color: #3a724c;
      }

      .btn-signin {
        margin-left: 150px;
        margin-bottom: 20px;
      }

      .arrow-wrapper-1,
      .arrow-wrapper-2 {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
      }

      .arrow-wrapper-1 {
        margin-left: 130px;
      }

      .arrow-wrapper-2 {
        margin-left: -130px;
      }

      .arrow-1,
      .arrow-2 {
        background-color: #ffffff;
        color: rgb(31, 112, 38);
        width: 40px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 24px;
      }

      .right-end,
      .profile {
        width: 40%;
        background: url(images/1.jpg) no-repeat;
        background-size: cover;
        min-height: 100vh;
        position: relative;
      }

      .content,
      .profile-upload-container {
        background-color: rgba(235, 231, 225, 0.8);
        border-radius: 20px;
      }

      .content {
        width: 400px;
        margin: 230px 0 0 70px;
        align-items: center;
      }

      .content h1,
      .profile-upload-container h1 {
        text-align: center;
        padding-top: 15px;
        font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
          "Lucida Sans", Arial, sans-serif;
        font-weight: lighter;
        font-size: 24px;
        padding-bottom: 15px;
      }

      .content p,
      .profile-upload-container p {
        text-align: center;
        font-size: 15px;
        padding-top: 5px;
      }

      .profile-upload-container {
        text-align: center;
        padding: 40px 30px;
        color: #000000;
        margin: 150px 50px;
      }

      .profile-upload-container .highlight {
        color: #000000;
      }

      .upload-circle {
        display: inline-block;
        width: 120px;
        height: 120px;
        background-color: #1d4d2d;
        border-radius: 50%;
        position: relative;
        cursor: pointer;
        margin-bottom: 10px;
        transition: transform 0.2s;
      }

      .upload-circle:hover {
        transform: scale(1.05);
      }

      .plus {
        font-size: 50px;
        color: white;
        line-height: 120px;
        display: block;
      }

      .upload-text {
        font-size: 5px;
        color: #726e6e;
        margin-bottom: 20px;
      }

      .contact-info {
        border-bottom: #1d4d2d 2px solid;
        padding-bottom: 30px;
        width: 100%;
      }

      #contact {
        margin-top: 50px;
      }

      #profetional {
        margin-top: 40px;
      }

      .farm-type,
      .membership,
      .license {
        flex: 1 1 100%;
      }

      .farm-type {
        margin-top: 10px;
        margin-left: -5px;
      }

      .membership {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        gap: 10px;
      }

      .checkbox-membership {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #797373;
      }

      .check-box {
        display: flex;
        align-items: center;
        font-size: 13px;
        color: #8d8a8a;
        margin-top: 10px;
        gap: 8px;
        cursor: pointer;
        margin-left: 40px;
      }

      .check-box input[type="checkbox"] {
        appearance: none;
        width: 15px;
        height: 3px;
        border: 1px solid #182512;
        border-radius: 10px;
        outline: none;
        cursor: pointer;
        position: relative;
      }

      .check-box input[type="checkbox"]:checked {
        background-color: #1d4d2d;
        border-color: #1d4d2d;
      }

      .check-box input[type="checkbox"]:checked::after {
        content: "✓";
        position: absolute;
        color: white;
        font-size: 12px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }

      .check-box a {
        color: #1d4d2d;
        text-decoration: none;
      }

      .check-box a:hover {
        text-decoration: underline;
      }
    </style>
  </head>

  <body>
    <div class="container-1" id="section-1">
      <div class="left-end">
        <h1>Create Account</h1>
        <p class="subheading">
          Join RiceLink to connect directly with buyers, manage your rice
          listings, and grow your business with ease.
        </p>
        <div class="signup-container">
          <form
            id="signupForm"
            action="register.php"
            method="POST"
            enctype="multipart/form-data"
            onsubmit="return validateForm();"
          >
            <p>User Information</p>

            <div class="box">
              <input
                type="text"
                name="name"
                placeholder="Full Name"
                required
              />
            </div>

            <div class="box">
              <div class="dob">
                <label for="dob">DOB / Age</label>
                <input type="number" name="age"  required />
              </div>
              <input
                type="tel"
                name="phone_number"
                placeholder="Phone Number"
                required
              />
            </div>

            <div class="box">
              <input
                type="tel"
                name="whatsapp_number"
                placeholder="WhatsApp Number"
                required
              />
              <input type="email" name="email" placeholder="Email" required />
            </div>

            <div class="box">
              <div class="password-toggle">
                <input
                  type="password"
                  name="password"
                  id="password"
                  placeholder="Password"
                  required
                />
                <i class="fas fa-eye" onclick="togglePassword('password')"></i>
              </div>
              <div class="password-toggle">
                <input
                  type="password"
                  name="confirm_password"
                  id="confirm_password"
                  placeholder="Confirm Password"
                  required
                />
                <i
                  class="fas fa-eye"
                  onclick="togglePassword('confirm_password')"
                ></i>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="arrow-wrapper-1">
        <div class="arrow-1" onclick="nextSection()">&gt;</div>
      </div>

      <div class="right-end">
        <div class="content">
          <h1>Welcome Back!</h1>
          <p>If you already have an account, login here</p>
          <button
            class="btn-signin"
            onclick="window.location.href='login.php';"
          >
            Sign In
          </button>
        </div>
      </div>
    </div>

    <!-- section 2 -->
    <div class="container-2" id="section-2" style="display: none">
      <div class="profile">
        <div class="profile-upload-container">
          <h1>Please Upload Your Profile Picture<br />here!</h1>

          <label for="profile-upload" class="upload-circle">
            <span class="plus">+</span>
          </label>
          <input
            type="file"
            id="profile-upload"
            name="profile_picture"
            accept="image/*"
            onchange="previewImage()"
            hidden
          />

          <p class="upload-text">
            Choose your profile photo to personalize your account.
          </p>

          <label class="check-box">
            <input type="checkbox" />
            I accept <a>terms and conditions</a> & <a>privacy policy</a>
          </label>

          <button class="btn-signup" type="submit">Sign Up</button>
        </div>
      </div>

      <div class="arrow-wrapper-2">
        <div class="arrow-2" onclick="prevSection()">&lt;</div>
      </div>

      <div class="left-end">
        <div class="signup-container">
          <form
            id="signupForm"
            action="register.php"
            method="POST"
            onsubmit="return validateForm();"
          >
            <!-- contact info -->
            <div class="contact-info">
              <p id="contact">Contact Information</p>

              <div class="box">
                <input
                  type="text"
                  name="address"
                  placeholder="Address"
                  required
                />
              </div>

              <div class="box">
                <input
                  type="text"
                  name="district"
                  placeholder="District / Provice"
                  required
                />
                <input
                  type="text"
                  name="city"
                  placeholder="City / Town"
                  required
                />
                
              </div>
            </div>

            <!-- profetional info -->
            <div class="profetional-info">
              <p id="profetional">Profetional Information</p>
            </div>
          </form>
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
        if (password !== confirmPassword) {
          alert("Passwords do not match.");
          return false;
        }

        return true;
      }

      function nextSection() {
        document.getElementById("section-1").style.display = "none";
        document.getElementById("section-2").style.display = "flex";
      }

      function prevSection() {
        document.getElementById("section-2").style.display = "none";
        document.getElementById("section-1").style.display = "flex";
      }
    </script>
  </body>
</html>
