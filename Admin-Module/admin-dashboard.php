<?php 
session_start();
include('../backend/db_connect.php');

// Check if admin logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get stats
$total_buyers = $conn->query("SELECT COUNT(*) AS total FROM buyers")->fetch_assoc()['total'];
$total_farmers = $conn->query("SELECT COUNT(*) AS total FROM farmers")->fetch_assoc()['total'];
$total_listings = $conn->query("SELECT COUNT(*) AS total FROM listings")->fetch_assoc()['total'];
// Removed flagged listings count to avoid error
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - RiceLink</title>
    <link rel="stylesheet" href="css/admin-style.css" />
</head>
<body>
 

    <header class="main-header">
        <h2>Admin Dashboard - RiceLink</h2>
    </header>

    <div class="container">
        <h1>Welcome, Admin</h1>
        

        <div class="stats">
            <div class="stat">
                <h2><?php echo $total_buyers; ?></h2>
                <p>Total Buyers</p>
            </div>
            <div class="stat">
                <h2><?php echo $total_farmers; ?></h2>
                <p>Total Farmers</p>
            </div>
            <div class="stat">
                <h2><?php echo $total_listings; ?></h2>
                <p>Total Listings</p>                                                                                                                   /* Reset */

 
            </div>
        </div>

        <div class="admin-links">
            <a href="manage-listings.php">Manage Listings</a>
            <a href="view-users.php">View Users</a>
            <a href="available_rice_product.php">Available Rice Products</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
</body>
</html>