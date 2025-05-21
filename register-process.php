<?php
// Database connection
include_once 'dbConnection.php';

try {
    // Get and sanitize inputs
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($fullname) || empty($password) || (empty($email) && empty($phone))) {
        die("All fields are required.");
    }
    
    // Check for duplicate email or phone
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email OR phone_number = :phone");
    $stmt->execute(['email' => $email, 'phone' => $phone]);
        
    if ($stmt->rowCount() > 0) {
        die("Email or phone number already registered. Try logging in.");
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $insert = $conn->prepare("INSERT INTO users (full_name, email, phone_number, password) 
                             VALUES (:fullname, :email, :phone, :password)");
    $insert->execute([
        'fullname' => $fullname,
        'email'    => $email,
        'phone'    => $phone,
        'password' => $hashedPassword
    ]);
    
    // Set a success message in session
    session_start();
    $_SESSION['success_message'] = "Registration successful! Please log in with your credentials.";
    $_SESSION['message_type'] = "success";
    
    // Redirect to login page
    header("Location: Login.php");
    exit;
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>