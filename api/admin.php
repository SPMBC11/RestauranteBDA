<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

function ensureAdmin(): void
{
    if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Solo admin']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
ensureAdmin();

if ($method === 'GET') {
    $users = userStore();
    $list = [];
    foreach ($users as $email => $data) {
        $list[] = [
            'email' => $email,
            'name' => $data['name'] ?? '',
            'role' => $data['role'] ?? 'cliente',
        ];
    }
    echo json_encode(['ok' => true, 'items' => $list]);
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $payload['action'] ?? '';
    if ($action === 'setRole') {
        $email = strtolower(trim((string)($payload['email'] ?? '')));
        $role = trim((string)($payload['role'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $role === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Datos inválidos']);
            exit;
        }
        $users = &userStore();
        if (!isset($users[$email])) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }
        $users[$email]['role'] = $role;
        saveUsers($users);
        echo json_encode(['ok' => true, 'user' => ['email' => $email, 'name' => $users[$email]['name'], 'role' => $role]]);
        exit;
    }
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Acción inválida']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
