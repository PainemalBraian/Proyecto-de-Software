<?php
/**
 * ==============================================================================
 * Pantalla de Registro de Nuevos Usuarios — Tema Chocolate & Crema con Bootstrap 5
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
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- Estilos Personalizados: Chocolate & Crema -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="auth-wrapper-choco py-4">
    <div class="auth-card-choco" style="max-width: 540px;">
      
      <!-- Cabecera -->
      <div class="text-center mb-4">
        <div class="brand-icon-choco mx-auto mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
          🚀
        </div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--color-choco-dark);">Crear Cuenta</h1>
        <p class="text-muted small">Regístrate para comenzar a gestionar el sistema</p>
      </div>

      <!-- Error General de Registro si aplica -->
      <?php if (isset($errores['general'])): ?>
        <div class="alert-choco alert-choco-error mb-4">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($errores['general']) ?></span>
          </div>
          <button type="button" class="btn-close alert-close" aria-label="Cerrar"></button>
        </div>
      <?php endif; ?>

      <!-- Formulario de Registro (POST) -->
      <form method="POST" action="registro.php" data-modo="alta" onsubmit="return validarFormularioUsuario(this);" novalidate>
        
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
              placeholder="ej. Juan"
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
              placeholder="ej. Pérez"
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
          <!-- Campo: Usuario -->
          <div class="col-sm-6">
            <label class="form-label-choco" for="username">
              Usuario <span class="text-danger">*</span>
            </label>
            <input 
              type="text" 
              id="username" 
              name="username" 
              class="form-control form-control-choco <?= isset($errores['username']) ? 'is-invalid' : '' ?>" 
              placeholder="ej. juanperez"
              value="<?= htmlspecialchars($datos['username']) ?>" 
              required
            >
            <?php if (isset($errores['username'])): ?>
              <div class="text-danger small mt-1 fw-medium">
                <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['username']) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Campo: Rol Asignado -->
          <div class="col-sm-6">
            <label class="form-label-choco" for="role">
              Rol Inicial <span class="text-danger">*</span>
            </label>
            <select id="role" name="role" class="form-select form-select-choco <?= isset($errores['role']) ? 'is-invalid' : '' ?>" required>
              <?php foreach ($rolesDisponibles as $rol): ?>
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
            placeholder="ej. juan@correo.com"
            value="<?= htmlspecialchars($datos['email']) ?>" 
            required
          >
          <?php if (isset($errores['email'])): ?>
            <div class="text-danger small mt-1 fw-medium">
              <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['email']) ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="row g-3 mb-4">
          <!-- Campo: Contraseña -->
          <div class="col-sm-6">
            <label class="form-label-choco" for="password">
              Contraseña <span class="text-danger">*</span>
            </label>
            <div class="position-relative d-flex align-items-center">
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control form-control-choco pe-5 <?= isset($errores['password']) ? 'is-invalid' : '' ?>" 
                placeholder="Mínimo 6 caracteres" 
                required
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

          <!-- Campo: Confirmar Contraseña -->
          <div class="col-sm-6">
            <label class="form-label-choco" for="confirm_password">
              Confirmar Contraseña <span class="text-danger">*</span>
            </label>
            <div class="position-relative d-flex align-items-center">
              <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                class="form-control form-control-choco pe-5 <?= isset($errores['confirm_password']) ? 'is-invalid' : '' ?>" 
                placeholder="Repite la clave" 
                required
              >
              <button type="button" class="input-icon-btn-choco btn-toggle-password" data-target="confirm_password" title="Mostrar contraseña">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <?php if (isset($errores['confirm_password'])): ?>
              <div class="text-danger small mt-1 fw-medium">
                <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['confirm_password']) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Botón de Registro -->
        <button type="submit" class="btn btn-choco w-100 py-2 fs-6">
          <i class="bi bi-person-plus-fill me-1"></i> Registrarme
        </button>
      </form>

      <!-- Enlace hacia Login -->
      <div class="text-center mt-4 pt-3 border-top" style="border-color: var(--color-cream-border) !important; font-size: 0.9rem; color: var(--color-text-muted);">
        ¿Ya tienes una cuenta registrada? <a href="login.php" class="fw-bold">Inicia sesión</a>
      </div>

    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Scripts Personalizados -->
  <script src="js/main.js"></script>
</body>
</html>
