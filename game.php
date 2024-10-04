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
                        // Recharger le joueur si besoin
                        if (data.idUser == joueurs[i].idUser && joueurs[i].refresh == 1) {
                            fetch('update_game_refresh.php')
                            .then(function() {
                                location.reload();
                            })
                            .catch(error => console.error('Erreur:', error));
                        }

                        if (i == 0) {
                            playerElement.querySelector('.playerName').textContent = joueurs[i].username + " (Hôte)";
                        }
                        else {
                            playerElement.querySelector('.playerName').textContent = joueurs[i].username;
                        }

                        const removeElement = playerElement.querySelector('.playerRemove');
                        if (removeElement) removeElement.innerHTML = '<button class="text-white bg-attention rounded-3 border-0 removePlayer" style="display: inline;">X</button>';

                        const betElement = playerElement.querySelector('.playerBet');
                        betElement.textContent = joueurs[i].mise === -1 ? '0$' : joueurs[i].mise + '$';

                        const scoreElement = playerElement.querySelector('.playerScore');
                        if (joueurs[i].asCard == 1) {
                            scoreElement.textContent = joueurs[i].score + "/" + (joueurs[i].score + 10);
                        }
                        else {
                            scoreElement.textContent = joueurs[i].score;
                        }
                    }
                    else {
                        // Si le joueur n'est pas présent, on peut mettre à jour l'interface pour refléter cela
                        playerElement.querySelector('.playerName').textContent = 'En attente...';
                        playerElement.querySelector('.playerBet').textContent = '';
                        playerElement.querySelector('.playerScore').textContent = '0';

                        const removeElement = playerElement.querySelector('.playerRemove');
                        if (removeElement) removeElement.innerHTML = "";
                    }
                }

                const croupierElement = document.getElementById('croupier');
                croupierElement.textContent = data.party.croupier;
            })
            .catch(error => console.error('Erreur:', error));

        }, 1000);

        function startBtn(event) {
            event.target.style.display = "none";
            event.target.form.submit();
        }
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

            if (isset($_POST["cardStay"])) {
                $db->stayCard($_SESSION["selectedParty"]);
                header("Location: game.php");
                exit();
            }
            else if (isset($_POST["cardTake"])) {
                $db->takeCard($_SESSION["selectedParty"]);
                header("Location: game.php");
                exit();
            }
            else if (isset($_POST["cardDouble"]) ) {
                $db->doubleCard($_SESSION["selectedParty"]);
                header("Location: game.php");
                exit();
            }

            if (isset($_POST["bet"])) {
                $db->betParty($_SESSION["selectedParty"], $_POST["bet"]);
                header("Location: game.php");
                exit();
            }

            if (isset($_POST["partyStart"])) {
                $db->checkPartyCards($_SESSION["selectedParty"]);
                $db->startParty($_SESSION["selectedParty"]);
                header("Location: game.php");
                exit();
            }
            else if (isset($_POST["partyRestart"])) {
                $db->resetParty($_SESSION["selectedParty"]);
                header("Location: game.php");
                exit();
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
                                <?php if ($i != 0 && $_SESSION['user']['idUser'] == $db->getPartyOwner($_SESSION['selectedParty'])['idUser'] && isset($joueurs[$i])): ?>
                                    <span class="playerRemove"><button class="text-white bg-attention rounded-3 border-0 removePlayer" style="display: inline;">X</button></span>
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
                        <?php if (isset($joueurs[$i]) && $joueurs[$i]['idUser'] == $_SESSION['user']['idUser'] && $joueurs[$i]['canPlay'] == 1 && $_SESSION['user']['idUser'] == $db->getPartyTour($_SESSION['selectedParty'])): ?>
                            <form action="game.php" method="post">
                                <div class="row mt-2">
                                    <div class="col-4 d-grid">
                                        <button type="submit" name="cardStay" class="text-white bg-attention rounded-3 border-0 py-2 px-2">Rester</button>
                                    </div>
                                    <div class="col-4 d-grid">
                                        <button type="submit" name="cardTake" class="text-white bg-perso rounded-3 border-0 py-2 px-2">Tirer</button>
                                    </div>
                                    <?php if ($joueurs[$i]['canDouble'] == 1 && $joueurs[$i]['money'] >= $joueurs[$i]['mise']): ?>
                                        <div class="col-4 d-grid">
                                            <button type="submit" name="cardDouble" class="text-white bg-primary rounded-3 border-0 py-2 px-2">Doubler</button>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </form>

                            <div class="text-center pb-5 pt-0">
                                <h1 class="py-5"><span class="playerScore"><?php if ($joueurs[$i]['asCard']) echo($joueurs[$i]['score']."/".($joueurs[$i]['score'] + 10)); else echo($joueurs[$i]['score']); ?></span></h1>
                            </div>

                        <!-- Mise + Score -->
                        <?php elseif (isset($joueurs[$i]) && $joueurs[$i]['idUser'] == $_SESSION['user']['idUser'] && $joueurs[$i]['mise'] == -1 && $db->getPartyStatus($_SESSION['selectedParty']) == "En attente"): ?>
                            <form action="game.php" method="post">
                                <div class="row mt-2">
                                    <div class="col-8 d-grid">
                                        <input type="number" name="bet" placeholder="Montant" required class="text-center rounded-3 border-0" min="0">
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
                                <h1 class="py-5"><span class="playerScore"><?php if (isset($joueurs[$i])) { if ($joueurs[$i]['asCard']) echo($joueurs[$i]['score']."/".($joueurs[$i]['score'] + 10)); else echo($joueurs[$i]['score']); } else echo("0"); ?></span></h1>
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
                    <h1 class="py-5"><span id="croupier"><?php echo($db->getPartyInfos($_SESSION['selectedParty'])['croupier']) ?></span></h1>
                </div>
            </div>
            <div class="col-3">
                <!-- Bouton start -->
                <?php if ($_SESSION['user']['idUser'] == $db->getPartyOwner($_SESSION['selectedParty'])['idUser']): ?>
                    <?php $status = $db->getPartyStatus($_SESSION['selectedParty']); ?>
                    <?php if ($status == "En attente"): ?>
                        <div class="row">
                            <div class="col">
                                <form action="game.php" method="post" class="d-grid">
                                    <button type="submit" name="partyStart" class="text-white bg-perso rounded-3 fs-4 py-2 px-4 border-0" onclick="startBtn(event)">Lancer</button>
                                </form>
                            </div>
                        </div>
                    <?php endif ?>
                    <!-- Bouton restart -->
                    <?php if ($status == "Résultats"): ?>
                        <div class="row">
                            <div class="col">
                                <form action="game.php" method="post" class="d-grid">
                                    <button type="submit" name="partyRestart" class="text-white bg-perso rounded-3 fs-4 py-2 px-4 border-0" onclick="startBtn(event)">Relancer</button>
                                </form>
                            </div>
                        </div>
                    <?php endif ?>
                <?php endif ?>
            </div>
        </div>

        <div class="text-center mt-3">
            <?php
            
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>
</html>