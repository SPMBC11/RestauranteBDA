<?php
declare(strict_types=1);
session_start();

$parametros = require __DIR__ . '/etc/parametros.php';
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
$role = $_SESSION['user']['role'] ?? 'guest';
if ($role !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($parametros['app_name'], ENT_QUOTES, 'UTF-8'); ?> · Admin</title>
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
        <h1><?php echo htmlspecialchars($parametros['app_name'], ENT_QUOTES, 'UTF-8'); ?> · Panel Admin</h1>
        <p class="tagline">Usuarios, roles y trazabilidad.</p>
      </div>
    </div>
    <div class="user-chip">
      <div>
        <strong><?php echo htmlspecialchars($_SESSION['user']['name'] ?? $_SESSION['user']['email'], ENT_QUOTES, 'UTF-8'); ?></strong>
        <small><?php echo strtoupper(htmlspecialchars($role, ENT_QUOTES, 'UTF-8')); ?></small>
      </div>
      <a class="btn" href="index.php">Panel</a>
      <a class="btn" href="?logout=1">Salir</a>
    </div>
  </header>

  <main>
    <section>
      <div class="section-head">
        <div>
          <h2 class="section-title">Usuarios</h2>
          <p class="section-subtitle">Ajusta roles y revisa acceso.</p>
        </div>
        <span class="badge">Seguridad</span>
      </div>
      <div class="activity">
        <div class="activity-head">
          <h4>Listado</h4>
          <div class="activity-actions">
            <button type="button" class="btn" id="refresh">Recargar</button>
          </div>
        </div>
        <ul id="user-list"></ul>
      </div>
    </section>
  </main>
  </div>

  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script>
    const list = document.getElementById('user-list');
    const refreshBtn = document.getElementById('refresh');
    const toastEl = document.getElementById('toast');
    let toastTimeout;

    function showToast(message) {
      toastEl.textContent = message;
      toastEl.classList.add('show');
      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => toastEl.classList.remove('show'), 2400);
    }

    async function sendLog(action, meta = '', section = 'Admin') {
      try {
        await fetch('api/log.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ section, action, meta })
        });
      } catch (err) {
        // best effort
      }
    }

    function renderItem(user) {
      const li = document.createElement('li');
      const selectId = `role-${user.email}`;
      li.innerHTML = `
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;">
          <div>
            <strong>${user.name || user.email}</strong>
            <div class="helper-text">${user.email}</div>
          </div>
          <div style="display:flex;gap:8px;align-items:center;">
            <label class="helper-text" for="${selectId}">Rol</label>
            <select id="${selectId}" data-email="${user.email}">
              <option value="maitre" ${user.role === 'maitre' ? 'selected' : ''}>Maitre</option>
              <option value="mesero" ${user.role === 'mesero' ? 'selected' : ''}>Mesero</option>
              <option value="cocinero" ${user.role === 'cocinero' ? 'selected' : ''}>Cocinero</option>
              <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Administrador</option>
              <option value="cliente" ${user.role === 'cliente' ? 'selected' : ''}>Cliente</option>
            </select>
            <button class="btn" data-email="${user.email}" data-action="guardar">Guardar</button>
          </div>
        </div>
      `;
      return li;
    }

    async function loadUsers() {
      list.innerHTML = '';
      try {
        const res = await fetch('api/admin.php');
        const json = await res.json();
        if (!res.ok || !json.ok || !Array.isArray(json.items)) throw new Error(json.message || 'No se pudo cargar');
        json.items.forEach(u => list.appendChild(renderItem(u)));
      } catch (err) {
        const li = document.createElement('li');
        li.textContent = 'Error al cargar usuarios.';
        list.appendChild(li);
        showToast(err.message || 'Error al cargar');
      }
    }

    list.addEventListener('click', async ev => {
      const btn = ev.target.closest('button[data-action="guardar"]');
      if (!btn) return;
      const email = btn.dataset.email;
      const select = document.querySelector(`select[data-email="${email}"]`);
      const role = select?.value;
      try {
        const res = await fetch('api/admin.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'setRole', email, role })
        });
        const json = await res.json();
        if (!res.ok || !json.ok) throw new Error(json.message || 'Error al guardar');
        showToast('Rol actualizado');
        await sendLog('Rol actualizado', `${email} -> ${role}`);
      } catch (err) {
        showToast(err.message || 'Error al guardar');
      }
    });

    refreshBtn.addEventListener('click', loadUsers);
    loadUsers();
  </script>
</body>
</html>
