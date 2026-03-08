<?php
declare(strict_types=1);
session_start();

$parametros = require __DIR__ . '/etc/parametros.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/storage.php';

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
if (!in_array($role, ['maitre', 'admin'], true)) {
    header('Location: index.php');
    exit;
}

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($parametros['app_name'], ENT_QUOTES, 'UTF-8'); ?> · Maitre</title>
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
        <h1><?php echo htmlspecialchars($parametros['app_name'], ENT_QUOTES, 'UTF-8'); ?> · Tablero Maitre</h1>
        <p class="tagline">Gestiona reservas, confirma o cancela.</p>
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
          <h2 class="section-title">Crear reserva</h2>
          <p class="section-subtitle">Datos mínimos: cliente, fecha/hora, pax.</p>
        </div>
        <span class="badge">Alta</span>
      </div>
      <form id="form-reserva" class="form-grid">
        <label class="form-control">Nombre cliente
          <input name="nombre" required>
        </label>
        <label class="form-control">Fecha y hora
          <input name="fecha" type="datetime-local" required>
        </label>
        <label class="form-control">Pax
          <input name="pax" type="number" min="1" value="2" required>
        </label>
        <label class="form-control">Notas
          <textarea name="notas" placeholder="Alergias, ubicación preferida..."></textarea>
        </label>
        <button class="btn primary" type="submit">Guardar</button>
      </form>
    </section>

    <section>
      <div class="section-head">
        <div>
          <h2 class="section-title">Reservas</h2>
          <p class="section-subtitle">Actualiza estados en línea.</p>
        </div>
        <span class="badge">Listado</span>
      </div>
      <div class="activity">
        <div class="activity-head">
          <h4>Lista</h4>
          <div class="activity-actions">
            <button type="button" class="btn" id="refresh">Recargar</button>
          </div>
        </div>
        <ul id="res-list"></ul>
      </div>
    </section>
  </main>
  </div>

  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script>
    const form = document.getElementById('form-reserva');
    const list = document.getElementById('res-list');
    const refreshBtn = document.getElementById('refresh');
    const toastEl = document.getElementById('toast');
    let toastTimeout;

    function showToast(message) {
      toastEl.textContent = message;
      toastEl.classList.add('show');
      clearTimeout(toastTimeout);
      toastTimeout = setTimeout(() => toastEl.classList.remove('show'), 2400);
    }

    async function sendLog(action, meta = '', section = 'Reservas') {
      try {
        await fetch('api/log.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ section, action, meta })
        });
      } catch (err) {
        // logging is best-effort
      }
    }

    function renderItem(item) {
      const li = document.createElement('li');
      li.innerHTML = `
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;">
          <div>
            <strong>${item.nombre}</strong> · ${new Date(item.fecha).toLocaleString()} · ${item.pax} pax
            <div class="helper-text">${item.notas || ''}</div>
            <div class="helper-text">Estado: ${item.estado}</div>
          </div>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button class="btn" data-action="estado" data-id="${item.id}" data-estado="confirmada">Confirmar</button>
            <button class="btn" data-action="estado" data-id="${item.id}" data-estado="cancelada">Cancelar</button>
          </div>
        </div>
      `;
      return li;
    }

    async function loadReservas() {
      list.innerHTML = '';
      try {
        const res = await fetch('api/reservas.php');
        const json = await res.json();
        if (!res.ok || !json.ok || !Array.isArray(json.items)) {
          throw new Error(json.message || 'No se pudo cargar');
        }
        if (json.items.length === 0) {
          const li = document.createElement('li');
          li.textContent = 'Sin reservas todavía.';
          list.appendChild(li);
          return;
        }
        json.items.forEach(item => list.appendChild(renderItem(item)));
      } catch (err) {
        const li = document.createElement('li');
        li.textContent = 'Error al cargar reservas.';
        list.appendChild(li);
        showToast(err.message || 'Error al cargar');
      }
    }

    form.addEventListener('submit', async ev => {
      ev.preventDefault();
      const data = Object.fromEntries(new FormData(form));
      try {
        const res = await fetch('api/reservas.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'create', ...data })
        });
        const json = await res.json();
        if (!res.ok || !json.ok) {
          throw new Error(json.message || 'Error al crear');
        }
        showToast('Reserva creada');
        form.reset();
        await sendLog('Reserva creada', `${data.nombre} · ${data.fecha}`, 'Maitre');
        loadReservas();
      } catch (err) {
        showToast(err.message || 'Error al crear');
      }
    });

    list.addEventListener('click', async ev => {
      const btn = ev.target.closest('[data-action="estado"]');
      if (!btn) return;
      const id = btn.dataset.id;
      const estado = btn.dataset.estado;
      try {
        const res = await fetch('api/reservas.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'estado', id, estado })
        });
        const json = await res.json();
        if (!res.ok || !json.ok) {
          throw new Error(json.message || 'Error al actualizar');
        }
        showToast('Estado actualizado');
        await sendLog(`Reserva ${estado}`, `ID ${id}`, 'Maitre');
        loadReservas();
      } catch (err) {
        showToast(err.message || 'Error al actualizar');
      }
    });

    refreshBtn.addEventListener('click', loadReservas);
    loadReservas();
  </script>
</body>
</html>
