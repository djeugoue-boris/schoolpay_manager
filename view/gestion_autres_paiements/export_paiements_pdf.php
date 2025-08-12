<?php
require_once '../../config/db.php';
require_once '../../vendor/autoload.php'; // Dompdf

use Dompdf\Dompdf;

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(insc.nom LIKE :search OR insc.prenom LIKE :search OR insc.matricule LIKE :search OR p.objet LIKE :search)";
    $params['search'] = "%".$_GET['search']."%";
}
if (!empty($_GET['date_debut'])) {
    $where[] = "DATE(p.date_paiement) >= :date_debut";
    $params['date_debut'] = $_GET['date_debut'];
}
if (!empty($_GET['date_fin'])) {
    $where[] = "DATE(p.date_paiement) <= :date_fin";
    $params['date_fin'] = $_GET['date_fin'];
}

$sql = "
SELECT p.*, insc.nom, insc.prenom, insc.matricule, cl.nom_classe, sec.nom_section, an.libelle AS annee_scolaire
FROM autres_paiements p
JOIN inscriptions insc ON p.id_inscription = insc.id
JOIN classes cl ON insc.id_classe = cl.id
JOIN sections sec ON cl.id_section = sec.id
JOIN annees_scolaires an ON insc.id_annee = an.id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.date_paiement DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$paiements = $stmt->fetchAll();

$html = '<h2 style="text-align:center">Liste des Paiements</h2>
<table border="1" cellpadding="5" cellspacing="0" width="100%">
<thead>
<tr>
<th>#</th>
<th>Élève</th>
<th>Classe</th>
<th>Objet</th>
<th>Montant</th>
<th>Date</th>
</tr>
</thead><tbody>';

foreach ($paiements as $i => $p) {
    $html .= '<tr>
    <td>'.($i+1).'</td>
    <td>'.htmlspecialchars($p['nom'].' '.$p['prenom']).'</td>
    <td>'.htmlspecialchars($p['nom_classe']).' - '.htmlspecialchars($p['nom_section']).'</td>
    <td>'.htmlspecialchars($p['objet']).'</td>
    <td>'.number_format($p['montant_paye'], 2, ',', ' ').' FCFA</td>
    <td>'.date('d/m/Y H:i', strtotime($p['date_paiement'])).'</td>
    </tr>';
}
$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("paiements.pdf", ["Attachment" => false]);
