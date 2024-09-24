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
            else if (!isset($_SESSION["selectedParty"])) {
                header("Location: index.php");
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
                    <h1>Game - #<?php echo($_SESSION['selectedParty']) ?></h1>
                </div>
                <div class="col text-end pt-2 fs-4">
                    <?php echo($db->getUserMoney($_SESSION['user']['idUser'])) ?>$
                </div>
            </div>
        </div>
        
        <!-- Main -->
        <div class="row text-center">
            <div class="col">
                <div class="main rounded-4">1</div>
            </div>
            <div class="col">
                <div class="main rounded-4">2</div>
            </div>
            <div class="col">
                <div class="main rounded-4">3</div>
            </div>
            <div class="col">
                <div class="main rounded-4">4</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>