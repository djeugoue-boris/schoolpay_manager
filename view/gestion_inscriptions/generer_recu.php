<?php
require_once '../../config/db.php';
require_once '../../vendor/autoload.php'; // Dompdf

use Dompdf\Dompdf;

if (!isset($_GET['id'])) {
    die('ID manquant');
}

$id = intval($_GET['id']);
$sql = "
SELECT insc.*, cl.nom_classe, sec.nom_section, an.libelle AS annee_scolaire
FROM inscriptions insc
JOIN classes cl ON insc.id_classe = cl.id
JOIN sections sec ON cl.id_section = sec.id
JOIN annees_scolaires an ON insc.id_annee = an.id
WHERE insc.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die('Inscription introuvable.');
}

// Logo base64
$logoPath = realpath('../../assets/img/logo.jpg');
$logoData = '';
if ($logoPath && file_exists($logoPath)) {
    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
    $dataImg = file_get_contents($logoPath);
    $logoData = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
}

$html = '
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; margin: 30px; font-size: 12px; color: #000; }
    .header { text-align: center; border-bottom: 2px solid #003366; padding-bottom: 10px; margin-bottom: 20px; }
    .header img { width: 80px; }
    .header h2 { margin: 5px 0; color: #003366; }
    .republique { font-size: 12px; }
    .title { text-align: center; margin: 20px 0; font-size: 16px; font-weight: bold; }
    .filigrane {
      position: fixed;
      top: 35%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-30deg);
      font-size: 60px;
      color: rgba(150, 150, 150, 0.1);
      z-index: 0;
    }
    table.details { width: 100%; border-collapse: collapse; z-index: 1; position: relative; }
    table.details td, table.details th { border: 1px solid #ccc; padding: 8px; }
    table.details th { background: #f0f0f0; text-align: left; }
    .footer { text-align: center; font-size: 10px; color: #666; position: fixed; bottom: 10px; width: 100%; }
    .signature { margin-top: 50px; text-align: right; font-style: italic; }
    .info-section { margin-top: 10px; }
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
  <img src="' . $logoData . '" alt="Logo">
  <h2>Collège Polyvalent Bilingue Marie Thérèse</h2>
</div>

<div class="title">Reçu d\'Inscription / Enrollment Receipt</div>

<table class="details">
  <tr>
    <th>Nom / Name</th>
    <td>' . htmlspecialchars($data['nom'] . ' ' . $data['prenom']) . '</td>
  </tr>
  <tr>
    <th>Matricule / Registration ID</th>
    <td>' . htmlspecialchars($data['matricule']) . '</td>
  </tr>
  <tr>
    <th>Classe / Class</th>
    <td>' . htmlspecialchars($data['nom_classe']) . '</td>
  </tr>
  <tr>
    <th>Section</th>
    <td>' . htmlspecialchars($data['nom_section']) . '</td>
  </tr>
  <tr>
    <th>Année scolaire / Academic Year</th>
    <td>' . htmlspecialchars($data['annee_scolaire']) . '</td>
  </tr>
  <tr>
    <th>Frais d\'inscription / Enrollment Fee</th>
    <td>' . number_format($data['frais_inscription'], 2, ',', ' ') . ' FCFA</td>
  </tr>
  <tr>
    <th>Bourse /  Bush</th>
    <td>' . number_format($data['bourse'], 2, ',', ' ') . ' FCFA</td>
  </tr>
  <tr>
    <th>Date d\'inscription / Date</th>
    <td>' . date('d/m/Y', strtotime($data['date_inscription'])) . '</td>
  </tr>
</table>

<div class="signature">
  Signature Administration / School Office
  <br><br><br>
  ___________________________
</div>

<div class="footer">
<hr>
  schoolpay v2.0 | by-evaricekuete2@gmail.com | Date: ' . date('d/m/Y') . '
</div>

</body>
</html>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('Recu_Inscription_' . $data['matricule'] . '.pdf', ['Attachment' => true]);
?>
