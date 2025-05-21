
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <link rel="stylesheet" href="Register.css" />
  <style>
    .error {
      color: red;
      font-size: 0.9em;
      display: none;
    }
    .password-wrapper {
      position: relative;
    }
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
    }
  </style>
</head>

<body class="register-page">
  <div class="register-container">
    <h2>Register</h2>
    <p>Let's Get Started<br>Create an account</p>

    <form id="registerForm" action="register-process.php" method="post">
      <label for="fullname">Full Name</label>
      <input type="text" id="fullname" name="fullname" placeholder="Ex. Earl J. Smiley" pattern="^[A-Za-z\s]+$" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="example@gmail.com" required>

      <div class="divider"><span>OR</span></div>

      <label for="phone">Phone Number</label>
      <input type="tel" id="phone" name="phone" placeholder="+977 Enter Mobile Number" pattern="\+977[0-9]{10}" required>

      <!-- Password -->
      <label for="password">Password</label>
      <div class="password-wrapper">
        <input 
          type="password" 
          id="password" 
          name="password" 
          placeholder="Create a strong password" 
          pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[\W_]).{6,}$" 
          minlength="6"
          required
        />
        <span class="toggle-password" id="togglePassword">👁️</span>
      </div>

      <button type="submit" class="register-btn">Register</button>
    </form>

    <p class="login-link">Already have an account? <a href="Login.php">Sign in</a></p>
  </div>

  <script>
    const togglePassword = document.getElementById("togglePassword");
    const passwordField = document.getElementById("password");

    togglePassword.addEventListener("click", function () {
      passwordField.type = passwordField.type === "password" ? "text" : "password";
    });

    document.getElementById("registerForm").addEventListener("submit", function(event) {
      const fullname = document.getElementById("fullname").value.trim();
      const email = document.getElementById("email").value.trim();
      const phone = document.getElementById("phone").value.trim();
      const password = document.getElementById("password").value.trim();
      const phonePattern = /^\+977[0-9]{10}$/;
      const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[\W_]).{6,}$/;

      if (!fullname || !email || !phone || !password) {
        alert("Please fill out all fields.");
        event.preventDefault();
        return;
      }

      if (!phonePattern.test(phone)) {
        alert("Phone number must be in the format: +977XXXXXXXXXX (10 digits after +977).");
        event.preventDefault();
        return;
      }

      if (!passwordPattern.test(password)) {
        alert("Password must be at least 6 characters, include a letter, a number, and a special character.");
        event.preventDefault();
        return;
      }

      alert("Registration successful!");
    });
  </script>
</body>
</html>
