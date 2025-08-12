<?php
session_start();
require_once '../../config/db.php';
require_once 'archive_functions.php';

// Vérifier si l'utilisateur est connecté et est administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

// Récupérer les données pour l'interface
$annee_actuelle = date('Y');
$classes = getClasses();
$cycles = getCycles();
$tranches = getTranches();
$utilisateurs = getUtilisateurs();
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
    <a href="../gestion_classes/index.php" class="d-block mt-2"><i class="bi bi-building"></i> Mes Classes</a>
    <a href="../gestion_inscriptions/" class="d-block mt-2"><i class="bi bi-person-plus"></i> Inscriptions</a>
   
  </div>
</div>

<div class="sidebar-section">Paiements</div>
    <a href="../gestion_paiements/index.php" class="d-block mt-2"><i class="bi bi-cash-stack"></i> Enregistrer un paiement</a>
  <a href="../gestion_autres_paiements/index.php"><i class="bi bi-credit-card-2-front"></i> Autres Paiements </a>

 <div class="sidebar-section">Paramètres </div>
<div class="dropdown">
  <a class="dropdown-toggle d-block" href="#" id="dropdownParam" data-bs-toggle="collapse" data-bs-target="#menuParam" aria-expanded="false" aria-controls="menuParam">
    <i class="bi bi-gear-fill"></i> Derouler Menu <i class="bi bi-caret-down float-end"></i>
  </a>
  <div class="collapse ps-3" id="menuParam">
    <a href="../gestion_annees/" class="d-block mt-2"><i class="bi bi-calendar"></i> Config Années Scolaires</a>
    <a href="../gestion_section/" class="d-block mt-2"><i class="bi bi-ui-checks"></i> Config Sections</a>
    <a href="#" class="d-block mt-2"><i class="bi bi-diagram-3"></i> Config Cycles</a>
    <a href="admin_archives.php" class="d-block mt-2"><i class="bi bi-archive"></i> Archives</a>
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
    <style>
        .archive-card {
            border-left: 4px solid #0d6efd;
            transition: transform 0.2s;
        }
        .archive-card:hover {
            transform: translateY(-2px);
        }
        .data-selection {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }
    </style>
<main class="main-content" role="main" tabindex="-1" aria-live="polite">
  <br><br>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="mt-4 mb-4">
                    <i class="bi bi-archive-fill"></i> Gestion des Archives
                </h1>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title text-dark fw-bold">Année en cours</h5>
                                <p class="card-text display-6"><?php echo $annee_actuelle; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title text-dark fw-bold">Classes</h5>
                                <p class="card-text display-6"><?php echo count($classes); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title text-dark fw-bold">Utilisateurs</h5>
                                <p class="card-text display-6"><?php echo count($utilisateurs); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title text-dark fw-bold">Archives créées</h5>
                                <p class="card-text display-6"><?php echo countArchives(); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de création d'archive -->
                <div class="card mb-4" style="border: 2px solid #dee2e6; background-color: #f9fffeff;">
                    <div class="card-header">
                        <h5><i class="bi bi-plus-circle"></i> Créer une nouvelle archive</h5>
                    </div>
                    <div class="card-body">
                        <form id="archiveForm" method="POST" action="archive_process.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Données à conserver :</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold">Classes</label>
                                        <div class="data-selection">
                                            <?php foreach ($classes as $classe): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="classes[]" value="<?php echo $classe['id']; ?>" 
                                                           id="classe<?php echo $classe['id']; ?>" checked>
                                                    <label class="form-check-label text-dark fw-bold" for="classe<?php echo $classe['id']; ?>">
                                                        <?php echo htmlspecialchars($classe['nom_classe']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold">Cycles</label>
                                        <div class="data-selection">
                                            <?php foreach ($cycles as $cycle): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="cycles[]" value="<?php echo $cycle['id']; ?>" 
                                                           id="cycle<?php echo $cycle['id']; ?>" checked>
                                                    <label class="form-check-label text-dark fw-bold" for="cycle<?php echo $cycle['id']; ?>">
                                                        <?php echo htmlspecialchars($cycle['nom_cycle']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold">Tranches</label>
                                        <div class="data-selection">
                                            <?php foreach ($tranches as $tranche): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="tranches[]" value="<?php echo $tranche['id']; ?>" 
                                                           id="tranche<?php echo $tranche['id']; ?>" checked>
                                                    <label class="form-check-label text-dark fw-bold" for="tranche<?php echo $tranche['id']; ?>">
                                                        <?php echo htmlspecialchars($tranche['libelle']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold">Utilisateurs (sauf superadmin)</label>
                                        <div class="data-selection">
                                            <?php foreach ($utilisateurs as $user): ?>
                                                <?php if ($user['role'] != 'superadmin'): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="utilisateurs[]" value="<?php echo $user['id']; ?>" 
                                                               id="user<?php echo $user['id']; ?>" checked>
                                                        <label class="form-check-label text-dark fw-bold" for="user<?php echo $user['id']; ?>">
                                                            <?php echo htmlspecialchars($user['identifiant'] . ' ' . $user['mot_de_passe']); ?>
                                                        </label>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="archiveName" class="form-label text-dark fw-bold">Nom de l'archive</label>
                                        <input type="text" class="form-control" id="archiveName" 
                                               name="archive_name" 
                                               value="archive-<?php echo date('d-m-Y'); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="archiveDescription" class="form-label text-dark fw-bold">Description</label>
                                        <textarea class="form-control" id="archiveDescription" 
                                                  name="description" rows="3" 
                                                  placeholder="Description de l'archive..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" id="createArchiveBtn">
                                <i class="bi bi-archive"></i> Créer l'archive
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Archives existantes -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history"></i> Archives existantes</h5>
                    </div>
                    <div class="card-body">
                        <div id="existingArchives">
                            <!-- Chargé dynamiquement via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de progression -->
    <div class="modal fade" id="progressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Création de l'archive</h5>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="progressBar"></div>
                    </div>
                    <p class="mt-2" id="progressText">Préparation...</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('archiveForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const modal = new bootstrap.Modal(document.getElementById('progressModal'));
    modal.show();

    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    let progress = 0;
    progressBar.style.width = '0%';
    progressText.textContent = 'Initialisation...';

    // Fonction pour animer la barre de progression
    function simulateProgress() {
        if (progress < 90) {  // Ne pas dépasser 90% avant la réponse serveur
            progress += Math.floor(Math.random() * 10) + 5; // +5 à +14%
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
            progressText.textContent = `Création en cours... (${progress}%)`;
            setTimeout(simulateProgress, 400);
        }
    }

    simulateProgress();  // Démarre la progression simulée

    // Lance la requête AJAX
    fetch('archive_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur réseau');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            progress = 100;
            progressBar.style.width = '100%';
            progressText.textContent = 'Archive créée avec succès !';
        } else {
            throw new Error(data.error || 'Erreur serveur inconnue');
        }
    })
    .catch(error => {
        progressBar.classList.add('bg-danger');
        progressText.textContent = `Erreur lors de la création de l'archive : ${error.message}`;
        console.error('Erreur:', error);
    });
});
</script>

</body>
</html>
