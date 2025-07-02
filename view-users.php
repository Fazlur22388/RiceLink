<?php
session_start();
include('db_connect.php');

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch buyers
$buyers = $conn->query("SELECT id, name, email FROM buyers ORDER BY id DESC");

// Fetch farmers
$farmers = $conn->query("SELECT id, name, email FROM farmers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View Users - Admin</title>
    <link rel="stylesheet" href="css/admin-style.css" />
    <style>
        h2 {
            margin-top: 20px;
            color: rgb(44, 121, 3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
            background-color: rgb(228, 253, 215);
        }
        th, td {
            padding: 12px 10px;
            border: 1px solid #ccc;
            text-align: left;
            font: 1em sans-serif;
        }
        th {
            background-color: rgb(35, 100, 0);
            color: white;
        }
        p {
            margin-top: 10px;
            font: 1em sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px 20px;
        }
        .remove-link {
            color: red;
            font-weight: bold;
            text-decoration: none;
        }
        .remove-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Users Management</h1>

        <h2>Buyers</h2>
        <?php if ($buyers->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th> <!-- 4th column header -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($buyer = $buyers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $buyer['id']; ?></td>
                            <td><?php echo htmlspecialchars($buyer['name']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['email']); ?></td>
                            <td>
                                <a class="remove-link" href="remove_buyer.php?id=<?php echo $buyer['id']; ?>" onclick="return confirm('Are you sure you want to remove this buyer?');">
                                    Remove Buyer
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No buyers found.</p>
        <?php endif; ?>

        <h2>Farmers</h2>
        <?php if ($farmers->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th> <!-- 4th column header -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($farmer = $farmers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $farmer['id']; ?></td>
                            <td><?php echo htmlspecialchars($farmer['name']); ?></td>
                            <td><?php echo htmlspecialchars($farmer['email']); ?></td>
                            <td>
                                <a class="remove-link" href="remove_farmer.php?id=<?php echo $farmer['id']; ?>" onclick="return confirm('Are you sure you want to remove this farmer?');">
                                    Remove User
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No farmers found.</p>
        <?php endif; ?>

        <p><a href="admin-dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
