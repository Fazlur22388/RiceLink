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
$listing_query = "SELECT l.*, f.username as farmer_name, f.address as location, f.phone, f.whatsapp, f.email 
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
        /* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f8f9fa;
}

/* Header Styles */
.header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.nav-links {
    display: flex;
    gap: 2rem;
}

.nav-links a {
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.nav-links a:hover {
    background-color: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

/* Back Link */
.back-link {
    display: inline-block;
    color: #2c5530;
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 2rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.back-link:hover {
    background-color: #e8f5e8;
    transform: translateX(-5px);
}

/* Listing Container */
.listing-container {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

/* Listing Image */
.listing-image {
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    position: relative;
}

.listing-image img {
   margin-top: 10px
    
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.listing-image img:hover {
    transform: scale(1.05);
}

/* Listing Details */
.listing-details {
    padding: 2rem;
}

.listing-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c5530;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.listing-price {
    font-size: 2rem;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 1.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #fff5f5, #ffe8e8);
    border-radius: 8px;
    display: inline-block;
}

.description {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #555;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #4a7c59;
}

/* Detail Items */
.detail-item {
    display: flex;
    margin-bottom: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.detail-label {
    font-weight: 600;
    color: #2c5530;
    width: 180px;
    flex-shrink: 0;
}

.detail-item span:last-child,
.detail-item a {
    color: #555;
    flex: 1;
}

.detail-item a {
    color: #4a7c59;
    text-decoration: none;
    transition: color 0.3s ease;
}

.detail-item a:hover {
    color: #2c5530;
    text-decoration: underline;
}

/* Seller Info */
.seller-info {
    background: linear-gradient(135deg, #f8f9fa, #e8f5e8);
    padding: 2rem;
    border-radius: 12px;
    margin: 2rem 0;
    border: 1px solid #e0e0e0;
}

.seller-info h3 {
    color: #2c5530;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #4a7c59;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.btn {
    display: inline-block;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    min-width: 160px;
}

.btn {
    background: linear-gradient(135deg, #4a7c59, #2c5530);
    color: white;
    box-shadow: 0 4px 15px rgba(76, 124, 89, 0.3);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 124, 89, 0.4);
}

.btn-outline {
    background: white;
    color: #4a7c59;
    border: 2px solid #4a7c59;
    box-shadow: 0 4px 15px rgba(76, 124, 89, 0.1);
}

.btn-outline:hover {
    background: #4a7c59;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 124, 89, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .nav-links {
        flex-wrap: wrap;
        justify-content: center;
        gap: 1rem;
    }
    
    .container {
        padding: 1rem;
    }
    
    .listing-container {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .listing-image {
        min-height: 300px;
    }
    
    .listing-details {
        padding: 1.5rem;
    }
    
    .listing-title {
        font-size: 2rem;
    }
    
    .listing-price {
        font-size: 1.5rem;
    }
    
    .detail-item {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .detail-label {
        width: auto;
        font-size: 0.9rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

@media (max-width: 480px) {
    .header h1 {
        font-size: 1.5rem;
    }
    
    .listing-title {
        font-size: 1.5rem;
    }
    
    .listing-price {
        font-size: 1.2rem;
    }
    
    .seller-info {
        padding: 1rem;
    }
    
    .btn {
        padding: 0.8rem 1.5rem;
        font-size: 1rem;
    }
}

/* Animation for smooth transitions */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.listing-container {
    animation: fadeIn 0.6s ease-out;
}

/* Hover effects for better interactivity */
.listing-container:hover {
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

/* Focus styles for accessibility */
.btn:focus,
.back-link:focus,
.nav-links a:focus {
    outline: 3px solid #4a7c59;
    outline-offset: 2px;
}

/* Print styles */
@media print {
    .header,
    .action-buttons,
    .back-link {
        display: none;
    }
    
    .listing-container {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    body {
        background: white;
    }
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
                    <img src="../<?php echo htmlspecialchars($listing['image_url']); ?>" alt="Rice Image">
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