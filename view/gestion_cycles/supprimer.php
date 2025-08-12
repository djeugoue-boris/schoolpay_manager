<?php
// supprimer.php

// 1. Connexion à la base de données
require_once '../../config/db.php'; // Chemin à adapter



// 2. Vérifier l'ID et le token CSRF si vous en utilisez
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php?error=ID invalide");
    exit();
}

// 3. Vérification que l'élément existe avant suppression
try {
    $stmt = $pdo->prepare("SELECT id FROM cycles WHERE id = ?");
    $stmt->execute([$id]);
    
    if (!$stmt->fetch()) {
        header("Location: index.php?error=Cycle non trouvé");
        exit();
    }
} catch (PDOException $e) {
    die("Erreur de vérification : " . $e->getMessage());
}

// 4. Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM cycles WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?message=Cycle supprimé avec succès");
        exit();
    } catch (PDOException $e) {
        header("Location: index.php?error=Erreur lors de la suppression");
        exit();
    }
}

// 5. Sinon, afficher la page de confirmation
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

    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3>Confirmer la suppression</h3>
            </div>
            <div class="card-body">
                <p class="card-text text-info">Êtes-vous sûr de vouloir supprimer définitivement ce cycle ?</p>
                <p class="text-muted">Cette action est irréversible.</p>
                
                <form method="POST">
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
<script src="../../assets/js/theme.js"></script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

