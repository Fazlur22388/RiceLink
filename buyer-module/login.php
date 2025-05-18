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

<body>
    <div class="auth-box">
        <h2>Welcome to RiceLink</h2>
        <p>Buyer Login</p>

        <form action="login.php" method="POST" class="login-form" id="loginForm">
            <h3 style="color: var(--primary-color); text-align: center; margin-bottom: 20px;">
                Login as Buyer
            </h3>

            <input type="hidden" name="role" value="buyer">

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
            <p style="margin-top: 10px;">Don't have an account? <a href="register.php">Sign up</a></p>
        </form>
    </div>

    <?php
    // Login Processing
    if (isset($_POST["log"])) {
        $_SESSION['entered_email'] = trim($_POST["email"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        
        // Query the buyers table
        $stmt = $conn->prepare("SELECT id, email, password FROM buyers WHERE email = ?");
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
                $_SESSION['buyer_id'] = $row['id'];
                $_SESSION['role'] = 'buyer';
                ?>
                <script>
                    window.location.href = "./buyer-dashboard.php";
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
</body>
</html>