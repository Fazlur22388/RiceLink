<?php
session_start();
include("db_connect.php");

$user_id = $_SESSION['farmer_id'];

$query_user = "SELECT name, profile_picture FROM farmers WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);

$name = $user['name'];
$profile_picture = $user['profile_picture'];

$query_posts = "SELECT * FROM rice_posts WHERE farmer_id = $user_id ORDER BY id DESC";
$posts_result = mysqli_query($conn, $query_posts);

$query_farmers = "SELECT id, name, profile_picture FROM farmers WHERE id != $user_id";
$other_farmers_result = mysqli_query($conn, $query_farmers);

// Fetch WhatsApp messages for this farmer (simulate from a table `messages`)
$query_messages = "SELECT m.content, m.timestamp, f.name AS sender_name, f.profile_picture AS sender_pic, f.phone 
                   FROM messages m
                   JOIN farmers f ON m.sender_id = f.id
                   WHERE m.receiver_id = $user_id
                   ORDER BY m.timestamp DESC";
$messages_result = mysqli_query($conn, $query_messages);

$query_active_posts = "SELECT COUNT(*) AS total FROM rice_posts WHERE farmer_id = $user_id";
$query_buyers = "SELECT COUNT(DISTINCT buyer_id) AS total FROM inquiries WHERE farmer_id = $user_id";
$query_views = "SELECT SUM(views) AS total FROM rice_posts WHERE farmer_id = $user_id";

$active_posts_count = mysqli_fetch_assoc(mysqli_query($conn, $query_active_posts))['total'];
$total_buyers = mysqli_fetch_assoc(mysqli_query($conn, $query_buyers))['total'];
$total_views = mysqli_fetch_assoc(mysqli_query($conn, $query_views))['total'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>RiceLink | Farmer Dashboard</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Segoe UI", sans-serif;
      background-color: #f4f6f8;
      color: #333;
      display: flex;
    }

    .sidebar {
      width: 250px;
      background-color: #1d4d2d;
      color: white;
      padding: 25px 20px;
      height: 100vh;
      position: fixed;
    }

    .sidebar h2 {
      margin-bottom: 30px;
      font-family: "Trebuchet MS", sans-serif;
      font-weight: lighter;
    }

    nav ul {
      list-style: none;
      margin-top: 80px;
    }

    nav ul li a,
    .btn-logout,
    .dlt-acc {
      display: block;
      padding: 12px;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      transition: 0.3s;
    }

    nav ul li a:hover,
    .btn-logout:hover {
      background-color: #216445;
    }

    .btn-logout {
      margin-top: 300px;
      text-align: center;
      border: 1px solid #f4f6f8;
      border-radius: 25px;
    }

    .btn-logout:hover {
      color: #1d4d2d;
      background-color: #f4f6f8;
    }

    .dlt-acc {
      font-size: 13px;
      margin-left: 60px;
      margin-top: 10px;
    }

    .dlt-acc:hover {
      text-decoration: underline;
    }

    .main-content {
      margin-left: 250px;
      flex: 1;
      padding: 30px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h1 {
      color: #1d4d2d;
      font-family: "Trebuchet MS", sans-serif;
      font-weight: lighter;
    }

    .header img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
    }

    .stats {
      display: flex;
      gap: 30px;
      margin: 30px 0;
    }

    .stat-box {
      background: #fff7e6;
      border-left: 5px solid #f0d5a4;
      border-radius: 20px;
      padding: 15px 20px;
      flex: 1;
    }

    .stat-box h3 {
      font-size: 18px;
      margin-bottom: 5px;
      color: #1d4d2d;
      font-family: "Trebuchet MS", sans-serif;
    }

    .btn-add {
      background-color: #f4f6f8;
      color: #1d4d2d;
      padding: 12px 20px;
      border: 1px solid #1d4d2d;
      font-size: 16px;
      border-radius: 25px;
      cursor: pointer;
      margin-bottom: 20px;
      width: 500px;
    }

    .btn-add:hover {
      background-color: #216445;
      color: #f4f6f8;
    }

    .section {
      margin-bottom: 30px;
    }

    .section h2 {
      font-family: "Trebuchet MS", sans-serif;
      font-weight: lighter;
      margin-bottom: 15px;
      color: #1d4d2d;
    }

    .post-grid {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .post {
      background: #ffffff;
      border-radius: 10px;
      padding: 15px;
      width: 200px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .post img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 5px;
    }

    .actions {
      margin-top: 10px;
    }

    .actions a {
      text-decoration: none;
      color: #1d4d2d;
      margin-right: 10px;
    }

    .flex-sections {
      display: flex;
      gap: 40px;
      flex-wrap: wrap;
      margin: 30px 0;
    }

    .farmers-section,
    .message-section {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
      flex: 1;
      min-width: 300px;
    }

    .farmer-list {
      display: flex;
      gap: 15px;
      overflow-x: auto;
      padding-bottom: 10px;
    }

    .farmer-card {
      width: 100px;
      text-align: center;
      background: #f8f8f8;
      border-radius: 10px;
      padding: 10px;
      transition: transform 0.2s ease;
      cursor: pointer;
    }

    .farmer-card:hover {
      transform: scale(1.05);
    }

    .farmer-card img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 8px;
    }

    .message-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
      max-height: 300px;
      overflow-y: auto;
    }

    .message-card {
      background-color: #fcfcfc;
      border-radius: 10px;
      padding: 15px;
      border-left: 4px solid #1d4d2d;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }

    .msg-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
    }

    .msg-header img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
    }

    .msg-content {
      font-size: 14px;
      margin-bottom: 10px;
    }

    .whatsapp-btn {
      background: #25d366;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 13px;
    }
  </style>
</head>

<body>
  <div class="sidebar">
    <h2>RiceLink</h2>
    <nav>
      <ul>
        <li><a href="farmer_dashboard.html">Dashboard</a></li>
        <li><a href="add_posts.php">Add New Rice Posts</a></li>
        <li><a href="farmer_profile.php">Farmer Profile</a></li>
        <li><a href="#whatsapp center">Message Center</a></li>
        <li><a href="edit_profile.php">Edit Profile</a></li>
      </ul>
    </nav>
    <a href="logout.php" class="btn-logout">Logout</a>
    <a href="delete_account.php" class="dlt-acc">Delete Account</a>
  </div>

  <main class="main-content">
    <div class="header">
      <h1>Welcome, <?= htmlspecialchars($name) ?></h1>
      <img src="uploads/<?= htmlspecialchars($profile_picture) ?>" alt="Profile" />
    </div>

    <div class="stats">
      <div class="stat-box">
        <h3>Active Posts</h3>
        <p><?= $active_posts_count ?></p>
      </div>
      <div class="stat-box">
        <h3>Interested Buyers</h3>
        <p><?= $total_buyers ?></p>
      </div>
      <div class="stat-box">
        <h3>Views</h3>
        <p><?= $total_views ?></p>
      </div>
    </div>

    <form method="get" action="add_posts.php">
      <button class="btn-add" type="submit">+ Add New Rice Product</button>
    </form>

    <section class="section">
      <h2>My Posts</h2>
      <div class="post-grid">
        <?php while ($post = mysqli_fetch_assoc($posts_result)) { ?>
          <div class="post">
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Rice" />
            <h3><?= htmlspecialchars($post['type']) ?></h3>
            <p><?= htmlspecialchars($post['quantity_kg']) ?> kg</p>
            <p>Rs <?= number_format($post['price'], 2) ?></p>
            <div class="actions">
              <a href="edit_post.php?id=<?= $post['id'] ?>">Edit</a>
              <a href="delete_post.php?id=<?= $post['id'] ?>"
                onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
            </div>
          </div>
        <?php } ?>
      </div>
    </section>

    <div class="flex-sections">
      <section class="section farmers-section">
        <h2>Other Farmers</h2>
        <div class="farmer-list">
          <?php while ($farmer = mysqli_fetch_assoc($other_farmers_result)) { ?>
            <div class="farmer-card" onclick="window.location.href='view_farmer_posts.php?id=<?= $farmer['id'] ?>'">
              <img src="uploads/<?= htmlspecialchars($farmer['profile_picture']) ?>" alt="<?= htmlspecialchars($farmer['name']) ?>" />
              <p><?= htmlspecialchars($farmer['name']) ?></p>
            </div>
          <?php } ?>
        </div>
      </section>

      <section class="section message-section">
        <h2>Message Center</h2>
        <div class="message-list">
          <?php while ($msg = mysqli_fetch_assoc($messages_result)) { ?>
            <div class="message-card">
              <div class="msg-header">
                <img src="uploads/<?= htmlspecialchars($msg['sender_pic']) ?>" alt="Sender" />
                <div>
                  <strong><?= htmlspecialchars($msg['sender_name']) ?></strong><br />
                  <span><?= htmlspecialchars($msg['timestamp']) ?></span>
                </div>
              </div>
              <p class="msg-content"><?= htmlspecialchars($msg['content']) ?></p>
              <a class="whatsapp-btn" href="https://wa.me/<?= htmlspecialchars($msg['phone']) ?>" target="_blank">
                Reply on WhatsApp
              </a>
            </div>
          <?php } ?>
        </div>
      </section>
    </div>
  </main>
</body>

<script>
  function previewProfilePic(event) {
    const reader = new FileReader();
    reader.onload = function() {
      document.getElementById('profilePreview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  }
</script>

</html>