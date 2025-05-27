<?php
session_start();
include('../backend/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['buyer_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['buyer_id'];

// Check if listing ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: browse-listings.php");
    exit();
}

$listing_id = (int)$_GET['id'];

// Get listing details
$listing_query = "SELECT l.*, f.name as farmer_name, f.address as location, f.phone, f.whatsapp, f.email 
                 FROM listings l
                 JOIN farmers f ON l.farmer_id = f.id
                 WHERE l.id = ?";
$stmt = $conn->prepare($listing_query);
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: browse-listings.php");
    exit();
}

$listing = $result->fetch_assoc();

// Check if the listing is bookmarked by the current user
$bookmark_check = "SELECT * FROM bookmarks WHERE buyer_id = ? AND listing_id = ?";
$stmt = $conn->prepare($bookmark_check);
$stmt->bind_param("ii", $buyer_id, $listing_id);
$stmt->execute();
$bookmark_result = $stmt->get_result();
$is_bookmarked = ($bookmark_result->num_rows > 0);

// Handle bookmark/unbookmark action
if (isset($_POST['bookmark_action'])) {
    if ($_POST['bookmark_action'] == 'bookmark' && !$is_bookmarked) {
        $add_bookmark = "INSERT INTO bookmarks (buyer_id, listing_id, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($add_bookmark);
        $stmt->bind_param("ii", $buyer_id, $listing_id);
        $stmt->execute();
        $is_bookmarked = true;
    } elseif ($_POST['bookmark_action'] == 'unbookmark' && $is_bookmarked) {
        $remove_bookmark = "DELETE FROM bookmarks WHERE buyer_id = ? AND listing_id = ?";
        $stmt = $conn->prepare($remove_bookmark);
        $stmt->bind_param("ii", $buyer_id, $listing_id);
        $stmt->execute();
        $is_bookmarked = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($listing['title']); ?> - RiceLink</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>RiceLink</h1>
            <div class="nav-links">
                <a href="buyer-dashboard.php">Dashboard</a>
                <a href="browse-listings.php">Browse Listings</a>
                <a href="messages.php">Messages</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <a href="browse-listings.php" class="back-link">← Back to listings</a>
        
        <div class="listing-container">
            <div class="listing-image">
                <?php if (!empty($listing['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" alt="Rice Image">
                <?php else: ?>
                    <div style="color: #999; font-size: 18px;">No image available</div>
                <?php endif; ?>
            </div>
            
            <div class="listing-details">
                <h1 class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></h1>
                <div class="listing-price">Rs. <?php echo number_format($listing['price_per_kg'], 2); ?> per kg</div>
                
                <div class="description">
                    <?php echo nl2br(htmlspecialchars($listing['description'])); ?>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Rice Type:</span> 
                    <?php echo htmlspecialchars($listing['rice_type']); ?>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Quantity Available:</span> 
                    <?php echo htmlspecialchars($listing['quantity']); ?> kg
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Harvest Date:</span> 
                    <?php echo date('F j, Y', strtotime($listing['harvest_date'])); ?>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Location:</span> 
                    <?php echo htmlspecialchars($listing['location']); ?>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Listed On:</span> 
                    <?php echo date('F j, Y', strtotime($listing['created_at'])); ?>
                </div>
                
                <div class="seller-info">
                    <h3>Seller Information</h3>
                    <div class="detail-item">
                        <span class="detail-label">Farmer Name:</span> 
                        <?php echo htmlspecialchars($listing['farmer_name']); ?>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Contact Phone:</span> 
                        <?php echo htmlspecialchars($listing['phone']); ?>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Email:</span> 
                        <a href="mailto:<?php echo htmlspecialchars($listing['email']); ?>">
                            <?php echo htmlspecialchars($listing['email']); ?>
                        </a>
                    </div>
                    
                    <?php if (!empty($listing['whatsapp'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">WhatsApp:</span> 
                        <?php echo htmlspecialchars($listing['whatsapp']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="action-buttons">
                    <!-- Bookmark/Unbookmark Button -->
                    <form method="POST" style="display: inline;">
                        <?php if ($is_bookmarked): ?>
                            <input type="hidden" name="bookmark_action" value="unbookmark">
                            <button type="submit" class="btn btn-outline">Remove Bookmark</button>
                        <?php else: ?>
                            <input type="hidden" name="bookmark_action" value="bookmark">
                            <button type="submit" class="btn btn-outline">Bookmark Listing</button>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Message Farmer Button -->
                    <a href="message-farmer.php?farmer_id=<?php echo $listing['farmer_id']; ?>&listing_id=<?php echo $listing['id']; ?>" class="btn">Message Farmer</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 