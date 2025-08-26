<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login - AutoCare Pro</title>
  <link rel="stylesheet" href="../css/auth.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
</head>
<body class="auth-page admin-auth">
  <div class="auth-container">
    <div class="auth-box">
      <div class="auth-header">
        <img src="../images/logo.png" alt="AutoCare Pro Logo" class="auth-logo"/>
        <h1>Admin Portal</h1>
        <p>Enter your credentials to access the admin dashboard</p>
      </div>

      
      <form class="auth-form" method="POST" action="/car_garage/php_file/admin_login.php">
        <div class="form-group">
          <label for="email"><i class="fas fa-envelope"></i> Admin Email</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="admin@example.com" required/>
        </div>

        <div class="form-group">
          <label for="password"><i class="fas fa-lock"></i> Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required/>
          <i class="fas fa-eye toggle-password"></i>
        </div>

        <button type="submit" class="submit-btn">
          <i class="fas fa-sign-in-alt"></i> Sign In to Dashboard
        </button>
      </form>

      <div class="auth-footer">
        <a href="../Landing.html"><i class="fas fa-arrow-left"></i> Back to Website</a>
      </div>
    </div>
  </div>
  <script src="../js/admin.js"></script>
</body>
</html>