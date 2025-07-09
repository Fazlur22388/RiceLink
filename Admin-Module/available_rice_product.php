<?php
session_start();
include('../backend/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Rice Products</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e0f7fa, #ffffff);
            padding: 20px;
            margin: 0;

            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }

        h2 {
            text-align: center;
            font-size: 2em;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 0 20px;
        }

        .rice-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .rice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .rice-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .rice-card h4 {
            font-size: 1.2em;
            margin: 10px 0 5px;
            color: #34495e;
        }

        .rice-card p {
            font-size: 1em;
            color: #27ae60;
            font-weight: bold;
        }

        .add-link {
            display: inline-block;
            margin: 40px auto 0;
            text-align: center;
            display: block;
            text-decoration: none;
            background-color: #2980b9;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .add-link:hover {
            background-color: #1f618d;
        }

        .no-data {
            text-align: center;
            font-size: 1.2em;
            color: #7f8c8d;
            margin-top: 40px;
        }

        .grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.rice-card {
  background-color: #fff;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 0 8px rgba(0,0,0,0.05);
  text-align: center;
  transition: transform 0.2s;
}

.rice-card:hover {
  transform: translateY(-5px);
}

.rice-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 8px;
}

.rice-card h4 {
  margin: 10px 0 5px;
  font-size: 18px;
  color: #1d4d2d;
}

.rice-card p {
  color: #555;
  font-weight: bold;
}

.no-data {
  text-align: center;
  color: #999;
  font-style: italic;
  margin-top: 20px;
}

    </style>
</head>
<body>

<h2>Available Rice Products</h2>
<div class="grid-container">
<?php
$sql = "SELECT * FROM listings ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
    <div class="rice-card">
        <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Rice Image">
        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
        <p>Rs.<?php echo number_format($row['price_per_kg'], 2); ?>/kg</p>
    </div>
<?php
    endwhile;
else:
    echo "<p class='no-data'>No rice data found.</p>";
endif;

$conn->close();
?>
</div>

<a class="add-link" href="add_rice_product.php">➕ Add New Rice Product</a>

</body>
</html>
