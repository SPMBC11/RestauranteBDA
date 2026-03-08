<?php
session_start();
$parametros = require __DIR__ . '/etc/parametros.php';
require __DIR__ . '/lib/auth.php';

$error = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';
    if ($mode === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = authenticate($email, $password);
        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        }
        $error = 'Credenciales inválidas';
    } elseif ($mode === 'signup') {
        $name = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['rol'] ?? 'cliente';
        $password = $_POST['password'] ?? '';
        $result = registerUser($name, $email, $role, $password, (int)$parametros['seguridad']['min_pass_length']);
        if ($result['ok']) {
            $_SESSION['user'] = $result['user'];
            header('Location: index.php');
            exit;
        }
        $error = $result['message'] ?? 'No se pudo crear la cuenta';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($parametros['app_name'], ENT_QUOTES, 'UTF-8'); ?> · Acceso</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
  <div class="page">
  <header>
    <div class="brand">
      <div class="brand-mark">R</div>
      <div>
        <h1><?php echo htmlspecialchars($parametros['app_name'], ENT_QUOTES, 'UTF-8'); ?> · Acceso</h1>
        <p class="tagline">Autenticación y alta por rol.</p>
      </div>
    </div>
    <div class="pill">Entorno: <?php echo htmlspecialchars($parametros['entorno'], ENT_QUOTES, 'UTF-8'); ?></div>
  </header>

  <main>
    <section id="acceso">
      <div class="section-head">
        <div>
          <h2 class="section-title">Autenticación</h2>
          <p class="section-subtitle">Inicia sesión o crea tu cuenta para continuar al panel.</p>
        </div>
        <span class="badge">Control de acceso</span>
      </div>
      <div class="auth-panel">
        <div class="auth-copy">
          <h3>Roles contemplados</h3>
          <p class="helper-text">Usa las demos: maitre|mesero|cocinero|admin|cliente@resto.com · clave demo1234.</p>
          <ul>
            <li>Maitre: reservas, sala y lista de espera.</li>
            <li>Mesero: pedidos, cuentas y cobros.</li>
            <li>Cocinero: tickets, pase y stock rápido.</li>
            <li>Administrador: finanzas, usuarios y carta.</li>
            <li>Cliente: autogestión de reserva y cuenta.</li>
          </ul>
        </div>
        <div class="auth-box">
          <?php if ($error): ?>
            <div class="pill" style="border-color: rgba(239,68,68,0.5); color:#fecdd3; background: rgba(239,68,68,0.08);">
              <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
          <div class="tabs">
            <button type="button" class="tab active" data-auth-tab="login">Ya tengo cuenta</button>
            <button type="button" class="tab" data-auth-tab="signup">Crear cuenta</button>
          </div>
          <form id="form-login" data-auth-form="login" class="form-grid" method="post">
            <input type="hidden" name="mode" value="login">
            <label class="form-control">Correo
              <input name="email" type="email" placeholder="nombre@resto.com" required>
            </label>
            <label class="form-control">Contraseña
              <input name="password" type="password" minlength="<?php echo (int)$parametros['seguridad']['min_pass_length']; ?>" required>
            </label>
            <button class="btn primary" type="submit">Ingresar</button>
            <p class="helper-text">Sesiones simuladas en PHP. Token TTL: <?php echo (int)$parametros['seguridad']['token_ttl_minutes']; ?> min.</p>
          </form>

          <form id="form-signup" data-auth-form="signup" class="form-grid" method="post" hidden>
            <input type="hidden" name="mode" value="signup">
            <label class="form-control">Nombre completo
              <input name="nombre" required>
            </label>
            <label class="form-control">Correo institucional
              <input name="email" type="email" placeholder="usuario@resto.com" required>
            </label>
            <label class="form-control">Rol
              <select name="rol" required>
                <option value="maitre">Maitre</option>
                <option value="mesero">Mesero</option>
                <option value="cocinero">Cocinero</option>
                <option value="admin">Administrador</option>
                <option value="cliente">Cliente</option>
              </select>
            </label>
            <label class="form-control">Contraseña
              <input name="password" type="password" minlength="<?php echo (int)$parametros['seguridad']['min_pass_length']; ?>" required>
            </label>
            <button class="btn primary" type="submit">Crear cuenta</button>
            <p class="helper-text">Se valida el largo mínimo y unicidad de correo en sesión.</p>
          </form>
        </div>
      </div>
    </section>
  </main>
  </div>

  <script>
    const tabs = document.querySelectorAll('[data-auth-tab]');
    const forms = document.querySelectorAll('[data-auth-form]');

    function setAuthMode(mode) {
      tabs.forEach(tab => tab.classList.toggle('active', tab.dataset.authTab === mode));
      forms.forEach(form => {
        const isTarget = form.dataset.authForm === mode;
        form.hidden = !isTarget;
        form.setAttribute('aria-hidden', isTarget ? 'false' : 'true');
      });
    }

    tabs.forEach(tab => tab.addEventListener('click', () => setAuthMode(tab.dataset.authTab)));
  </script>
</body>
</html>