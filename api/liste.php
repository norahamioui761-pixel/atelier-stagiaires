<?php
require_once dirname(__DIR__) . '/config/db.php';

header('Content-Type: application/json');

try {
    $pdo = getDatabase();
    $stmt = $pdo->query("SELECT * FROM stagiaires ORDER BY id DESC");
    $stagiaires = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $stagiaires]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}