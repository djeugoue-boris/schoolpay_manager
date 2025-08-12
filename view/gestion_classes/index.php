<?php
session_start();

// verifier la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/db.php'; // Connexion à la base de données

// Récupération des données pour l'affichage
$query = "
SELECT cl.*, cy.nom_cycle, sec.nom_section, an.libelle AS annee_scolaire
FROM classes cl
JOIN cycles cy ON cl.id_cycle = cy.id
JOIN sections sec ON cl.id_section = sec.id
JOIN annees_scolaires an ON cl.id_annee = an.id
ORDER BY cl.nom_classe ASC
";
$stmt = $pdo->query($query);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Données pour les dropdowns (cycles, sections, années)
$cycles = $pdo->query("SELECT * FROM cycles ORDER BY nom_cycle")->fetchAll(PDO::FETCH_ASSOC);
$sections = $pdo->query("SELECT * FROM sections ORDER BY nom_section")->fetchAll(PDO::FETCH_ASSOC);
$annees = $pdo->query("SELECT * FROM annees_scolaires ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?> 


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tableau de bord Administrateur</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">

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
    <a href="" class="d-block mt-2"><i class="bi bi-building"></i> Mes Classes</a>
    <a href="../gestion_inscriptions/index.php" class="d-block mt-2"><i class="bi bi-person-plus"></i> Inscriptions</a>
  </div>
</div>

  <div class="sidebar-section">Paiements</div>
    <a href="../gestion_paiements/index.php" class="d-block mt-2"><i class="bi bi-cash-stack"></i> Enregistrer un paiement</a>
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

<!-- start contenue -->
<main class="main-content" role="main" tabindex="-1" aria-live="polite">
  <br><br>

<?php
  // Exemple : gestion d'alertes de succès/erreur
  $message = '';
  $alertType = '';

  if (isset($_GET['success'])) {
      switch ($_GET['success']) {
          case '1':
              $message = "Classe modifiée avec succès !";
              $alertType = 'success';
              break;
          case '2':
              $message = "Classe ajoutée avec succès.";
              $alertType = 'success';
              break;
          case '3':
              $message = "Classe supprimée avec succès.";
              $alertType = 'success';
              break;
      }
  } elseif (isset($_GET['error'])) {
      switch ($_GET['error']) {
          case '1':
              $message = "Erreur : Impossible de modifier la classe.";
              $alertType = 'danger';
              break;
          case '2':
              $message = "Erreur : Impossible d'ajouter la classe.";
              $alertType = 'danger';
              break;
          case '3':
              $message = "Erreur : Impossible de supprimer la classe.";
              $alertType = 'danger';
              break;
      }
  }
  $y = 1;
?>

  <?php if ($message): ?>
    <div class="alert alert-<?php echo htmlspecialchars($alertType); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
  <?php endif; ?>

  <section class="content">
    <h1 style="color: var(--text);">Liste des Classes déjà créées</h1>
    <p>Gérez les Classes de votre établissement scolaire</p>
  </section><br>
  <!-- listes des Classes deja crée -->
    <div class="container mt-4">
    <!-- Bouton ajouter Classes -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle"></i> Ajouter un Classes
        </button>
    </div>
   
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
        <thead class="table-light">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom Classe</th>
          <th>Frais Scolarité</th>
          <th>Tranches</th>
          <th>Cycle</th>
          <th>Section</th>
          <th>Année</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($classes as $classe): ?>
        <tr>
          <td><?= $y?></td>
          <td><?= htmlspecialchars($classe['nom_classe']) ?></td>
          <td><?= number_format($classe['frais_scolarite'], 2) ?> FCFA</td>
          <td><?= $classe['nombre_tranches'] ?></td>
          <td><?= htmlspecialchars($classe['nom_cycle']) ?></td>
          <td><?= htmlspecialchars($classe['nom_section']) ?></td>
          <td><?= htmlspecialchars($classe['annee_scolaire']) ?></td>
          <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $classe['id'] ?>">Modifier</button>
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $classe['id'] ?>">
              Supprimer
            </button>

          </td>
        </tr>
        <?php $y++; endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php foreach ($classes as $classe): ?>

<!-- Modal Modification -->
<div class="modal fade" id="editModal<?= $classe['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form action="modifier_classe.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifier Classe</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" value="<?= $classe['id'] ?>">
        <div class="mb-2">
          <label class="text-dark">Nom Classe</label>
          <input type="text" name="nom_classe" class="form-control" value="<?= htmlspecialchars($classe['nom_classe']) ?>" required>
        </div>
        <div class="mb-2">
          <label class="text-dark">Frais Scolarité</label>
          <input type="number" name="frais_scolarite" class="form-control" value="<?= $classe['frais_scolarite'] ?>" required>
        </div>
        <div class="mb-2">
          <label class="text-dark">Nombre Tranches</label>
          <input type="number" name="nombre_tranches" class="form-control" value="<?= $classe['nombre_tranches'] ?>" required>
        </div>
        <div class="mb-2">
          <label class="text-dark">Cycle</label>
          <select name="id_cycle" class="form-select" required>
            <?php foreach ($cycles as $cycle): ?>
              <option value="<?= $cycle['id'] ?>" <?= $cycle['id'] == $classe['id_cycle'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cycle['nom_cycle']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="text-dark">Section</label>
          <select name="id_section" class="form-select" required>
            <?php foreach ($sections as $section): ?>
              <option value="<?= $section['id'] ?>" <?= $section['id'] == $classe['id_section'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($section['nom_section']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="text-dark">Année</label>
          <select name="id_annee" class="form-select" required>
            <?php foreach ($annees as $annee): ?>
              <option value="<?= $annee['id'] ?>" <?= $annee['id'] == $classe['id_annee'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($annee['libelle']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-warning" type="submit">Enregistrer</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      </div>
    </form>
  </div>
</div>


<?php endforeach; ?>


  <!-- Modal Ajout -->
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
      <form action="ajouter_classe.php" method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ajouter Classe</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="text-dark">Nom Classe</label>
            <input type="text" name="nom_classe" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="text-dark">Frais Scolarité</label>
            <input type="number" name="frais_scolarite" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="text-dark">Nombre Tranches</label>
            <input type="number" name="nombre_tranches" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="text-dark">Cycle</label>
            <select name="id_cycle" class="form-select" required>
              <?php foreach ($cycles as $cycle): ?>
              <option value="<?= $cycle['id'] ?>"><?= htmlspecialchars($cycle['nom_cycle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="text-dark">Section</label>
            <select name="id_section" class="form-select" required>
              <?php foreach ($sections as $section): ?>
              <option value="<?= $section['id'] ?>"><?= htmlspecialchars($section['nom_section']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="text-dark">Année</label>
            <select name="id_annee" class="form-select" required>
              <?php foreach ($annees as $annee): ?>
              <option value="<?= $annee['id'] ?>"><?= htmlspecialchars($annee['libelle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Ajouter</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</main>
  <!-- end content -->


<!-- Script pour actions -->
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>
