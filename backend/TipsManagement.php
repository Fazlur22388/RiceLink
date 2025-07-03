<?php
session_start();
include('db_connect.php');

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        
        // Add new tip
        if ($_POST['action'] == 'add') {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $category = mysqli_real_escape_string($conn, $_POST['category']);
            $icon_class = mysqli_real_escape_string($conn, $_POST['icon_class']);
            
            $query = "INSERT INTO tips_for_farmers (title, description, category, icon_class) VALUES ('$title', '$description', '$category', '$icon_class')";
            
            if (mysqli_query($conn, $query)) {
                $success_message = "Tip added successfully!";
            } else {
                $error_message = "Error adding tip: " . mysqli_error($conn);
            }
        }
        
        // Delete tip
        if ($_POST['action'] == 'delete') {
            $id = (int)$_POST['tip_id'];
            $query = "DELETE FROM tips_for_farmers WHERE id = $id";
            
            if (mysqli_query($conn, $query)) {
                $success_message = "Tip deleted successfully!";
            } else {
                $error_message = "Error deleting tip: " . mysqli_error($conn);
            }
        }
        
        // Update tip
        if ($_POST['action'] == 'update') {
            $id = (int)$_POST['tip_id'];
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $category = mysqli_real_escape_string($conn, $_POST['category']);
            $icon_class = mysqli_real_escape_string($conn, $_POST['icon_class']);
            
            $query = "UPDATE tips_for_farmers SET title = '$title', description = '$description', category = '$category', icon_class = '$icon_class' WHERE id = $id";
            
            if (mysqli_query($conn, $query)) {
                $success_message = "Tip updated successfully!";
            } else {
                $error_message = "Error updating tip: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch all tips
$query = "SELECT * FROM tips_for_farmers ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Category options
$categories = [
    'weather' => 'Weather & Climate',
    'planting' => 'Planting & Seeding',
    'harvesting' => 'Harvesting',
    'irrigation' => 'Water Management',
    'pest_control' => 'Pest Control',
    'soil_management' => 'Soil Management',
    'general' => 'General Tips'
];

// Icon options
$icons = [
    'fas fa-cloud-rain' => 'Rain Cloud',
    'fas fa-sun' => 'Sun',
    'fas fa-seedling' => 'Seedling',
    'fas fa-leaf' => 'Leaf',
    'fas fa-tint' => 'Water Drop',
    'fas fa-bug' => 'Bug',
    'fas fa-thermometer-half' => 'Thermometer',
    'fas fa-calendar-alt' => 'Calendar',
    'fas fa-lightbulb' => 'Light Bulb',
    'fas fa-warehouse' => 'Warehouse',
    'fas fa-wind' => 'Wind',
    'fas fa-cloud-sun' => 'Partly Cloudy'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tips Management - Weather Control Center</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #667eea 100%);
            min-height: 100vh;
            padding: 20px 0;
            position: relative;
        }

        /* Animated weather background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255,255,255,0.05) 0%, transparent 50%);
            z-index: -1;
            animation: weatherMove 15s ease-in-out infinite alternate;
        }

        @keyframes weatherMove {
            0% { transform: translateX(-20px) translateY(-10px); }
            100% { transform: translateX(20px) translateY(10px); }
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            margin-bottom: 20px;
            padding: 12px 20px;
            background: rgba(255,255,255,0.15);
            border-radius: 50px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .back-link:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }

        .header {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.3);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: headerShine 3s infinite;
        }

        @keyframes headerShine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .header h1 {
            color: #1e3c72;
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            z-index: 2;
        }

        .header .weather-icon {
            animation: rotate 4s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .header p {
            color: #555;
            font-size: 1.2rem;
            position: relative;
            z-index: 2;
        }

        .alert {
            padding: 18px;
            margin-bottom: 25px;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert.success {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
            border: none;
        }

        .alert.error {
            background: linear-gradient(135deg, #e17055, #d63031);
            color: white;
            border: none;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .add-form, .tips-list {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .form-title {
            color: #1e3c72;
            font-size: 1.6rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }

        .form-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #2a5298);
            border-radius: 2px;