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
    $medicationName = trim($_POST['medicationName']);
    $dosage = trim($_POST['dosage']);
    $manufacturer = trim($_POST['manufacturer']);

    // Basic validation for empty fields.
    if (empty($medicationName) || empty($dosage) || empty($manufacturer)) {
        $error = "Please fill in all the fields.";
    } else {
        // Call the addMedication method.
        // Note: Your current addMedication() method echoes messages on success/failure.
        // You may wish to refactor it so that it returns a Boolean or a message instead.
        $db->addMedication($medicationName, $dosage, $manufacturer);
        $message = "Medication has been added successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Medication</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Add Medication</h1>
    <?php if (!empty($message)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="addMedication.php" method="POST">
        <label for="medicationName">Medication Name:</label>
        <input type="text" id="medicationName" name="medicationName" required>
        <br><br>
        <label for="dosage">Dosage:</label>
        <input type="text" id="dosage" name="dosage" required>
        <br><br>
        <label for="manufacturer">Manufacturer:</label>
        <input type="text" id="manufacturer" name="manufacturer" required>
        <br><br>
        <button type="submit">Add Medication</button>
    </form>
    <br>
    <a href="PharmacistDashboard.php" class="nav-link">Back to Dashboard</a>
</body>
</html>