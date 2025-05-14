<?php
class PharmacyDatabase {
    private $host = "localhost";
    private $port = "3306";
    private $database = "pharmacy_portal_db";
    private $user = "root";
    private $password = "";
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        error_log("Database connected successfully");
        //echo "Successfully connected to the database";
    }

    //This was the existing function for adding prescriptions
    public function addPrescription($patientUserName, $medicationId, $dosageInstructions, $quantity)  {
        //first we get the userId that corresponds to the patient username
        $stmt1 = $this->connection->prepare(
            "SELECT userId FROM users WHERE userName = ? AND userType = 'patient'"
        );
        if (!$stmt1) {
            die("Prepare failed (patient lookup): " . $this->connection->error);
        }
        $stmt1->bind_param("s", $patientUserName);
        if (!$stmt1->execute()) {
            die("Execute failed (patient lookup): " . $stmt1->error);
        }
    
        $stmt1->bind_result($patientId);
        if (!$stmt1->fetch()) {
            // No patient found we close the statement
            $stmt1->close();
            //we automatically add the new patient
            $patientId = $this->addUser($patientUserName, "", "patient");
            if (!$patientId) {
                echo "Failed to add patient with username: " . htmlspecialchars($patientUserName);
                return;
            }
        } else {
            $stmt1->close();
        }
       
        // now we insert the prescription using the found patientId

        $stmt2 = $this->connection->prepare(
            "INSERT INTO prescriptions (userId, medicationId, dosageInstructions, quantity) VALUES (?, ?, ?, ?)"
        );
        
        if (!$stmt2){
            die("Prepare failed (insertion): " . $this->connection->error);
        }
           
            $stmt2->bind_param("iisi", $patientId, $medicationId, $dosageInstructions, $quantity);
            if (!$stmt2->execute()) {
                die("Execute failed (insertion): " . $stmt2->error);
            }
            $stmt2->close();
            
            echo "Prescription added successfully";
        }
           
    
    

    //This gets all prescriptions joined with medications
    public function getAllPrescriptions() {
        $query = "SELECT p.prescriptionId, u.userName AS patientName, p.userId, p.medicationId, m.medicationName, p.dosageInstructions, p.quantity,
        p.prescribedDate
        FROM prescriptions p
        JOIN medications m ON p.medicationId = m.medicationId
        JOIN users u ON p.userId = u.userId
        WHERE u.userType = 'patient'
        ";

        $result = $this->connection->query($query);

        if (!$result) {
            printf("Error retrieving prescriptions: %s\n", $this->connection->error);
            return [];
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);


        //$result = $this->connection->query("SELECT * FROM  prescriptions join medications on prescriptions.medicationId= medications.medicationId");
       // return $result->fetch_all(MYSQLI_ASSOC);
    }

    //We are implementing the method addMedication that we had to do for the pharmacist to be able to add medication.
    public function addMedication($medicationName, $dosage, $manufacturer)  {
        $stmt = $this->connection->prepare(
            "INSERT INTO medications(medicationName, dosage, manufacturer) VALUES (?, ?, ?)");
        if (!$stmt) {
            return "Prepare failed: " . $this->connection->error;
            }
        $stmt->bind_param("sss", $medicationName, $dosage, $manufacturer);
        if ($stmt->execute()){
        $stmt->close();
        return true;  // success
        }else{
        $error = "Falied to execute: " .$stmt->error; 
        $stmt->close();
        return $error; // return error message
        }
    }


    //retrieves inventory data from sql view.

    public function medicationInventory() {
        /*
        Complete this function to test the functionality of
        MedicationInventoryView and implement it in the server
        */
        //Wrire code here
        $result = $this->connection->query("SELECT * FROM MedicationInventoryView");
        if (!$result) {
        error_log("Error querying MedicationInventoryView: " . $this->connection->error);
        return [];
    }
        return $result->fetch_all(MYSQLI_ASSOC);

    }



    public function addUser($userName, $contactInfo, $userType) {
     //Write Code here
     // first we have to check if the user already exists

     $stmt = $this->connection->prepare(
        "SELECT userId FROM Users WHERE userName = ?");
   
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $stmt->bind_result($userId);
    $stmt->fetch();
    $stmt->close();
    
    if ($userId){
     //if user exist already return their id
     return $userId;
    } else {
        $stmt = $this->connection->prepare("INSERT INTO Users (userName, contactInfo, userType) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $userName, $contactInfo, $userType);
    $stmt->execute();
    $newUserId = $stmt->insert_id;
    $stmt->close();
    return $newUserId;

    }

    }

    public function getUserDetails($userId) {
        // Get user info.
        $stmt = $this->connection->prepare("SELECT userId, userName, contactInfo, userType FROM Users WHERE userId = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userInfo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    
        // Get prescriptions for this user.
        $stmt2 = $this->connection->prepare(
            "SELECT p.prescriptionId, p.dosageInstructions, p.quantity, p.prescribedDate, m.medicationName 
             FROM Prescriptions p 
             JOIN Medications m ON p.medicationId = m.medicationId 
             WHERE p.userId = ?"
        );
        $stmt2->bind_param("i", $userId);
        $stmt2->execute();
        $prescriptions = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
    
        return [
            'user' => $userInfo,
            'prescriptions' => $prescriptions
        ];
    }



    //Add Other needed functions here

    public function loginUser($userName){
        $stmt = $this->connection->prepare(
            "SELECT userId, userName, password, userType FROM Users WHERE userName = ?"
        );
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }    




    public function registerUser($userName, $contactInfo, $password, $userType){
        //First we have to check if there is a user with the same username that already exists
        $stmt = $this->connection->prepare(
            "SELECT userId FROM Users WHERE userName = ?"
        );
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $stmt->bind_result($existingUserId);
        $stmt->fetch();
        $stmt->close();
        
        if ($existingUserId){
            //if username already exists
            return false;
        } else{
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare(
                "INSERT INTO Users (userName, contactInfo, password, userType) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $userName, $contactInfo, $hashedPassword, $userType);
            $result = $stmt->execute();
            if($result) {
                $newUserId = $stmt->insert_id;
                $stmt->close();
                return $newUserId;
            } else {
                $stmt->close();
                return false;
        }
    }
}
}
?>
