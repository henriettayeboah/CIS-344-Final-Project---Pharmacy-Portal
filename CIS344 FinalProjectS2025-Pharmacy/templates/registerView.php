<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register For Pharmacy - Pharmacy Portal</title>

  <!-- This line connects the HTML to the style.css file -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!-- Main registration box -->
  <div class="register-container">
    <form action="register.php" method="POST" class="register-box">

      <h2>REGISTER</h2>

      <!-- Input fields -->
      <input type="text" name="Fullname" placeholder="Full Name" required>
    <input type="email" name="Email" placeholder="Email" required>
    <input type="password" name="Password" placeholder="Password" required>
    <input type="password" name="Confirm_Password" placeholder="Confirm Password" required>

    <!-- dropdown for userType -->
    <select name="userType" required>
        <option value="">Select User Type</option>
        <option value="patient">Patient</option>
        <option value="pharmacist">Pharmacist</option>
      </select>



      <!-- Submit button -->
      <button type="submit">Register</button>

      <!-- Link to login -->
      <p>Already have an account? <a href="login.php">Login</a></p>


      <?php if (!empty($error)): ?>
          <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>


    </form>
  </div>

</body>
</html>

