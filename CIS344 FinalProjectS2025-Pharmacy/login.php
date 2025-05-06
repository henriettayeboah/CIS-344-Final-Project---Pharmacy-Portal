<?php
//We will start the session to be able to store user data if they register properly.
session_start();

//Including the file that contains the database connection.
require_once 'PharmacyDatabase.php';
$error = "";
$db = new PharmacyDatabase();

//check that all required feilds are entered.
if (!isset($_POST['userName']) || !isset($_POST['Password'])) {
    $error = " Please enter both username and password";

} else {

// Get's inputs
$userName = trim($_POST['userName']);
$password = $_POST['Password'];

//fetches the user record
$user = $db->loginUser($userName);

if($user){
    //compare the submitted passwprd with the hashed password.
    if(password_verify($password, $user['password'])) {
    //if login was successful we will set session variables.
    $_SESSION['userId']   = $user['userId'];
    $_SESSION['userName'] = $user['userName'];
    $_SESSION['userType'] = $user['userType'];

//welcome message before redirecting
echo "Login successful. Welcome, " . htmlspecialchars($user['userName']) . "! You will be redirected shortly.";

//depending on the user we will redirect them to their own dashboard
if ($user['userType'] === 'pharmacist') {
    header("Location: PharmacistDashboard.php");
    exit(); 
    
} elseif ($user['userType'] === 'patient') {
    header("Location: patientDashboard.php");
    exit();
}
} else { 
    //password is incorrect
    $error = "Incorrect password. Please try again.";
}
    } else {
//No account found with that username so redirecting to registeration page 
        echo "No account found with that username. Redirecting to register...";
        echo "<script>
                        setTimeout(function(){
                          window.location.href = 'register.php';
                        }, 3000);
                      </script>";
        exit();
     }
}   
                     
 
//login view to display form and errors if login fails
    include 'templates/loginView.php';

?>
