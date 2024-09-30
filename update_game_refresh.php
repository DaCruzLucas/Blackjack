<?php
session_start();
require_once("Database.php");
$db = new Database();

if (isset($_SESSION['selectedParty'])) {
    $partyId = $_SESSION['selectedParty'];

    $db->resetRefresh($partyId);
}
?>