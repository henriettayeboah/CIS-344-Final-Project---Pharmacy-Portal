<?php
//We will start the session to be able to store user data if they register properly.
session_start();

//Including the file that contains the database connection.
require_once 'PharmacyDatabase.php';

$error = "";

//check that all required Post variables are set before processing.
if (!isset($_POST['Fullname'], $_POST['Email'], $_POST['Password'], $_POST['Confirm_Password'], $_POST['userType'])) {
    $error = "Please fill all required details.";
    
}else{

// Get's inputs
$userName = trim($_POST['Fullname']);
$contactInfo = trim($_POST['Email']);
$password = $_POST['Password'];
$confirm_password = $_POST['Confirm_Password'];
$userType = $_POST['userType']; // either "patient" of "pharmacist"


//Ensure both passwords match
if($password !== $confirm_password) {
    $error = "Passwords do not match.";
} else{
    $db = new PharmacyDatabase();
    $userId = $db->registerUser($userName, $contactInfo, $password, $userType);


if($userId){
    //if registration was successful we will redirect user to log in.
    header("Location: login.php");
    exit();

} else {

$error = "Registration failed. This username may already exist.";

}
}
}

include 'templates/registerView.php';
?>