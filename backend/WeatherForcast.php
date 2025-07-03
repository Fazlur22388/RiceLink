<?php
session_start();
include('db_connect.php');

// Fetch weather alerts with their locations
function getWeatherAlerts($conn) {
    $sql = "SELECT wa.*, GROUP_CONCAT(wl.location_name SEPARATOR '|') as locations 
            FROM weather_alerts wa 
            LEFT JOIN weather_locations wl ON wa.id = wl.alert_id 
            WHERE wa.is_active = 1 
            GROUP BY wa.id 
            ORDER BY wa.alert_type";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fetch current weather data
function getCurrentWeatherData($conn) {
    $sql = "SELECT * FROM weather_data WHERE is_current = 1 ORDER BY updated_at DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Fetch harvest recommendations
function getHarvestRecommendations($conn) {
    $sql = "SELECT * FROM harvest_recommendations WHERE is_active = 1 ORDER BY start_date";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get data using MySQLi
$alerts = getWeatherAlerts($conn);
$weatherData = getCurrentWeatherData($conn);
$harvestData = getHarvestRecommendations($conn);

// Organize alerts by type
$alertsByType = [];
foreach ($alerts as $alert) {
    $alertsByType[$alert['alert_type']] = $alert;
}

// Count active alerts
$activeAlertsCount = count($alerts);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Forecast for Farmers - RiceConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .weather-hero {
            background: linear-gradient(rgba(52, 152, 219, 0.8), rgba(41, 128, 185, 0.8)), 
                        url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
        }
        
        .weather-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .weather-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .weather-card-header {
            padding: 20px;
            font-weight: bold;
            font-size: 1.3rem;
            color: white;
            border: none;
        }
        
        .rainfall-header { background: linear-gradient(135deg, #3498db, #2980b9); }
        .dry-header { background: linear-gradient(135deg, #f39c12, #d68910); }
        .wind-header { background: linear-gradient(135deg, #95a5a6, #7f8c8d); }
        .temperature-header { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .harvest-header { background: linear-gradient(135deg, #27ae60, #229954); }
        
        .weather-content {
            padding: 25px;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .weather-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .location-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 3px;
            display: inline-block;
        }
        
        .date-range {
            background: #fff3e0;
            color: #f57c00;
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .harvest-entry {
            background: #f1f8e9;
            border-left: 4px solid #4caf50;
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="index.html" class="btn btn-success back-button">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>

    <!-- Hero Section -->
    <section class="weather-hero text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">
                <i class="fas fa-cloud-sun me-3"></i>Weather Forecast for Farmers
            </h1>
            <p class="lead">Stay updated with weather to protect your crops and plan better</p>
            <div class="mt-4">
                <span class="badge bg-light text-dark fs-6 me-3">
                    <i class="fas fa-calendar me-2"></i>Updated: <?php echo date('F j, Y, g:i a'); ?>
                </span>
                <span class="badge bg-success fs-6">
                    <i class="fas fa-check-circle me-2"></i>Active Alerts: <?php echo $activeAlertsCount; ?>
                </span>
            </div>
        </div>
    </section>

    <div class="container my-5">
        <!-- Weather Cards -->
        <div class="row">
            <!-- Rainfall Alert -->
            <div class="col-lg-6 mb-4">
                <div class="card weather-card">
                    <div class="card-header weather-card-header rainfall-header text-center">
                        <i class="fas fa-cloud-rain weather-icon"></i>
                        <div>Rainfall Alert</div>
                    </div>
                    <div class="card-body weather-content">
                        <?php if(isset($alertsByType['rainfall'])): ?>
                            <p class="mb-3">Heavy rain expected in:</p>
                            <div class="mb-3">
                                <?php 
                                $locations = explode('|', $alertsByType['rainfall']['locations']);
                                foreach($locations as $location): 
                                ?>
                                    <span class="location-tag"><?php echo htmlspecialchars($location); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <p class="mb-3">Duration: 
                                <span class="date-range">
                                    <?php echo date('M j', strtotime($alertsByType['rainfall']['start_date'])); ?> to 
                                    <?php echo date('M j', strtotime($alertsByType['rainfall']['end_date'])); ?>
                                </span>
                            </p>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Farmer Advisory:</strong> <?php echo htmlspecialchars($alertsByType['rainfall']['advisory_message']); ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data">No rainfall alerts at this time</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Dry Weather Warning -->
            <div class="col-lg-6 mb-4">
                <div class="card weather-card">
                    <div class="card-header weather-card-header dry-header text-center">
                        <i class="fas fa-sun weather-icon"></i>
                        <div>Dry Weather Warning</div>
                    </div>
                    <div class="card-body weather-content">
                        <?php if(isset($alertsByType['dry_weather'])): ?>
                            <p class="mb-3">Dry weather conditions in:</p>
                            <div class="mb-3">
                                <?php 
                                $locations = explode('|', $alertsByType['dry_weather']['locations']);
                                foreach($locations as $location): 
                                ?>
                                    <span class="location-tag"><?php echo htmlspecialchars($location); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <p class="mb-3">Duration: 
                                <span class="date-range">
                                    <?php echo date('M j', strtotime($alertsByType['dry_weather']['start_date'])); ?> to 
                                    <?php echo date('M j', strtotime($alertsByType['dry_weather']['end_date'])); ?>
                                </span>
                            </p>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-tint me-2"></i>
                                <strong>Farmer Advisory:</strong> <?php echo htmlspecialchars($alertsByType['dry_weather']['advisory_message']); ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data">No dry weather warnings at this time</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Strong Winds Notice -->
            <div class="col-lg-6 mb-4">
                <div class="card weather-card">
                    <div class="card-header weather-card-header wind-header text-center">
                        <i class="fas fa-wind weather-icon"></i>
                        <div>Strong Winds Notice</div>
                    </div>
                    <div class="card-body weather-content">
                        <?php if(isset($alertsByType['wind'])): ?>
                            <p class="mb-3">Strong winds expected in:</p>
                            <div class="mb-3">
                                <?php 
                                $locations = explode('|', $alertsByType['wind']['locations']);
                                foreach($locations as $location): 
                                ?>
                                    <span class="location-tag"><?php echo htmlspecialchars($location); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <p class="mb-3">Duration: 
                                <span class="date-range">
                                    <?php echo date('M j', strtotime($alertsByType['wind']['start_date'])); ?> to 
                                    <?php echo date('M j', strtotime($alertsByType['wind']['end_date'])); ?>
                                </span>
                            </p>
                            <div class="alert alert-secondary mb-0">
                                <i class="fas fa-shield-alt me-2"></i>
                                <strong>Farmer Advisory:</strong> <?php echo htmlspecialchars($alertsByType['wind']['advisory_message']); ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data">No wind warnings at this time</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Temperature Trends -->
            <div class="col-lg-6 mb-4">
                <div class="card weather-card">
                    <div class="card-header weather-card-header temperature-header text-center">
                        <i class="fas fa-thermometer-half weather-icon"></i>
                        <div>Temperature Trends</div>
                    </div>
                    <div class="card-body weather-content">
                        <?php if($weatherData): ?>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-danger"><?php echo htmlspecialchars($weatherData['location_name']); ?></h4>
                                    <h2 class="text-primary"><?php echo $weatherData['temperature']; ?>°C</h2>
                                    <small class="text-muted">Average Temperature</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success">Humidity</h4>
                                    <h2 class="text-info"><?php echo $weatherData['humidity']; ?>%</h2>
                                    <small class="text-muted">Current Level</small>
                                </div>
                            </div>
                            <?php if(isset($alertsByType['temperature'])): ?>
                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="fas fa-temperature-high me-2"></i>
                                    <strong>Farmer Advisory:</strong> <?php echo htmlspecialchars($alertsByType['temperature']['advisory_message']); ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-data">No temperature data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Time to Harvest - Full Width -->
        <div class="row">
            <div class="col-12">
                <div class="card weather-card">
                    <div class="card-header weather-card-header harvest-header text-center">
                        <i class="fas fa-seedling weather-icon"></i>
                        <div>Best Time to Harvest</div>
                    </div>
                    <div class="card-body weather-content">
                        <?php if(!empty($harvestData)): ?>
                            <p class="mb-4 text-center lead">Ideal harvesting conditions forecast for the following locations:</p>
                            <?php foreach($harvestData as $harvest): ?>
                                <div class="harvest-entry">
                                    <h6 class="mb-1 text-success">📍 <?php echo htmlspecialchars($harvest['location_name']); ?></h6>
                                    <span class="date-range">
                                        <?php echo date('M j', strtotime($harvest['start_date'])); ?> to 
                                        <?php echo date('M j', strtotime($harvest['end_date'])); ?>
                                    </span>
                                    <p class="mb-0 mt-2 text-muted">
                                        <?php echo htmlspecialchars($harvest['conditions']); ?>. 
                                        <?php echo htmlspecialchars($harvest['description']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data">No harvest recommendations available at this time</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Important Notes for Farmers</h5>
                    <ul class="mb-0">
                        <li>Weather conditions can change rapidly. Check this page regularly for updates.</li>
                        <li>Local micro-climates may vary. Observe your local conditions alongside these forecasts.</li>
                        <li>For emergency weather alerts, contact your local agricultural extension officer.</li>
                        <li>All times are in Sri Lanka Standard Time (UTC+5:30).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>