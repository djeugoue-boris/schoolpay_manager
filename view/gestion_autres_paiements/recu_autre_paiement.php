<?php
// recu_autre_paiement.php
require_once '../../config/db.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (empty($_GET['id'])) {
    die('ID manquant');
}
$id = intval($_GET['id']);

// Récupération du paiement et des infos élève/classe/section/année
$sql = "
SELECT ap.*, i.nom, i.prenom, i.matricule, i.frais_inscription, 
       COALESCE(i.bourse, 0) AS bourse,
       cl.nom_classe, sec.nom_section, an.libelle AS annee_scolaire
FROM autres_paiements ap
JOIN inscriptions i ON ap.id_inscription = i.id
JOIN classes cl ON ap.id_classe = cl.id
JOIN sections sec ON ap.id_section = sec.id
JOIN annees_scolaires an ON ap.id_annee = an.id
WHERE ap.id = :id
LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    die('Paiement introuvable');
}

// Préparer logo en base64
$logoPath = realpath('../../assets/img/logo.jpg');
$logoData = '';
if ($logoPath && file_exists($logoPath)) {
    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
    $dataImg = file_get_contents($logoPath);
    $logoData = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
}

// Construire le HTML (style proche de ton modèle d'inscription)
$eleveNom = htmlspecialchars($p['nom'] . ' ' . $p['prenom']);
$matricule = htmlspecialchars($p['matricule']);
$classe = htmlspecialchars($p['nom_classe']);
$section = htmlspecialchars($p['nom_section']);
$annee = htmlspecialchars($p['annee_scolaire']);
$objet = htmlspecialchars($p['objet']);
$montant = number_format($p['montant_paye'], 2, ',', ' ');
$datePaiement = date('d/m/Y H:i', strtotime($p['date_paiement']));
$observations = nl2br(htmlspecialchars($p['observations'] ?? ''));
$frais_inscription = number_format($p['frais_inscription'] ?? 0, 2, ',', ' ');
$bourse = number_format($p['bourse'] ?? 0, 2, ',', ' ');

$html = '
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Reçu Autre Paiement</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; margin: 30px; font-size: 12px; color: #000; }
  .header { text-align: center; border-bottom: 2px solid #003366; padding-bottom: 10px; margin-bottom: 20px; }
  .header img { width: 80px; display:block; margin: 0 auto 6px; }
  .header h2 { margin: 5px 0; color: #003366; font-size: 18px; }
  .republique { font-size: 11px; color: #333; }
  .title { text-align: center; margin: 20px 0; font-size: 16px; font-weight: bold; color:#222; }
  .filigrane {
    position: fixed;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 60px;
    color: rgba(150,150,150,0.08);
    z-index: 0;
  }
  table.details { width: 100%; border-collapse: collapse; z-index: 1; position: relative; margin-top:10px; }
  table.details td, table.details th { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
  table.details th { background: #f7f7f7; text-align: left; width: 35%; }
  .footer { text-align: center; font-size: 10px; color: #666; position: fixed; bottom: 10px; width: 100%; }
  .signature { margin-top: 40px; text-align: right; font-style: italic; }
  .info-section { margin-top: 10px; }
  .small-muted { font-size: 11px; color:#555; }
</style>
</head>
<body>
<div class="filigrane">REÇU ORIGINAL</div>

<div class="header">
  <div class="republique">
    <div style="float:left; text-align:left;">
      <p>République du Cameroun<br>Paix - Travail - Patrie</p>
    </div>
    <div style="float:right; text-align:right;">
      <p>Republic of Cameroon<br>Peace - Work - Fatherland</p>
    </div>
    <div style="clear:both;"></div>
  </div>
  ' . ($logoData ? '<img src="' . $logoData . '" alt="Logo">' : '') . '
  <h2>Collège Polyvalent Bilingue Marie Thérèse</h2>
</div>

<div class="title">Reçu de Paiement — Autre Paiement</div>

<table class="details">
  <tr>
    <th>Nom / Name</th>
    <td>' . $eleveNom . '</td>
  </tr>
  <tr>
    <th>Matricule / Registration ID</th>
    <td>' . $matricule . '</td>
  </tr>
  <tr>
    <th>Classe / Class</th>
    <td>' . $classe . ' &mdash; ' . $section . '</td>
  </tr>
  <tr>
    <th>Année scolaire / Academic Year</th>
    <td>' . $annee . '</td>
  </tr>
  <tr>
    <th>Objet / Purpose</th>
    <td>' . $objet . '</td>
  </tr>
  <tr>
    <th>Montant payé / Amount</th>
    <td>' . $montant . ' FCFA</td>
  </tr>
  <tr>
    <th>Date / Date</th>
    <td>' . $datePaiement . '</td>
  </tr>
  <tr>
    <th>Observations / Notes</th>
    <td>' . ($observations ?: '-') . '</td>
  </tr>
  <tr>
    <th>Frais inscription / Enrollment fee</th>
    <td>' . $frais_inscription . ' FCFA</td>
  </tr>
  <tr>
    <th>Bourse / Scholarship</th>
    <td>' . $bourse . ' FCFA</td>
  </tr>
</table>

<div class="signature">
  Signature Administration / School Office
  <br><br><br>
  ___________________________
</div>

<div class="footer">
  <hr>
  schoolpay v2.0 | by-evaricekuete2@gmail.com | Imprimé le ' . date('d/m/Y H:i') . '
</div>

</body>
</html>
';

// Préparer Dompdf
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Télécharger le PDF
$filename = 'recu_autre_paiement_' . preg_replace('/[^A-Za-z0-9_\-]/', '', $p['matricule']) . '_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
