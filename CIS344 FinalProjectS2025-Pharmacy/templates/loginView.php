<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Pharmacy Portal</title>

  <!-- This line connects the HTML to the style.css file -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <h2>LOGIN</h2>
  
    <?php if (!empty($error)): ?>
          <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>


    <form action="login.php" method="POST">
       <label for="username">UserName:</label>
        <input type="text" name="userName" id="username" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" name="Password" id="password" required>
        <br><br>
        <button type="submit">Login</button>
    </form>
    <p><a href="PharmacyServer.php?action=home">Back to Home</a></p>
    <p>Don't have an account? <a href="register.php">Register here.</a></p>
</body>
</html>


    