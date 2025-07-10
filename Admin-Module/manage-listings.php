<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$result = $conn->query("SELECT * FROM listings ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Listings</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px;
            background: linear-gradient(to right, #e8f5e9, #ffffff); /* Soft green to white gradient */
            color: #2c3e50;
        }

        h2 {
            text-align: center;
            font-size: 2em;
            color: #2e7d32; /* Dark green */
            margin-bottom: 30px;
        }

        table { 
            border-collapse: collapse; 
            width: 100%;
            margin-top: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td { 
            border: 1px solid #c8e6c9; 
            padding: 12px 15px; 
            text-align: left; 
        }

        th {
            background-color: #a5d6a7; /* Light green */
            color: #1b5e20; /* Darker green */
        }

        td {
            background-color: #f1f8e9; /* Soft greenish background for rows */
        }

        img {
            max-height: 100px;
            border-radius: 4px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            margin-right: 5px;
        }

        .edit-btn {
            background-color: #0288d1; /* Blue */
        }

        .edit-btn:hover {
            background-color: #0277bd;
        }

        .remove-btn {
            background-color: #e53935; /* Red */
        }

        .remove-btn:hover {
            background-color: #c62828;
        }

        .msg {
            color: #2e7d32;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }

        .removed-label {
            color: #9e9e9e;
            font-style: italic;
        }

        form {
            display: inline;
        }
    </style>
</head>
<body>

<h2>Admin - Manage Listings</h2>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'deleted'): ?>
        <p class="msg">🗑 Listing deleted successfully!</p>
    <?php elseif ($_GET['msg'] === 'updated'): ?>
        <p class="msg">✏️ Listing updated successfully!</p>
    <?php endif; ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Farmer ID</th>
            <th>Variety</th>
            <th>Quantity (kg)</th>
            <th>Price/kg</th>
            <th>Location</th>
            <th>Description</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['farmer_id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= number_format($row['price_per_kg'], 2) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= ucfirst(htmlspecialchars($row['is_available'])) ?></td>
                <td>
                    <form method="get" action="edit_listing.php">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="action-btn edit-btn">✏️ Edit</button>
                    </form>
                    <form method="post" action="delete_listing.php" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="action-btn remove-btn">🗑 Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
