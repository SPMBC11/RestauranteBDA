<?php
$parametros = require __DIR__ . '/etc/parametros.php';
require __DIR__ . '/lib/libreria.php';
require __DIR__ . '/lib/restaurante.php';
$menus = obtenerMenusRestaurante();
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
  <header>
    <div class="brand">
      <div class="brand-mark">R</div>
      <div>
        <h1><?php echo e($parametros['app_name']); ?> · Panel unificado</h1>
        <p class="tagline">HTML5 listo para conectar a tu backend o base de datos.</p>
      </div>
    </div>
    <div class="pill">Entorno: <?php echo e($parametros['entorno']); ?></div>
  </header>

  <nav aria-label="Menú principal">
    <div class="nav-grid">
      <a class="nav-link" href="#acceso">
        <div>🔐 Acceso</div>
        <span>Autenticación</span>
      </a>
      <?php foreach ($menus as $id => $menu): ?>
        <a class="nav-link" href="#<?php echo e($id); ?>">
          <div><?php echo e($menu['icon']); ?> <?php echo e($menu['short']); ?></div>
          <span><?php echo e($menu['badge']); ?></span>
        </a>
      <?php endforeach; ?>
      <a class="nav-link" href="#actividad">
        <div>📝 Log</div>
        <span>Simulación</span>
      </a>
    </div>
  </nav>

  <main>
    <section id="acceso">
      <div class="section-head">
        <div>
          <h2 class="section-title">Autenticación</h2>
          <p class="section-subtitle">Crear cuenta o iniciar sesión para asignar el rol correcto.</p>
        </div>
        <span class="badge">Control de acceso</span>
      </div>
      <div class="auth-panel">
        <div class="auth-copy">
          <h3>Roles contemplados</h3>
          <p class="helper-text">Una vez autenticado, el sistema puede dirigir al usuario al menú de su rol.</p>
          <ul>
            <li>Maitre: reservas, sala y lista de espera.</li>
            <li>Mesero: toma de pedidos, cuentas y cobros.</li>
            <li>Cocinero: tickets, pase y stock rápido.</li>
            <li>Administrador: finanzas, usuarios y carta.</li>
            <li>Cliente: autogestión de reserva y cuenta.</li>
          </ul>
        </div>
        <div class="auth-box">
          <div class="tabs">
            <button type="button" class="tab active" data-auth-tab="login">Ya tengo cuenta</button>
            <button type="button" class="tab" data-auth-tab="signup">Crear cuenta</button>
          </div>
          <form id="form-login" data-auth-form="login" class="form-grid">
            <label class="form-control">Correo
              <input name="email" type="email" placeholder="nombre@resto.com" required>
            </label>
            <label class="form-control">Contraseña
              <input name="password" type="password" minlength="<?php echo (int)$parametros['seguridad']['min_pass_length']; ?>" required>
            </label>
            <button class="btn primary" type="submit">Ingresar</button>
            <p class="helper-text">Las sesiones se pueden proteger con tokens de <?php echo (int)$parametros['seguridad']['token_ttl_minutes']; ?> minutos.</p>
          </form>

          <form id="form-signup" data-auth-form="signup" class="form-grid" hidden>
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
            <p class="helper-text">El alta puede validar correo o SMS antes de asignar rol.</p>
          </form>
        </div>
      </div>
    </section>

    <?php foreach ($menus as $id => $menu): ?>
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

  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script>
    const tabs = document.querySelectorAll('[data-auth-tab]');
    const forms = document.querySelectorAll('[data-auth-form]');
    const toastEl = document.getElementById('toast');
    const activityFeed = document.getElementById('activity-feed');
    const activityClear = document.getElementById('activity-clear');
    const STORAGE_KEY = 'resto-ui-activity-v2';
    let toastTimeout;

    function setAuthMode(mode) {
      tabs.forEach(tab => tab.classList.toggle('active', tab.dataset.authTab === mode));
      forms.forEach(form => {
        const isTarget = form.dataset.authForm === mode;
        form.hidden = !isTarget;
        form.setAttribute('aria-hidden', isTarget ? 'false' : 'true');
      });
    }

    tabs.forEach(tab => {
      tab.addEventListener('click', () => setAuthMode(tab.dataset.authTab));
    });

    function showToast(message) {
      toastEl.textContent = message;
      toastEl.classList.add('show');
      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => toastEl.classList.remove('show'), 2600);
    }

    function addActivity(message) {
      const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      const entry = `[${timestamp}] ${message}`;
      const li = document.createElement('li');
      li.textContent = entry;
      activityFeed.prepend(li);
      persistActivity(entry);
      trimActivity();
    }

    function persistActivity(entry) {
      const saved = loadActivity();
      saved.unshift(entry);
      const limit = 30;
      while (saved.length > limit) saved.pop();
      localStorage.setItem(STORAGE_KEY, JSON.stringify(saved));
    }

    function loadActivity() {
      try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
      } catch (err) {
        return [];
      }
    }

    function renderStoredActivity() {
      const saved = loadActivity();
      saved.forEach(entry => {
        const li = document.createElement('li');
        li.textContent = entry;
        activityFeed.appendChild(li);
      });
      trimActivity();
    }

    function trimActivity() {
      const limit = 12;
      while (activityFeed.children.length > limit) {
        activityFeed.removeChild(activityFeed.lastChild);
      }
    }

    document.querySelectorAll('ul.actions li').forEach(item => {
      item.tabIndex = 0;
      item.setAttribute('role', 'button');
      item.addEventListener('click', () => {
        const label = item.dataset.label;
        const section = item.dataset.sectionTitle;
        addActivity(`${section}: ${label}`);
        showToast(`${label} listo para implementar con API`);
      });
      item.addEventListener('keydown', ev => {
        if (ev.key === 'Enter' || ev.key === ' ') {
          ev.preventDefault();
          item.click();
        }
      });
    });

    forms.forEach(form => {
      form.addEventListener('submit', ev => {
        ev.preventDefault();
        const data = new FormData(form);
        const mode = form.dataset.authForm === 'signup' ? 'Alta' : 'Login';
        const email = data.get('email');
        const rol = data.get('rol') || 'Pendiente de rol';
        addActivity(`${mode}: ${email} · ${rol}`);
        showToast(`${mode} simulado para ${email}`);
      });
    });

    activityClear.addEventListener('click', () => {
      localStorage.removeItem(STORAGE_KEY);
      activityFeed.innerHTML = '';
      showToast('Historial borrado.');
    });

    renderStoredActivity();
  </script>
</body>
</html>
