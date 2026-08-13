<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Owner.php';

start_session_if_needed();

$data = read_json_body();
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

$owner = Owner::findByEmail($email);

if (!$owner || !password_verify($password, $owner['password_hash'])) {
    send_error('Credenciales inválidas', 401);
}

$_SESSION['owner_id'] = (int) $owner['id'];

send_success(['owner' => Owner::findById((int) $owner['id'])]);
