<?php
// export_paiements.php
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

// --- Construction dynamique des conditions
$conditions = ["insc.statut = 'inscrit'"];
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

// --- Requête principale (inclut bourse et frais_inscription)
$sql = "
  SELECT insc.id, insc.matricule, insc.nom, insc.prenom,
         COALESCE(insc.frais_inscription,0) AS frais_inscription,
         COALESCE(insc.bourse,0) AS bourse,
         sec.nom_section, cl.nom_classe, cl.frais_scolarite,
         an.libelle AS annee_scolaire,
         COALESCE(SUM(p.montant_paye), 0) AS total_paye
  FROM inscriptions insc
  JOIN classes cl ON insc.id_classe = cl.id
  JOIN sections sec ON cl.id_section = sec.id
  JOIN annees_scolaires an ON insc.id_annee = an.id
  LEFT JOIN paiements p ON p.id_inscription = insc.id
  WHERE " . implode(' AND ', $conditions) . "
  GROUP BY insc.id
  ORDER BY insc.date_inscription DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Logo (base64) pour s'assurer qu'il s'affiche dans le PDF
$logoPath = realpath(__DIR__ . '/../../assets/img/logo.jpg'); // adapte si besoin
$logoBase64 = '';
$logoMime = 'image/jpeg';
if ($logoPath && is_file($logoPath)) {
    $data = @file_get_contents($logoPath);
    if ($data !== false) {
        $logoBase64 = base64_encode($data);
        // détection mime simple via extension
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        if ($ext === 'png') $logoMime = 'image/png';
        if ($ext === 'gif') $logoMime = 'image/gif';
    }
}

// --- Générer le HTML
ob_start();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>État des paiements</title>
  <style>
    /* Reset simple */
    * { box-sizing: border-box; }
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

    .money { white-space: nowrap; }
    .solde-zero { color: #2e7d32; font-weight: 700; }    /* vert */
    .solde-positif { color: #c62828; font-weight: 700; }/* rouge */

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
        <div style="font-size:10px; color:#666;">État des paiements</div>
      </div>
    </div>

    <div class="title">
      <h1>État des paiements des élèves</h1>
    </div>

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
      echo !empty($parts) ? "Filtres appliqués — " . implode(' | ', $parts) : "Aucun filtre appliqué (année active par défaut)";
    ?>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Matricule</th>
        <th>Nom complet</th>
        <th>Section</th>
        <th>Classe</th>
        <th>Année scolaire</th>
        <th>Frais scolarité (brut)</th>
        <th>Frais inscription</th>
        <th>Bourse</th>
        <th>Frais net à payer</th>
        <th>Payé</th>
        <th>Solde</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $i = 1;
        foreach ($rows as $r):
          $frais_scolarite = (float) $r['frais_scolarite'];
          $frais_inscription = (float) $r['frais_inscription'];
          $bourse = (float) $r['bourse'];
          $total_paye = (float) $r['total_paye'];

          // Frais net = frais_scolarite - frais_inscription - bourse
          $frais_net = $frais_scolarite - $frais_inscription - $bourse;
          if ($frais_net < 0) $frais_net = 0;

          // Solde restant = frais_net - total_paye (empêcher négatif)
          $solde = $frais_net - $total_paye;
          if ($solde < 0) $solde = 0;

          $soldeClass = ($solde == 0.0) ? 'solde-zero' : 'solde-positif';
      ?>
      <tr>
        <td><?= $i ?></td>
        <td><?= htmlspecialchars($r['matricule'] ?? '') ?></td>
        <td style="text-align:left; padding-left:8px;"><?= htmlspecialchars(trim($r['nom'] . ' ' . $r['prenom'])) ?></td>
        <td><?= htmlspecialchars($r['nom_section']) ?></td>
        <td><?= htmlspecialchars($r['nom_classe']) ?></td>
        <td><?= htmlspecialchars($r['annee_scolaire']) ?></td>
        <td class="money"><?= number_format($frais_scolarite, 0, ',', ' ') ?> FCFA</td>
        <td class="money"><?= number_format($frais_inscription, 0, ',', ' ') ?> FCFA</td>
        <td class="money"><?= number_format($bourse, 0, ',', ' ') ?> FCFA</td>
        <td class="money"><?= number_format($frais_net, 0, ',', ' ') ?> FCFA</td>
        <td class="money"><?= number_format($total_paye, 0, ',', ' ') ?> FCFA</td>
        <td class="money <?= $soldeClass ?>"><?= number_format($solde, 0, ',', ' ') ?> FCFA</td>
      </tr>
      <?php
        $i++;
        endforeach;
      ?>
    </tbody>
  </table>

  <footer>
    schoolpay v2.0 | by-evaricekuete2@gmail.com
  </footer>
</body>
</html>
<?php
// --- Récupère le HTML du buffer et rend le PDF
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Téléchargement (Attachment => true pour forcer download)
$filename = 'etat_paiements_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
