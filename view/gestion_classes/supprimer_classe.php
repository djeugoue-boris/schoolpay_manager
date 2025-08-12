<?php
require_once '../../config/db.php'; // Connexion PDO via $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        // Vérifie si la classe existe
        $stmt = $pdo->prepare("SELECT id FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        $classe = $stmt->fetch();

        if ($classe) {
            // Supprimer la classe
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
            if ($stmt->execute([$id])) {
                header('Location: index.php?success=3'); // suppression réussie
                exit();
            } else {
                header('Location: index.php?error=3'); // erreur suppression
                exit();
            }
        } else {
            // Classe non trouvée
            header('Location: index.php?error=3');
            exit();
        }
    } catch (PDOException $e) {
        // Pour debug : error_log($e->getMessage());
        header('Location: index.php?error=3');
        exit();
    }
} else {
    // Requête invalide ou ID manquant
    header('Location: index.php?error=3');
    exit();
}
?>
