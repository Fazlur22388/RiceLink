<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['farmer_id'])) {
  header("Location: login.php");
  exit();
}
$farmer_id = $_SESSION['farmer_id'];

$conn = new mysqli("localhost", "root", "", "rice_link_db");
$conn->query("DELETE FROM farmers WHERE farmer_id='$farmer_id'");
$conn->query("DELETE FROM posts WHERE farmer_id='$farmer_id'");
$conn->query("DELETE FROM inquiries WHERE farmer_id='$farmer_id'");

session_destroy();
echo "<script>alert('Account deleted successfully.'); window.location.href = 'register.php';</script>";
?>
