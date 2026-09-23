<?php
/**
 * ==============================================================================
 * Pantalla de Inicio de Sesión (Login)
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';

// Si el usuario ya está autenticado, redirigir directamente al panel
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
  <!-- Estilos Globales -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-header">
        <div class="brand-icon">✨</div>
        <h1 class="auth-title">Bienvenido</h1>
        <p class="auth-subtitle">Ingresa tus credenciales para acceder al sistema</p>
      </div>

      <!-- Mensaje Flash (si proviene de un registro o redirección) -->
      <?php if ($mensajeFlash): ?>
        <div class="alert alert-<?= htmlspecialchars($mensajeFlash['tipo']) ?>">
          <div class="alert-content">
            <span><?= htmlspecialchars($mensajeFlash['mensaje']) ?></span>
          </div>
          <button type="button" class="alert-close" aria-label="Cerrar">&times;</button>
        </div>
      <?php endif; ?>

      <!-- Error General de Login -->
      <?php if (isset($errores['general'])): ?>
        <div class="alert alert-error">
          <div class="alert-content">
            <span>⚠️ <?= htmlspecialchars($errores['general']) ?></span>
          </div>
          <button type="button" class="alert-close" aria-label="Cerrar">&times;</button>
        </div>
      <?php endif; ?>

      <!-- Formulario de Inicio de Sesión (Envío por POST) -->
      <form method="POST" action="login.php" onsubmit="return validarFormularioLogin(this);" novalidate>
        
        <!-- Campo: Usuario o Correo Electrónico -->
        <div class="form-group">
          <label class="form-label" for="identificador">Usuario o Correo Electrónico <span class="required">*</span></label>
          <div class="input-wrapper">
            <input 
              type="text" 
              id="identificador" 
              name="identificador" 
              class="form-input <?= isset($errores['identificador']) ? 'is-invalid' : '' ?>" 
              placeholder="ej. admin o admin@sistema.local"
              value="<?= htmlspecialchars($identificador) ?>" 
              required
              autofocus
            >
          </div>
          <?php if (isset($errores['identificador'])): ?>
            <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['identificador']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Campo: Contraseña -->
        <div class="form-group">
          <label class="form-label" for="password">Contraseña <span class="required">*</span></label>
          <div class="input-wrapper">
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-input <?= isset($errores['password']) ? 'is-invalid' : '' ?>" 
              placeholder="••••••••" 
              required
            >
            <button type="button" class="input-icon-btn btn-toggle-password" data-target="password" title="Mostrar contraseña">👁️</button>
          </div>
          <?php if (isset($errores['password'])): ?>
            <div class="error-feedback">⚠️ <?= htmlspecialchars($errores['password']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Botón de Envío -->
        <div style="margin-top: 1.5rem;">
          <button type="submit" class="btn btn-primary btn-block">
            Iniciar Sesión
          </button>
        </div>
      </form>

      <!-- Credenciales de Demostración para Prueba Rápida -->
      <div style="margin-top: 1.5rem; padding: 0.85rem; background-color: #f8fafc; border: 1px dashed var(--color-border); border-radius: var(--radius-md); font-size: 0.8rem; color: var(--color-text-muted);">
        <strong style="color: var(--color-text-main);">🔑 Credenciales de prueba:</strong><br>
        Usuario: <code>admin</code> &bull; Clave: <code>admin123</code>
      </div>

      <!-- Enlace hacia Registro -->
      <div class="auth-footer">
        ¿Aún no tienes una cuenta? <a href="registro.php">Regístrate aquí</a>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="js/main.js"></script>
</body>
</html>
