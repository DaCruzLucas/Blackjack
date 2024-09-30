<?php
session_start();
require_once("Database.php");
$db = new Database();

$parties = $db->getParties();
$partieDetails = [];

if ($parties) {
    foreach ($parties as $partie) {
        $partieDetails[] = [
            'idPartie' => $partie['idPartie'],
            'status' => $partie['status'],
            'playersCount' => $db->getPartyPlayersCount($partie['idPartie']),
            'owner' => $db->getPartyOwner($partie['idPartie'])['username']
        ];
    }
}

// Retourne les détails des parties en JSON
header('Content-Type: application/json');
echo json_encode($partieDetails);
?>
