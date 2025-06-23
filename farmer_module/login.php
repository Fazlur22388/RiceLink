<?php
session_start();
include('../backend/db_connect.php');
error_reporting(0);

// Capture previous values if login fails
$errorMessage = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : "";
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Login - RiceLink</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Login - RiceLink</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-box {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(44, 62, 80, 0.15);
            padding: 40px 32px 32px 32px;
            max-width: 370px;
            width: 100%;
            margin: 40px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .auth-box h2 {
            margin-bottom: 8px;
            color: #388E3C;
            font-weight: 700;
        }
        .auth-box p {
            margin-bottom: 24px;
            color: #555;
        }
        .login-form {
            width: 100%;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-size: 15px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cfd8dc;
            border-radius: 6px;
            font-size: 15px;
            background: #f9f9f9;
            transition: border 0.2s;
        }
        .form-group input:focus {
            border-color: #4CAF50;
            outline: none;
            background: #fff;
        }
        .btn {
            width: 100%;
            padding: 12px 0;
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #388E3C;
        }
        .error-msg {
            color: #d32f2f;
            background: #ffebee;
            border: 1px solid #ffcdd2;
            border-radius: 4px;
            padding: 8px 12px;
            margin: 16px 0 0 0;
            font-size: 14px;
            text-align: center;
            min-height: 20px;
        }
        a {
            color: #388E3C;
            text-decoration: none;
            transition: color 0.2s;
        }
        a:hover {
            color: #2e7031;
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .auth-box {
                padding: 24px 8px 16px 8px;
                max-width: 98vw;
            }
        }
    </style>
</head>

<body>
    <!-- Google Translate element (e.g., in your navbar or header) -->
<div id="google_translate_element" style="float:right; margin: 10px;"></div>

    <div class="auth-box">
        <h2>Welcome to RiceLink</h2>
        <p>Farmer Login</p>

        <form action="login.php" method="POST" class="login-form" id="loginForm">
            <h3 style="color: var(--primary-color, #388E3C); text-align: center; margin-bottom: 20px;">
                Login as Farmer
            </h3>

            <input type="hidden" name="role" value="farmer">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['entered_email']) ?
                                                                    $_SESSION['entered_email'] : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" name="log" class="btn btn-full">Login</button>

            <div class="error-msg">
                <?php echo $errorMessage; ?>
            </div>

            <p style="margin-top: 20px;"><a href="forgotpass.php">Forgot Password?</a></p>
            <p style="margin-top: 10px;">Don't have an account? <a href="register_farmer.php">Sign up</a></p>
        </form>
    </div>

    <?php
    // Login Processing
    if (isset($_POST["log"])) {
        $_SESSION['entered_email'] = trim($_POST["email"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        
        // Query the farmers table
        $stmt = $conn->prepare("SELECT id, email, password FROM farmers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Check if user exists
        if ($row) {
            $storedPassword = $row['password'];

            // Check if password is hashed
            if (password_verify($password, $storedPassword) || $password === $storedPassword) {
                $_SESSION['farmer_id'] = $row['id'];
                $_SESSION['role'] = 'farmer';
                ?>
                <script>
                    window.location.href = "farmer_dashboard.php";
                </script>
                <?php
                exit();
            } else {
                $_SESSION['login_error'] = "Incorrect password.";
                ?>
                <script>
                    window.location.href = "./login.php";
                </script>
                <?php
                exit();
            }
        } else {
            $_SESSION['login_error'] = "User not found.";
            ?>
            <script>
                window.location.href = "./login.php";
            </script>
            <?php
            exit();
        }
    }
    ?>
<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'en',
      includedLanguages: 'en,si,ta',
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
  }
</script>
<script type="text/javascript"
  src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
</script>
</body>
</html>