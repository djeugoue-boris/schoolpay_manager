<?php
// supprimer_section.php - Script pour supprimer une section
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? '';

    if (empty($id)) {
        header(LOCATION : 'Location: index.php?error=ID requis');
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: index.php?success=Section supprimée avec succès');
    } catch (PDOException $e) {
        header('Location: index.php?error=Erreur lors de la suppression de la section: ' . $e->getMessage());
        exit;
    }
} else {
    header('Location: index.php?error=Méthode non autorisée');
    exit;
}
?>
