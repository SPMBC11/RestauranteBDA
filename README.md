# Restaurante · Taller (Propuesta UI)

Estructura solicitada para la propuesta de interfaz HTML5 del restaurante.

## Estructura

- esqueleto.html — plantilla estática de referencia (sin PHP).
- login.php — página de autenticación/alta en PHP.
- index.php — vista principal, requiere sesión y muestra solo el rol del usuario.
- css/estilo.css — estilos globales.
- etc/parametros.php — parámetros globales (app y seguridad).
- lib/libreria.php — utilidades de presentación.
- lib/restaurante.php — datos/menús por rol.
- lib/auth.php — helpers de autenticación en memoria.

## Cómo usar

1. Servir la carpeta en PHP (`php -S localhost:8000`).
2. Abrir login.php para iniciar sesión o crear cuenta (usuarios demo: maitre/mesero/cocinero/admin/cliente@resto.com, clave demo1234).
3. Tras autenticación, index.php muestra solo el menú del rol activo.
4. Revisar esqueleto.html si se requiere la versión estática.

## Roles contemplados

- Maitre
- Mesero
- Cocinero
- Administrador
- Cliente

Cada sección está lista para conectarse a la base de datos o API que se defina.