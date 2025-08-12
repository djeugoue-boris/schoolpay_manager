<?php
require_once '../../config/db.php';

$sql = "
  SELECT insc.id, insc.matricule, insc.nom, insc.prenom, insc.frais_inscription,
         sec.nom_section,
         cl.nom_classe, cl.frais_scolarite,
         an.libelle AS annee_scolaire,
         COALESCE(SUM(p.montant_paye), 0) AS total_paye
  FROM inscriptions insc
  JOIN classes cl ON insc.id_classe = cl.id
  JOIN sections sec ON cl.id_section = sec.id
  JOIN annees_scolaires an ON insc.id_annee = an.id
  LEFT JOIN paiements p ON p.id_inscription = insc.id
  WHERE insc.statut = 'inscrit'
    AND insc.id_annee = (SELECT id FROM annees_scolaires WHERE active = 1 LIMIT 1)
  GROUP BY insc.id
  ORDER BY insc.date_inscription DESC
";

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des paiements</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <script src="../../assets/js/bootstrap.bundle.min.js"></script>
  <style>
    #searchInput {
      width: 300px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body class="p-4">
  <h3 class="mb-4">Liste des paiements</h3>

  <!-- 🔍 Champ de recherche -->
  <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par nom, matricule, classe, section...">

  <table class="table table-bordered table-striped mt-3" id="paiementsTable">
    <thead class="table-dark">
      <tr>
        <th>Matricule</th>
        <th>Nom complet</th>
        <th>Section</th>
        <th>Classe</th>
        <th>Année scolaire</th>
        <th>Frais scolaire (FCFA)</th>
        <th>Payé (FCFA)</th>
        <th>Solde (FCFA)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $row): 
        $nomComplet = $row['nom'] . ' ' . $row['prenom'];
        $fraisScolaire = $row['frais_scolarite'] - $row['frais_inscription'];
        $solde = $fraisScolaire - $row['total_paye'];
      ?>
        <tr>
          <td><?= htmlspecialchars($row['matricule']) ?></td>
          <td><?= htmlspecialchars($nomComplet) ?></td>
          <td><?= htmlspecialchars($row['nom_section']) ?></td>
          <td><?= htmlspecialchars($row['nom_classe']) ?></td>
          <td><?= htmlspecialchars($row['annee_scolaire']) ?></td>
          <td><?= number_format($fraisScolaire, 0, ',', ' ') ?></td>
          <td><?= number_format($row['total_paye'], 0, ',', ' ') ?></td>
          <td><?= number_format($solde, 0, ',', ' ') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>
    // 🔍 Filtrage JS du tableau
    document.getElementById('searchInput').addEventListener('keyup', function () {
      const query = this.value.toLowerCase();
      const rows = document.querySelectorAll('#paiementsTable tbody tr');

      rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        row.style.display = rowText.includes(query) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
