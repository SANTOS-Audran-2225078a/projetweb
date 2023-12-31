<?php
namespace models;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../../../config.php";

// Créez une connexion à la base de données

class Users {
    private $conn;


    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getUserByEmailAndPassword($email, $password)
    {
        try {
            // Prepare the SQL query with placeholders
            $query = "SELECT * FROM users WHERE email = ? AND password = ?";
            $stmt = $this->conn->prepare($query);

            // Bind the parameters
            $stmt->bind_param('ss', $email, $password);

            // Execute the query
            $stmt->execute();

            // Get the result as an associative array
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            return $user;
        } catch (Exception $e) {
            // Handle exceptions here
            echo "Erreur : " . $e->getMessage();
            return null;
        }
    }


    public function UtilisateurExistant($pseudo)
    {
        // Je crée une requête SQL pour sélectionner tous les enregistrements de la table "users" où la colonne "pseudo" correspond à la valeur passée en argument.
        $query = "SELECT * FROM users WHERE pseudo=:pseudo";

        // J'obtiens une connexion à la base de données en utilisant la méthode "connectwap_bd()"
        // Ensuite, je prépare la requête SQL en utilisant la connexion obtenue.
        $stmt = $this->connectwap_bd()->prepare($query);

        // Je lie la valeur du paramètre ":pseudo" de la requête SQL à la valeur du paramètre "$pseudo" passé en argument.
        // J'indique également que "$pseudo" est une chaîne de caractères (PDO::PARAM_STR).
        $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);

        // J'exécute la requête préparée.
        $stmt->execute();
    }

    // Je définis une méthode publique nommée "MailExistant" qui vérifie si un utilisateur avec l'adresse e-mail donnée existe dans la base de données.
    public function MailExistant($email) {
        // Je crée une requête SQL pour sélectionner tous les enregistrements de la table "users" où la colonne "email" correspond à la valeur passée en argument.
        // La requête doit utiliser le signe de deux-points (:) pour définir le paramètre nommé "email".
        $query = "SELECT * FROM users WHERE email=:email";

        // J'obtiens une connexion à la base de données en utilisant la méthode "connectwap_bd()" (non montrée ici, mais probablement définie ailleurs dans la classe).
        // Ensuite, je prépare la requête SQL en utilisant la connexion obtenue.
        $stmt = $this->connectwap_bd()->prepare($query);

        // Je lie la valeur du paramètre nommé ":email" de la requête SQL à la valeur du paramètre "$email" passé en argument.
        // J'indique également que "$email" est une chaîne de caractères (PDO::PARAM_STR).
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // J'exécute la requête préparée.
        $stmt->execute();
    }
    public function AjoutUtilisateur() {

        if (!empty($_POST['pseudo'])  && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password_retype']) && !empty($_POST['age']) && !empty($_POST['genre'])) {
        // Patch XSS
        $pseudo = htmlspecialchars($_POST['pseudo']);
        $email = htmlspecialchars($_POST['email']);
        $age = htmlspecialchars($_POST['age']);
        $genre = htmlspecialchars($_POST['genre']);
        $password = htmlspecialchars($_POST['password']);
        $password_retype = htmlspecialchars($_POST['password_retype']);

        // Vérifie si l'utilisateur existe
        $check = $conn->prepare('SELECT pseudo, age, genre, email, password FROM users WHERE email = ?');
        $check->bind_param('s', $email);
        $check->execute();
        $result = $check->get_result();
        $row = $result->num_rows;

        $email = strtolower($email); // Convertit l'e-mail en minuscules pour éviter les doublons d'adresses e-mail

    // Si la requête ne renvoie aucun résultat, l'utilisateur n'existe pas
    if ($row == 0) {
        if (strlen($pseudo) <= 100) { // Vérifie que la longueur du 'nom' est inférieure ou égale à 100
            if (strlen($email) <= 100) { // Vérifie que la longueur de 'email' est inférieure ou égale à 100
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) { // Vérifie si l'e-mail est au bon format
                    if ($password === $password_retype) { // Vérifie si les deux mots de passe saisis correspondent

                        // Hachage du mot de passe en utilisant Bcrypt avec un coût de 12
                        //$cost = ['cost' => 12];
                        //$password = password_hash($password, PASSWORD_BCRYPT, $cost);

                        // Stocke l'adresse IP
                        $ip = $_SERVER['REMOTE_ADDR'];

                        // Génère un jeton
                        $token = bin2hex(openssl_random_pseudo_bytes(64));

                        // Insère les données dans la base de données
                        $insert = $conn->prepare('INSERT INTO users(pseudo, password, email, age, genre, ip, token) VALUES(?, ?, ?, ?, ?, ?, ?)');
                        $insert->bind_param('sssssss', $pseudo, $password, $email, $age, $genre, $ip, $token);
                        $insert->execute();

                        // Redirection avec un message de succès
                        header('Location: ../views/inscription_view.php?reg_err=success');
                        die();
                    } else {
                        header('Location: ../views/inscription_view.php?reg_err=password');
                        die();
                    }
                } else {
                    header('Location: ../views/inscription_view.php.php?reg_err=email');
                    die();
                }
            } else {
                header('Location: ../views/inscription_view.php?reg_err=email_length');
                die();
            }
        } else {
            header('Location: ../views/inscription_view.php?reg_err=pseudo_length');
            die();
        }
    }
} else {
    header('Location: ../views/layout.php?reg_err=already');
    die();

}

    //}
    }








    public function changeMotDePasse($newPassword, $confirmPassword, $email){
        if ($newPassword == $confirmPassword) {
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password =:newPassword WHERE email = email";
            $stmt = $this->connectwap_bd()->prepare($query);
            $stmt -> bindParam('newMDP', $hashed_password, PDO::PARAM_STR);
            $stmt -> bindParam('email', $email, PDO::PARAM_STR);

        }
    }
}
?>
