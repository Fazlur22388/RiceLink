<?php
session_start();
include('../backend/db_connect.php');

$farmer_id = $_SESSION['farmer_id'] ?? null;
if (!$farmer_id) {
  header('Location: login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Escape all inputs to prevent SQL injection
  $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
  $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $dob = mysqli_real_escape_string($conn, $_POST['dob']);
  $nic = mysqli_real_escape_string($conn, $_POST['nic']);
  $phone = mysqli_real_escape_string($conn, $_POST['phone']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $address = mysqli_real_escape_string($conn, $_POST['address']);
  $district = mysqli_real_escape_string($conn, $_POST['district']);
  $city = mysqli_real_escape_string($conn, $_POST['city']);
  $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
  $farm_type = mysqli_real_escape_string($conn, $_POST['farm-type']);
  $experience = mysqli_real_escape_string($conn, $_POST['experience']);
  $land_size = mysqli_real_escape_string($conn, $_POST['land_size']);
  $membership = isset($_POST['membership']) ? 1 : 0;

  // Handle profile picture upload
  $image_sql = '';
  if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $image_name = time() . '_' . basename($_FILES['profile_image']['name']);
    $target_path = 'uploads/' . $image_name;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
      // Save only filename or relative path
      $image_sql = ", profile_picture = '$image_name'"; // or '$target_path' if you save full path
      $_SESSION['user_image'] = $image_name; // or $target_path
    }
  }

  // Then include $image_sql in your update query:
  $update = "UPDATE farmers SET name = '$username', email = '$email' $image_sql WHERE id = $farmer_id";


  // Handle license file upload (optional)
  $license_sql = '';
  if (!empty($_FILES['license']['name'])) {
    $license_name = time() . '_' . basename($_FILES['license']['name']);
    $license_path = 'uploads/' . $license_name;
    if (move_uploaded_file($_FILES['license']['tmp_name'], $license_path)) {
      $license_sql = ", license = '$license_path'";
    }
  }

  // Final SQL query
  $sql = "UPDATE farmers SET 
    first_name = '$first_name',
    last_name = '$last_name',
    username = '$username',
    dob = '$dob',
    nic = '$nic',
    phone = '$phone',
    email = '$email',
    address = '$address',
    district = '$district',
    city = '$city',
    postal_code = '$postal_code',
    farm_type = '$farm_type',
    experience = '$experience',
    land_size = '$land_size',
    membership = '$membership'
    $image_sql
    $license_sql
    WHERE id = $farmer_id";

  // Execute the query
  if (mysqli_query($conn, $sql)) {
    echo "<script>
      alert('Profile updated successfully!');
      window.location.href = 'farmer_profile.php';
    </script>";
    exit;
  } else {
    echo "<script>
      alert('Update failed: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');
      window.history.back();
    </script>";
    exit;
  }
}

// Fetch updated data (if not submitted yet)
$result = mysqli_query($conn, "SELECT * FROM farmers WHERE id = $farmer_id");
$farmer = mysqli_fetch_assoc($result);
$_SESSION['user_image'] = $farmer['profile_picture'] ?? '';
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit User Profile | RiceLink</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      height: 100vh;
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

    /* profile pic */
    .profile-header {
      background: white;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 20px;
      border-bottom: 1px solid #b4b3b3;
      position: relative;
    }

    .profile-picture-container {
      position: relative;
      width: 130px;
      height: 130px;
      transform: translateY(-70px);
      margin-left: 70px;
    }

    .profile-picture-label img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #ffffff;
    }

    .profile-picture-label input[type="file"] {
      display: none;
    }

    .edit-icon {
      position: absolute;
      bottom: 0;
      right: 0;
      border-radius: 50%;
    }

    .profile-info {
      flex-grow: 1;
    }

    .profile-info h2 {
      margin-top: -70px;
      font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
        "Lucida Sans", Arial, sans-serif;
      font-weight: lighter;
    }

    .profile-info p {
      color: #5e6570;
      line-height: 20px;
      font-size: 12px;
      font-family: Arial, Helvetica, sans-serif;
      margin-top: -50;
    }

    .profile-actions button {
      padding: 8px 14px;
      border-radius: 25px;
      border: 1px solid #1d4d2d;
      background-color: #ffffff;
      color: #1d4d2d;
      cursor: pointer;
      font-size: 14px;
      margin-right: 10px;
    }

    .profile-actions button:hover {
      background: #d3f1e8;
    }

    .edit-icon {
      position: absolute;
      bottom: 0;
      right: 0;
      border-radius: 50%;
      padding: 5px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      cursor: pointer;
      height: 30px;
      width: 30px;
    }

    .edit-icon input[type="file"] {
      display: none;
    }

    input,
    select,
    label,
    p {
      font-family: Arial, Helvetica, sans-serif;
    }

    input {
      width: 100%;
      border: none;
      border-bottom: 1px solid #ccc;
      padding: 20px 0 8px;
      font-size: 12px;
      text-transform: uppercase;
      outline: none;
    }

    .container {
      width: 80%;
    }

    .main-container {
      display: flex;
      flex-direction: column;
      margin-left: 100px;
    }

    .main-container p {
      font-size: 14px;
      text-transform: uppercase;
      color: #3c3d41;
    }

    .box {
      display: flex;
      gap: 32px;
      padding: 12px 100px 12px 0;
    }

    .dob {
      display: flex;
      width: 600px;
    }

    .dob label,
    .farm-type label,
    .license label,
    .membership-label {
      padding: 20px 5px 8px;
      font-size: 12px;
      text-transform: uppercase;
      color: #797373;
    }

    .user-info,
    .contact-info,
    .profetional-info {
      background-color: white;
      border: 1px solid rgb(197, 195, 195);
      border-radius: 20px;
      padding-top: 50px;
      padding-left: 90px;
      padding-bottom: 30px;
      margin-top: 30px;
      margin-bottom: 20px;
      margin-left: 170px;
    }

    .farm-type label {
      margin-left: -5px;
    }

    .farm-type select {
      padding: 10px 5px;
      font-size: 14px;
      border: none;
      border-bottom: 1px solid #ccc;
      outline: none;
      background-color: transparent;
      transition: border-color 0.3s;
    }

    .farm-type select:focus {
      border-bottom: 1px solid #1d4d2d;
    }

    .membership {
      display: flex;
      align-items: center;
    }

    .membership input[type="checkbox"] {
      width: 15px;
      height: 15px;
      margin-right: 10px;
    }

    .membership label {
      font-size: 13px;
      line-height: 1.4;
      color: #797373;
      text-transform: uppercase;
    }

    .license {
      display: flex;
      align-items: center;
    }

    .license input[type="file"] {
      margin-left: 20px;
    }

    .license label {
      margin-left: -5px;
    }
  </style>
</head>

<body>
  <div class="welcome-image">
    <img src="images/15.jpg" alt="" />
    <div class="welcome-msg">
      <div class="content">
        <h1>Edit Your Profile Here,</h1>
        <p>Update your details to keep your profile fresh and up-to-date.</p>
      </div>
    </div>
  </div>
  

  <!-- main -->
  <form id="editprofileForm" action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">

    <!-- profile pic -->
    <div class="profile-header">
      <div class="profile-picture-container">
        <label for="profileInput" class="profile-picture-label">
          
          <img src="<?= !empty($farmer['profile_picture']) ? htmlspecialchars($farmer['profile_picture']) : 'uploads/default.png' ?>" />

          <div class="edit-icon">
            <img src="images/camera.png" alt="edit-icon" />
          </div>

          <input type="file" id="profileInput" name="profile_image" accept="image/*" onchange="previewImage(event)" />
        </label>
      </div>

      <div class="profile-info">
        <h2>
          <?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']) ?>
        </h2>
        <p><?= htmlspecialchars($farmer['email']) ?></p>
        <p><?= htmlspecialchars($farmer['address']) ?></p>
      </div>
      <div class="profile-actions">
        <button type="submit" form="editprofileForm">Save Changes</button>
        <button onclick="window.location.href='farmer_dashboard.php'">Back to Dashboard</button>
        <button onclick="window.location.href='logout.php'">Logout</button>
      </div>
    </div>

    <div class="main-container">
      <div class="container">

        <div class="user-info">
          <p>User Information</p>

          <div class="box">
            <input type="text" name="username" value="<?= htmlspecialchars($farmer['username']) ?>" required />
          </div>

          <div class="box">
            <input type="text" name="first_name" value="<?= htmlspecialchars($farmer['first_name']) ?>" required />
            <input type="text" name="last_name" value="<?= htmlspecialchars($farmer['last_name']) ?>" required />
          </div>

          <div class="box">
            <div class="dob">
              <label for="dob">DOB / Age</label>
              <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($farmer['dob']) ?>" required />
            </div>
            <input
              type="text"
              name="nic"
              placeholder="National ID Number / NIC"
              value="<?= htmlspecialchars($farmer['nic']) ?>" required />
          </div>

          <div class="box">
            <input
              type="tel"
              name="phone"
              placeholder="Phone Number"
              value="<?= htmlspecialchars($farmer['phone']) ?>" required />
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($farmer['email']) ?>" required />
          </div>
        </div>

        <div class="contact-info">
          <p id="contact">Contact Information</p>

          <div class="box">
            <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($farmer['address']) ?>" required />
          </div>

          <div class="box">
            <input
              type="text"
              name="district"
              placeholder="District / Provice"
              value="<?= htmlspecialchars($farmer['district']) ?>" required />
            <input type="text" name="city" placeholder="City / Town" value="<?= htmlspecialchars($farmer['city']) ?>" required />
            <input
              type="number"
              name="postal_code"
              placeholder="Postal Code"
              value="<?= htmlspecialchars($farmer['postal_code']) ?>" required />
          </div>
        </div>

        <!-- profetional info -->
        <div class="profetional-info">
          <p id="profetional">Profetional Information</p>

          <div class="box">
            <div class="farm-type">
              <label for="farm-type">Type of Farming</label>
              <select name="farm-type" id="farm-type">
                <option value="rice" <?= $farmer['farm_type'] == 'rice' ? 'selected' : '' ?>>Paddy Farmers</option>
                <option value="vegetable" <?= $farmer['farm_type'] == 'vegetable' ? 'selected' : '' ?>>Vegetable Growers</option>
                <option value="tea" <?= $farmer['farm_type'] == 'tea' ? 'selected' : '' ?>>Tea Planters</option>
                <option value="fruit" <?= $farmer['farm_type'] == 'fruit' ? 'selected' : '' ?>>Fruit Growers</option>
              </select>
            </div>
          </div>

          <div class="box">
            <input
              type="number"
              name="experience"
              placeholder="Years of Experience"
              value="<?= htmlspecialchars($farmer['experience']) ?>" required />
          </div>

          <div class="box">
            <input type="number" name="land_size" placeholder="Land Size" value="<?= htmlspecialchars($farmer['land_size']) ?>" />
          </div>

          <div class="box">
            <div class="display-group">
              <div class="membership">
                <input type="checkbox" id="membership" name="membership" <?= $farmer['membership'] ? 'checked' : '' ?> />
                <label for="membership">Membership in Agricultural Associations</label>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="license">
              <label for="license">Certifications / Licenses</label>
              <?php if (!empty($farmer['license'])): ?>
                <p style="font-size: 13px;">Current License: <a href="<?= $farmer['license'] ?>" target="_blank">View File</a></p>
              <?php endif; ?>
              <input type="file" id="license" name="license" />
            </div>
          </div>
        </div>
      </div>
  </form>
  </div>

  <script>
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        const output = document.getElementById("profilePicture");
        output.src = reader.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>

</body>

</html>