<?php
// Connexion à la base de données via PDO
require_once '../../config/db.php'; // Ce fichier doit créer une instance $pdo de PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifie que tous les champs nécessaires sont présents
    if (
        isset($_POST['id'], $_POST['nom_classe'], $_POST['frais_scolarite'], $_POST['nombre_tranches'],
              $_POST['id_cycle'], $_POST['id_section'], $_POST['id_annee'])
    ) {
        $id = intval($_POST['id']);
        $nom_classe = trim($_POST['nom_classe']);
        $frais_scolarite = floatval($_POST['frais_scolarite']);
        $nombre_tranches = intval($_POST['nombre_tranches']);
        $id_cycle = intval($_POST['id_cycle']);
        $id_section = intval($_POST['id_section']);
        $id_annee = intval($_POST['id_annee']);

        try {
            // Prépare la requête SQL avec PDO
            $sql = "UPDATE classes 
                    SET nom_classe = :nom_classe, 
                        frais_scolarite = :frais_scolarite, 
                        nombre_tranches = :nombre_tranches, 
                        id_cycle = :id_cycle, 
                        id_section = :id_section, 
                        id_annee = :id_annee
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            // Bind des paramètres
            $stmt->bindParam(':nom_classe', $nom_classe);
            $stmt->bindParam(':frais_scolarite', $frais_scolarite);
            $stmt->bindParam(':nombre_tranches', $nombre_tranches, PDO::PARAM_INT);
            $stmt->bindParam(':id_cycle', $id_cycle, PDO::PARAM_INT);
            $stmt->bindParam(':id_section', $id_section, PDO::PARAM_INT);
            $stmt->bindParam(':id_annee', $id_annee, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Exécution
            if ($stmt->execute()) {
                header('Location: index.php?success=1');
                exit();
            } else {
                 header('Location: index.php?erreurr=1');
                exit();

            }
        } catch (PDOException $e) {
            echo "Erreur PDO : " . $e->getMessage();
        }
    } else {
        echo "Tous les champs sont requis.";
    }
} else {
    echo "Requête invalide.";
}
?>
