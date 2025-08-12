<?php
require_once '../../config/db.php';

// Vérifier que les données du formulaire sont envoyées
if (
    isset($_POST['id_inscription'], $_POST['montant_paiement'], $_POST['date_paiement'], $_POST['mode_paiement']) &&
    is_numeric($_POST['id_inscription']) && is_numeric($_POST['montant_paiement'])
) {
    $id_inscription = intval($_POST['id_inscription']);
    $montant_paye = floatval($_POST['montant_paiement']);
    $date_paiement = $_POST['date_paiement'];
    $mode_paiement = trim($_POST['mode_paiement']);
    $observation = !empty($_POST['observation']) ? trim($_POST['observation']) : null;

    // Vérifier si l'inscription existe
    $stmt = $pdo->prepare("
        SELECT i.id, i.frais_inscription, c.frais_scolarite
        FROM inscriptions i
        JOIN classes c ON i.id_classe = c.id
        WHERE i.id = ?
    ");
    $stmt->execute([$id_inscription]);
    $inscription = $stmt->fetch();

    if (!$inscription) {
        die("Inscription non trouvée.");
    }

    // Calcul du total déjà payé
    $stmt = $pdo->prepare("SELECT SUM(montant_paye) AS total_paye FROM paiements WHERE id_inscription = ?");
    $stmt->execute([$id_inscription]);
    $result = $stmt->fetch();
    $total_paye = $result ? floatval($result['total_paye']) : 0.0;

    // Calcul du total à ne pas dépasser
    $total_autorise = floatval($inscription['frais_scolarite']-$inscription['frais_inscription']-$inscription['bourse']);

    if (($total_paye + $montant_paye) > $total_autorise) {
        header("Location: index.php?Ooups=1");
        exit();
    }

    // Enregistrer le paiement
    $stmt = $pdo->prepare("
        INSERT INTO paiements (id_inscription, montant_paye, date_paiement, mode_paiement, observation)
        VALUES (?, ?, ?, ?, ?)
    ");

    $result = $stmt->execute([
        $id_inscription,
        $montant_paye,
        $date_paiement,
        $mode_paiement,
        $observation
    ]);

    if ($result) {
        // Rediriger avec succès
        header("Location: index.php?success=1");
        exit();
    } else {
        // Erreur d'enregistrement
        header("Location: index.php?error=1");
        exit();
    }

} else {
    die("Données manquantes ou invalides.");
}
