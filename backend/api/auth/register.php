<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../models/Owner.php';

start_session_if_needed();

$data = read_json_body();
$fullName = trim($data['full_name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$phone = trim($data['phone'] ?? '');

if ($fullName === '' || $email === '' || $password === '' || $phone === '') {
    send_error('Todos los campos son obligatorios', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_error('Email inválido', 400);
}

if (strlen($password) < 6) {
    send_error('La contraseña debe tener al menos 6 caracteres', 400);
}

if (Owner::findByEmail($email)) {
    send_error('Ese email ya está registrado', 409);
}

$ownerId = Owner::create($fullName, $email, password_hash($password, PASSWORD_DEFAULT), $phone);
$_SESSION['owner_id'] = $ownerId;

send_success(['owner' => Owner::findById($ownerId)], 201);
