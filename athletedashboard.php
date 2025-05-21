<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection 
include_once 'dbConnection.php';

// Check if user is not logged in as admin, redirect to login page
// Uncomment this when your login system is ready
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: login.php");
//     exit();
// }

// Debug function
function debug_to_console($data) {
    echo '<script>console.log(' . json_encode($data) . ');</script>';
}

// Process form submission for adding or updating athletes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // Add new athlete
        if ($_POST['action'] === 'add') {
            $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
            $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
            
            // File upload handling
            $target_dir = "uploads/athletes/";
            $image_path = "";
            
            // Check if directory exists, if not create it
            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    $_SESSION['error_message'] = "Failed to create upload directory";
                    header("Location: athletedashboard.php");
                    exit();
                }
            }
            
            // Make sure the directory is writable
            if (!is_writable($target_dir)) {
                chmod($target_dir, 0777);
            }
            
            // Debug file upload info
            debug_to_console($_FILES);
            
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                $original_filename = basename($_FILES["image"]["name"]);
                $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                
                // Generate unique filename
                $new_filename = uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $new_filename;
                
                // Check file type
                $allowed_types = array("jpg", "jpeg", "png", "gif");
                if (in_array($imageFileType, $allowed_types)) {
                    // Try to upload the file
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_path = $target_file;
                        debug_to_console("File successfully uploaded to: " . $target_file);
                    } else {
                        $upload_error = error_get_last();
                        debug_to_console("File upload failed: " . ($upload_error ? $upload_error['message'] : 'Unknown error'));
                        $_SESSION['error_message'] = "Sorry, there was an error uploading your file. Error: " . 
                            ($_FILES["image"]["error"] == 0 ? "Unknown error" : $_FILES["image"]["error"]);
                    }
                } else {
                    $_SESSION['error_message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else if (isset($_FILES["image"]) && $_FILES["image"]["error"] != 0) {
                // If there was an error with the upload
                switch ($_FILES["image"]["error"]) {
                    case UPLOAD_ERR_INI_SIZE:
                        $_SESSION['error_message'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $_SESSION['error_message'] = "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $_SESSION['error_message'] = "The uploaded file was only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        // No file was uploaded but that's ok
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $_SESSION['error_message'] = "Missing a temporary folder.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $_SESSION['error_message'] = "Failed to write file to disk.";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $_SESSION['error_message'] = "A PHP extension stopped the file upload.";
                        break;
                    default:
                        $_SESSION['error_message'] = "Unknown upload error.";
                        break;
                }
            }
            
            // Use PDO consistently
            try {
                $sql = "INSERT INTO athletes (name, description, image_path) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$name, $description, $image_path]);
                
                if ($result) {
                    $_SESSION['success_message'] = "Athlete added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding athlete.";
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
            
            // Redirect to refresh the page
            header("Location: athletedashboard.php");
            exit();
        }
        
        // Update existing athlete
        elseif ($_POST['action'] === 'update') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
            $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
            
            // Check if a new image was uploaded
            $target_dir = "uploads/athletes/";
            $image_path = isset($_POST['current_image']) ? $_POST['current_image'] : '';
            
            // Check if directory exists, if not create it
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Make sure the directory is writable
            if (!is_writable($target_dir)) {
                chmod($target_dir, 0777);
            }
            
            // Debug file upload info
            debug_to_console($_FILES);
            
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                $original_filename = basename($_FILES["image"]["name"]);
                $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                
                // Generate unique filename
                $new_filename = uniqid() . '.' . $imageFileType;
                $target_file = $target_dir . $new_filename;
                
                // Check file type
                $allowed_types = array("jpg", "jpeg", "png", "gif");
                if (in_array($imageFileType, $allowed_types)) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        // Delete old image if it exists
                        if (!empty($_POST['current_image']) && file_exists($_POST['current_image'])) {
                            unlink($_POST['current_image']);
                        }
                        $image_path = $target_file;
                        debug_to_console("File successfully uploaded to: " . $target_file);
                    } else {
                        $upload_error = error_get_last();
                        debug_to_console("File upload failed: " . ($upload_error ? $upload_error['message'] : 'Unknown error'));
                        $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $_SESSION['error_message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else if (isset($_FILES["image"]) && $_FILES["image"]["error"] != 0 && $_FILES["image"]["error"] != 4) {
                // If there was an error with the upload (except UPLOAD_ERR_NO_FILE which is ok)
                switch ($_FILES["image"]["error"]) {
                    case UPLOAD_ERR_INI_SIZE:
                        $_SESSION['error_message'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $_SESSION['error_message'] = "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $_SESSION['error_message'] = "The uploaded file was only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $_SESSION['error_message'] = "Missing a temporary folder.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $_SESSION['error_message'] = "Failed to write file to disk.";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $_SESSION['error_message'] = "A PHP extension stopped the file upload.";
                        break;
                    default:
                        $_SESSION['error_message'] = "Unknown upload error.";
                        break;
                }
            }
            
            // Use PDO consistently for update
            try {
                $sql = "UPDATE athletes SET name = ?, description = ?, image_path = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$name, $description, $image_path, $id]);
                
                if ($result) {
                    $_SESSION['success_message'] = "Athlete updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating athlete.";
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
            
            // Redirect to refresh the page
            header("Location: athletedashboard.php");
            exit();
        }
        
        // Delete athlete
        elseif ($_POST['action'] === 'delete') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            // Use PDO consistently for delete
            try {
                // Get the image path before deleting
                $sql = "SELECT image_path FROM athletes WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row) {
                    $image_path = $row['image_path'];
                    
                    // Delete the image file if it exists
                    if (!empty($image_path) && file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                // Delete from database
                $sql = "DELETE FROM athletes WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$id]);
                
                if ($result) {
                    $_SESSION['success_message'] = "Athlete deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting athlete.";
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            }
            
            // Redirect to refresh the page
            header("Location: athletedashboard.php");
            exit();
        }
    }
}

// Get all athletes - fixed to use PDO consistently
$athletes = [];
try {
    $sql = "SELECT * FROM athletes ORDER BY id DESC";
    $stmt = $conn->query($sql);
    $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Athlete Management</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    <style>
        body {
            padding-top: px;
        }
        .content-wrapper {
            padding: 20px;
        }
        .custom-file-label::after {
            content: "Browse";
        }
        .img-thumbnail {
            object-fit: cover;
            width: 50px;
            height: 50px;
        }
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
    
    </style>
</head>
<body>
<div class="sidebar">
        <h4 class="p-3">GloveUp </h4>
        <a href="adminoveralldashboard.php" class="py-2"><i class="fas fa-tachometer-alt icon"></i> Dashboard</a>
        <a href="user.php" class="py-2 active"><i class="fas fa-user-tag icon"></i> Roles</a>
        <a href="classesdashboard.php" class="py-2"><i class="fas fa-dumbbell icon"></i> Classes</a>
        <a href="athletedashboard.php" class="py-2"><i class="fas fa-medal icon"></i> Athletes</a>
        <!-- <a href="#" class="py-2"><i class="fas fa-cog icon"></i> Settings</a> -->
        <a href="logout.php" class="py-2"><i class="fas fa-sign-out-alt icon"></i> Logout</a>
    </div>
    <div class="container">
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Athlete Management</h1>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <!-- Display messages -->
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?php 
                                echo $_SESSION['success_message']; 
                                unset($_SESSION['success_message']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?php 
                                echo $_SESSION['error_message']; 
                                unset($_SESSION['error_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add Athlete Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAthleteModal">
                            <i class="fas fa-plus"></i> Add New Athlete
                        </button>
                    </div>

                    <!-- Athletes Table -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Athletes List</h3>
                        </div>
                        <div class="card-body">
                            <table id="athletesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($athletes)): ?>
                                        <?php foreach ($athletes as $row): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td>
                                                    <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                                                        <img src="<?php echo $row['image_path']; ?>" alt="<?php echo $row['name']; ?>" class="img-thumbnail">
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">No Image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo substr($row['description'], 0, 50) . (strlen($row['description']) > 50 ? '...' : ''); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info edit-athlete" 
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                                                        data-description="<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>"
                                                        data-image="<?php echo $row['image_path']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-athlete" 
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No athletes found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Add Athlete Modal -->
    <div class="modal fade" id="addAthleteModal" tabindex="-1" role="dialog" aria-labelledby="addAthleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAthleteModalLabel">Add New Athlete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="name">Athlete Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Athlete Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                <label class="custom-file-label" for="image">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Recommended size: 300x300 pixels</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Athlete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Athlete Modal -->
    <div class="modal fade" id="editAthleteModal" tabindex="-1" role="dialog" aria-labelledby="editAthleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAthleteModalLabel">Edit Athlete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="current_image" id="current_image">
                        
                        <div class="form-group">
                            <label for="edit_name">Athlete Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Current Image</label>
                            <div id="image_preview" class="mb-2"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_image">New Image (leave empty to keep current image)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="edit_image" name="image" accept="image/*">
                                <label class="custom-file-label" for="edit_image">Choose file</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Athlete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Athlete Modal -->
    <div class="modal fade" id="deleteAthleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteAthleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAthleteModalLabel">Delete Athlete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Are you sure you want to delete the athlete: <strong id="delete_name"></strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#athletesTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false
        });
        
        // Update file input label with filename
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            
            // Optional: Preview the image
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    if ($(this).attr('id') === 'edit_image') {
                        $('#image_preview').html('<img src="' + e.target.result + '" class="img-thumbnail" width="150">');
                    }
                }.bind(this);
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Handle edit button click
        $('.edit-athlete').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var description = $(this).data('description');
            var image = $(this).data('image');
            
            $('#edit_id').val(id);
            $('#edit_name').val(name);
            $('#edit_description').val(description);
            $('#current_image').val(image);
            
            // Display current image
            if (image && image !== "") {
                $('#image_preview').html('<img src="' + image + '" class="img-thumbnail" width="150">');
            } else {
                $('#image_preview').html('<span class="badge badge-secondary">No Image</span>');
            }
            
            $('#editAthleteModal').modal('show');
        });
        
        // Handle delete button click
        $('.delete-athlete').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#delete_id').val(id);
            $('#delete_name').text(name);
            
            $('#deleteAthleteModal').modal('show');
        });
        
        // Add debug info to check if the file input is working
        $('#image, #edit_image').on('change', function() {
            console.log("File selected:", this.files[0]);
        });
    });
    </script>
</body>
</html>