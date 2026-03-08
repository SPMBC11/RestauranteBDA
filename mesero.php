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
if (!in_array($role, ['mesero', 'admin'], true)) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($parametros['app_name'], ENT_QUOTES, 'UTF-8'); ?> · Mesero</title>
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
        <h1><?php echo htmlspecialchars($parametros['app_name'], ENT_QUOTES, 'UTF-8'); ?> · Tablero Mesero</h1>
        <p class="tagline">Pedidos por mesa y estados de servicio.</p>
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
          <h2 class="section-title">Nuevo pedido</h2>
          <p class="section-subtitle">Asigna la mesa y carga ítems rápidos.</p>
        </div>
        <span class="badge">Servicio</span>
      </div>
      <form id="form-pedido" class="form-grid">
        <label class="form-control">Mesa
          <input name="mesa" placeholder="A1" required>
        </label>
        <label class="form-control">Ítems
          <textarea name="items" placeholder="2x Pasta, 1x Agua" required></textarea>
        </label>
        <label class="form-control">Total (opcional)
          <input name="total" type="number" step="0.01" min="0" placeholder="0.00">
        </label>
        <button class="btn primary" type="submit">Enviar a cocina</button>
      </form>
    </section>

    <section>
      <div class="section-head">
        <div>
          <h2 class="section-title">Pedidos asignados</h2>
          <p class="section-subtitle">Actualiza a servido o cobrado.</p>
        </div>
        <span class="badge">Mesas</span>
      </div>
      <div class="activity">
        <div class="activity-head">
          <h4>Lista</h4>
          <div class="activity-actions">
            <button type="button" class="btn" id="refresh">Recargar</button>
          </div>
        </div>
        <ul id="ped-list"></ul>
      </div>
    </section>
  </main>
  </div>

  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script>
    const form = document.getElementById('form-pedido');
    const list = document.getElementById('ped-list');
    const refreshBtn = document.getElementById('refresh');
    const toastEl = document.getElementById('toast');
    let toastTimeout;

    function showToast(message) {
      toastEl.textContent = message;
      toastEl.classList.add('show');
      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => toastEl.classList.remove('show'), 2400);
    }

    async function sendLog(action, meta = '', section = 'Pedidos') {
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

    function renderItem(item) {
      const li = document.createElement('li');
      li.innerHTML = `
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;">
          <div>
            <strong>Mesa ${item.mesa}</strong> · ${item.items}
            <div class="helper-text">Estado: ${item.estado} · Total: ${item.total ?? 0}</div>
            <div class="helper-text">Asignado: ${item.asignadoA || ''}</div>
          </div>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button class="btn" data-action="estado" data-id="${item.id}" data-estado="servido">Servido</button>
            <button class="btn" data-action="estado" data-id="${item.id}" data-estado="cobrado">Cobrado</button>
          </div>
        </div>
      `;
      return li;
    }

    async function loadPedidos() {
      list.innerHTML = '';
      try {
        const res = await fetch('api/pedidos.php');
        const json = await res.json();
        if (!res.ok || !json.ok || !Array.isArray(json.items)) throw new Error(json.message || 'No se pudo cargar');
        if (json.items.length === 0) {
          const li = document.createElement('li');
          li.textContent = 'Sin pedidos todavía.';
          list.appendChild(li);
          return;
        }
        json.items.forEach(item => list.appendChild(renderItem(item)));
      } catch (err) {
        const li = document.createElement('li');
        li.textContent = 'Error al cargar pedidos.';
        list.appendChild(li);
        showToast(err.message || 'Error al cargar');
      }
    }

    form.addEventListener('submit', async ev => {
      ev.preventDefault();
      const data = Object.fromEntries(new FormData(form));
      try {
        const res = await fetch('api/pedidos.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'create', ...data })
        });
        const json = await res.json();
        if (!res.ok || !json.ok) throw new Error(json.message || 'Error al crear');
        showToast('Pedido enviado');
        await sendLog('Pedido enviado', `${data.mesa} · ${data.items}`);
        form.reset();
        loadPedidos();
      } catch (err) {
        showToast(err.message || 'Error al crear');
      }
    });

    list.addEventListener('click', async ev => {
      const btn = ev.target.closest('[data-action="estado"]');
      if (!btn) return;
      const { id, estado } = btn.dataset;
      try {
        const res = await fetch('api/pedidos.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'estado', id, estado })
        });
        const json = await res.json();
        if (!res.ok || !json.ok) throw new Error(json.message || 'Error al actualizar');
        showToast('Estado actualizado');
        await sendLog(`Pedido ${estado}`, `ID ${id}`);
        loadPedidos();
      } catch (err) {
        showToast(err.message || 'Error al actualizar');
      }
    });

    refreshBtn.addEventListener('click', loadPedidos);
    loadPedidos();
  </script>
</body>
</html>
