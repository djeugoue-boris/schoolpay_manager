<?php
// ajouter_autre_paiement.php
require_once '../../config/db.php';
session_start(); // si tu utilises la session pour l'ID utilisateur

// Récupération sécurisée des POST
$id_classe     = isset($_POST['id_classe']) ? intval($_POST['id_classe']) : 0;
$id_inscription= isset($_POST['id_inscription']) ? intval($_POST['id_inscription']) : 0;
$objet         = isset($_POST['objet']) ? trim($_POST['objet']) : '';
$montant_raw   = isset($_POST['montant_paye']) ? trim($_POST['montant_paye']) : '0';
$montant       = floatval(str_replace(',', '.', $montant_raw)); // gérer virgule
$observations  = isset($_POST['observations']) ? trim($_POST['observations']) : '';
// Récupérer l'utilisateur effectuant l'opération si tu gères une session
$effectue_par  = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// redirection de base
$redirectBase = 'index.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirectBase}?error=method");
    exit;
}

// validations simples
if ($id_classe <= 0 || $id_inscription <= 0 || $objet === '' || $montant <= 0) {
    header("Location: {$redirectBase}?error=" . urlencode('Veuillez remplir tous les champs obligatoires et saisir un montant valide.'));
    exit;
}

try {
    // Vérifier que l'inscription existe et appartient bien à la classe sélectionnée
    $sql = "SELECT i.id, i.id_classe, i.id_annee, cl.id_section
            FROM inscriptions i
            JOIN classes cl ON i.id_classe = cl.id
            WHERE i.id = :id_inscription
              AND i.id_classe = :id_classe
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_inscription' => $id_inscription, ':id_classe' => $id_classe]);
    $ins = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ins) {
        header("Location: {$redirectBase}?error=" . urlencode("L'élève sélectionné n'appartient pas à la classe choisie."));
        exit;
    }

    $id_annee   = (int)$ins['id_annee'];
    // id_section provient de la table classes
    $id_section = (int)$ins['id_classe']; // fallback
    $stmt2 = $pdo->prepare("SELECT id_section FROM classes WHERE id = :id_classe LIMIT 1");
    $stmt2->execute([':id_classe' => $id_classe]);
    $r = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($r && isset($r['id_section'])) {
        $id_section = (int)$r['id_section'];
    }

    // Insert dans autres_paiements
    $insertSql = "INSERT INTO autres_paiements 
        (id_annee, id_section, id_classe, id_inscription, objet, montant_paye, observations, effectue_par, date_paiement)
        VALUES
        (:id_annee, :id_section, :id_classe, :id_inscription, :objet, :montant_paye, :observations, :effectue_par, NOW())";

    $insStmt = $pdo->prepare($insertSql);
    $insStmt->execute([
        ':id_annee'      => $id_annee,
        ':id_section'    => $id_section,
        ':id_classe'     => $id_classe,
        ':id_inscription'=> $id_inscription,
        ':objet'         => $objet,
        ':montant_paye'  => $montant,
        ':observations'  => $observations,
        ':effectue_par'  => $effectue_par
    ]);

    header("Location: {$redirectBase}?success=1");
    exit;

} catch (PDOException $e) {
    // Pour debug local tu peux logger $e->getMessage(); en prod évite d'exposer le message SQL
    $err = 'Erreur base de données';
    // si tu veux l'erreur détaillée (uniquement en dev) : $err = $e->getMessage();
    header("Location: {$redirectBase}?error=" . urlencode($err));
    exit;
}
