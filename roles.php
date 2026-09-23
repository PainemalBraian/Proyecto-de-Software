<?php
/**
 * ==============================================================================
 * Módulo ABM de Roles (Gestión Completa en Pantalla Única)
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/roles.php';

requiereAutenticacion();

$usuarioActual = obtenerUsuarioLogueado();
$mensajeFlash = obtenerMensajeFlash();

$errores = [];
$modoEdicion = false;
$idRolEdicion = null;

$datosForm = [
  'name' => '',
  'description' => ''
];

// 1. Manejo de Acciones vía POST (Crear, Editar, Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? 'guardar';

  if ($accion === 'eliminar') {
    // Proceso de Eliminación
    $idEliminar = (int)($_POST['id'] ?? 0);
    $resultado = eliminarRol($idEliminar);

    if ($resultado['exito']) {
      setMensajeFlash('exito', $resultado['mensaje']);
    } else {
      setMensajeFlash('error', $resultado['mensaje']);
    }
    header('Location: roles.php');
    exit;
  } 
  elseif ($accion === 'guardar') {
    // Proceso de Guardado (Alta o Edición)
    $idPost = isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    $datosForm = ['name' => $name, 'description' => $description];

    if ($idPost) {
      // Actualizar
      $resultado = actualizarRol($idPost, $name, $description);
      if ($resultado['exito']) {
        setMensajeFlash('exito', $resultado['mensaje']);
        header('Location: roles.php');
        exit;
      } else {
        $modoEdicion = true;
        $idRolEdicion = $idPost;
        $errores = $resultado['errores'];
      }
    } else {
      // Crear
      $resultado = crearRol($name, $description);
      if ($resultado['exito']) {
        setMensajeFlash('exito', $resultado['mensaje']);
        header('Location: roles.php');
        exit;
      } else {
        $errores = $resultado['errores'];
      }
    }
  }
}

// 2. Manejo de precarga para edición vía GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
  $idGet = (int)$_GET['id'];
  $rolEncontrado = obtenerRolPorId($idGet);
  if ($rolEncontrado) {
    $modoEdicion = true;
    $idRolEdicion = $rolEncontrado['id'];
    $datosForm = [
      'name' => $rolEncontrado['name'],
      'description' => $rolEncontrado['description'] ?? ''
    ];
  } else {
    setMensajeFlash('error', 'El rol solicitado para edición no existe.');
    header('Location: roles.php');
    exit;
  }
}

// Obtener lista actualizada de roles
$roles = listarRoles();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Roles — Panel de Control</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Estilos Globales -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <!-- Barra Superior -->
  <header class="topbar">
    <div class="topbar-container">
      <a href="index.php" class="brand-logo">
        <span class="brand-icon">⚡</span>
        <span>Panel de Gestión</span>
      </a>

      <nav class="topbar-nav">
        <a href="index.php" class="nav-link">👥 Usuarios</a>
        <a href="roles.php" class="nav-link active">🛡️ Roles</a>
        <div class="user-profile-badge">
          <div class="avatar"><?= strtoupper(substr($usuarioActual['username'], 0, 2)) ?></div>
          <div class="user-meta">
            <span class="user-name"><?= htmlspecialchars($usuarioActual['nombre_completo']) ?></span>
            <span class="user-role-label"><?= htmlspecialchars($usuarioActual['rol_nombre']) ?></span>
          </div>
          <a href="logout.php" class="btn btn-secondary btn-sm" style="margin-left: 0.5rem;">Salir 🚪</a>
        </div>
      </nav>
    </div>
  </header>

  <!-- Contenedor Principal -->
  <main class="main-content">
    
    <div class="page-header">
      <div>
        <h1 class="page-title">Módulo de Roles</h1>
        <p class="page-subtitle">Define los perfiles de acceso y permisos para los usuarios del sistema</p>
      </div>
      <div>
        <a href="index.php" class="btn btn-secondary">
          👥 Ir a Usuarios
        </a>
      </div>
    </div>

    <!-- Alerta Flash si existe -->
    <?php if ($mensajeFlash): ?>
      <div class="alert alert-<?= htmlspecialchars($mensajeFlash['tipo']) ?>">
        <div class="alert-content">
          <span><?= htmlspecialchars($mensajeFlash['mensaje']) ?></span>
        </div>
        <button type="button" class="alert-close" aria-label="Cerrar">&times;</button>
      </div>
    <?php endif; ?>

    <!-- Layout de Dos Columnas: Tabla a la Izquierda y Formulario a la Derecha -->
    <div class="roles-layout">
      
      <!-- Columna 1: Listado de Roles -->
      <section class="card">
        <div class="card-header">
          <h2 class="card-title">Roles Registrados (<?= count($roles) ?>)</h2>
        </div>
        <div class="card-body" style="padding: 0;">
          <?php if (empty($roles)): ?>
            <div class="empty-state">
              <div class="empty-state-icon">🛡️</div>
              <h3 class="empty-state-title">No hay roles registrados</h3>
              <p>Utiliza el formulario contiguo para crear tu primer rol.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nombre del Rol</th>
                    <th>Descripción</th>
                    <th style="text-align: right;">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($roles as $rol): ?>
                    <tr>
                      <td style="font-weight: 700; color: var(--color-text-muted);">#<?= $rol['id'] ?></td>
                      <td>
                        <strong style="color: var(--color-primary);"><?= htmlspecialchars($rol['name']) ?></strong>
                      </td>
                      <td style="color: var(--color-text-muted); font-size: 0.875rem;">
                        <?= htmlspecialchars($rol['description'] ?? 'Sin descripción') ?>
                      </td>
                      <td>
                        <div class="actions-group" style="justify-content: flex-end;">
                          <!-- Botón Editar (Carga por GET de solo lectura) -->
                          <a 
                            href="roles.php?id=<?= $rol['id'] ?>" 
                            class="btn btn-secondary btn-sm"
                            title="Editar rol"
                          >
                            ✏️ Editar
                          </a>

                          <!-- Botón Eliminar (Envío por POST con confirmación) -->
                          <form 
                            method="POST" 
                            action="roles.php" 
                            onsubmit="return confirmarEliminacion('¿Deseas eliminar el rol \'<?= htmlspecialchars(addslashes($rol['name'])) ?>\'? Si tiene usuarios asignados, el sistema no permitirá borrarlo.');"
                            style="display: inline;"
                          >
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $rol['id'] ?>">
                            <button 
                              type="submit" 
                              class="btn btn-outline-danger btn-sm"
                              title="Eliminar rol"
                              <?= ((int)$rol['id'] === 1) ? 'title="Rol Administrador principal protegido"' : '' ?>
                            >
                              🗑️
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

      <!-- Columna 2: Formulario Compacto de Alta / Edición -->
      <section class="card">
        <div class="card-header">
          <h2 class="card-title">
            <?= $modoEdicion ? "✏️ Editar Rol #{$idRolEdicion}" : '➕ Nuevo Rol' ?>
          </h2>
        </div>
        <div class="card-body">
          <form method="POST" action="roles.php" novalidate>
            <input type="hidden" name="accion" value="guardar">
            <?php if ($modoEdicion): ?>
              <input type="hidden" name="id" value="<?= htmlspecialchars($idRolEdicion) ?>">
            <?php endif; ?>

            <!-- Campo: Nombre del Rol -->
            <div class="form-group">
              <label class="form-label" for="role_name">Nombre del Rol <span class="required">*</span></label>
              <input 
                type="text" 
                id="role_name" 
                name="name" 
                class="form-input <?= isset($errores['name']) ? 'is-invalid' : '' ?>" 
                placeholder="ej. Supervisor, Auditor"
                value="<?= htmlspecialchars($datosForm['name']) ?>" 
                required
              >
              <?php if (isset($errores['name'])): ?>
                <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['name']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Campo: Descripción -->
            <div class="form-group">
              <label class="form-label" for="role_description">Descripción</label>
              <textarea 
                id="role_description" 
                name="description" 
                rows="3" 
                class="form-textarea <?= isset($errores['description']) ? 'is-invalid' : '' ?>" 
                placeholder="Breve descripción de las funciones y permisos de este rol"
              ><?= htmlspecialchars($datosForm['description']) ?></textarea>
              <?php if (isset($errores['description'])): ?>
                <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['description']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Botones de Acción del Formulario -->
            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
              <?php if ($modoEdicion): ?>
                <a href="roles.php" class="btn btn-secondary" style="flex: 1;">
                  Cancelar
                </a>
              <?php endif; ?>
              <button type="submit" class="btn btn-primary" style="flex: 2;">
                💾 <?= $modoEdicion ? 'Actualizar Rol' : 'Crear Rol' ?>
              </button>
            </div>
          </form>
        </div>
      </section>

    </div>

  </main>

  <footer class="footer">
    <div class="topbar-container" style="justify-content: center;">
      <p>&copy; <?= date('Y') ?> Sistema de Gestión de Usuarios y Roles — Sprint 2</p>
    </div>
  </footer>

  <script src="js/main.js"></script>
</body>
</html>
