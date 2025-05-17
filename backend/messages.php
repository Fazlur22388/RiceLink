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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            gap: 20px;
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
        .sidebar {
            flex: 1;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 300px;
            height: calc(100vh - 130px);
            overflow-y: auto;
        }
        .chat-container {
            flex: 3;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            height: calc(100vh - 130px);
        }
        .conversation-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .conversation-item:hover {
            background-color: #f5f5f5;
        }
        .conversation-item.active {
            background-color: #e3f7ea;
            border-left: 4px solid rgb(22, 199, 96);
        }
        .conversation-item h3 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .conversation-meta {
            display: flex;
            justify-content: space-between;
            color: #777;
            font-size: 12px;
        }
        .unread-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: rgb(22, 199, 96);
            border-radius: 50%;
            margin-left: 5px;
        }
        .chat-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .farmer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e9e9e9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            font-weight: bold;
            margin-right: 15px;
            font-size: 20px;
        }
        .farmer-info h2 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .farmer-info p {
            margin: 0;
            color: #777;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
            position: relative;
            word-wrap: break-word;
        }
        .message-bubble.sent {
            background-color: rgb(22, 199, 96);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .message-bubble.received {
            background-color: #e9e9e9;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        .message-time {
            font-size: 11px;
            color: #777;
            margin-top: 5px;
            text-align: right;
        }
        .message-bubble.sent .message-time {
            color: rgba(255, 255, 255, 0.8);
        }
        .listing-reference {
            background-color: #f0f7ff;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 5px;
            font-size: 12px;
        }
        .chat-input {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .message-form {
            display: flex;
            width: 100%;
            gap: 10px;
        }
        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            resize: none;
        }
        .send-btn {
            background: rgb(22, 199, 96);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            min-width: 100px;
        }
        .no-conversation {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #777;
            text-align: center;
        }
        .no-conversation i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ddd;
        }
        .error-msg {
            color: red;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                max-width: 100%;
                height: auto;
                max-height: 300px;
            }
            .chat-container {
                height: 60vh;
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
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <h2>Conversations</h2>
            <?php if ($farmers_result->num_rows > 0): ?>
                <ul class="conversation-list">
                    <?php while ($farmer = $farmers_result->fetch_assoc()): ?>
                        <?php
                        // Check if there are unread messages
                        $unread_query = "SELECT COUNT(*) as count FROM messages 
                                         WHERE buyer_id = ? AND farmer_id = ? AND sent_by = 'farmer' AND is_read = FALSE";
                        $stmt = $conn->prepare($unread_query);
                        $stmt->bind_param("ii", $buyer_id, $farmer['id']);
                        $stmt->execute();
                        $unread_result = $stmt->get_result();
                        $unread = $unread_result->fetch_assoc();
                        $has_unread = ($unread['count'] > 0);
                        ?>
                        <li class="conversation-item <?php echo ($selected_farmer_id == $farmer['id']) ? 'active' : ''; ?>">
                            <a href="messages.php?farmer_id=<?php echo $farmer['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                                <h3>
                                    <?php echo htmlspecialchars($farmer['name']); ?>
                                    <?php if ($has_unread): ?>
                                        <span class="unread-indicator"></span>
                                    <?php endif; ?>
                                </h3>
                                <div class="conversation-meta">
                                    <span><?php echo htmlspecialchars($farmer['address']); ?></span>
                                    <span><?php echo date('M d', strtotime($farmer['last_message_time'])); ?></span>
                                </div>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No conversations yet. Browse listings and contact farmers to start a conversation.</p>
                <p><a href="browse-listings.php" class="btn">Browse Listings</a></p>
            <?php endif; ?>
        </div>

        <div class="chat-container">
            <?php if ($selected_farmer_id > 0 && isset($selected_farmer)): ?>
                <div class="chat-header">
                    <div class="farmer-avatar">
                        <?php echo strtoupper(substr($selected_farmer['name'], 0, 1)); ?>
                    </div>
                    <div class="farmer-info">
                        <h2><?php echo htmlspecialchars($selected_farmer['name']); ?></h2>
                        <p><?php echo htmlspecialchars($selected_farmer['address']); ?></p>
                    </div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($messages as $message): ?>
                        <div class="message-bubble <?php echo ($message['sent_by'] == 'buyer' && $message['buyer_id'] == $buyer_id) ? 'sent' : 'received'; ?>">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            
                            <?php if (!empty($message['listing_title'])): ?>
                                <div class="listing-reference">
                                    Re: <?php echo htmlspecialchars($message['listing_title']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="message-time">
                                <?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="chat-input">
                    <?php if (!empty($message_error)): ?>
                        <div class="error-msg"><?php echo $message_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" class="message-form">
                        <textarea name="message" class="message-input" placeholder="Type your message here..." rows="2" required></textarea>
                        <button type="submit" class="send-btn">Send</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="no-conversation">
                    <i>📬</i>
                    <h3>Select a conversation</h3>
                    <p>Choose a farmer from the sidebar to view your conversation history.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Scroll to bottom of chat
        window.onload = function() {
            var chatContainer = document.getElementById('chatMessages');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }
    </script>
</body>
</html> 