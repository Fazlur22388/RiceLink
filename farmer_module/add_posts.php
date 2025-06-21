<?php
include('../backend/db_connect.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = $_POST['product-type'];
  $variety = $_POST['farm-type'];
  $quantity = $_POST['quantity'];
  $price = $_POST['price-per-unit'];
  $harvest_date = $_POST['harvest-date'];
  $address = $_POST['farm-address'];
  $description = $_POST['description'] ?? '';
  $moisture = $_POST['moisture-level'];
  $grain_size = $_POST['grain-size'];
  $color_desc = $_POST['color-desc'];

  // Handle Product Image
  $product_img_name = $_FILES['product_picture']['name'];
  $product_img_tmp = $_FILES['product_picture']['tmp_name'];
  $product_img_path = "uploads/" . basename($product_img_name);
  move_uploaded_file($product_img_tmp, $product_img_path);

  // Handle Certification File
  $cert_file_name = $_FILES['Certification']['name'];
  $cert_file_tmp = $_FILES['Certification']['tmp_name'];
  $cert_file_path = "uploads/" . basename($cert_file_name);
  move_uploaded_file($cert_file_tmp, $cert_file_path);

  $sql = "INSERT INTO rice_posts 
        (title, variety, quantity, price, harvest_date, image, address, certification, description, moisture_level, grain_size, color_desc)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssdssssssss", $title, $variety, $quantity, $price, $harvest_date, $product_img_path, $address, $cert_file_path, $description, $moisture, $grain_size, $color_desc);

  if ($stmt->execute()) {
    header("Location: farmer_dashboard.php?msg=success");
    exit();
  } else {
    echo "Error: " . $conn->error;
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
      width: 500px;
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

    .moisture-level {
      display: flex;
      gap: 3px;
    }

    .grain-size {
      display: flex;
      gap: 30px;
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

  <div class="main-container">
    <div class="btn">
      <button type="submit" onclick="window.location.href='farmer_dashboard.php'">Submit</button>
      <button onclick="window.location.href='farmer_dashboard.php'">Cancel</button>
    </div>
    <form action="add_post.php" method="POST" enctype="multipart/form-data">
      <p>Basic Rice Information</p>
      <div class="rice-info">
        <div class="box-1">
          <div class="basic-info">
            <div class="display-group">
              <input
                type="text"
                name="product-type"
                placeholder="Product Title" required/>
            </div>

            <div class="display-group">
              <div class="rice-variety">
                <label for="rice-type">Rice variety / Type</label>
                <select name="rice-type" id="rice-type" required>
                  <option value="">-- Select Rice Variety --</option>
                  <option value="BG 300">BG 300</option>
                  <option value="BG 352">BG 352</option>
                  <option value="BG 358">BG 358</option>
                  <option value="BG 366">BG 366</option>
                  <option value="BG 379-2">BG 379-2</option>
                  <option value="BG 407">BG 407</option>
                  <option value="BG 450">BG 450</option>
                  <option value="BG 94-1">BG 94-1</option>
                  <option value="Bg 251">Bg 251</option>
                  <option value="Bg 300">Bg 300</option>
                  <option value="Bg 403">Bg 403</option>
                  <option value="At 307">At 307</option>
                  <option value="At 362">At 362</option>
                  <option value="At 401">At 401</option>
                  <option value="At 353">At 353</option>
                  <option value="Suwandel">Suwandel</option>
                  <option value="Pachchaperumal">Pachchaperumal</option>
                  <option value="Kaluheenati">Kaluheenati</option>
                  <option value="Ma-Wee">Ma-Wee</option>
                  <option value="Kuruluthuda">Kuruluthuda</option>
                  <option value="Madathawalu">Madathawalu</option>
                  <option value="Gonabaru">Gonabaru</option>
                  <option value="Rathdel">Rathdel</option>
                  <option value="Dahanala">Dahanala</option>
                  <option value="Hetada Wee">Hetada Wee</option>
                  <option value="Kahawanu">Kahawanu</option>
                  <option value="Murungakayan">Murungakayan</option>
                  <option value="Kottiyaran">Kottiyaran</option>
                  <option value="Godaheenati">Godaheenati</option>
                  <option value="Keeri Samba">Keeri Samba</option>
                  <option value="Samba">Samba</option>
                  <option value="Nadu">Nadu</option>
                  <option value="Red Raw Rice">Red Raw Rice</option>
                  <option value="White Raw Rice">White Raw Rice</option>
                </select>
              </div>
            </div>

            <div class="display-group">
              <input
                type="number"
                name="quantity"
                placeholder="Quantity Available (kg)" required/>
            </div>

            <div class="display-group">
              <input
                type="text"
                name="price-per-unit"
                placeholder="Price-Per-Unit" required/>
            </div>

            <div class="display-group">
              <div class="harvest-date">
                <label for="harvest-date">Harvest Date</label>
                <input type="date" name="harvest-date" id="harvest-date" required/>
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
                hidden required/>
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
                  name="farm-address"
                  placeholder="Location / Farm Address" required/>
              </div>

              <div class="display-group">
                <div class="certification">
                  <label>Organic Certification</label>
                  <input type="file" name="Certification" />
                </div>
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
                  <textarea id="description" required></textarea>
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <div class="moisture-level">
                  <label>Moisture Level</label>
                  <select name="moisture-level" id="moisture-level">
                    <option value="">-- Select moisture-level --</option>
                    <option value="level-1">Below 12%</option>
                    <option value="level-2">12% - 14%</option>
                    <option value="level-3">14% - 16%</option>
                    <option value="level-4">16% - 18%</option>
                    <option value="level-5">Above 18%</option>
                  </select>
                </div>
              </div>

              <div class="display-group">
                <div class="grain-size">
                  <label>Grain Size</label>
                  <select name="grain-size" id="grain-size">
                    <option value="">-- Select grain-size --</option>
                    <option value="size-1">Small (<= 5.0mm)</option>
                    <option value="size-2">Medium (5.1 - 6.5mm)</option>
                    <option value="size-3">Long (6.6 - 7.5mm)</option>
                    <option value="size-4">Extra Long (>= 7.6mm)</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <input
                  type="text"
                  name="color-desc"
                  placeholder="Color and Pleasant Description" required/>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script>
    function previewImage() {
      const fileInput = document.getElementById("product-upload");
      const reader = new FileReader();

      reader.onload = function(e) {
        const imgPreview = document.createElement("img");
        imgPreview.src = e.target.result;
        imgPreview.style.width = "200px";
        imgPreview.style.height = "200px";
        imgPreview.style.objectFit = "cover";
        imgPreview.style.borderRadius = "25px";

        const container = document.querySelector(".product-upload-container");
        const oldImage = container.querySelector("img");
        if (oldImage) container.removeChild(oldImage);
        container.appendChild(imgPreview);
      };

      if (fileInput.files[0]) {
        reader.readAsDataURL(fileInput.files[0]);
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