<?php
require_once '../../config/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur connecté
try {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COALESCE(e.nom, a.nom) as nom,
               COALESCE(e.prenom, a.prenom) as prenom,
               COALESCE(e.telephone, a.telephone) as telephone,
               COALESCE(e.adresse, a.adresse) as adresse
        FROM utilisateurs u
        LEFT JOIN eleves e ON u.id = e.utilisateur_id
        LEFT JOIN administrateurs a ON u.id = a.utilisateur_id
        WHERE u.id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['message'] = "Utilisateur non trouvé.";
        $_SESSION['message_type'] = "error";
        header("Location: ../../dashboard_administrateur.php");
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur lors de la récupération des données: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../../dashboard_administrateur.php");
    exit();
}

// Récupérer l'historique des connexions
try {
    $stmt = $pdo->prepare("
        SELECT * FROM historique_connexions 
        WHERE utilisateur_id = :user_id 
        ORDER BY date_connexion DESC 
        LIMIT 10
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $historique_connexions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $historique_connexions = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - SchoolPay</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/dashboard-admin.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-5px);
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto;
        }
        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .activity-item {
            padding: 10px;
            border-left: 3px solid #007bff;
            margin-bottom: 10px;
            background: #f8f9fa;
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
    <a href="../gestion_paiements/index.php" class="d-block mt-2"><i class="bi bi-cash-stack"></i> Enregistrer un paiement</a>
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
    <a href="index.php" class="d-block mt-2"><i class="bi bi-person"></i> Profil</a>
    <a href="../../auth/logout.php" class="d-block mt-2"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
  </div>
</div>
</nav>

<header class="topbar" role="banner">
  <div class="welcome me-auto ps-3">
    👋 Bonjour, <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
  </div>
</header>

<main class="main-content" role="main" tabindex="-1" aria-live="polite">
    <br><br>
    <div class="container">
        <h1><i class="bi bi-person-circle"></i> Mon Profil</h1>
        
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

        <div class="row">
            <div class="col-md-4">
                <div class="card profile-card">
                    <div class="card-body text-center">
                        <div class="profile-avatar mb-3">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['role']); ?></p>
                        <span class="badge bg-<?php echo $user['statut'] == 'actif' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($user['statut']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions rapides</h5>
                    </div>
                    <div class="card-body">
                        <a href="modifier.php" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-pencil"></i> Modifier mon profil
                        </a>
                        <a href="changer_mot_de_passe.php" class="btn btn-warning w-100 mb-2">
                            <i class="bi bi-key"></i> Changer mot de passe
                        </a>
                        <a href="historique.php" class="btn btn-info w-100">
                            <i class="bi bi-clock-history"></i> Voir l'historique
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informations personnelles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Identifiant</div>
                                    <div><?php echo htmlspecialchars($user['identifiant']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Nom complet</div>
                                    <div><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Téléphone</div>
                                    <div><?php echo htmlspecialchars($user['telephone'] ?? 'Non renseigné'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Email</div>
                                    <div><?php echo htmlspecialchars($user['email'] ?? 'Non renseigné'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Rôle</div>
                                    <div><?php echo htmlspecialchars($user['role']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Date de création</div>
                                    <div><?php echo date('d/m/Y H:i', strtotime($user['date_creation'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activité récente</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($historique_connexions) > 0): ?>
                            <?php foreach ($historique_connexions as $connexion): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between">
                                        <span>
                                            <i class="bi bi-clock"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($connexion['date_connexion'])); ?>
                                        </span>
                                        <span class="text-muted">
                                            IP: <?php echo htmlspecialchars($connexion['ip_adresse']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Aucune activité récente</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
