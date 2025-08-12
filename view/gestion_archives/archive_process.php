<?php
session_start();
require_once '../../config/db.php';
require_once 'archive_functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Vérifier méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Méthode non autorisée',
        'method_received' => $_SERVER['REQUEST_METHOD']
    ]);
    exit();
}

// Vérifier l'accès utilisateur
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Accès non autorisé'
    ]);
    exit();
}

try {
    $archive_name = trim($_POST['archive_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($archive_name)) {
        throw new Exception('Le nom de l\'archive est requis');
    }

    $selected_classes = $_POST['classes'] ?? [];
    $selected_cycles = $_POST['cycles'] ?? [];
    $selected_tranches = $_POST['tranches'] ?? [];
    $selected_users = $_POST['utilisateurs'] ?? [];

    $archive_data = [
        'classes' => [],
        'cycles' => [],
        'tranches' => [],
        'utilisateurs' => []
    ];

    // Fonction pour récupérer les données depuis la base avec placeholders
    function fetchData(PDO $pdo, string $table, array $ids, string $extraCondition = '') {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM $table WHERE id IN ($placeholders)";
        if ($extraCondition) {
            $sql .= " AND $extraCondition";
        }
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute($ids)) {
            throw new Exception("Erreur lors de la récupération des données pour la table $table");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Charger les données sélectionnées
    $archive_data['classes'] = fetchData($pdo, 'classes', $selected_classes);
    $archive_data['cycles'] = fetchData($pdo, 'cycles', $selected_cycles);
    $archive_data['tranches'] = fetchData($pdo, 'tranches', $selected_tranches);
    $archive_data['utilisateurs'] = fetchData($pdo, 'utilisateurs', $selected_users, "role != 'superadmin'");

    // Vérifier au moins une donnée
    if (empty($archive_data['classes']) && empty($archive_data['cycles']) && empty($archive_data['tranches']) && empty($archive_data['utilisateurs'])) {
        throw new Exception('Aucune donnée sélectionnée ou données introuvables');
    }

    // Générer le PDF (fonction à implémenter dans archive_functions.php)
    $pdf_filename = createArchivePDF($archive_data, $archive_name);

    if (!$pdf_filename || !file_exists($pdf_filename)) {
        throw new Exception('Le fichier PDF n\'a pas été créé ou est introuvable.');
    }

    // Enregistrer l'archive dans la base
    $stmt = $pdo->prepare("
        INSERT INTO archives (nom, description, fichier, date_creation, created_by) 
        VALUES (?, ?, ?, NOW(), ?)
    ");
    $success = $stmt->execute([
        $archive_name,
        $description,
        basename($pdf_filename),
        $_SESSION['user_id']
    ]);
    if (!$success) {
        $errorInfo = $stmt->errorInfo();
        throw new Exception("Erreur lors de l'insertion en base : " . $errorInfo[2]);
    }

    // Réponse succès
    echo json_encode([
        'success' => true,
        'message' => 'Archive créée avec succès',
        'filename' => basename($pdf_filename),
        'path' => $pdf_filename
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
