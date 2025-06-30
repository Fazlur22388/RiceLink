<?php
session_start();
include('../backend/db_connect.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid farmer ID.";
  exit();
}

$farmer_id = intval($_GET['id']);

// Get farmer name
$farmer_result = mysqli_query($conn, "SELECT username FROM farmers WHERE id = $farmer_id");
if (!$farmer_result || mysqli_num_rows($farmer_result) === 0) {
  echo "Farmer not found.";
  exit();
}
$farmer = mysqli_fetch_assoc($farmer_result);

// Get all posts from that farmer
$posts_result = mysqli_query($conn, "SELECT * FROM listings WHERE farmer_id = $farmer_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($farmer['username']) ?>'s Posts | RiceLnk</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #f5f5f5;
    }
    h2 {
      margin-bottom: 20px;
      margin-left: 10px;
      font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
        "Lucida Sans", Arial, sans-serif;
        font-weight: lighter;
    }
    .post-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    .post {
      background: white;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .post img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 10px;
    }
    .post h3 {
      margin: 10px 0 5px;
      font-size: 18px;
    }
    .post p {
      margin: 2px 0;
      color: #333;
    }
    .back-btn {
      display: block;
      text-align: right;
      text-decoration: underline;
      margin-top: -30px;
    }
    .back-btn a {
      color: #1d4d2d;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <h2><?= htmlspecialchars($farmer['username']) ?>'s Rice Listings</h2>
  <div class="back-btn">
    <a href="farmer_dashboard.php">Back to Dashboard</a>
  </div>

  <div class="post-grid">
    <?php while ($post = mysqli_fetch_assoc($posts_result)) { ?>
      <div class="post">
        <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Rice Image">
        <h3><?= htmlspecialchars($post['title']) ?></h3>
        <p>Quantity: <?= htmlspecialchars($post['quantity']) ?> kg</p>
        <p>Rs <?= number_format($post['price_per_kg'], 2) ?> per kg</p>
      </div>
    <?php } ?>
  </div>

</body>
</html>
