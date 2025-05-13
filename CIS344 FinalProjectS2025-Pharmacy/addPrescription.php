<?php
session_start();
require_once 'PharmacyDatabase.php';

// Ensure the user is logged in and is a pharmacist.
if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'pharmacist') {
    header("Location: login.php");
    exit();
}

$db = new PharmacyDatabase();
$message = "";
$error = "";

// Process the form if the request method is POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input values
    $patientUserName = trim($_POST['patient_username']);
    $medicationId= $_POST['medication_id'];
    $dosageInstructions = $_POST['dosage_instructions'];
    $quantity = $_POST['quantity'];


    // Basic validation for empty fields.
    if (empty($patientUserName) || empty($dosageInstructions) || empty($quantity)) {
        $error = "Please fill in all the fields.";
    } else {
        // Call the addPrescription method.
    
        $db->addPrescription($patientUserName, $medicationId, $dosageInstructions, $quantity);
        $message = "Prescription has been added successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Prescription</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Add Prescription</h1>
    <?php if (!empty($message)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>



    <form action="addPrescription.php" method="POST">
        Patient Username: <input type="text" name="patient_username" /><br>
        Medication ID : <input type="number" name="medication_id"/><br>
        Dosage Instructions: <textarea name="dosage_instructions"></textarea><br>
        Quantity: <input type="number" name="quantity" /><br>
        <button type="submit">Save</button>
    </form>
    <br>

    <a href="PharmacistDashboard.php" class="nav-link">Back to Dashboard</a>
</body>
</html>