<?php
require_once '../../config/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $sql = "DELETE FROM inscriptions WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Redirection avec succès
        header("Location: liste_inscriptions.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        echo "Erreur lors de la suppression : " . $e->getMessage();
        exit;
    }
} else {
    // Redirection si aucun ID fourni
    header("Location: liste_inscriptions.php");
    exit;
}
?>
