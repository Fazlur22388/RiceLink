<?php
session_start();
include('../backend/db_connect.php');

if (!isset($_SESSION['farmer_id'])) {
    header("Location: login_farmer.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmer_id = $_SESSION['farmer_id'];
    $title = trim($_POST['product-type']);
    $rice_type = trim($_POST['rice_type']);
    $quantity = floatval($_POST['quantity']);
    $price_per_kg = floatval($_POST['price_per_kg']);
    $harvest_date = $_POST['harvest_date'];
    $location = trim($_POST['farm_address']);
    $description = trim($_POST['description']);
    $is_available = isset($_POST['availability']) ? 'Available' : 'Not Available';
    $image_url = "";
    $uploadDir = __DIR__ . '/../product_pics/';
    $relativePath = 'product_pics/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['product_picture']['name']);
    $uploadPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['product_picture']['tmp_name'], $uploadPath)) {
        $image_url = $relativePath . $fileName;
    } else {
        echo "Image upload failed.";
        exit();
    }

    // ✅ Moved inside POST
    $sql = "INSERT INTO listings (farmer_id, title, description, rice_type, quantity, price_per_kg, harvest_date, location, image_url, is_available) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssddssss", $farmer_id, $title, $description, $rice_type, $quantity, $price_per_kg, $harvest_date, $location, $image_url, $is_available);


    if ($stmt->execute()) {
        header("Location: farmer_dashboard.php?msg=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}


?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add New Product | RiceLnk</title>
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
      width: 360px;
      height: 260px;
      border: 1px solid #696666;
      border-radius: 20px;
      padding: 10px;
      text-align: center;
      position: relative;
    }

    .product-image .hint {
      font-size: 10px;
      color: #777;
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

    .upload-circle:hover {
      transform: scale(1.05);
    }

    .plus {
      font-size: 50px;
      color: rgb(0, 0, 0);
      display: block;
      padding: 70px;
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

    .date label {
      padding: 20px 2px 8px;
      font-size: 12px;
      text-transform: uppercase;
      color: #797373;
      font-family: Arial, Helvetica, sans-serif;
    }
  </style>
</head>

<body>
  <div class="welcome-image">
    <img src="images/6.jpg" alt="welcome-image" />
    <div class="welcome-msg">
      <div class="content">
        <h1>Add New Rice Posts</h1>
        <p>
          Fill in the details below to showcase your rice product to potential
          buyers.
        </p>
      </div>
    </div>
  </div>

  <form action="add_posts.php" method="POST" enctype="multipart/form-data">
    <div class="main-container">
      <div class="btn">
        <button type="submit">Submit</button>
        <button type="button" onclick="window.location.href='farmer_dashboard.php'">Cancel</button>
      </div>

      <p>Basic Rice Information</p>
      <div class="rice-info">
        <div class="box-1">
          <div class="basic-info">
            <div class="display-group">
              <input
                type="text"
                name="product-type"
                placeholder="Product Title" required />
            </div>

            <div class="display-group">
              <div class="rice-variety">
                <label for="rice_type">Rice variety / Type</label>
                <select name="rice_type" id="rice_type" required>
                  <option value="">-- Select Rice Variety --</option>
                  <option value="Keeri Samba">Keeri Samba</option>
                  <option value="Samba">Samba</option>
                  <option value="Nadu">Nadu</option>
                  <option value="White Raw Rice">White Raw Rice</option>
                  <option value="Red Raw Rice">Red Raw Rice</option>
                  <option value="Suwandel">Suwandel</option>
                  <option value="Kalu Heenati">Kalu Heenati</option>
                </select>
              </div>
            </div>

            <div class="display-group">
              <input
                type="number"
                name="quantity"
                placeholder="Quantity Available (kg)" required />
            </div>

            <div class="display-group">
              <input
                type="text"
                name="price_per_kg"
                placeholder="Price-Per-kg" required />
            </div>

            <div class="display-group">
              <div class="harvest-date">
                <label for="harvest_date">Harvest Date</label>
                <input type="date" name="harvest_date" id="harvest_date" required />
              </div>
            </div>
          </div>
        </div>

        <div class="box-1">
          <p class="product">Product Image</p>
          <div class="product-image">
            <div class="product-upload-container">
              <label for="product-upload" class="upload-circle">
                <span class="plus">+</span>
              </label>
              <input
                type="file"
                id="product-upload"
                name="product_picture"
                accept="image/*"
                onchange="previewImage()"
                hidden required />
              <p class="hint">
                Set the product thumbnail image. Only *.png, *.jpg and *.jpeg
                image files are accepted
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="sub-container">
        <!-- origin details -->
        <div class="sec-1">
          <p>Origin Details</p>
          <div class="origin-info">
            <div class="box-1">
              <div class="display-group">
                <input
                  type="text"
                  name="farm_address"
                  placeholder="Location / Farm Address" required />
              </div>

              
            </div>
          </div>
        </div>

        <div class="sec-2">
          <p>Additional Details</p>
          <div class="additional-info">
            <div class="box-1">
              <div class="display-group">
                <div class="rice-disc">
                  <label for="description">Description</label>
                  <textarea id="description" name="description" required></textarea>
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <div class="date">
                  <label for="created_at">Created At</label>
                  <input type="date" id="created_at">
                </div>
              </div>
            </div>
            <div class="box-1">
              <div class="display-group">
                <div class="date">
                  <label for="updated_at">Updated At</label>
                  <input type="date" id="updated_at">
                </div>
              </div>
            </div>
            <div class="box-1">
              <div class="display-group">
                <div class="availability">
                  <label for="availability">Available</label>
                  <input type="checkbox" id="availability" name="availability" value="Available">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  </form>
  </div>

  <script>
    function previewImage() {
      const input = document.getElementById("product-upload");
      const label = document.querySelector(".upload-circle");
      const plusIcon = label.querySelector(".plus");

      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          plusIcon.style.display = "none"; // hide the '+'

          // Remove any existing image
          const existingImg = label.querySelector("img");
          if (existingImg) label.removeChild(existingImg);

          // Create new image preview
          const img = document.createElement("img");
          img.src = e.target.result;
          img.style.width = "100%";
          img.style.height = "100%";
          img.style.borderRadius = "25px";
          img.style.objectFit = "cover";
          img.style.position = "absolute";
          img.style.top = 0;
          img.style.left = 0;

          label.style.position = "relative";
          label.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Cancel button redirection
    document
      .querySelector(".btn button:nth-child(2)")
      .addEventListener("click", function() {
        window.location.href = "farmer_dashboard.php";
      });
  </script>
</body>

</html>