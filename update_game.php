<?php
session_start();
require_once("Database.php");
$db = new Database();

if (isset($_SESSION['selectedParty'])) {
    $partyId = $_SESSION['selectedParty'];
    $joueurs = $db->getPlayerList($partyId);
    $tour = $db->getPartyTour($partyId);
    $status = $db->getPartyStatus($partyId);
    $party = $db->getPartyInfos($partyId);
    $userId = $_SESSION['user']['idUser'];
    
    $data = [
        'joueurs' => $joueurs,
        'party' => $party,
        'tour' => $tour,
        'status' => $status,
        'idUser' => $userId
    ];
    
    echo json_encode($data);
}
?>