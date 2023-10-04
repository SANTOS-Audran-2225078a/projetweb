<?php
include('../../blog/models/users_model.php');
$publicationModel = new PublicationModel();
$Publications = $publicationModel->getPublications();
$cheminConfig = '/home/wap/www/config.php';

if (file_exists($cheminConfig)) {
    require_once $cheminConfig;
} else {
    // Gérez l'erreur de manière appropriée, par exemple en la journalisant ou en affichant un message d'erreur.
    echo "Erreur : fichier 'config.php' introuvable.";
    // Vous pouvez décider de quitter le script ou de prendre d'autres mesures appropriées.
    exit();
}
session_start();
require_once '../../../config.php';
require_once '../models/users_model.php';

if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $userModel = new UserModel($conn);
    $user = $userModel->getUserByEmailAndPassword($email, $password);

    if ($user) {
        // L'authentification réussit, vous pouvez stocker des informations dans la session si nécessaire
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];

        // Redirigez l'utilisateur vers la page d'accueil
        header("Location: index.php");
        exit();
    } else {
        // L'authentification a échoué, redirigez vers la page d'erreur
        header("Location: index.php?error=Incorrect User name or password");
        exit();
    }
} else {
    // Les champs email et mot de passe sont vides, redirigez vers la page de connexion
    header("Location: index.php");
    exit();
}

session_start();
require_once 'config.php';
require_once '../models/users_model.php';


if (!empty($_POST['pseudo']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password_retype']) && !empty($_POST['age']) && !empty($_POST['genre'])) {
    // Valide et sécurise les données
    $pseudo = htmlspecialchars($_POST['pseudo']);
    $email = htmlspecialchars($_POST['email']);
    $age = htmlspecialchars($_POST['age']);
    $genre = htmlspecialchars($_POST['genre']);
    $password = htmlspecialchars($_POST['password']);
    $password_retype = htmlspecialchars($_POST['password_retype']);

    if ($user) {
        // L'authentification réussit, vous pouvez stocker des informations dans la session si nécessaire
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];

        // Redirigez l'utilisateur vers index.php
        header("Location: index.php");
        exit();
    } else {
        // L'authentification a échoué, redirigez vers la page d'erreur
        header("Location: index.php?error=Incorrect User name or password");
        exit();
    }
    // Vérification de l'existence de l'utilisateur
    $userModel = new UserModel($conn);
    if (!$userModel->isUserExists($email)) {

        // L'authentification réussit, vous pouvez stocker des informations dans la session si nécessaire
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_email'] = $user['email'];

        // Vérification du formulaire
        if (strlen($pseudo) <= 100 && strlen($email) <= 100 && filter_var($email, FILTER_VALIDATE_EMAIL) && $password === $password_retype) {
            // Hachage du mot de passe (vous pouvez ajouter le hachage ici si nécessaire)
            //$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Adresse IP de l'utilisateur
            $ip = $_SERVER['REMOTE_ADDR'];

            // Génération d'un jeton
            $token = bin2hex(openssl_random_pseudo_bytes(64));

            // Ajout de l'utilisateur
            $userAdded = $userModel->addUser($pseudo, $email, $age, $genre, $password, $ip, $token);

            if ($userAdded) {
                header('Location: inscription.php?reg_err=success');
                exit();
            } else {
                header('Location: inscription.php?reg_err=database');
                exit();
            }
        } else {
            header('Location: inscription.php?reg_err=invalid');
            exit();
        }
    } else {
        header('Location: inscription.php?reg_err=already');
        exit();
    }
} else {
    header('Location: inscription.php');
    exit();
}

