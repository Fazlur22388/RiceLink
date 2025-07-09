<?php
session_start();
include('db_connect.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $farmer_id = $_GET['id'];

    // Prepare and execute deletion
    $stmt = $conn->prepare("DELETE FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to the users page
header("Location: view-users.php"); // Adjust if filename differs
exit();
?>
