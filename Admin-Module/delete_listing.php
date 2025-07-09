<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'], $_POST['token'])) {
        die("Invalid request.");
    }

    $id = intval($_POST['id']);
    $token = $_POST['token'];

    if ($token !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch.");
    }

    $stmt = $conn->prepare("DELETE FROM listings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage-listings.php?msg=deleted");
    exit();
} else {
    die("Invalid access.");
}
?>
