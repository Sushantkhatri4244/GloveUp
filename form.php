<?php
include_once 'dbConnection.php';
// Initialize error variable
$error = "";
$success = "";
$khalti_pidx = "";
$payment_url = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Collect form data
        $fullName = $_POST['fullName'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $combatSport = $_POST['combatSport'] ?? '';
        
        // Address information
        $permStreetAddress = $_POST['permStreetAddress'] ?? '';
        $permCity = $_POST['permCity'] ?? '';
        $permState = $_POST['permState'] ?? '';
        
        $tempStreetAddress = $_POST['tempStreetAddress'] ?? '';
        $tempCity = $_POST['tempCity'] ?? '';
        $tempState = $_POST['tempState'] ?? '';
        
        // Package selection
        $packageDuration = $_POST['packageDuration'] ?? '';
        
        // Determine amount based on package
        $packageAmount = 0;
        switch ($packageDuration) {
            case 'Monthly':
                $packageAmount = 5200;
                break;
            case '3-Month':
                $packageAmount = 13650;
                break;
            case '6-Month':
                $packageAmount = 23400;
                break;
            case '1-Year':
                $packageAmount = 39000;
                break;
            default:
                $packageAmount = 5200;
        }
        
        // Get payment method
        $paymentMethod = $_POST['paymentMethod'] ?? '';
        
        // Insert data into registrations table
        $conn->beginTransaction();
        
        $sql = "INSERT INTO registrations (full_name, email, phone, dob, gender, combat_sport, 
                perm_street_address, perm_city, perm_state, 
                temp_street_address, temp_city, temp_state, 
                package_duration, created_at) 
                VALUES (:fullName, :email, :phone, :dob, :gender, :combatSport, 
                :permStreetAddress, :permCity, :permState, 
                :tempStreetAddress, :tempCity, :tempState, 
                :packageDuration, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fullName', $fullName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':combatSport', $combatSport);
        $stmt->bindParam(':permStreetAddress', $permStreetAddress);
        $stmt->bindParam(':permCity', $permCity);
        $stmt->bindParam(':permState', $permState);
        $stmt->bindParam(':tempStreetAddress', $tempStreetAddress);
        $stmt->bindParam(':tempCity', $tempCity);
        $stmt->bindParam(':tempState', $tempState);
        $stmt->bindParam(':packageDuration', $packageDuration);
        
        $stmt->execute();
        $registrationId = $conn->lastInsertId();
        
        // Handle payment based on method
        if ($paymentMethod == 'khalti') {
            // Initialize Khalti payment
            $purchaseOrderId = 'GloveUp-' . $registrationId . '-' . time();
            
            // Fix: Full URLs for return_url and website_url
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
            
            // Prepare payment data
            $paymentData = [
                'return_url' => $baseUrl . '/payment_success.php',
                'website_url' => $baseUrl . '/LandingPage.php',
                'amount' => intval($packageAmount * 100), // Khalti requires amount in paisa and as integer
                'purchase_order_id' => $purchaseOrderId,
                'purchase_order_name' => 'GloveUp ' . $packageDuration . ' Membership',
                'customer_info' => [
                    'name' => $fullName,
                    'email' => $email,
                    'phone' => $phone
                ]
            ];
            
            // Debug: Log the payment data
            error_log("Khalti payment data: " . json_encode($paymentData));
            
            // Initialize Khalti payment
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://dev.khalti.com/api/v2/epayment/initiate/');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: key f08adc24ad514a3199917796dea5a419',
                'Content-Type: application/json'
            ]);
            
            // Add error handling for CURL
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            
            $response = curl_exec($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Debug: Log the response and error
            error_log("Khalti response: " . $response);
            if ($curl_error) {
                error_log("CURL Error: " . $curl_error);
            }
            error_log("HTTP Code: " . $http_code);
            
            if ($response && empty($curl_error)) {
                $responseData = json_decode($response, true);
                
                if (isset($responseData['payment_url']) && isset($responseData['pidx'])) {
                    // Save payment information
                    $sql = "INSERT INTO khalti_payments (registration_id, pidx, amount, payment_url, status) 
                            VALUES (:registration_id, :pidx, :amount, :payment_url, 'pending')";
                            
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':registration_id', $registrationId);
                    $stmt->bindParam(':pidx', $responseData['pidx']);
                    $stmt->bindParam(':amount', $packageAmount);
                    $stmt->bindParam(':payment_url', $responseData['payment_url']);
                    $stmt->execute();
                    
                    $khalti_pidx = $responseData['pidx'];
                    $payment_url = $responseData['payment_url'];
                    
                    $conn->commit();
                    $success = "Registration submitted successfully! Redirecting to payment...";
                    // We'll redirect to the payment URL after showing the success message
                } else {
                    $conn->rollBack();
                    // More informative error message
                    $error = "Failed to initialize payment. Error: " . ($responseData['detail'] ?? 'Unknown error from payment gateway');
                    error_log("Khalti error: " . json_encode($responseData));
                }
            } else {
                $conn->rollBack();
                $error = "Failed to connect to payment gateway. " . ($curl_error ? "Error: " . $curl_error : "Please try again.");
            }
        } else if ($paymentMethod == 'receipt') {
            // File upload handling for receipt method
            $paymentImage = '';
            if(isset($_FILES['paymentImage']) && $_FILES['paymentImage']['error'] == 0) {
                $target_dir = "uploads/";
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $target_file = $target_dir . basename($_FILES["paymentImage"]["name"]);
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                
                // Check if file is an actual image
                $check = getimagesize($_FILES["paymentImage"]["tmp_name"]);
                if($check !== false) {
                    // Generate unique filename
                    $paymentImage = $target_dir . uniqid() . '.' . $imageFileType;
                    move_uploaded_file($_FILES["paymentImage"]["tmp_name"], $paymentImage);
                    
                    // Update registration with payment image
                    $sql = "UPDATE registrations SET payment_image = :paymentImage WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':paymentImage', $paymentImage);
                    $stmt->bindParam(':id', $registrationId);
                    $stmt->execute();
                    
                    $conn->commit();
                    $success = "Registration submitted successfully! We'll contact you soon.";
                } else {
                    $conn->rollBack();
                    $error = "File is not an image.";
                }
            } else {
                $conn->rollBack();
                $error = "Please upload your payment receipt.";
            }
        }
    } catch(PDOException $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        $error = "Registration error: " . $e->getMessage();
        error_log("PDO Exception: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - GloveUp Boxing Training Center</title>
    <link rel="stylesheet" href="form.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        // Function to redirect to Khalti payment
        function redirectToPayment(url) {
            if (url) {
                window.location.href = url;
            }
        }
    </script>
</head>

<body>
    <nav class="navbar">
        <div class="logo">GloveUp</div>
        <ul class="nav-links">
            <li><a href="LandingPage.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="classes.php">Classes</a></li>
            <li><a href="athlete.php">Athletes</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
        <div class="auth-buttons">
            <a href="Login.php"><button class="login-btn">Login</button></a>
            <a href="Register.php" class="active"><button class="signup-btn">Sign Up</button></a>
        </div>
    </nav>

    <div class="main-container">
        <h1 class="page-title">Registration Form</h1>
        
        <?php if(!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
            <?php if(!empty($payment_url)): ?>
                <script>
                    // Wait 2 seconds before redirecting to payment
                    setTimeout(function() {
                        redirectToPayment('<?php echo $payment_url; ?>');
                    }, 2000);
                </script>
            <?php endif; ?>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" class="registration-form">
            <!-- Personal Information -->
            <div class="form-section">
                <h2>Personal Information</h2>
                
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" placeholder="mm/dd/yyyy" required>
                </div>
                
                <div class="form-group">
                    <label>Gender</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="Male" required>
                            Male
                        </label>
                        <label>
                            <input type="radio" name="gender" value="Female">
                            Female
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Interested Combat Sports</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="combatSport" value="Boxing" checked>
                            Boxing
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Rest of the form remains unchanged -->
            <!-- Permanent Address -->
            <div class="form-section">
                <h2>Permanent Address</h2>
                
                <div class="form-group">
                    <label for="permStreetAddress">Street Address</label>
                    <input type="text" id="permStreetAddress" name="permStreetAddress" required>
                </div>
                
                <div class="form-group">
                    <label for="permCity">City</label>
                    <input type="text" id="permCity" name="permCity" required>
                </div>
                
                <div class="form-group">
                    <label for="permState">State/Province</label>
                    <input type="text" id="permState" name="permState" required>
                </div>
            </div>
            
            <!-- Temporary Address -->
            <div class="form-section">
                <h2>Temporary Address</h2>
                
                <div class="form-group">
                    <label for="tempStreetAddress">Street Address</label>
                    <input type="text" id="tempStreetAddress" name="tempStreetAddress">
                </div>
                
                <div class="form-group">
                    <label for="tempCity">City</label>
                    <input type="text" id="tempCity" name="tempCity">
                </div>
                
                <div class="form-group">
                    <label for="tempState">State/Province</label>
                    <input type="text" id="tempState" name="tempState">
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="form-section">
                <h2>Payment Information</h2>
                
                <div class="form-group">
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>Package Duration</th>
                                <th>Admission Fee</th>
                                <th>Package Price</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Monthly Membership</td>
                                <td>Free(Offer)</td>
                                <td>NPR 5,200 (20% off)</td>
                                <td><input type="radio" name="packageDuration" value="Monthly" required></td>
                            </tr>
                            <tr>
                                <td>3-Month Membership</td>
                                <td>Free(Offer)</td>
                                <td>NPR 13,650 (30% off)</td>
                                <td><input type="radio" name="packageDuration" value="3-Month"></td>
                            </tr>
                            <tr>
                                <td>6-Month Membership</td>
                                <td>Free(Offer)</td>
                                <td>NPR 23,400 (40% off)</td>
                                <td><input type="radio" name="packageDuration" value="6-Month"></td>
                            </tr>
                            <tr>
                                <td>1-Year Membership</td>
                                <td>Free(Offer)</td>
                                <td>NPR 39,000 (50% off)</td>
                                <td><input type="radio" name="packageDuration" value="1-Year"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="radio-group payment-methods">
                        <label>
                            <input type="radio" name="paymentMethod" value="khalti" required>
                            Pay with Khalti
                        </label>
                       
                    </div>
                </div>
                
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-btn">SUBMIT APPLICATION</button>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
    
    <script>
        // Toggle receipt upload section based on payment method selection
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethodRadios = document.querySelectorAll('input[name="paymentMethod"]');
            const receiptUploadSection = document.getElementById('receiptUploadSection');
            
            paymentMethodRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'receipt') {
                        receiptUploadSection.style.display = 'block';
                    } else {
                        receiptUploadSection.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>