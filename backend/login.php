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
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color:rgb(47, 114, 34);
        }

        .login-box {
            background-color:rgb(67, 138, 39);
            padding: 30px;
            width: 450px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .login-box h2 {
            color: white;
            margin-bottom: 15px;
        }

        .role-selection {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .role-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #1A252F;
            color: white;
            font-size: 16px;
        }

        .role-btn:hover {
            background-color: #273746;
        }

        .login-form {
            display: none;
            text-align: left;
        }

        .login-form label {
            display: block;
            color: #ECF0F1;
            margin-top: 10px;
        }

        .login-form input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: none;
            border-radius: 5px;
            background: #ECF0F1;
        }

        .login-form button {
            width: 100%;
            padding: 10px;
            background-color: #1A252F;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
            font-size: 16px;
        }

        .login-form button:hover {
            background-color: #273746;
        }

        .error-msg {
            color: #e74c3c;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>Select Your Portal</h2>

        <div class="role-selection">
            <button class="role-btn" onclick="showLogin('farmer')">Farmer</button>
            <button class="role-btn" onclick="showLogin('buyer')">Buyer</button>
        </div>

        <form action="login.php" method="POST" class="login-form" id="loginForm" style="display: <?php echo ($selectedRole ? 'block' : 'none'); ?>;">
            <h3 id="roleTitle" style="color: white; text-align: center;">
                <?php echo $selectedRole ? "Login as " . $selectedRole : ""; ?>
            </h3>

            <input type="hidden" id="role" name="role" value="<?php echo $selectedRole; ?>">

            <label for="email">Email:</label>
            <input type="text" id="email" name="email" value="<?php echo isset($_SESSION['entered_email']) ? 
            $_SESSION['entered_email'] : ''; ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" name="log">Login</button>

            <div class="error-msg">
                <?php echo $errorMessage; ?>
            </div>

            <p style="margin-top: 10px;"><a href="forgotpass.php">Forgot Password?</a></p>
        </form>
    </div>

    <script>
        function showLogin(role) {
            document.getElementById("loginForm").style.display = "block";
            document.getElementById("roleTitle").innerText = "Login as " + role;
            document.getElementById("role").value = role; // Set role input correctly
        }

        window.onload = function() {
            <?php if ( !empty($errorMessage)): ?>
                document.getElementById("loginForm").style.display = "block";
            <?php endif; ?>
        };
    </script>
    <?php 

include('dbconnect.php');

// Login Processing
if (isset($_POST["log"])) {
    
    $_SESSION['entered_email'] = trim($_POST["email"]); // Store entered email
    $_SESSION['role'] = trim($_POST["role"]); // Store selected role
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);

    // Role Mapping
    $roleTables = [
        
        "farmer"   => ["table" => "farmers", "id_col" => "id", "email_col" => "email", "password_col" => "password", "redirect" => "staff.php"],
        "buyer" => ["table" => "buyers", "id_col" => "id", "email_col" => "email", "password_col" => "password", "redirect" => "../index.php"]
    ];

    if (array_key_exists($role, $roleTables)) {
        $table = $roleTables[$role]['table'];
        $id_col = $roleTables[$role]['id_col'];
        $email_col = $roleTables[$role]['email_col'];
        $password_col = $roleTables[$role]['password_col'];
        $redirect_page = $roleTables[$role]['redirect'];

        // Debugging SQL Query
        

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
                $_SESSION['id'] = $row[$id_col]; // Store correct user ID
                $_SESSION['email'] = $email;
                $_SESSION['user_type'] = $role;

                // Redirect to user-specific dashboard
                header("Location: $redirect_page");
                exit();
            } 
            else {
                $_SESSION['login_error'] = "Incorrect password.";
            }
        } else {
            $_SESSION['login_error'] = "User not found.";
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password";
    }
}

?>


</body>
</html>