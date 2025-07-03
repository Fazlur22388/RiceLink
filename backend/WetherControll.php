<?php
session_start();
include('db_connect.php');

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add_alert':
                addWeatherAlert($conn, $_POST);
                break;
            case 'update_alert':
                updateWeatherAlert($conn, $_POST);
                break;
            case 'delete_alert':
                deleteWeatherAlert($conn, $_POST['alert_id']);
                break;
            case 'update_weather_data':
                updateWeatherData($conn, $_POST);
                break;
            case 'add_harvest':
                addHarvestRecommendation($conn, $_POST);
                break;
            case 'update_harvest':
                updateHarvestRecommendation($conn, $_POST);
                break;
            case 'delete_harvest':
                deleteHarvestRecommendation($conn, $_POST['harvest_id']);
                break;
        }
        header("Location: WetherControll.php");
        exit();
    }
}

// Functions
function addWeatherAlert($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO weather_alerts (alert_type, title, description, start_date, end_date, advisory_message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $data['alert_type'], $data['title'], $data['description'], $data['start_date'], $data['end_date'], $data['advisory_message']);
    $stmt->execute();
    $alert_id = $conn->insert_id;

    if(!empty($data['locations'])) {
        $locations = explode(',', $data['locations']);
        foreach($locations as $location) {
            $location = trim($location);
            if(!empty($location)) {
                $stmt = $conn->prepare("INSERT INTO weather_locations (alert_id, location_name) VALUES (?, ?)");
                $stmt->bind_param("is", $alert_id, $location);
                $stmt->execute();
            }
        }
    }
}

function updateWeatherAlert($conn, $data) {
    $stmt = $conn->prepare("UPDATE weather_alerts SET title=?, description=?, start_date=?, end_date=?, advisory_message=?, is_active=? WHERE id=?");
    $stmt->bind_param("ssssssi", $data['title'], $data['description'], $data['start_date'], $data['end_date'], $data['advisory_message'], $data['is_active'], $data['alert_id']);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM weather_locations WHERE alert_id = ?");
    $stmt->bind_param("i", $data['alert_id']);
    $stmt->execute();

    if(!empty($data['locations'])) {
        $locations = explode(',', $data['locations']);
        foreach($locations as $location) {
            $location = trim($location);
            if(!empty($location)) {
                $stmt = $conn->prepare("INSERT INTO weather_locations (alert_id, location_name) VALUES (?, ?)");
                $stmt->bind_param("is", $data['alert_id'], $location);
                $stmt->execute();
            }
        }
    }
}

function deleteWeatherAlert($conn, $alert_id) {
    $stmt = $conn->prepare("DELETE FROM weather_alerts WHERE id = ?");
    $stmt->bind_param("i", $alert_id);
    $stmt->execute();
}

function updateWeatherData($conn, $data) {
    $conn->query("UPDATE weather_data SET is_current = 0");
    $stmt = $conn->prepare("INSERT INTO weather_data (location_name, temperature, humidity, measurement_date, is_current) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sdds", $data['location_name'], $data['temperature'], $data['humidity'], $data['measurement_date']);
    $stmt->execute();
}

function addHarvestRecommendation($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO harvest_recommendations (location_name, start_date, end_date, description, conditions) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $data['location_name'], $data['start_date'], $data['end_date'], $data['description'], $data['conditions']);
    $stmt->execute();
}

function updateHarvestRecommendation($conn, $data) {
    $stmt = $conn->prepare("UPDATE harvest_recommendations SET location_name=?, start_date=?, end_date=?, description=?, conditions=?, is_active=? WHERE id=?");
    $stmt->bind_param("ssssssi", $data['location_name'], $data['start_date'], $data['end_date'], $data['description'], $data['conditions'], $data['is_active'], $data['harvest_id']);
    $stmt->execute();
}

function deleteHarvestRecommendation($conn, $harvest_id) {
    $stmt = $conn->prepare("DELETE FROM harvest_recommendations WHERE id = ?");
    $stmt->bind_param("i", $harvest_id);
    $stmt->execute();
}

// Fetch data for display
$alerts_result = $conn->query("SELECT wa.*, GROUP_CONCAT(wl.location_name SEPARATOR ', ') as locations FROM weather_alerts wa LEFT JOIN weather_locations wl ON wa.id = wl.alert_id GROUP BY wa.id ORDER BY wa.created_at DESC");
$alerts = $alerts_result->fetch_all(MYSQLI_ASSOC);

$weather_result = $conn->query("SELECT * FROM weather_data ORDER BY created_at DESC LIMIT 5");
$weatherData = $weather_result->fetch_all(MYSQLI_ASSOC);

$harvest_result = $conn->query("SELECT * FROM harvest_recommendations ORDER BY created_at DESC");
$harvestData = $harvest_result->fetch_all(MYSQLI_ASSOC);

$locations_result = $conn->query("SELECT name FROM sri_lankan_locations WHERE is_active = 1 ORDER BY name");
$locations = [];
while ($row = $locations_result->fetch_assoc()) {
    $locations[] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Control Panel - RiceConnect Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                    <h1 class="h3 mb-0"><i class="fas fa-cloud-sun"></i> Weather Control Panel</h1>
                    <span class="badge bg-success">RiceConnect Admin</span>
                </div>
            </div>
        </div>

        <!-- Weather Alerts Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Weather Alerts</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAlertModal">
                            <i class="fas fa-plus"></i> Add Alert
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Locations</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($alerts as $alert): ?>
                                    <tr>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($alert['alert_type']); ?></span></td>
                                        <td><?php echo htmlspecialchars($alert['title']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($alert['description'], 0, 50)) . '...'; ?></td>
                                        <td><?php echo date('Y-M-d', strtotime($alert['start_date'])); ?></td>
                                        <td><?php echo date('Y-M-d', strtotime($alert['end_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($alert['locations'] ?? 'No locations'); ?></td>
                                        <td>
                                            <?php if($alert['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-alert-btn" 
                                                    data-alert='<?php echo json_encode($alert); ?>'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_alert">
                                                <input type="hidden" name="alert_id" value="<?php echo $alert['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Delete this alert?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weather Data Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-thermometer-half"></i> Current Weather Data</h5>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#updateWeatherModal">
                            <i class="fas fa-sync-alt"></i> Update Weather
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Location</th>
                                        <th>Temperature (°C)</th>
                                        <th>Humidity (%)</th>
                                        <th>Measurement Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($weatherData as $weather): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($weather['location_name']); ?></td>
                                        <td><?php echo $weather['temperature']; ?>°C</td>
                                        <td><?php echo $weather['humidity']; ?>%</td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($weather['measurement_date'])); ?></td>
                                        <td>
                                            <?php if($weather['is_current']): ?>
                                                <span class="badge bg-success">Current</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Historical</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Harvest Recommendations Section -->
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-seedling"></i> Harvest Recommendations</h5>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addHarvestModal">
                            <i class="fas fa-plus"></i> Add Recommendation
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Location</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Description</th>
                                        <th>Conditions</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($harvestData as $harvest): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($harvest['location_name']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($harvest['start_date'])); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($harvest['end_date'])); ?></td>
                                        <td><?php echo htmlspecialchars(substr($harvest['description'], 0, 50)) . '...'; ?></td>
                                        <td><?php echo htmlspecialchars(substr($harvest['conditions'], 0, 30)) . '...'; ?></td>
                                        <td>
                                            <?php if($harvest['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-harvest-btn" 
                                                    data-harvest='<?php echo json_encode($harvest); ?>'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_harvest">
                                                <input type="hidden" name="harvest_id" value="<?php echo $harvest['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Delete this recommendation?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Alert Modal -->
    <div class="modal fade" id="addAlertModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Weather Alert</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_alert">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alert Type</label>
                                <select name="alert_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="rainfall">Rainfall</option>
                                    <option value="dry_weather">Dry Weather</option>
                                    <option value="wind">Wind</option>
                                    <option value="temperature">Temperature</option>
                                    <option value="flood">Flood</option>
                                    <option value="drought">Drought</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="datetime-local" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="datetime-local" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Advisory Message</label>
                            <textarea name="advisory_message" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Locations (comma-separated)</label>
                            <input type="text" name="locations" class="form-control" 
                                   placeholder="e.g., Colombo, Kandy, Galle">
                            <div class="form-text">Available locations: <?php echo implode(', ', $locations); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Alert</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Alert Modal -->
    <div class="modal fade" id="editAlertModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Weather Alert</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_alert">
                        <input type="hidden" name="alert_id" id="edit_alert_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alert Type</label>
                                <select name="alert_type" id="edit_alert_type" class="form-select" required>
                                    <option value="rainfall">Rainfall</option>
                                    <option value="dry_weather">Dry Weather</option>
                                    <option value="wind">Wind</option>
                                    <option value="temperature">Temperature</option>
                                    <option value="flood">Flood</option>
                                    <option value="drought">Drought</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" id="edit_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="datetime-local" name="start_date" id="edit_start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="datetime-local" name="end_date" id="edit_end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Advisory Message</label>
                            <textarea name="advisory_message" id="edit_advisory_message" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Locations (comma-separated)</label>
                            <input type="text" name="locations" id="edit_locations" class="form-control">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Alert</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Weather Modal -->
    <div class="modal fade" id="updateWeatherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Weather Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_weather_data">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <select name="location_name" class="form-select" required>
                                <option value="">Select Location</option>
                                <?php foreach($locations as $location): ?>
                                    <option value="<?php echo htmlspecialchars($location); ?>">
                                        <?php echo htmlspecialchars($location); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Temperature (°C)</label>
                            <input type="number" name="temperature" class="form-control" step="0.1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Humidity (%)</label>
                            <input type="number" name="humidity" class="form-control" step="0.1" min="0" max="100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Measurement Date</label>
                            <input type="datetime-local" name="measurement_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Weather</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Harvest Modal -->
    <div class="modal fade" id="addHarvestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Harvest Recommendation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_harvest">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <select name="location_name" class="form-select" required>
                                <option value="">Select Location</option>
                                <?php foreach($locations as $location): ?>
                                    <option value="<?php echo htmlspecialchars($location); ?>">
                                        <?php echo htmlspecialchars($location); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Conditions</label>
                            <textarea name="conditions" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Add Recommendation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Harvest Modal -->
    <div class="modal fade" id="editHarvestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Harvest Recommendation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_harvest">
                        <input type="hidden" name="harvest_id" id="edit_harvest_id">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <select name="location_name" id="edit_harvest_location" class="form-select" required>
                                <?php foreach($locations as $location): ?>
                                    <option value="<?php echo htmlspecialchars($location); ?>">
                                        <?php echo htmlspecialchars($location); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="edit_harvest_start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" id="edit_harvest_end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_harvest_description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Conditions</label>
                            <textarea name="conditions" id="edit_harvest_conditions" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_harvest_is_active" value="1">
                                <label class="form-check-label" for="edit_harvest_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Recommendation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit alert button click
        document.querySelectorAll('.edit-alert-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const alert = JSON.parse(this.getAttribute('data-alert'));
                document.getElementById('edit_alert_id').value = alert.id;
                document.getElementById('edit_alert_type').value = alert.alert_type;
                document.getElementById('edit_title').value = alert.title;
                document.getElementById('edit_description').value = alert.description;
                document.getElementById('edit_start_date').value = new Date(alert.start_date).toISOString().slice(0,16);
                document.getElementById('edit_end_date').value = new Date(alert.end_date).toISOString().slice(0,16);
                document.getElementById('edit_advisory_message').value = alert.advisory_message;
                document.getElementById('edit_locations').value = alert.locations;
                document.getElementById('edit_is_active').checked = alert.is_active == 1;
                new bootstrap.Modal(document.getElementById('editAlertModal')).show();
            });
        });
    
        // Handle edit harvest button click
        document.querySelectorAll('.edit-harvest-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const harvest = JSON.parse(this.getAttribute('data-harvest'));
                document.getElementById('edit_harvest_id').value = harvest.id;
                document.getElementById('edit_harvest_location').value = harvest.location_name;
                document.getElementById('edit_harvest_start_date').value = harvest.start_date;
                document.getElementById('edit_harvest_end_date').value = harvest.end_date;
                document.getElementById('edit_harvest_description').value = harvest.description;
                document.getElementById('edit_harvest_conditions').value = harvest.conditions;
                document.getElementById('edit_harvest_is_active').checked = harvest.is_active == 1;
                new bootstrap.Modal(document.getElementById('editHarvestModal')).show();
            });
        });
    </script>
</body>
</html>
