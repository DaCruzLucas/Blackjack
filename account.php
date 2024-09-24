<?php 
    session_start();
    require_once("Database.php");
    $db = new Database();
?>
<!doctype html>
<html lang="fr">
    <head>
        <title>Task Manager</title>

        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"/>
    
        <link type="text/css" href="style.css" rel="stylesheet">
        
        <script src="https://kit.fontawesome.com/66c180b670.js" crossorigin="anonymous"></script>
    </head>

    <body>
        <div class="container">
            <div class="bg-perso rounded-4 text-white mt-3 mb-3 p-4">
                <form action="signin.php" method="post">
                    <div class="row align-middle">
                        <div class="col text-start">
                            
                        </div>
                        <div class="col text-center">
                            <h1>Votre compte</h1>
                        </div>
                        <div class="col text-end">
                            <button type="submit" class="bouton bg-transparent rounded-3 fs-4 py-2 px-3 border-0"><i class="fa-solid fa-right-from-bracket" style="color: #ffffff;"></i></button>
                            <input type="hidden" name="signout">
                        </div>
                    </div>
                </form>
            </div>

            <div class="main rounded-4 p-5">
                <form action="account.php" method="post">
                    <div class="row p-1">
                        <h2 class="text-center">Mot de passe</h2>
                    </div>
                    <!-- Ancien mot de passe -->
                    <div class="row p-1 mt-3">
                        <div class="offset-4 col-4 d-grid">
                            <input type="password" name="oldpassword" placeholder="Ancien mot de passe" class="border-0 text-center" required maxlength="20">
                        </div>
                    </div>
                    <!-- Mot de passe -->
                    <div class="row p-1 pt-1">
                        <div class="offset-4 col-4 d-grid">
                            <input type="password" name="newpassword" placeholder="Nouveau mot de passe" class="border-0 text-center" required maxlength="20">
                        </div>
                    </div>
                    <!-- Répéter le mot de passe -->
                    <div class="row p-1 pt-1">
                        <div class="offset-4 col-4 d-grid">
                            <input type="password" name="newpassword2" placeholder="Confirmer le mot de passe" class="border-0 text-center" required maxlength="20">
                        </div>
                    </div>
                    <!-- Boutons -->
                    <div class="row text-center pt-4">
                        <div class="col-4 offset-4 d-grid">
                            <button type="submit" class="bouton rounded-4 p-1 border-0">Changer le mot de passe</button>
                        </div>
                    </div>
                </form>
                <form action="index.php">
                    <div class="row text-center pt-5">
                        <div class="col-4 offset-4 d-grid">
                            <button type="submit" class="bouton rounded-4 p-1 border-0">Revenir en arrière</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <?php
                if (!isset($_SESSION["user"])) {
                    header("Location: signin.php");
                }

                if (isset($_POST['oldpassword']) && isset($_POST['newpassword']) && isset($_POST['newpassword2'])) {
                    if ($_POST['newpassword'] == $_POST['newpassword2']) {
                        if ($db->checkPassword($_POST['oldpassword'])) {
                            $db->editPassword($_POST['newpassword']);
                        }
                    }
                    else {
                        echo("Les mots de passe ne correspondent pas");
                    }
                }
            ?>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    </body>
</html>
