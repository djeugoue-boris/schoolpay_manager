<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (empty($identifiant) || empty($mot_de_passe)) {
        $_SESSION['error'] = "Identifiant et mot de passe sont obligatoires.";
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, identifiant, mot_de_passe, role FROM utilisateurs WHERE identifiant = ?");
    $stmt->execute([$identifiant]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['identifiant'] = $user['identifiant'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        switch ($user['role']) {
            case 'superadmin':
                echo('vous n\'etes pas autorisé à vous connecter');
                break;
            case 'admin':
                header('Location: ../dashboard_administrateur.php');
                break;
            case 'caissier':
                 echo('vous n\'etes pas autorisé à vous connecter');
                break;
            default:
                // Unknown role, redirect to a default page or logout
                session_destroy();
                header('Location: login.php?error=role');
                exit;
        }
        exit;
    } else {
        $_SESSION['error'] = "Identifiant ou mot de passe incorrect.";
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>
