<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer Login - AutoCare Pro</title>
    <link rel="stylesheet" href="../css/auth.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
  </head>
  <body class="auth-page customer-auth">
    <div class="auth-container">
      <div class="auth-box">
        <div class="auth-header">
          <img
            src="../images/logo.png"
            alt="AutoCare Pro Logo"
            class="auth-logo"
          />
          <h1>Welcome Back</h1>
          <p>Sign in to manage your vehicle services and bookings</p>
        </div>

        <div class="auth-tabs">
          <button class="tab-btn active" data-tab="login">Login</button>
          <button class="tab-btn" data-tab="register">Register</button>
        </div>

        <div class="tab-content">
          <!-- Login Form -->
          <form id="loginForm" class="auth-form active" method="POST" action="../php_file/process_login.php">
            <div class="form-group">
              <label for="loginEmail">
                <i class="fas fa-envelope"></i>
                Email Address
              </label>
              <input
                type="email"
                class="form-control"
                id="loginEmail"
                name="email"
                placeholder="Enter your email address"
                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                title="Please enter a valid email address"
                required
              />
            </div>
            <div class="form-group">
              <label for="loginPassword">
                <i class="fas fa-lock"></i>
                Password
              </label>
              <input
                type="password"
                class="form-control"
                id="loginPassword"
                name="password"
                placeholder="Enter your password"
                required
              />
              <i class="fas fa-eye toggle-password"></i>
            </div>
            <div class="form-options">
              <label class="remember-me">
                <input type="checkbox" name="remember" />
                <span>Keep me signed in</span>
              </label>
              <a href="#" class="forgot-password">Forgot Password?</a>
            </div>
            <button type="submit" class="submit-btn">
              <i class="fas fa-sign-in-alt"></i>
              Sign In
            </button>
          </form>

          <!-- Register Form -->
          <form
            id="registerForm"
            class="auth-form"
            method="POST"
            action="../php_file/register.php"
          >
            <div class="form-group">
              <label for="regName">
                <i class="fas fa-user"></i>
                Full Name
              </label>
              <input
                type="text"
                class="form-control"
                id="regName"
                name="username"
                placeholder="Enter your full name"
                required
              />
            </div>
            <div class="form-group">
              <label for="regEmail">
                <i class="fas fa-envelope"></i>
                Email Address
              </label>
              <input
                type="email"
                class="form-control"
                id="regEmail"
                name="email"
                placeholder="Enter your email address"
                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                title="Please enter a valid email address"
                required
              />
            </div>
            <div class="form-group">
              <label for="regPhone">
                <i class="fas fa-phone"></i>
                Phone Number
              </label>
              <input
                type="tel"
                class="form-control"
                id="regPhone"
                name="phone"
                placeholder="Enter your phone number"
                pattern="[0-9]{11}"
                title="Please enter a valid UK phone number (11 digits)"
                required
              />
            </div>
            <div class="form-group">
              <label for="regPassword">
                <i class="fas fa-lock"></i>
                Password
              </label>
              <input
                type="password"
                class="form-control"
                id="regPassword"
                name="password"
                placeholder="Create a password"
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"
                required
              />
              <i class="fas fa-eye toggle-password"></i>
            </div>
            <div class="form-group">
              <label for="regConfirmPassword">
                <i class="fas fa-lock"></i>
                Confirm Password
              </label>
              <input
                type="password"
                class="form-control"
                id="regConfirmPassword"
                name="confirmPassword"
                placeholder="Confirm your password"
                required
              />
              <i class="fas fa-eye toggle-password"></i>
            </div>
            <button type="submit" class="submit-btn">
              <i class="fas fa-user-plus"></i>
              Create Account
            </button>
          </form>
        </div>

        <div class="social-divider">
          <span>Or continue with</span>
        </div>

        <div class="social-buttons">
          <button class="social-btn google">
            <i class="fab fa-google"></i>
            Google
          </button>
          <button class="social-btn facebook">
            <i class="fab fa-facebook-f"></i>
            Facebook
          </button>
        </div>

        <div class="auth-footer">
          <a href="../landing.html">
            <i class="fas fa-arrow-left"></i>
            Back to Website
          </a>
        </div>
      </div>
    </div>
    <script src="../js/customer.js"></script>
    <script>
      console.log('✅ Inline script loaded');
    </script>
  </body>
</html>
