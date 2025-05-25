<?php
session_start();
include('db_connect.php');

// You can fetch from DB instead
$profileImage = $_SESSION['user_image'] ?? 'uploads/default.png';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Profile | RiceLink</title>
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

      .content button {
        border: none;
        background-color: bisque;
        padding: 8px 17px;
        border-radius: 8px;
      }

      .content button:hover {
        background-color: rgb(243, 189, 123);
      }

      .display-1 {
        /* flex: 1; */
        background-color: #eef0f3;
        margin-left: 300px;
        border-radius: 8px;
        box-shadow: 2px 2px 15px rgb(0, 0, 0, 0.5);
        width: 700px;
        min-height: 100vh;
        transform: translateY(-100px);
        padding-bottom: 15px;
      }

      .profile-edit-wrapper {
        position: absolute;
        top: 400px; /* adjust upward position */
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
      }

      .profile-container {
        position: relative;
        width: 100px;
        height: 100px;
      }

      .profile-image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        background-color: white;
      }

      .edit-icon {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: white;
        border-radius: 50%;
        padding: 5px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        cursor: pointer;
      }

      .edit-icon input[type="file"] {
        display: none;
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
      }

      .display-box input {
        border: none;
        padding: 5px 16px;
        border-radius: 8px;
        font-size: 14px;
        color: #374151;
        background-color: #ffffff;
        margin-left: 5px;
        box-sizing: border-box;
        box-shadow: 2px 2px 15px rgb(0, 0, 0, 0.3);
        width: 100%;
      }

      .display-box select {
        border: none;
        padding: 5px 16px;
        border-radius: 8px;
        font-size: 14px;
        color: #374151;
        background-color: #ffffff;
        margin-left: 5px;
        box-sizing: border-box;
        box-shadow: 2px 2px 15px rgb(0, 0, 0, 0.3);
        width: 100%;
      }

      .farmer-id {
        padding: 13px 16px;
        border-radius: 8px;
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

      .membership {
        color: black;
        background-color: white;
        padding: 7px 16px;
        border-radius: 8px;
        box-shadow: 2px 2px 15px rgb(0, 0, 0, 0.3);
        margin-left: 5px;
        box-sizing: border-box;
      }

      .btn-submit {
        margin: 20px auto 20px 550px;
        border: none;
        background-color: rgb(19, 78, 19);
        padding: 12px 16px;
        color: white;
        border-radius: 8px;
      }
    </style>
  </head>
  <body>
    <div class="welcome-image">
      <img src="images/7.jpg" alt="" />
      <div class="welcome-msg">
        <div class="content">
          <h1>Edit Your Profile Here,</h1>
          <p>
            This is your edit profile page. You can see your personal and
            profetional details and manage it.
          </p>
        </div>
      </div>
    </div>

    <!-- my profile -->
    <div class="main-container">
      <form
        id="editForm"
        action="update_profile.php"
        method="POST"
        enctype="multipart/form-data"
      >
        <div class="profile-edit-wrapper">
          <div class="profile-container">
            <img
              src="<?= $profileImage ?>"
              alt="Profile Picture"
              class="profile-image"
              id="profilePic"
            />
            <form
              id="uploadForm"
              action="upload_profile_picture.php"
              method="POST"
              enctype="multipart/form-data"
            >
              <div class="edit-icon">
                <label for="fileInput">
                  <img
                    src="images/camera.png"
                    width="24"
                    height="24"
                    alt="Edit"
                  />
                </label>
                <input
                  type="file"
                  name="profile_picture"
                  id="fileInput"
                  accept="image/*"
                  onchange="previewAndUpload()"
                />
              </div>
            </form>
          </div>
        </div>

        <div class="display-1">
          <!-- user information -->
          <div class="user-info">
            <h4>My Account</h4>
            <p>User Information</p>

            <div class="box-1">
              <div class="display-group">
                <label for="username">Username</label>
                <div class="display-box">
                  <input type="text" id="username" name="username" />
                </div>
              </div>
              <div class="display-group">
                <label for="email">Email</label>
                <div class="display-box">
                  <input type="email" id="email" name="email" />
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <label for="fname">First Name</label>
                <div class="display-box">
                  <input type="text" name="fname" id="fname" />
                </div>
              </div>
              <div class="display-group">
                <label for="lname">Last Name</label>
                <div class="display-box">
                  <input type="text" name="lname" id="lname" />
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <label for="gender">Gender</label>
                <div class="display-box">
                  <select name="gender" id="gender">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                  </select>
                </div>
              </div>
              <div class="display-group">
                <label for="dob">Date Of Birth / Age</label>
                <div class="display-box">
                  <input type="date" id="dob" name="dob" />
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <label for="nic">NIC / National ID Number</label>
                <div class="display-box">
                  <input type="text" id="nic" name="nic" />
                </div>
              </div>
              <div class="display-group">
                <label for="phone">Phone Number</label>
                <div class="display-box">
                  <input type="tel" id="phone" name="phone" />
                </div>
              </div>
            </div>
          </div>

          <!-- contact information -->
          <div class="contact-info">
            <p>Contact Information</p>

            <div class="box-1">
              <div class="display-group">
                <label for="address">Address</label>
                <div class="display-box">
                  <input type="text" id="address" name="address" />
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <label for="district">District / Province</label>
                <div class="display-box">
                  <input type="text" id="district" name="district" />
                </div>
              </div>
              <div class="display-group">
                <label for="city">City / Town</label>
                <div class="display-box">
                  <input type="text" id="city" name="city" />
                </div>
              </div>
              <div class="display-group">
                <label for="pcode">Postal Code</label>
                <div class="display-box">
                  <input type="number" id="pcode" name="pcode" />
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
                <div class="farmer-id">
                  <?= htmlspecialchars($farmer['farmer_id']) ?>
                </div>
              </div>
              <div class="display-group">
                <label for="farm-type">Type of Farming</label>
                <div class="display-box">
                  <select name="farm-type" id="farm-type">
                    <option value="rice">Paddy Farmers</option>
                    <option value="vegetable">Vegetable Growers</option>
                    <option value="tea">Tea Planters</option>
                    <option value="fruit">Fruit Growers</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <label for="experience">Years of Experience</label>
                <div class="display-box">
                  <input type="number" id="experience" name="experience" />
                </div>
              </div>
              <div class="display-group">
                <label for="land-size">Land Size</label>
                <div class="display-box">
                  <input type="number" id="land-size" name="land-size" />
                </div>
              </div>
            </div>

            <div class="box-1">
              <div class="display-group">
                <label for="license">Certifications / Licenses</label>
                <div class="display-box">
                  <input type="file" id="license" name="license" />
                </div>
              </div>
              <div class="display-group">
                <label for="membership"
                  >Membership in Agricultural Associations</label
                >
                <div class="membership">
                  <input type="checkbox" id="membership" name="membership" />
                  <label for="membership">Yes</label>
                </div>
              </div>
            </div>
          </div>
          <input type="submit" value="Save Changes" class="btn-submit" />
        </div>
      </form>
    </div>

    <script>
      document
        .getElementById("editForm")
        .addEventListener("submit", function (e) {
          const phone = document.getElementById("phone").value;
          const nic = document.getElementById("nic").value;

          if (!/^\d{10}$/.test(phone)) {
            alert("Please enter a valid 10-digit phone number.");
            e.preventDefault();
          }

          if (!nic || nic.length < 10) {
            alert("NIC must be at least 12 characters.");
            e.preventDefault();
          }
        });

      function previewAndUpload() {
        const fileInput = document.getElementById("fileInput");
        const profilePic = document.getElementById("profilePic");

        if (fileInput.files && fileInput.files[0]) {
          const reader = new FileReader();
          reader.onload = function (e) {
            profilePic.src = e.target.result;
            document.getElementById("editForm").submit(); // Optional: auto-submit
          };
          reader.readAsDataURL(fileInput.files[0]);
        }
      }
    </script>
  </body>
</html>
