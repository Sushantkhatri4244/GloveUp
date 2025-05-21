
<?php
include_once 'dbConnection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GloveUp - Boxing Training Center</title>
    <link rel="stylesheet" href="LandingPage.css" />
</head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<body>
     <nav class="navbar">
        <a href="landingPage.php" class="logo">GloveUp</a>
        
        <ul class="nav-links">
            <li><a href="landingPage.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="classes.php">Classes</a></li>
            <li><a href="athlete.php">Athlete</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
        
        <div class="auth-buttons">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php"><button class="auth-button logout-btn">Logout</button></a>
                <?php if(isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'admin'): ?>
                    <a href="user.php"><button class="auth-button">Admin</button></a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php"><button class="auth-button login-btn">Login</button></a>
                <a href="register.php"><button class="auth-button signup-btn">Sign Up</button></a>
            <?php endif; ?>
        </div>
    </nav>