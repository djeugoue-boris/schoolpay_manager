<?php
session_start();

// verifier la connexion de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/db.php'; // doit définir $pdo (PDO)

// Construction des conditions de filtre
$where = [];
$params = [];

// Récup des valeurs GET en sécurisant/trim
$search = trim($_GET['search'] ?? '');
$date_debut = trim($_GET['date_debut'] ?? '');
$date_fin = trim($_GET['date_fin'] ?? '');

// Search
if ($search !== '') {
    $where[] = "(insc.nom LIKE :search OR insc.prenom LIKE :search OR insc.matricule LIKE :search OR ap.objet LIKE :search)";
    $params['search'] = "%{$search}%";
}

// Date début (valider le format YYYY-MM-DD)
if ($date_debut !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $date_debut);
    if ($d) {
        $where[] = "DATE(ap.date_paiement) >= :date_debut";
        $params['date_debut'] = $d->format('Y-m-d');
    }
}

// Date fin (valider le format YYYY-MM-DD)
if ($date_fin !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $date_fin);
    if ($d) {
        $where[] = "DATE(ap.date_paiement) <= :date_fin";
        $params['date_fin'] = $d->format('Y-m-d');
    }
}

// Requête de base (utilise l'alias 'ap')
$sql = "
SELECT ap.id, ap.objet, ap.montant_paye, ap.date_paiement, ap.observations,
       ap.id_classe, ap.id_annee, ap.id_section,
       insc.id AS id_inscription, insc.matricule, insc.nom, insc.prenom,
       cl.nom_classe, sec.nom_section, an.libelle AS annee_scolaire
FROM autres_paiements ap
JOIN inscriptions insc ON ap.id_inscription = insc.id
JOIN classes cl ON ap.id_classe = cl.id
JOIN sections sec ON ap.id_section = sec.id
JOIN annees_scolaires an ON ap.id_annee = an.id
";

// Ajout des WHERE si besoin
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY ap.date_paiement DESC";

// Prépare, exécute et récupère
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des classes (pour le select) — inchangé
$classes = $pdo->query("
  SELECT cl.id, cl.nom_classe, cl.id_section, sec.nom_section, cl.id_annee, an.libelle
  FROM classes cl
  JOIN sections sec ON cl.id_section = sec.id
  JOIN annees_scolaires an ON cl.id_annee = an.id
  ORDER BY an.libelle DESC, sec.nom_section, cl.nom_classe
")->fetchAll(PDO::FETCH_ASSOC);
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
  <a href=""><i class="bi bi-credit-card-2-front"></i> Autres Paiements </a>

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
  <?php


  // Récupérer classes + section + année (pour le select de la modal)
  $classes = $pdo->query("
    SELECT cl.id, cl.nom_classe, cl.id_section, sec.nom_section, cl.id_annee, an.libelle
    FROM classes cl
    JOIN sections sec ON cl.id_section = sec.id
    JOIN annees_scolaires an ON cl.id_annee = an.id
    ORDER BY an.libelle DESC, sec.nom_section, cl.nom_classe
  ")->fetchAll(PDO::FETCH_ASSOC);

  // alert messages
  $success = isset($_GET['success']) ? intval($_GET['success']) : 0;
  $error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
  ?>
  <main class="main-content" role="main" tabindex="-1" aria-live="polite">
    <br><br>
      <h1>Autres paiements</h1>
      <p>Liste et enregistrement des autres paiements.</p>

      <?php if ($success === 1): ?>
        <div class="alert alert-success">Paiement enregistré avec succès.</div>
      <?php elseif ($success === 2): ?>
        <div class="alert alert-success">Paiement supprimé.</div>
      <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      <!-- filtre et recherche pour exportation -->
      <?php
  // Connexion PDO (exemple)
  $dsn = 'mysql:host=127.0.0.1;dbname=schoolpay_db_3;charset=utf8mb4';
  $user = 'root';
  $pass = '';
  $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
  $pdo = new PDO($dsn, $user, $pass, $options);

  // Récupérer les filtres
  $search = $_GET['search'] ?? '';
  $date_debut = $_GET['date_debut'] ?? '';
  $date_fin = $_GET['date_fin'] ?? '';

  // Construire la requête SQL avec clauses conditionnelles
  $sql = "SELECT ap.*, i.nom, i.matricule 
          FROM autres_paiements ap
          JOIN inscriptions i ON ap.id_inscription = i.id
          WHERE 1=1";

  $params = [];

  if ($search !== '') {
      $sql .= " AND (i.nom LIKE :search OR i.matricule LIKE :search OR ap.objet LIKE :search)";
      $params[':search'] = "%$search%";
  }

  if ($date_debut !== '') {
      $sql .= " AND ap.date_paiement >= :date_debut";
      $params[':date_debut'] = $date_debut;
  }

  if ($date_fin !== '') {
      $sql .= " AND ap.date_paiement <= :date_fin";
      $params[':date_fin'] = $date_fin . " 23:59:59"; // inclure toute la journée
  }

  $sql .= " ORDER BY ap.date_paiement DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);


  ?>


      <!-- filtre et recherche simple -->
       <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
          <input type="text" name="search" class="form-control" placeholder="Rechercher élève, matricule ou objet" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrer</button>
        </div>
        <div class="col-md-3 text-end">
          <a href="export_paiements_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-danger w-100" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> Exporter PDF
          </a>
        </div>
      </form>
    <!-- tableau des paiements -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-plus-circle"></i> Nouveau paiement
    </button>

    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Élève</th>
            <th>Classe</th>
            <th>Objet</th>
            <th>Montant</th>
            <th>Date</th>
            <th>Observations</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($paiements) === 0): ?>
            <tr><td colspan="8" class="text-center">Aucun paiement enregistré</td></tr>
          <?php else: ?>
            <?php foreach ($paiements as $i => $p): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?> (<?= htmlspecialchars($p['matricule']) ?>)</td>
                <td><?= htmlspecialchars($p['nom_classe']) ?> - <?= htmlspecialchars($p['nom_section']) ?> (<?= htmlspecialchars($p['annee_scolaire']) ?>)</td>
                <td><?= htmlspecialchars($p['objet']) ?></td>
                <td><?= number_format($p['montant_paye'], 2, ',', ' ') ?> FCFA</td>
                <td><?= date('d/m/Y H:i', strtotime($p['date_paiement'])) ?></td>
                <td><?= nl2br(htmlspecialchars($p['observations'])) ?></td>
                <td>
                  <a class="btn btn-sm btn-success" href="recu_autre_paiement.php?id=<?= $p['id'] ?>" target="_blank" title="Reçu"><i class="bi bi-receipt"></i></a>

                  <!-- delete trigger -->
                  <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $p['id'] ?>"><i class="bi bi-trash"></i></button>

                  <!-- delete modal -->
                  <div class="modal fade" id="deleteModal<?= $p['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                      <form method="POST" action="supprimer_autre_paiement.php" class="modal-content">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <div class="modal-header">
                          <h5 class="modal-title">Confirmer suppression</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          Supprimer le paiement de <strong><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></strong> ?
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                          <button type="submit" class="btn btn-danger">Supprimer</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Modal Ajout -->
    <div class="modal fade" id="addModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <form method="POST" action="ajouter_autre_paiement.php" class="modal-content" id="formAdd">
          <div class="modal-header">
            <h5 class="modal-title">Ajouter un autre paiement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-dark">Classe</label>
                <select name="id_classe" id="id_classe" class="form-select" required>
                  <option value="">-- choisir une classe --</option>
                  <?php foreach ($classes as $cl): ?>
                    <option value="<?= (int)$cl['id'] ?>"
                      data-section="<?= (int)$cl['id_section'] ?>"
                      data-annee="<?= (int)$cl['id_annee'] ?>">
                      <?= htmlspecialchars($cl['libelle'].' • '.$cl['nom_section'].' • '.$cl['nom_classe']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label text-dark">Élève</label>
                <select name="id_inscription" id="id_inscription" class="form-select" required>
                  <option value="">-- sélectionnez une classe d'abord --</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label text-dark">Objet</label>
                <input type="text" name="objet" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label text-dark">Montant (FCFA)</label>
                <input type="number" name="montant_paye" class="form-control" step="0.01" min="0.01" required>
              </div>

              <div class="col-12">
                <label class="form-label text-dark">Observations</label>
                <textarea name="observations" class="form-control" rows="3"></textarea>
              </div>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>

  </main>

  <script src="../../assets/js/bootstrap.bundle.min.js"></script>
  <script>
    // DOM ready - gestion de l'appel fetch pour récupérer les élèves selon la classe
    (function(){
      const idClasseEl = document.getElementById('id_classe');
      const idInscriptionEl = document.getElementById('id_inscription');

      function setLoading() {
        idInscriptionEl.innerHTML = '<option value="">Chargement...</option>';
      }
      function setEmpty() {
        idInscriptionEl.innerHTML = '<option value="">-- sélectionnez une classe d\'abord --</option>';
      }

      if (!idClasseEl) return;

      idClasseEl.addEventListener('change', function(){
        const idClasse = this.value;
        setLoading();
        if (!idClasse) { setEmpty(); return; }

        // l'URL : get_eleves.php doit être dans le même dossier
        fetch('./get_eleves.php?id_classe=' + encodeURIComponent(idClasse), {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        })
        .then(resp => {
          if (!resp.ok) throw new Error('Erreur réseau');
          return resp.json();
        })
        .then(data => {
          // data attendu: array d'objets {id, matricule, nom, prenom}
          idInscriptionEl.innerHTML = '<option value="">-- Choisir un élève --</option>';
          if (!Array.isArray(data) || data.length === 0) {
            idInscriptionEl.innerHTML = '<option value="">Aucun élève trouvé</option>';
            return;
          }
          data.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.id;
            opt.text = `${e.nom} ${e.prenom} (${e.matricule})`;
            idInscriptionEl.appendChild(opt);
          });
        })
        .catch(err => {
          console.error(err);
          idInscriptionEl.innerHTML = '<option value="">Erreur lors du chargement</option>';
        });
      });

      // clear form when modal opens
      const addModal = document.getElementById('addModal');
      if (addModal) {
        addModal.addEventListener('show.bs.modal', function(){
          document.getElementById('formAdd').reset();
          setEmpty();
        });
      }
    })();
  </script>



<!-- end content  -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalModifier = document.getElementById('modalModifierCycle');
    modalModifier.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const cycleId = button.getAttribute('data-id');
        const cycleNom = button.getAttribute('data-nom');

        modalModifier.querySelector('#edit_id').value = cycleId;
        modalModifier.querySelector('#edit_nom_cycle').value = cycleNom;
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalSupprimer = document.getElementById('modalSupprimerCycle');
    modalSupprimer.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const cycleId = button.getAttribute('data-id');

        modalSupprimer.querySelector('#delete_id').value = cycleId;
    });
});
  </script>
    <script>
          if (!Array.isArray(data) || data.length === 0) {
            idInscriptionEl.innerHTML = '<option value="">Aucun élève trouvé</option>';
            return;
          }
          data.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.id;
            opt.text = `${e.nom} ${e.prenom} (${e.matricule})`;
            idInscriptionEl.appendChild(opt);
          });
        });
        .catch(err => {
          console.error(err);
          idInscriptionEl.innerHTML = '<option value="">Erreur lors du chargement</option>';
        });
      });

      const addModal = document.getElementById('addModal');
      if (addModal) {
        addModal.addEventListener('show.bs.modal', function(){
          document.getElementById('formAdd').reset();
          setEmpty();
        });
      }
    })();
  </script>




<!-- end content  -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalModifier = document.getElementById('modalModifierCycle');
    modalModifier.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const cycleId = button.getAttribute('data-id');
        const cycleNom = button.getAttribute('data-nom');

        modalModifier.querySelector('#edit_id').value = cycleId;
        modalModifier.querySelector('#edit_nom_cycle').value = cycleNom;
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalSupprimer = document.getElementById('modalSupprimerCycle');
    modalSupprimer.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const cycleId = button.getAttribute('data-id');

        modalSupprimer.querySelector('#delete_id').value = cycleId;
    });
});
</script>

<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
