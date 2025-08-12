<?php
session_start();

// Vérifier la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/db.php';

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Traiter la mise à jour du profil
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Mise à jour des informations de base
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $telephone = trim($_POST['telephone']);
        
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, telephone = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $email, $telephone, $user_id]);
        
        $message = 'Profil mis à jour avec succès!';
        $message_type = 'success';
        
        // Rafraîchir les données
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (isset($_POST['change_password'])) {
        // Changement de mot de passe
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Vérifier le mot de passe actuel
        if (password_verify($current_password, $user['mot_de_passe'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                    
                    $message = 'Mot de passe modifié avec succès!';
                    $message_type = 'success';
                } else {
                    $message = 'Le mot de passe doit contenir au moins 6 caractères!';
                    $message_type = 'danger';
                }
            } else {
                $message = 'Les mots de passe ne correspondent pas!';
                $message_type = 'danger';
            }
        } else {
            $message = 'Mot de passe actuel incorrect!';
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['update_avatar'])) {
        // Gestion de l'avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (in_array($_FILES['avatar']['type'], $allowed_types) && $_FILES['avatar']['size'] <= $max_size) {
                $upload_dir = '../../uploads/avatars/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . $user_id . '.' . $extension;
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filepath)) {
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET avatar = ? WHERE id = ?");
                    $stmt->execute([$filename, $user_id]);
                    
                    $message = 'Avatar mis à jour avec succès!';
                    $message_type = 'success';
                    
                    // Rafraîchir les données
                    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $message = 'Erreur lors du téléchargement de l\'image!';
                    $message_type = 'danger';
                }
            } else {
                $message = 'Format d\'image non supporté ou fichier trop volumineux!';
                $message_type = 'danger';
            }
        }
    }
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
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #6f42c1;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .sidebar-section {
            color: #6f42c1;
            font-weight: bold;
            margin: 15px 0 10px 0;
            font-size: 14px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<nav class="sidebar" role="navigation" aria-label="Menu principal">
  <div class="logo-container text-center mb-4">
    <img src="../../assets/img/logo.jpg" alt="Logo" class="logo-img">
    <h5 class="mt-2" style="color: white;"><b>SchoolPay</b></h5>
  </div>

  <div class="sidebar-section">Tableau de bord</div>
  <a href="../../dashboard_administrateur.php">
    <i class="bi bi-bar-chart-fill"></i> <span>Accueil</span>
  </a>

  <div class="sidebar-section">Gestion Scolaire</div>
  <div class="dropdown">
    <a class="dropdown-toggle d-block" href="#" id="dropdownScolaire" data-bs-toggle="collapse" data-bs-target="#menuScolaire">
      <i class="bi bi-mortarboard-fill"></i> Dérouler Menu <i class="bi bi-caret-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="menuScolaire">
      <a href="../gestion_classes/index.php" class="d-block mt-2"><i class="bi bi-building"></i> Mes Classes</a>
      <a href="../gestion_inscriptions/index.php" class="d-block mt-2"><i class="bi bi-person-plus"></i> Inscriptions</a>
    </div>
  </div>

  <div class="sidebar-section">Paiements</div>
  <a href="../gestion_paiements/index.php" class="d-block mt-2"><i class="bi bi-cash-stack"></i> Enregistrer un paiement</a>
  <a href="../gestion_autres_paiements/index.php"><i class="bi bi-credit-card-2-front"></i>Autres Paiements</a>

  <div class="sidebar-section">Paramètres</div>
  <div class="dropdown">
    <a class="dropdown-toggle d-block" href="#" id="dropdownParam" data-bs-toggle="collapse" data-bs-target="#menuParam">
      <i class="bi bi-gear-fill"></i> Dérouler Menu <i class="bi bi-caret-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="menuParam">
      <a href="../gestion_annees/" class="d-block mt-2"><i class="bi bi-calendar"></i> Config Années Scolaires</a>
      <a href="../gestion_section/" class="d-block mt-2"><i class="bi bi-ui-checks"></i> Config Sections</a>
      <a href="../gestion_cycles/" class="d-block mt-2"><i class="bi bi-diagram-3"></i> Config Cycles</a>
    </div>
  </div>

  <div class="sidebar-section">Utilisateurs</div>
  <a href="index.php"><i class="bi bi-person-fill"></i> Utilisateurs</a>

  <div class="sidebar-section">Mon compte</div>
  <a href="profil.php" class="active"><i class="bi bi-person-circle"></i> Mon Profil</a>
  <a href="../../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
</nav>

<header class="topbar">
  <div class="welcome me-auto ps-3">
    👋 Bonjour, <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong>
  </div>
</header>

<main class="main-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <h2 class="mb-4"><i class="bi bi-person-circle"></i> Mon Profil</h2>
        
        <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
          <?= $message ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
          <!-- Informations de profil -->
          <div class="col-md-4">
            <div class="card profile-card">
              <div class="card-body text-center">
                <h5 class="card-title mb-3">Photo de profil</h5>
                <?php
                $avatar_path = '../../uploads/avatars/' . ($user['avatar'] ?? 'default.png');
                if (!file_exists($avatar_path) || !$user['avatar']) {
                    $avatar_path = '../../assets/img/default-avatar.png';
                }
                ?>
                <img src="<?= $avatar_path ?>" alt="Avatar" class="avatar-preview mb-3">
                
                <form method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="update_avatar" value="1">
                  <div class="mb-3">
                    <input type="file" name="avatar" class="form-control" accept="image/*">
                  </div>
                  <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-upload"></i> Changer l'avatar
                  </button>
                </form>
              </div>
            </div>
          </div>
          
          <!-- Informations personnelles -->
          <div class="col-md-8">
            <div class="card profile-card">
              <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-fill"></i> Informations personnelles</h5>
              </div>
              <div class="card-body">
                <form method="POST">
                  <input type="hidden" name="update_profile" value="1">
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Nom</label>
                      <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Prénom</label>
                      <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nom_utilisateur']) ?>" readonly>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Rôle</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                  </div>
                  
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Enregistrer les modifications
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Changement de mot de passe -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card profile-card">
              <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key-fill"></i> Changer le mot de passe</h5>
              </div>
              <div class="card-body">
                <form method="POST">
                  <input type="hidden" name="change_password" value="1">
                  
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Mot de passe actuel</label>
                      <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Nouveau mot de passe</label>
                      <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Confirmer le mot de passe</label>
                      <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                  </div>
                  
                  <button type="submit" class="btn btn-warning">
                    <i class="bi bi-key"></i> Changer le mot de passe
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Historique de connexion -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card profile-card">
              <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Informations du compte</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <p><strong>Date de création :</strong> <?= date('d/m/Y H:i', strtotime($user['date_creation'])) ?></p>
                    <p><strong>Dernière connexion :</strong> <?= $user['derniere_connexion'] ? date('d/m/Y H:i', strtotime($user['derniere_connexion'])) : 'Jamais' ?></p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>Statut :</strong> 
                      <span class="badge bg-<?= $user['statut'] == 'actif' ? 'success' : 'danger' ?>">
                        <?= ucfirst($user['statut']) ?>
                      </span>
                    </p>
                    <p><strong>Adresse IP :</strong> <?= $_SERVER['REMOTE_ADDR'] ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/theme.js"></script>
</body>
</html>
