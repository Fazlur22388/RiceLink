<?php
session_start();
include('../backend/db_connect.php');

if (!isset($_SESSION['farmer_id'])) {
  header("Location: login.php");
  exit();
}

$farmer_id = $_SESSION['farmer_id'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $current = $_POST['current_password'];
  $new = $_POST['new_password'];
  $confirm = $_POST['confirm_password'];

  $result = $conn->query("SELECT password FROM farmers WHERE id = $farmer_id");
  $row = $result->fetch_assoc();

  if (!password_verify($current, $row['password'])) {
    $msg = "Current password is incorrect!";
  } elseif ($new !== $confirm) {
    $msg = "New passwords do not match!";
  } else {
    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $conn->query("UPDATE farmers SET password = '$hashed' WHERE id = $farmer_id");
    // Redirect to dashboard with success message
    header("Location: farmer_dashboard.php?msg=Password changed successfully");
    exit();
  }
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Change Password | RiceLink</title>
    <style>
      body {
        padding: 0;
        margin: 0;
      }

      .main-container {
        margin: 200px 550px;
        background-color: rgb(242, 245, 244);
        padding: 20px;
        border-radius: 20px;
        box-shadow: 4px 3px 6px rgb(0, 0, 0, 0.3);
      }

      h2 {
        text-align: center;
        font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
          "Lucida Sans", Arial, sans-serif;
        font-weight: lighter;
        margin-bottom: 5px;
      }

      p {
        text-align: center;
        margin-top: 5px;
        color: rgb(164, 165, 166);
        font-size: 14px;
        font-family: Arial, Helvetica, sans-serif;
        margin-bottom: 35px;
      }

      label{
        font-family: Arial, Helvetica, sans-serif;
        margin-top: 10px;

      }

      .main-container input[type="password"] {
        padding: 8px;
        box-sizing: border-box;
        width: 100%;
        border-radius: 8px;
        margin-top: 5px;
        background-color: rgb(242, 245, 244);
        border-color: rgb(211, 211, 211);
      }

      .main-container input[type="submit"]{
        background-color: rgb(10, 10, 66);
        padding: 10px;
        border-radius: 25px;
        color: white;
        margin-left: 100px;
      }
    </style>
  </head>
  <body>
    <div class="main-container">
      <h2>Update Password</h2>
      <p>Please fill the form below to update the password</p>
      <form method="POST">
        <label>Current Password</label><br />
        <input type="password" name="current_password" required /><br /><br />

        <label>New Password</label><br />
        <input type="password" name="new_password" required /><br /><br />

        <label>Confirm New Password</label><br />
        <input type="password" name="confirm_password" required /><br /><br />

        <input type="submit" value="Update Password" />
      </form>
    </div>
    <p style="color: red"><?= $msg ?></p>
  </body>
</html>
