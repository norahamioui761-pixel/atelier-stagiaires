<?php

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/db.php';

use Cloudinary\Cloudinary;

function repondre(int $code, array $data): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Seule la méthode POST est acceptée
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(405, ['success' => false, 'message' => 'Méthode non autorisée.']);
}

// 2. Récupérer et vérifier les champs texte
$nom    = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$ville  = trim($_POST['ville'] ?? '');

if ($nom === '' || $prenom === '' || $ville === '') {
    repondre(400, ['success' => false, 'message' => 'Nom, prénom et ville sont obligatoires.']);
}

if (mb_strlen($nom) > 100 || mb_strlen($prenom) > 100 || mb_strlen($ville) > 100) {
    repondre(400, ['success' => false, 'message' => 'Chaque champ doit faire 100 caractères maximum.']);
}

// 3. Vérifier le fichier image
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    repondre(400, ['success' => false, 'message' => 'Veuillez choisir une photo valide.']);
}

$tmpPath = $_FILES['photo']['tmp_name'];

if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
    repondre(400, ['success' => false, 'message' => 'La photo ne doit pas dépasser 5 Mo.']);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($tmpPath);
$typesAutorises = ['image/jpeg', 'image/png', 'image/webp'];

if (!in_array($mime, $typesAutorises, true)) {
    repondre(400, ['success' => false, 'message' => 'Format accepté : JPG, PNG ou WEBP.']);
}

try {
    // 4. Envoyer la photo vers Cloudinary
    $cloudinary = new Cloudinary(getenv('CLOUDINARY_URL'));
    $resultat = $cloudinary->uploadApi()->upload($tmpPath, [
        'folder' => 'stagiaires',
    ]);

    // 5. Récupérer l'URL sécurisée
    $photoUrl = $resultat['secure_url'];

    // 6. Enregistrer dans MySQL
    $pdo = getDatabase();
    $stmt = $pdo->prepare(
        'INSERT INTO stagiaires (nom, prenom, ville, photo) VALUES (:nom, :prenom, :ville, :photo)'
    );
    $stmt->execute([
        ':nom'    => $nom,
        ':prenom' => $prenom,
        ':ville'  => $ville,
        ':photo'  => $photoUrl,
    ]);

    // 7. Réponse JSON
    repondre(201, [
        'success' => true,
        'message' => 'Stagiaire inscrit avec succès.',
        'photo'   => $photoUrl,
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    repondre(500, ['success' => false, 'message' => 'Erreur serveur. Réessayez plus tard.']);
}
