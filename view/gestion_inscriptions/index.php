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
    <a href="" class="d-block mt-2"><i class="bi bi-person-plus"></i> Inscriptions</a>
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

<?php
require_once '../../config/db.php'; // Connexion PDO à la base

// Fonction pour générer un matricule unique
function genererMatricule($pdo) {
    $stmt = $pdo->query("SELECT matricule FROM inscriptions ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);
    $numero = ($last && preg_match('/IMT00(\d{4})/', $last['matricule'], $matches)) ? ((int)$matches[1] + 1) : 1;
    return 'IMT00' . str_pad($numero, 4, '0', STR_PAD_LEFT);
}

$nouveauMatricule = genererMatricule($pdo);

// Récupérer les classes, sections, années
$classes = $pdo->query("SELECT id, nom_classe FROM classes ORDER BY nom_classe")->fetchAll();
$sections = $pdo->query("SELECT id, nom_section FROM sections ORDER BY nom_section")->fetchAll();
$annees = $pdo->query("SELECT id, libelle FROM annees_scolaires ORDER BY id DESC")->fetchAll();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $nouveauMatricule;
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['date_naissance'];
    $sexe = $_POST['sexe'];
    $adresse = $_POST['adresse'];
    $telephone = $_POST['telephone'];
    $id_classe = $_POST['id_classe'];
    $id_section = $_POST['id_section'];
    $id_annee = $_POST['id_annee'];
    $frais_inscription = $_POST['frais_inscription'];
    $bourse = $_POST['bourse'] ?? 0;

    // Vérifier si déjà inscrit même année
    $stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE nom=? AND prenom=? AND date_naissance=? AND id_annee=?");
    $stmt->execute([$nom, $prenom, $date_naissance, $id_annee]);

    if ($stmt->rowCount() > 0) {
        $message = "<div class='alert alert-warning'>Élève déjà inscrit pour cette année scolaire.</div>";
    } else {
        $insert = $pdo->prepare("INSERT INTO inscriptions (matricule, nom, prenom, date_naissance, sexe, adresse, telephone, statut, frais_inscription, bourse, id_classe, id_annee) VALUES (?, ?, ?, ?, ?, ?, ?, 'inscrit', ?, ?, ?, ?)");
        $success = $insert->execute([$matricule, $nom, $prenom, $date_naissance, $sexe, $adresse, $telephone, $frais_inscription, $bourse, $id_classe, $id_annee]);

        if ($success) {
            $message = "<div class='alert alert-success'>Inscription réussie. Matricule : <strong>$matricule</strong></div>";
            $nouveauMatricule = genererMatricule($pdo); // Nouveau matricule pour prochaine inscription
        } else {
            $message = "<div class='alert alert-danger'>Erreur d'enregistrement.</div>";
        }
    }
}
?>

<main class="main-content" role="main" tabindex="-1" aria-live="polite">
  <br><br>

    <div class="d-flex justify-content-between align-items-center mb-4">
         <h1>FICHE D'INSCRIPTION DES ELEVES</h1>
        <a href="liste_inscriptions.php" class="btn btn-secondary"><i class="bi bi-list"></i> Liste des Inscrits</a>
    </div>
     <p><small class="text-info">Veillez remplir les champs pour inscrire un élève !</small></p>

    <?= $message ?>
    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Matricule</label>
            <input type="text" class="form-control" value="<?= $nouveauMatricule ?>" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date de Naissance</label>
            <input type="date" name="date_naissance" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sexe</label>
            <select name="sexe" class="form-control" required>
                <option value="">Choisir...</option>
                <option value="M">Masculin</option>
                <option value="F">Féminin</option>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">Adresse</label>
            <input type="text" name="adresse" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Téléphone</label>
            <input type="text" name="telephone" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Frais d'Inscription (FCFA)</label>
            <input type="number" step="0.01" name="frais_inscription" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Bourse (FCFA)</label>
            <input type="number" step="0.01" name="bourse" class="form-control" value="0">
        </div>
        <div class="col-md-6">
            <label class="form-label">Classe</label>
            <select name="id_classe" class="form-control" required>
                <option value="">Choisir...</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom_classe']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Section</label>
            <select name="id_section" class="form-control" required>
                <option value="">Choisir...</option>
                <?php foreach ($sections as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom_section']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Année Scolaire</label>
            <select name="id_annee" class="form-control" required>
                <option value="">Choisir...</option>
                <?php foreach ($annees as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Valider l'Inscription</button>
        </div>
    </form>

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
</script>
</body>
</html>
