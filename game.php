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

    <script>
        setInterval(function() {
            // location.reload();

            fetch('update_game.php')
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    // Met à jour le tour
                    // document.getElementById('currentTurn').textContent = 'Tour : ' + data.tour;

                    // Met à jour la liste des joueurs
                    const joueurs = data.joueurs;
                    for (let i = 0; i < 4; i++) {
                        const playerElement = document.getElementById('player' + i);
                        if (joueurs[i]) {
                            if (i == 0) {
                                playerElement.querySelector('.playerName').textContent = joueurs[i].username + " (Hôte)";
                            }
                            else {
                                playerElement.querySelector('.playerName').textContent = joueurs[i].username;
                            }

                            const removeButton = playerElement.querySelector('.removePlayer');
                            if (removeButton) {
                                // Si le joueur est présent, afficher le bouton
                                removeButton.style.display = 'block'; // Afficher le bouton
                            } else {
                                // Si le joueur n'est pas présent, masquer le bouton
                                removeButton.style.display = 'none'; // Masquer le bouton
                            }

                            // Met à jour la mise
                            const betElement = playerElement.querySelector('.playerBet');
                            betElement.textContent = joueurs[i].mise === -1 ? '0$' : joueurs[i].mise + '$';

                            // Met à jour le score
                            const scoreElement = playerElement.querySelector('.playerScore');
                            scoreElement.textContent = joueurs[i].score || '0';
                        } 
                        else {
                            // Si le joueur n'est pas présent, on peut mettre à jour l'interface pour refléter cela
                            playerElement.querySelector('.playerName').textContent = 'En attente...';
                            playerElement.querySelector('.playerBet').textContent = '';
                            playerElement.querySelector('.playerScore').textContent = '0';
                        }
                    }
                })
                .catch(error => console.error('Erreur:', error));

        }, 1000);
    </script>
</head>

<body>
    <div class="container">
        <div class="text-center mt-3">
            <?php
            if (!isset($_SESSION["user"])) {
                header("Location: signin.php");
                exit();
            } 
            else if (!isset($_SESSION["selectedParty"])) {
                header("Location: index.php");
                exit();
            }

            if (isset($_POST["partyStart"])) {
                if ($db->startParty($_SESSION["selectedParty"]) <= 20) {
                    $db->resetPartyCards($_SESSION["selectedParty"]);
                }
                $db->startParty($_SESSION["selectedParty"]);
                header("Location: game.php");
            }

            if (isset($_POST["cardStay"])) {

                header("Location: game.php");
            }
            else if (isset($_POST["cardTake"])) {

                header("Location: game.php");
            }
            else if (isset($_POST["cardDouble"]) ) {

                header("Location: game.php");
            }

            if (isset($_POST["bet"])) {
                $db->betParty($_SESSION["selectedParty"], $_POST["bet"]);
                header("Location: game.php");
            }
            ?>
        </div>

        <!-- Header -->
        <div class="bg-perso rounded-4 text-white mt-3 mb-3 p-4">
            <div class="row align-middle">
                <div class="col text-start">
                    <form action="index.php" method="post">
                        <button type="submit" name="partyLeave" class="text-white bg-attention rounded-3 fs-4 py-2 px-4 border-0">Quitter</button>
                    </form>
                </div>
                <div class="col text-center">
                    <h1>Game - #<?php echo ($_SESSION['selectedParty']) ?></h1>
                </div>
                <div class="col text-end pt-2 fs-4">
                    <?php echo ($db->getUserMoney($_SESSION['user']['idUser'])) ?>$
                </div>
            </div>
        </div>

        <!-- Main -->
        <div class="row">
            <?php $joueurs = $db->getPlayerList($_SESSION['selectedParty']); ?>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <?php
                if (isset($joueurs[$i])) {
                    if ($i == 0) {
                        $txt = $joueurs[$i]['username'] . " (Hôte)";
                    } 
                    else {
                        $txt = $joueurs[$i]['username'];
                    }
                } 
                else {
                    $txt = 'En attente...';
                }
                ?>
                <div class="col" id="player<?php echo($i); ?>">
                    <div class="main rounded-4 p-3">
                        <div class="row">
                            <div class="col text-start">
                                <?php if ($_SESSION['user']['idUser'] == $db->getPartyOwner($_SESSION['selectedParty'])['idUser'] && $i != 0): ?>
                                    <button class="text-white bg-attention rounded-3 border-0 removePlayer" style="display: none;">X</button>
                                <?php endif ?>
                                <span class="playerName"><?php echo ($txt); ?></span>
                            </div>
                            <div class="col text-end">
                                <span class="playerBet">
                                    <?php 
                                    if (isset($joueurs[$i])) {
                                        if ($joueurs[$i]['mise'] == -1) {
                                            echo("0$");
                                        }
                                        else {
                                            echo ($joueurs[$i]['mise']."$");
                                        }
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <!-- Boutons de jeu + Score -->
                        <?php if (isset($joueurs[$i]) && $joueurs[$i]['idUser'] == $_SESSION['user']['idUser'] && $joueurs[$i]['canPlay'] == 1): ?>
                            <form action="game.php" method="post">
                                <div class="row mt-2">
                                    <div class="col d-grid">
                                        <button type="submit" name="cardStay" class="text-white bg-attention rounded-3 border-0 py-2 px-2">Rester</button>
                                    </div>
                                    <div class="col d-grid">
                                        <button type="submit" name="cardTake" class="text-white bg-perso rounded-3 border-0 py-2 px-2">Tirer</button>
                                    </div>
                                    <div class="col d-grid">
                                        <button type="submit" name="cardDouble" class="text-white bg-primary rounded-3 border-0 py-2 px-2">Doubler</button>
                                    </div>
                                </div>
                            </form>

                            <div class="text-center pb-5 pt-0">
                                <h1 class="py-5"><span class="playerScore"><?php echo($joueurs[$i]['score']); ?></span></h1>
                            </div>

                        <!-- Mise + Score -->
                        <?php elseif (isset($joueurs[$i]) && $joueurs[$i]['idUser'] == $_SESSION['user']['idUser'] && $joueurs[$i]['mise'] == -1): ?>
                            <form action="game.php" method="post">
                                <div class="row mt-2">
                                    <div class="col-8 d-grid">
                                        <input type="text" name="bet" placeholder="Montant" required class="text-center rounded-3 border-0">
                                    </div>
                                    <div class="col-4 d-grid">
                                        <button type="submit" class="text-white bg-perso rounded-3 border-0 py-2 px-3">Miser</button>
                                    </div>
                                </div>
                            </form>

                            <div class="text-center pb-5 pt-0">
                                <h1 class="py-5"><span class="playerScore">0</span></h1>
                            </div>

                        <!-- Score uniquement -->
                        <?php else: ?>
                            <div class="text-center py-5">
                                <h1 class="py-5"><span class="playerScore"><?php if (isset($joueurs[$i])) echo($joueurs[$i]['score']); else echo("0"); ?></span></h1>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            <?php endfor ?>
        </div>
        <div class="row mt-3">
            <div class="col-3 text-center">
                Tour : <?php echo($db->getPartyTour($_SESSION['selectedParty'])); ?>
            </div>
            <div class="col-6">
                <div class="main rounded-4 p-3 text-center">
                    <h1 class="py-5">0</h1>
                </div>
            </div>
            <div class="col-3">
                <!-- Bouton start -->
                <?php if ($_SESSION['user']['idUser'] == $db->getPartyOwner($_SESSION['selectedParty'])['idUser'] && $db->getPartyStatus($_SESSION['selectedParty']) == "En attente"): ?>
                    <div class="row">
                        <div class="col">
                            <form action="game.php" method="post" class="d-grid">
                                <button type="submit" name="partyStart" class="text-white bg-perso rounded-3 fs-4 py-2 px-4 border-0">Start</button>
                            </form>
                        </div>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>