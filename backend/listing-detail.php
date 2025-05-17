<?php
session_start();
include('db_connect.php');

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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 90%;
            max-width: 1200px;
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
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: rgb(22, 199, 96);
            text-decoration: none;
        }
        .listing-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .listing-image {
            flex: 1;
            min-width: 300px;
            max-width: 500px;
            background-color: #e9e9e9;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 350px;
        }
        .listing-image img {
            max-width: 100%;
            max-height: 100%;
        }
        .listing-details {
            flex: 1;
            min-width: 300px;
        }
        .listing-title {
            font-size: 24px;
            margin: 0 0 15px 0;
            color: rgb(22, 199, 96);
        }
        .listing-price {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .detail-item {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .seller-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .btn {
            background: rgb(22, 199, 96);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            font-size: 16px;
        }
        .btn-outline {
            background: white;
            color: rgb(22, 199, 96);
            border: 1px solid rgb(22, 199, 96);
        }
        .btn-danger {
            background: #ff6b6b;
        }
        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .description {
            margin: 20px 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>RiceLink</h1>
            <div class="nav-links">
                <a href="buyer-dashboard.php">Dashboard</a>
                <a href="browse-listings.php">Browse Listings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <a href="browse-listings.php" class="back-link">← Back to listings</a>
        
        <div class="listing-container">
            <div class="listing-image">
                <?php if (!empty($listing['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" alt="Rice">
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
                        <span class="detail-label">Phone:</span> 
                        <?php echo htmlspecialchars($listing['phone']); ?>
                    </div>
                    
                    <?php if (!empty($listing['whatsapp'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">WhatsApp:</span> 
                        <?php echo htmlspecialchars($listing['whatsapp']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <span class="detail-label">Email:</span> 
                        <?php echo htmlspecialchars($listing['email']); ?>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="message-farmer.php?farmer_id=<?php echo $listing['farmer_id']; ?>&listing_id=<?php echo $listing_id; ?>" class="btn">Contact Farmer</a>
                    
                    <form method="post" style="display: inline;">
                        <?php if ($is_bookmarked): ?>
                            <input type="hidden" name="bookmark_action" value="unbookmark">
                            <button type="submit" class="btn btn-danger">Remove Bookmark</button>
                        <?php else: ?>
                            <input type="hidden" name="bookmark_action" value="bookmark">
                            <button type="submit" class="btn btn-outline">Bookmark Listing</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 