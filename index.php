<?php
session_start();
require_once("Database.php");
$db = new Database();
?>
<!doctype html>
<html lang="fr">

<head>
    <title>Blackjack</title>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />

    <link type="text/css" href="style.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/66c180b670.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <div class="text-center mt-3">
            <?php
            if (!isset($_SESSION["user"])) {
                header("Location: signin.php");
                exit();
            }

            if (isset($_POST['partyCreate'])) {
                $db->createParty($_POST['partyCreate']);
                header("Location: index.php");
                exit();
            }

            if (isset($_POST['partyJoin'])) {
                $db->joinParty($_POST['partyCreate']);
                header("Location: game.php");
                exit();
            }
            ?>
        </div>

        <!-- Header -->
        <div class="bg-perso rounded-4 text-white mt-3 mb-3 p-4">
            <div class="row align-middle">
                <div class="col text-start">
                    <form action="index.php">
                        <button type="submit" class="text-black bg-chance rounded-3 fs-4 py-2 px-4 border-0">Chance</button>
                    </form>
                </div>
                <div class="col text-center">
                    <h1>Lobby</h1>
                </div>
                <div class="col text-end">
                    <form action="account.php">
                        <button type="submit" class="bouton text-white bg-transparent rounded-3 fs-4 py-2 px-3 border-0"><i class="fa-solid fa-user" style="color: #ffffff;"></i></button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main -->
        <div class="row">
            <!-- Gestionnaire de parties -->
            <div class="col">
                <div class="rounded-4 p-2 main">
                    <!-- Rejoindre une partie -->
                    <div class="row py-2 mb-3">
                        <h3 class="text-center">Rejoindre une partie</h3>
                    </div>
                    <div class="row px-2 py-2">
                        <div class="col d-grid">
                            <input type="text" name="partyJoin" placeholder="Identifiant" class="border-1 text-center rounded-4 py-2">
                        </div>
                        <div class="col d-grid">
                            <button type="submit" class="bg-perso text-white border-0 rounded-4 py-2">Rejoindre</button>
                        </div>
                    </div>

                    <!-- Créer une partie -->
                    <div class="row py-2 pt-5 mb-3">
                        <h3 class="text-center">Créer une partie</h3>
                    </div>
                    <div class="row px-2 py-2">
                        <div class="col d-grid">
                            <form action="index.php" method="post" class="d-grid">
                                <input type="hidden" name="partyCreate" value="public">
                                <button type="submit" class="bg-perso text-white border-0 rounded-4 py-2">Partie privée</button>
                            </form>
                        </div>
                        <div class="col d-grid">
                            <form action="index.php" method="post" class="d-grid">
                                <input type="hidden" name="partyCreate" value="private">
                                <button type="submit" class="bg-perso text-white border-0 rounded-4 py-2">Partie publique</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Leaderboard -->
            <div class="col">
                <div class="rounded-4 p-2 main">
                    Leaderboard
                </div>
            </div>
        </div>

        <!-- Liste des parties -->
        <div class="rounded-4 mt-3 p-2 main">
            <?php $parties = $db->getParties() ?>
            <?php if ($parties != null): ?>
                <div class="row m-2 text-center rounded-4">
                    <div class="col-10 py-2 bouton rounded-4">
                        <div class="row">
                            <div class="col-2"><b>ID</b></div>
                            <div class="col"><b>Status</b></div>
                            <div class="col-2"><b>Joueurs</b></div>
                            <div class="col"><b>Chef</b></div>
                        </div>
                    </div>
                </div>
                <?php foreach ($parties as $partie): ?>
                    <div class="row m-2 text-center">
                        <div class="col-10 py-2 bouton rounded-4">
                            <div class="row">
                                <div class="col-2">#<?php echo($partie['idPartie']); ?></div>
                                <div class="col"><?php echo($partie['status']); ?></div>
                                <div class="col-2"><?php echo($db->getPartyPlayersCount($partie['idPartie'])); ?>/4</div>
                                <div class="col"><?php echo($db->getPartyOwner($partie['idPartie']))['username']; ?></div>
                            </div>
                        </div>
                        <div class="col-2 d-grid">
                            <form action="index.php" class="d-grid">
                                <input type="hidden" name="partyJoin" value="<?php echo($partie['idPartie']); ?>">
                                <button type="submit" class="bg-perso text-white border-0 rounded-4">Rejoindre</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach ?>
            <?php endif ?>

            <?php if ($parties == null): ?>
                <div class="row">
                    <div class="text-center">Aucune partie disponible</div>
                </div>
            <?php endif ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>