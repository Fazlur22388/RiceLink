<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['farmer_id'])) {
  header("Location: login.php");
  exit();
}

$farmer_id = $_SESSION['farmer_id'];

// Get farmer data
$query = "SELECT * FROM farmers WHERE farmer_id='$farmer_id'";
$result = $conn->query($query);
if ($result->num_rows == 1) {
  $farmer = $result->fetch_assoc();
} else {
  die("Farmer not found.");
}

$posts = $conn->query("SELECT COUNT(*) as total FROM posts WHERE farmer_id='$farmer_id' AND status='active'")->fetch_assoc()['total'];
$buyers = $conn->query("SELECT COUNT(DISTINCT buyer_id) as total FROM inquiries WHERE farmer_id='$farmer_id'")->fetch_assoc()['total'];
$sold = $conn->query("SELECT COUNT(*) as total FROM posts WHERE farmer_id='$farmer_id' AND status='sold'")->fetch_assoc()['total'];

// Profile picture
$profilePicture = !empty($farmer['profile_picture']) ? 'uploads/' . $farmer['profile_picture'] : 'default-avatar.png';

// Full address (for map)
$fullAddress = urlencode($farmer['address'] . ', ' . $farmer['city'] . ', ' . $farmer['district']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Profile | RiceLink</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />

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
      }

      .content h1 {
        font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
          "Lucida Sans", Arial, sans-serif;
        font-weight: lighter;
      }

      .content p {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
      }

      .content button {
        border: none;
        background-color: bisque;
        padding: 8px 17px;
        border-radius: 25px;
      }

      .content button:hover {
        background-color: rgb(250, 221, 186);
      }

      .display-1 {
        flex: 1;
        background-color: #eef0f3;
        margin-left: 50px;
        border-radius: 20px;
        box-shadow: 2px 2px 15px rgb(0, 0, 0, 0.5);
        width: 700px;
        min-height: 100vh;
        transform: translateY(-100px);
        padding-bottom: 15px;
      }

      .user-info {
        border-bottom: #ced2da 2px solid;
        padding-bottom: 40px;
      }

      .user-info h4 {
        padding: 15px;
        border-bottom: #ced2da 2px solid;
      }

      .display-1 p {
        margin-left: 15px;
        font-size: 12px;
        font-weight: lighter;
        text-transform: uppercase;
        color: #a1a3a8;
        font-family: Arial, Helvetica, sans-serif;
      }

      .box-1 {
        display: flex;
        gap: 32px;
        padding: 12px 15px;
      }

      .display-group {
        flex: 1;
        display: flex;
        flex-direction: column;
      }

      .display-group label {
        font-size: 14px;
        color: #565b66;
        margin-bottom: 8px;
        font-family: Arial, Helvetica, sans-serif;
      }

      .display-box {
        padding: 12px 16px;
        border-radius: 20px;
        font-size: 14px;
        color: #374151;
        background-color: #ffffff;
        margin-left: 5px;
        box-sizing: border-box;
        box-shadow: 2px 2px 15px rgb(0, 0, 0, 0.3);
      }

      .contact-info {
        border-bottom: #ced2da 2px solid;
        padding-bottom: 40px;
      }

      .dis-2 {
        display: flex;
        flex-direction: column;
        gap: 20px;
        flex: 1;
      }

      .display-2 {
        background-color: #eef0f3;
        margin-left: 50px;
        border-radius: 20px;
        box-shadow: 2px 2px 15px rgb(0, 0, 0, 0.5);
        width: 450px;
        height: 450px;
        transform: translateY(-100px);
        padding-bottom: 15px;
      }

      .main-container {
        display: flex;
        gap: 50px;
      }

      .profile-container {
        justify-content: center;
        align-items: center;
      }

      .profile-pic {
        position: absolute;
        top: -60px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      }

      .profile-pic img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .sum-1 {
        text-align: center;
        padding-top: 70px;
        color: #5e6570;
        line-height: 3px;
        font-size: 14px;
        font-family: Arial, Helvetica, sans-serif;
      }

      .btn-summery {
        margin: 60px 70px 25px;
        display: flex;
        gap: 30px;
        text-align: center;
        align-items: center;
        color: #284e12;
      }

      .btn-summery p{
        font-family: Arial, Helvetica, sans-serif;
        font-size: 15px;
      }

      .action-buttons {
        display: flex;
        gap: 20px;
        padding: 10px 70px;
      }

      .action-button {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 90px;
        height: 90px;
        border-radius: 25px;
        border: 1px solid #7d8d6a;
        cursor: pointer;
        transition: 0.3s;
        text-align: center;
        margin-left: 3px;
      }

      .action-button:hover {
        background-color: #eef6ff;
        border-color: #356817;
      }

      .action-button.active {
        background-color: #e6f0ff;
        border-color: #356817;
      }

      .action-button i {
        color: #356817;
        margin-bottom: 8px;
      }

      .action-button span {
        font-size: 13px;
        color: #333;
      }

      .display-3 {
        background-color: #eef0f3;
        margin-left: 50px;
        border-radius: 20px;
        box-shadow: 2px 2px 15px rgb(0, 0, 0, 0.5);
        width: 450px;
        height: 450px;
        transform: translateY(-100px);
        padding-bottom: 15px;
        margin-top: 70px;
      }

      #display-map {
        width: 100%;
        height: 100%;
        border-radius: 20px;
      }
    </style>
  </head>

  <body>
    <div class="welcome-image">
      <img src="images/7.jpg" alt="" />
      <div class="welcome-msg">
        <div class="content">
          <h1>
            Welcome,
            <?= htmlspecialchars($farmer['first_name']) ?>
          </h1>
          <p>
            This is your profile page. You can see your personal and profetional
            details and manage it.
          </p>
          <button>Edit Profile</button>
        </div>
      </div>
    </div>

    <!-- my profile -->
    <div class="main-container">
      <div class="display-1">
        <!-- user information -->
        <div class="user-info">
          <h4>My Account</h4>
          <p>User Information</p>

          <div class="box-1">
            <div class="display-group">
              <label>Username</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['username']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>Email</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['email']) ?>
              </div>
            </div>
          </div>

          <div class="box-1">
            <div class="display-group">
              <label>First Name</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['first_name']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>Last Name</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['last_name']) ?>
              </div>
            </div>
          </div>

          <div class="box-1">
            <div class="display-group">
              <label>Gender</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['gender']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>Date Of Birth / Age</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['dob']) ?>
              </div>
            </div>
          </div>

          <div class="box-1">
            <div class="display-group">
              <label>NIC / National ID Number</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['nic']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>Phone Number</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['p_number']) ?>
              </div>
            </div>
          </div>
        </div>

        <!-- contact information -->
        <div class="contact-info">
          <p>Contact Information</p>

          <div class="box-1">
            <div class="display-group">
              <label>Address</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['address']) ?>
              </div>
            </div>
          </div>

          <div class="box-1">
            <div class="display-group">
              <label>District / Province</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['district']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>City / Town</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['city']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>Postal Code</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['postal_code']) ?>
              </div>
            </div>
          </div>
        </div>

        <!-- profetional information -->
        <div class="profetional-info">
          <p>Profetional Information</p>

          <div class="box-1">
            <div class="display-group">
              <label>Farmer ID</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['farmer_id']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>Type of Farming</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['farm_type']) ?>
              </div>
            </div>
          </div>

          <div class="box-1">
            <div class="display-group">
              <label>Years of Experience</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['experience']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>Land Size</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['land_size']) ?>
              </div>
            </div>
          </div>

          <div class="box-1">
            <div class="display-group">
              <label>Certifications / Licenses</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['license']) ?>
              </div>
            </div>
            <div class="display-group">
              <label>Membership in Agricultural Associations</label>
              <div class="display-box">
                <?= htmlspecialchars($farmer['membership']) ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="dis-2">
        <div class="display-2">
          <!-- profile pic -->
          <div class="profile-container">
            <div class="profile-pic">
              <img
                src="<?= $_SESSION['user_image'] ?? 'uploads/default.png' ?>"
                alt="Profile"
              />
            </div>
          </div>

          <div class="summery">
            <div class="sum-1">
              <p>
                <?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']) ?>
              </p>
              <p><?= htmlspecialchars($farmer['email']) ?></p>
              <p><?= htmlspecialchars($farmer['address']) ?></p>
            </div>
            <div class="btn-summery">
              <p>
                Active Posts
                <?= $posts ?>
              </p>
              <p>
                Total Buyers
                <?= $buyers ?>
              </p>
              <p>
                Product Sold
                <?= $sold ?>
              </p>
            </div>
          </div>

          <div class="action-buttons">
            <div class="action-button" onclick="logout()">
              <i class="fas fa-sign-out-alt"></i>
              <span>Logout</span>
            </div>
            <div class="action-button" onclick="changePassword()">
              <i class="fas fa-key"></i>
              <span>Change Password</span>
            </div>
            <div class="action-button" onclick="deactivateAccount()">
              <i class="fas fa-user-slash"></i>
              <span>Delete Account</span>
            </div>
          </div>
        </div>

        <div class="display-3">
          <iframe
            width="100%"
            height="300"
            frameborder="0"
            style="border: 0"
            referrerpolicy="no-referrer-when-downgrade"
            src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_MAPS_API_KEY&q=<?= $fullAddress ?>"
            allowfullscreen
          >
          </iframe>
        </div>
      </div>
    </div>

    <!-- Google Maps Script -->
    <script>
      function initMap() {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode(
          {
            address: "<?= htmlspecialchars($farmer['city']) ?>, Sri Lanka",
          },
          function (results, status) {
            if (status === "OK") {
              const map = new google.maps.Map(
                document.getElementById("display-map"),
                {
                  zoom: 10,
                  center: results[0].geometry.location,
                }
              );
              new google.maps.Marker({
                map: map,
                position: results[0].geometry.location,
              });
            } else {
              alert("Geocode failed: " + status);
            }
          }
        );
      }
    </script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap"
      async
      defer
    ></script>

    <script>
      // Logout
      function logout() {
        if (confirm("Are you sure you want to logout?")) {
          window.location.href = "logout.php";
        }
      }

      // Change password
      function changePassword() {
        window.location.href = "change_password.php";
      }

      // Delete account
      function deactivateAccount() {
        if (
          confirm("Are you sure you want to delete your account permanently?")
        ) {
          window.location.href = "delete_account.php";
        }
      }

      window.onload = () => {
        const boxes = document.querySelectorAll(".display-box");
        const values = [
          "<?= htmlspecialchars($farmer['username']) ?>",
          "<?= htmlspecialchars($farmer['email']) ?>",
          "<?= htmlspecialchars($farmer['first_name']) ?>",
          "<?= htmlspecialchars($farmer['last_name']) ?>",
          "<?= htmlspecialchars($farmer['gender']) ?>",
          "<?= htmlspecialchars($farmer['dob']) ?>",
          "<?= htmlspecialchars($farmer['nic']) ?>",
          "<?= htmlspecialchars($farmer['phone']) ?>",
          "<?= htmlspecialchars($farmer['address']) ?>",
          "<?= htmlspecialchars($farmer['district']) ?>",
          "<?= htmlspecialchars($farmer['city']) ?>",
          "<?= htmlspecialchars($farmer['postal_code']) ?>",
          "<?= htmlspecialchars($farmer['farmer_id']) ?>",
          "<?= htmlspecialchars($farmer['farming_type']) ?>",
          "<?= htmlspecialchars($farmer['experience']) ?>",
          "<?= htmlspecialchars($farmer['land_size']) ?>",
          "<?= htmlspecialchars($farmer['certifications']) ?>",
          "<?= htmlspecialchars($farmer['memberships']) ?>",
        ];
        boxes.forEach((box, i) => (box.textContent = values[i]));

        // Update profile picture
        const profileImg = document.querySelector(".profile-pic img");
        profileImg.src =
          "uploads/<?= htmlspecialchars($farmer['profile_image']) ?>";

        // Update summary
        document.querySelector(".sum-1").innerHTML = `
      <p><?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']) ?></p>
      <p><?= htmlspecialchars($farmer['email']) ?></p>
      <p><?= htmlspecialchars($farmer['city']) ?></p>
    `;

        // Update stats
        document.querySelector(".btn-summery").innerHTML = `
      <p>Active Posts: <?= $posts ?></p>
      <p>Total Buyers: <?= $buyers ?></p>
      <p>Product Sold: <?= $sold ?></p>
    `;
      };
    </script>
  </body>
</html>
