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
    </head>

    <body>
        <div class="container">
            <div class="bg-perso rounded-4 text-center text-white mt-3 mb-3 p-4">
                <h1>Inscription</h1>
            </div>

            <div class="main rounded-4 p-5">
                <form action="signup.php" method="post">
                    <!-- Email -->
                    <div class="row p-1">
                        <div class="offset-4 col-4 d-grid">
                            <input type="email" name="email" class="border-0 text-center" placeholder="Email" required maxlength="50" value="<?php if (isset($_GET['email'])) echo($_GET['email']);?>">
                        </div>
                    </div>    
                    <!-- Nom d'utilisateur -->
                    <div class="row p-1">
                        <div class="offset-4 col-4 d-grid">
                            <input type="text" name="username" class="border-0 text-center" placeholder="Nom d'utilisateur" required maxlength="20" value="<?php if (isset($_GET['username'])) echo($_GET['username']);?>">
                        </div>
                    </div>
                    <!-- Mot de passe -->
                    <div class="row p-1 pt-5">
                        <div class="offset-4 col-4 d-grid">
                            <input type="password" name="password" class="border-0 text-center" placeholder="Mot de passe" required maxlength="20">
                        </div>
                    </div>
                    <!-- Confirmer le mot de passe -->
                    <div class="row p-1">
                        <div class="offset-4 col-4 d-grid">
                            <input type="password" name="password2" class="border-0 text-center" placeholder="Confirmer le mot de passe" required maxlength="20">
                        </div>
                    </div>
                    <!-- Boutons -->
                    <div class="row text-center pt-5">
                        <div class="col-4 offset-4 d-grid">
                            <button type="submit" class="rounded-4 p-1 border-0 bouton">Créer le compte</button>
                        </div>
                    </div>
                </form>
                <form action="signin.php">
                    <div class="row text-center pt-3">
                        <div class="col-4 offset-4 d-grid">
                            <button type="submit" class="rounded-4 p-1 border-0 bg-transparent">Se connecter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <?php
                if (isset($_SESSION['user'])) {
                    header("Location: index.php");
                    exit();
                }
                else if (isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password2'])) {
                    if ($_POST['password'] == $_POST['password2']) {
                        if ($db->createUser($_POST['username'], $_POST['email'], $_POST['password'])) {
                            header("Location: index.php");
                            exit;
                        }
                        else {
                            $email = $_POST['email'];
                            $username = $_POST['username'];
                            $_SESSION['error'] = "Nom d'utilisateur déjà utilisé";
                            header("Location: signup.php"."?email=$email"."&username=$username");
                            exit;
                        }
                    }
                    else {
                        echo("Les mots de passes ne correspondent pas");
                    }
                }

                if (isset($_SESSION['error'])) {
                    echo($_SESSION['error']);
                    unset($_SESSION['error']);
                }
            ?>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    </body>
</html>