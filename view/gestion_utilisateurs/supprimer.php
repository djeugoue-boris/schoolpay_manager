<?php
require_once '../../config/db.php';
session_start();

// Vérifier si l'ID est fourni
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "Erreur : ID utilisateur manquant.";
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Empêcher la suppression du superadmin (ID 1)
if ($id == 1) {
    $_SESSION['message'] = "Erreur : Impossible de supprimer le super administrateur.";
    header('Location: index.php');
    exit;
}

// Vérifier si l'utilisateur existe
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    $_SESSION['message'] = "Erreur : Utilisateur introuvable.";
    header('Location: index.php');
    exit;
}

// Traitement de la suppression
if (isset($_POST['confirm'])) {
    // Supprimer l'utilisateur
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['message'] = "Utilisateur supprimé avec succès.";
    header('Location: index.php');
    exit;
}

// Afficher la page de confirmation
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmer la suppression - SchoolPay</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Confirmer la suppression</h3>
                    </div>
                    <div class="card-body">
                        <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <strong><?php echo htmlspecialchars($utilisateur['identifiant']); ?></strong> ?</p>
                        
                        <form method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" name="confirm" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Confirmer la suppression
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
