<?php
session_start();
include('db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid listing ID.");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM listings WHERE id = $id");

if ($result->num_rows === 0) {
    die("Listing not found.");
}

$listing = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update logic
    $variety = $conn->real_escape_string($_POST['variety']);
    $quantity = floatval($_POST['quantity']);
    $price = floatval($_POST['price']);
    $location = $conn->real_escape_string($_POST['location']);
    $description = $conn->real_escape_string($_POST['description']);
    $status = $conn->real_escape_string($_POST['status']);

    $conn->query("UPDATE listings SET 
        title = '$variety',
        quantity= '$quantity',
        price_per_kg = '$price',
        location = '$location',
        description = '$description',
        is_available = '$status'
        WHERE id = $id");

    header("Location: manage-listings.php?msg=updated");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Listing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: linear-gradient(to right, #e8f5e9, #ffffff);
        }

        h2 {
            color: #2e7d32;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"], select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #43a047;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #388e3c;
        }
    </style>
</head>
<body>
    <h2>Edit Listing</h2>
    <form method="post">
        <label>Variety:</label>
        <input type="text" name="variety" value="<?= htmlspecialchars($listing['title']) ?>" required>

        <label>Quantity (kg):</label>
        <input type="number" name="quantity" step="0.01" value="<?= $listing['quantity'] ?>" required>

        <label>Price per kg:</label>
        <input type="number" name="price" step="0.01" value="<?= $listing['price_per_kg'] ?>" required>

        <label>Location:</label>
        <input type="text" name="location" value="<?= htmlspecialchars($listing['location']) ?>" required>

        <label>Description:</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($listing['description']) ?></textarea>

        <label>Status:</label>
        <select name="status">
            <option value="pending" <?= $listing['is_available'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $listing['is_available'] === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $listing['is_available'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>

        <button type="submit">Update Listing</button>
    </form>
</body>
</html>
