<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['token'])) {
        die("Missing data.");
    }

    $id = intval($_POST['id']);
    $token = $_POST['token'];

    // CSRF Token check
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        die("Invalid CSRF token.");
    }

    // Only delete if the listing is rejected
    $stmt = $conn->prepare("DELETE FROM listings WHERE id = ? AND status = 'rejected'");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Rejected listing deleted successfully.');
            window.location.href = 'manage-listings.php?msg=deleted';
        </script>";
    } else {
        die("Error deleting listing.");
    }
} else {
    die("Invalid request method.");
}
