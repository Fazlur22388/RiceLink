<?php
session_start();
include('../backend/db_connect.php');

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

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        die("Invalid CSRF token.");
    }

    $stmt = $conn->prepare("UPDATE listings SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Listing rejected successfully!');
            window.location.href = 'manage-listings.php?msg=rejected';
        </script>";
    } else {
        die("Database error during rejection.");
    }
}
?>
