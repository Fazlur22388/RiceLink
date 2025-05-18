<?php
session_start();
include('db_connect.php');
error_reporting(0);

// Capture previous values if login fails
$selectedRole = isset($_SESSION['role']) ? $_SESSION['role'] : "";
$errorMessage = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : "";
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RiceLink</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="auth-box">
        <h2>Welcome to RiceLink</h2>
        <p>Please select your user type to continue</p>

        <div class="role-selection">
            <button class="btn btn-outline" onclick="showLogin('farmer')">Farmer</button>
            <button class="btn btn-outline" onclick="showLogin('buyer')">Buyer</button>
        </div>

        <form action="login.php" method="POST" class="login-form" id="loginForm" style="display: <?php echo ($selectedRole ? 'block' : 'none'); ?>;">
            <h3 id="roleTitle" style="color: var(--primary-color); text-align: center; margin-bottom: 20px;">
                <?php echo $selectedRole ? "Login as " . ucfirst($selectedRole) : ""; ?>
            </h3>

            <input type="hidden" id="role" name="role" value="<?php echo $selectedRole; ?>">

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

    <script>
        function showLogin(role) {
            document.getElementById("loginForm").style.display = "block";
            document.getElementById("roleTitle").innerText = "Login as " + role.charAt(0).toUpperCase() + role.slice(1);
            document.getElementById("role").value = role;
        }

        window.onload = function() {
            <?php if (!empty($errorMessage)): ?>
                document.getElementById("loginForm").style.display = "block";
            <?php endif; ?>
        };
    </script>
    <?php

    // Login Processing
    if (isset($_POST["log"])) {
        $_SESSION['entered_email'] = trim($_POST["email"]);
        $_SESSION['role'] = trim($_POST["role"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $role = trim($_POST["role"]);

        // Role Mapping
        $roleTables = [
            "farmer" => ["table" => "farmers", "id_col" => "id", "email_col" => "email", "password_col" => "password"],
            "buyer" => ["table" => "buyers", "id_col" => "id", "email_col" => "email", "password_col" => "password"]
        ];

        if (array_key_exists($role, $roleTables)) {
            $table = $roleTables[$role]['table'];
            $id_col = $roleTables[$role]['id_col'];
            $email_col = $roleTables[$role]['email_col'];
            $password_col = $roleTables[$role]['password_col'];

            // Prepare SQL Query
            $stmt = $conn->prepare("SELECT $id_col, $email_col, $password_col FROM $table WHERE $email_col = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            // Check if user exists
            if ($row) {
                $storedPassword = $row[$password_col];

                // Check if password is hashed
                if (password_verify($password, $storedPassword) || $password === $storedPassword) {
                    // Set session variables based on role
                    if ($role == 'farmer') {
                        $_SESSION['farmer_id'] = $row[$id_col];
                        $_SESSION['role'] = 'farmer';
    ?>
                        <script>
                            window.location.href = "./farmer-dashboard.php";
                        </script>
                    <?php
                        exit();
                    } else if ($role == 'buyer') {
                        $_SESSION['buyer_id'] = $row[$id_col];
                        $_SESSION['role'] = 'buyer';

                    ?>
                        <script>
                            window.location.href = "./buyer-dashboard.php";
                        </script>
                    <?php
                        exit();
                    }
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
        } else {
            $_SESSION['login_error'] = "Invalid role selected.";
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