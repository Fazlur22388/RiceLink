<?php
session_start();
include('../backend/db_connect.php');
error_reporting(0);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["log"])) {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, email, password FROM farmers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $storedPassword = $row['password'];
        if (password_verify($password, $storedPassword) || $password === $storedPassword) {
            $_SESSION['farmer_id'] = $row['id'];
            $_SESSION['role'] = 'farmer';
            header("Location: farmer_dashboard.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['login_error'] = "User not found.";
    }

    // Redirect back to login with error
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Farmer Login | RiceLink</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      height: 100vh;
      overflow-y: hidden;
      font-family: Arial, sans-serif;
    }

    .container {
      display: flex;
      width: 100%;
    }

    .left-end {
      width: 40%;
      background: url(images/8.jpg) no-repeat;
      background-size: cover;
      min-height: 100vh;
    }

    .content {
      background-color: rgba(255, 255, 255, 0.5);
      border-radius: 20px;
      width: 400px;
      margin: 250px auto;
      text-align: center;
    }

    .content h1 {
      padding-top: 15px;
      font-size: 34px;
      padding-bottom: 15px;
      font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
        "Lucida Sans", Arial, sans-serif;
    }

    .content p {
      font-size: 15px;
    }

    .btn-signup {
      margin-top: 30px;
      padding: 10px 20px;
      border-radius: 25px;
      background-color: #201905;
      border: none;
      color: white;
      margin-bottom: 10px;
      cursor: pointer;
    }

    .btn-signup:hover {
      background-color: #58450f;
    }

    input {
      width: 100%;
      border: none;
      border-bottom: 1px solid #ccc;
      padding: 50px 0 8px;
      font-size: 12px;
      text-transform: uppercase;
      outline: none;
    }

    .right-end {
      width: 100%;
    }

    .right-end h1 {
      margin: 180px auto 10px 280px;
      font-size: 30px;
      font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
        "Lucida Sans", Arial, sans-serif;
    }

    .subheading {
      font-size: 15px;
      color: #555;
      margin: auto 180px 40px;
    }

    .signin-container {
      margin-left: 120px;
      margin-right: 100px;
    }

    .btn-signin {
      margin-top: 50px;
      padding: 10px 20px;
      border-radius: 25px;
      background-color: #201905;
      border: none;
      color: white;
      margin-left: 650px;
      cursor: pointer;
    }

    .btn-signin:hover {
      background-color: #58450f;
    }

    .box {
      margin-top: 20px;
    }

    .error-message {
      text-align: center;
      color: red;
      font-size: 14px;
      margin-top: 10px;
    }
  </style>
</head>

<body>
<div class="container">
  <div class="left-end">
    <div class="content">
      <h1>Welcome Back!</h1>
      <p>Don't have an account? Sign Up here</p>
      <button class="btn-signup" onclick="window.location.href='register_farmer.php';">
        Sign Up
      </button>
    </div>
  </div>

  <form id="signinForm" method="POST" enctype="multipart/form-data">
    <div class="right-end">
      <h1>Login to your Account</h1>
      <p class="subheading">
        Join RiceLink to access farmer dashboard and manage your rice posts easily.
      </p>
    </div>

    <div class="signin-container">
      <div class="box">
        <input type="email" name="email" placeholder="Email" required />
      </div>

      <div class="box">
        <input type="password" name="password" placeholder="Password" required />
      </div>
    </div>

    <button class="btn-signin" type="submit" name="log">Sign In</button>
  </form>
</div>

<?php if (isset($_SESSION['login_error'])): ?>
<script>
  alert("<?php echo $_SESSION['login_error']; ?>");
</script>
<?php unset($_SESSION['login_error']); endif; ?>

</body>
</html>
