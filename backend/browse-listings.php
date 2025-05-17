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
        .filters-container {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filters-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .listing-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .listing-card:hover {
            transform: translateY(-5px);
        }
        .listing-image {
            height: 200px;
            background-color: #e9e9e9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .listing-image img {
            max-width: 100%;
            max-height: 100%;
        }
        .listing-details {
            padding: 15px;
        }
        .listing-title {
            font-size: 18px;
            margin: 0 0 10px 0;
            color: rgb(22, 199, 96);
        }
        .listing-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .listing-price {
            font-weight: bold;
            font-size: 18px;
        }
        .listing-location {
            color: #666;
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
        }
        .filter-btn {
            min-width: 120px;
        }
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>RiceLink</h1>
            <div class="nav-links">
                <a href="buyer-dashboard.php">Dashboard</a>
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
                                <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" alt="Rice">
                            <?php else: ?>
                                <div style="color: #999;">Rice Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="listing-details">
                            <h3 class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></h3>
                            <div class="listing-meta">
                                <span class="listing-price">Rs. <?php echo number_format($listing['price_per_kg'], 2); ?>/kg</span>
                                <span class="listing-location"><?php echo htmlspecialchars($listing['location']); ?></span>
                            </div>
                            <p>Type: <?php echo htmlspecialchars($listing['rice_type']); ?></p>
                            <p>Quantity: <?php echo htmlspecialchars($listing['quantity']); ?> kg</p>
                            <p>Seller: <?php echo htmlspecialchars($listing['farmer_name']); ?></p>
                            <a href="listing-detail.php?id=<?php echo $listing['id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <h3>No listings found</h3>
                    <p>Try adjusting your filters to see more results.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 