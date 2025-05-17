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
        <a href="<?php echo $listing_id ? 'listing-detail.php?id=' . $listing_id : 'messages.php'; ?>" class="back-link">
            ← Back to <?php echo $listing_id ? 'listing' : 'messages'; ?>
        </a>
        
        <div class="card">
            <div class="card-header">
                <h2>Message to <?php echo htmlspecialchars($farmer['name']); ?></h2>
                <?php if ($listing): ?>
                    <p>Regarding: <a href="listing-detail.php?id=<?php echo $listing['id']; ?>"><?php echo htmlspecialchars($listing['title']); ?></a></p>
                <?php endif; ?>
            </div>
            
            <div class="card-body">
                <div class="message-container" style="box-shadow: none; padding: 0; height: auto;">
                    <div class="chat-messages" id="chat-messages" style="max-height: 400px; margin-bottom: 20px; background-color: #f5f7f9;">
                        <?php if ($messages_result->num_rows > 0): ?>
                            <?php while ($message = $messages_result->fetch_assoc()): ?>
                                <div class="message <?php echo ($message['sent_by'] == 'buyer') ? 'message-sent' : 'message-received'; ?>">
                                    <?php if ($message['listing_id']): ?>
                                        <?php 
                                        // Get listing title
                                        $list_query = "SELECT title FROM listings WHERE id = ?";
                                        $stmt = $conn->prepare($list_query);
                                        $stmt->bind_param("i", $message['listing_id']);
                                        $stmt->execute();
                                        $list_result = $stmt->get_result();
                                        $list_data = $list_result->fetch_assoc();
                                        ?>
                                        <?php if ($list_data): ?>
                                            <div style="font-size: 12px; margin-bottom: 5px; <?php echo ($message['sent_by'] == 'buyer') ? 'text-align: right;' : ''; ?>">
                                                <a href="listing-detail.php?id=<?php echo $message['listing_id']; ?>" style="color: var(--primary-color);">
                                                    Re: <?php echo htmlspecialchars($list_data['title']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                    
                                    <div class="message-time">
                                        <?php echo date('M d, g:i a', strtotime($message['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px; color: var(--text-light);">
                                <p>No previous messages. Start the conversation!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="chat-input" style="background-color: white; border-radius: 0 0 var(--border-radius) var(--border-radius);">
                        <form method="POST" action="">
                            <textarea name="message" placeholder="Type your message here..." required></textarea>
                            <button type="submit" class="btn">Send</button>
                            
                            <?php if ($message_error): ?>
                                <div class="error-msg" style="margin-top: 10px;"><?php echo $message_error; ?></div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3>Farmer Contact Information</h3>
            </div>
            <div class="card-body">
                <div class="detail-item">
                    <span class="detail-label">Name:</span> 
                    <?php echo htmlspecialchars($farmer['name']); ?>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Location:</span> 
                    <?php echo htmlspecialchars($farmer['address']); ?>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Phone:</span> 
                    <?php echo htmlspecialchars($farmer['phone']); ?>
                </div>
                
                <?php if (!empty($farmer['whatsapp'])): ?>
                <div class="detail-item">
                    <span class="detail-label">WhatsApp:</span> 
                    <?php echo htmlspecialchars($farmer['whatsapp']); ?>
                </div>
                <?php endif; ?>
                
                <div class="detail-item">
                    <span class="detail-label">Email:</span> 
                    <a href="mailto:<?php echo htmlspecialchars($farmer['email']); ?>">
                        <?php echo htmlspecialchars($farmer['email']); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Scroll to bottom of chat on page load
        window.onload = function() {
            var chatMessages = document.getElementById('chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        };
    </script>
</body>
</html> 