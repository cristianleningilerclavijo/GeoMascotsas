<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/uploads.php';
require_once __DIR__ . '/../../models/Pet.php';
require_once __DIR__ . '/../../models/QrToken.php';

$ownerId = require_auth();

$name = trim($_POST['name'] ?? '');
$qrToken = trim($_POST['qr_token'] ?? '');

if ($name === '') {
    send_error('El nombre de la mascota es obligatorio', 400);
}

if (!preg_match('/^[0-9a-fA-F-]{36}$/', $qrToken)) {
    send_error('qr_token inválido (se espera un UUID de 36 caracteres)', 400);
}

if (QrToken::tokenExists($qrToken)) {
    send_error('El token ya existe, genera uno nuevo e intenta de nuevo', 409);
}

$validSpecies = ['perro', 'gato', 'otro'];
$species = in_array($_POST['species'] ?? '', $validSpecies, true) ? $_POST['species'] : 'perro';

$photoUrl = null;
if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $photoUrl = save_pet_photo($_FILES['photo']);
}

$pet = Pet::create($ownerId, [
    'name' => $name,
    'species' => $species,
    'breed' => $_POST['breed'] ?? null,
    'color' => $_POST['color'] ?? null,
    'medical_notes' => $_POST['medical_notes'] ?? null,
    'photo_url' => $photoUrl,
], $qrToken);

send_success(['pet' => $pet], 201);
