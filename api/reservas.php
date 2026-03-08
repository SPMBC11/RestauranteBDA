<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../lib/storage.php';
require __DIR__ . '/../lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

const RES_PATH = __DIR__ . '/../etc/reservas.json';

function ensureAuth(): void
{
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'No autenticado']);
        exit;
    }
}

function ensureRole(array $allowed): void
{
    $role = $_SESSION['user']['role'] ?? 'guest';
    if (!in_array($role, $allowed, true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Sin permiso']);
        exit;
    }
}

ensureAuth();

$role = $_SESSION['user']['role'] ?? 'guest';
$userEmail = $_SESSION['user']['email'] ?? 'anon';

$method = $_SERVER['REQUEST_METHOD'];
$store = load_json(RES_PATH, []);

if ($method === 'GET') {
    if (in_array($role, ['maitre', 'admin'], true)) {
        echo json_encode(['ok' => true, 'items' => array_values($store)]);
        exit;
    }
    if ($role === 'cliente') {
        $own = array_values(array_filter($store, fn($r) => ($r['creadoPor'] ?? '') === $userEmail));
        echo json_encode(['ok' => true, 'items' => $own]);
        exit;
    }
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sin permiso']);
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $payload['action'] ?? 'create';

    if ($action === 'create') {
        if (!in_array($role, ['maitre', 'admin', 'cliente'], true)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Sin permiso']);
            exit;
        }
        $nombre = trim((string)($payload['nombre'] ?? ''));
        $fecha = trim((string)($payload['fecha'] ?? ''));
        $pax = max(1, (int)($payload['pax'] ?? 1));
        $notas = trim((string)($payload['notas'] ?? ''));
        if ($nombre === '' || $fecha === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Faltan datos obligatorios']);
            exit;
        }
        $id = uniqid('res_', true);
        $entry = [
            'id' => $id,
            'nombre' => $nombre,
            'fecha' => $fecha,
            'pax' => $pax,
            'notas' => $notas,
            'estado' => 'pendiente',
            'creadoPor' => $userEmail,
            'ts' => date('c'),
        ];
        $store[$id] = $entry;
        save_json(RES_PATH, $store);
        echo json_encode(['ok' => true, 'item' => $entry]);
        exit;
    }

    if ($action === 'estado') {
        if (!in_array($role, ['maitre', 'admin'], true)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Solo maitre/admin actualizan estados']);
            exit;
        }
        $id = $payload['id'] ?? '';
        $nuevo = $payload['estado'] ?? '';
        if (!$id || !isset($store[$id])) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Reserva no encontrada']);
            exit;
        }
        $store[$id]['estado'] = $nuevo ?: $store[$id]['estado'];
        $store[$id]['ts'] = date('c');
        save_json(RES_PATH, $store);
        echo json_encode(['ok' => true, 'item' => $store[$id]]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Acción inválida']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
