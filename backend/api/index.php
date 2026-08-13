<?php

require_once __DIR__ . '/../helpers/response.php';

function dispatch(string $method, array $segments): void
{
    // $routeParams queda disponible dentro de los archivos incluidos más abajo:
    // un require() ejecutado dentro de esta función comparte su ámbito local.
    $routeParams = [];

    if ($segments === ['auth', 'register'] && $method === 'POST') {
        require __DIR__ . '/auth/register.php';
        return;
    }
    if ($segments === ['auth', 'login'] && $method === 'POST') {
        require __DIR__ . '/auth/login.php';
        return;
    }
    if ($segments === ['auth', 'logout'] && $method === 'POST') {
        require __DIR__ . '/auth/logout.php';
        return;
    }

    if (count($segments) === 3 && $segments[0] === 'public' && $segments[1] === 'pets' && $method === 'GET') {
        $routeParams = ['token' => $segments[2]];
        require __DIR__ . '/public/lookup.php';
        return;
    }
    if ($segments === ['public', 'scans'] && $method === 'POST') {
        require __DIR__ . '/public/scan.php';
        return;
    }

    if ($segments === ['pets'] && $method === 'GET') {
        require __DIR__ . '/pets/list.php';
        return;
    }
    if ($segments === ['pets'] && $method === 'POST') {
        require __DIR__ . '/pets/create.php';
        return;
    }
    if (count($segments) === 2 && $segments[0] === 'pets' && ctype_digit($segments[1])) {
        $routeParams = ['id' => (int) $segments[1]];
        if ($method === 'GET') {
            require __DIR__ . '/pets/show.php';
            return;
        }
        if ($method === 'PUT') {
            require __DIR__ . '/pets/update.php';
            return;
        }
        if ($method === 'DELETE') {
            require __DIR__ . '/pets/delete.php';
            return;
        }
    }
    if (count($segments) === 3 && $segments[0] === 'pets' && ctype_digit($segments[1]) && $method === 'POST') {
        $routeParams = ['id' => (int) $segments[1]];
        if ($segments[2] === 'qr') {
            require __DIR__ . '/pets/qr.php';
            return;
        }
    }
    if (count($segments) === 3 && $segments[0] === 'pets' && ctype_digit($segments[1]) && $segments[2] === 'scans' && $method === 'GET') {
        $routeParams = ['id' => (int) $segments[1]];
        require __DIR__ . '/pets/scans.php';
        return;
    }

    send_error('Ruta no encontrada: ' . $method . ' /' . implode('/', $segments), 404);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$marker = '/backend/api';
$pos = strpos($uri, $marker);
$path = $pos !== false ? substr($uri, $pos + strlen($marker)) : $uri;
$path = trim($path, '/');
$segments = $path === '' ? [] : explode('/', $path);

dispatch($_SERVER['REQUEST_METHOD'], $segments);
