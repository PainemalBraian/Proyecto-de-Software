<?php
/**
 * ==============================================================================
 * Panel Principal / Listado de Usuarios — Tema Chocolate & Crema con Bootstrap 5
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/usuarios.php';
require_once __DIR__ . '/backend/roles.php';

// Proteger la vista con autenticación
requiereAutenticacion();

$usuarioActual = obtenerUsuarioLogueado();
$mensajeFlash = obtenerMensajeFlash();

// Obtener datos
$usuarios = listarUsuarios();
$roles = listarRoles();

// Métricas KPI
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
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- Estilos Personalizados: Chocolate & Crema -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <!-- Barra de Navegación Superior -->
  <header class="topbar-choco">
    <div class="container-xl d-flex align-items-center justify-content-between py-2">
      <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
        <span class="brand-icon-choco">☕</span>
        <span class="fw-bold fs-5" style="color: var(--color-choco-dark);">Panel de Gestión</span>
      </a>

      <nav class="d-flex align-items-center gap-3">
        <a href="index.php" class="nav-link-choco active">
          <i class="bi bi-people-fill"></i> Usuarios
        </a>
        <a href="roles.php" class="nav-link-choco">
          <i class="bi bi-shield-lock-fill"></i> Roles
        </a>

        <div class="d-flex align-items-center gap-2 ps-3 border-start" style="border-color: var(--color-cream-border) !important;">
          <div class="avatar-choco" title="<?= htmlspecialchars($usuarioActual['nombre_completo']) ?>">
            <?= strtoupper(substr($usuarioActual['username'], 0, 2)) ?>
          </div>
          <div class="d-none d-md-flex flex-column text-start">
            <span class="fw-bold small lh-1" style="color: var(--color-choco-dark);"><?= htmlspecialchars($usuarioActual['nombre_completo']) ?></span>
            <span class="small text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($usuarioActual['rol_nombre']) ?></span>
          </div>
          <a href="logout.php" class="btn btn-cream btn-sm ms-2" title="Cerrar sesión">
            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Salir</span>
          </a>
        </div>
      </nav>
    </div>
  </header>

  <!-- Contenedor Principal -->
  <main class="container-xl my-4 flex-grow-1">
    
    <!-- Encabezado de Página -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h2 fw-bold mb-1" style="color: var(--color-choco-dark);">Módulo de Usuarios</h1>
        <p class="text-muted mb-0">Gestiona los accesos, credenciales y perfiles de cada usuario</p>
      </div>
      <div>
        <a href="formulario.php" class="btn btn-choco">
          <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
        </a>
      </div>
    </div>

    <!-- Mensajes Flash -->
    <?php if ($mensajeFlash): ?>
      <div class="alert-choco alert-choco-<?= htmlspecialchars($mensajeFlash['tipo']) ?> mb-4">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill"></i>
          <span><?= htmlspecialchars($mensajeFlash['mensaje']) ?></span>
        </div>
        <button type="button" class="btn-close alert-close" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <!-- Tarjetas de Métricas (KPIs) -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="stat-card-choco d-flex align-items-center justify-content-between">
          <div>
            <div class="text-uppercase small fw-bold text-muted mb-1" style="letter-spacing: 0.05em;">Total Usuarios</div>
            <div class="h2 fw-bold mb-0" style="color: var(--color-choco-dark);"><?= $totalUsuarios ?></div>
          </div>
          <div class="stat-icon-choco icon-bg-cocoa">
            <i class="bi bi-people-fill"></i>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="stat-card-choco d-flex align-items-center justify-content-between">
          <div>
            <div class="text-uppercase small fw-bold text-muted mb-1" style="letter-spacing: 0.05em;">Administradores</div>
            <div class="h2 fw-bold mb-0" style="color: var(--color-caramel);"><?= $totalAdmins ?></div>
          </div>
          <div class="stat-icon-choco icon-bg-caramel">
            <i class="bi bi-shield-check"></i>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="stat-card-choco d-flex align-items-center justify-content-between">
          <div>
            <div class="text-uppercase small fw-bold text-muted mb-1" style="letter-spacing: 0.05em;">Roles Definidos</div>
            <div class="h2 fw-bold mb-0" style="color: var(--color-choco-medium);"><?= $totalRoles ?></div>
          </div>
          <div class="stat-icon-choco icon-bg-latte">
            <i class="bi bi-tags-fill"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjeta Principal con Tabla de Usuarios -->
    <div class="card-choco">
      <div class="card-header-choco">
        <h2 class="h5 fw-bold mb-0" style="color: var(--color-choco-dark);">
          <i class="bi bi-list-nested me-2 text-muted"></i> Usuarios Registrados
        </h2>
        <div style="max-width: 320px; width: 100%;">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0" style="border-color: var(--color-cream-border);">
              <i class="bi bi-search text-muted"></i>
            </span>
            <input 
              type="text" 
              id="tabla-buscador" 
              class="form-control form-control-choco border-start-0" 
              placeholder="Buscar por usuario, email o rol..."
            >
          </div>
        </div>
      </div>

      <div class="p-0">
        <?php if (empty($usuarios)): ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-person-slash display-4 opacity-50 mb-3 d-block"></i>
            <h5 class="fw-bold text-dark">No hay usuarios registrados</h5>
            <p>Utiliza el botón superior para crear el primer usuario del sistema.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table-choco">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>Nombre Completo</th>
                  <th>Correo Electrónico</th>
                  <th>Rol Asignado</th>
                  <th>Fecha Registro</th>
                  <th class="text-end">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $u): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="avatar-choco" style="width: 32px; height: 32px; font-size: 0.75rem;">
                          <?= strtoupper(substr($u['username'], 0, 2)) ?>
                        </div>
                        <div>
                          <strong class="d-block text-dark">@<?= htmlspecialchars($u['username']) ?></strong>
                          <?php if ((int)$u['id'] === (int)$usuarioActual['id']): ?>
                            <span class="badge rounded-pill bg-secondary" style="font-size: 0.65rem;">Tú</span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="fw-medium"><?= htmlspecialchars($u['name'] . ' ' . $u['last_name']) ?></span>
                    </td>
                    <td>
                      <a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="text-muted text-decoration-none">
                        <?= htmlspecialchars($u['email']) ?>
                      </a>
                    </td>
                    <td>
                      <?php 
                        $badgeClass = 'badge-role-custom';
                        if ((int)$u['role'] === 1) $badgeClass = 'badge-role-admin';
                        elseif ((int)$u['role'] === 2) $badgeClass = 'badge-role-editor';
                        elseif ((int)$u['role'] === 3) $badgeClass = 'badge-role-guest';
                      ?>
                      <span class="badge-role <?= $badgeClass ?>">
                        <i class="bi bi-shield-fill-check"></i>
                        <?= htmlspecialchars($u['role_name']) ?>
                      </span>
                    </td>
                    <td class="text-muted small">
                      <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?>
                    </td>
                    <td>
                      <div class="d-flex align-items-center justify-content-end gap-1">
                        <!-- Botón Editar (GET solo lectura para precarga) -->
                        <a 
                          href="formulario.php?id=<?= $u['id'] ?>" 
                          class="btn btn-cream btn-sm"
                          title="Editar usuario"
                        >
                          <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <!-- Botón Eliminar (Envío por POST con confirmación) -->
                        <form 
                          method="POST" 
                          action="eliminar.php" 
                          onsubmit="return confirmarEliminacion('¿Deseas eliminar permanentemente al usuario \'<?= htmlspecialchars(addslashes($u['username'])) ?>\'?');"
                          class="d-inline"
                        >
                          <input type="hidden" name="id" value="<?= $u['id'] ?>">
                          <button 
                            type="submit" 
                            class="btn btn-outline-danger-choco btn-sm"
                            title="Eliminar usuario"
                            <?= ((int)$u['id'] === (int)$usuarioActual['id']) ? 'disabled title="No puedes eliminarte a ti mismo"' : '' ?>
                          >
                            <i class="bi bi-trash3-fill"></i>
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
    </div>

  </main>

  <!-- Pie de Página -->
  <footer class="footer-choco">
    <div class="container-xl">
      <p class="mb-0">&copy; <?= date('Y') ?> Sistema de Gestión de Usuarios y Roles — Sprint 2</p>
    </div>
  </footer>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Scripts Personalizados -->
  <script src="js/main.js"></script>
</body>
</html>
