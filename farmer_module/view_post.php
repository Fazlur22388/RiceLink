<?php
session_start();
include('../backend/db_connect.php');

if (!isset($_SESSION['farmer_id'])) {
  header("Location: login.php");
  exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid post ID.";
  exit();
}

$post_id = intval($_GET['id']);
$farmer_id = $_SESSION['farmer_id'];

// Fetch post from DB
$sql = "SELECT * FROM listings WHERE id = $post_id AND farmer_id = $farmer_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
  echo "Post not found.";
  exit();
}

$post = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>View Post | RiceLnk</title>
  <link rel="stylesheet" href="add_posts.css" />
  <style>
    body {
      margin: 0;
      padding: 0;
    }

    .welcome-image {
      position: relative;
    }

    .welcome-image img {
      width: 100%;
      height: 500px;
      display: block;
      filter: brightness() sepia(0.6) hue-rotate(40deg);
    }

    .welcome-image .welcome-msg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
    }

    .content {
      color: white;
      margin-top: 200px;
      margin-left: 50px;
      text-align: center;
    }

    .content h1 {
      margin-bottom: 0;
      font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
        "Lucida Sans", Arial, sans-serif;
      font-weight: lighter;
    }

    .content p {
      margin-top: 10px;
      font-family: Arial, Helvetica, sans-serif;
    }

    .main-container {
      margin-top: 80px;
    }

    .main-container p {
      text-transform: uppercase;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 14px;
      color: #3c3d41;
      padding: 20px 40px 2px;
    }

    .rice-info {
      display: flex;
      gap: 60px;
    }

    .basic-info,
    .product-image {
      border: 1px solid rgb(133, 132, 132);
      padding: 10px;
      border-radius: 20px;
    }

    .basic-info {
      width: 750px;
      margin-left: 30px;
      padding: 20px 30px;
    }

    .main-container [type="text"],
    [type="number"],
    [type="date"],
    select {
      border: none;
      width: 100%;
      border: none;
      border-bottom: 1px solid #ccc;
      padding: 20px 0 8px;
      font-size: 12px;
      text-transform: uppercase;
      outline: none;
    }

    .harvest-date {
      display: flex;
      width: 600px;
    }

    .harvest-date label {
      padding: 20px 2px 8px;
      font-size: 12px;
      text-transform: uppercase;
      color: #797373;
      font-family: Arial, Helvetica, sans-serif;
    }

    .rice-variety label {
      padding: 20px 2px 8px;
      font-size: 12px;
      text-transform: uppercase;
      color: #797373;
      font-family: Arial, Helvetica, sans-serif;
    }

    .rice-variety {
      display: flex;
    }

    .product-image {
      width: 300px;
      height: 260px;
      border: 1px solid #696666;
      border-radius: 20px;
      padding: 10px;
      text-align: center;
      position: relative;
    }

    .product {
      margin-top: -50px;
      margin-left: -30px;
    }

    .sub-container {
      display: flex;
      gap: 50px;
    }

    .sec-1,
    .sec-2 {
      width: 50%;
    }

    .sec-2 p {
      margin-left: -30px;
    }

    .origin-info,
    .additional-info {
      border: 1px solid rgb(133, 132, 132);
      padding: 10px;
      border-radius: 20px;
      padding: 30px;
    }

    .origin-info {
      margin-left: 30px;
    }

    .additional-info {
      margin-right: 50px;
      margin-bottom: 20px;
      padding-bottom: 20px;
    }

    .certification {
      display: flex;
      align-items: center;
    }

    .certification input[type="file"] {
      margin-left: 20px;
      padding: 20px 2px 8px;
    }

    .certification label {
      padding: 20px 2px 8px;
      font-size: 12px;
      text-transform: uppercase;
      color: #797373;
      font-family: Arial, Helvetica, sans-serif;
    }

    .rice-disc {
      display: flex;
      flex-direction: column;
    }

    .rice-disc label {
      margin-bottom: 5px;
      text-transform: uppercase;
      font-size: 12px;
      color: #666;
    }

    .rice-disc textarea {
      resize: vertical;
      min-height: 60px;
      padding: 5px;
      border-radius: 25px;
    }

    .additional-info label {
      padding: 20px 2px 8px;
      font-size: 12px;
      text-transform: uppercase;
      color: #797373;
      font-family: Arial, Helvetica, sans-serif;
    }

    .product-upload-container {
      text-align: center;
      padding-top: 10px;
      color: #000000;
    }

    .upload-circle {
      display: inline-block;
      width: 200px;
      height: 200px;
      background-color: #ffffff;
      border-radius: 25px;
      cursor: pointer;
      transition: transform 0.2s;
      border: #9b8e8e 1px solid;
    }

    .btn button {
      margin-top: -40px;
      padding: 8px 14px;
      border-radius: 25px;
      border: 1px solid #1d4d2d;
      background-color: #ffffff;
      color: #1d4d2d;
      cursor: pointer;
      font-size: 14px;
    }

    .btn button:hover {
      background: #d3f1e8;
    }

    .btn {
      display: flex;
      gap: 20px;
      float: right;
      margin-right: 50px;
    }

    .availability {
      margin-top: 12px;
    }

    #product-upload {
      display: none;
    }

    .date{
      display: flex;
      width: 500px;
    }
  </style>
</head>
<body>
  <div class="welcome-image">
    <img src="images/17.jpg" alt="welcome-image" />
    <div class="welcome-msg">
      <div class="content">
        <h1>Post Details</h1>
        <p>Here are the full details of your rice post.</p>
      </div>
    </div>
  </div>

  <div class="main-container">
    <div class="btn" style="margin: 20px 70px auto;">
      <button onclick="window.location.href='farmer_dashboard.php'">Back to Dashboard</button>
    </div>

    <p>Basic Rice Information</p>
    <div class="rice-info">
      <div class="box-1">
        <div class="basic-info">
          <input type="text" value="<?= htmlspecialchars($post['title']) ?>" readonly />
          <div class="rice-variety">
            <label>Rice Variety / Type</label>
            <input type="text" value="<?= htmlspecialchars($post['rice_type']) ?>" readonly />
          </div>
          <input type="text" value="Quantity: <?= $post['quantity'] ?> kg" readonly />
          <input type="text" value="Rs <?= number_format($post['price_per_kg'], 2) ?> per kg" readonly />
          <div class="harvest-date">
            <label>Harvest Date</label>
            <input type="text" value="<?= htmlspecialchars($post['harvest_date']) ?>" readonly />
          </div>
        </div>
      </div>

      <div class="box-1">
        <p class="product">Product Image</p>
        <div class="product-image">
          <img src="<?= htmlspecialchars($post['image_url']) ?>" style="width:100%; height:100%; border-radius:25px; object-fit:cover;">
        </div>
      </div>
    </div>

    <div class="sub-container">
      <div class="sec-1">
        <p>Origin Details</p>
        <div class="origin-info">
          <input type="text" value="<?= htmlspecialchars($post['farm_address']) ?>" readonly />
        </div>
      </div>

      <div class="sec-2">
        <p>Additional Details</p>
        <div class="additional-info">
          <textarea readonly><?= htmlspecialchars($post['description']) ?></textarea>
          <div class="date">
            <label>Created At</label>
            <input type="text" value="<?= htmlspecialchars($post['created_at']) ?>" readonly />
          </div>
          <div class="date">
            <label>Updated At</label>
            <input type="text" value="<?= htmlspecialchars($post['updated_at']) ?>" readonly />
          </div>
          <div class="availability">
            <label>Available</label>
            <input type="checkbox" <?= $post['is_available'] ? 'checked' : '' ?> disabled />
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
