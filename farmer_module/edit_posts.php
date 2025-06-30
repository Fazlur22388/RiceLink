<?php
session_start();
include('../backend/db_connect.php');

if (!isset($_SESSION['farmer_id'])) {
  header("Location: login.php");
  exit();
}

$farmer_id = $_SESSION['farmer_id'];

// Validate post ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: farmer_dashboard.php");
  exit();
}

$post_id = intval($_GET['id']);

// Fetch post
$sql = "SELECT * FROM listings WHERE id = ? AND farmer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
  echo "Post not found or unauthorized access.";
  exit();
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = trim($_POST['title']);
  $description = trim($_POST['description'] ?? '');
  $rice_type = trim($_POST['rice_type']);
  $quantity = (float) $_POST['quantity'];
  $price = (float) $_POST['price_per_kg'];
  $harvest_date = $_POST['harvest_date'];
  $farm_address = trim($_POST['farm_address']);
  $img_path = $post['image_url'];

  if (empty($title) || empty($rice_type) || empty($quantity) || empty($price) || empty($harvest_date) || empty($farm_address)) {
    header("Location: edit_posts.php?id=$post_id&msg=Please fill all required fields");
    exit();
  }

  // Handle image upload
  if (isset($_FILES['product_picture']) && $_FILES['product_picture']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $_FILES['product_picture']['tmp_name']);
    finfo_close($file_info);

    if (!in_array($mime_type, $allowed_types)) {
      header("Location: edit_posts.php?id=$post_id&msg=Only JPEG, PNG and GIF images are allowed");
      exit();
    }

    if (!file_exists('uploads')) {
      mkdir('uploads', 0777, true);
    }

    $file_ext = pathinfo($_FILES['product_picture']['name'], PATHINFO_EXTENSION);
    $img_name = uniqid('img_', true) . '.' . $file_ext;
    $new_img_path = "uploads/" . $img_name;

    if (move_uploaded_file($_FILES['product_picture']['tmp_name'], $new_img_path)) {
      if (file_exists($img_path)) {
        unlink($img_path); // Delete old image
      }
      $img_path = $new_img_path;
    } else {
      header("Location: edit_posts.php?id=$post_id&msg=Image upload failed");
      exit();
    }
  }

  // Update post
  $sql = "UPDATE listings SET 
            title = ?, description = ?, rice_type = ?, quantity = ?, price_per_kg = ?, 
            harvest_date = ?, image_url = ?, farm_address = ?, updated_at = NOW()
          WHERE id = ? AND farmer_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssddsssii", $title, $description, $rice_type, $quantity, $price, $harvest_date, $img_path, $farm_address, $post_id, $farmer_id);

  if ($stmt->execute()) {
    header("Location: farmer_dashboard.php?msg=updated");
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

    .product-upload-container {
      text-align: center;
      padding-top: 10px;
      color: #000000;
      position: relative;
    }

    .upload-circle {
      position: relative;
      display: inline-block;
      width: 200px;
      height: 200px;
      background-color: #ffffff;
      border-radius: 25px;
      border: 1px solid #9b8e8e;
      cursor: pointer;
      overflow: hidden;
      transition: transform 0.2s;
    }

    .upload-circle:hover {
      transform: scale(1.05);
    }

    .plus {
      font-size: 50px;
      color: #000000;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }

    #product-upload {
      width: 100%;
      height: 100%;
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
      border-radius: 25px;
      z-index: 1;
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
    <form action="edit_posts.php?id=<?= $post_id ?>" method="POST" enctype="multipart/form-data">

      <div class="btn">
        <button type="submit">Save Changes</button>
        <button onclick="window.location.href='farmer_dashboard.php'">Cancel</button>
      </div>

      <input type="hidden" name="id" value="<?php echo $post_id; ?>">

      <p>Basic Rice Information</p>
      <div class="rice-info">
        <div class="box-1">
          <div class="basic-info">
            <div class="display-group">
              <input
                type="text"
                name="title"
                placeholder="Product Title"
                value="<?php echo $post['title']; ?>" required />
            </div>

            <div class="display-group">
              <div class="rice-variety">
                <label for="rice_type">Rice variety / Type</label>
                <select name="rice_type" id="rice_type" required>
                  <option value="">-- Select Rice Variety --</option>
                  <?php
                  $types = ['Keeri Samba', 'Samba', 'Nadu', 'White Raw Rice', 'Red Raw Rice', 'Suwandel', 'Kalu Heenati'];
                  foreach ($types as $type) {
                    $selected = ($post['rice_type'] == $type) ? 'selected' : '';
                    echo "<option value=\"$type\" $selected>$type</option>";
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="display-group">
              <input
                type="number"
                name="quantity"
                placeholder="Quantity Available (kg)"
                value="<?php echo $post['quantity']; ?>" required />
            </div>

            <div class="display-group">
              <input
                type="text"
                name="price_per_kg"
                placeholder="Price-Per-Unit"
                value="<?php echo $post['price_per_kg']; ?>" required />
            </div>

            <div class="display-group">
              <div class="harvest-date">
                <label for="harvest_date">Harvest Date</label>
                <input type="date" name="harvest_date" id="harvest_date"
                  value="<?php echo $post['harvest_date']; ?>" required />
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
                <?php if (!empty($post['image_url'])): ?>
                  <img id="preview" src="<?php echo $post['image_url']; ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 25px;">
                <?php endif; ?>
              </label>
              <input
                type="file"
                id="product-upload"
                name="product_picture"
                accept="image/*"
                onchange="previewImage()"
                hidden />
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
                  placeholder="Location / Farm Address"
                  value="<?php echo $post['farm_address']; ?>" required />
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
                <div class="harvest-date">
                  <label for="created_at">Created At</label>
                  <input type="date" id="created_at" value="<?= $post['created_at']; ?>" readonly>
                </div>
              </div>

              <div class="display-group">
                <div class="harvest-date">
                  <label for="updated_at">Updated At</label>
                  <input type="date" id="updated_at" value="<?= $post['updated_at']; ?>" readonly>
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <div class="availability">
                  <label for="is_available">Available</label>
                  <input type="checkbox" id="is_available" <?= $post['is_available'] ? 'checked' : '' ?> disabled>
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
      const input = document.getElementById('product-upload');
      const uploadCircle = document.querySelector('.upload-circle');
      const plusIcon = document.querySelector('.plus');
      const preview = document.getElementById('preview');

      if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
          // Hide the plus icon when image is selected
          plusIcon.style.display = 'none';

          // Create or update preview image
          if (!preview) {
            const img = document.createElement('img');
            img.id = 'preview';
            img.src = e.target.result;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '25px';
            uploadCircle.appendChild(img);
          } else {
            preview.src = e.target.result;
          }
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
</body>

</html>