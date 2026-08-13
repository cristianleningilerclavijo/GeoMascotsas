<?php

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/../config/config.php';

function save_pet_photo(array $file): string
{
    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);

    if (!isset($allowedMimes[$mime])) {
        send_error('Formato de imagen no soportado (usa JPG, PNG o WEBP)', 400);
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        send_error('La imagen supera el límite de 5MB', 400);
    }

    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0775, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedMimes[$mime];

    if (!move_uploaded_file($file['tmp_name'], UPLOADS_DIR . $filename)) {
        send_error('No se pudo guardar la imagen', 500);
    }

    return $filename;
}
