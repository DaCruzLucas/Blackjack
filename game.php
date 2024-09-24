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
        <div class="px-3">
            <div class="text-center mt-3">
                <?php
                    if (!isset($_SESSION["user"])) {
                        header("Location: signin.php");
                        exit();
                    }

                    
                ?>
            </div>

            <div class="bg-primary rounded-4 text-white mt-3 mb-3 p-4">
                <form action="account.php">
                    <div class="row align-middle">
                        <div class="col text-start">
                            <h1>Tasks</h1>
                        </div>
                        <div class="col text-end">
                            <button type="submit" class="bouton text-white bg-transparent rounded-3 fs-4 py-2 px-3 border-0"><i class="fa-solid fa-user" style="color: #ffffff;"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row">
                <div class="col-3">
                    <div class="rounded-4 p-2 main">
                        awdawdawd
                    </div>
                </div>
                <div class="col-9">
                    <div class="rounded-4 p-2 main">
                        awdawdawdaw
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    </body>
</html>
