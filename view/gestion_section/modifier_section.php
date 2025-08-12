<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $nom_section = trim($_POST['nom_section']);

    if (!empty($id) && !empty($nom_section)) {
        $stmt = $pdo->prepare("UPDATE sections SET nom_section = ? WHERE id = ?");
        $stmt->execute([$nom_section, $id]);

        header("Location: index.php?success=1");
        exit;
    } else {
        header("Location: index.php?error=1");
        exit;
    }
} else {
    die("Méthode non autorisée");
}
