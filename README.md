# Restaurante · Taller (Propuesta UI)

Estructura solicitada para la propuesta de interfaz HTML5 del restaurante.

## Estructura

- esqueleto.html — plantilla estática de referencia (sin PHP).
- index.php — vista principal con autenticación simulada y menús por rol.
- css/estilo.css — estilos globales.
- etc/parametros.php — parámetros globales (app y seguridad).
- lib/libreria.php — utilidades de presentación.
- lib/restaurante.php — datos/menús por rol.

## Cómo usar

1. Servir la carpeta en PHP (`php -S localhost:8000`).
2. Abrir index.php para ver la UI dinámica (logs en LocalStorage).
3. Revisar esqueleto.html si se requiere la versión estática.

## Roles contemplados

- Maitre
- Mesero
- Cocinero
- Administrador
- Cliente

Cada sección está lista para conectarse a la base de datos o API que se defina.