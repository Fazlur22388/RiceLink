<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'])) {
        die("Invalid request.");
    }

    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM rice_prices WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: available_rice_product.php?msg=deleted");
    exit();
} else {
    die("Unauthorized access.");
}
?>
