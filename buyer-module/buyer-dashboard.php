<?php
session_start();
include('../backend/db_connect.php');

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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>RiceLink</h1>
            <div class="nav-links">
                <a href="browse-listings.php">Browse Listings</a>
                <a href="messages.php">Messages</a>
                <a href="../backend/logout.php" onclick="return confirmLogout();" class="btn-logout">Logout</a>
                <script>
                function confirmLogout() {
                     return confirm("Are you sure you want to logout?");
                     }
                </script>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card" style="margin-bottom: 30px; background: var(--primary-color); color: white;">
            <div class="card-body">
                <h2 style="color: white; margin-bottom: 10px;">Welcome, <?php echo htmlspecialchars($buyer['name']); ?>!</h2>
                <p>Find and connect with rice farmers across Sri Lanka.</p>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-label">New Listings</div>
                <div class="stat-value"><?php echo $latest_listings->num_rows; ?></div>
                <a href="browse-listings.php" class="btn btn-outline">Browse All</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Bookmarks</div>
                <div class="stat-value"><?php echo $bookmarked_listings->num_rows; ?></div>
                <a href="browse-listings.php?filter=bookmarked" class="btn btn-outline">View Bookmarks</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Messages</div>
                <div class="stat-value"><?php echo $messages->num_rows; ?></div>
                <a href="messages.php" class="btn btn-outline">Check Messages</a>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>Recent Listings</h2>
            <div class="listings-grid">
                <?php if ($latest_listings->num_rows > 0): ?>
                    <?php 
                    // Reset the result pointer to the beginning
                    $latest_listings->data_seek(0);
                    while ($listing = $latest_listings->fetch_assoc()): 
                    ?>
                        <div class="listing-card">
                            <div class="listing-image">
                                <?php if (!empty($listing['image_url'])): ?>
                                    <img src="../<?php echo htmlspecialchars($listing['image_url']); ?>" alt="Rice Image">
                                <?php else: ?>
                                    <div style="color: #999; font-size: 18px;">No image available</div>
                                <?php endif; ?>
                            </div>
                            <div class="listing-details">
                                <h3 class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></h3>
                                <div class="listing-meta">
                                    <div class="listing-price">Rs. <?php echo number_format($listing['price_per_kg'], 2); ?>/kg</div>
                                    <div class="listing-location"><?php echo htmlspecialchars($listing['rice_type']); ?></div>
                                </div>
                                <p><a href="listing-detail.php?id=<?php echo $listing['id']; ?>" class="btn">View Details</a></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <p>No listings available at the moment.</p>
                            <a href="browse-listings.php" class="btn">Refresh Listings</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>Bookmarked Listings</h2>
            <?php if ($bookmarked_listings->num_rows > 0): ?>
                <div class="listings-grid">
                    <?php 
                    // Reset the result pointer to the beginning
                    $bookmarked_listings->data_seek(0);
                    while ($bookmark = $bookmarked_listings->fetch_assoc()): 
                    ?>
                        <div class="listing-card">
                            <div class="listing-image">
                                <?php if (!empty($bookmark['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($bookmark['image_url']); ?>" alt="Rice Image">
                                <?php else: ?>
                                    <div style="color: #999; font-size: 18px;">No image available</div>
                                <?php endif; ?>
                            </div>
                            <div class="listing-details">
                                <h3 class="listing-title"><?php echo htmlspecialchars($bookmark['title']); ?></h3>
                                <div class="listing-meta">
                                    <div class="listing-price">Rs. <?php echo number_format($bookmark['price_per_kg'], 2); ?>/kg</div>
                                    <div class="listing-location"><?php echo htmlspecialchars($bookmark['rice_type']); ?></div>
                                </div>
                                <p><a href="listing-detail.php?id=<?php echo $bookmark['id']; ?>" class="btn">View Details</a></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <p>You haven't bookmarked any listings yet.</p>
                        <a href="browse-listings.php" class="btn">Browse Listings</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <h2>Recent Messages</h2>
            <?php if ($messages->num_rows > 0): ?>
                <div class="card">
                    <?php 
                    // Reset the result pointer to the beginning
                    $messages->data_seek(0);
                    while ($message = $messages->fetch_assoc()): 
                    ?>
                        <div style="padding: 15px; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3 style="margin: 0; font-size: 16px; color: var(--primary-color);">
                                    <?php echo htmlspecialchars($message['farmer_name']); ?>
                                </h3>
                                <small style="color: var(--text-light);"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></small>
                            </div>
                            <p style="margin: 10px 0;">
                                <?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>
                                <?php if (strlen($message['message']) > 100): ?>...<?php endif; ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                    <div style="padding: 15px; text-align: center;">
                        <a href="messages.php" class="btn">View All Messages</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <p>No messages yet.</p>
                        <a href="browse-listings.php" class="btn">Contact Farmers</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 