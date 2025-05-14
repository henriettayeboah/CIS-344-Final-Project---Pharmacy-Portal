<?php
session_start();

// Ensure the user is logged in and is a pharmacist.
if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'pharmacist') {
    header("Location: login.php");
    exit();
}

require_once 'PharmacyDatabase.php';
$db = new PharmacyDatabase();

// Retrieve the inventory details from the MedicationInventoryView.
$inventory = $db->medicationInventory();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacist Dashboard - Pharmacy Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['userName']); ?>!</h1>
    
    <h2>Inventory Management</h2>
    <table border="1">
        <tr>
            <th>Medication Name</th>
            <th>Dosage</th>
            <th>Manufacturer</th>
            <th>Quantity Available</th>
        </tr>
        <?php if (empty($inventory)): ?>
            <tr>
                <td colspan="4">No inventory data found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($inventory as $med): ?>
                <tr>
                    <td><?php echo htmlspecialchars($med['medicationName']); ?></td>
                    <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                    <td><?php echo htmlspecialchars($med['manufacturer']); ?></td>
                    <td><?php echo htmlspecialchars($med['quantityAvailable']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <nav>
        <a href="addMedication.php" class="nav-link">Add Medication</a>
        <a href="addPrescription.php" class="nav-link">Add Prescription</a>
        <a href="templates/viewPrescriptions.php" class="nav-link">View Prescriptions</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>
</body>
</html>