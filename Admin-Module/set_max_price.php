<?php
session_start();
include('db_connect.php'); // Ensure this file connects to MySQL using $conn

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["rice_image"])) {
    $riceVariety = $_POST["rice_variety"];
    $price = $_POST["max_price"];

    $targetDir = "uploads/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $filename = basename($_FILES["rice_image"]["name"]);
    $uniqueName = time() . "_" . $filename;
    $targetFile = $targetDir . $uniqueName;

    if (move_uploaded_file($_FILES["rice_image"]["tmp_name"], $targetFile)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO rice_prices (rice_variety, max_price_per_kg, image_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $riceVariety, $price, $targetFile);
        $stmt->execute();
        $stmt->close();
        header("Location: set_max_price.php");
        exit;
    } else {
        echo "<p style='color:red;'>Image upload failed.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Max Price - Rice Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f3f3;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        form {
            background: #fff;
            padding: 20px;
            margin-bottom: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
        }

        input[type="text"], input[type="number"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: #fff;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .rice-card {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }

        .rice-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .rice-card h4 {
            margin: 10px 0 5px;
        }
    </style>
</head>
<body>

<h2>Add Rice Product</h2>
<form action="" method="post" enctype="multipart/form-data">
    <label>Rice Variety:</label>
    <input type="text" name="rice_variety" required>

    <label>Max Price per KG:</label>
    <input type="number" step="0.01" name="max_price" required>

    <label>Upload Image:</label>
    <input type="file" name="rice_image" accept="image/*" required>

    <input type="submit" value="Add Rice">
</form>

<h2>Available Rice Products</h2>
<div class="grid-container">
<?php
$sql = "SELECT * FROM rice_prices ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
    <div class="rice-card">
        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Rice Image">
        <h4><?php echo htmlspecialchars($row['rice_variety']); ?></h4>
        <p><strong>₹<?php echo number_format($row['max_price_per_kg'], 2); ?>/kg</strong></p>
    </div>
<?php
    endwhile;
else:
    echo "<p>No rice data found.</p>";
endif;

$conn->close();
?>
</div>

</body>
</html>
