<?php
session_start();
include('db_connect.php');
error_reporting(0);


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
        if ($password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['role'] = 'admin';
            echo "<script>window.location.href = 'admin-dashboard.php';</script>";
            exit();
        } else {
            $_SESSION['login_error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['login_error'] = "Admin account not found.";
    }

    echo "<script>window.location.href = 'login.php';</script>";
    exit();
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
        background: linear-gradient(135deg, #e8f5e9, #ffffff); /* greenish white gradient */
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
        color: #2e7d32; /* deep green */
        margin: 0;
        padding: 10px 0;
    }

    .auth-box {
        background-color: #ffffff;
        padding: 40px;
        width: 320px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .login-heading {
        font-size: 30px;
        color: #388e3c; /* medium green */
        margin-bottom: 20px;
    }

    .auth-box h1 {
        font-size: 25px;
        margin-bottom: 20px;
        color: #388e3c;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
        color: #2e7d32;
    }

    label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #2e7d32;
    }

    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #c8e6c9;
        border-radius: 5px;
        background-color: #f1f8e9;
    }

    .btn {
        width: 100%;
        padding: 10px;
        background-color: #4caf50; /* green */
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn:hover {
        background-color: #388e3c;
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

            <?php if (!empty($errorMessage)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
