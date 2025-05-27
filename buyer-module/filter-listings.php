<?php
session_start();
include('../backend/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['buyer_id'])) {
    header("Location: login.php");
    exit();
}

// This page handles AJAX requests for filtering listings
// Return JSON response
header('Content-Type: application/json');

// Default values
$location = isset($_GET['location']) ? $_GET['location'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;
$rice_type = isset($_GET['rice_type']) ? $_GET['rice_type'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 12; // Number of listings per page

// Calculate offset for pagination
$offset = ($page - 1) * $items_per_page;

// Build query with filters
$query = "SELECT l.*, f.name as farmer_name, f.address as location 
          FROM listings l
          JOIN farmers f ON l.farmer_id = f.id
          WHERE l.price_per_kg >= ? AND l.price_per_kg <= ?";

$count_query = "SELECT COUNT(*) as total FROM listings l
                JOIN farmers f ON l.farmer_id = f.id
                WHERE l.price_per_kg >= ? AND l.price_per_kg <= ?";

$params = array($min_price, $max_price);
$types = "dd"; // double, double

if (!empty($location)) {
    $query .= " AND f.address LIKE ?";
    $count_query .= " AND f.address LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

if (!empty($rice_type)) {
    $query .= " AND l.rice_type = ?";
    $count_query .= " AND l.rice_type = ?";
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

// Add pagination
$query .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $items_per_page;
$types .= "ii";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$count_stmt = $conn->prepare($count_query);
$count_types = substr($types, 0, -2); // Remove the last two characters (ii for LIMIT params)
$count_params = array_slice($params, 0, -2); // Remove the last two elements (offset and limit)
$count_stmt->bind_param($count_types, ...$count_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_items = $count_row['total'];
$total_pages = ceil($total_items / $items_per_page);

// Prepare response
$listings = array();
while ($row = $result->fetch_assoc()) {
    // Format data for JSON response
    $listings[] = array(
        'id' => $row['id'],
        'title' => $row['title'],
        'rice_type' => $row['rice_type'],
        'price_per_kg' => $row['price_per_kg'],
        'quantity' => $row['quantity'],
        'farmer_name' => $row['farmer_name'],
        'location' => $row['location'],
        'created_at' => $row['created_at'],
        'image_url' => $row['image_url'],
        'description' => substr($row['description'], 0, 100) . '...'
    );
}

// Return JSON response
echo json_encode(array(
    'listings' => $listings,
    'pagination' => array(
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_items' => $total_items,
        'items_per_page' => $items_per_page
    ),
    'filters' => array(
        'location' => $location,
        'min_price' => $min_price,
        'max_price' => $max_price,
        'rice_type' => $rice_type,
        'sort_by' => $sort_by
    )
));

$conn->close();
?> 