<?php
declare(strict_types=1);

function obtenerMenusRestaurante(): array
{
    return [
        'principal' => [
            'icon' => '🏠',
            'short' => 'Principal',
            'title' => 'Menú principal',
            'badge' => 'Vista general',
            'cards' => [
                [
                    'title' => 'Reservas en curso',
                    'meta' => 'Revisa disponibilidad y asigna mesas.',
                    'actions' => [
                        ['label' => 'Calendario y slots', 'tag' => 'Próximamente API', 'meta' => 'Disponibilidad por bloque'],
                        ['label' => 'Asignación rápida', 'tag' => 'Drag & Drop'],
                        ['label' => 'Alertas de retraso', 'tag' => 'Notificaciones'],
                    ],
                ],
                [
                    'title' => 'Mesas activas',
                    'meta' => 'Estado de servicio por mesa.',
                    'actions' => [
                        ['label' => 'Mapa de sala', 'tag' => 'Grid responsivo'],
                        ['label' => 'Cuenta en curso', 'tag' => 'Consumos vivos'],
                        ['label' => 'Solicitudes pendientes', 'tag' => 'Timbre digital'],
                    ],
                ],
                [
                    'title' => 'Órdenes de cocina',
                    'meta' => 'Preparaciones, pases y tiempos.',
                    'actions' => [
                        ['label' => 'Ticket digital', 'tag' => 'Prioridades'],
                        ['label' => 'Pasar a barra', 'tag' => 'Flujo caliente'],
                        ['label' => 'Histórico', 'tag' => 'Auditoría'],
                    ],
                ],
                [
                    'title' => 'Panel administrativo',
                    'meta' => 'Indicadores, costos y usuarios.',
                    'actions' => [
                        ['label' => 'KPIs diarios', 'tag' => 'Tickets/rotación'],
                        ['label' => 'Inventario', 'tag' => 'Reposiciones'],
                        ['label' => 'Roles y accesos', 'tag' => 'Seguridad'],
                    ],
                ],
            ],
        ],
        'maitre' => [
            'icon' => '🛎️',
            'short' => 'Maitre',
            'title' => 'Menú · Maitre',
            'badge' => 'Salón y reservas',
            'cards' => [
                [
                    'title' => 'Reservas',
                    'meta' => 'Crear, editar y confirmar reservas.',
                    'actions' => [
                        ['label' => 'Agenda visual', 'tag' => 'Horas pico'],
                        ['label' => 'Preasignar mesa', 'tag' => 'Capacidad'],
                        ['label' => 'Notas especiales', 'tag' => 'Alergias'],
                    ],
                ],
                [
                    'title' => 'Control de sala',
                    'meta' => 'Seguimiento en vivo de mesas.',
                    'actions' => [
                        ['label' => 'Mapa de ocupación', 'tag' => 'Color por estado'],
                        ['label' => 'Cambio de mesero', 'tag' => 'Cobertura'],
                        ['label' => 'Alertas de espera', 'tag' => 'Tiempo en fila'],
                    ],
                ],
                [
                    'title' => 'Lista de espera',
                    'meta' => 'Gestiona turnos y notificaciones.',
                    'actions' => [
                        ['label' => 'Check-in por QR', 'tag' => 'Autoatención'],
                        ['label' => 'SMS/Email', 'tag' => 'Aviso de mesa lista'],
                        ['label' => 'Estimados dinámicos', 'tag' => 'Algoritmo'],
                    ],
                ],
            ],
        ],
        'mesero' => [
            'icon' => '🧾',
            'short' => 'Mesero',
            'title' => 'Menú · Mesero',
            'badge' => 'Mesas y pedidos',
            'cards' => [
                [
                    'title' => 'Asignaciones',
                    'meta' => 'Mesas a cargo y cobertura.',
                    'actions' => [
                        ['label' => 'Transferir mesa', 'tag' => 'Autorización'],
                        ['label' => 'Bloques de turno', 'tag' => 'Rotación'],
                        ['label' => 'Alertas de llamados', 'tag' => 'Campanilla'],
                    ],
                ],
                [
                    'title' => 'Toma de pedidos',
                    'meta' => 'Carga rápida y modificadores.',
                    'actions' => [
                        ['label' => 'Menú digital', 'tag' => 'Categorías'],
                        ['label' => 'Notas a cocina', 'tag' => 'Sin gluten'],
                        ['label' => 'Upselling sugerido', 'tag' => 'Maridajes'],
                    ],
                ],
                [
                    'title' => 'Cuentas y pagos',
                    'meta' => 'Control de cuenta y cierres.',
                    'actions' => [
                        ['label' => 'Dividir cuenta', 'tag' => 'Por ítem'],
                        ['label' => 'Propinas sugeridas', 'tag' => 'Configurables'],
                        ['label' => 'Pago mixto', 'tag' => 'Tarjeta/efectivo'],
                    ],
                ],
            ],
        ],
        'cocinero' => [
            'icon' => '👨‍🍳',
            'short' => 'Cocinero',
            'title' => 'Menú · Cocinero',
            'badge' => 'Órdenes y pases',
            'cards' => [
                [
                    'title' => 'Tickets de cocina',
                    'meta' => 'Cola de órdenes por prioridad.',
                    'actions' => [
                        ['label' => 'Prioridad visual', 'tag' => 'Color + tiempo'],
                        ['label' => 'Modificadores', 'tag' => 'Alergias'],
                        ['label' => 'Reasignar estación', 'tag' => 'Caliente/frío'],
                    ],
                ],
                [
                    'title' => 'Pase a sala',
                    'meta' => 'Listo para servir y retiros.',
                    'actions' => [
                        ['label' => 'Marcar listo', 'tag' => 'Notifica mesero'],
                        ['label' => 'Consolidar bandejas', 'tag' => 'Por mesa'],
                        ['label' => 'Control de devoluciones', 'tag' => 'Motivos'],
                    ],
                ],
                [
                    'title' => 'Inventario rápido',
                    'meta' => 'Insumos críticos y mermas.',
                    'actions' => [
                        ['label' => 'Alertas de stock', 'tag' => 'Umbrales'],
                        ['label' => 'Registro de merma', 'tag' => 'Motivo'],
                        ['label' => 'Requerimientos a compras', 'tag' => 'Solicitud'],
                    ],
                ],
            ],
        ],
        'admin' => [
            'icon' => '⚙️',
            'short' => 'Administrador',
            'title' => 'Menú · Administrador',
            'badge' => 'Operación y negocio',
            'cards' => [
                [
                    'title' => 'Usuarios y roles',
                    'meta' => 'Permisos y accesos granulares.',
                    'actions' => [
                        ['label' => 'Alta/Baja/Edición', 'tag' => 'Auditable'],
                        ['label' => 'Perfiles por área', 'tag' => 'Principio mínimo'],
                        ['label' => 'Bitácora de actividad', 'tag' => 'Trazabilidad'],
                    ],
                ],
                [
                    'title' => 'Finanzas',
                    'meta' => 'Ventas, costos y cierres.',
                    'actions' => [
                        ['label' => 'Arqueo de caja', 'tag' => 'Turno'],
                        ['label' => 'Costeo de recetas', 'tag' => 'Margen'],
                        ['label' => 'Reportes diarios', 'tag' => 'Exportables'],
                    ],
                ],
                [
                    'title' => 'Menú y precios',
                    'meta' => 'Gestión central de carta.',
                    'actions' => [
                        ['label' => 'Versionado de carta', 'tag' => 'Histórico'],
                        ['label' => 'Promociones', 'tag' => 'Rangos horarios'],
                        ['label' => 'Disponibilidad por día', 'tag' => '86 por día'],
                    ],
                ],
            ],
        ],
        'cliente' => [
            'icon' => '🧑‍🧑‍🧒',
            'short' => 'Cliente',
            'title' => 'Menú · Cliente',
            'badge' => 'Autogestión',
            'cards' => [
                [
                    'title' => 'Reserva en línea',
                    'meta' => 'Solicitar y confirmar horario.',
                    'actions' => [
                        ['label' => 'Selección de mesa', 'tag' => 'Preferencias'],
                        ['label' => 'Pre-orden opcional', 'tag' => 'Acelera servicio'],
                        ['label' => 'Recordatorios', 'tag' => 'Email/SMS'],
                    ],
                ],
                [
                    'title' => 'Carta digital',
                    'meta' => 'Explora platos y maridajes.',
                    'actions' => [
                        ['label' => 'Filtros de alérgenos', 'tag' => 'Seguridad'],
                        ['label' => 'Recomendaciones', 'tag' => 'Chef'],
                        ['label' => 'Valoraciones', 'tag' => 'Social'],
                    ],
                ],
                [
                    'title' => 'Cuenta y pago',
                    'meta' => 'Ver consumo y pagar.',
                    'actions' => [
                        ['label' => 'Ver cuenta en vivo', 'tag' => 'Transparencia'],
                        ['label' => 'Pagar desde el móvil', 'tag' => 'QR'],
                        ['label' => 'Descargar factura', 'tag' => 'PDF'],
                    ],
                ],
            ],
        ],
    ];
}
