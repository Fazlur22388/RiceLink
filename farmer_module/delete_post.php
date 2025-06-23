<?php
include('../backend/db_connect.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = intval($_GET['id']);

    $sql = "DELETE FROM listings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        header("Location: farmer_dashboard.php?msg=deleted");
        exit();
    } else {
        echo "Error deleting post.";
    }
} else {
    echo "Invalid post ID.";
}
?>
