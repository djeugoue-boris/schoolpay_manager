<?php
session_start();

// verifier la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/db.php';


// Récupération des années scolaires
$stmt = $pdo->query("SELECT * FROM annees_scolaires ORDER BY libelle DESC");
$annees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des années scolaires - SchoolPay</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/dashboard-admin.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
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
      <a href="../gestion_autres_paiements/index.php"><i class="bi bi-credit-card-2-front"></i>Autres Paiements </a>

 <div class="sidebar-section">Paramètres </div>
<div class="dropdown">
  <a class="dropdown-toggle d-block" href="#" id="dropdownParam" data-bs-toggle="collapse" data-bs-target="#menuParam" aria-expanded="false" aria-controls="menuParam">
    <i class="bi bi-gear-fill"></i> Derouler Menu <i class="bi bi-caret-down float-end"></i>
  </a>
  <div class="collapse ps-3" id="menuParam">
    <a href="" class="d-block mt-2"><i class="bi bi-calendar"></i> Config Années Scolaires</a>
    <a href="../gestion_sections/" class="d-block mt-2"><i class="bi bi-ui-checks"></i> Config Sections</a>
    <a href="../gestion_cycles/" class="d-block mt-2"><i class="bi bi-diagram-3"></i> Config Cycles</a>
    <a href="../gestion_archives/" class="d-block mt-2"><i class="bi bi-archive"></i> Archives</a>
  </div>
</div>

  <div class="sidebar-section"> Utilisateurs</div>
  <a href="../gestion_utilisateurs/"><i class="bi bi-person-fill"></i> Utilisateurs</a>
  <a href="admin_impressions.php"><i class="bi bi-printer-fill"></i> Impressions</a>

 <div class="sidebar-section">Mon compte</div>
<div class="dropdown">
  <a class="dropdown-toggle d-block" href="#" id="dropdownCompte" data-bs-toggle="collapse" data-bs-target="#menuCompte" aria-expanded="false" aria-controls="menuCompte">
    <i class="bi bi-person-circle"></i> Mon compte <i class="bi bi-caret-down float-end"></i>
  </a>
  <div class="collapse ps-3" id="menuCompte">
    <a href="admin_profil.php" class="d-block mt-2"><i class="bi bi-person"></i> Profil</a>
    <a href="../../auth/logout.php" class="d-block mt-2"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
  </div>
</div>
</nav>

<header class="topbar" role="banner">
  <div class="welcome me-auto ps-3">
    👋 Bonjour, <strong>Administrateur de <em><b>SchoolPay</b></em></strong>
  </div>
</header>

<main class="main-content" role="main" tabindex="-1" aria-live="polite">
    <br><br>
    <div class="container">
        <h1>Gestion des années scolaires</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
            unset($_SESSION['message']); 
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <div class="mb-3">
            <a href="ajouter.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Ajouter une année scolaire
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Liste des années scolaires</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Libellé</th>
                                <th>Statut</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($annees as $annee): ?>
                                <tr>
                                    <td><?php echo $annee['id']; ?></td>
                                    <td><?php echo htmlspecialchars($annee['libelle']); ?></td>
                                    <td>
                                        <?php if ($annee['active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($annee['date_creation'])); ?></td>
                                    <td>
                                        <a href="modifier.php?id=<?php echo $annee['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Modifier
                                        </a>
                                        <a href="supprimer.php?id=<?php echo $annee['id']; ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette année scolaire ?')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
                                        <?php if (!$annee['active']): ?>
                                            <a href="activer.php?id=<?php echo $annee['id']; ?>" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-circle"></i> Activer
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>



<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Vérifier le thème préféré de l'utilisateur
        const preferredTheme = localStorage.getItem("theme") || "light";
        setTheme(preferredTheme);
    });
</script>
