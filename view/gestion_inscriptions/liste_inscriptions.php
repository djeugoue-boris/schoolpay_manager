<?php
session_start();

// verifier la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}
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
    /* style tableaux  */
    .table-inscriptions th {
        background-color: #6f42c1;  /* Violet Bootstrap */
        color: white;
        text-align: center;
        vertical-align: middle;
      }

      .table-inscriptions td {
        vertical-align: middle;
        text-align: center;
      }

      .table-inscriptions tr:hover {
        background-color: #f4f0fb;  /* léger violet clair */
      }

      .btn-sm {
        margin: 0 2px;
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
    <a href="index.php" class="d-block mt-2"><i class="bi bi-person-plus"></i> Inscriptions</a>
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

<main class="main-content" role="main" tabindex="-1" aria-live="polite">
  <br><br>

    <!-- content star -->
<?php
require_once '../../config/db.php';
require_once '../../vendor/autoload.php'; // Pour DomPDF

use Dompdf\Dompdf;

// Gestion des filtres
$filters = [];
$where = [];
$params = [];

// Filtre par classe
if (isset($_GET['classe']) && $_GET['classe'] != '') {
    $where[] = "cl.id = :classe";
    $params[':classe'] = $_GET['classe'];
}

// Filtre par section
if (isset($_GET['section']) && $_GET['section'] != '') {
    $where[] = "sec.id = :section";
    $params[':section'] = $_GET['section'];
}

// Filtre par année scolaire
if (isset($_GET['annee']) && $_GET['annee'] != '') {
    $where[] = "an.id = :annee";
    $params[':annee'] = $_GET['annee'];
}

// Filtre par sexe
if (isset($_GET['sexe']) && $_GET['sexe'] != '') {
    $where[] = "insc.sexe = :sexe";
    $params[':sexe'] = $_GET['sexe'];
}

// Filtre par recherche
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = '%' . $_GET['search'] . '%';
    $where[] = "(insc.nom LIKE :search OR insc.prenom LIKE :search OR insc.matricule LIKE :search)";
    $params[':search'] = $search;
}

// Construction de la requête
$sql = "
SELECT insc.*, cl.nom_classe, sec.nom_section, an.libelle AS annee_scolaire
FROM inscriptions insc
JOIN classes cl ON insc.id_classe = cl.id
JOIN sections sec ON cl.id_section = sec.id
JOIN annees_scolaires an ON insc.id_annee = an.id
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY insc.date_inscription DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des données pour les filtres
$classes = $pdo->query("SELECT id, nom_classe FROM classes ORDER BY nom_classe")->fetchAll(PDO::FETCH_ASSOC);
$sections = $pdo->query("SELECT id, nom_section FROM sections ORDER BY nom_section")->fetchAll(PDO::FETCH_ASSOC);
$annees = $pdo->query("SELECT id, libelle FROM annees_scolaires ORDER BY libelle DESC")->fetchAll(PDO::FETCH_ASSOC);

// Génération PDF
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
    $dompdf = new Dompdf();
    
    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { width: 100px; height: auto; }
            .title { font-size: 24px; font-weight: bold; margin: 10px 0; }
            .subtitle { font-size: 16px; color: #666; }
            .date { font-size: 14px; color: #888; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
            th { background-color: #6f42c1; color: white; }
            .total { margin-top: 20px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1 class="title">COLLEGE POLYVALENT BILINGUE MARIE THERESE</h1>
            <p class="subtitle">Liste des Élèves Inscrits</p>
            <p class="date">Généré le: ' . date('d/m/Y H:i') . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date Naissance</th>
                    <th>Sexe</th>
                    <th>Classe</th>
                    <th>Section</th>
                    <th>Année</th>
                    <th>Frais Inscription</th>
                    <th>Bourse</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($inscriptions as $index => $inscription) {
        $html .= '
                <tr>
                    <td>' . ($index + 1) . '</td>
                    <td>' . htmlspecialchars($inscription['matricule']) . '</td>
                    <td>' . htmlspecialchars($inscription['nom']) . '</td>
                    <td>' . htmlspecialchars($inscription['prenom']) . '</td>
                    <td>' . $inscription['date_naissance'] . '</td>
                    <td>' . $inscription['sexe'] . '</td>
                    <td>' . htmlspecialchars($inscription['nom_classe']) . '</td>
                    <td>' . htmlspecialchars($inscription['nom_section']) . '</td>
                    <td>' . htmlspecialchars($inscription['annee_scolaire']) . '</td>
                    <td>' . number_format($inscription['frais_inscription'], 2) . ' FCFA</td>
                    <td>' . number_format($inscription['bourse'], 2) . ' FCFA</td>
                </tr>';
    }

    $total_eleves = count($inscriptions);
    $total_frais = array_sum(array_column($inscriptions, 'frais_inscription'));
    $total_bourse = array_sum(array_column($inscriptions, 'bourse'));

    $html .= '
            </tbody>
        </table>
        
        <div class="total">
            <p>Total élèves: ' . $total_eleves . '</p>
            <p>Total frais inscription: ' . number_format($total_frais, 2) . ' FCFA</p>
            <p>Total bourses: ' . number_format($total_bourse, 2) . ' FCFA</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('liste_eleves_inscrits_' . date('Y-m-d') . '.pdf', ['Attachment' => false]);
    exit();
}
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><b>LES INSCRITS AU COLLEGE POLYVALENT BILINGUE MARIE THERESE</b></h2>
        <a href="#" class="btn btn-secondary"><i class="bi bi-list"></i> Liste des Inscrits</a>
    </div>
    <!-- gestion des messages d'alertes -->

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ Inscription mise à jour avec succès.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        ⚠️ Inscription supprimée avec succès.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ❌ Une erreur est survenue : <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>


    <!-- end message alerte -->
    <h3>Liste des Élèves Inscrits</h3>
    <a href="index.php" class="btn btn-primary mb-3">+ Nouvelle Inscription</a>
    
    <!-- Système de filtrage amélioré -->
    <form method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-2">
                <label for="classe" class="form-label">Classe</label>
                <select name="classe" class="form-select">
                    <option value="">Toutes les classes</option>
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?= $classe['id'] ?>" <?= isset($_GET['classe']) && $_GET['classe'] == $classe['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($classe['nom_classe']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="section" class="form-label">Section</label>
                <select name="section" class="form-select">
                    <option value="">Toutes les sections</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?= $section['id'] ?>" <?= isset($_GET['section']) && $_GET['section'] == $section['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($section['nom_section']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="annee" class="form-label">Année Scolaire</label>
                <select name="annee" class="form-select">
                    <option value="">Toutes les années</option>
                    <?php foreach ($annees as $annee): ?>
                        <option value="<?= $annee['id'] ?>" <?= isset($_GET['annee']) && $_GET['annee'] == $annee['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($annee['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="sexe" class="form-label">Sexe</label>
                <select name="sexe" class="form-select">
                    <option value="">Tous</option>
                    <option value="M" <?= isset($_GET['sexe']) && $_GET['sexe'] == 'M' ? 'selected' : '' ?>>Masculin</option>
                    <option value="F" <?= isset($_GET['sexe']) && $_GET['sexe'] == 'F' ? 'selected' : '' ?>>Féminin</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" name="search" class="form-control" placeholder="Nom, prénom ou matricule" 
                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrer
                </button>
            </div>
        </div>
        
        <div class="row g-3 mt-2">
            <div class="col-md-10"></div>
            <div class="col-md-1">
               <a href="export_pdf.php?<?= http_build_query(array_merge($_GET, ['export' => 'pdf'])) ?>" 
                class="btn btn-danger w-100" target="_blank">
                  <i class="bi bi-file-pdf"></i> PDF
              </a>

            </div>
            <div class="col-md-1">
                <a href="liste_inscriptions.php" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </div>
    </form>
    
    <!-- Statistiques -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card text-center bg-warning shadow-lg">
                <div class="card-body">
                    <h5 class="card-title"><?= count($inscriptions) ?></h5>
                    <p class="card-text text-dark">Total élèves</p>
                </div>
+            </div>
+        </div>
+        <div class="col-md-3">
+            <div class="card text-center bg-success shadow-lg">
+                <div class="card-body">
+                    <h5 class="card-title"><?= number_format(array_sum(array_column($inscriptions, 'frais_inscription')), 2) ?> FCFA</h5>
+                    <p class="card-text text-dark">Total frais inscription</p>
+                </div>
+            </div>
+        </div>
+        <div class="col-md-3">
+            <div class="card text-center bg-info shadow-lg">
+                <div class="card-body">
+                    <h5 class="card-title"><?= number_format(array_sum(array_column($inscriptions, 'bourse')), 2) ?> FCFA</h5>
+                    <p class="card-text text-dark">Total bourses</p>
+                </div>
+            </div>
+        </div>
+        <div class="col-md-3">
+            <div class="card text-center bg-light shadow-lg">
+                <div class="card-body">
+                    <h5 class="card-title"><?= count(array_filter($inscriptions, fn($i) => $i['sexe'] == 'M')) ?></h5>
+                    <p class="card-text text-dark">Garçons</p>
+                </div>
+            </div>
+        </div>
+    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped table-inscriptions">
    <thead>
        <tr>
            <th>#</th>
            <th>Matricule</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Date Naissance</th>
            <th>Sexe</th>
            <th>Téléphone</th>
            <th>Classe</th>
            <th>Section</th>
            <th>Année</th>
            <th>Frais Inscription</th>
            <th>Bourse</th>
            <th>Date Inscription</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php $index=1; foreach ($inscriptions as $i): ?>
            <tr>
                <td><?= $index++ ?></td>
                <td><?= htmlspecialchars($i['matricule']) ?></td>
                <td><?= htmlspecialchars($i['nom']) ?></td>
                <td><?= htmlspecialchars($i['prenom']) ?></td>
                <td><?= $i['date_naissance'] ?></td>
                <td><?= $i['sexe'] ?></td>
                <td><?= htmlspecialchars($i['telephone']) ?></td>
                <td><?= htmlspecialchars($i['nom_classe']) ?></td>
                <td><?= htmlspecialchars($i['nom_section']) ?></td>
                <td><?= htmlspecialchars($i['annee_scolaire']) ?></td>
                <td><?= number_format($i['frais_inscription'], 2) ?> FCFA</td>
                <td><?= number_format($i['bourse'], 2) ?> FCFA</td>
                <td><?= date('d/m/Y', strtotime($i['date_inscription'])) ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $i['id'] ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $i['id'] ?>">
                        <i class="bi bi-trash"></i>
                    </button>
                    <a href="generer_recu.php?id=<?= $i['id'] ?>" class="btn btn-sm btn-success" target="_blank">
                        <i class="bi bi-printer-fill"></i>
                    </a>
                </td>
            </tr>
 <!-- modal pour update et delete -->
                <!-- Modal pour éditer -->
                <div class="modal fade" id="editModal<?= $i['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $i['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel<?= $i['id'] ?>">Modifier l'inscription</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="modifier_inscription.php" method="POST">
                                    <input type="hidden" name="id" value="<?= $i['id'] ?>">
                                    <div class="mb-3">
                                        <label for="nom<?= $i['id'] ?>" class="form-label text-dark">Nom</label>
                                        <input type="text" class="form-control" id="nom<?= $i['id'] ?>" name="nom" value="<?= htmlspecialchars($i['nom']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="prenom<?= $i['id'] ?>" class="form-label text-dark">Prénom</label>
                                        <input type="text" class="form-control" id="prenom<?= $i['id'] ?>" name="prenom" value="<?= htmlspecialchars($i['prenom']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="telephone<?= $i['id'] ?>" class="form-label text-dark">Téléphone</label>
                                        <input type="text" class="form-control" id="telephone<?= $i['id'] ?>" name="telephone" value="<?= htmlspecialchars($i['telephone']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="frais_inscription<?= $i['id'] ?>" class="form-label text-dark">Frais d'Inscription</label>
                                        <input type="number" step="0.01" class="form-control" id="frais_inscription<?= $i['id'] ?>" name="frais_inscription" value="<?= $i['frais_inscription'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bourse<?= $i['id'] ?>" class="form-label text-dark">Montant de la Bourse</label>
                                        <input type="number" step="0.01" class="form-control" id="bourse<?= $i['id'] ?>" name="bourse" value="<?= $i['bourse'] ?>" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal pour supprimer -->
                <div class="modal fade" id="deleteModal<?= $i['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $i['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel<?= $i['id'] ?>">Confirmer la suppression</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-dark">Êtes-vous sûr de vouloir supprimer l'inscription de <strong><?= htmlspecialchars($i['nom']) ?> <?= htmlspecialchars($i['prenom']) ?></strong> ?</p>
                                <p class="text-dark">Cette action est irréversible.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <a href="supprimer_inscription.php?id=<?= $i['id'] ?>" class="btn btn-danger">Supprimer</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end modal pour update et delete -->
            <?php endforeach; ?>
        </tbody>
    </table>

               


    <!-- content end -->
</main>


<!-- Script pour actions -->
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
function populateEditModal(id, id_eleve, id_classe, id_annee) {
  // Remplir les champs d’un autre modal (à créer si besoin)
  alert('Édition non encore implémentée. ID: ' + id);
}

function deleteInscription(id) {
  if (confirm('Voulez-vous vraiment supprimer cette inscription ?')) {
    window.location.href = 'supprimer_inscription.php?id=' + id;
  }
}

// js search systeme

function searchTable() {
  let input = document.getElementById("searchInput").value.toLowerCase();
  let rows = document.querySelectorAll(".table-inscriptions tbody tr");

  rows.forEach(row => {
    let text = row.innerText.toLowerCase();
    row.style.display = text.includes(input) ? "" : "none";
  });
}
</script>
</body>
</html>
