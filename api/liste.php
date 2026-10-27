<?php
require_once dirname(__DIR__) . '/config/db.php';

header('Content-Type: application/json');

try {
    $conn = getDatabase();
    
    // جلب photo باسم photo_url لتتوافق مع الصفحة
    $result = $conn->query("SELECT id, nom, prenom, ville, photo AS photo_url FROM stagiaires ORDER BY id DESC");
    
    if (!$result) {
        throw new Exception($conn->error);
    }

    $stagiaires = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $stagiaires]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
