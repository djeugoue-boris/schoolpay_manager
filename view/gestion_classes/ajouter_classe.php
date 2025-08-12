<?php
require_once '../../config/db.php'; // Connexion DB
session_start(); // Pour messages de session si nécessaire

// Vérifie si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage et validation des données
    $nom_classe = trim($_POST['nom_classe'] ?? '');
    $frais_scolarite = floatval($_POST['frais_scolarite'] ?? 0);
    $nombre_tranches = intval($_POST['nombre_tranches'] ?? 0);
    $id_cycle = intval($_POST['id_cycle'] ?? 0);
    $id_section = intval($_POST['id_section'] ?? 0);
    $id_annee = intval($_POST['id_annee'] ?? 0);

    // Validation basique
    if (
        empty($nom_classe) ||
        $frais_scolarite <= 0 ||
        $nombre_tranches <= 0 ||
        $id_cycle <= 0 ||
        $id_section <= 0 ||
        $id_annee <= 0
    ) {
        // Redirection avec message d'erreur
        $_SESSION['error'] = "Tous les champs sont obligatoires et doivent être valides.";
        header('Location: index.php');
        exit;
    }

    try {
        // Vérifie si la classe existe déjà pour la même année, cycle et section
        $checkQuery = "SELECT COUNT(*) FROM classes 
                       WHERE nom_classe = :nom_classe AND id_cycle = :id_cycle 
                       AND id_section = :id_section AND id_annee = :id_annee";
        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute([
            ':nom_classe' => $nom_classe,
            ':id_cycle' => $id_cycle,
            ':id_section' => $id_section,
            ':id_annee' => $id_annee
        ]);

        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Cette classe existe déjà pour l'année scolaire sélectionnée.";
            header('Location: index.php');
            exit;
        }

        // Insertion de la classe
        $insertQuery = "
            INSERT INTO classes (nom_classe, frais_scolarite, nombre_tranches, id_cycle, id_section, id_annee)
            VALUES (:nom_classe, :frais_scolarite, :nombre_tranches, :id_cycle, :id_section, :id_annee)
        ";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([
            ':nom_classe' => $nom_classe,
            ':frais_scolarite' => $frais_scolarite,
            ':nombre_tranches' => $nombre_tranches,
            ':id_cycle' => $id_cycle,
            ':id_section' => $id_section,
            ':id_annee' => $id_annee
        ]);

        $_SESSION['success'] = "Classe ajoutée avec succès.";
        header('Location: index.php?success=2');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'ajout : " . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    // Accès non autorisé
    header('Location: index.php');
    exit;
}
