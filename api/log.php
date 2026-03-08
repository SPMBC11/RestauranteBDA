<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../lib/storage.php';
require __DIR__ . '/../lib/auth.php';

const LOG_PATH = __DIR__ . '/../etc/activity.json';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $data = load_json(LOG_PATH, []);
    $limit = 50;
    $slice = array_slice(array_reverse($data), 0, $limit);
    echo json_encode(['ok' => true, 'items' => $slice]);
    exit;
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true) ?: [];

    $section = trim((string)($payload['section'] ?? 'General'));
    $action = trim((string)($payload['action'] ?? 'Acción'));
    $meta = (string)($payload['meta'] ?? '');

    $userEmail = $_SESSION['user']['email'] ?? 'anon';
    $userRole = $_SESSION['user']['role'] ?? 'guest';

    $entry = [
        'ts' => date('c'),
        'section' => $section,
        'action' => $action,
        'meta' => $meta,
        'user' => $userEmail,
        'role' => $userRole,
    ];

    $data = load_json(LOG_PATH, []);
    $data[] = $entry;
    $max = 200;
    if (count($data) > $max) {
        $data = array_slice($data, -$max);
    }
    save_json(LOG_PATH, $data);

    echo json_encode(['ok' => true, 'entry' => $entry]);
    exit;
}

echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
