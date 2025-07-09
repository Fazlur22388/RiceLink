<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid product ID.");
}

$product_id = intval($_GET['id']);

// Fetch existing product details
$stmt = $conn->prepare("SELECT rice_variety, max_price_per_kg, image_path FROM rice_prices WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $riceVariety = htmlspecialchars(trim($_POST["rice_variety"]));
    $price = floatval($_POST["max_price"]);

    // Check if a file was uploaded
    $imagePath = $product['image_path']; // Default to existing image path
    if (isset($_FILES["rice_image"]) && $_FILES["rice_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
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

        // Check file size (max 2MB)
        if ($_FILES["rice_image"]["size"] > 2 * 1024 * 1024) {
            echo "<p style='color:red;'>File size must be less than 2MB.</p>";
            exit;
        }

        if (move_uploaded_file($_FILES["rice_image"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile;

            // Optionally, delete old image file if different and exists
            if ($product['image_path'] && file_exists($product['image_path']) && $product['image_path'] !== $imagePath) {
                unlink($product['image_path']);
            }
        } else {
            echo "<p style='color:red;'>Image upload failed. Please try again.</p>";
            exit;
        }
    }

    // Update the database record
    $stmt = $conn->prepare("UPDATE rice_prices SET rice_variety = ?, max_price_per_kg = ?, image_path = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("sdsi", $riceVariety, $price, $imagePath, $product_id);
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
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Rice Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #f1f8e9, #ffffff);
            padding: 40px;
        }

        form {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2e7d32;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 20px;
            padding: 10px;
            background-color: #43a047;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #388e3c;
        }

        img {
            display: block;
            margin-top: 10px;
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

<h2>Edit Rice Product</h2>

<form method="post" enctype="multipart/form-data">
    <label>Rice Variety</label>
    <input type="text" name="rice_variety" value="<?= htmlspecialchars($product['rice_variety']) ?>" required>

    <label>Max Price per Kg (Rs)</label>
    <input type="number" step="0.01" name="max_price" value="<?= htmlspecialchars($product['max_price_per_kg']) ?>" required>

    <label for="rice_image">Upload Image:</label>
    <input type="file" id="rice_image" name="rice_image" accept="image/*">
    
    <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="Current Rice Image">
    <?php endif; ?>

    <button type="submit">Update Product</button>
</form>

</body>
</html>
