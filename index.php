<?php
/**
 * ==============================================================================
 * Listado Principal de Usuarios (Dashboard ABM)
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/usuarios.php';
require_once __DIR__ . '/backend/roles.php';

// Verificar que el usuario tenga sesión activa
requiereAutenticacion();

$usuarioActual = obtenerUsuarioLogueado();
$mensajeFlash = obtenerMensajeFlash();

// Obtener los datos para la vista
$usuarios = listarUsuarios();
$roles = listarRoles();

// Métricas para los KPI cards
$totalUsuarios = count($usuarios);
$totalRoles = count($roles);
$totalAdmins = count(array_filter($usuarios, fn($u) => (int)$u['role'] === 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Usuarios — Panel de Control</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Estilos Globales -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <!-- Barra de Navegación Superior (Topbar) -->
  <header class="topbar">
    <div class="topbar-container">
      <a href="index.php" class="brand-logo">
        <span class="brand-icon">⚡</span>
        <span>Panel de Gestión</span>
      </a>

      <nav class="topbar-nav">
        <a href="index.php" class="nav-link active">
          👥 Usuarios
        </a>
        <a href="roles.php" class="nav-link">
          🛡️ Roles
        </a>
        <div class="user-profile-badge">
          <div class="avatar" title="<?= htmlspecialchars($usuarioActual['nombre_completo']) ?>">
            <?= strtoupper(substr($usuarioActual['username'], 0, 2)) ?>
          </div>
          <div class="user-meta">
            <span class="user-name"><?= htmlspecialchars($usuarioActual['nombre_completo']) ?></span>
            <span class="user-role-label"><?= htmlspecialchars($usuarioActual['rol_nombre']) ?></span>
          </div>
          <a href="logout.php" class="btn btn-secondary btn-sm" title="Cerrar sesión" style="margin-left: 0.5rem;">
            Salir 🚪
          </a>
        </div>
      </nav>
    </div>
  </header>

  <!-- Contenedor Principal -->
  <main class="main-content">
    
    <!-- Encabezado de Página -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Módulo de Usuarios</h1>
        <p class="page-subtitle">Administra los accesos, credenciales y roles asignados a cada usuario</p>
      </div>
      <div>
        <a href="formulario.php" class="btn btn-primary">
          ➕ Nuevo Usuario
        </a>
      </div>
    </div>

    <!-- Mensajes Flash de Retroalimentación -->
    <?php if ($mensajeFlash): ?>
      <div class="alert alert-<?= htmlspecialchars($mensajeFlash['tipo']) ?>">
        <div class="alert-content">
          <span><?= htmlspecialchars($mensajeFlash['mensaje']) ?></span>
        </div>
        <button type="button" class="alert-close" aria-label="Cerrar">&times;</button>
      </div>
    <?php endif; ?>

    <!-- Tarjetas de Métricas (KPIs) -->
    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-info">
          <h4>Total Usuarios</h4>
          <div class="stat-number"><?= $totalUsuarios ?></div>
        </div>
        <div class="stat-icon-wrapper icon-purple">👥</div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <h4>Administradores</h4>
          <div class="stat-number"><?= $totalAdmins ?></div>
        </div>
        <div class="stat-icon-wrapper icon-blue">🛡️</div>
      </div>

      <div class="stat-card">
        <div class="stat-info">
          <h4>Roles Definidos</h4>
          <div class="stat-number"><?= $totalRoles ?></div>
        </div>
        <div class="stat-icon-wrapper icon-green">🏷️</div>
      </div>
    </section>

    <!-- Tarjeta Principal con Tabla de Usuarios -->
    <section class="card">
      <div class="card-header">
        <h2 class="card-title">Listado de Usuarios Registrados</h2>
        <div style="max-width: 300px; width: 100%;">
          <input 
            type="text" 
            id="tabla-buscador" 
            class="form-input" 
            placeholder="🔍 Buscar por nombre, email o rol..."
            aria-label="Buscar en la tabla de usuarios"
          >
        </div>
      </div>

      <div class="card-body" style="padding: 0;">
        <?php if (empty($usuarios)): ?>
          <div class="empty-state">
            <div class="empty-state-icon">👤</div>
            <h3 class="empty-state-title">No hay usuarios registrados</h3>
            <p>Comienza creando el primer usuario en el sistema con el botón superior.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>Nombre Completo</th>
                  <th>Correo Electrónico</th>
                  <th>Rol</th>
                  <th>Fecha de Registro</th>
                  <th style="text-align: right;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $u): ?>
                  <tr>
                    <td>
                      <div class="user-cell">
                        <div class="user-cell-avatar">
                          <?= strtoupper(substr($u['username'], 0, 2)) ?>
                        </div>
                        <div>
                          <span class="user-cell-name">@<?= htmlspecialchars($u['username']) ?></span>
                          <?php if ((int)$u['id'] === (int)$usuarioActual['id']): ?>
                            <span style="font-size: 0.7rem; background: #e2e8f0; color: #475569; padding: 1px 6px; border-radius: 4px; margin-left: 4px;">Tú</span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <td>
                      <?= htmlspecialchars($u['name'] . ' ' . $u['last_name']) ?>
                    </td>
                    <td>
                      <a href="mailto:<?= htmlspecialchars($u['email']) ?>" style="color: var(--color-text-muted);">
                        <?= htmlspecialchars($u['email']) ?>
                      </a>
                    </td>
                    <td>
                      <?php 
                        $badgeClass = 'badge-custom';
                        if ((int)$u['role'] === 1) $badgeClass = 'badge-admin';
                        elseif ((int)$u['role'] === 2) $badgeClass = 'badge-editor';
                        elseif ((int)$u['role'] === 3) $badgeClass = 'badge-guest';
                      ?>
                      <span class="badge <?= $badgeClass ?>">
                        <?= htmlspecialchars($u['role_name']) ?>
                      </span>
                    </td>
                    <td style="color: var(--color-text-muted); font-size: 0.85rem;">
                      <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?>
                    </td>
                    <td>
                      <div class="actions-group" style="justify-content: flex-end;">
                        <!-- Botón Editar (GET solo lectura para precargar el formulario) -->
                        <a 
                          href="formulario.php?id=<?= $u['id'] ?>" 
                          class="btn btn-secondary btn-sm"
                          title="Editar usuario"
                        >
                          ✏️ Editar
                        </a>

                        <!-- Botón Eliminar (Siempre enviado por POST mediante formulario) -->
                        <form 
                          method="POST" 
                          action="eliminar.php" 
                          onsubmit="return confirmarEliminacion('¿Estás seguro de que deseas eliminar permanentemente al usuario \'<?= htmlspecialchars(addslashes($u['username'])) ?>\'?');"
                          style="display: inline;"
                        >
                          <input type="hidden" name="id" value="<?= $u['id'] ?>">
                          <button 
                            type="submit" 
                            class="btn btn-outline-danger btn-sm"
                            title="Eliminar usuario"
                            <?= ((int)$u['id'] === (int)$usuarioActual['id']) ? 'disabled title="No puedes eliminarte a ti mismo"' : '' ?>
                          >
                            🗑️ Eliminar
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </section>

  </main>

  <!-- Pie de Página -->
  <footer class="footer">
    <div class="topbar-container" style="justify-content: center;">
      <p>&copy; <?= date('Y') ?> Sistema de Gestión de Usuarios y Roles — Sprint 2</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="js/main.js"></script>
</body>
</html>
