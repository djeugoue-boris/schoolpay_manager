<!-- code php pour ajouter un cycle -->
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
$message = '';
$errors = [];
$cycle = null;
// Récupération de l'ID depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Charger les données existantes
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cycles WHERE id = ?");
        $stmt->execute([$id]);
        $cycle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cycle) {
            header("Location: index.php?error=Cycle non trouvé");
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur lors de la récupération du cycle: " . $e->getMessage());
    }
} else {
    header("Location: index.php?error=ID invalide");
    exit();
}
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_cycle = trim($_POST['nom_cycle'] ?? '');
    
    // Validation
    if (empty($nom_cycle)) {
        $errors[] = "Le nom du cycle est obligatoire";
    } elseif (strlen($nom_cycle) > 100) {
        $errors[] = "Le nom du cycle ne doit pas dépasser 100 caractères";
    }
    // Si pas d'erreurs, on met à jour dans la base
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE cycles SET nom_cycle = ? WHERE id = ?");
            $stmt->execute([$nom_cycle, $id]);
            header("Location: index.php?message=Cycle modifié avec succès");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la modification du cycle: " . $e->getMessage();
        }
    }
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
    <a href="admin/gestion_inscriptions/index.php" class="d-block mt-2"><i class="bi bi-person-plus"></i> Inscriptions</a>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Modifier le Cycle</h1>
                <a href="index.php" class="btn btn-secondary">Retour à la liste</a>
            </div>
            
            <div class="form-container">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">Erreurs:</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($cycle): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nom_cycle" class="form-label">Nom du cycle *</label>
                            <input type="text" class="form-control" id="nom_cycle" name="nom_cycle" 
                                value="<?= htmlspecialchars($cycle['nom_cycle']) ?>" 
                                maxlength="100" required>
                            <div class="form-text">Maximum 100 caractères</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php" class="btn btn-outline-secondary me-md-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">Aucune donnée de cycle trouvée.</div>
                <?php endif; ?>
            </div>
        </div>


   <!-- Script pour actions -->
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
