<?php
require_once '../../config/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations actuelles de l'utilisateur
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
        header("Location: index.php");
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur lors de la récupération des données: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit();
}

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    
    // Validation des données
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est requis.";
    }
    
    if (!empty($telephone) && !preg_match('/^[0-9+\-\s()]+$/', $telephone)) {
        $errors[] = "Le format du téléphone est invalide.";
    }
    
    if (empty($errors)) {
        try {
            // Mise à jour des informations selon le type d'utilisateur
            if ($user['role'] == 'eleve') {
                $stmt = $pdo->prepare("UPDATE eleves SET nom = :nom, prenom = :prenom, telephone = :telephone, adresse = :adresse WHERE utilisateur_id = :user_id");
            } else {
                $stmt = $pdo->prepare("UPDATE administrateurs SET nom = :nom, prenom = :prenom, telephone = :telephone, adresse = :adresse WHERE utilisateur_id = :user_id");
            }
            
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
            $stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Profil mis à jour avec succès!";
                $_SESSION['message_type'] = "success";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['message'] = "Erreur lors de la mise à jour du profil.";
                $_SESSION['message_type'] = "error";
            }
            
        } catch (PDOException $e) {
            $_SESSION['message'] = "Erreur de base de données: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mon profil - SchoolPay</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/dashboard-admin.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .form-section {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
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
        <h1><i class="bi bi-person-circle"></i> Modifier mon profil</h1>
        
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

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p class="mb-0"><?php echo $error; ?></p>
                <?php endforeach; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card form-section">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Modifier mes informations</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="modifier.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nom" name="nom" 
                                               value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" 
                                               value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telephone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="telephone" name="telephone" 
                                               value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="adresse" class="form-label">Adresse</label>
                                        <input type="text" class="form-control" id="adresse" name="adresse" 
                                               value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
            </div>
        </div>
    </div>
</main>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
