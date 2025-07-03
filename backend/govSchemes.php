<?php
session_start();
include('db_connect.php');

// Fetch all active government schemes from database
$query = "SELECT * FROM gov_schemes WHERE status = 'active' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Government Schemes & Support - AgriConnect Lanka</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .schemes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .scheme-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 5px solid #4CAF50;
            position: relative;
            overflow: hidden;
        }

        .scheme-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #8BC34A);
        }

        .scheme-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .scheme-title {
            color: #2E7D32;
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .scheme-title i {
            font-size: 1.2rem;
            color: #4CAF50;
        }

        .scheme-description {
            color: #555;
            line-height: 1.6;
            font-size: 1rem;
            text-align: justify;
        }

        .no-schemes {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .no-schemes i {
            font-size: 4rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .no-schemes h3 {
            color: #2E7D32;
            margin-bottom: 10px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #2E7D32;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-btn:hover {
            background: #1B5E20;
            transform: translateY(-2px);
        }

        .admin-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #FF9800;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(255,152,0,0.3);
            transition: all 0.3s ease;
        }

        .admin-btn:hover {
            background: #F57C00;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .schemes-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .scheme-card {
                padding: 20px;
            }
            
            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-landmark"></i> Government Schemes & Support</h1>
            <p>Explore financial assistance and support programs for Sri Lankan farmers</p>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="schemes-grid">
                <?php while ($scheme = mysqli_fetch_assoc($result)): ?>
                    <div class="scheme-card">
                        <h3 class="scheme-title">
                            <i class="fas fa-seedling"></i>
                            <?php echo htmlspecialchars($scheme['title']); ?>
                        </h3>
                        <p class="scheme-description">
                            <?php echo nl2br(htmlspecialchars($scheme['description'])); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-schemes">
                <i class="fas fa-info-circle"></i>
                <h3>No Schemes Available</h3>
                <p>Currently, there are no government schemes available. Please check back later.</p>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.html" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>

    <!-- Admin Management Button (only show if logged in as admin) -->
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin'): ?>
        <a href="schemeManagement.php" class="admin-btn" title="Manage Schemes">
            <i class="fas fa-cog"></i>
        </a>
    <?php endif; ?>
</body>
</html>

<?php
mysqli_close($conn);
?>