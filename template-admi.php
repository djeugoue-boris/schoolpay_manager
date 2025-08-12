                                                                                                                                                                                                                                                                                                                                                                  <?php
// Database connection
require_once 'config/db.php';

// Initialize variables
$totalSections = 0;
$totalTranches = 0;
$totalCycles = 0;
$totalStudents = 0;
$totalClasses = 0;
$totalTeachers = 0;
$totalUsers = 0;
$totalPayments = 0;
$totalPendingPayments = 0;
$totalCompletedPayments = 0;
$totalRevenue = 0;
$monthlyRevenue = 0;
$totalRegistrations = 0;
$currentYearRegistrations = 0;

try {
    // Count sections
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sections");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalSections = $result['total'] ?? 0;

    // Count tranches
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tranches");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalTranches = $result['total'] ?? 0;

    // Count cycles
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cycles");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalCycles = $result['total'] ?? 0;

    // Count students
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM eleves WHERE statut = 'actif'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalStudents = $result['total'] ?? 0;

    // Count classes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM classes WHERE statut = 'actif'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalClasses = $result['total'] ?? 0;

    // Count teachers
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM enseignants WHERE statut = 'actif'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalTeachers = $result['total'] ?? 0;

    // Count users
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM utilisateurs WHERE statut = 'actif'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $result['total'] ?? 0;

    // Count payments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(montant) as total_amount FROM paiements");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPayments = $result['total'] ?? 0;
    $totalRevenue = $result['total_amount'] ?? 0;

    // Count pending payments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM paiements WHERE statut = 'en_attente'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPendingPayments = $result['total'] ?? 0;

    // Count completed payments
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM paiements WHERE statut = 'complet'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalCompletedPayments = $result['total'] ?? 0;

    // Monthly revenue
    $currentMonth = date('Y-m');
    $stmt = $pdo->prepare("SELECT SUM(montant) as monthly_total FROM paiements WHERE DATE_FORMAT(date_paiement, '%Y-%m') = ? AND statut = 'complet'");
    $stmt->execute([$currentMonth]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $monthlyRevenue = $result['monthly_total'] ?? 0;

    // Total registrations
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inscriptions");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRegistrations = $result['total'] ?? 0;

    // Current year registrations
    $currentYear = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inscriptions WHERE YEAR(date_inscription) = ?");
    $stmt->execute([$currentYear]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentYearRegistrations = $result['total'] ?? 0;

} catch (PDOException $e) {
    // Handle database errors gracefully
    error_log("Database error: " . $e->getMessage());
    $totalSections = $totalTranches = $totalCycles = $totalStudents = $totalClasses = $totalTeachers = $totalUsers = 0;
    $totalPayments = $totalPendingPayments = $totalCompletedPayments = $totalRevenue = $monthlyRevenue = 0;
    $totalRegistrations = $currentYearRegistrations = 0;
}
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
    <a href="view/gestion_section/" class="d-block mt-2"><i class="bi bi-ui-checks"></i> Config Sections</a>
    <a href="view/gestion_cycles/" class="d-block mt-2"><i class="bi bi-diagram-3"></i> Config Cycles</a>
    <a href="admin_archives.php" class="d-block mt-2"><i class="bi bi-archive"></i> Archives</a>
  </div>
</div>

  <div class="sidebar-section">Rapports</div>
  <a href="admin_stats.php"><i class="bi bi-graph-up-arrow"></i> Statistiques</a>
  <a href="admin_impressions.php"><i class="bi bi-printer-fill"></i> Impressions</a>

 <div class="sidebar-section">Mon compte</div>
<div class="dropdown">
  <a class="dropdown-toggle d-block" href="#" id="dropdownCompte" data-bs-toggle="collapse" data-bs-target="#menuCompte" aria-expanded="false" aria-controls="menuCompte">
    <i class="bi bi-person-circle"></i> Mon compte <i class="bi bi-caret-down float-end"></i>
  </a>
  <div class="collapse ps-3" id="menuCompte">
    <a href="admin_profil.php" class="d-block mt-2"><i class="bi bi-person"></i> Profil</a>
    <a href="logout.php" class="d-block mt-2"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
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
  <!--contenue des statistiques global et des graphes pour les paiements et les inscriptions et les utilisateurs et solde global de l'ecole-->
  <section class="stats-section">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="stats-card bg-sections">
            <div class="stats-icon"><i class="bi bi-ui-checks"></i></div>
            <div class="stats-title">Sections</div>
            <div class="stats-number"><?php echo $totalSections; ?></div>
            <div class="stats-desc">Nombre total de sections</div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="stats-card bg-tranches">
            <div class="stats-icon"><i class="bi bi-diagram-3"></i></div>
            <div class="stats-title">Tranches</div>
            <div class="stats-number"><?php echo $totalTranches; ?></div>
            <div class="stats-desc">Nombre total de tranches</div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="stats-card bg-cycles">
            <div class="stats-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="stats-title">Cycles</div>
            <div class="stats-number"><?php echo $totalCycles; ?></div>
            <div class="stats-desc">Nombre total de cycles</div>
          </div>
        </div>
      </div>
      
      <!-- Global Statistics Row -->
      <div class="row mt-4">
        <div class="col-md-3 mb-4">
          <div class="stats-card" style="background: #e8f5e8;">
            <div class="stats-icon" style="color: #28a745;"><i class="bi bi-people-fill"></i></div>
            <div class="stats-title" style="color: #155724;">Élèves</div>
            <div class="stats-number" style="color: #28a745;"><?php echo $totalStudents; ?></div>
            <div class="stats-desc" style="color: #5a8f5a;">Total élèves actifs</div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stats-card" style="background: #fff3cd;">
            <div class="stats-icon" style="color: #ffc107;"><i class="bi bi-building"></i></div>
            <div class="stats-title" style="color: #856404;">Classes</div>
            <div class="stats-number" style="color: #ffc107;"><?php echo $totalClasses; ?></div>
            <div class="stats-desc" style="color: #b8860b;">Classes actives</div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stats-card" style="background: #d1ecf1;">
            <div class="stats-icon" style="color: #17a2b8;"><i class="bi bi-person-badge"></i></div>
            <div class="stats-title" style="color: #0c5460;">Enseignants</div>
            <div class="stats-number" style="color: #17a2b8;"><?php echo $totalTeachers; ?></div>
            <div class="stats-desc" style="color: #138496;">Enseignants actifs</div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stats-card" style="background: #f8d7da;">
            <div class="stats-icon" style="color: #dc3545;"><i class="bi bi-person-circle"></i></div>
            <div class="stats-title" style="color: #721c24;">Utilisateurs</div>
            <div class="stats-number" style="color: #dc3545;"><?php echo $totalUsers; ?></div>
            <div class="stats-desc" style="color: #a52a2a;">Utilisateurs système</div>
          </div>
        </div>
      </div>

      <!-- Payment Statistics Row -->
      <div class="row mt-4">
        <div class="col-md-3 mb-4">
          <div class="stats-card" style="background: #d4edda;">
            <div class="stats-icon" style="color: #28a745;"><i class="bi bi-cash-stack"></i></div>
            <div class="stats-title" style="color: #155724;">Revenu Total</div>
            <div class="stats-number" style="color: #28a745;"><?php echo number_format($totalRevenue, 0, ',', ' '); ?> FCFA</div>
            <div class="stats-desc" style="color: #5a8f5a;">Total des paiements</div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stats-card" style="background: #d1ecf1;">
            <div class="stats-icon" style="color: #17a2b8;"><i class="bi bi-calendar-month"></i></div>
            <div class="stats-title" style="color: #0c5460;">Revenu Mensuel</div>
            <div class="stats-number" style="color: #17a2b8;"><?php echo number_format($monthlyRevenue, 0, ',', ' '); ?> FCFA</div>
            <div class="stats-desc" style="color: #138496;">Mois en cours</div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stats-card" style="background: #fff3cd;">
            <div class="stats-icon" style="color: #ffc107;"><i class="bi bi-check-circle"></i></div>
            <div class="stats-title" style="color: #856404;">Paiements Complétés</div>
            <div class="stats-number" style="color: #ffc107;"><?php echo $totalCompletedPayments; ?></div>
            <div class="stats-desc" style="color: #b8860b;">Paiements réussis</div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="stats-card" style="background: #f8d7da;">
            <div class="stats-icon" style="color: #dc3545;"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stats-title" style="color: #721c24;">Paiements en Attente</div>
            <div class="stats-number" style="color: #dc3545;"><?php echo $totalPendingPayments; ?></div>
            <div class="stats-desc" style="color: #a52a2a;">En attente de validation</div>
          </div>
        </div>
      </div>

      <!-- Registration Statistics Row -->
      <div class="row mt-4">
        <div class="col-md-6 mb-4">
          <div class="stats-card" style="background: #e2e3e5;">
            <div class="stats-icon" style="color: #6c757d;"><i class="bi bi-person-plus"></i></div>
            <div class="stats-title" style="color: #495057;">Inscriptions Totales</div>
            <div class="stats-number" style="color: #6c757d;"><?php echo $totalRegistrations; ?></div>
            <div class="stats-desc" style="color: #5a6268;">Depuis la création</div>
          </div>
        </div>
        <div class="col-md-6 mb-4">
          <div class="stats-card" style="background: #d4edda;">
            <div class="stats-icon" style="color: #28a745;"><i class="bi bi-calendar-check"></i></div>
            <div class="stats-title" style="color: #155724;">Inscriptions Année <?php echo $currentYear; ?></div>
            <div class="stats-number" style="color: #28a745;"><?php echo $currentYearRegistrations; ?></div>
            <div class="stats-desc" style="color: #5a8f5a;">Année scolaire en cours</div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="row mt-5">
        <div class="col-md-6 mb-4">
          <div class="card">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0"><i class="bi bi-graph-up"></i> Évolution des Paiements</h5>
            </div>
            <div class="card-body">
              <canvas id="paymentChart" width="400" height="200"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-4">
          <div class="card">
            <div class="card-header bg-success text-white">
              <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Répartition des Paiements</h5>
            </div>
            <div class="card-body">
              <canvas id="paymentPieChart" width="400" height="200"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions Section -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header bg-info text-white">
              <h5 class="mb-0"><i class="bi bi-lightning"></i> Actions Rapides</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3 mb-3">
                  <a href="view/gestion_paiements/" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-plus-circle"></i> Nouveau Paiement
                  </a>
                </div>
                <div class="col-md-3 mb-3">
                  <a href="view/gestion_inscriptions/" class="btn btn-success btn-lg w-100">
                    <i class="bi bi-person-plus"></i> Nouvelle Inscription
                  </a>
                </div>
                <div class="col-md-3 mb-3">
                  <a href="view/gestion_eleves/" class="btn btn-warning btn-lg w-100">
                    <i class="bi bi-people"></i> Gérer Élèves
                  </a>
                </div>
                <div class="col-md-3 mb-3">
                  <a href="admin_stats.php" class="btn btn-info btn-lg w-100">
                    <i class="bi bi-graph-up-arrow"></i> Voir Statistiques
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity Section -->
      <div class="row mt-4">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-warning text-dark">
              <h5 class="mb-0"><i class="bi bi-clock"></i> Activité Récente</h5>
            </div>
            <div class="card-body">
              <div class="list-group">
                <div class="list-group-item">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Dernier paiement enregistré</h6>
                    <small class="text-muted">Aujourd'hui</small>
                  </div>
                  <p class="mb-1">Consultez les derniers paiements dans la section paiements</p>
                </div>
                <div class="list-group-item">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Nouvelle inscription</h6>
                    <small class="text-muted">Hier</small>
                  </div>
                  <p class="mb-1">Gérez les nouvelles inscriptions dans la section inscriptions</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-danger text-white">
              <h5 class="mb-0"><i class="bi bi-exclamation-circle"></i> Alertes</h5>
            </div>
            <div class="card-body">
              <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> 
                <strong><?php echo $totalPendingPayments; ?></strong> paiements en attente de validation
              </div>
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                Revenu mensuel: <strong><?php echo number_format($monthlyRevenue, 0, ',', ' '); ?> FCFA</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    </div>
  </section>
</main>
</div>

<!-- Chart.js CDN -->
<script src="assets/js/theme.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<!-- Scripts for Charts -->
<script>
  // Payment Evolution Chart
  const ctx1 = document.getElementById('paymentChart').getContext('2d');
  new Chart(ctx1, {
    type: 'line',
    data: {
      labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
      datasets: [{
        label: 'Paiements (FCFA)',
        data: [120000, 190000, 300000, 500000, 200000, 300000, 450000, 380000, 420000, 350000, 400000, 480000],
        borderColor: 'rgb(75, 192, 192)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        tension: 0.1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });

  // Payment Distribution Pie Chart
  const ctx2 = document.getElementById('paymentPieChart').getContext('2d');
  new Chart(ctx2, {
    type: 'pie',
    data: {
      labels: ['Complétés', 'En attente', 'Échoués'],
      datasets: [{
        data: [<?php echo $totalCompletedPayments; ?>, <?php echo $totalPendingPayments; ?>, 5],
        backgroundColor: [
          'rgba(40, 167, 69, 0.8)',
          'rgba(255, 193, 7, 0.8)',
          'rgba(220, 53, 69, 0.8)'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
</script>

<!-- Scripts CRUD JS de base (à adapter côté serveur ensuite) -->
<script>
  document.getElementById('formSection').addEventListener('submit', function (e) {
    e.preventDefault();
    const nom = document.getElementById('sectionNom').value.trim();

    if (nom === "") {
      alert("Le nom de la section est requis.");
      return;
    }

    fetch('ajouter_section.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `sectionNom=${encodeURIComponent(nom)}`
    })
    .then(response => response.text())
    .then(data => {
      if (data === 'success') {
        alert("Section ajoutée avec succès !");
        document.getElementById('formSection').reset();
        bootstrap.Modal.getInstance(document.getElementById('modalSection')).hide();
        location.reload(); // Recharge la page pour mettre à jour la liste
      } else {
        alert("Erreur : " + data);
      }
    })
    .catch(error => {
      alert("Erreur réseau : " + error);
    });
  });
</script>


<script>
  // Tranche
  document.getElementById('formTranche').addEventListener('submit', function (e) {
    e.preventDefault();
    const nom = document.getElementById('trancheNom').value.trim();

    if (nom === "") {
      alert("Le nom de la tranche est requis.");
      return;
    }

    fetch('ajouter_tranche.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `trancheNom=${encodeURIComponent(nom)}`
    })
    .then(response => response.text())
    .then(data => {
      if (data === 'success') {
        alert("Tranche ajoutée avec succès !");
        document.getElementById('formTranche').reset();
        bootstrap.Modal.getInstance(document.getElementById('modalTranche')).hide();
        location.reload();
      } else {
        alert("Erreur : " + data);
      }
    })
    .catch(error => alert("Erreur réseau : " + error));
  });

  // Cycle
  document.getElementById('formCycle').addEventListener('submit', function (e) {
    e.preventDefault();
    const nom = document.getElementById('cycleNom').value.trim();

    if (nom === "") {
      alert("Le nom du cycle est requis.");
      return;
    }

    fetch('ajouter_cycle.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `cycleNom=${encodeURIComponent(nom)}`
    })
    .then(response => response.text())
    .then(data => {
      if (data === 'success') {
        alert("Cycle ajouté avec succès !");
        document.getElementById('formCycle').reset();
        bootstrap.Modal.getInstance(document.getElementById('modalCycle')).hide();
        location.reload();
      } else {
        alert("Erreur : " + data);
      }
    })
    .catch(error => alert("Erreur réseau : " + error));
  });
</script>


</main>
<!-- end content  -->



</body>
</html>