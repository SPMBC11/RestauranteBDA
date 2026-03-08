<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../lib/storage.php';
require __DIR__ . '/../lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

const PED_PATH = __DIR__ . '/../etc/pedidos.json';

enum RolesCocina: string
{
    case Cocinero = 'cocinero';
    case Admin = 'admin';
}

function ensureAuthCocina(): void
{
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'No autenticado']);
        exit;
    }
}

ensureAuthCocina();

$role = $_SESSION['user']['role'] ?? 'guest';
$method = $_SERVER['REQUEST_METHOD'];
$store = load_json(PED_PATH, []);

if ($method === 'GET') {
    if (!in_array($role, [RolesCocina::Cocinero->value, RolesCocina::Admin->value], true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Sin permiso']);
        exit;
    }
    echo json_encode(['ok' => true, 'items' => array_values($store)]);
    exit;
}

if ($method === 'POST') {
    if (!in_array($role, [RolesCocina::Cocinero->value, RolesCocina::Admin->value], true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Sin permiso']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = $payload['id'] ?? '';
    $nuevo = trim((string)($payload['estado'] ?? ''));
    if (!$id || !isset($store[$id])) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Pedido no encontrado']);
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

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
