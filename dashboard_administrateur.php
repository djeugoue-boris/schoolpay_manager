

<?php
// demarrer une nouvelle session
session_start();
// verifier la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Inclusion du fichier de configuration de la base de données
require_once 'config/db.php';

// Classe pour gérer les statistiques
class DashboardStats {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Statistiques des sections, cycles et tranches
    public function getSectionsStats() {
        $query = "SELECT COUNT(*) as total FROM sections WHERE 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getCyclesStats() {
        $query = "SELECT COUNT(*) as total FROM cycles WHERE 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getTranchesStats() {
        $query = "SELECT COUNT(*) as total FROM tranches WHERE 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    // Statistiques des paiements
    public function getTotalPayments() {
        // Total des paiements réguliers
        $query1 = "SELECT COALESCE(SUM(montant_paye), 0) as total FROM paiements WHERE 1";
        $stmt1 = $this->db->prepare($query1);
        $stmt1->execute();
        $totalPaiements = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total des autres paiements
        $query2 = "SELECT COALESCE(SUM(montant_paye), 0) as total FROM autres_paiements WHERE 1";
        $stmt2 = $this->db->prepare($query2);
        $stmt2->execute();
        $totalAutresPaiements = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];
        
        return $totalPaiements + $totalAutresPaiements;
    }
    
    public function getRegistrationFees() {
        $query = "SELECT COALESCE(SUM(frais_inscription), 0) as total FROM inscriptions WHERE 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getSchoolBalance() {
        $totalPayments = $this->getTotalPayments();
        $registrationFees = $this->getRegistrationFees();
        return $totalPayments - $registrationFees;
    }
    
    // Statistiques des utilisateurs
    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getActiveUsers() {
        $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE statut = 'actif'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getInactiveUsers() {
        $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE statut = 'inactif'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}

// Créer une instance de la classe DashboardStats
$stats = new DashboardStats($pdo);

// Récupérer toutes les statistiques
$sectionsCount = $stats->getSectionsStats();
$cyclesCount = $stats->getCyclesStats();
$tranchesCount = $stats->getTranchesStats();
$totalPayments = $stats->getTotalPayments();
$registrationFees = $stats->getRegistrationFees();
$schoolBalance = $stats->getSchoolBalance();
$totalUsers = $stats->getTotalUsers();
$activeUsers = $stats->getActiveUsers();
$inactiveUsers = $stats->getInactiveUsers();


// statique inscriptions
// Requêtes
$total = $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();
$total_inscrits = $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE statut='inscrit'")->fetchColumn();
$total_demis = $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE statut='demissionnaire'")->fetchColumn();
$total_autres = $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE statut='autres'")->fetchColumn();


// Statistiques des utilisateurs
// Récupération des statistiques
$totalUtilisateurs = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$utilisateursActifs = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE statut = 'actif'")->fetchColumn();
$utilisateursInactifs = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE statut = 'inactif'")->fetchColumn();

// Montant total des autres paiements
$sqlTotalAutres = "SELECT SUM(montant_paye) as total FROM autres_paiements";
$totalAutres = $pdo->query($sqlTotalAutres)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Nombre total des autres paiements effectués
$sqlCountAutres = "SELECT COUNT(*) as total FROM autres_paiements";
$countAutres = $pdo->query($sqlCountAutres)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Montant moyen par paiement
$averageAutres = $countAutres > 0 ? $totalAutres / $countAutres : 0;
?>

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tableau de bord Administrateur</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.css">

  <link rel="stylesheet" href="assets/css/dashboard-admin.css" />
  <link rel="stylesheet" href="assets/css/dashboard.css" />
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
    .stats-card {
    border-radius: 8px;
    padding: 20px;
    color: #333;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .stats-card .stats-icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
  }

  .bg-paiements { background-color: #ffe5ec; }    /* Rose pastel */
  .bg-inscription { background-color: #fff3cd; }  /* Jaune pastel */
  .bg-solde { background-color: #e0f7fa; }        /* Bleu pastel */

  .bg-utilisateurs { background-color: #e3f2fd; } /* Bleu clair */
  .bg-actifs { background-color: #d0f0c0; }       /* Vert pastel */
  .bg-inactifs { background-color: #fddede; }     /* Rose clair */

</style>
</head>
<body>
<nav class="sidebar" role="navigation" aria-label="Menu principal">
  <div class="logo-container text-center mb-4">
    <img src="assets/img/logo.jpg" alt="Logo" class="logo-img" />
    <h5 class="mt-2" style="color: white;"><b>SchoolPay</b></h5>
  </div>

  <div class="sidebar-section">Tableau de bord</div>
  <a href="dashboard_administrateur.php" class="active">
    <i class="bi bi-bar-chart-fill"></i> <span>Accueil</span>
  </a>

  <div class="sidebar-section">Gestion Scolaire</div>
  <div class="dropdown">
    <a class="dropdown-toggle d-block" href="#" id="dropdownScolaire" data-bs-toggle="collapse" data-bs-target="#menuScolaire" aria-expanded="false" aria-controls="menuScolaire">
      <i class="bi bi-mortarboard-fill"></i> Derouler Menu <i class="bi bi-caret-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="menuScolaire">
      <a href="view/gestion_classes/index.php" class="d-block mt-2"><i class="bi bi-building"></i> Mes Classes</a>
      <a href="view/gestion_inscriptions/" class="d-block mt-2"><i class="bi bi-person-plus"></i> Inscriptions</a>
    </div>
  </div>

    <div class="sidebar-section">Paiements</div>
      <a href="view/gestion_paiements/" class="d-block mt-2"><i class="bi bi-cash-stack"></i> Enregistrer un paiement</a>
    <a href="view/gestion_autres_paiements/index.php"><i class="bi bi-credit-card-2-front"></i>Autres Paiements </a>

  <div class="sidebar-section">Paramètres </div>
  <div class="dropdown">
    <a class="dropdown-toggle d-block" href="#" id="dropdownParam" data-bs-toggle="collapse" data-bs-target="#menuParam" aria-expanded="false" aria-controls="menuParam">
      <i class="bi bi-gear-fill"></i> Derouler Menu <i class="bi bi-caret-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="menuParam">
      <a href="view/gestion_annees/index.php" class="d-block mt-2"><i class="bi bi-calendar"></i> Config Années Scolaires</a>
      <a href="view/gestion_section/" class="d-block mt-2"><i class="bi bi-ui-checks"></i> Config Sections</a>
      <a href="view/gestion_cycles/" class="d-block mt-2"><i class="bi bi-diagram-3"></i> Config Cycles</a>
      <a href="admin_archives.php" class="d-block mt-2"><i class="bi bi-archive"></i> Archives</a>
    </div>
  </div>

  <div class="sidebar-section"> Utilisateurs</div>
  <a href="view/gestion_utilisateurs/index.php"><i class="bi bi-person-fill"></i> Utilisateurs</a>
  <a href="admin_impressions.php"><i class="bi bi-printer-fill"></i> Impressions</a>

  <div class="sidebar-section">Mon compte</div>
  <div class="dropdown">
    <a class="dropdown-toggle d-block" href="#" id="dropdownCompte" data-bs-toggle="collapse" data-bs-target="#menuCompte" aria-expanded="false" aria-controls="menuCompte">
      <i class="bi bi-person-circle"></i> Mon compte <i class="bi bi-caret-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="menuCompte">
      <a href="view/gestion_profil_utilisateurs/index.php" class="d-block mt-2"><i class="bi bi-person"></i> Profil</a>
      <a href="auth/logout.php" class="d-block mt-2"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
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
  <section class="content">
    <h1 style="color: var(--text);">Bienvenue dans le tableau de bord Administrateur</h1>
    <p>Utilisez les menus à gauche pour gérer les utilisateurs, les paiements, les inscriptions et consulter les statistiques.</p>
  </section><br>
  <!--  statistiques inscriptions -->
  <div class="container mt-4">
    <h2>Statistiques des Inscriptions</h2>
    <div class="row">
        <div class="col-md-3">
            <div class="stats-card" style="background: #f49b9b;">
                <div class="stats-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stats-title">Total Inscriptions</div>
                <div class="stats-number"><?= $total ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: #03ffd5;">
                <div class="stats-icon"><i class="bi bi-person-check-fill"></i></div>
                <div class="stats-title">Inscrits</div>
                <div class="stats-number"><?= $total_inscrits ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: #bedef9;">
                <div class="stats-icon"><i class="bi bi-person-x-fill"></i></div>
                <div class="stats-title">Démissionnaires</div>
                <div class="stats-number"><?= $total_demis ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card" style="background: #f5b642;">
                <div class="stats-icon"><i class="bi bi-person-dash-fill"></i></div>
                <div class="stats-title">Autres</div>
                <div class="stats-number"><?= $total_autres ?></div>
            </div>
        </div>
    </div>
</div>
 <!-- Statistiques des Paiements -->
     <div class="container mt-4">
        <h2 class="mb-4 text-start">Statistiques des Paiements</h2>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="stats-card text-white" style="background: linear-gradient(135deg, #ff6b6b, #ff4757); border-radius: 12px; padding: 20px;">
              <div class="stats-icon fs-1 mb-2"><i class="bi bi-currency-exchange"></i></div>
              <div class="stats-title fw-bold fs-5">Montant Total des Paiements</div>
              <div class="stats-number fs-3"><?php echo number_format($totalPayments, 0, ',', ' '); ?> F</div>
              <div class="stats-desc">Total des paiements effectués</div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="stats-card text-white" style="background: linear-gradient(135deg, #feca57, #ff9f43); border-radius: 12px; padding: 20px;">
              <div class="stats-icon fs-1 mb-2"><i class="bi bi-receipt"></i></div>
              <div class="stats-title fw-bold fs-5">Frais d'Inscription</div>
              <div class="stats-number fs-3"><?php echo number_format($registrationFees, 0, ',', ' '); ?> F</div>
              <div class="stats-desc">Total des frais d'inscription encaissés</div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="stats-card text-white" style="background: linear-gradient(135deg, #54a0ff, #2e86de); border-radius: 12px; padding: 20px;">
              <div class="stats-icon fs-1 mb-2"><i class="bi bi-piggy-bank"></i></div>
              <div class="stats-title fw-bold fs-5">Solde Global de l'École</div>
              <div class="stats-number fs-3"><?php echo number_format($schoolBalance, 0, ',', ' '); ?> F</div>
              <div class="stats-desc">Solde disponible après frais d'inscription</div>
            </div>
          </div>
        </div>
      </div>
      <!-- statistiques des autres paiements -->

<div class="container mt-4">
  <h2 class="mb-4 text-start">Statistiques des Autres Paiements</h2>
  <div class="row g-4">
    <!-- Montant Total -->
    <div class="col-md-4">
      <div class="stats-card text-white" style="background: linear-gradient(135deg, #1dd1a1, #10ac84); border-radius: 12px; padding: 20px;">
        <div class="stats-icon fs-1 mb-2"><i class="bi bi-cash-stack"></i></div>
        <div class="stats-title fw-bold fs-5">Montant Total</div>
        <div class="stats-number fs-3"><?php echo number_format($totalAutres, 0, ',', ' '); ?> F</div>
        <div class="stats-desc">Somme totale des autres paiements</div>
      </div>
    </div>

    <!-- Nombre de paiements -->
    <div class="col-md-4">
      <div class="stats-card text-white" style="background: linear-gradient(135deg, #ff9ff3, #f368e0); border-radius: 12px; padding: 20px;">
        <div class="stats-icon fs-1 mb-2"><i class="bi bi-list-check"></i></div>
        <div class="stats-title fw-bold fs-5">Nombre de Paiements</div>
        <div class="stats-number fs-3"><?php echo $countAutres; ?></div>
        <div class="stats-desc">Nombre total de transactions enregistrées</div>
      </div>
    </div>

    <!-- Montant moyen -->
    <div class="col-md-4">
      <div class="stats-card text-white" style="background: linear-gradient(135deg, #48dbfb, #0abde3); border-radius: 12px; padding: 20px;">
        <div class="stats-icon fs-1 mb-2"><i class="bi bi-graph-up"></i></div>
        <div class="stats-title fw-bold fs-5">Moyenne par Paiement</div>
        <div class="stats-number fs-3"><?php echo number_format($averageAutres, 0, ',', ' '); ?> F</div>
        <div class="stats-desc">Montant moyen par paiement</div>
      </div>
    </div>
  </div>
</div>


  <!--contenue des statistiques global et des graphes pour les paiements et les inscriptions et les utilisateurs et solde global de l'ecole-->
  <!-- conteneur du nombre de section et du nombre de cycle et de tranches -->
  <div class="container mt-4">
    <h2>Statistiques Globales</h2>
    <div class="row">
      <div class="col-md-4">
        <div class="stats-card bg-sections">
          <div class="stats-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
          <div class="stats-title">Sections</div>
          <div class="stats-number"><?php echo number_format($sectionsCount); ?></div>
          <div class="stats-desc">Total des sections actives</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card bg-cycles">
          <div class="stats-icon"><i class="bi bi-arrow-repeat"></i></div>
          <div class="stats-title">Cycles</div>
          <div class="stats-number"><?php echo number_format($cyclesCount); ?></div>
          <div class="stats-desc">Total des cycles disponibles</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card bg-tranches">
          <div class="stats-icon"><i class="bi bi-layers-fill"></i></div>
          <div class="stats-title">Tranches</div>
          <div class="stats-number"><?php echo number_format($tranchesCount); ?></div>
          <div class="stats-desc">Total des tranches de paiement</div>
        </div>
      </div>
    </div>
  </div>
  <!-- fin conteneur du nombre de section et du nombre de cycle et de tranches -->
   <!-- conteneur qui preente les statistique des paiements et autres paiements ainsi que le solde global de l'ecole  et le solde des frais d'inscriptions encaissés -->
  
   <!-- fin conteneur qui preente les statistique des paiements et autres paiements ainsi que le solde global de l'ecole  et le solde des frais d'inscriptions encaissés -->
    <!-- conteneur qui presente les statistiques des utilisateurs -->
    <!-- Statistiques des Utilisateurs -->
      <div class="container mt-4">
    <h2>Statistiques des Utilisateurs</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="stats-card" style="background: #f49b9bff;">
              <div class="stats-icon"><i class="bi bi-person-fill"></i></div>
              <div class="stats-title">Nombre Total d'Utilisateurs</div>
              <div class="stats-number" id="nombreTotalUtilisateurs"><?= $totalUtilisateurs ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card" style="background: #03ffd5ff;">
              <div class="stats-icon"><i class="bi bi-person-check-fill"></i></div>
              <div class="stats-title">Nombre d'Utilisateurs Actifs</div>
              <div class="stats-number" id="nombreUtilisateursActifs"><?= $utilisateursActifs ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card" style="background: #bedef9ff;">
              <div class="stats-icon"><i class="bi bi-person-x-fill"></i></div>
              <div class="stats-title">Nombre d'Utilisateurs Inactifs</div>
              <div class="stats-number" id="nombreUtilisateursInactifs"><?= $utilisateursInactifs ?></div>
            </div>
        </div>
    </div>
</div>
   <!-- fin conteneur qui presente les statistiques des utilisateurs -->
    

</main>
</div>
<!-- end content  -->






<!-- Chart.js CDN -->
<script src="assets/js/theme.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>