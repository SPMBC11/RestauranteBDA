<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../lib/storage.php';
require __DIR__ . '/../lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

const PED_PATH = __DIR__ . '/../etc/pedidos.json';

enum Roles: string
{
    case Mesero = 'mesero';
    case Admin = 'admin';
}

function ensureAuthPedidos(): void
{
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'No autenticado']);
        exit;
    }
}

ensureAuthPedidos();

$role = $_SESSION['user']['role'] ?? 'guest';
$userEmail = $_SESSION['user']['email'] ?? 'anon';
$method = $_SERVER['REQUEST_METHOD'];
$store = load_json(PED_PATH, []);

if ($method === 'GET') {
    if ($role === Roles::Admin->value) {
        echo json_encode(['ok' => true, 'items' => array_values($store)]);
        exit;
    }
    if ($role === Roles::Mesero->value) {
        $own = array_values(array_filter($store, fn($p) => ($p['asignadoA'] ?? '') === $userEmail));
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

    if (!in_array($role, [Roles::Mesero->value, Roles::Admin->value], true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Sin permiso']);
        exit;
    }

    if ($action === 'create') {
        $mesa = trim((string)($payload['mesa'] ?? ''));
        $items = trim((string)($payload['items'] ?? ''));
        $total = (float)($payload['total'] ?? 0);
        if ($mesa === '' || $items === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Faltan datos obligatorios']);
            exit;
        }
        $id = uniqid('ped_', true);
        $entry = [
            'id' => $id,
            'mesa' => $mesa,
            'items' => $items,
            'total' => $total,
            'estado' => 'enviado',
            'asignadoA' => $userEmail,
            'creadoPor' => $userEmail,
            'ts' => date('c'),
        ];
        $store[$id] = $entry;
        save_json(PED_PATH, $store);
        echo json_encode(['ok' => true, 'item' => $entry]);
        exit;
    }

    if ($action === 'estado') {
        $id = $payload['id'] ?? '';
        $nuevo = trim((string)($payload['estado'] ?? ''));
        if (!$id || !isset($store[$id])) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Pedido no encontrado']);
            exit;
        }
        if ($role === Roles::Mesero->value && ($store[$id]['asignadoA'] ?? '') !== $userEmail) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Solo el mesero asignado puede actualizar']);
            exit;
        }
        if ($nuevo !== '') {
            $store[$id]['estado'] = $nuevo;
        }
        $store[$id]['ts'] = date('c');
        save_json(PED_PATH, $store);
        echo json_encode(['ok' => true, 'item' => $store[$id]]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Acción inválida']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
