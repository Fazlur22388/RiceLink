<?php
session_start();
include('db_connect.php');

// Check if the user is logged in as a buyer
if (!isset($_SESSION['buyer_id'])) {
    header("Location: login.php");
    exit();
}

// Get buyer information
$buyer_id = $_SESSION['buyer_id'];
$sql = "SELECT * FROM buyers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
$buyer = $result->fetch_assoc();

// Get the latest listings
$latest_listings_sql = "SELECT * FROM listings ORDER BY created_at DESC LIMIT 6";
$latest_listings = $conn->query($latest_listings_sql);

// Get bookmarked listings if any
$bookmarked_sql = "SELECT l.* FROM listings l 
                  JOIN bookmarks b ON l.id = b.listing_id 
                  WHERE b.buyer_id = ? 
                  ORDER BY b.created_at DESC LIMIT 4";
$stmt = $conn->prepare($bookmarked_sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$bookmarked_listings = $stmt->get_result();

// Get latest messages
$messages_sql = "SELECT m.*, f.name as farmer_name 
                FROM messages m 
                JOIN farmers f ON m.farmer_id = f.id 
                WHERE m.buyer_id = ? 
                ORDER BY m.created_at DESC LIMIT 5";
$stmt = $conn->prepare($messages_sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - RiceLink</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: rgb(22, 199, 96);
            padding: 15px 0;
            color: white;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            margin: 0 auto;
        }
        .header h1 {
            margin: 0;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .dashboard-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .dashboard-card h2 {
            color: rgb(22, 199, 96);
            margin-top: 0;
        }
        .listing-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .listing-item:last-child {
            border-bottom: none;
        }
        .btn {
            background: rgb(22, 199, 96);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .welcome-banner {
            background: linear-gradient(to right, rgb(22, 199, 96), #2ecc71);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>RiceLink</h1>
            <div class="nav-links">
                <a href="browse-listings.php">Browse Listings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome-banner">
            <h2>Welcome, <?php echo htmlspecialchars($buyer['name']); ?>!</h2>
            <p>Find and connect with rice farmers across Sri Lanka.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Recent Listings</h2>
                <?php if ($latest_listings->num_rows > 0): ?>
                    <?php while ($listing = $latest_listings->fetch_assoc()): ?>
                        <div class="listing-item">
                            <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
                            <p>Type: <?php echo htmlspecialchars($listing['rice_type']); ?></p>
                            <p>Price: Rs. <?php echo htmlspecialchars($listing['price_per_kg']); ?> per kg</p>
                            <a href="listing-detail.php?id=<?php echo $listing['id']; ?>" class="btn">View Details</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No listings available at the moment.</p>
                <?php endif; ?>
                <a href="browse-listings.php" class="btn">View All Listings</a>
            </div>

            <div class="dashboard-card">
                <h2>Bookmarked Listings</h2>
                <?php if ($bookmarked_listings->num_rows > 0): ?>
                    <?php while ($bookmark = $bookmarked_listings->fetch_assoc()): ?>
                        <div class="listing-item">
                            <h3><?php echo htmlspecialchars($bookmark['title']); ?></h3>
                            <p>Type: <?php echo htmlspecialchars($bookmark['rice_type']); ?></p>
                            <p>Price: Rs. <?php echo htmlspecialchars($bookmark['price_per_kg']); ?> per kg</p>
                            <a href="listing-detail.php?id=<?php echo $bookmark['id']; ?>" class="btn">View Details</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>You haven't bookmarked any listings yet.</p>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h2>Recent Messages</h2>
                <?php if ($messages->num_rows > 0): ?>
                    <?php while ($message = $messages->fetch_assoc()): ?>
                        <div class="listing-item">
                            <p><strong>From:</strong> <?php echo htmlspecialchars($message['farmer_name']); ?></p>
                            <p><?php echo htmlspecialchars(substr($message['message'], 0, 50)); ?>...</p>
                            <small><?php echo date('M d, Y', strtotime($message['created_at'])); ?></small>
                        </div>
                    <?php endwhile; ?>
                    <a href="messages.php" class="btn">View All Messages</a>
                <?php else: ?>
                    <p>No messages yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 