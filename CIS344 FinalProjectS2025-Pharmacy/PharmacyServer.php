<?php

session_start();

// I added this to ensure only pharmacists can access these actions.
//if (!isset($_SESSION['userId']) || $_SESSION['userType'] !== 'pharmacist') {
    //header("Location: login.php");
    //exit();
//}

require_once 'PharmacyDatabase.php';

class PharmacyPortal {
    private $db;

    public function __construct() {
        $this->db = new PharmacyDatabase();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? 'home';

        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'register':
                $this->register();
                break;
            case 'addPrescription':
                $this->addPrescription();
                break;
            case 'viewPrescriptions':
                $this->viewPrescriptions();
                break;
            case 'viewInventory':
                $this->viewInventory();
                break;
            case 'addUser':
                $this->addUser();
                break;
            default:
                $this->home();
        }
    }

    private function home() {
        include 'templates/home.php'; 

    }

    private function login() {
        include 'login.php'; 
    }

    private function register() {
        include 'register.php';

    }

   // private function addPrescription() {
       // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           // $patientUserName = $_POST['patient_username'];
           // $medicationId= $_POST['medication_id'];
           // $dosageInstructions = $_POST['dosage_instructions'];
           // $quantity = $_POST['quantity'];

           //call database function to add prescription
           // $this->  
           // db->addPrescription($patientUserName, $medicationId, $dosageInstructions, $quantity);
           // header("Location:?action=viewPrescriptions&message=Prescription Added");
        //} else {
            //include 'templates/addPrescription.php';
        //}
    //}

    private function viewPrescriptions() {
        $prescriptions = $this->db->getAllPrescriptions();
        include 'templates/viewPrescriptions.php';
    }
}

$portal = new PharmacyPortal();
$portal->handleRequest();
