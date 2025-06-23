<?php
// Include the database connection
include('../backend/db_connect.php');

// Initialize variables
$errors = [];
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect and sanitize input
    $name = trim($_POST['name'] ?? '');
    $shop_name = trim($_POST['shop_name'] ?? '');
    $shop_address = trim($_POST['shop_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $buyer_type = $_POST['buyer_type'] ?? '';
    $profile_pic = ""; // Will be set after upload

    // Basic validation
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($shop_name)) $errors[] = "Shop name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($buyer_type)) $errors[] = "Buyer type is required.";

    // File upload handling
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $fileName = time() . '_' . basename($_FILES['profile_pic']['name']);
        $uploadPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadPath)) {
            $profile_pic = $fileName;
        } else {
            $errors[] = "Failed to upload profile picture.";
        }
    }

    // Insert into database if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO buyers (name, shop_name, shop_address, phone, whatsapp, email, password, buyer_type, profile_pic, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssss", $name, $shop_name, $shop_address, $phone, $whatsapp, $email, $hashed_password, $buyer_type, $profile_pic);

        if ($stmt->execute()) {
            $success = "Registration successful!";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!-- HTML form -->
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Login - RiceLink</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Buyer Registration</h2>
    <style>
        body {
            background: #4CAF50;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        h2 {
            text-align: center;
            color: #fff;
            margin-top: 40px;
            margin-bottom: 20px;
        }
        form {
            background: #fff;
            max-width: 400px;
            margin: 30px auto 0 auto;
            padding: 30px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            display: flex;
            flex-direction: column;
            gap: px;
        }
        label {
            font-weight: bold;
            margin-bottom: 4px;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
            background: #f9f9f9;
            transition: border 0.2s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            border: 1.5px solid #4CAF50;
            outline: none;
        }
        input[type="file"] {
            margin-bottom: 10px;
        }
        button[type="submit"] {
            background: #4CAF50;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: #388e3c;
        }
        div[style*="color:red"] {
            background: #ffeaea;
            border: 1px solid #ffb3b3;
            color: #d32f2f !important;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
        div[style*="color:green"] {
            background: #eaffea;
            border: 1px solid #b3ffb3;
            color: #388e3c !important;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
    <?php if (!empty($errors)): ?>
        <div style="color:red;">
            <?= implode('<br>', $errors); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="color:green;">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" enctype="multipart/form-data">
        <label>Name:</label><br>
        <input type="text" name="name"><br><br>

        <label>Shop Name:</label><br>
        <input type="text" name="shop_name"><br><br>

        <label>Shop Address:</label><br>
        <input type="text" name="shop_address"><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone"><br><br>

        <label>WhatsApp:</label><br>
        <input type="text" name="whatsapp"><br><br>

        <label>Email:</label><br>
        <input type="email" name="email"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <label>Buyer Type:</label><br>
        <select name="buyer_type">
            <option value="">-- Select Type --</option>
            <option value="Trader">Trader</option>
            <option value="Mill">Mill</option>
            <option value="Retailer">Retailer</option>
            <option value="Other">Other</option>
        </select><br><br>

        <label>Profile Picture:</label><br>
        <input type="file" name="profile_pic"><br><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>
