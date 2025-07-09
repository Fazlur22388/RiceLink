<?php
session_start();
include('db_connect.php');
error_reporting(0);

// Define enteredEmail for the form field
$enteredEmail = $_SESSION['entered_email'] ?? '';

// Login logic
if (isset($_POST['log'])) {
    $_SESSION['entered_email'] = trim($_POST['email']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Query the admins table
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if ($admin) {
        if ($password === $admin['password']) {  // You can switch to password_verify() if using hashes
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['role'] = 'admin';
            unset($_SESSION['login_error']); // clear old errors
            echo "<script>window.location.href = 'admin-dashboard.php';</script>";
            exit();
        } else {
            $_SESSION['login_error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['login_error'] = "Admin account not found.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - RiceLink</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('images/rice.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .main-header {
            position: absolute;
            top: 20px;
            width: 100%;
            text-align: center;
        }

        .main-header h2 {
            font-size: 36px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            margin: 0;
            padding: 10px 0;
        }

        .auth-box {
            background-color: rgba(195, 250, 108, 0.32);
            padding: 40px;
            width: 320px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            text-align: center;
        }

        .login-heading {
            font-size: 30px;
            text-align: center;
            color: white;
        }

        .auth-box h1 {
            font-size: 25px;
            margin-bottom: 20px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
            color: #000;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: white;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background-color: rgb(90, 203, 3);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #219150;
        }

        .error-msg {
            color: red;
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <h2>Welcome to RiceLink</h2>
    </header>

    <div class="auth-box">
        <h1 class="login-heading">Admin Login</h1>
        <form action="login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required autofocus
       value="<?php echo htmlspecialchars($enteredEmail); ?>">

            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" name="log" class="btn">Login</button>

            <?php if (!empty($_SESSION['login_error'])): ?>
                <div class="error-msg"><?php echo htmlspecialchars($_SESSION['login_error']); ?></div>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>

        </form>
    </div>
</body>
</html>
