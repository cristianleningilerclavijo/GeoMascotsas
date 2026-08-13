<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Pet.php';

$ownerId = require_auth();
$petId = $routeParams['id'];

$data = read_json_body();
$allowedFields = ['name', 'species', 'breed', 'color', 'medical_notes', 'status'];
$fieldsToUpdate = array_intersect_key($data, array_flip($allowedFields));

if (empty($fieldsToUpdate)) {
    send_error('No hay campos válidos para actualizar', 400);
}

if (!Pet::update($petId, $ownerId, $fieldsToUpdate)) {
    send_error('Mascota no encontrada', 404);
}

send_success(['pet' => Pet::findByIdForOwner($petId, $ownerId)]);
