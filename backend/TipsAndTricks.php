<?php
session_start();
include('db_connect.php');

// Fetch all active tips from database
$query = "SELECT * FROM tips_for_farmers WHERE status = 'active' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Category colors for weather theme
$category_colors = [
    'weather' => '#FF6B35',
    'planting' => '#4ECDC4', 
    'harvesting' => '#45B7D1',
    'irrigation' => '#96CEB4',
    'pest_control' => '#FECA57',
    'soil_management' => '#A0522D',
    'general' => '#6C5CE7'
];

$category_gradients = [
    'weather' => 'linear-gradient(135deg, #FF6B35, #FF8E53)',
    'planting' => 'linear-gradient(135deg, #4ECDC4, #44A08D)', 
    'harvesting' => 'linear-gradient(135deg, #45B7D1, #2196F3)',
    'irrigation' => 'linear-gradient(135deg, #96CEB4, #FECA57)',
    'pest_control' => 'linear-gradient(135deg, #FECA57, #FF9F43)',
    'soil_management' => 'linear-gradient(135deg, #A0522D, #8B4513)',
    'general' => 'linear-gradient(135deg, #6C5CE7, #A29BFE)'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agricultural Tips & Tricks - Weather Wise Farming</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            position: relative;
        }

        /* Animated background clouds */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255,255,255,0.05) 0%, transparent 50%);
            z-index: -1;
            animation: cloudMove 20s ease-in-out infinite alternate;
        }

        @keyframes cloudMove {
            0% { transform: translateX(-10px) translateY(-5px); }
            100% { transform: translateX(10px) translateY(5px); }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 5px;
            background: linear-gradient(90deg, #FFD700, #FFA500);
            border-radius: 5px;
        }

        .header h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header .weather-icon {
            font-size: 2.5rem;
            color: #FFD700;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .header p {
            font-size: 1.3rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
        }

        .weather-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .weather-stat {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            color: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            min-width: 150px;
        }

        .weather-stat i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .weather-stat .value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .weather-stat .label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .tip-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.4s ease;
            position: relative;
        }

        .tip-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .tip-header {
            padding: 25px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .tip-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.1) 10px,
                rgba(255,255,255,0.1) 20px
            );
            animation: shimmer 3s linear infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%); }
            100% { transform: translateX(100%) translateY(100%); }
        }

        .tip-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
            position: relative;
            z-index: 2;
        }

        .tip-title {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .tip-category {
            background: rgba(255,255,255,0.3);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 2;
        }

        .tip-content {
            padding: 25px;
        }

        .tip-description {
            color: #444;
            line-height: 1.7;
            font-size: 1rem;
            text-align: justify;
        }

        .tip-footer {
            padding: 15px 25px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: #666;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tip-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tip-actions {
            display: flex;
            gap: 10px;
        }

        .no-tips {
            text-align: center;
            background: rgba(255,255,255,0.95);
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }

        .no-tips i {
            font-size: 5rem;
            color: #667eea;
            margin-bottom: 25px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .no-tips h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .admin-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #FF6B35, #FF8E53);
            color: white;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            text-decoration: none;
            box-shadow: 0 8px 25px rgba(255,107,53,0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .admin-btn:hover {
            transform: scale(1.1) rotate(360deg);
            box-shadow: 0 12px 35px rgba(255,107,53,0.6);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.2rem;
                flex-direction: column;
                gap: 10px;
            }
            
            .tips-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .weather-stats {
                gap: 15px;
            }
            
            .weather-stat {
                min-width: 120px;
                padding: 15px;
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
            <h1>
                <i class="fas fa-cloud-sun weather-icon"></i>
                Agricultural Tips & Tricks
                <i class="fas fa-seedling weather-icon"></i>
            </h1>
            <p>Weather-wise farming techniques and expert advice for Sri Lankan agriculture</p>
        </div>

        <!-- Weather-style stats -->
        <div class="weather-stats">
            <div class="weather-stat">
                <i class="fas fa-lightbulb" style="color: #FFD700;"></i>
                <div class="value"><?php echo mysqli_num_rows($result); ?></div>
                <div class="label">Total Tips</div>
            </div>
            <div class="weather-stat">
                <i class="fas fa-thermometer-half" style="color: #FF6B35;"></i>
                <div class="value">28°C</div>
                <div class="label">Today's Temp</div>
            </div>
            <div class="weather-stat">
                <i class="fas fa-tint" style="color: #4ECDC4;"></i>
                <div class="value">75%</div>
                <div class="label">Humidity</div>
            </div>
            <div class="weather-stat">
                <i class="fas fa-wind" style="color: #96CEB4;"></i>
                <div class="value">12 km/h</div>
                <div class="label">Wind Speed</div>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="tips-grid">
                <?php while ($tip = mysqli_fetch_assoc($result)): ?>
                    <div class="tip-card">
                        <div class="tip-header" style="background: <?php echo $category_gradients[$tip['category']] ?? $category_gradients['general']; ?>">
                            <i class="tip-icon <?php echo htmlspecialchars($tip['icon_class']); ?>"></i>
                            <h3 class="tip-title"><?php echo htmlspecialchars($tip['title']); ?></h3>
                            <span class="tip-category"><?php echo ucfirst(str_replace('_', ' ', $tip['category'])); ?></span>
                        </div>
                        
                        <div class="tip-content">
                            <p class="tip-description">
                                <?php echo nl2br(htmlspecialchars($tip['description'])); ?>
                            </p>
                        </div>
                        
                        <div class="tip-footer">
                            <div class="tip-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('M j, Y', strtotime($tip['created_at'])); ?>
                            </div>
                            <div class="tip-actions">
                                <i class="fas fa-heart" style="color: #e74c3c;"></i>
                                <i class="fas fa-share-alt" style="color: #3498db;"></i>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-tips">
                <i class="fas fa-cloud-sun"></i>
                <h3>No Tips Available</h3>
                <p>Weather conditions are perfect for new tips! Please check back later.</p>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <a href="index.html" class="back-btn">
                <i class="fas fa-home"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Admin Management Button -->
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin'): ?>
        <a href="TipsManagement.php" class="admin-btn" title="Manage Tips">
            <i class="fas fa-cogs"></i>
        </a>
    <?php endif; ?>
</body>
</html>

<?php
mysqli_close($conn);
?>