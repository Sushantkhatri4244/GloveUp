<?php

include_once 'dbConnection.php';

// Check if user is admin
// Uncomment this section in production
// if (!isAdmin()) {
//     header("Location: ../login.php");
//     exit();
// }

// Function to get all classes
function getAllClasses($conn) {
    $sql = "SELECT c.*, u.full_name as trainer_name, 
            DATE_FORMAT(c.start_time, '%h:%i %p') as formatted_start_time,
            DATE_FORMAT(c.end_time, '%h:%i %p') as formatted_end_time
            FROM classes c
            JOIN users u ON u.full_name = c.trainer_name
            ORDER BY FIELD(c.day_of_week, 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'), 
            c.start_time ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all trainers
function getAllTrainers($conn) {
    $sql = "SELECT id, full_name as name FROM users WHERE role = 'trainer' OR role = 'admin' ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all class types
function getAllClassTypes($conn) {
    $sql = "SELECT id, name FROM class_types WHERE status = 1 ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new class
    if (isset($_POST['add_class'])) {
        $day = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $class_type = $_POST['class_type'];
        $trainer_id = (int)$_POST['trainer_id'];
        
        // Get the trainer name from the trainer ID
        $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute([$trainer_id]);
        $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
        $trainer_name = $trainer ? $trainer['full_name'] : '';
        
        // Insert class with trainer_name
        $sql = "INSERT INTO classes (day_of_week, start_time, end_time, class_type, trainer_name) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$day, $start_time, $end_time, $class_type, $trainer_name])) {
            $success_message = "Class added successfully!";
        } else {
            $error_message = "Error adding class: " . implode(" ", $stmt->errorInfo());
        }
    }
    
    // Update existing class
    if (isset($_POST['update_class'])) {
        $class_id = (int)$_POST['class_id'];
        $day = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $class_type = $_POST['class_type'];
        $trainer_id = (int)$_POST['trainer_id'];
        $status = isset($_POST['status']) ? 1 : 0;
        
        // Get the trainer name from the trainer ID
        $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $stmt->execute([$trainer_id]);
        $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
        $trainer_name = $trainer ? $trainer['full_name'] : '';
        
        // Update class with trainer_name
        $sql = "UPDATE classes SET day_of_week = ?, start_time = ?, end_time = ?, 
                class_type = ?, trainer_name = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$day, $start_time, $end_time, $class_type, $trainer_name, $status, $class_id])) {
            $success_message = "Class updated successfully!";
        } else {
            $error_message = "Error updating class: " . implode(" ", $stmt->errorInfo());
        }
    }
    
    // Delete class
    if (isset($_POST['delete_class'])) {
        $class_id = (int)$_POST['class_id'];
        
        $sql = "DELETE FROM classes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$class_id])) {
            $success_message = "Class deleted successfully!";
        } else {
            $error_message = "Error deleting class: " . implode(" ", $stmt->errorInfo());
        }
    }
}

// Get data for display
$classes = getAllClasses($conn);
$trainers = getAllTrainers($conn);
$class_types = getAllClassTypes($conn);

// Define days of week for form dropdown
$days_of_week = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes - GloveUp Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        /* Sidebar styles - updated to match user.php */
        .sidebar {
            background-color: #1e3a5c;
            color: white;
            height: 100vh;
            position: fixed;
            width: 200px;
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            transition: background-color 0.3s;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background-color: #2c4d76;
        }
        
        .sidebar .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content-area {
            margin-left: 200px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .user-modal .form-group {
            margin-bottom: 15px;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }
        
        /* Content area - adjusted to match sidebar width */
        .classes-container {
            margin-left: 200px;
            padding: 20px;
            max-width: 1200px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .admin-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .class-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 5px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }

        /* Header styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .user-modal .form-group {
            margin-bottom: 15px;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="p-3">GloveUp </h4>
        <a href="adminoveralldashboard.php" class="py-2"><i class="fas fa-tachometer-alt icon"></i> Dashboard</a>
        <a href="user.php" class="py-2"><i class="fas fa-user-tag icon"></i> Roles</a>
        <a href="classesdashboard.php" class="py-2 active"><i class="fas fa-dumbbell icon"></i> Classes</a>
        <a href="athletedashboard.php" class="py-2"><i class="fas fa-medal icon"></i> Athletes</a>
        <!-- <a href="#" class="py-2"><i class="fas fa-cog icon"></i> Settings</a> -->
        <a href="logout.php" class="py-2"><i class="fas fa-sign-out-alt icon"></i> Logout</a>
    </div>

    <div class="classes-container">
        <h1>Manage Classes</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="admin-controls">
            <h2>Classes Administration</h2>
            <!-- Add New Class Button -->
            <button id="addClassBtn" class="btn-primary">Add New Class</button>
        </div>
        
        <!-- Add Class Modal -->
        <div id="addClassModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New Class</h2>
                <form action="" method="POST" class="class-form">
                    <div class="form-group">
                        <label for="day_of_week">Day of Week</label>
                        <select name="day_of_week" id="day_of_week" class="form-control" required>
                            <?php foreach ($days_of_week as $day): ?>
                                <option value="<?php echo $day; ?>"><?php echo ucfirst($day); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="class_type">Class Type</label>
                        <select name="class_type" id="class_type" class="form-control" required>
                            <?php foreach ($class_types as $type): ?>
                                <option value="<?php echo $type['name']; ?>"><?php echo $type['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainer_id">Trainer</label>
                        <select name="trainer_id" id="trainer_id" class="form-control" required>
                            <?php foreach ($trainers as $trainer): ?>
                                <option value="<?php echo $trainer['id']; ?>"><?php echo $trainer['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_class" class="btn-primary">Add Class</button>
                </form>
            </div>
        </div>
        
        <!-- Edit Class Modal -->
        <div id="editClassModal" class="modal">
            <div class="modal-content">
                <span class="close" id="editClose">&times;</span>
                <h2>Edit Class</h2>
                <form action="" method="POST" class="class-form" id="editClassForm">
                    <input type="hidden" name="class_id" id="edit_class_id">
                    
                    <div class="form-group">
                        <label for="edit_day_of_week">Day of Week</label>
                        <select name="day_of_week" id="edit_day_of_week" class="form-control" required>
                            <?php foreach ($days_of_week as $day): ?>
                                <option value="<?php echo $day; ?>"><?php echo ucfirst($day); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_start_time">Start Time</label>
                        <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_end_time">End Time</label>
                        <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_class_type">Class Type</label>
                        <select name="class_type" id="edit_class_type" class="form-control" required>
                            <?php foreach ($class_types as $type): ?>
                                <option value="<?php echo $type['name']; ?>"><?php echo $type['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_trainer_id">Trainer</label>
                        <select name="trainer_id" id="edit_trainer_id" class="form-control" required>
                            <?php foreach ($trainers as $trainer): ?>
                                <option value="<?php echo $trainer['id']; ?>"><?php echo $trainer['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="status" id="edit_status" value="1">
                            Active
                        </label>
                    </div>
                    
                    <button type="submit" name="update_class" class="btn-primary">Update Class</button>
                </form>
            </div>
        </div>
        
        <!-- Classes Table -->
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Class Type</th>
                    <th>Trainer</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($classes) > 0): ?>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?php echo ucfirst($class['day_of_week']); ?></td>
                            <td><?php echo $class['formatted_start_time']; ?> - <?php echo $class['formatted_end_time']; ?></td>
                            <td><?php echo $class['class_type']; ?></td>
                            <td><?php echo $class['trainer_name']; ?></td>
                            <td><?php echo ($class['status'] == 1) ? 'Active' : 'Inactive'; ?></td>
                            <td class="action-buttons">
                                <button class="btn-warning edit-btn" 
                                        data-id="<?php echo $class['id']; ?>"
                                        data-day="<?php echo $class['day_of_week']; ?>"
                                        data-start="<?php echo $class['start_time']; ?>"
                                        data-end="<?php echo $class['end_time']; ?>"
                                        data-type="<?php echo $class['class_type']; ?>"
                                        data-trainer-name="<?php echo $class['trainer_name']; ?>"
                                        data-status="<?php echo $class['status']; ?>">
                                    Edit
                                </button>
                                
                                <form action="" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this class?');">
                                    <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                    <button type="submit" name="delete_class" class="btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No classes found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add Class Modal
            const addModal = document.getElementById('addClassModal');
            const addBtn = document.getElementById('addClassBtn');
            const addClose = document.querySelector('#addClassModal .close');
            
            addBtn.onclick = function() {
                addModal.style.display = "block";
            }
            
            addClose.onclick = function() {
                addModal.style.display = "none";
            }
            
            // Edit Class Modal
            const editModal = document.getElementById('editClassModal');
            const editBtns = document.getElementsByClassName('edit-btn');
            const editClose = document.getElementById('editClose');
            
            // Find trainer ID by name function
            function findTrainerIdByName(trainerName) {
                const trainerSelect = document.getElementById('edit_trainer_id');
                const options = trainerSelect.options;
                
                for (let i = 0; i < options.length; i++) {
                    const optionText = options[i].text;
                    if (optionText === trainerName) {
                        return options[i].value;
                    }
                }
                
                return ''; // Return empty string if not found
            }
            
            for (let i = 0; i < editBtns.length; i++) {
                editBtns[i].onclick = function() {
                    const id = this.getAttribute('data-id');
                    const day = this.getAttribute('data-day');
                    const start = this.getAttribute('data-start');
                    const end = this.getAttribute('data-end');
                    const type = this.getAttribute('data-type');
                    const trainerName = this.getAttribute('data-trainer-name');
                    const status = this.getAttribute('data-status');
                    
                    // Find trainer ID by name
                    const trainerId = findTrainerIdByName(trainerName);
                    
                    document.getElementById('edit_class_id').value = id;
                    document.getElementById('edit_day_of_week').value = day;
                    document.getElementById('edit_start_time').value = start;
                    document.getElementById('edit_end_time').value = end;
                    document.getElementById('edit_class_type').value = type;
                    document.getElementById('edit_trainer_id').value = trainerId;
                    document.getElementById('edit_status').checked = (status == '1');
                    
                    editModal.style.display = "block";
                }
            }
            
            editClose.onclick = function() {
                editModal.style.display = "none";
            }
            
            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target == addModal) {
                    addModal.style.display = "none";
                }
                if (event.target == editModal) {
                    editModal.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>