<?php
session_start(); // Pour stocker les messages flash

// Configuration de la connexion à la base de données
include_once '../../config/db.php';
// Vérification que le formulaire a été soumis via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sectionNom']) && !empty(trim($_POST['sectionNom']))) {
        $sectionNom = trim($_POST['sectionNom']);

        try {
            // Vérifier si la section existe déjà
            $verifStmt = $pdo->prepare("SELECT COUNT(*) FROM sections WHERE nom_section = :nom_section");
            $verifStmt->bindValue(':nom_section', $sectionNom, PDO::PARAM_STR);
            $verifStmt->execute();
            $existe = $verifStmt->fetchColumn();

            if ($existe > 0) {
                $_SESSION['message'] = "Cette section existe déjà.";
                $_SESSION['message_type'] = "warning";
            } else {
                // Insertion de la nouvelle section
                $stmt = $pdo->prepare("INSERT INTO sections (nom_section) VALUES (:nom_section)");
                $stmt->bindValue(':nom_section', $sectionNom, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Section ajoutée avec succès.";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Erreur lors de l'ajout de la section.";
                    $_SESSION['message_type'] = "danger";
                }
            }

        } catch (PDOException $e) {
            $_SESSION['message'] = "Erreur lors de l'exécution : " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }

    } else {
        $_SESSION['message'] = "Le champ 'Nom de la section' est obligatoire.";
        $_SESSION['message_type'] = "warning";
    }

    // Redirection après traitement
    header("Location: index.php"); // À ajuster selon ta page
    exit();

} else {
    // Requête non autorisée
    header("Location: index.php");
    exit();
}
?>
