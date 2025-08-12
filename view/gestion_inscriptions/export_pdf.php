<?php
// export_pdf.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- Options Dompdf
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// --- Récupération des filtres GET
$filtreClasse  = isset($_GET['classe'])  && $_GET['classe'] !== ''  ? intval($_GET['classe'])  : null;
$filtreSection = isset($_GET['section']) && $_GET['section'] !== '' ? intval($_GET['section']) : null;
$filtreAnnee   = isset($_GET['annee'])   && $_GET['annee'] !== ''   ? intval($_GET['annee'])   : null;
$filtreSexe    = isset($_GET['sexe'])    && $_GET['sexe'] !== ''    ? $_GET['sexe'] : null;
$filtreStatut  = isset($_GET['statut'])  && $_GET['statut'] !== ''  ? $_GET['statut'] : null;

// --- Construction dynamique des conditions
$conditions = ["1=1"];
$params = [];

if ($filtreClasse) {
    $conditions[] = 'cl.id = :classe';
    $params[':classe'] = $filtreClasse;
}
if ($filtreSection) {
    $conditions[] = 'sec.id = :section';
    $params[':section'] = $filtreSection;
}
if ($filtreAnnee) {
    $conditions[] = 'an.id = :annee';
    $params[':annee'] = $filtreAnnee;
} else {
    $conditions[] = 'an.active = 1';
}
if ($filtreSexe) {
    $conditions[] = 'insc.sexe = :sexe';
    $params[':sexe'] = $filtreSexe;
}
if ($filtreStatut) {
    $conditions[] = 'insc.statut = :statut';
    $params[':statut'] = $filtreStatut;
}

// --- Requête principale
$sql = "
  SELECT insc.matricule, insc.nom, insc.prenom, insc.sexe,
         sec.nom_section, cl.nom_classe,
         an.libelle AS annee_scolaire,
         DATE_FORMAT(insc.date_inscription, '%d/%m/%Y') AS date_inscription
  FROM inscriptions insc
  JOIN classes cl ON insc.id_classe = cl.id
  JOIN sections sec ON cl.id_section = sec.id
  JOIN annees_scolaires an ON insc.id_annee = an.id
  WHERE " . implode(' AND ', $conditions) . "
  ORDER BY insc.date_inscription DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Logo (base64)
$logoPath = realpath(__DIR__ . '/../../assets/img/logo.jpg');
$logoBase64 = '';
$logoMime = 'image/jpeg';
if ($logoPath && is_file($logoPath)) {
    $data = @file_get_contents($logoPath);
    if ($data !== false) {
        $logoBase64 = base64_encode($data);
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        if ($ext === 'png') $logoMime = 'image/png';
        if ($ext === 'gif') $logoMime = 'image/gif';
    }
}

// --- HTML
ob_start();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Liste des inscriptions</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 0.5cm; }
    header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #efefef; padding-bottom: 8px; }
    .brand { display: flex; align-items: center; gap: 12px; }
    .logo { max-height: 60px; }
    .title { text-align: center; flex: 1; }
    h1 { font-size: 16px; margin: 0; color: #333; }
    .meta { text-align: right; font-size: 10px; color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #bbb; padding: 6px 8px; vertical-align: middle; }
    th { background: #4e3a8c; color: #fff; font-weight: 700; font-size: 11px; }
    tbody tr:nth-child(even) { background: #fafafa; }
    td { font-size: 10.5px; text-align: center; }
    .filters { margin-top: 6px; font-size: 10px; color: #444; }
    footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #eee; padding-top: 6px; }
  </style>
</head>
<body>
  <header>
    <div class="brand">
      <?php if ($logoBase64): ?>
        <img src="data:<?= $logoMime ?>;base64,<?= $logoBase64 ?>" class="logo" alt="Logo">
      <?php endif; ?>
      <div>
        <div style="font-size:12px; font-weight:700;">COLLÈGE POLYVALENT BILINGUE MARIE-THÉRÈSE</div>
        <div style="font-size:10px; color:#666;">Liste des inscriptions</div>
      </div>
    </div>
    <div class="title"><h1>Liste des inscriptions</h1></div>
    <div class="meta">
      <div>Généré le: <?= date('d/m/Y H:i') ?></div>
      <div>Total élèves: <?= count($rows) ?></div>
    </div>
  </header>

  <div class="filters">
    <?php
      $parts = [];
      if ($filtreClasse)  { $parts[] = "Classe: " . htmlspecialchars($filtreClasse); }
      if ($filtreSection) { $parts[] = "Section: " . htmlspecialchars($filtreSection); }
      if ($filtreAnnee)   { $parts[] = "Année: " . htmlspecialchars($filtreAnnee); }
      if ($filtreSexe)    { $parts[] = "Sexe: " . htmlspecialchars($filtreSexe); }
      if ($filtreStatut)  { $parts[] = "Statut: " . htmlspecialchars($filtreStatut); }
      echo !empty($parts) ? "Filtres appliqués — " . implode(' | ', $parts) : "Aucun filtre appliqué";
    ?>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Matricule</th>
        <th>Nom complet</th>
        <th>Sexe</th>
        <th>Section</th>
        <th>Classe</th>
        <th>Année scolaire</th>
        <th>Date inscription</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; foreach ($rows as $r): ?>
      <tr>
        <td><?= $i ?></td>
        <td><?= htmlspecialchars($r['matricule']) ?></td>
        <td style="text-align:left;"><?= htmlspecialchars(trim($r['nom'] . ' ' . $r['prenom'])) ?></td>
        <td><?= htmlspecialchars($r['sexe']) ?></td>
        <td><?= htmlspecialchars($r['nom_section']) ?></td>
        <td><?= htmlspecialchars($r['nom_classe']) ?></td>
        <td><?= htmlspecialchars($r['annee_scolaire']) ?></td>
        <td><?= htmlspecialchars($r['date_inscription']) ?></td>
      </tr>
      <?php $i++; endforeach; ?>
    </tbody>
  </table>

  <footer>
    schoolpay v2.0 | by-evaricekuete2@gmail.com
  </footer>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$filename = 'liste_inscriptions_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
