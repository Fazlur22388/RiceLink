<?php
session_start();
include('db_connect.php');

// Access control
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["rice_image"])) {
    // Sanitize and validate inputs
    $riceVariety = htmlspecialchars(trim($_POST["rice_variety"]));
    $price = floatval($_POST["max_price"]);

    // Handle file upload
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $originalFilename = basename($_FILES["rice_image"]["name"]);
    $fileExtension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
    $uniqueName = uniqid("rice_", true) . "." . $fileExtension;
    $targetFile = $targetDir . $uniqueName;

    // Check MIME type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES["rice_image"]["tmp_name"]);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        echo "<p style='color:red;'>Invalid image format. Allowed types: JPG, PNG, GIF, WebP.</p>";
        exit;
    }

    // Check file size (optional, max 2MB)
    if ($_FILES["rice_image"]["size"] > 2 * 1024 * 1024) {
        echo "<p style='color:red;'>File size must be less than 2MB.</p>";
        exit;
    }

    // Upload file and insert into DB
    if (move_uploaded_file($_FILES["rice_image"]["tmp_name"], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO rice_prices (rice_variety, max_price_per_kg, image_path) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sds", $riceVariety, $price, $targetFile);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: available_rice_product.php");
                exit;
            } else {
                echo "<p style='color:red;'>Database error: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color:red;'>Database prepare failed: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Image upload failed. Please try again.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Rice Product</title>
    <style>
       <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Rice Product</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('images/rice.jpg'); /* Adjust path if needed */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            padding: 20px;
        }

        form {
            background: rgba(255, 255, 255, 0.28);
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 400px;
            width: 100%;
            transition: box-shadow 0.3s ease;
        }

        form:hover {
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 2rem;
            text-align: center;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="file"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.3);
        }

        input[type="submit"] {
            background-color:rgb(46, 249, 6);
            color: black;
            border: none;
            padding: 14px 0;
            width: 100%;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0 6px 15px rgba(39, 174, 96, 0.4);
        }

        input[type="submit"]:hover {
            background-color:rgb(213, 245, 2);
        }

        p {
            margin-top: 20px;
            text-align: center;
            font-weight: 500;
        }

        a {
            color:rgb(0, 4, 7);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #1c5980;
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            form {
                padding: 20px;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <form action="" method="post" enctype="multipart/form-data">
        <h2>Add Rice Product</h2>
        
        <label for="rice_variety">Rice Variety:</label>
        <input type="text" id="rice_variety" name="rice_variety" required>

        <label for="max_price">Max Price per KG:</label>
        <input type="number" step="0.01" id="max_price" name="max_price" required>

        <label for="rice_image">Upload Image:</label>
        <input type="file" id="rice_image" name="rice_image" accept="image/*" required>

        <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />

        <input type="submit" value="Add Rice">

        <p><a href="available_rice_product.php">View Available Rice Products</a></p>
    </form>

</body>
</html>
