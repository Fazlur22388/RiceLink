<?php
session_start();
include('../backend/db_connect.php'); // Include your DB connection

if (!isset($_GET['post_id'])) {
    header("Location: farmer_dashboard.php");
    exit();
}

$post_id = $_GET['post_id'];

// Fetch existing post data
$sql = "SELECT * FROM posts WHERE post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "Post not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_type = $_POST['product-type'];
    $farm_type = $_POST['farm-type'];
    $quantity = $_POST['quantity'];
    $price_per_unit = $_POST['price-per-unit'];
    $harvest_date = $_POST['harvest-date'];
    $farm_address = $_POST['farm-address'];
    $moisture_level = $_POST['moisture-level'];
    $grain_size = $_POST['grain-size'];
    $color_desc = $_POST['color-desc'];
    $description = $_POST['description'];

    // Handle image upload if a new one is selected
    $product_picture = $post['product_picture'];
    if (!empty($_FILES['product_picture']['name'])) {
        $image_name = time() . '_' . basename($_FILES['product_picture']['name']);
        $target_dir = "uploads/";
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES["product_picture"]["tmp_name"], $target_file);
        $product_picture = $image_name;
    }

    // Handle certification file upload
    $cert_file = $post['certification'];
    if (!empty($_FILES['Certification']['name'])) {
        $cert_name = time() . '_' . basename($_FILES['Certification']['name']);
        $cert_target = "certs/";
        $cert_file = $cert_target . $cert_name;
        move_uploaded_file($_FILES["Certification"]["tmp_name"], $cert_file);
    }

    $update_sql = "UPDATE posts SET 
        product_type = ?, farm_type = ?, quantity = ?, price_per_unit = ?, harvest_date = ?, 
        product_picture = ?, farm_address = ?, certification = ?, description = ?, 
        moisture_level = ?, grain_size = ?, color_desc = ?
        WHERE post_id = ?";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssidsdssssssi", $product_type, $farm_type, $quantity, $price_per_unit, $harvest_date,
        $product_picture, $farm_address, $cert_file, $description, $moisture_level, $grain_size, $color_desc, $post_id);

    if ($stmt->execute()) {
        header("Location: farmer_dashboard.php?success=updated");
        exit();
    } else {
        echo "Update failed: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Product | RiceLink</title>
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
    <img src="images/19.jpg" alt="welcome-image" />
    <div class="welcome-msg">
      <div class="content">
        <h1>Edit Your Posts here,</h1>
        <p>
          Edit your ric product informaton below.
        </p>
      </div>
    </div>
  </div>

  <div class="main-container">
    <div class="btn">
      <button type="submit" form="editprofileForm" onclick="window.location.href='farmer_dashboard.php'">Save Changes</button>
      <button onclick="window.location.href='farmer_dashboard.php'">Cancel</button>
    </div>
    <form action="edit_post.php" method="POST" enctype="multipart/form-data">
      <p>Basic Rice Information</p>
      <div class="rice-info">
        <div class="box-1">
          <div class="basic-info">
            <div class="display-group">
              <input
                type="text"
                name="product-type"
                placeholder="Product Title"
                value="<?php echo $post['product-type']; ?>" required/>
            </div>

            <div class="display-group">
              <div class="rice-variety">
                <label for="rice-type">Rice variety / Type</label>
                <select name="rice-type" id="rice-type" required>
                  <option value="">-- Select Rice Variety --</option>
                  <option value="BG 300" <?php if($post['rice_type']=='BG 300') echo 'selected'; ?>>BG 300</option>
                  <option value="BG 352" <?php if($post['rice_type']=='BG 352') echo 'selected'; ?>>BG 352</option>
                  <option value="BG 358" <?php if($post['rice_type']=='BG 358') echo 'selected'; ?>>BG 358</option>
                  <option value="BG 366" <?php if($post['rice_type']=='BG 366') echo 'selected'; ?>>BG 366</option>
                  <option value="BG 379-2" <?php if($post['rice_type']=='BG 379-2') echo 'selected'; ?>>BG 379-2</option>
                  <option value="BG 407" <?php if($post['rice_type']=='BG 407') echo 'selected'; ?>>BG 407</option>
                  <option value="BG 450" <?php if($post['rice_type']=='BG 450') echo 'selected'; ?>>BG 450</option>
                  <option value="BG 94-1" <?php if($post['rice_type']=='BG 94-1') echo 'selected'; ?>>BG 94-1</option>
                  <option value="Bg 251" <?php if($post['rice_type']=='Bg 251') echo 'selected'; ?>>Bg 251</option>
                  <option value="Bg 300" <?php if($post['rice_type']=='Bg 300') echo 'selected'; ?>>Bg 300</option>
                  <option value="Bg 403" <?php if($post['rice_type']=='Bg 403') echo 'selected'; ?>>Bg 403</option>
                  <option value="At 307" <?php if($post['rice_type']=='At 307') echo 'selected'; ?>>At 307</option>
                  <option value="At 362" <?php if($post['rice_type']=='At 362') echo 'selected'; ?>>At 362</option>
                  <option value="At 401" <?php if($post['rice_type']=='At 401') echo 'selected'; ?>>At 401</option>
                  <option value="At 353" <?php if($post['rice_type']=='At 353') echo 'selected'; ?>>At 353</option>
                  <option value="Suwandel" <?php if($post['rice_type']=='Suwandel') echo 'selected'; ?>>Suwandel</option>
                  <option value="Pachchaperumal" <?php if($post['rice_type']=='Pachchaperumal') echo 'selected'; ?>>Pachchaperumal</option>
                  <option value="Kaluheenati" <?php if($post['rice_type']=='Kaluheenati') echo 'selected'; ?>>Kaluheenati</option>
                  <option value="Ma-Wee" <?php if($post['rice_type']=='Ma-Wee') echo 'selected'; ?>>Ma-Wee</option>
                  <option value="Kuruluthuda" <?php if($post['rice_type']=='Kuruluthuda') echo 'selected'; ?>>Kuruluthuda</option>
                  <option value="Madathawalu" <?php if($post['rice_type']=='Madathawalu') echo 'selected'; ?>>Madathawalu</option>
                  <option value="Gonabaru" <?php if($post['rice_type']=='Gonabaru') echo 'selected'; ?>>Gonabaru</option>
                  <option value="Rathdel" <?php if($post['rice_type']=='Rathdel') echo 'selected'; ?>>Rathdel</option>
                  <option value="Dahanala" <?php if($post['rice_type']=='Dahanala') echo 'selected'; ?>>Dahanala</option>
                  <option value="Hetada Wee" <?php if($post['rice_type']=='Hetada Wee') echo 'selected'; ?>>Hetada Wee</option>
                  <option value="Kahawanu" <?php if($post['rice_type']=='Kahawanu') echo 'selected'; ?>>Kahawanu</option>
                  <option value="Murungakayan" <?php if($post['rice_type']=='Murungakayan') echo 'selected'; ?>>Murungakayan</option>
                  <option value="Kottiyaran" <?php if($post['rice_type']=='Kottiyaran') echo 'selected'; ?>>Kottiyaran</option>
                  <option value="Godaheenati" <?php if($post['rice_type']=='Godaheenati') echo 'selected'; ?>>Godaheenati</option>
                  <option value="Keeri Samba" <?php if($post['rice_type']=='Keeri Samba') echo 'selected'; ?>>Keeri Samba</option>
                  <option value="Samba" <?php if($post['rice_type']=='Samba') echo 'selected'; ?>>Samba</option>
                  <option value="Nadu" <?php if($post['rice_type']=='Nadu') echo 'selected'; ?>>Nadu</option>
                  <option value="Red Raw Rice" <?php if($post['rice_type']=='Red Raw Rice') echo 'selected'; ?>>Red Raw Rice</option>
                  <option value="White Raw Rice" <?php if($post['rice_type']=='White Raw Rice') echo 'selected'; ?>>White Raw Rice</option>
                </select>
              </div>
            </div>

            <div class="display-group">
              <input
                type="number"
                name="quantity"
                placeholder="Quantity Available (kg)" 
                value="<?php echo $post['quantity']; ?>" required/>
            </div>

            <div class="display-group">
              <input
                type="text"
                name="price-per-unit"
                placeholder="Price-Per-Unit" 
                value="<?php echo $post['price']; ?>" required/>
            </div>

            <div class="display-group">
              <div class="harvest-date">
                <label for="harvest-date">Harvest Date</label>
                <input type="date" name="harvest-date" id="harvest-date" 
                value="<?php echo $post['harvest_date']; ?>" required/>
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
  hidden/>
<!-- Optional preview below upload -->
<?php if (!empty($post['image'])): ?>
  <img src="uploads/<?php echo $post['image']; ?>" width="200">
<?php endif; ?>

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
                  placeholder="Location / Farm Address" 
                  value="<?php echo $post['farm_address']; ?>" required/>
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
                  <textarea id="description" name="description" required><?php echo $post['description']; ?></textarea>
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <div class="moisture-level">
                  <label>Moisture Level</label>
                  <select name="moisture-level" id="moisture-level">
                    <option value="">-- Select moisture-level --</option>
                    <option value="level-1" <?php if($post['moisture_level']=='level-1') echo 'selected'; ?>>Below 12%</option>
                    <option value="level-2" <?php if($post['moisture_level']=='level-2') echo 'selected'; ?>>12% - 14%</option>
                    <option value="level-3" <?php if($post['moisture_level']=='level-3') echo 'selected'; ?>>14% - 16%</option>
                    <option value="level-4" <?php if($post['moisture_level']=='level-4') echo 'selected'; ?>>16% - 18%</option>
                    <option value="level-5" <?php if($post['moisture_level']=='level-5') echo 'selected'; ?>>Above 18%</option>
                  </select>
                </div>
              </div>

              <div class="display-group">
                <div class="grain-size">
                  <label>Grain Size</label>
                  <select name="grain-size" id="grain-size">
                    <option value="">-- Select grain-size --</option>
                    <option value="size-1" <?php if($post['grain_size']=='size-1') echo 'selected'; ?>>Small (<= 5.0mm)</option>
                    <option value="size-2" <?php if($post['grain_size']=='size-2') echo 'selected'; ?>>Medium (5.1 - 6.5mm)</option>
                    <option value="size-3" <?php if($post['grain_size']=='size-3') echo 'selected'; ?>>Long (6.6 - 7.5mm)</option>
                    <option value="size-4" <?php if($post['grain_size']=='size-4') echo 'selected'; ?>>Extra Long (>= 7.6mm)</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <input
                  type="text"
                  name="color-desc"
                  placeholder="Color and Pleasant Description" 
                  value="<?php echo $post['color_desc']; ?>" required/>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script>
        function previewImage() {
            const input = document.getElementById('product-upload');
            const preview = document.getElementById('preview');

            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>