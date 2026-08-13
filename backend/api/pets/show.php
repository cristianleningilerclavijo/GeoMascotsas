<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Pet.php';

$ownerId = require_auth();
$pet = Pet::findByIdForOwner($routeParams['id'], $ownerId);

if (!$pet) {
    send_error('Mascota no encontrada', 404);
}

send_success(['pet' => $pet]);
