<?php
/**
 * ==============================================================================
 * Pantalla de Inicio de Sesión (Login) — Tema Chocolate & Crema con Bootstrap 5
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';

// Si el usuario ya está autenticado, redirigir al panel principal
if (estaAutenticado()) {
  header('Location: index.php');
  exit;
}

$errores = [];
$identificador = '';
$mensajeFlash = obtenerMensajeFlash();

// Procesar formulario enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $identificador = $_POST['identificador'] ?? '';
  $password = $_POST['password'] ?? '';

  $resultado = iniciarSesionUsuario($identificador, $password);

  if ($resultado['exito']) {
    setMensajeFlash('exito', '¡Bienvenido de nuevo!');
    header('Location: index.php');
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
  <title>Iniciar Sesión — Panel de Gestión</title>
  
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

  <div class="auth-wrapper-choco">
    <div class="auth-card-choco">
      
      <!-- Cabecera de la Tarjeta -->
      <div class="text-center mb-4">
        <div class="brand-icon-choco mx-auto mb-3" style="width: 52px; height: 52px; font-size: 1.5rem;">
          ☕
        </div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--color-choco-dark);">Bienvenido</h1>
        <p class="text-muted small">Ingresa tus credenciales para acceder al sistema</p>
      </div>

      <!-- Alerta Flash (si proviene de registro o logout) -->
      <?php if ($mensajeFlash): ?>
        <div class="alert-choco alert-choco-<?= htmlspecialchars($mensajeFlash['tipo']) ?> mb-4">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill"></i>
            <span><?= htmlspecialchars($mensajeFlash['mensaje']) ?></span>
          </div>
          <button type="button" class="btn-close alert-close" aria-label="Cerrar"></button>
        </div>
      <?php endif; ?>

      <!-- Error General de Login -->
      <?php if (isset($errores['general'])): ?>
        <div class="alert-choco alert-choco-error mb-4">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($errores['general']) ?></span>
          </div>
          <button type="button" class="btn-close alert-close" aria-label="Cerrar"></button>
        </div>
      <?php endif; ?>

      <!-- Formulario de Login (POST) -->
      <form method="POST" action="login.php" onsubmit="return validarFormularioLogin(this);" novalidate>
        
        <!-- Campo: Usuario / Email -->
        <div class="mb-3">
          <label class="form-label-choco" for="identificador">
            <i class="bi bi-person me-1 text-muted"></i> Usuario o Correo Electrónico <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <input 
              type="text" 
              id="identificador" 
              name="identificador" 
              class="form-control form-control-choco <?= isset($errores['identificador']) ? 'is-invalid' : '' ?>" 
              placeholder="ej. admin o admin@sistema.local"
              value="<?= htmlspecialchars($identificador) ?>" 
              required
              autofocus
            >
          </div>
          <?php if (isset($errores['identificador'])): ?>
            <div class="text-danger small mt-1 fw-medium">
              <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errores['identificador']) ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Campo: Contraseña -->
        <div class="mb-4">
          <label class="form-label-choco" for="password">
            <i class="bi bi-shield-lock me-1 text-muted"></i> Contraseña <span class="text-danger">*</span>
          </label>
          <div class="position-relative d-flex align-items-center">
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-control form-control-choco pe-5 <?= isset($errores['password']) ? 'is-invalid' : '' ?>" 
              placeholder="••••••••" 
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

        <!-- Botón de Envío -->
        <button type="submit" class="btn btn-choco w-100 py-2 fs-6">
          <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
        </button>
      </form>

      <!-- Credenciales de Demostración -->
      <div class="mt-4 p-3 rounded-3" style="background-color: #f7eee4; border: 1px dashed var(--color-cream-border); font-size: 0.8rem; color: var(--color-text-muted);">
        <div class="fw-bold mb-1" style="color: var(--color-choco-dark);">
          <i class="bi bi-key-fill text-warning me-1"></i> Credenciales de demostración:
        </div>
        <div>Usuario: <code class="text-dark bg-white px-1 py-0.5 rounded border">admin</code> &bull; Clave: <code class="text-dark bg-white px-1 py-0.5 rounded border">admin123</code></div>
      </div>

      <!-- Enlace hacia Registro -->
      <div class="text-center mt-4 pt-3 border-top" style="border-color: var(--color-cream-border) !important; font-size: 0.9rem; color: var(--color-text-muted);">
        ¿No tienes una cuenta aún? <a href="registro.php" class="fw-bold">Regístrate aquí</a>
      </div>

    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Scripts Personalizados -->
  <script src="js/main.js"></script>
</body>
</html>
