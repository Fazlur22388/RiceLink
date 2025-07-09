<?php
session_start();
include('db_connect.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Remove (delete) a listing
if (isset($_POST['remove_id'])) {
    $remove_id = (int)$_POST['remove_id'];
    // Option 1: Soft delete (change status)
    $update_query = "UPDATE listings SET status = 'removed' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $remove_id);
    $stmt->execute();

    // Option 2: Hard delete (uncomment below to delete from DB)
    /*
    $delete_query = "DELETE FROM listings WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $remove_id);
    $stmt->execute();
    */
}

// Fetch all listings except removed ones (or include removed if you want)
$query = "SELECT l.*, f.name AS farmer_name FROM listings l JOIN farmers f ON l.farmer_id = f.id WHERE l.status != 'removed' ORDER BY l.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Remove Listings - Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin-style.css" />
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard - Remove Listings</h1>
        <nav>
            <a href="admin-dashboard.php">Dashboard</a> |
            <a href="approve-listings.php">Approve Listings</a> |
            <a href="remove-listings.php">Remove Listings</a> |
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h2>Current Listings</h2>

        <?php if ($result->num_rows == 0): ?>
            <p>No listings available.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Farmer</th>
                        <th>Status</th>
                        <th>Price per kg</th>
                        <th>Quantity (kg)</th>
                        <th>Posted On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($listing = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($listing['id']); ?></td>
                        <td><?php echo htmlspecialchars($listing['title']); ?></td>
                        <td><?php echo htmlspecialchars($listing['farmer_name']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($listing['status'])); ?></td>
                        <td>Rs. <?php echo number_format($listing['price_per_kg'], 2); ?></td>
                        <td><?php echo htmlspecialchars($listing['quantity']); ?></td>
                        <td><?php echo date('F j, Y', strtotime($listing['created_at'])); ?></td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="remove_id" value="<?php echo $listing['id']; ?>" />
                                <button type="submit" onclick="return confirm('Remove this listing? This action cannot be undone.')">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
