<?php
/**
 * ==============================================================================
 * Módulo ABM de Roles — Tema Chocolate & Crema con Bootstrap 5
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

// 1. Manejo de Acciones vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? 'guardar';

  if ($accion === 'eliminar') {
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
    $idPost = isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    $datosForm = ['name' => $name, 'description' => $description];

    if ($idPost) {
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
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- Estilos Personalizados: Chocolate & Crema -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <!-- Barra Superior -->
  <header class="topbar-choco">
    <div class="container-xl d-flex align-items-center justify-content-between py-2">
      <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
        <span class="brand-icon-choco">☕</span>
        <span class="fw-bold fs-5" style="color: var(--color-choco-dark);">Panel de Gestión</span>
      </a>

      <nav class="d-flex align-items-center gap-3">
        <a href="index.php" class="nav-link-choco">
          <i class="bi bi-people-fill"></i> Usuarios
        </a>
        <a href="roles.php" class="nav-link-choco active">
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
    
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h2 fw-bold mb-1" style="color: var(--color-choco-dark);">Módulo de Roles</h1>
        <p class="text-muted mb-0">Define los perfiles de acceso y permisos para los usuarios del sistema</p>
      </div>
      <div>
        <a href="index.php" class="btn btn-cream">
          <i class="bi bi-people-fill me-1"></i> Ir a Usuarios
        </a>
      </div>
    </div>

    <!-- Alerta Flash -->
    <?php if ($mensajeFlash): ?>
      <div class="alert-choco alert-choco-<?= htmlspecialchars($mensajeFlash['tipo']) ?> mb-4">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-info-circle-fill"></i>
          <span><?= htmlspecialchars($mensajeFlash['mensaje']) ?></span>
        </div>
        <button type="button" class="btn-close alert-close" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <!-- Layout en 2 Columnas con Bootstrap Grid -->
    <div class="row g-4">
      
      <!-- Columna 1: Listado de Roles -->
      <div class="col-lg-7">
        <div class="card-choco">
          <div class="card-header-choco">
            <h2 class="h5 fw-bold mb-0" style="color: var(--color-choco-dark);">
              <i class="bi bi-shield-check me-2 text-muted"></i> Roles Registrados (<?= count($roles) ?>)
            </h2>
          </div>
          <div class="p-0">
            <?php if (empty($roles)): ?>
              <div class="text-center py-5 text-muted">
                <i class="bi bi-shield-slash display-4 opacity-50 mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">No hay roles registrados</h5>
                <p>Utiliza el formulario contiguo para crear tu primer rol.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table-choco">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Nombre del Rol</th>
                      <th>Descripción</th>
                      <th class="text-end">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($roles as $rol): ?>
                      <tr>
                        <td class="fw-bold text-muted">#<?= $rol['id'] ?></td>
                        <td>
                          <strong style="color: var(--color-choco-primary);"><?= htmlspecialchars($rol['name']) ?></strong>
                        </td>
                        <td class="text-muted small">
                          <?= htmlspecialchars($rol['description'] ?? 'Sin descripción') ?>
                        </td>
                        <td>
                          <div class="d-flex align-items-center justify-content-end gap-1">
                            <!-- Botón Editar (Carga por GET) -->
                            <a 
                              href="roles.php?id=<?= $rol['id'] ?>" 
                              class="btn btn-cream btn-sm"
                              title="Editar rol"
                            >
                              <i class="bi bi-pencil-square"></i>
                            </a>

                            <!-- Botón Eliminar (POST con confirmación) -->
                            <form 
                              method="POST" 
                              action="roles.php" 
                              onsubmit="return confirmarEliminacion('¿Deseas eliminar el rol \'<?= htmlspecialchars(addslashes($rol['name'])) ?>\'? Si tiene usuarios asignados, el sistema no permitirá borrarlo.');"
                              class="d-inline"
                            >
                              <input type="hidden" name="accion" value="eliminar">
                              <input type="hidden" name="id" value="<?= $rol['id'] ?>">
                              <button 
                                type="submit" 
                                class="btn btn-outline-danger-choco btn-sm"
                                title="Eliminar rol"
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
      </div>

      <!-- Columna 2: Formulario Compacto de Alta / Edición -->
      <div class="col-lg-5">
        <div class="card-choco">
          <div class="card-header-choco">
            <h2 class="h5 fw-bold mb-0" style="color: var(--color-choco-dark);">
              <i class="bi bi-plus-circle-fill me-2 text-muted"></i>
              <?= $modoEdicion ? "Editar Rol #{$idRolEdicion}" : 'Nuevo Rol' ?>
            </h2>
          </div>
          <div class="p-4">
            <form method="POST" action="roles.php" novalidate>
              <input type="hidden" name="accion" value="guardar">
              <?php if ($modoEdicion): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($idRolEdicion) ?>">
              <?php endif; ?>

              <!-- Campo: Nombre del Rol -->
              <div class="mb-3">
                <label class="form-label-choco" for="role_name">
                  Nombre del Rol <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  id="role_name" 
                  name="name" 
                  class="form-control form-control-choco <?= isset($errores['name']) ? 'is-invalid' : '' ?>" 
                  placeholder="ej. Supervisor, Auditor"
                  value="<?= htmlspecialchars($datosForm['name']) ?>" 
                  required
                >
                <?php if (isset($errores['name'])): ?>
                  <div class="text-danger small mt-1 fw-medium">
                    <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['name']) ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Campo: Descripción -->
              <div class="mb-4">
                <label class="form-label-choco" for="role_description">
                  Descripción
                </label>
                <textarea 
                  id="role_description" 
                  name="description" 
                  rows="3" 
                  class="form-control form-control-choco <?= isset($errores['description']) ? 'is-invalid' : '' ?>" 
                  placeholder="Breve descripción de las funciones y permisos"
                ><?= htmlspecialchars($datosForm['description']) ?></textarea>
                <?php if (isset($errores['description'])): ?>
                  <div class="text-danger small mt-1 fw-medium">
                    <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['description']) ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Botones de Acción -->
              <div class="d-flex gap-2">
                <?php if ($modoEdicion): ?>
                  <a href="roles.php" class="btn btn-cream flex-fill">
                    Cancelar
                  </a>
                <?php endif; ?>
                <button type="submit" class="btn btn-choco flex-fill">
                  <i class="bi bi-floppy-fill me-1"></i> <?= $modoEdicion ? 'Actualizar Rol' : 'Crear Rol' ?>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>

  </main>

  <footer class="footer-choco">
    <div class="container-xl">
      <p class="mb-0">&copy; <?= date('Y') ?> Sistema de Gestión de Usuarios y Roles — Sprint 2</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
