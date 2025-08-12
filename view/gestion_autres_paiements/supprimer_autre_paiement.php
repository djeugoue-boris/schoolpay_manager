<?php
// Connexion à la base
require_once '../../config/db.php';

// Vérifier si un ID a été envoyé
if (!isset($_GET['id'])) {
    die("ID invalide.");
}

$id = (int) $_GET['id'];

// Supprimer le paiement
$sql = "DELETE FROM autres_paiements WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

// Rediriger vers la liste avec un message
header("Location: index.php?message=Paiement supprimé avec succès");
exit;
