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
            // echo "L'utilisateur existe déjà.";
        } 
        else {
            //$passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Users (username, email, password) VALUES ('$username', '$email', '$password')";
            
            try {
                $this->dbh->query($sql);
                // echo "Utilisateur créé avec succès";
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
                // echo "Identifiant ou mot de passe incorrect";
            }
        }
        else {
            // echo "Identifiant ou mot de passe incorrect";
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
            // echo "Mot de passe mis à jour avec succès.";
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
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['money'];
            }
            else {
                // echo "Utilisateur non trouvé.";
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
        $sql2 = "INSERT INTO Joueurs (idUser, idPartie, chef, mise, canPlay, doubler, blackjack, score, asCard) VALUES (:idUser, :idPartie, :chef, :mise, :canPlay, :doubler, :blackjack, :score, :asCard)";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':status', 'En attente', PDO::PARAM_STR);
            $stmt->bindValue(':private', $private, PDO::PARAM_BOOL);
            $stmt->bindValue(':croupier', 0, PDO::PARAM_INT);
            $stmt->bindValue(':cards', '1234567890000123456789000012345678900001234567890000', PDO::PARAM_STR);
            $stmt->bindValue(':tour', -1, PDO::PARAM_INT);
            $stmt->execute();

            $partieId = $this->dbh->lastInsertId();
            $_SESSION['selectedParty'] = $partieId;

            $stmt2 = $this->dbh->prepare($sql2);
            $stmt2->bindValue(':idUser', $userId, PDO::PARAM_INT);
            $stmt2->bindValue(':idPartie', $partieId, PDO::PARAM_INT);  
            $stmt2->bindValue(':chef', 1, PDO::PARAM_BOOL);
            $stmt2->bindValue(':mise', -1, PDO::PARAM_INT);
            $stmt2->bindValue(':canPlay', 0, PDO::PARAM_BOOL);
            $stmt2->bindValue(':doubler', 0, PDO::PARAM_BOOL);
            $stmt2->bindValue(':blackjack', 0, PDO::PARAM_BOOL);
            $stmt2->bindValue(':score', 0, PDO::PARAM_INT);
            $stmt2->bindValue(':asCard', 0, PDO::PARAM_BOOL);
            $stmt2->execute();

            // echo "Partie créée avec succès.";
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
        $sql = "INSERT INTO Joueurs (idUser, idPartie, chef, mise, canPlay, doubler, blackjack, score, asCard) VALUES (:userId, :idPartie, 0, -1, 0, 0, 0, 0, 0)";

        try {
            $stmtCheck = $this->dbh->prepare($sqlCheck);
            $stmtCheck->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmtCheck->execute();

            $stmtCheck2 = $this->dbh->prepare($sqlCheck2);
            $stmtCheck2->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmtCheck2->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmtCheck2->execute();
            
            if ($stmtCheck->rowCount() == 0) {
                // echo "La partie n'existe pas";
                return false;
            }
            else if ($stmtCheck2->rowCount() > 0) {
                // echo "Vous êtes déjà dans la partie";
            }
            else if ($playerCount >= 4) {
                // echo "La partie est complète";
                return false;
            }
            else {
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
                $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
                $stmt->execute();
            }
    
            $_SESSION['selectedParty'] = $idPartie;

            // echo "Vous avez rejoint la partie avec succès.";

            return true;
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la tentative de rejoindre la partie : " . $e->getMessage();
        }
    }

    function leaveParty($idPartie) {
        $userId = $_SESSION['user']['idUser'];
            
        try {
            $sqlCheck = "SELECT chef FROM Joueurs WHERE idUser = :userId AND idPartie = :idPartie";
            $stmtCheck = $this->dbh->prepare($sqlCheck);
            $stmtCheck->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmtCheck->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            $sqlDelete = "DELETE FROM Joueurs WHERE idUser = :userId AND idPartie = :idPartie";
            $stmtDelete = $this->dbh->prepare($sqlDelete);
            $stmtDelete->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmtDelete->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmtDelete->execute();

            if ($resultCheck['chef'] == 1) {
                $sqlNewChef = "SELECT idJoueur, idUser FROM Joueurs WHERE idPartie = :idPartie ORDER BY idJoueur ASC LIMIT 1";
                $stmtNewChef = $this->dbh->prepare($sqlNewChef);
                $stmtNewChef->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
                $stmtNewChef->execute();
    
                $newChef = $stmtNewChef->fetch(PDO::FETCH_ASSOC);
                
                if ($newChef) {
                    $sqlUpdateChef = "UPDATE Joueurs SET chef = 1 WHERE idUser = :newChef AND idPartie = :idPartie";
                    $stmtUpdateChef = $this->dbh->prepare($sqlUpdateChef);
                    $stmtUpdateChef->bindValue(':newChef', $newChef['idUser'], PDO::PARAM_INT);
                    $stmtUpdateChef->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
                    $stmtUpdateChef->execute();
                    // echo "Nouveau chef défini";
                }
            }
    
            if ($stmtDelete->rowCount() > 0) {
                unset($_SESSION['selectedParty']);
                // echo "Vous avez quitté la partie.";
            } 
            else {
                // echo "Impossible de quitter la partie ou vous n'êtes pas dans cette partie.";
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la tentative de quitter la partie : " . $e->getMessage();
        }
    }

    function getPlayerList($idPartie) {
        $sql = "SELECT Users.idUser, Users.username, Joueurs.canPlay, Joueurs.mise, Joueurs.doubler, Joueurs.blackjack, Joueurs.score, Joueurs.asCard FROM Joueurs JOIN Users ON Joueurs.idUser = Users.idUser WHERE Joueurs.idPartie = :idPartie ORDER BY Joueurs.idJoueur";
    
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la récupération des joueurs : " . $e->getMessage();
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

    function getPartyStatus($idPartie) {
        $sql = "SELECT status FROM Parties WHERE idPartie = :idPartie";
    
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['status'];
            } 
            else {
                return null;
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la récupération du statut de la partie : " . $e->getMessage();
            return null;
        }
    }

    function getPartyTour($idPartie) {
        $sql = "SELECT tour FROM Parties WHERE idPartie = :idPartie";
    
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmt->execute();
    
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['tour'];
            } 
            else {
                return -1;
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la récupération du tour de la partie : " . $e->getMessage();
            return null;
        }
    }

    function getPartyCardsCount($idPartie) {
        $sql = "SELECT CHAR_LENGTH(cards) AS nbCaracteres FROM Parties WHERE idPartie = :idPartie";
        
        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['nbCaracteres'];
            } 
            else {
                return 0;
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la récupération du nombre de cartes : " . $e->getMessage();
            return false;
        }
    }

    function resetPartyCards($idPartie) {
        
    }

    function pickRandomCard($idPartie) {
        $sql = "SELECT cards FROM Parties WHERE idPartie = :idPartie";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $cards = $result['cards'];
            $randomIndex = rand(0, strlen($cards) - 1); // Choisir un index aléatoire
            $randomCard = $cards[$randomIndex]; // Carte choisie

            $updatedCards = substr($cards, 0, $randomIndex) . substr($cards, $randomIndex + 1); // Enlever la carte de la chaîne

            $sqlUpdate = "UPDATE Parties SET cards = :updatedCards WHERE idPartie = :idPartie";
            $stmtUpdate = $this->dbh->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':updatedCards', $updatedCards, PDO::PARAM_STR);
            $stmtUpdate->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $stmtUpdate->execute();

            return $randomCard; // Retourner la carte choisie

        } 
        catch (PDOException $e) {
            echo "Erreur lors de la récupération et suppression de la carte : " . $e->getMessage();
        }
    }

    function betParty($idPartie, $mise) {
        $userId = $_SESSION['user']['idUser'];
    
        $sqlCheckMoney = "SELECT money FROM Users WHERE idUser = :idUser";
        $sqlUpdateBet = "UPDATE Joueurs SET mise = :mise WHERE idUser = :idUser AND idPartie = :idPartie";
    
        try {
            $stmtCheck = $this->dbh->prepare($sqlCheckMoney);
            $stmtCheck->bindValue(':idUser', $userId, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            $resultCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($resultCheck && $resultCheck['money'] >= $mise) {
                $stmtUpdateBet = $this->dbh->prepare($sqlUpdateBet);
                $stmtUpdateBet->bindValue(':mise', $mise, PDO::PARAM_INT);
                $stmtUpdateBet->bindValue(':idUser', $userId, PDO::PARAM_INT);
                $stmtUpdateBet->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
                $stmtUpdateBet->execute();
    
                return true;
            } 
            else {
                return false;
            }
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la mise à jour de la mise : " . $e->getMessage();
            return false;
        }
    }
    
    
    function startParty($idPartie) {

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

                $deleteSql2 = "DELETE FROM Joueurs WHERE idPartie = :idPartie";
                $deleteStmt2 = $this->dbh->prepare($deleteSql2);

                foreach ($emptyParties as $party) {
                    $deleteStmt->bindValue(':idPartie', $party['idPartie'], PDO::PARAM_INT);
                    $deleteStmt->execute();

                    $deleteStmt2->bindValue(':idPartie', $party['idPartie'], PDO::PARAM_INT);
                    $deleteStmt2->execute();
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