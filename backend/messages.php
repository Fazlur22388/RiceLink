<?php
session_start();
include('db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['buyer_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['buyer_id'];

// Get buyer information
$buyer_query = "SELECT * FROM buyers WHERE id = ?";
$stmt = $conn->prepare($buyer_query);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$buyer_result = $stmt->get_result();
$buyer = $buyer_result->fetch_assoc();

// Get all farmers the buyer has communicated with
$farmers_query = "SELECT DISTINCT f.id, f.name, f.address, 
                 (SELECT created_at FROM messages 
                  WHERE (buyer_id = ? AND farmer_id = f.id) 
                  ORDER BY created_at DESC LIMIT 1) as last_message_time
                FROM farmers f
                JOIN messages m ON f.id = m.farmer_id
                WHERE m.buyer_id = ?
                ORDER BY last_message_time DESC";
$stmt = $conn->prepare($farmers_query);
$stmt->bind_param("ii", $buyer_id, $buyer_id);
$stmt->execute();
$farmers_result = $stmt->get_result();

// Get farmer_id from URL if viewing a specific conversation
$selected_farmer_id = isset($_GET['farmer_id']) ? (int)$_GET['farmer_id'] : 0;

// If a farmer is selected, get the conversation
$messages = [];
if ($selected_farmer_id > 0) {
    // Get farmer information
    $farmer_query = "SELECT * FROM farmers WHERE id = ?";
    $stmt = $conn->prepare($farmer_query);
    $stmt->bind_param("i", $selected_farmer_id);
    $stmt->execute();
    $farmer_result = $stmt->get_result();
    
    if ($farmer_result->num_rows > 0) {
        $selected_farmer = $farmer_result->fetch_assoc();
        
        // Get messages
        $messages_query = "SELECT m.*, l.title as listing_title 
                          FROM messages m
                          LEFT JOIN listings l ON m.listing_id = l.id
                          WHERE (m.buyer_id = ? AND m.farmer_id = ?) 
                          OR (m.buyer_id = ? AND m.farmer_id = ?)
                          ORDER BY m.created_at ASC";
        $stmt = $conn->prepare($messages_query);
        $stmt->bind_param("iiii", $buyer_id, $selected_farmer_id, $selected_farmer_id, $buyer_id);
        $stmt->execute();
        $messages_result = $stmt->get_result();
        
        while ($message = $messages_result->fetch_assoc()) {
            $messages[] = $message;
        }
        
        // Mark unread messages as read
        $update_read = "UPDATE messages SET is_read = TRUE 
                       WHERE buyer_id = ? AND farmer_id = ? AND sent_by = 'farmer' AND is_read = FALSE";
        $stmt = $conn->prepare($update_read);
        $stmt->bind_param("ii", $buyer_id, $selected_farmer_id);
        $stmt->execute();
    }
}

// Process message submission
$message_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message']) && $selected_farmer_id > 0) {
    $message_text = trim($_POST['message']);
    $listing_id = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : null;
    
    if (empty($message_text)) {
        $message_error = "Please enter a message.";
    } else {
        // Insert message
        $insert_message = "INSERT INTO messages (buyer_id, farmer_id, listing_id, message, sent_by, created_at) 
                          VALUES (?, ?, ?, ?, 'buyer', NOW())";
        $stmt = $conn->prepare($insert_message);
        $stmt->bind_param("iiis", $buyer_id, $selected_farmer_id, $listing_id, $message_text);
        
        if ($stmt->execute()) {
            // Redirect to avoid form resubmission
            header("Location: messages.php?farmer_id=" . $selected_farmer_id);
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
    <title>Messages - RiceLink</title>
    <link rel="stylesheet" href="css/style.css">
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
        <div class="message-container">
            <div class="conversation-list">
                <?php if ($farmers_result->num_rows > 0): ?>
                    <?php while ($farmer = $farmers_result->fetch_assoc()): ?>
                        <?php
                        // Check if there are unread messages from this farmer
                        $unread_query = "SELECT COUNT(*) as count FROM messages 
                                        WHERE buyer_id = ? AND farmer_id = ? AND sent_by = 'farmer' AND is_read = FALSE";
                        $stmt = $conn->prepare($unread_query);
                        $stmt->bind_param("ii", $buyer_id, $farmer['id']);
                        $stmt->execute();
                        $unread_result = $stmt->get_result();
                        $unread_count = $unread_result->fetch_assoc()['count'];
                        
                        // Get latest message preview
                        $preview_query = "SELECT message FROM messages 
                                        WHERE (buyer_id = ? AND farmer_id = ?) OR (buyer_id = ? AND farmer_id = ?)
                                        ORDER BY created_at DESC LIMIT 1";
                        $stmt = $conn->prepare($preview_query);
                        $stmt->bind_param("iiii", $buyer_id, $farmer['id'], $farmer['id'], $buyer_id);
                        $stmt->execute();
                        $preview_result = $stmt->get_result();
                        $message_preview = $preview_result->fetch_assoc()['message'];
                        $message_preview = strlen($message_preview) > 30 ? substr($message_preview, 0, 30) . '...' : $message_preview;
                        ?>
                        <a href="messages.php?farmer_id=<?php echo $farmer['id']; ?>" class="conversation <?php echo ($selected_farmer_id == $farmer['id']) ? 'active' : ''; ?>">
                            <div class="conversation-name">
                                <?php echo htmlspecialchars($farmer['name']); ?>
                                <?php if ($unread_count > 0): ?>
                                    <span style="background-color: var(--primary-color); color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; margin-left: 5px;">
                                        <?php echo $unread_count; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="conversation-preview"><?php echo htmlspecialchars($message_preview); ?></div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: var(--text-light);">
                        <p>No conversations yet.</p>
                        <p>Start by browsing listings and messaging farmers.</p>
                        <a href="browse-listings.php" class="btn" style="margin-top: 10px;">Browse Listings</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="chat-area">
                <?php if ($selected_farmer_id > 0 && isset($selected_farmer)): ?>
                    <div class="chat-header">
                        <h2><?php echo htmlspecialchars($selected_farmer['name']); ?></h2>
                        <p><?php echo htmlspecialchars($selected_farmer['address']); ?></p>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <?php if (count($messages) > 0): ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?php echo ($message['sent_by'] == 'buyer') ? 'message-sent' : 'message-received'; ?>">
                                    <?php if ($message['listing_id'] && $message['listing_title']): ?>
                                        <div style="font-size: 12px; margin-bottom: 5px; <?php echo ($message['sent_by'] == 'buyer') ? 'text-align: right;' : ''; ?>">
                                            <a href="listing-detail.php?id=<?php echo $message['listing_id']; ?>" target="_blank" style="color: var(--primary-color);">
                                                Re: <?php echo htmlspecialchars($message['listing_title']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                    
                                    <div class="message-time">
                                        <?php echo date('M d, g:i a', strtotime($message['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px; color: var(--text-light);">
                                <p>No messages yet. Start the conversation!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="chat-input">
                        <form method="POST" action="messages.php?farmer_id=<?php echo $selected_farmer_id; ?>">
                            <textarea name="message" placeholder="Type your message here..." required></textarea>
                            <button type="submit" class="btn">Send</button>
                            <?php if ($message_error): ?>
                                <div class="error-msg"><?php echo $message_error; ?></div>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; color: var(--text-light);">
                        <p style="font-size: 18px; margin-bottom: 20px;">Select a conversation to view messages</p>
                        <?php if ($farmers_result->num_rows == 0): ?>
                            <a href="browse-listings.php" class="btn">Browse Listings</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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