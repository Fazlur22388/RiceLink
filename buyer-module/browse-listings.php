<?php
session_start();
include('db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['buyer_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$location = isset($_GET['location']) ? $_GET['location'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;
$rice_type = isset($_GET['rice_type']) ? $_GET['rice_type'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query with filters
$query = "SELECT l.*, f.name as farmer_name, f.address as location 
          FROM listings l
          JOIN farmers f ON l.farmer_id = f.id
          WHERE l.price_per_kg >= ? AND l.price_per_kg <= ?";

$params = array($min_price, $max_price);
$types = "dd"; // double, double

if (!empty($location)) {
    $query .= " AND f.address LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

if (!empty($rice_type)) {
    $query .= " AND l.rice_type = ?";
    $params[] = $rice_type;
    $types .= "s";
}

// Add sorting
switch ($sort_by) {
    case 'price_low':
        $query .= " ORDER BY l.price_per_kg ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY l.price_per_kg DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY l.created_at ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY l.created_at DESC";
        break;
}

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get rice types for filter dropdown
$rice_types_query = "SELECT DISTINCT rice_type FROM listings ORDER BY rice_type";
$rice_types_result = $conn->query($rice_types_query);

// Get locations for filter dropdown
$locations_query = "SELECT DISTINCT address FROM farmers ORDER BY address";
$locations_result = $conn->query($locations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Rice Listings - RiceLink</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>RiceLink</h1>
            <div class="nav-links">
                <a href="buyer-dashboard.php">Dashboard</a>
                <a href="messages.php">Messages</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h2>Browse Rice Listings</h2>
        
        <div class="filters-container">
            <form class="filters-form" method="GET" action="">
                <div class="filter-group">
                    <label for="location">Location</label>
                    <select name="location" id="location">
                        <option value="">All Locations</option>
                        <?php while ($loc = $locations_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($loc['address']); ?>" 
                                <?php echo ($location == $loc['address']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc['address']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="rice_type">Rice Type</label>
                    <select name="rice_type" id="rice_type">
                        <option value="">All Types</option>
                        <?php while ($type = $rice_types_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($type['rice_type']); ?>"
                                <?php echo ($rice_type == $type['rice_type']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['rice_type']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="min_price">Min Price (Rs/kg)</label>
                    <input type="number" name="min_price" id="min_price" value="<?php echo $min_price; ?>" min="0">
                </div>
                
                <div class="filter-group">
                    <label for="max_price">Max Price (Rs/kg)</label>
                    <input type="number" name="max_price" id="max_price" value="<?php echo $max_price; ?>" min="0">
                </div>
                
                <div class="filter-group">
                    <label for="sort">Sort By</label>
                    <select name="sort" id="sort">
                        <option value="newest" <?php echo ($sort_by == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo ($sort_by == 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_low" <?php echo ($sort_by == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo ($sort_by == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn filter-btn">Apply Filters</button>
                </div>
            </form>
        </div>
        
        <div class="listings-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($listing = $result->fetch_assoc()): ?>
                    <div class="listing-card">
                        <div class="listing-image">
                            <?php if (!empty($listing['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" alt="Rice Image">
                            <?php else: ?>
                                <div style="color: #999; font-size: 18px;">No image available</div>
                            <?php endif; ?>
                        </div>
                        <div class="listing-details">
                            <h3 class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></h3>
                            <div class="listing-meta">
                                <div class="listing-price">Rs. <?php echo number_format($listing['price_per_kg'], 2); ?>/kg</div>
                                <div class="listing-location"><?php echo htmlspecialchars($listing['location']); ?></div>
                            </div>
                            <p>Type: <?php echo htmlspecialchars($listing['rice_type']); ?></p>
                            <p>Available: <?php echo htmlspecialchars($listing['quantity']); ?> kg</p>
                            <p>
                                <a href="listing-detail.php?id=<?php echo $listing['id']; ?>" class="btn">View Details</a>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <h3>No listings found matching your criteria</h3>
                    <p>Try adjusting your filters or check back later for new listings.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 