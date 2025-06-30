<?php
session_start();
include('../backend/db_connect.php');

if (!isset($_SESSION['farmer_id'])) {
  header("Location: login.php");
  exit();
}

$farmer_id = $_SESSION['farmer_id'];

// Step 1: Delete related listings first
$conn->query("DELETE FROM listings WHERE farmer_id = $farmer_id");

// Step 2: Delete farmer record
$conn->query("DELETE FROM farmers WHERE id = $farmer_id");

// Destroy session and redirect
session_destroy();
header("Location: register_farmer.php?msg=Account deleted successfully");
exit();
?>
