<?php
// get_eleves.php
require_once '../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_GET['id_classe'])) {
    echo json_encode([]);
    exit;
}
$id_classe = intval($_GET['id_classe']);

// Optionnel : ne retourner que les élèves "inscrit" et pour l'année active
$sql = "
SELECT id, matricule, nom, prenom
FROM inscriptions
WHERE id_classe = :id_classe
  AND statut = 'inscrit'
  AND id_annee = (SELECT id FROM annees_scolaires WHERE active = 1 LIMIT 1)
ORDER BY nom, prenom
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_classe' => $id_classe]);
$eleves = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($eleves);
exit;
