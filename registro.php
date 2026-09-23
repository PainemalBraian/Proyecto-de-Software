<?php
/**
 * ==============================================================================
 * Pantalla de Registro de Nuevos Usuarios
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/roles.php';

// Si el usuario ya está autenticado, redirigir al panel principal
if (estaAutenticado()) {
  header('Location: index.php');
  exit;
}

$errores = [];
$datos = [
  'name' => '',
  'last_name' => '',
  'username' => '',
  'email' => '',
  'role' => 3 // Invitado por defecto
];

// Obtener la lista de roles para el selector
$rolesDisponibles = listarRoles();

// Procesar formulario enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $datos = [
    'name' => $_POST['name'] ?? '',
    'last_name' => $_POST['last_name'] ?? '',
    'username' => $_POST['username'] ?? '',
    'email' => $_POST['email'] ?? '',
    'password' => $_POST['password'] ?? '',
    'confirm_password' => $_POST['confirm_password'] ?? '',
    'role' => $_POST['role'] ?? 3
  ];

  $resultado = registrarNuevoUsuario($datos);

  if ($resultado['exito']) {
    setMensajeFlash('exito', $resultado['mensaje']);
    header('Location: login.php');
    exit;
  } else {
    $errores = $resultado['errores'];
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Cuenta — Panel de Gestión</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Estilos Globales -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="auth-wrapper">
    <div class="auth-card" style="max-width: 520px;">
      <div class="auth-header">
        <div class="brand-icon">🚀</div>
        <h1 class="auth-title">Crear Cuenta</h1>
        <p class="auth-subtitle">Regístrate para comenzar a gestionar el sistema</p>
      </div>

      <!-- Error General de Registro si aplica -->
      <?php if (isset($errores['general'])): ?>
        <div class="alert alert-error">
          <div class="alert-content">
            <span>⚠️ <?= htmlspecialchars($errores['general']) ?></span>
          </div>
          <button type="button" class="alert-close" aria-label="Cerrar">&times;</button>
        </div>
      <?php endif; ?>

      <!-- Formulario de Registro (Envío por POST) -->
      <form method="POST" action="registro.php" data-modo="alta" onsubmit="return validarFormularioUsuario(this);" novalidate>
        
        <div class="form-grid">
          <!-- Campo: Nombre -->
          <div class="form-group">
            <label class="form-label" for="name">Nombre <span class="required">*</span></label>
            <input 
              type="text" 
              id="name" 
              name="name" 
              class="form-input <?= isset($errores['name']) ? 'is-invalid' : '' ?>" 
              placeholder="ej. Juan"
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
              placeholder="ej. Pérez"
              value="<?= htmlspecialchars($datos['last_name']) ?>" 
              required
            >
            <?php if (isset($errores['last_name'])): ?>
              <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['last_name']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-grid">
          <!-- Campo: Usuario -->
          <div class="form-group">
            <label class="form-label" for="username">Nombre de Usuario <span class="required">*</span></label>
            <input 
              type="text" 
              id="username" 
              name="username" 
              class="form-input <?= isset($errores['username']) ? 'is-invalid' : '' ?>" 
              placeholder="ej. juanperez"
              value="<?= htmlspecialchars($datos['username']) ?>" 
              required
            >
            <?php if (isset($errores['username'])): ?>
              <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['username']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Campo: Rol Asignado -->
          <div class="form-group">
            <label class="form-label" for="role">Rol Inicial <span class="required">*</span></label>
            <select id="role" name="role" class="form-select <?= isset($errores['role']) ? 'is-invalid' : '' ?>" required>
              <?php foreach ($rolesDisponibles as $rol): ?>
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
            placeholder="ej. juan@correo.com"
            value="<?= htmlspecialchars($datos['email']) ?>" 
            required
          >
          <?php if (isset($errores['email'])): ?>
            <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['email']) ?></div>
          <?php endif; ?>
        </div>

        <div class="form-grid">
          <!-- Campo: Contraseña -->
          <div class="form-group">
            <label class="form-label" for="password">Contraseña <span class="required">*</span></label>
            <div class="input-wrapper">
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input <?= isset($errores['password']) ? 'is-invalid' : '' ?>" 
                placeholder="Mínimo 6 caracteres" 
                required
              >
              <button type="button" class="input-icon-btn btn-toggle-password" data-target="password" title="Mostrar contraseña">👁️</button>
            </div>
            <?php if (isset($errores['password'])): ?>
              <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['password']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Campo: Confirmar Contraseña -->
          <div class="form-group">
            <label class="form-label" for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
            <div class="input-wrapper">
              <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                class="form-input <?= isset($errores['confirm_password']) ? 'is-invalid' : '' ?>" 
                placeholder="Repite la clave" 
                required
              >
              <button type="button" class="input-icon-btn btn-toggle-password" data-target="confirm_password" title="Mostrar contraseña">👁️</button>
            </div>
            <?php if (isset($errores['confirm_password'])): ?>
              <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['confirm_password']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Botón de Registro -->
        <div style="margin-top: 1.5rem;">
          <button type="submit" class="btn btn-primary btn-block">
            Registrarme
          </button>
        </div>
      </form>

      <!-- Enlace hacia Login -->
      <div class="auth-footer">
        ¿Ya tienes una cuenta registrada? <a href="login.php">Inicia sesión</a>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="js/main.js"></script>
</body>
</html>
