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
        echo "Successfully connected to the database";
    }

    //This was the existing function for adding prescriptions
    public function addPrescription($patientUserName, $medicationId, $dosageInstructions, $quantity)  {
        $stmt = $this->connection->prepare(
            "SELECT userId FROM Users WHERE userName = ? AND userType = 'patient'"
        );
        $stmt->bind_param("s", $patientUserName);
        $stmt->execute();
        $stmt->bind_result($patientId);
        $stmt->fetch();
        $stmt->close();
        
        if ($patientId){
            //insert the prescription using the found patientId
            $stmt = $this->connection->prepare(
                "INSERT INTO prescriptions (userId, medicationId, dosageInstructions, quantity) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("iisi", $patientId, $medicationId, $dosageInstructions, $quantity);
            $stmt->execute();
            $stmt->close();
            echo "Prescription added successfully";
        }else{
            echo "failed to add prescription";
        }
    }

    //This gets all prescriptions joined with medications
    public function getAllPrescriptions() {
        $result = $this->connection->query("SELECT * FROM  prescriptions join medications on prescriptions.medicationId= medications.medicationId");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //We are implementing the method addMedication that we had to do
    public function addMedication($medicationName, $dosage, $manufacturer)  {
        $stmt = $this->connection->prepare(
            "INSERT INTO Medications(medicationName, dosage, manufacturer) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $medicationName, $dosage, $manufacturer);
        if ($stmt->execute()){
        echo "Medication has been added successfully.";

        }else{
            echo "Error:Medication has not been added successfully" . $stmt->error;
        }
        $stmt->close();

    }


    //retrieves inventory data from sql view.

    public function medicationInventory() {
        /*
        Complete this function to test the functionality of
        MedicationInventoryView and implement it in the server
        */
        //Wrire code here
        $result = $this->connection->query("SELECT * FROM MedicationInventoryView");
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
     //if user exist already return thei id
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
        //First we have to chech if there is a user with the same username that already exists
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
