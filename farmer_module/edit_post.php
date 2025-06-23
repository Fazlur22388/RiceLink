<?php
session_start();
include('../backend/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['farmer_id'])) {
    header("Location: login.php");
    exit();
}

// Check if post ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = intval($_GET['id']);

    // Delete the listing from the database
    $sql = "DELETE FROM listings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        header("Location: farmer_dashboard.php?msg=deleted");
        exit();
    } else {
        echo "Error deleting the post.";
    }
} else {
    echo "Invalid post ID.";
}
?>
