<?php
session_start(); // Added session start to access session variables
include_once 'dbConnection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'admin') {
    // Redirect to login page if not logged in as admin
    $_SESSION['error_message'] = "You must be logged in as an admin to access this page.";
    header("Location: login.php");
    exit();
}

// Initialize variables
$message = '';
$messageType = '';
$result = null;
$totalUsers = 0;
$totalPages = 0;

// Initialize variables for pagination
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$startIndex = ($currentPage - 1) * $itemsPerPage;

// Check if database connection is established
if (!isset($conn) || $conn === null) {
    $message = "Database connection failed. Please check your database settings in dbConnection.php.";
    $messageType = "danger";
} else {
    // Edit user
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $role = $_POST['role'];
        
        try {
            $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $message = "Role updated successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }

    // Delete user
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $message = "User deleted successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }

    // Fetch users with pagination and search
    $params = [];
    $searchCondition = '';
    if ($search != '') {
        $searchCondition = " WHERE full_name LIKE :search OR 
                            email LIKE :search OR 
                            phone_number LIKE :search OR 
                            role LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Get total users count
    try {
        $countSql = "SELECT COUNT(*) as total FROM users" . $searchCondition;
        $countStmt = $conn->prepare($countSql);
        
        if (!empty($params)) {
            $countStmt->execute($params);
        } else {
            $countStmt->execute();
        }
        
        $totalUsers = $countStmt->fetch()['total'];
        $totalPages = ceil($totalUsers / $itemsPerPage);
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
        $totalUsers = 0;
        $totalPages = 0;
    }

    // Get users for current page
    try {
        $sql = "SELECT * FROM users" . $searchCondition . " ORDER BY id DESC LIMIT :offset, :limit";
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->bindParam(':offset', $startIndex, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt;
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
        $result = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GloveUp Gym - Users Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
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
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="p-3">GloveUp </h4>
        <a href="adminoveralldashboard.php" class="py-2"><i class="fas fa-tachometer-alt icon"></i> Dashboard</a>
        <a href="user.php" class="py-2 active"><i class="fas fa-user-tag icon"></i> Roles</a>
        <a href="classesdashboard.php" class="py-2"><i class="fas fa-dumbbell icon"></i> Classes</a>
        <a href="athletedashboard.php" class="py-2"><i class="fas fa-medal icon"></i> Athletes</a>
        <!-- <a href="#" class="py-2"><i class="fas fa-cog icon"></i> Settings</a> -->
        <a href="logout.php" class="py-2"><i class="fas fa-sign-out-alt icon"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content-area">
        <div class="header">
            <h2>Users Management</h2>
            <div class="user-info">
                <!-- Changed static "Admin User" to dynamic display of admin name -->
                <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Users Directory</h5>
                
                <?php if (isset($pdo) && $pdo !== null): // Only show add user button if database connection is working ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus-circle"></i> Add User
                        </button>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="">
                            <div class="input-group">
                                <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Registered At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result && $result->rowCount() > 0) {
                                while ($row = $result->fetch()) {
                                    $roleBadgeClass = '';
                                    switch ($row['role']) {
                                        case 'Admin':
                                            $roleBadgeClass = 'bg-danger';
                                            break;
                                        case 'Coach':
                                            $roleBadgeClass = 'bg-warning';
                                            break;
                                        case 'Member':
                                            $roleBadgeClass = 'bg-success';
                                            break;
                                        default:
                                            $roleBadgeClass = 'bg-secondary';
                                    }
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                <td>
                                    <span class="badge <?php echo $roleBadgeClass; ?>"><?php echo htmlspecialchars($row['role']); ?></span>
                                </td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($row['registered_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-user" data-id="<?php echo $row['id']; ?>" 
                                            data-bs-toggle="modal" data-bs-target="#viewUserModal"
                                            data-name="<?php echo htmlspecialchars($row['full_name']); ?>"
                                            data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                            data-phone="<?php echo htmlspecialchars($row['phone_number']); ?>"
                                            data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                            data-registered="<?php echo date('Y-m-d H:i:s', strtotime($row['registered_at'])); ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary edit-user" data-id="<?php echo $row['id']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-name="<?php echo htmlspecialchars($row['full_name']); ?>"
                                            data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                            data-phone="<?php echo htmlspecialchars($row['phone_number']); ?>"
                                            data-role="<?php echo htmlspecialchars($row['role']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $row['id']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                                            data-name="<?php echo htmlspecialchars($row['full_name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <?php 
                                        if (!isset($pdo) || $pdo === null) {
                                            echo "Database connection error. Please check your database settings.";
                                        } else {
                                            echo "No users found";
                                        } 
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div id="pagination" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade user-modal" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm" method="POST" action="">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="edit_role">Role</label>
                            <select class="form-control" id="edit_role" name="role" required>
                                <option value="Member">Member</option>
                                <option value="Coach">Coach</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete user <span id="delete_user_name"></span>? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <form id="deleteUserForm" method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="delete_id" name="id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Details Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>ID:</th>
                                        <td id="view_id"></td>
                                    </tr>
                                    <tr>
                                        <th>Name:</th>
                                        <td id="view_name"></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td id="view_email"></td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td id="view_phone"></td>
                                    </tr>
                                    <tr>
                                        <th>Role:</th>
                                        <td id="view_role"></td>
                                    </tr>
                                    <tr>
                                        <th>Registered:</th>
                                        <td id="view_registered"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $(document).on('click', '.password-toggle', function() {
                const passwordField = $(this).siblings('input');
                const fieldType = passwordField.attr('type');
                
                if (fieldType === 'password') {
                    passwordField.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // View user details
            $(document).on('click', '.view-user', function() {
                const userId = $(this).data('id');
                const userName = $(this).data('name');
                const userEmail = $(this).data('email');
                const userPhone = $(this).data('phone');
                const userRole = $(this).data('role');
                const userRegistered = $(this).data('registered');
                
                $('#view_id').text(userId);
                $('#view_name').text(userName);
                $('#view_email').text(userEmail);
                $('#view_phone').text(userPhone);
                $('#view_role').text(userRole);
                $('#view_registered').text(userRegistered);
            });
            
            // Edit user
            $(document).on('click', '.edit-user', function() {
                const userId = $(this).data('id');
                const userName = $(this).data('name');
                const userEmail = $(this).data('email');
                const userPhone = $(this).data('phone');
                const userRole = $(this).data('role');
                
                $('#edit_id').val(userId);
                $('#edit_role').val(userRole);
            });
            
            // Delete user
            $(document).on('click', '.delete-user', function() {
                const userId = $(this).data('id');
                const userName = $(this).data('name');
                
                $('#delete_id').val(userId);
                $('#delete_user_name').text(userName);
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>