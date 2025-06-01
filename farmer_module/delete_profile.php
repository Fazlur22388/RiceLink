<?php
session_start();
require 'db_connect.php';

$farmer_id = $_SESSION['farmer_id'] ?? null;
if ($farmer_id) {
    mysqli_query($conn, "DELETE FROM farmers WHERE id = $farmer_id");
    session_destroy();
}
header("Location: register.php");
exit;
?>