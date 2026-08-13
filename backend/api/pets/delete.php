<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Pet.php';

$ownerId = require_auth();
$petId = $routeParams['id'];

if (!Pet::delete($petId, $ownerId)) {
    send_error('Mascota no encontrada', 404);
}

send_success(['deleted_id' => $petId]);
