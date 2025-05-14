-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 01:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pharmacy_portal_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddOrUpdateUser` (IN `pUserName` VARCHAR(45), IN `pContactInfo` VARCHAR(200), IN `pUserType` ENUM('pharmacist','patient'))   BEGIN 
INSERT INTO Users(userName, contactInfo, userType)
VALUES(pUserName, pContactInfo, pUserType)
ON DUPLICATE KEY UPDATE
contactInfo = pContactInfo,
userType = pUserType;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessSale` (IN `pPrescriptionId` INT, IN `pQuantitySold` INT, IN `pSaleAmount` DECIMAL(10,2))   BEGIN 
  -- Update inventory (ensures the stock won't go to negative)
UPDATE Inventory 
JOIN Prescriptions ON Inventory.medicationId = Prescriptions.medicationId
SET Inventory.quantityAvailable = Inventory.quantityAvailable - pQuantitySold
WHERE Prescriptions.prescriptionId = pPrescriptionId
AND Inventory.quantityAvailable >= pQuantitySold;

 -- To record the sale
INSERT INTO Sales (prescriptionId, saleDate, quantitySold, saleAmount)
VALUES (pPrescriptionId, NOW(), pQuantitySold, pSaleAmount);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventoryId` int(11) NOT NULL,
  `medicationId` int(11) NOT NULL,
  `quantityAvailable` int(11) NOT NULL,
  `lastUpdated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventoryId`, `medicationId`, `quantityAvailable`, `lastUpdated`) VALUES
(1, 1, 100, '2025-05-05 22:18:11'),
(2, 2, 170, '2025-05-05 22:18:11'),
(3, 3, 80, '2025-05-05 22:18:11');

-- --------------------------------------------------------

--
-- Stand-in structure for view `medicationinventoryview`
-- (See below for the actual view)
--
CREATE TABLE `medicationinventoryview` (
`medicationName` varchar(45)
,`dosage` varchar(45)
,`manufacturer` varchar(100)
,`quantityAvailable` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `medicationId` int(11) NOT NULL,
  `medicationName` varchar(45) NOT NULL,
  `dosage` varchar(45) NOT NULL,
  `manufacturer` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`medicationId`, `medicationName`, `dosage`, `manufacturer`) VALUES
(1, 'Tylenol', '500ml', 'CDC'),
(2, 'Aspirin', '500mg', 'Bayer'),
(3, 'Ibuprofen', '200mg', 'Advil'),
(4, 'Paracetamol', '500mg', 'Tylenol'),
(5, 'Amoxicillin', '200ml', 'CDC'),
(6, 'Acetaminophen', '500ml', 'CDC');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescriptionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `medicationId` int(11) NOT NULL,
  `prescribedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `dosageInstructions` varchar(200) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `refillCount` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`prescriptionId`, `userId`, `medicationId`, `prescribedDate`, `dosageInstructions`, `quantity`, `refillCount`) VALUES
(4, 543673, 2, '2025-05-05 22:45:50', 'Take one tablet every 8 hours', 30, 1),
(5, 543674, 3, '2025-05-05 22:45:50', 'Take one tablet every 6 hours', 60, 2),
(6, 543675, 4, '2025-05-05 22:45:50', 'Take one tablet every 12 hours', 20, 0),
(8, 543673, 2, '2025-05-12 19:57:29', 'take 2 a day', 20, 0),
(9, 543675, 2, '2025-05-12 20:38:22', 'take once a day', 20, 0),
(10, 543675, 3, '2025-05-14 18:59:30', 'take once a day ', 10, 0);

--
-- Triggers `prescriptions`
--
DELIMITER $$
CREATE TRIGGER `AfterPrescriptionInsert` AFTER INSERT ON `prescriptions` FOR EACH ROW BEGIN
    -- Update inventory to reduce stock based on the new prescription
    UPDATE Inventory 
    SET quantityAvailable = quantityAvailable - NEW.quantity
    WHERE medicationId = NEW.medicationId;
    
    -- Log low stock alerts if inventory falls below a threshold (e.g., 10 units)
    IF (SELECT quantityAvailable FROM Inventory WHERE medicationId = NEW.medicationId) < 10 THEN
        INSERT INTO low_stock_logs (medicationId, logDate, message) 
        VALUES (NEW.medicationId, NOW(), 'Low stock alert: Medication running low.');
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `saleId` int(11) NOT NULL,
  `prescriptionId` int(11) NOT NULL,
  `saleDate` datetime NOT NULL DEFAULT current_timestamp(),
  `quantitySold` int(11) NOT NULL,
  `saleAmount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userId` int(11) NOT NULL,
  `userName` varchar(45) NOT NULL,
  `contactInfo` varchar(200) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `userType` enum('pharmacist','patient') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `userName`, `contactInfo`, `password`, `userType`) VALUES
(543673, 'Ben Dock', 'BDock@gmail.com', '$2y$10$K5h.a0TEAL54ZC/lekSNp.vH7sd9ZIMD69MD5eHsy8E8x3vXPSW8K', 'patient'),
(543674, 'Helen Sherry', 'HSherry@gmail.com', '$2y$10$5uD8ED6kNF33AZZjfsCbe.IvI7LDMvd.PWdTHKRXcfmIwkVEMQgNG', 'pharmacist'),
(543675, 'Maria Moon', 'MMoon@gmail.com', '$2y$10$W8URnNyihOjQ2wMrtfAjQuFMtB/Hoh1yERh4m8ap1aTUj3UYudXau', 'patient');

-- --------------------------------------------------------

--
-- Structure for view `medicationinventoryview`
--
DROP TABLE IF EXISTS `medicationinventoryview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `medicationinventoryview`  AS SELECT `medications`.`medicationName` AS `medicationName`, `medications`.`dosage` AS `dosage`, `medications`.`manufacturer` AS `manufacturer`, `inventory`.`quantityAvailable` AS `quantityAvailable` FROM (`medications` join `inventory` on(`medications`.`medicationId` = `inventory`.`medicationId`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventoryId`),
  ADD UNIQUE KEY `inventoryId` (`inventoryId`),
  ADD KEY `medicationId` (`medicationId`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`medicationId`),
  ADD UNIQUE KEY `medicationId` (`medicationId`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescriptionId`),
  ADD UNIQUE KEY `prescriptionId` (`prescriptionId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `medicationId` (`medicationId`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`saleId`),
  ADD UNIQUE KEY `saleId` (`saleId`),
  ADD KEY `prescriptionId` (`prescriptionId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `userId` (`userId`),
  ADD UNIQUE KEY `userName` (`userName`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `medicationId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescriptionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `saleId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=543680;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`medicationId`) REFERENCES `medications` (`medicationId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `medicationId` FOREIGN KEY (`medicationId`) REFERENCES `medications` (`medicationId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`prescriptionId`) REFERENCES `prescriptions` (`prescriptionId`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
