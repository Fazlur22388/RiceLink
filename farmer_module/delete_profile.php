<?php
session_start();
include('../backend/db_connect.php');

// Check if farmer is logged in


$farmer_id = $_SESSION['farmer_id'];

// Ensure the 'id' parameter is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $listing_id = intval($_GET['id']);

    // Check if the listing belongs to the logged-in farmer
    $check_query = "SELECT * FROM listings WHERE id = ? AND farmer_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $listing_id, $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If found, proceed with deletion
    if ($result->num_rows > 0) {
        $delete_query = "DELETE FROM listings WHERE id = ? AND farmer_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $listing_id, $farmer_id);
        if ($stmt->execute()) {
            header("Location: farmer_dashboard.php?msg=deleted");
            exit();
        } else {
            echo "Error deleting listing.";
        }
    } else {
        echo "Unauthorized or listing not found.";
    }
} else {
    echo "Invalid request.";
}
?>
