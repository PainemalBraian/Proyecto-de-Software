<?php
/**
 * ==============================================================================
 * Formulario de Alta y Edición de Usuarios — Tema Chocolate & Crema con Bootstrap 5
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/usuarios.php';
require_once __DIR__ . '/backend/roles.php';

requiereAutenticacion();

$usuarioActual = obtenerUsuarioLogueado();
$roles = listarRoles();

$errores = [];
$esEdicion = false;
$idUsuario = null;

$datos = [
  'name' => '',
  'last_name' => '',
  'username' => '',
  'email' => '',
  'role' => !empty($roles) ? $roles[0]['id'] : ''
];

// 1. Manejo de precarga vía GET (Modo Edición)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $usuarioExistente = obtenerUsuarioPorId($id);

    if ($usuarioExistente) {
      $esEdicion = true;
      $idUsuario = $usuarioExistente['id'];
      $datos = [
        'name' => $usuarioExistente['name'],
        'last_name' => $usuarioExistente['last_name'],
        'username' => $usuarioExistente['username'],
        'email' => $usuarioExistente['email'],
        'role' => $usuarioExistente['role']
      ];
    } else {
      setMensajeFlash('error', 'El usuario solicitado no fue encontrado.');
      header('Location: index.php');
      exit;
    }
  }
}

// 2. Manejo de guardado vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idPost = isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null;
  $esEdicion = ($idPost !== null);
  $idUsuario = $idPost;

  $datos = [
    'name' => $_POST['name'] ?? '',
    'last_name' => $_POST['last_name'] ?? '',
    'username' => $_POST['username'] ?? '',
    'email' => $_POST['email'] ?? '',
    'role' => $_POST['role'] ?? '',
    'password' => $_POST['password'] ?? ''
  ];

  if ($esEdicion) {
    // Proceso de Actualización
    $resultado = actualizarUsuario(
      $idUsuario,
      $datos['username'],
      $datos['email'],
      $datos['name'],
      $datos['last_name'],
      $datos['role'],
      $datos['password']
    );

    if ($resultado['exito']) {
      setMensajeFlash('exito', '¡Usuario actualizado correctamente!');
      header('Location: index.php');
      exit;
    } else {
      $errores = $resultado['errores'];
    }
  } else {
    // Proceso de Alta
    $resultado = crearUsuario(
      $datos['username'],
      $datos['email'],
      $datos['name'],
      $datos['last_name'],
      $datos['role'],
      $datos['password']
    );

    if ($resultado['exito']) {
      setMensajeFlash('exito', '¡Nuevo usuario creado exitosamente!');
      header('Location: index.php');
      exit;
    } else {
      $errores = $resultado['errores'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $esEdicion ? 'Editar Usuario' : 'Nuevo Usuario' ?> — Panel de Control</title>
  
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

  <!-- Contenido Principal -->
  <main class="container-xl my-4 flex-grow-1">
    
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h2 fw-bold mb-1" style="color: var(--color-choco-dark);">
          <?= $esEdicion ? 'Editar Usuario' : 'Registrar Nuevo Usuario' ?>
        </h1>
        <p class="text-muted mb-0">
          <?= $esEdicion ? "Modifica los datos del usuario #{$idUsuario} en el sistema" : 'Completa la información para dar de alta un usuario en el sistema' ?>
        </p>
      </div>
      <div>
        <a href="index.php" class="btn btn-cream">
          <i class="bi bi-arrow-left me-1"></i> Volver al Listado
        </a>
      </div>
    </div>

    <!-- Error General si existiera -->
    <?php if (isset($errores['general'])): ?>
      <div class="alert-choco alert-choco-error mb-4">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span><?= htmlspecialchars($errores['general']) ?></span>
        </div>
        <button type="button" class="btn-close alert-close" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <!-- Tarjeta del Formulario -->
    <div class="card-choco mx-auto" style="max-width: 780px;">
      <div class="card-header-choco">
        <h2 class="h5 fw-bold mb-0" style="color: var(--color-choco-dark);">
          <i class="bi bi-person-lines-fill me-2 text-muted"></i>
          <?= $esEdicion ? 'Formulario de Edición' : 'Datos del Usuario' ?>
        </h2>
      </div>

      <div class="p-4">
        <form 
          method="POST" 
          action="formulario.php" 
          data-modo="<?= $esEdicion ? 'edicion' : 'alta' ?>" 
          onsubmit="return validarFormularioUsuario(this);" 
          novalidate
        >
          <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($idUsuario) ?>">
          <?php endif; ?>

          <div class="row g-3 mb-3">
            <!-- Campo: Nombre -->
            <div class="col-sm-6">
              <label class="form-label-choco" for="name">
                Nombre <span class="text-danger">*</span>
              </label>
              <input 
                type="text" 
                id="name" 
                name="name" 
                class="form-control form-control-choco <?= isset($errores['name']) ? 'is-invalid' : '' ?>" 
                placeholder="ej. Carlos" 
                value="<?= htmlspecialchars($datos['name']) ?>" 
                required
              >
              <?php if (isset($errores['name'])): ?>
                <div class="text-danger small mt-1 fw-medium">
                  <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['name']) ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Campo: Apellido -->
            <div class="col-sm-6">
              <label class="form-label-choco" for="last_name">
                Apellido <span class="text-danger">*</span>
              </label>
              <input 
                type="text" 
                id="last_name" 
                name="last_name" 
                class="form-control form-control-choco <?= isset($errores['last_name']) ? 'is-invalid' : '' ?>" 
                placeholder="ej. Gómez" 
                value="<?= htmlspecialchars($datos['last_name']) ?>" 
                required
              >
              <?php if (isset($errores['last_name'])): ?>
                <div class="text-danger small mt-1 fw-medium">
                  <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['last_name']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <!-- Campo: Nombre de Usuario -->
            <div class="col-sm-6">
              <label class="form-label-choco" for="username">
                Nombre de Usuario <span class="text-danger">*</span>
              </label>
              <input 
                type="text" 
                id="username" 
                name="username" 
                class="form-control form-control-choco <?= isset($errores['username']) ? 'is-invalid' : '' ?>" 
                placeholder="ej. cgomez" 
                value="<?= htmlspecialchars($datos['username']) ?>" 
                required
              >
              <div class="form-text small" style="color: var(--color-text-muted);">Solo letras, números, puntos y guiones.</div>
              <?php if (isset($errores['username'])): ?>
                <div class="text-danger small mt-1 fw-medium">
                  <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['username']) ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Campo: Rol Asignado -->
            <div class="col-sm-6">
              <label class="form-label-choco" for="role">
                Rol en el Sistema <span class="text-danger">*</span>
              </label>
              <select id="role" name="role" class="form-select form-select-choco <?= isset($errores['role']) ? 'is-invalid' : '' ?>" required>
                <?php foreach ($roles as $rol): ?>
                  <option value="<?= $rol['id'] ?>" <?= ((int)$datos['role'] === (int)$rol['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($rol['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errores['role'])): ?>
                <div class="text-danger small mt-1 fw-medium">
                  <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['role']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Campo: Correo Electrónico -->
          <div class="mb-3">
            <label class="form-label-choco" for="email">
              Correo Electrónico <span class="text-danger">*</span>
            </label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              class="form-control form-control-choco <?= isset($errores['email']) ? 'is-invalid' : '' ?>" 
              placeholder="ej. carlos.gomez@empresa.com" 
              value="<?= htmlspecialchars($datos['email']) ?>" 
              required
            >
            <?php if (isset($errores['email'])): ?>
              <div class="text-danger small mt-1 fw-medium">
                <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['email']) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Campo: Contraseña -->
          <div class="mb-4">
            <label class="form-label-choco" for="password">
              Contraseña <?= $esEdicion ? '<span class="text-muted fw-normal">(dejar en blanco para mantener la actual)</span>' : '<span class="text-danger">*</span>' ?>
            </label>
            <div class="position-relative d-flex align-items-center">
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control form-control-choco pe-5 <?= isset($errores['password']) ? 'is-invalid' : '' ?>" 
                placeholder="<?= $esEdicion ? '••••••••' : 'Mínimo 6 caracteres' ?>" 
                <?= $esEdicion ? '' : 'required' ?>
              >
              <button type="button" class="input-icon-btn-choco btn-toggle-password" data-target="password" title="Mostrar contraseña">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <?php if (isset($errores['password'])): ?>
              <div class="text-danger small mt-1 fw-medium">
                <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['password']) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Botones de Acción -->
          <div class="d-flex justify-content-end gap-2 pt-3 border-top" style="border-color: var(--color-cream-border) !important;">
            <a href="index.php" class="btn btn-cream">
              Cancelar
            </a>
            <button type="submit" class="btn btn-choco">
              <i class="bi bi-floppy-fill me-1"></i> <?= $esEdicion ? 'Guardar Cambios' : 'Crear Usuario' ?>
            </button>
          </div>

        </form>
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
