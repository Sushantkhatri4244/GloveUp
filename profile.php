<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login
    $_SESSION['error_message'] = "Please login to view your profile.";
    $_SESSION['message_type'] = "danger";
    header("Location: login.php");
    exit();
}

// Include database connection
include_once 'dbConnection.php';

// Get user data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    $_SESSION['message_type'] = "danger";
    header("Location: landingPage.php");
    exit();
}

// Include header
include_once 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GloveUp</title>
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-header h1 {
            color: #333;
        }
        
        .profile-info {
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .info-label {
            width: 150px;
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            flex: 1;
        }
        
        .edit-profile-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #ff9900;
            color: white;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        
        .edit-profile-btn:hover {
            background-color: #e68a00;
        }

        .membership-info {
            margin-top: 40px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }

        .membership-info h2 {
            color: #ff9900;
            margin-bottom: 20px;
        }

        .class-history {
            margin-top: 40px;
        }

        .class-history h2 {
            margin-bottom: 20px;
        }

        .class-card {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <p>Welcome to your personal profile page</p>
        </div>
        
        <div class="profile-info">
            <div class="info-item">
                <div class="info-label">Full Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Phone Number:</div>
                <div class="info-value"><?php echo htmlspecialchars($user['phone_number']); ?></div>
            </div>
            
            <!-- Add more fields as needed -->
        </div>
        
        <a href="edit-profile.php" class="edit-profile-btn">Edit Profile</a>
        
        <div class="membership-info">
            <h2>Membership Information</h2>
            <p>Membership Type: <strong>Premium</strong></p>
            <p>Expiration Date: <strong>December 31, 2025</strong></p>
            <p>Status: <strong>Active</strong></p>
        </div>
        
        <div class="class-history">
            <h2>Recent Classes</h2>
            <div class="class-card">
                <div>Boxing with Sushant Khatri</div>
                <div>May 18, 2025</div>
            </div>
            <div class="class-card">
                <div>Boxing with Sushant Khatri</div>
                <div>May 15, 2025</div>
            </div>
            <div class="class-card">
                <div>Boxing with Sushant Khatri</div>
                <div>May 12, 2025</div>
            </div>
        </div>
    </div>

<?php
include_once 'footer.php';
?>
</body>
</html>