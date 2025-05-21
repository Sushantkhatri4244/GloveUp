<?php
// Include database connection
include_once 'dbConnection.php';

// Initialize variables
$success = false;
$message = '';
$transactionDetails = [];
$registrationDetails = [];

// Check if the required parameters exist in the URL
if(isset($_GET['pidx']) && isset($_GET['transaction_id'])) {
    $pidx = $_GET['pidx'];
    $transaction_id = $_GET['transaction_id'];
    
    try {
        // Verify the payment with Khalti API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://dev.khalti.com/api/v2/epayment/lookup/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key f08adc24ad514a3199917796dea5a419',
            'Content-Type: application/json'
        ]);
        
        $data = json_encode(['pidx' => $pidx]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if($response) {
            $responseData = json_decode($response, true);
            
            // Check if payment is completed
            if(isset($responseData['status']) && $responseData['status'] === 'Completed') {
                // Update payment status in database
                $sql = "UPDATE khalti_payments SET status = 'completed', transaction_id = :transaction_id, 
                        updated_at = NOW() WHERE pidx = :pidx";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':transaction_id', $transaction_id);
                $stmt->bindParam(':pidx', $pidx);
                $stmt->execute();
                
                // Get payment and registration details
                $sql = "SELECT kp.*, r.full_name, r.email, r.package_duration 
                        FROM khalti_payments kp 
                        JOIN registrations r ON kp.registration_id = r.id 
                        WHERE kp.pidx = :pidx";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':pidx', $pidx);
                $stmt->execute();
                
                if($stmt->rowCount() > 0) {
                    $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);
                    $success = true;
                    $message = "Payment successful!";
                    $transactionDetails = [
                        'transaction_id' => $transaction_id,
                        'amount' => $paymentData['amount'],
                        'payment_date' => date('Y-m-d H:i:s')
                    ];
                    $registrationDetails = [
                        'name' => $paymentData['full_name'],
                        'email' => $paymentData['email'],
                        'package' => $paymentData['package_duration']
                    ];
                    
                    // Send confirmation email (you can implement this part)
                    // sendConfirmationEmail($registrationDetails['email'], $registrationDetails, $transactionDetails);
                } else {
                    $message = "Payment record not found.";
                }
            } else {
                $message = "Payment verification failed. Status: " . ($responseData['status'] ?? 'Unknown');
            }
        } else {
            $message = "Failed to verify payment with Khalti.";
        }
    } catch(PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    } catch(Exception $e) {
        $message = "Error processing payment verification: " . $e->getMessage();
    }
} else {
    $message = "Missing required payment parameters.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - GloveUp Boxing Training Center</title>
    <link rel="stylesheet" href="form.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .payment-success-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .payment-success-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .payment-success-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .payment-receipt {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .receipt-row:last-child {
            border-bottom: none;
        }
        
        .receipt-label {
            font-weight: bold;
            color: #555;
        }
        
        .receipt-value {
            color: #333;
        }
        
        .payment-action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background-color: #3f51b5;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #303f9f;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid #3f51b5;
            color: #3f51b5;
        }
        
        .btn-outline:hover {
            background-color: #f0f2ff;
        }
        
        .payment-error {
            text-align: center;
            color: #d32f2f;
            padding: 20px;
            background-color: #ffebee;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .payment-error i {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .payment-success-container {
                margin: 20px;
                padding: 15px;
            }
            
            .payment-action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
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
            <a href="Register.php"><button class="signup-btn">Sign Up</button></a>
        </div>
    </nav>

    <div class="main-container">
        <div class="payment-success-container">
            <?php if($success): ?>
                <div class="payment-success-header">
                    <i class="fas fa-check-circle payment-success-icon"></i>
                    <h2>Payment Successful!</h2>
                    <p>Thank you for registering with GloveUp Boxing Training Center.</p>
                </div>
                
                <div class="payment-receipt">
                    <h3>Payment Receipt</h3>
                    
                    <div class="receipt-row">
                        <span class="receipt-label">Member Name:</span>
                        <span class="receipt-value"><?php echo htmlspecialchars($registrationDetails['name']); ?></span>
                    </div>
                    
                    <div class="receipt-row">
                        <span class="receipt-label">Package:</span>
                        <span class="receipt-value"><?php echo htmlspecialchars($registrationDetails['package']); ?> Membership</span>
                    </div>
                    
                    <div class="receipt-row">
                        <span class="receipt-label">Amount Paid:</span>
                        <span class="receipt-value">NPR <?php echo number_format($transactionDetails['amount'], 2); ?></span>
                    </div>
                    
                    <div class="receipt-row">
                        <span class="receipt-label">Transaction ID:</span>
                        <span class="receipt-value"><?php echo htmlspecialchars($transactionDetails['transaction_id']); ?></span>
                    </div>
                    
                    <div class="receipt-row">
                        <span class="receipt-label">Payment Date:</span>
                        <span class="receipt-value"><?php echo htmlspecialchars($transactionDetails['payment_date']); ?></span>
                    </div>
                </div>
                
                <div class="next-steps">
                    <h3>What's Next?</h3>
                    <ul>
                        <li>A confirmation email has been sent to your registered email address.</li>
                        <li>Please visit our center with a photo ID to complete your registration.</li>
                        <li>Our trainer will schedule an orientation session for you.</li>
                        <li>You will receive your membership card during your first visit.</li>
                    </ul>
                </div>
                
                <div class="payment-action-buttons">
                    <a href="LandingPage.php" class="btn btn-primary">Return to Homepage</a>
                    <button class="btn btn-outline" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            <?php else: ?>
                <div class="payment-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <h2>Payment Verification Issue</h2>
                    <p><?php echo $message; ?></p>
                </div>
                
                <div class="payment-action-buttons">
                    <a href="LandingPage.php" class="btn btn-primary">Return to Homepage</a>
                    <a href="Register.php" class="btn btn-outline">Try Again</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>