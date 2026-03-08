<?php
declare(strict_types=1);

const USERS_PATH = __DIR__ . '/../etc/users.json';

function seedUsers(): array
{
    return [
        'maitre@resto.com' => [
            'name' => 'Maitre Demo',
            'role' => 'maitre',
            'password' => password_hash('demo1234', PASSWORD_DEFAULT),
        ],
        'mesero@resto.com' => [
            'name' => 'Mesero Demo',
            'role' => 'mesero',
            'password' => password_hash('demo1234', PASSWORD_DEFAULT),
        ],
        'cocinero@resto.com' => [
            'name' => 'Cocinero Demo',
            'role' => 'cocinero',
            'password' => password_hash('demo1234', PASSWORD_DEFAULT),
        ],
        'admin@resto.com' => [
            'name' => 'Admin Demo',
            'role' => 'admin',
            'password' => password_hash('demo1234', PASSWORD_DEFAULT),
        ],
        'cliente@resto.com' => [
            'name' => 'Cliente Demo',
            'role' => 'cliente',
            'password' => password_hash('demo1234', PASSWORD_DEFAULT),
        ],
    ];
}

function loadUsers(): array
{
    if (!file_exists(USERS_PATH)) {
        $seed = seedUsers();
        file_put_contents(USERS_PATH, json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $seed;
    }
    $raw = file_get_contents(USERS_PATH);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : seedUsers();
}

function saveUsers(array $users): void
{
    file_put_contents(USERS_PATH, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function &userStore(): array
{
    static $cache;
    if ($cache === null) {
        $cache = loadUsers();
    }
    return $cache;
}

function findUser(string $email): ?array
{
    $users = userStore();
    $key = strtolower(trim($email));
    return $users[$key] ?? null;
}

function authenticate(string $email, string $password): ?array
{
    $user = findUser($email);
    if (!$user) return null;
    return password_verify($password, $user['password']) ? $user + ['email' => strtolower(trim($email))] : null;
}

function registerUser(string $name, string $email, string $role, string $password, int $minLength): array
{
    $emailKey = strtolower(trim($email));
    if (!filter_var($emailKey, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Correo inválido'];
    }
    if (strlen($password) < $minLength) {
        return ['ok' => false, 'message' => 'Contraseña demasiado corta'];
    }
    $store = &userStore();
    if (isset($store[$emailKey])) {
        return ['ok' => false, 'message' => 'Ya existe un usuario con ese correo'];
    }
    $store[$emailKey] = [
        'name' => trim($name) ?: 'Usuario',
        'role' => $role,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ];
    saveUsers($store);
    return ['ok' => true, 'user' => $store[$emailKey] + ['email' => $emailKey]];
}