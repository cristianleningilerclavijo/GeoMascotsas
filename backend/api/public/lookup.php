<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../models/Pet.php';

$token = $routeParams['token'];
$pet = Pet::findPublicByToken($token);

if (!$pet) {
    send_error('Token no encontrado o inactivo', 404);
}

send_success([
    'pet' => [
        'id' => (int) $pet['id'],
        'name' => $pet['name'],
        'species' => $pet['species'],
        'breed' => $pet['breed'],
        'color' => $pet['color'],
        'photo_url' => $pet['photo_url'],
        'medical_notes' => $pet['medical_notes'],
        'status' => $pet['status'],
        'qr_token' => $pet['qr_token'],
    ],
    'owner_contact' => [
        'full_name' => $pet['owner_full_name'],
        'phone' => $pet['owner_phone'],
    ],
]);
