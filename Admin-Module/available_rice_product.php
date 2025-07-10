<?php
session_start();
include('../backend/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Rice Products</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e6f4ea, #ffffff); /* subtle greenish-white */
            padding: 20px;
            margin: 0;

           
        }

        h2 {
             text-align: center;
            font-size: 2em;
            color: #2e7d32; /* dark green */
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px #a5d6a7;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 0 20px;
        }

        .rice-card {
             background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #a5d6a7; /* light green border */
            box-shadow: 0 6px 15px rgba(46, 125, 50, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            position: relative;
        }

        .rice-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.4);
            border-color: #388e3c; /* darker green on hover */
        }

        .rice-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 2px solid #c8e6c9; /* subtle green border around image */
        }

        .rice-card h4 {
            font-size: 1.3em;
            margin: 10px 0 5px;
            color: #2e7d32; /* dark green */
        }

        .rice-card p {
             font-size: 1.1em;
            color: #388e3c; /* medium green */
            font-weight: 700;
        }

        .action-buttons {
    margin-top: 15px;
    display: flex;
    justify-content: space-between;
    gap: 10px; /* optional, adds space between buttons */
}

.action-buttons a,
.action-buttons form button {
    font-size: 0.95em;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 700;
    cursor: pointer;
    color: white;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    padding: 10px 15px;
    text-align: center;
    box-sizing: border-box;
    display: inline-block;
}

.edit-btn {
    background-color: #43a047; /* green */
}

.edit-btn:hover {
    background-color: #2e7d32; /* darker green */
    box-shadow: 0 6px 12px rgba(46, 125, 50, 0.6);
}

.delete-btn {
    background-color: #d32f2f; /* red */
}

.delete-btn:hover {
    background-color: #b71c1c; /* darker red */
    box-shadow: 0 6px 12px rgba(179, 28, 28, 0.6);
}

        .no-data {
            text-align: center;
            font-size: 1.3em;
            color: #4caf50;
            margin-top: 40px;
            font-weight: 600;
            text-shadow: 1px 1px 1px #c8e6c9;
        }
            
        
            


            </style>
</head>
<body>

<h2>Available Rice Products</h2>
<div class="grid-container">
<?php
$sql = "SELECT * FROM listings ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
    <div class="rice-card">
        <img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Rice Image">
        <h4><?php echo htmlspecialchars($row['variety']); ?></h4>
        <p>Rs.<?php echo number_format($row['price_per_kg'], 2); ?>/kg</p>
        <div class="action-buttons">
            <a href="edit_rice_product.php?id=<?= $row['id'] ?>" class="edit-btn">✏️ Edit</a>
            <form method="post" action="delete_rice_product.php" onsubmit="return confirm('Delete this rice product?');">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="delete-btn">🗑 Delete</button>
            </form>
        </div>
    </div>
<?php
    endwhile;
else:
    echo "<p class='no-data'>No rice data found.</p>";
endif;

$conn->close();
?>
</div>

<a class="add-link" href="add_rice_product.php">➕ Add New Rice Product</a>

</body>
</html>
