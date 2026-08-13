<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Pet.php';
require_once __DIR__ . '/../../models/QrToken.php';

$ownerId = require_auth();
$petId = $routeParams['id'];

if (!Pet::findByIdForOwner($petId, $ownerId)) {
    send_error('Mascota no encontrada', 404);
}

$data = read_json_body();
$newToken = trim($data['qr_token'] ?? '');

if (!preg_match('/^[0-9a-fA-F-]{36}$/', $newToken)) {
    send_error('qr_token inválido (se espera un UUID de 36 caracteres)', 400);
}

if (QrToken::tokenExists($newToken)) {
    send_error('El token ya existe, genera uno nuevo e intenta de nuevo', 409);
}

QrToken::deactivateAllForPet($petId);
QrToken::create($petId, $newToken);

send_success(['pet' => Pet::findByIdForOwner($petId, $ownerId)]);
