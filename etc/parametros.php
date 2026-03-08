<?php
return [
    'app_name' => 'Restaurante BDA',
    'entorno' => 'desarrollo',
    'db' => [
        'host' => 'localhost',
        'name' => 'restaurante',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'seguridad' => [
        'min_pass_length' => 8,
        'token_ttl_minutes' => 30,
    ],
];
