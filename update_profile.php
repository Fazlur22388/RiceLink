<?php
session_start();
include 'db_connect.php'; // include your DB connection script

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
  die("Unauthorized access.");
}

// Get form inputs
$username = $_POST['username'];
$email = $_POST['email'];
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$nic = $_POST['nic'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$district = $_POST['district'];
$city = $_POST['city'];
$pcode = $_POST['pcode'];
$farmType = $_POST['farm-type'];
$experience = $_POST['experience'];
$landSize = $_POST['land-size'];
$membership = isset($_POST['membership']) ? 1 : 0;

// Handle profile picture if uploaded
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $targetDir = "uploads/";
    $fileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
        $profileImage = $targetFile;
        $_SESSION['user_image'] = $profileImage;

        // Update profile image
        $stmt = $conn->prepare("UPDATE farmers SET profile_image = ? WHERE farmer_id = ?");
        $stmt->bind_param("si", $profileImage, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

// Update other fields
$stmt = $conn->prepare("UPDATE farmers SET username=?, email=?, first_name=?, last_name=?, gender=?, dob=?, nic=?, phone=?, address=?, district=?, city=?, postal_code=?, farm_type=?, experience=?, land_size=?, membership=? WHERE farmer_id=?");

$stmt->bind_param("sssssssssssisssii", 
  $username, $email, $fname, $lname, $gender, $dob, $nic, $phone, $address,
  $district, $city, $pcode, $farmType, $experience, $landSize, $membership, $userId
);

if ($stmt->execute()) {
  $_SESSION['success'] = "Profile updated successfully!";
  header("Location: profile.php");
  exit;
} else {
  echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
