<?php
session_start();
include('db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['buyer_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['buyer_id'];

// Check if both farmer_id and listing_id are provided
if (!isset($_GET['farmer_id']) || empty($_GET['farmer_id'])) {
    header("Location: browse-listings.php");
    exit();
}

$farmer_id = (int)$_GET['farmer_id'];
$listing_id = isset($_GET['listing_id']) ? (int)$_GET['listing_id'] : 0;

// Get buyer information
$buyer_query = "SELECT * FROM buyers WHERE id = ?";
$stmt = $conn->prepare($buyer_query);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$buyer_result = $stmt->get_result();
$buyer = $buyer_result->fetch_assoc();

// Get farmer information
$farmer_query = "SELECT * FROM farmers WHERE id = ?";
$stmt = $conn->prepare($farmer_query);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$farmer_result = $stmt->get_result();

if ($farmer_result->num_rows == 0) {
    header("Location: browse-listings.php");
    exit();
}

$farmer = $farmer_result->fetch_assoc();

// Get listing information if listing_id is provided
$listing = null;
if ($listing_id > 0) {
    $listing_query = "SELECT * FROM listings WHERE id = ? AND farmer_id = ?";
    $stmt = $conn->prepare($listing_query);
    $stmt->bind_param("ii", $listing_id, $farmer_id);
    $stmt->execute();
    $listing_result = $stmt->get_result();
    if ($listing_result->num_rows > 0) {
        $listing = $listing_result->fetch_assoc();
    }
}

// Get conversation history
$messages_query = "SELECT * FROM messages 
                  WHERE (buyer_id = ? AND farmer_id = ?) OR (buyer_id = ? AND farmer_id = ?)
                  ORDER BY created_at ASC";
$stmt = $conn->prepare($messages_query);
$stmt->bind_param("iiii", $buyer_id, $farmer_id, $farmer_id, $buyer_id);
$stmt->execute();
$messages_result = $stmt->get_result();

// Process message submission
$message_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message_text = trim($_POST['message']);
    
    if (empty($message_text)) {
        $message_error = "Please enter a message.";
    } else {
        // Insert message
        $insert_message = "INSERT INTO messages (buyer_id, farmer_id, listing_id, message, sent_by, created_at) 
                          VALUES (?, ?, ?, ?, 'buyer', NOW())";
        $stmt = $conn->prepare($insert_message);
        $stmt->bind_param("iiis", $buyer_id, $farmer_id, $listing_id, $message_text);
        
        if ($stmt->execute()) {
            // Redirect to avoid form resubmission
            header("Location: message-farmer.php?farmer_id=" . $farmer_id . "&listing_id=" . $listing_id);
            exit();
        } else {
            $message_error = "Failed to send message. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Farmer - RiceLink</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 90%;
            max-width: 900px;
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
        .message-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .farmer-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .farmer-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #e9e9e9;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 24px;
        }
        .farmer-details h2 {
            margin: 0 0 5px 0;
            color: rgb(22, 199, 96);
        }
        .conversation {
            margin-bottom: 20px;
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 8px;
            max-width: 80%;
        }
        .message.sent {
            background-color: rgb(22, 199, 96);
            color: white;
            margin-left: auto;
        }
        .message.received {
            background-color: #e9e9e9;
            margin-right: auto;
        }
        .message-time {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
            text-align: right;
        }
        .message.sent .message-time {
            color: rgba(255, 255, 255, 0.8);
        }
        .message-form {
            display: flex;
            gap: 10px;
        }
        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            background: rgb(22, 199, 96);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .listing-reference {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .listing-reference h3 {
            margin-top: 0;
            color: rgb(22, 199, 96);
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .no-messages {
            text-align: center;
            color: #777;
            padding: 20px;
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
        <a href="<?php echo $listing ? 'listing-detail.php?id=' . $listing_id : 'browse-listings.php'; ?>" class="back-link">← Back</a>
        
        <div class="message-container">
            <div class="farmer-info">
                <div class="farmer-photo">
                    <?php echo strtoupper(substr($farmer['name'], 0, 1)); ?>
                </div>
                <div class="farmer-details">
                    <h2><?php echo htmlspecialchars($farmer['name']); ?></h2>
                    <p><?php echo htmlspecialchars($farmer['address']); ?></p>
                </div>
            </div>
            
            <?php if ($listing): ?>
            <div class="listing-reference">
                <h3>Regarding: <?php echo htmlspecialchars($listing['title']); ?></h3>
                <p>Type: <?php echo htmlspecialchars($listing['rice_type']); ?></p>
                <p>Price: Rs. <?php echo number_format($listing['price_per_kg'], 2); ?> per kg</p>
            </div>
            <?php endif; ?>
            
            <div class="conversation">
                <?php if ($messages_result->num_rows > 0): ?>
                    <?php while ($message = $messages_result->fetch_assoc()): ?>
                        <div class="message <?php echo ($message['sent_by'] == 'buyer' && $message['buyer_id'] == $buyer_id) ? 'sent' : 'received'; ?>">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            <div class="message-time"><?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-messages">
                        <p>Start a conversation with <?php echo htmlspecialchars($farmer['name']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($message_error)): ?>
                <div class="error"><?php echo $message_error; ?></div>
            <?php endif; ?>
            
            <form method="post" class="message-form">
                <textarea name="message" class="message-input" placeholder="Type your message here..." rows="3" required></textarea>
                <button type="submit" class="btn">Send</button>
            </form>
        </div>
    </div>
</body>
</html> 