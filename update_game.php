<?php
session_start();
require_once("Database.php");
$db = new Database();

if (isset($_SESSION['selectedParty'])) {
    $partyId = $_SESSION['selectedParty'];
    $joueurs = $db->getPlayerList($partyId);
    $tour = $db->getPartyTour($partyId);
    $status = $db->getPartyStatus($partyId);
    
    $data = [
        'joueurs' => $joueurs,
        'tour' => $tour,
        'status' => $status
    ];
    
    echo json_encode($data);
}
?>