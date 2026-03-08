<?php
session_start();
$parametros = require __DIR__ . '/etc/parametros.php';
require __DIR__ . '/lib/libreria.php';
require __DIR__ . '/lib/restaurante.php';
require __DIR__ . '/lib/auth.php';

if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: login.php');
  exit;
}

if (empty($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$user = $_SESSION['user'];
$role = $user['role'] ?? 'cliente';
$menus = obtenerMenusRestaurante();

$visibleMenus = [];
if (isset($menus['principal'])) {
  $visibleMenus['principal'] = $menus['principal'];
}
if (isset($menus[$role])) {
  $visibleMenus[$role] = $menus[$role];
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($parametros['app_name']); ?> · Panel</title>
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
        <h1><?php echo e($parametros['app_name']); ?> · Panel unificado</h1>
        <p class="tagline">HTML5 con autenticación simulada y vistas por rol.</p>
      </div>
    </div>
    <div class="user-chip">
      <div>
        <strong><?php echo e($user['name'] ?? $user['email'] ?? 'Usuario'); ?></strong>
        <small><?php echo strtoupper(e($role)); ?></small>
      </div>
      <a class="btn" href="?logout=1">Salir</a>
    </div>
  </header>

  <nav aria-label="Menú principal">
    <div class="nav-grid">
      <?php foreach ($visibleMenus as $id => $menu): ?>
        <a class="nav-link" href="#<?php echo e($id); ?>">
          <div><?php echo e($menu['icon']); ?> <?php echo e($menu['short']); ?></div>
          <span><?php echo e($menu['badge']); ?></span>
        </a>
      <?php endforeach; ?>
      <?php if (in_array($role, ['maitre', 'admin'], true)): ?>
        <a class="nav-link" href="maitre.php">
          <div>🛎️ Tablero Maitre</div>
          <span>Reservas en vivo</span>
        </a>
      <?php endif; ?>
      <a class="nav-link" href="#actividad">
        <div>📝 Log</div>
        <span>Simulación</span>
      </a>
    </div>
  </nav>

  <main>
    <section>
      <div class="section-head">
        <div>
          <h2 class="section-title">Bienvenido, <?php echo e($user['name'] ?? $user['email'] ?? ''); ?></h2>
          <p class="section-subtitle">Rol activo: <?php echo strtoupper(e($role)); ?> · Solo verás las funcionalidades de tu rol.</p>
        </div>
        <span class="badge">Sesión activa</span>
      </div>
      <div class="auth-panel" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <div class="auth-copy">
          <h3>Atajos</h3>
          <ul>
            <li><a href="#principal">Ir a Principal</a></li>
            <?php if (isset($visibleMenus[$role])): ?>
              <li><a href="#<?php echo e($role); ?>">Ir a tu menú</a></li>
            <?php endif; ?>
            <?php if (in_array($role, ['maitre', 'admin'], true)): ?>
              <li><a href="maitre.php">Abrir tablero de reservas</a></li>
            <?php endif; ?>
            <li><a href="#actividad">Ir al log</a></li>
          </ul>
        </div>
        <div class="auth-box">
          <p class="helper-text">Usa el botón “Salir” para cambiar de usuario. Los datos de sesión se guardan en memoria PHP.</p>
          <p class="helper-text">Puedes conectar estas acciones a tu API real sustituyendo el log simulado.</p>
        </div>
      </div>
    </section>

    <?php foreach ($visibleMenus as $id => $menu): ?>
      <section id="<?php echo e($id); ?>">
        <div class="section-head">
          <div>
            <h2 class="section-title"><?php echo e($menu['title']); ?></h2>
            <p class="section-subtitle"><?php echo e($menu['badge']); ?> · Listo para conectar a la lógica PHP o API.</p>
          </div>
          <span class="badge"><?php echo e($menu['badge']); ?></span>
        </div>
        <div class="grid">
          <?php foreach ($menu['cards'] as $card): ?>
            <article class="card">
              <div class="role"><?php echo e($card['title']); ?></div>
              <p class="meta"><?php echo e($card['meta']); ?></p>
              <?php echo renderCardActions($card['actions'], $id, $menu['title']); ?>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>

    <section id="actividad">
      <div class="section-head">
        <div>
          <h2 class="section-title">Actividad simulada</h2>
          <p class="section-subtitle">Pista de lo que se enviaría al servidor cuando un usuario actúa.</p>
        </div>
        <span class="badge">Front sin backend</span>
      </div>
      <div class="activity">
        <div class="activity-head">
          <h4>Historial reciente</h4>
          <div class="activity-actions">
            <button type="button" class="btn" id="activity-clear">Borrar historial</button>
          </div>
        </div>
        <ul id="activity-feed"></ul>
      </div>
    </section>
  </main>
  </div>

  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script>
    const toastEl = document.getElementById('toast');
    const activityFeed = document.getElementById('activity-feed');
    const activityClear = document.getElementById('activity-clear');
    let toastTimeout;

    async function fetchActivity() {
      try {
        const res = await fetch('api/log.php');
        const json = await res.json();
        if (json.ok && Array.isArray(json.items)) {
          activityFeed.innerHTML = '';
          json.items.forEach(entry => appendActivity(entry));
        }
      } catch (err) {
        appendActivity({ action: 'Sin conexión a API' });
      }
      trimActivity();
    }

    function appendActivity(entry) {
      const li = document.createElement('li');
      const ts = entry.ts ? new Date(entry.ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
      const label = `[${ts || '---'}] [${entry.section || 'General'}] ${entry.action || ''} ${entry.meta ? '· ' + entry.meta : ''}`;
      li.textContent = label;
      activityFeed.appendChild(li);
    }

    function trimActivity() {
      const limit = 50;
      while (activityFeed.children.length > limit) {
        activityFeed.removeChild(activityFeed.firstChild);
      }
    }

    function showToast(message) {
      toastEl.textContent = message;
      toastEl.classList.add('show');
      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => toastEl.classList.remove('show'), 2600);
    }

    async function sendAction(sectionTitle, label, meta = '') {
      try {
        const res = await fetch('api/log.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ section: sectionTitle, action: label, meta })
        });
        const json = await res.json();
        if (json.ok && json.entry) {
          activityFeed.prepend(document.createElement('li')).textContent = `[${new Date(json.entry.ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}] [${json.entry.section}] ${json.entry.action} · ${json.entry.meta || ''}`;
          trimActivity();
          return true;
        }
      } catch (err) {
        // ignore
      }
      activityFeed.prepend(document.createElement('li')).textContent = `[---] [${sectionTitle}] ${label} · (offline)`;
      trimActivity();
      return false;
    }

    document.querySelectorAll('ul.actions li').forEach(item => {
      item.tabIndex = 0;
      item.setAttribute('role', 'button');
      item.addEventListener('click', () => {
        const label = item.dataset.label;
        const section = item.dataset.sectionTitle;
        const meta = item.querySelector('.tag')?.textContent || '';
        sendAction(section, label, meta);
        showToast(`${label} enviado`);
      });
      item.addEventListener('keydown', ev => {
        if (ev.key === 'Enter' || ev.key === ' ') {
          ev.preventDefault();
          item.click();
        }
      });
    });

    activityClear.addEventListener('click', async () => {
      await sendAction('Sistema', 'Historial borrado');
      activityFeed.innerHTML = '';
      showToast('Historial borrado.');
    });

    fetchActivity();
  </script>
</body>
</html>
