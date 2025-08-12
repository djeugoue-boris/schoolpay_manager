<?php
require_once '../../config/db.php';
require_once '../../vendor/autoload.php'; // Dompdf

use Dompdf\Dompdf;

if (!isset($_GET['id'])) {
    die('Identifiant manquant.');
}

$idInscription = intval($_GET['id']);

// Requête élève (ajout de frais_inscription)
$sql = "
    SELECT insc.nom, insc.prenom, insc.matricule, insc.date_inscription,
           cl.nom_classe, cl.frais_scolarite,
           insc.frais_inscription,
           insc.bourse,
           sec.nom_section,
           an.libelle AS annee_scolaire
    FROM inscriptions insc
    JOIN classes cl ON insc.id_classe = cl.id
    JOIN sections sec ON cl.id_section = sec.id
    JOIN annees_scolaires an ON insc.id_annee = an.id
    WHERE insc.id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$idInscription]);
$eleve = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$eleve) {
    die('Élève non trouvé.');
}

// Paiements
$sqlPaiements = "
    SELECT montant_paye, date_paiement, mode_paiement, observation
    FROM paiements
    WHERE id_inscription = ?
    ORDER BY date_paiement ASC
";
$stmt2 = $pdo->prepare($sqlPaiements);
$stmt2->execute([$idInscription]);
$paiements = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Calculs
$totalPaye = array_sum(array_column($paiements, 'montant_paye'));

// Solde restant = (frais_scolarité + frais_inscription) - total payé
$solde = ($eleve['frais_scolarite'] + $eleve['frais_inscription']) - $totalPaye;

// Empêcher un solde négatif
if ($solde < 0) {
    $solde = 0;
}
if ($totalPaye < 0) {
    $totalPaye = 0;
}
// Empêcher un solde négatif pour les frais d'inscription
if ($eleve['frais_inscription'] < 0) {
    $eleve['frais_inscription'] = 0;
}
// Empêcher un total payé négatif
if ($totalPaye < 0) {
    $totalPaye = 0;
}
// Empêcher un solde négatif pour les frais de scolarité
if ($eleve['frais_scolarite'] < 0) {
    $eleve['frais_scolarite'] = 0;
}
// Empêcher un solde négatif pour les frais d'inscription
if ($eleve['frais_inscription'] < 0) {
    $eleve['frais_inscription'] = 0;
}
// Message pour la bourse
if ($eleve['bourse'] < 0) {
    $eleve['bourse'] = 0;
}   
// message lorsque solde restant est egale à 0

    $ges_msg_1= "Vous devez payer encore :  ";
    $ges_msg_2= " (scolarité soldée !) ";



// Nom de l'école
$nom_ecole = "COLLEGE POLYVALENT BILINGUE MARIE-THERESE";

// Logo en base64
$logoPath = realpath('../../assets/img/logo.jpg');
$logoBase64 = $logoPath ? base64_encode(file_get_contents($logoPath)) : '';

ob_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Paiement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .logo { height: 60px; }
        h1, h3 { margin: 5px 0; }
        .info-box { border: 1px solid #aaa; padding: 10px; margin-bottom: 10px; border-radius: 6px; }
        .info-box strong { display: inline-block; width: 160px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 8px; }
        th, td { border: 1px solid #aaa; padding: 5px; }
        th { background-color: #eaeaea; }
        .footer { text-align: right; margin-top: 20px; font-size: 11px; }
        .signature { margin-top: 40px; text-align: right; }
        .signature p { margin: 5px; }

        .footer2 { text-align: center; font-size: 10px; color: #666; position: fixed; bottom: 10px; width: 100%; }
    .signature { margin-top: 50px; text-align: right; font-style: italic; }
    </style>
</head>
<body>

<div class="header">
    <?php if ($logoBase64): ?>
        <img src="data:image/jpeg;base64,<?= $logoBase64 ?>" class="logo" />
    <?php endif; ?>
    <h1><?= htmlspecialchars($nom_ecole) ?></h1>
    <h3>Reçu de Paiement Scolaire</h3>
    <p><strong>Année scolaire :</strong> <?= htmlspecialchars($eleve['annee_scolaire']) ?></p>
</div>

<div class="info-box">
    <p><strong>Nom de l'élève :</strong> <?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']) ?></p>
    <p><strong>Matricule :</strong> <?= htmlspecialchars($eleve['matricule']) ?></p>
    <p><strong>Classe :</strong> <?= htmlspecialchars($eleve['nom_classe']) ?></p>
    <p><strong>Section :</strong> <?= htmlspecialchars($eleve['nom_section']) ?></p>
    <p><strong>Date d’inscription :</strong> <?= date('d/m/Y', strtotime($eleve['date_inscription'])) ?></p>
</div>

<div class="info-box">
    <p><strong>Frais de scolarité :</strong> <?= number_format($eleve['frais_scolarite'], 0, ',', ' ') ?> FCFA</p>
    <p><strong>Frais d'inscription :</strong> <?= number_format($eleve['frais_inscription'], 0, ',', ' ') ?> FCFA                Payé</p>
    <p><strong>Total déjà payé :</strong> <?= number_format($totalPaye, 0, ',', ' ') ?> FCFA                 </p>
    <p><strong>Bourse Acquis :</strong> <?= number_format($eleve['bourse'], 0, ',', ' ') ?> FCFA                 </p>
    <p><strong>Solde restant :</strong> 
    <?php $msg = number_format($solde- $eleve['frais_inscription']*2 - $eleve['bourse']); ?>
     <?php if($msg < 1){ echo "0 FCFA  ".$ges_msg_2; } else { echo $ges_msg_1.$msg.' '." FCFA"; } ?> </p>
</div>

<div class="section">
    <h4>Détail des paiements :</h4>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Mode</th>
                <th>Observation</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($paiements): ?>
            <?php foreach ($paiements as $p): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                    <td><?= number_format($p['montant_paye'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= htmlspecialchars($p['mode_paiement']) ?></td>
                    <td><?= htmlspecialchars($p['observation']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">Aucun paiement enregistré.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="footer">
    Fait le <?= date('d/m/Y') ?>
</div>

<div class="signature">
    <p><strong>Signature :</strong></p>
    <p>___________________________</p>
</div>


<div class="footer2">
    <hr>
    schoolpay v2.0 | by-evaricekuete2@gmail.com
</div>

</body>
</html>

<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("recu_paiement_{$eleve['matricule']}.pdf", ["Attachment" => false]);
exit;
