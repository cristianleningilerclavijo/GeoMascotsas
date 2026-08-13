<?php

require_once __DIR__ . '/response.php';

function start_session_if_needed(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function require_auth(): int
{
    start_session_if_needed();

    if (empty($_SESSION['owner_id'])) {
        send_error('No autenticado', 401);
    }

    return (int) $_SESSION['owner_id'];
}
