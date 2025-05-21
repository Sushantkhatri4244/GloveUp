<?php session_start(); ?> 
<!DOCTYPE html> 
<html lang="en"> 
<head>   
  <meta charset="UTF-8" />   
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>   
  <title>Login - GloveUp</title>   
  <link rel="stylesheet" href="Login.css" /> 
  <link rel="stylesheet" href="alert-styles.css" />
</head> 
<body>   
  <div class="container">
    <!-- Display success messages -->   
    <?php if (isset($_SESSION['success_message'])): ?>   
    <div class="alert alert-success alert-dismissible fade show">     
      <?php
        echo $_SESSION['success_message'];
        unset($_SESSION['success_message']);
      ?>     
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">&times;</button>   
    </div>   
    <?php endif; ?>      
    
    <!-- Display logout messages -->
    <?php if (isset($_SESSION['logout_message'])): ?>   
    <div class="alert alert-<?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?> alert-dismissible fade show">     
      <?php
        echo $_SESSION['logout_message'];
        unset($_SESSION['logout_message']);
      ?>     
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">&times;</button>   
    </div>   
    <?php endif; ?>      
    
    <!-- Display error messages -->
    <?php if (isset($_SESSION['error_message'])): ?>   
    <div class="alert alert-danger alert-dismissible fade show">     
      <?php
        echo $_SESSION['error_message'];
        unset($_SESSION['error_message']);
      ?>     
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">&times;</button>   
    </div>   
    <?php endif; ?>
    
    <!-- Clean up message_type if it exists but is no longer needed -->
    <?php if (isset($_SESSION['message_type'])) unset($_SESSION['message_type']); ?>
    
    <h2>Login</h2>     
    <p>Welcome back!<br>Login to your account</p>          
    
    <form id="loginForm" action="login-process.php" method="post">       
      <!-- Email or Mobile -->       
      <label for="emailOrMobile">Email or Mobile</label>       
      <input         
        type="text"         
        id="emailOrMobile"         
        name="emailOrMobile"         
        placeholder="example@gmail.com or +977XXXXXXXXXX"         
        pattern="^(\+977[0-9]{10}|[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$"         
        required       
      />              
      
      <!-- Password -->       
      <label for="password">Password</label>       
      <div class="password-wrapper">         
        <input           
          type="password"           
          id="password"           
          name="password"           
          placeholder="********"           
          minlength="6"           
          required         
        />         
        <span class="toggle-password" id="togglePassword">👁️</span>       
      </div>              
      
      <div class="forgot-password">         
        <a href="forgot-password.php">Forget Password?</a>       
      </div>              
      
      <button type="submit" class="register-btn">Login</button>     
    </form>          
    
    <p class="login-link">       
      Don't have an account? <a href="register.php">Sign Up</a>     
    </p>   
  </div>      
  
  <script>     
    const togglePassword = document.getElementById("togglePassword");     
    const passwordField = document.getElementById("password");          
    
    togglePassword.addEventListener("click", function () {       
      passwordField.type = passwordField.type === "password" ? "text" : "password";     
    });
    
    // Close alert messages when the close button is clicked
    document.querySelectorAll('.btn-close').forEach(button => {
      button.addEventListener('click', function() {
        this.parentElement.style.display = 'none';
      });
    });
  </script> 
</body> 
</html>