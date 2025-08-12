<?php
session_start();

// verifier la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}
?>

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


// Récupérer les années scolaires
$sqlAnnees = "SELECT id, libelle FROM annees_scolaires ORDER BY libelle DESC";
$stmtAnnees = $pdo->query($sqlAnnees);
$annees = $stmtAnnees->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tableau de bord Administrateur</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
   <script src="../../assets/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../../assets/css/dashboard-admin.css" />
  <link rel="stylesheet" href="../../assets/css/dashboard.css" />
  
  <style>
    .stats-card {
      border-radius: 12px;
      padding: 1.5rem;
      color: #333;
      box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgb(0 0 0 / 0.15);
    }
    .stats-icon {
      font-size: 3rem;
      margin-bottom: 0.5rem;
      color: #6f42c1;
    }
    .stats-title {
      font-weight: 700;
      font-size: 1.2rem;
      margin-bottom: 0.3rem;
      color: #4e3a8c;
    }
    .stats-number {
      font-weight: 900;
      font-size: 3.5rem;
      color: #5a3ea5;
      line-height: 1;
    }
    .stats-desc {
      color: #7a6ebd;
      font-size: 0.9rem;
    }

    /* Background color variations */
    .bg-sections {
      background: #ede7f6;
    }
    .bg-tranches {
      background: #e0f7fa;
    }
    .bg-cycles {
      background: #f3e5f5;
    }
</style>
</head>
<body>
<nav class="sidebar" role="navigation" aria-label="Menu principal">
    <div class="logo-container text-center mb-4">
      <img src="../../assets/img/logo.jpg" alt="Logo" class="logo-img" />
      <h5 class="mt-2" style="color: white;"><b>SchoolPay</b></h5>
    </div>

    <div class="sidebar-section">Tableau de bord</div>
    <a href="../../dashboard_administrateur.php" class="active">
      <i class="bi bi-bar-chart-fill"></i> <span>Accueil</span>
    </a>

    <div class="sidebar-section">Gestion Scolaire</div>
  <div class="dropdown">
    <a class="dropdown-toggle d-block" href="#" id="dropdownScolaire" data-bs-toggle="collapse" data-bs-target="#menuScolaire" aria-expanded="false" aria-controls="menuScolaire">
      <i class="bi bi-mortarboard-fill"></i> Derouler Menu <i class="bi bi-caret-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="menuScolaire">
      <a href="../gestion_classes/index.php" class="d-block mt-2"><i class="bi bi-building"></i> Mes Classes</a>
      <a href="../gestion_inscriptions/index.php" class="d-block mt-2"><i class="bi bi-person-plus"></i> Inscriptions</a>
    </div>
  </div>

    <div class="sidebar-section">Paiements</div>
      <a href="" class="d-block mt-2"><i class="bi bi-cash-stack"></i> Enregistrer un paiement</a>
      <a href="../gestion_autres_paiements/index.php"><i class="bi bi-credit-card-2-front"></i>Autres Paiements </a>

  <div class="sidebar-section">Paramètres </div>
  <div class="dropdown">
    <a class="dropdown-toggle d-block" href="#" id="dropdownParam" data-bs-toggle="collapse" data-bs-target="#menuParam" aria-expanded="false" aria-controls="menuParam">
      <i class="bi bi-gear-fill"></i> Derouler Menu <i class="bi bi-caret-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="menuParam">
      <a href="../gestion_annees/" class="d-block mt-2"><i class="bi bi-calendar"></i> Config Années Scolaires</a>
      <a href="../gestion_section/" class="d-block mt-2"><i class="bi bi-ui-checks"></i> Config Sections</a>
      <a href="../gestion_cycles/" class="d-block mt-2"><i class="bi bi-diagram-3"></i> Config Cycles</a>
      <a href="../gestion_archives/" class="d-block mt-2"><i class="bi bi-archive"></i> Archives</a>
    </div>
  </div>

  <div class="sidebar-section"> Utilisateurs</div>
  <a href="../gestion_utilisateurs/index.php"><i class="bi bi-person-fill"></i> Utilisateurs</a>
    <a href="admin_impressions.php"><i class="bi bi-printer-fill"></i> Impressions</a>

  <div class="sidebar-section">Mon compte</div>
  <div class="dropdown">
    <a class="dropdown-toggle d-block" href="#" id="dropdownCompte" data-bs-toggle="collapse" data-bs-target="#menuCompte" aria-expanded="false" aria-controls="menuCompte">
      <i class="bi bi-person-circle"></i> Mon compte <i class="bi bi-caret-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="menuCompte">
      <a href="" class="d-block mt-2"><i class="bi bi-person"></i> Profil</a>
      <a href="../../auth/logout.php" class="d-block mt-2"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
    </div>
  </div>
</nav>


<!-- content -->
<!-- contenue entete -->
<header class="topbar" role="banner">
  <div class="welcome me-auto ps-3">
    👋 Bonjour, <strong>Administrateur de <em><b>SchoolPay</b></em></strong>
  </div>
  <div class="theme-toggle pe-3">
    <button class="btn btn-link p-0" onclick="setTheme('light')" aria-label="Thème clair">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sun" viewBox="0 0 16 16">
        <path d="M8 4.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7z"/>
        <path d="M8 0a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1a.5.5 0 0 1 .5-.5zm6-5a.5.5 0 0 1 .5.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5zm-13 0a.5.5 0 0 1 .5.5H0a.5.5 0 0 1 0-1h1a.5.5 0 0 1 .5-.5zm10.657-4.657a.5.5 0 0 1 .707 0l.707.707a.5.5 0 0 1-.707.707l-.707-.707a.5.5 0 0 1 0-.707zm-9.193 9.193a.5.5 0 0 1 .707 0l.707.707a.5.5 0 0 1-.707.707l-.707-.707a.5.5 0 0 1 0-.707zm9.193 0a.5.5 0 0 1 0 .707l-.707.707a.5.5 0 0 1-.707-.707l.707-.707a.5.5 0 0 1 .707 0zm-9.193-9.193a.5.5 0 0 1 0 .707L3.354 3.354a.5.5 0 1 1-.707-.707l.707-.707a.5.5 0 0 1 .707 0z"/>
      </svg>
    </button>
    <button class="btn btn-link p-0" onclick="setTheme('dark')" aria-label="Thème sombre">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-moon" viewBox="0 0 16 16">
        <path d="M6 0a7 7 0 0 0 0 14 7 7 0 0 0 0-14z"/>
      </svg>
    </button>
    <button class="btn btn-link p-0" onclick="setTheme('violet')" aria-label="Thème violet">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
        <path d="M8 2.748l-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 3.905C2.4 8.68 4.5 10.5 8 13.5c3.5-3 5.6-4.82 6.286-6.542.955-1.405.837-2.882.314-3.905C13.486.878 10.4.28 8.717 2.01L8 2.748z"/>
      </svg>
    </button>
  </div>
</header>
<!-- end content header -->
<main class="main-content" role="main" tabindex="-1" aria-live="polite">
  <br>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>FICHE D'ENREGISTREMENT D'UN PAIEMENT DE L'ÉLÈVE</h1>
    <a href="../gestion_inscriptions/liste_inscriptions.php" class="btn btn-secondary">
      <i class="bi bi-list"></i> Liste des Inscrits
    </a>
  </div>

  <!-- alerte telechargement (montant= Ooups=1) -->
 <?php if (isset($_GET['Ooups']) && $_GET['Ooups'] == 1): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ❌ Une erreur est survenue : <strong class="fw-bold text-danger">Le Montant à Payer ne doit pas depasser Les Frais de Scolarité</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

  <div class="alert alert-info" role="alert">
    <i class="bi bi-info-circle-fill"></i>     <strong>Note :</strong> Vous pouvez rechercher un élève par son nom, matricule, classe ou section ou par les indices des boutons d'action .
          <input type="text" id="searchInput" class="form-control fw-bold" placeholder="Rechercher par nom, matricule, classe, section...">

  </div>
  <!-- gestion des alertes -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i class="bi bi-check-circle-fill"></i> Paiement enregistré avec succès !
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 1): ?>
      <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> Une erreur s'est produite lors de l'enregistrement du paiement.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
    <?php endif; ?>

<!-- end gestion des  -->
  <div class="table-responsive mt-4">
    <div class="mb-3">
       <!-- 🔍 Champ de recherche -->
<br>
      <!-- recherche dynamique pour telechargements-->
      <form class="row g-2 mb-3" method="GET" action="export_paiements.php" target="_blank">
      <div class="col-md-3">
        <select name="classe" class="form-select">
          <option value="">-- Toutes les classes --</option>
          <?php
          $res = $pdo->query("SELECT id, nom_classe FROM classes ORDER BY nom_classe");
          while ($c = $res->fetch()) {
            echo "<option value='{$c['id']}'>{$c['nom_classe']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="col-md-3">
        <select name="section" class="form-select">
          <option value="">-- Toutes les sections --</option>
          <?php
          $res = $pdo->query("SELECT id, nom_section FROM sections ORDER BY nom_section");
          while ($s = $res->fetch()) {
            echo "<option value='{$s['id']}'>{$s['nom_section']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="col-md-3">
        <select name="annee" class="form-select">
          <option value="">-- Année scolaire --</option>
          <?php
          $res = $pdo->query("SELECT id, libelle FROM annees_scolaires ORDER BY id DESC");
          while ($a = $res->fetch()) {
            echo "<option value='{$a['id']}'>{$a['libelle']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="col-md-3 d-grid">
        <button type="submit" class="btn btn-danger">
          <i class="bi bi-filetype-pdf"></i> Exporter en PDF
        </button>
      </div>
    </form>

</div>

    <table class="table table-hover table-bordered align-middle" id="paiementsTable">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Nom Complet</th>
          <th>Section</th>
          <th>Classe</th>
          <th>Année scolaire</th>
          <th>Frais scolaire</th>
          <th>Frais inscription</th>
          <th>Bourse</th>
          <th>Paiement déjà effectué</th>
          <th>Solde restant</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        require_once '../../config/db.php';

        $sql = "
          SELECT insc.id,
                insc.nom, insc.prenom,
                insc.frais_inscription,
                insc.bourse,
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
        $index = 1;

        if ($stmt->rowCount() > 0) {
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fraisScolarite = (float) $row['frais_scolarite'];
            $fraisInscription = (float) $row['frais_inscription'];
            $bourse = (float) $row['bourse'];
            $fraisScolaireNet = $fraisScolarite - $fraisInscription - $bourse;
            $totalPaye = (float) $row['total_paye'];
            $solde = $fraisScolaireNet - $totalPaye;

            $nomComplet = htmlspecialchars($row['nom'] . ' ' . $row['prenom']);
            $nomClasse = htmlspecialchars($row['nom_classe']);
            $nomSection = htmlspecialchars($row['nom_section']);
            $annee = htmlspecialchars($row['annee_scolaire']);
            $frais = number_format($fraisScolaireNet, 2, ',', ' ') . " FCFA";
            $inscription = number_format($fraisInscription, 2, ',', ' ') . " FCFA";
            $bourse = number_format($bourse, 2, ',', ' ') . " FCFA";
            $paye = number_format($totalPaye, 2, ',', ' ') . " FCFA";
            $reste = number_format(max(0, $solde), 2, ',', ' ') . " FCFA";

            // Bouton paiement : désactivé si solde <= 0
            $paiementBtn = ($solde > 0) ? "
              <button type='button' class='btn btn-success btn-sm'
                onclick='openPaymentModal(
                  {$row['id']},
                  \"" . addslashes($nomComplet) . "\",
                  \"" . addslashes($nomClasse) . "\",
                  {$fraisScolaireNet},
                  {$totalPaye},
                  {$solde}
                )'>
                <i class='bi bi-plus-circle'></i> Paiement
              </button>
            " : "
              <button type='button' class='btn btn-secondary btn-sm' disabled>
                <i class='bi bi-check-circle'></i> Payé
              </button>
            ";

            echo "<tr>
              <td>{$index}</td>
              <td>{$nomComplet}</td>
              <td>{$nomSection}</td>
              <td>{$nomClasse}</td>
              <td>{$annee}</td>
              <td>{$frais}</td>
              <td>{$inscription}</td>
              <td>{$bourse}</td>
              <td>{$paye}</td>
              <td><strong>{$reste}</strong></td>
              <td>
                {$paiementBtn}
                <a href='recu_paiement.php?id={$row['id']}' class='btn btn-primary btn-sm'>
                  <i class='bi bi-file-earmark-pdf'></i> Reçu
                </a>
                <button onclick='deleteInscription({$row['id']})' class='btn btn-danger btn-sm'>
                  <i class='bi bi-trash'></i>
                </button>
              </td>
            </tr>";

            $index++;
          }
        } else {
          echo "<tr><td colspan='10' class='text-center text-muted'>Aucun élève inscrit pour l’année active.</td></tr>";
        }
        ?>
      </tbody>
    </table>

  </div>
  <!-- Le modal de paiement  -->
<div class="modal fade" id="modalPaiement" tabindex="-1" aria-labelledby="modalPaiementLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalPaiementLabel">
          <i class="bi bi-cash-coin"></i> Enregistrer un Nouveau paiement
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form id="formPaiement" method="POST" action="enregistrer_paiement.php">
        <div class="modal-body">
          <input type="hidden" id="id_inscription" name="id_inscription">

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="nom_eleve" class="form-label text-dark">Nom de l'élève</label>
              <input type="text" class="form-control" id="nom_eleve" readonly>
            </div>
            <div class="col-md-6">
              <label for="classe_eleve" class="form-label text-dark">Classe</label>
              <input type="text" class="form-control" id="classe_eleve" readonly>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="frais_scolarite" class="form-label text-dark">Frais scolaire total</label>
              <input type="text" class="form-control" id="frais_scolarite" readonly>
            </div>
            <div class="col-md-6">
              <label for="total_paye" class="form-label text-dark">Total déjà payé</label>
              <input type="text" class="form-control" id="total_paye" readonly>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="solde_restant" class="form-label text-dark">Solde restant</label>
              <input type="text" class="form-control" id="solde_restant" readonly>
            </div>
            <div class="col-md-6">
              <label for="montant_paiement" class="form-label text-dark">Montant à payer *</label>
              <input type="number" class="form-control" id="montant_paiement" name="montant_paiement" 
                     min="1" step="0.01" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="date_paiement" class="form-label text-dark">Date du paiement *</label>
            <input type="date" class="form-control" id="date_paiement" name="date_paiement" 
                   value="<?php echo date('Y-m-d'); ?>" required>
          </div>

          <div class="mb-3">
            <label for="mode_paiement" class="form-label text-dark">Mode de paiement *</label>
            <select class="form-select" id="mode_paiement" name="mode_paiement" required>
              <option value="">-- Sélectionner --</option>
              <option value="espèces">Espèces</option>
              <option value="chèque">Chèque</option>
              <option value="virement bancaire">Virement bancaire</option>
              <option value="mobile money">Mobile Money</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="observation" class="form-label text-dark">Observation / Note</label>
            <textarea class="form-control" id="observation" name="observation" rows="2" 
                      placeholder="Entrez une note ou observation (optionnel)"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Annuler
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Enregistrer le paiement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Fin du modal de paiement -->

</main>





<!-- Script pour actions -->
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
function openPaymentModal(id, nomComplet, classe, fraisScolarite, totalPaye, soldeRestant) {
  document.getElementById('id_inscription').value = id;
  document.getElementById('nom_eleve').value = nomComplet;
  document.getElementById('classe_eleve').value = classe;
  document.getElementById('frais_scolarite').value = fraisScolarite + ' FCFA';
  document.getElementById('total_paye').value = totalPaye + ' FCFA';
  document.getElementById('solde_restant').value = soldeRestant + ' FCFA';
  document.getElementById('montant_paiement').value = '';
  document.getElementById('mode_paiement').value = '';
  document.getElementById('observation').value = '';
  document.getElementById('date_paiement').value = new Date().toISOString().slice(0, 10);
  var modal = new bootstrap.Modal(document.getElementById('modalPaiement'));
  modal.show();
}

</script>

<!-- systeme de recherche js -->
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
