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

            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        
        }

        h2 {
            text-align: center;
            font-size: 2em;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        table 
        { 
            border-collapse: collapse; 
            width: 100%;
            margin-top: 20px;
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 10px; 
            text-align: left; 
        }

        
        th { background-color: #f9f9f9; }
        img { max-height: 100px; }
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            color: white;
            cursor: pointer;
            text-decoration: none;
            margin-right: 5px;
        }
        .approve-btn { background-color: green; }
        .reject-btn { background-color: orange; }
        .remove-btn { background-color: red; }
        .msg { color: green; font-weight: bold; }
        .removed-label { color: gray; font-style: italic; }
        form { display: inline; }
    </style>
</head>
<body>

<h2>Admin - Manage Listings</h2>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'approved'): ?>
        <p class="msg">✅ Listing approved successfully!</p>
    <?php elseif ($_GET['msg'] === 'rejected'): ?>
        <p class="msg">❌ Listing rejected successfully!</p>
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <p class="msg">🗑 Listing deleted successfully!</p>
    <?php endif; ?>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Farmer ID</th>
            <th>Title</th>
            <th>Quantity (kg)</th>
            <th>Price/kg</th>
            <th>Location</th>
            <th>Description</th>
            <th>Image</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php $status = strtolower(trim($row['is_available'])); ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['farmer_id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= number_format($row['price_per_kg'], 2) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td>
                    <?php if (!empty($row['image_path'])): ?>
                        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Image">
                    <?php endif; ?>
                </td>
                <td><?= ucfirst($status) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <?php if ($status == 1): ?>
                        <form method="post" action="approve_listing.php" onsubmit="return confirm('Approve this listing?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="action-btn approve-btn">✅ Approve</button>
                        </form>
                        <form method="post" action="reject_listing.php" onsubmit="return confirm('Reject this listing?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="action-btn reject-btn">❌ Reject</button>
                        </form>
                    <?php elseif ($status === 'rejected'): ?>
                        <form method="post" action="delete_rejected_listing.php" onsubmit="return confirm('Delete this rejected listing permanently?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="action-btn remove-btn">🗑 Delete</button>
                        </form>
                    <?php else: ?>
                        <span class="removed-label">No actions available</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
