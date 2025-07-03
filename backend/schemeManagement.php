<?php
session_start();
include('db_connect.php');

// Check if user is admin (you can modify this condition based on your authentication system)


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        
        // Add new scheme
        if ($_POST['action'] == 'add') {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            
            $query = "INSERT INTO gov_schemes (title, description) VALUES ('$title', '$description')";
            
            if (mysqli_query($conn, $query)) {
                $success_message = "Scheme added successfully!";
            } else {
                $error_message = "Error adding scheme: " . mysqli_error($conn);
            }
        }
        
        // Delete scheme
        if ($_POST['action'] == 'delete') {
            $id = (int)$_POST['scheme_id'];
            $query = "DELETE FROM gov_schemes WHERE id = $id";
            
            if (mysqli_query($conn, $query)) {
                $success_message = "Scheme deleted successfully!";
            } else {
                $error_message = "Error deleting scheme: " . mysqli_error($conn);
            }
        }
        
        // Update scheme
        if ($_POST['action'] == 'update') {
            $id = (int)$_POST['scheme_id'];
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            
            $query = "UPDATE gov_schemes SET title = '$title', description = '$description' WHERE id = $id";
            
            if (mysqli_query($conn, $query)) {
                $success_message = "Scheme updated successfully!";
            } else {
                $error_message = "Error updating scheme: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch all schemes
$query = "SELECT * FROM gov_schemes ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheme Management - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #2E7D32;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert.success {
            background: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }

        .alert.error {
            background: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .add-form, .schemes-list {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-title {
            color: #2E7D32;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #2E7D32;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #E8F5E8;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #f44336;
            color: white;
            font-size: 0.9rem;
            padding: 8px 15px;
        }

        .btn-danger:hover {
            background: #da190b;
        }

        .btn-edit {
            background: #FF9800;
            color: white;
            font-size: 0.9rem;
            padding: 8px 15px;
            margin-right: 10px;
        }

        .btn-edit:hover {
            background: #F57C00;
        }

        .scheme-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
        }

        .scheme-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .scheme-title {
            color: #2E7D32;
            font-size: 1.2rem;
            font-weight: bold;
            flex-grow: 1;
            margin-right: 15px;
        }

        .scheme-actions {
            display: flex;
            gap: 5px;
        }

        .scheme-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .scheme-meta {
            font-size: 0.9rem;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .edit-form {
            display: none;
            background: #fff3e0;
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
            border: 2px solid #FF9800;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(255,255,255,0.2);
        }

        @media (max-width: 968px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="govSchemes.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Schemes
        </a>

        <div class="header">
            <h1><i class="fas fa-cogs"></i> Government Schemes Management</h1>
            <p>Add, edit, and manage government schemes and support programs</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="main-content">
            <!-- Add New Scheme Form -->
            <div class="add-form">
                <h2 class="form-title">
                    <i class="fas fa-plus-circle"></i>
                    Add New Scheme
                </h2>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label for="title">Scheme Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required placeholder="Enter detailed description of the scheme..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Add Scheme
                    </button>
                </form>
            </div>

            <!-- Existing Schemes List -->
            <div class="schemes-list">
                <h2 class="form-title">
                    <i class="fas fa-list"></i>
                    Existing Schemes (<?php echo mysqli_num_rows($result); ?>)
                </h2>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($scheme = mysqli_fetch_assoc($result)): ?>
                        <div class="scheme-item">
                            <div class="scheme-header">
                                <h3 class="scheme-title"><?php echo htmlspecialchars($scheme['title']); ?></h3>
                                <div class="scheme-actions">
                                    <button class="btn btn-edit" onclick="toggleEdit(<?php echo $scheme['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this scheme?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="scheme_id" value="<?php echo $scheme['id']; ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="scheme-description">
                                <?php echo nl2br(htmlspecialchars($scheme['description'])); ?>
                            </div>
                            
                            <div class="scheme-meta">
                                <i class="fas fa-calendar"></i> Created: <?php echo date('F j, Y g:i A', strtotime($scheme['created_at'])); ?>
                                <?php if ($scheme['updated_at'] != $scheme['created_at']): ?>
                                    | <i class="fas fa-edit"></i> Updated: <?php echo date('F j, Y g:i A', strtotime($scheme['updated_at'])); ?>
                                <?php endif; ?>
                            </div>

                            <!-- Edit Form (Hidden by default) -->
                            <div id="edit-form-<?php echo $scheme['id']; ?>" class="edit-form">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="scheme_id" value="<?php echo $scheme['id']; ?>">
                                    
                                    <div class="form-group">
                                        <label>Edit Scheme Title</label>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($scheme['title']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Edit Description</label>
                                        <textarea name="description" required><?php echo htmlspecialchars($scheme['description']); ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Scheme
                                    </button>
                                    <button type="button" class="btn" style="background: #666; color: white; margin-left: 10px;" onclick="toggleEdit(<?php echo $scheme['id']; ?>)">
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 20px;"></i>
                        <h3>No schemes found</h3>
                        <p>Start by adding your first government scheme.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleEdit(schemeId) {
            const editForm = document.getElementById('edit-form-' + schemeId);
            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
            } else {
                editForm.style.display = 'none';
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>