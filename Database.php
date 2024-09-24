<?php
class Database {
    private PDO $dbh;

    function __construct() {
        try {
            $this->dbh = new PDO('mysql:host=localhost;dbname=blackjack', "root", "");
            // $this->isConnected();
        } 
        catch (PDOException $e) {
            echo("Erreur lors de la connexion de la base de données :".$e->getMessage()."<br>");
        }
    }

    function createUser($username, $email, $password) {
        $sql = "SELECT * FROM Users WHERE username = '$username' OR email = '$email'";
        $result = $this->dbh->query($sql);

        if ($result->rowCount() > 0) {
            echo "L'utilisateur existe déjà.";
        } 
        else {
            //$passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Users (username, email, password) VALUES ('$username', '$email', '$password')";
            
            try {
                $this->dbh->query($sql);
                echo "Utilisateur créé avec succès";
            } 
            catch (PDOException $e) {
                echo("Erreur lors de la création de l'utilisateur :".$e->getMessage()."<br>");
            }
        }
    }

    function findUser($login, $password) {
        $sql = "SELECT * FROM Users WHERE username = '$login' OR email = '$login'";
        $result = $this->dbh->query($sql);

        if ($result->rowCount() > 0) {
            $user = $result->fetch();

            // (password_verify($password, $user['password']))
            if ($password == $user['password']) {
                $_SESSION['user'] = $user;
                header("Location: index.php");
            } 
            else {
                echo "Mot de passe incorrect";
            }
        }
        else {
            echo "Utilisateur non trouvé";
        }
    }

    function checkPassword($password) {
        $userId = $_SESSION["user"]["idUser"];
        $sql = "SELECT * FROM Users WHERE idUser = '$userId' AND password = '$password'";
        $result = $this->dbh->query($sql);

        if ($result->rowCount() > 0) {
            return true;
        }
        else {
            echo "Mot de passe incorrect";
            return false;
        }
    }

    function editPassword($newPassword) {
        $userId = $_SESSION["user"]["idUser"];
        $sql = "UPDATE Users SET password = '$newPassword' WHERE idUser = '$userId'";

        try {
            $this->dbh->query($sql);
            echo "Mot de passe mis à jour avec succès.";
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la mise à jour du mot de passe : " . $e->getMessage();
        }
    }

    function getUserMoney($idUser) {
        $sql = "SELECT money FROM Users WHERE idUser = :idUser";

        try {
            
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':idUser', $idUser, PDO::PARAM_INT);
            
            // Exécuter la requête
            $stmt->execute();
            
            // Récupérer le résultat
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérifier si l'utilisateur existe et retourner son argent
            if ($result) {
                return $result['money'];
            } else {
                echo "Utilisateur non trouvé.";
                return null;
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la récupération de l'argent de l'utilisateur : " . $e->getMessage();
        }
    }

    function createParty($type) {
        $private = ($type == "public") ? 0 : 1;
        $userId = $_SESSION["user"]["idUser"];

        $sql = "INSERT INTO Parties (status, private, croupier, cards, tour) VALUES (:status, :private, :croupier, :cards, :tour)";
        $sql2 = "INSERT INTO Joueurs (idUser, idPartie, chef, mise, canPlay, doubler, blackjack, cards) VALUES (:idUser, :idPartie, :chef, :mise, :canPlay, :doubler, :blackjack, :cards)";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':status', 'En attente', PDO::PARAM_STR);
            $stmt->bindValue(':private', $private, PDO::PARAM_BOOL);
            $stmt->bindValue(':croupier', 0, PDO::PARAM_INT);
            $stmt->bindValue(':cards', '', PDO::PARAM_STR);
            $stmt->bindValue(':tour', 1, PDO::PARAM_INT);
            $stmt->execute();

            $partieId = $this->dbh->lastInsertId();
            $_SESSION['selectedParty'] = $partieId;

            $stmt2 = $this->dbh->prepare($sql2);
            $stmt2->bindValue(':idUser', $userId, PDO::PARAM_INT);
            $stmt2->bindValue(':idPartie', $partieId, PDO::PARAM_INT);
            $stmt2->bindValue(':chef', 1, PDO::PARAM_BOOL);
            $stmt2->bindValue(':mise', 0, PDO::PARAM_INT);
            $stmt2->bindValue(':canPlay', 0, PDO::PARAM_BOOL);
            $stmt2->bindValue(':doubler', 0, PDO::PARAM_BOOL);
            $stmt2->bindValue(':blackjack', 0, PDO::PARAM_BOOL);
            $stmt2->bindValue(':cards', '', PDO::PARAM_STR);
            $stmt2->execute();

            echo "Partie créée avec succès.";
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la création de la partie : " . $e->getMessage();
        }
    }

    function joinParty($idPartie) {
        $userId = $_SESSION['user']['idUser'];
        $playerCount = $this->getPartyPlayersCount($idPartie);
    
        $sqlCheck = "SELECT * FROM Parties WHERE idPartie = :idPartie";
        $sqlCheck2 = "SELECT * FROM Joueurs WHERE idUser = :userId AND idPartie = :idPartie";
        $sql = "INSERT INTO Joueurs (idUser, idPartie, chef, mise, canPlay, doubler, blackjack, cards) VALUES (:userId, :idPartie, 0, 0, 0, 0, 0, '')";

        try {
            $stmtCheck = $this->dbh->prepare($sqlCheck);
            $stmtCheck->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmtCheck->execute();

            $stmtCheck2 = $this->dbh->prepare($sqlCheck2);
            $stmtCheck2->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmtCheck2->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmtCheck2->execute();
            
            if ($stmtCheck->rowCount() == 0) {
                echo "La partie n'existe pas";
                return false;
            }
            else if ($stmtCheck2->rowCount() > 0) {
                echo "Vous êtes déjà dans la partie";
            }
            else if ($playerCount >= 4) {
                echo "La partie est complète";
                return false;
            }
            else {
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
                $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
                $stmt->execute();
            }
    
            $_SESSION['selectedParty'] = $idPartie;

            echo "Vous avez rejoint la partie avec succès.";

            return true;
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la tentative de rejoindre la partie : " . $e->getMessage();
        }
    }

    function leaveParty($idPartie) {
        $userId = $_SESSION['user']['idUser'];

        $sql = "DELETE FROM Joueurs WHERE idUser = :userId AND idPartie = :idPartie";
        
        $this->checkParties();
        
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) {
                echo "Vous avez quitté la partie.";
                unset($_SESSION['selectedParty']);
            }
            else {
                echo "Impossible de quitter la partie ou vous n'êtes pas dans cette partie.";
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la tentative de quitter la partie : " . $e->getMessage();
        }
    }

    function getParties() {
        $sql = "SELECT * FROM Parties WHERE private = :private";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':private', 1, PDO::PARAM_BOOL);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } 
            else {
                return null;
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la récupération des parties : " . $e->getMessage();
        }
    }

    function getPartyOwner($idPartie) {
        $sql = "SELECT Users.* FROM Users INNER JOIN Joueurs ON Users.idUser = Joueurs.idUser WHERE Joueurs.idPartie = :idPartie AND Joueurs.chef = 1";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la récupération du créateur de la partie : " . $e->getMessage();
        }
    }

    function getPartyPlayersCount($idPartie) {
        $sql = "SELECT COUNT(*) AS playerCount FROM Joueurs WHERE idPartie = :idPartie";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['playerCount'];
        } 
        catch (PDOException $e) {
            echo "Erreur lors du comptage des joueurs dans la partie : " . $e->getMessage();
        }
    }

    function checkParties() {
        try {
            $sql = "SELECT p.idPartie FROM Parties p LEFT JOIN Joueurs j ON p.idPartie = j.idPartie WHERE j.idPartie IS NULL";
            
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute();

            $emptyParties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($emptyParties) {
                $deleteSql = "DELETE FROM Parties WHERE idPartie = :idPartie";
                $deleteStmt = $this->dbh->prepare($deleteSql);

                foreach ($emptyParties as $party) {
                    $deleteStmt->bindValue(':idPartie', $party['idPartie'], PDO::PARAM_INT);
                    $deleteStmt->execute();
                }
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la vérification des parties : " . $e->getMessage();
        }
    }

    function isConnected() {
        if ($this->dbh) {
            echo("Connexion à la base de données réussie <br>");
        }
    }
}