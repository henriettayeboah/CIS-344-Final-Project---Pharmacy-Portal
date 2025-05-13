<?php
session_start();

// Ensure the user is logged in and is a patient.
if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'patient') {
    header("Location: login.php");
    exit();
}

require_once 'PharmacyDatabase.php';
$db = new PharmacyDatabase();

$userId       = $_SESSION['userId'];
$userDetails  = $db->getUserDetails($userId); // Returns an array with 'user' and 'prescriptions'
$prescriptions = $userDetails['prescriptions'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard - Pharmacy Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['userName']); ?>!</h1>

    <h2>Your Prescriptions</h2>
    <table border="1">
        <tr>
            <th>Prescription ID</th>
            <th>Medication Name</th>
            <th>Dosage Instructions</th>
            <th>Quantity</th>
            <th>Prescribed Date</th>
        </tr>
        <?php if (empty($prescriptions)): ?>
            <tr>
                <td colspan="5">No prescriptions found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($prescriptions as $prescription): ?>
                <tr>
                    <td><?php echo htmlspecialchars($prescription['prescriptionId']); ?></td>
                    <td><?php echo htmlspecialchars($prescription['medicationName']); ?></td>
                    <td><?php echo htmlspecialchars($prescription['dosageInstructions']); ?></td>
                    <td><?php echo htmlspecialchars($prescription['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($prescription['prescribedDate']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>