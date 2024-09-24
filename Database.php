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
        // Récupérer l'identifiant de l'utilisateur connecté
        $userId = $_SESSION['user']['idUser'];
    
        try {
            // Vérifier si le joueur est déjà dans la partie
            $checkSql = "SELECT * FROM Joueurs WHERE idUser = :userId AND idPartie = :idPartie";
            $checkStmt = $this->dbh->prepare($checkSql);
            $checkStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $checkStmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $checkStmt->execute();
    
            if ($checkStmt->rowCount() > 0) {
                echo "Vous êtes déjà dans cette partie.";
                return;
            }
    
            // Compter le nombre de joueurs dans la partie
            $playerCount = $this->getPartyPlayersCount($idPartie);
            
            // Vérifier si la partie est complète (maximum 4 joueurs)
            if ($playerCount >= 4) {
                echo "La partie est complète.";
                return;
            }
    
            // Ajouter l'utilisateur à la partie
            $insertSql = "INSERT INTO Joueurs (idUser, idPartie, chef, mise, canPlay, doubler, blackjack, cards) 
                          VALUES (:userId, :idPartie, 0, 0, 1, 0, 0, '')"; // chef=0, mise=0, canPlay=1, doubler=0, blackjack=0, cards=''
            
            $insertStmt = $this->dbh->prepare($insertSql);
            $insertStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $insertStmt->bindValue(':idPartie', $idPartie, PDO::PARAM_INT);
            $insertStmt->execute();
    
            echo "Vous avez rejoint la partie avec succès.";
        } 
        catch (PDOException $e) {
            echo "Erreur lors de la tentative de rejoindre la partie : " . $e->getMessage();
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

    function isConnected() {
        if ($this->dbh) {
            echo("Connexion à la base de données réussie <br>");
        }
    }
}