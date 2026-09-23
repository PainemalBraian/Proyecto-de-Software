<?php
/**
 * ==============================================================================
 * Formulario Unificado de Alta y Edición de Usuarios
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/usuarios.php';
require_once __DIR__ . '/backend/roles.php';

// Proteger la ruta con autenticación
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

// 1. Manejo de lectura vía GET (Modo Edición o Modo Alta)
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
        <a href="index.php" class="nav-link active">👥 Usuarios</a>
        <a href="roles.php" class="nav-link">🛡️ Roles</a>
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

  <!-- Contenido Principal -->
  <main class="main-content">
    
    <div class="page-header">
      <div>
        <h1 class="page-title"><?= $esEdicion ? 'Editar Usuario' : 'Registrar Nuevo Usuario' ?></h1>
        <p class="page-subtitle">
          <?= $esEdicion ? "Modifica los datos del usuario #{$idUsuario} en el sistema" : 'Completa la información para dar de alta un usuario en el sistema' ?>
        </p>
      </div>
      <div>
        <a href="index.php" class="btn btn-secondary">
          ⬅️ Volver al Listado
        </a>
      </div>
    </div>

    <!-- Error General si existiera -->
    <?php if (isset($errores['general'])): ?>
      <div class="alert alert-error">
        <div class="alert-content">
          <span>⚠️ <?= htmlspecialchars($errores['general']) ?></span>
        </div>
        <button type="button" class="alert-close" aria-label="Cerrar">&times;</button>
      </div>
    <?php endif; ?>

    <!-- Tarjeta del Formulario -->
    <div class="card" style="max-width: 780px; margin: 0 auto;">
      <div class="card-header">
        <h2 class="card-title">
          <?= $esEdicion ? '✏️ Formulario de Edición' : '📝 Datos del Usuario' ?>
        </h2>
      </div>

      <div class="card-body">
        <!-- Formulario (Envío obligatorio por POST) -->
        <form 
          method="POST" 
          action="formulario.php" 
          data-modo="<?= $esEdicion ? 'edicion' : 'alta' ?>" 
          onsubmit="return validarFormularioUsuario(this);" 
          novalidate
        >
          <!-- Input oculto para identificar el registro en edición -->
          <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($idUsuario) ?>">
          <?php endif; ?>

          <div class="form-grid">
            <!-- Campo: Nombre -->
            <div class="form-group">
              <label class="form-label" for="name">Nombre <span class="required">*</span></label>
              <input 
                type="text" 
                id="name" 
                name="name" 
                class="form-input <?= isset($errores['name']) ? 'is-invalid' : '' ?>" 
                placeholder="ej. Carlos" 
                value="<?= htmlspecialchars($datos['name']) ?>" 
                required
              >
              <?php if (isset($errores['name'])): ?>
                <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['name']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Campo: Apellido -->
            <div class="form-group">
              <label class="form-label" for="last_name">Apellido <span class="required">*</span></label>
              <input 
                type="text" 
                id="last_name" 
                name="last_name" 
                class="form-input <?= isset($errores['last_name']) ? 'is-invalid' : '' ?>" 
                placeholder="ej. Gómez" 
                value="<?= htmlspecialchars($datos['last_name']) ?>" 
                required
              >
              <?php if (isset($errores['last_name'])): ?>
                <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['last_name']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-grid">
            <!-- Campo: Nombre de Usuario -->
            <div class="form-group">
              <label class="form-label" for="username">Nombre de Usuario <span class="required">*</span></label>
              <input 
                type="text" 
                id="username" 
                name="username" 
                class="form-input <?= isset($errores['username']) ? 'is-invalid' : '' ?>" 
                placeholder="ej. cgomez" 
                value="<?= htmlspecialchars($datos['username']) ?>" 
                required
              >
              <span class="form-helper">Solo letras, números, puntos y guiones.</span>
              <?php if (isset($errores['username'])): ?>
                <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['username']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Campo: Rol Asignado -->
            <div class="form-group">
              <label class="form-label" for="role">Rol en el Sistema <span class="required">*</span></label>
              <select id="role" name="role" class="form-select <?= isset($errores['role']) ? 'is-invalid' : '' ?>" required>
                <?php foreach ($roles as $rol): ?>
                  <option value="<?= $rol['id'] ?>" <?= ((int)$datos['role'] === (int)$rol['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($rol['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errores['role'])): ?>
                <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['role']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Campo: Correo Electrónico -->
          <div class="form-group">
            <label class="form-label" for="email">Correo Electrónico <span class="required">*</span></label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              class="form-input <?= isset($errores['email']) ? 'is-invalid' : '' ?>" 
              placeholder="ej. carlos.gomez@empresa.com" 
              value="<?= htmlspecialchars($datos['email']) ?>" 
              required
            >
            <?php if (isset($errores['email'])): ?>
              <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['email']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Campo: Contraseña -->
          <div class="form-group">
            <label class="form-label" for="password">
              Contraseña <?= $esEdicion ? '<span style="font-weight: 400; color: var(--color-text-muted);">(dejar en blanco para mantener la actual)</span>' : '<span class="required">*</span>' ?>
            </label>
            <div class="input-wrapper">
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input <?= isset($errores['password']) ? 'is-invalid' : '' ?>" 
                placeholder="<?= $esEdicion ? '••••••••' : 'Mínimo 6 caracteres' ?>" 
                <?= $esEdicion ? '' : 'required' ?>
              >
              <button type="button" class="input-icon-btn btn-toggle-password" data-target="password" title="Mostrar contraseña">👁️</button>
            </div>
            <?php if (isset($errores['password'])): ?>
              <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['password']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Botones de Acción -->
          <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border);">
            <a href="index.php" class="btn btn-secondary">
              Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
              💾 <?= $esEdicion ? 'Guardar Cambios' : 'Crear Usuario' ?>
            </button>
          </div>

        </form>
      </div>
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
