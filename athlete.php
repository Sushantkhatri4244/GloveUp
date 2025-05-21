<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'header.php';


// Get all athletes sorted by ID
$athletes = [];
try {
    $sql = "SELECT * FROM athletes ORDER BY id DESC";
    $stmt = $conn->query($sql);
    $athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error quietly
    error_log("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Athletes - GloveUp</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #000;
        }
        .btn-login {
            background-color: #f8a100;
            color: white;
            border: none;
        }
        .btn-signup {
            background-color: #f8a100;
            color: white;
            border: none;
        }
        .page-title {
            color: #dc3545;
            text-align: center;
            margin: 30px 0;
            font-weight: bold;
        }
        .athlete-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            padding: 15px;
        }
        .athlete-card:hover {
            transform: translateY(-5px);
        }
        .athlete-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }
        .athlete-name {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }

        .athlete-description {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: 40px;
            border-top: 1px solid #e9ecef;
        }
        .connect-section {
            margin-bottom: 20px;
        }
        .connect-title {
            color: #f8a100;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .location-title {
            color: #f8a100;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .social-icons {
            margin-top: 10px;
        }
        .social-icons a {
            color: #6c757d;
            margin-right: 10px;
            font-size: 18px;
        }
        .map-container {
            height: 200px;
            width: 100%;
            border-radius: 4px;
            overflow: hidden;
        }
        .copyright {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 14px;
        }
             /* Auth buttons styles */
        .auth-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .auth-button, .login-btn, .signup-btn, .logout-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-btn, .signup-btn {
            background-color: #ff9900;
            color: white;
        }
        
        .logout-btn {
            background-color: orange;
            color: white;
        }
        
        .user-name {
            font-weight: 500;
            margin-right: 10px;
        }
    </style>
</head>
<body>


    <!-- Athletes Section -->
    <div class="container">
        <h1 class="page-title">Athletes</h1>
        
        <div class="row">
            <?php if (!empty($athletes)): ?>
                <?php foreach ($athletes as $athlete): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="athlete-card">
                            <?php if (!empty($athlete['image_path']) && file_exists($athlete['image_path'])): ?>
                                <img src="<?php echo $athlete['image_path']; ?>" alt="<?php echo $athlete['name']; ?>" class="athlete-img">
                            <?php else: ?>
                                <img src="assets/images/default-athlete.jpg" alt="Default Athlete" class="athlete-img">
                            <?php endif; ?>
                            <h5 class="athlete-name"><?php echo $athlete['name']; ?></h5>
                            <h6 class="athlete-description"><?php echo $athlete['description']; ?></h6>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No athletes found. Check back soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
   <?php
   include_once 'footer.php';
   ?>
         

    <!-- jQuery -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> -->
    <!-- Bootstrap JS -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>