<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifie si tous les champs nécessaires sont présents
    if (isset($_POST['id'], $_POST['nom'], $_POST['prenom'], $_POST['telephone'], $_POST['frais_inscription'], $_POST['bourse'])) {
        $id = intval($_POST['id']);
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $telephone = trim($_POST['telephone']);
        $frais_inscription = floatval($_POST['frais_inscription']);
        $bourse = floatval($_POST['bourse']);

        try {
            $sql = "UPDATE inscriptions 
                    SET nom = :nom, prenom = :prenom, telephone = :telephone, frais_inscription = :frais_inscription, bourse = :bourse
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':telephone' => $telephone,
                ':frais_inscription' => $frais_inscription,
                ':bourse' => $bourse,
                ':id' => $id
            ]);

            // Redirection vers la liste avec message de succès (optionnel)
            header("Location: liste_inscriptions.php?success=1");
            exit;
        } catch (PDOException $e) {
            // Gestion erreur (log ou affichage simple)
            echo "Erreur lors de la mise à jour : " . $e->getMessage();
            exit;
        }
    } else {
        echo "Champs manquants.";
    }
} else {
    // Si accès direct à ce fichier sans POST
    header("Location: liste_inscriptions.php");
    exit;
}
?>
