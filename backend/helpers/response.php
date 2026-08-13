<?php

function resolve_format(): string
{
    $format = strtolower($_GET['format'] ?? '');
    if ($format === 'xml' || $format === 'json') {
        return $format;
    }

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (stripos($accept, 'xml') !== false && stripos($accept, 'json') === false) {
        return 'xml';
    }

    return 'json';
}

function array_to_xml(array $data, SimpleXMLElement $xml): void
{
    foreach ($data as $key => $value) {
        if (is_int($key)) {
            $key = 'item';
        }
        $key = preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $key);

        if (is_array($value)) {
            $child = $xml->addChild($key);
            array_to_xml($value, $child);
        } else {
            $xml->addChild($key, htmlspecialchars((string) ($value ?? ''), ENT_XML1));
        }
    }
}

function send_response($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    $format = resolve_format();

    if ($format === 'xml') {
        header('Content-Type: application/xml; charset=utf-8');
        $xml = new SimpleXMLElement('<response/>');
        array_to_xml($data, $xml);
        echo $xml->asXML();
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    exit;
}

function send_success($data, int $statusCode = 200): void
{
    send_response(['success' => true, 'data' => $data, 'error' => null], $statusCode);
}

function send_error(string $message, int $statusCode = 400): void
{
    send_response(['success' => false, 'data' => null, 'error' => $message], $statusCode);
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}
