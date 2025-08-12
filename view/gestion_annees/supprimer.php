<?php
// Inclure la configuration de la base de données
require_once '../../config/db.php';

// Vérifier si l'ID de l'année est passé en paramètre
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $annee_id = (int)$_GET['id'];
    
    try {
        // Préparer la requête de suppression
        $query = "DELETE FROM annees_scolaires WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $annee_id, PDO::PARAM_INT);
        
        // Exécuter la suppression
        if ($stmt->execute()) {
            // Vérifier si une ligne a été affectée
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "Année scolaire supprimée avec succès!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Aucune année trouvée avec cet ID.";
                $_SESSION['message_type'] = "warning";
            }
        } else {
            $_SESSION['message'] = "Erreur lors de la suppression de l'année.";
            $_SESSION['message_type'] = "error";
        }
        
    } catch (PDOException $e) {
        // Gérer les erreurs de base de données
        if ($e->getCode() == '23000') {
            // Erreur de clé étrangère - l'année est référencée dans d'autres tables
            $_SESSION['message'] = "Impossible de supprimer cette année car elle est utilisée dans d'autres enregistrements.";
            $_SESSION['message_type'] = "error";
        } else {
            $_SESSION['message'] = "Erreur de base de données: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
    }
    
    // Rediriger vers la page de gestion des années
    header("Location: index.php");
    exit();
    
} else {
    // Si aucun ID n'est fourni
    $_SESSION['message'] = "ID de l'année non fourni ou invalide.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit();
}
?>
