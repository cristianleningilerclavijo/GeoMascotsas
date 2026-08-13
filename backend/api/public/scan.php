<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../models/Pet.php';
require_once __DIR__ . '/../../models/ScanLog.php';

$data = read_json_body();
$token = trim($data['token'] ?? '');

$pet = Pet::findPublicByToken($token);

if (!$pet) {
    send_error('Token no encontrado o inactivo', 404);
}

ScanLog::create(
    (int) $pet['qr_token_id'],
    (int) $pet['id'],
    $_SERVER['REMOTE_ADDR'] ?? '',
    $_SERVER['HTTP_USER_AGENT'] ?? ''
);

send_success(['logged' => true], 201);
