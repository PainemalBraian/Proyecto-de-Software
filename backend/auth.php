<?php
/**
 * ==============================================================================
 * Módulo de Autenticación y Control de Sesión
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * Manejo de Login, Registro, Sesiones y Mensajes Flash
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/usuarios.php';
require_once __DIR__ . '/roles.php';

/**
 * Autentica las credenciales de un usuario y establece la sesión si son válidas.
 * 
 * @param string $identificador Nombre de usuario o correo electrónico.
 * @param string $password Contraseña en texto plano.
 * @return array ['exito' => bool, 'mensaje' => string, 'errores' => array]
 */
function iniciarSesionUsuario($identificador, $password) {
  $identificador = trim($identificador ?? '');
  $password = $password ?? '';
  $errores = [];

  if (empty($identificador)) {
    $errores['identificador'] = 'Por favor ingresa tu usuario o correo electrónico.';
  }

  if (empty($password)) {
    $errores['password'] = 'Por favor ingresa tu contraseña.';
  }

  if (!empty($errores)) {
    return ['exito' => false, 'mensaje' => 'Campos incompletos.', 'errores' => $errores];
  }

  $usuario = obtenerUsuarioPorIdentificador($identificador);

  if (!$usuario || !password_verify($password, $usuario['password'])) {
    return [
      'exito' => false,
      'mensaje' => 'Credenciales inválidas. Verifica tu usuario/correo y contraseña.',
      'errores' => ['general' => 'Usuario o contraseña incorrectos.']
    ];
  }

  // Regenerar ID de sesión para prevenir Session Fixation si las cabeceras no fueron enviadas
  if (!headers_sent()) {
    session_regenerate_id(true);
  }

  $_SESSION['usuario_id'] = (int)$usuario['id'];
  $_SESSION['usuario_username'] = $usuario['username'];
  $_SESSION['usuario_email'] = $usuario['email'];
  $_SESSION['usuario_nombre_completo'] = $usuario['name'] . ' ' . $usuario['last_name'];
  $_SESSION['usuario_rol_id'] = (int)$usuario['role'];
  $_SESSION['usuario_rol_nombre'] = $usuario['role_name'];

  return [
    'exito' => true,
    'mensaje' => 'Inicio de sesión exitoso.',
    'errores' => []
  ];
}

/**
 * Registra un nuevo usuario desde la pantalla pública de registro.
 * Asigna un rol predeterminado (por defecto Invitado = 3 o el rol provisto).
 * 
 * @param array $datos ['username', 'email', 'name', 'last_name', 'password', 'confirm_password', 'role']
 * @return array ['exito' => bool, 'mensaje' => string, 'errores' => array]
 */
function registrarNuevoUsuario($datos) {
  $username = trim($datos['username'] ?? '');
  $email = trim(strtolower($datos['email'] ?? ''));
  $name = trim($datos['name'] ?? '');
  $lastName = trim($datos['last_name'] ?? '');
  $password = $datos['password'] ?? '';
  $confirmPassword = $datos['confirm_password'] ?? '';
  $role = isset($datos['role']) ? (int)$datos['role'] : 3; // 3 = Invitado por defecto

  $errores = [];

  if (empty($password)) {
    $errores['password'] = 'La contraseña es obligatoria.';
  } elseif (strlen($password) < 6) {
    $errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
  }

  if ($password !== $confirmPassword) {
    $errores['confirm_password'] = 'Las contraseñas no coinciden.';
  }

  $erroresValidacion = validarDatosUsuario($username, $email, $name, $lastName, $role);
  $errores = array_merge($erroresValidacion, $errores);

  if (!empty($errores)) {
    return [
      'exito' => false,
      'mensaje' => 'Por favor corrige los datos del formulario.',
      'errores' => $errores
    ];
  }

  $resultadoCreacion = crearUsuario($username, $email, $name, $lastName, $role, $password);

  if (!$resultadoCreacion['exito']) {
    return $resultadoCreacion;
  }

  return [
    'exito' => true,
    'mensaje' => '¡Cuenta creada con éxito! Ya puedes iniciar sesión con tus credenciales.',
    'errores' => []
  ];
}

/**
 * Verifica si el usuario actual tiene una sesión iniciada.
 * 
 * @return bool True si está autenticado, false en caso contrario.
 */
function estaAutenticado() {
  return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Restringe el acceso a páginas privadas. Redirige a login si no está logueado.
 */
function requiereAutenticacion() {
  if (!estaAutenticado()) {
    setMensajeFlash('advertencia', 'Debes iniciar sesión para acceder a este módulo.');
    header('Location: login.php');
    exit;
  }
}

/**
 * Retorna los datos del usuario en la sesión actual.
 * 
 * @return array|null
 */
function obtenerUsuarioLogueado() {
  if (!estaAutenticado()) {
    return null;
  }
  return [
    'id' => $_SESSION['usuario_id'],
    'username' => $_SESSION['usuario_username'],
    'email' => $_SESSION['usuario_email'],
    'nombre_completo' => $_SESSION['usuario_nombre_completo'],
    'rol_id' => $_SESSION['usuario_rol_id'],
    'rol_nombre' => $_SESSION['usuario_rol_nombre']
  ];
}

/**
 * Cierra la sesión activa de forma segura.
 */
function cerrarSesion() {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params['path'], $params['domain'],
      $params['secure'], $params['httponly']
    );
  }
  session_destroy();
}

/**
 * Guarda un mensaje flash en la sesión para ser mostrado en la siguiente vista.
 * 
 * @param string $tipo 'exito', 'error', 'advertencia', 'info'
 * @param string $mensaje Texto del mensaje.
 */
function setMensajeFlash($tipo, $mensaje) {
  $_SESSION['flash_message'] = [
    'tipo' => $tipo,
    'mensaje' => $mensaje
  ];
}

/**
 * Obtiene y elimina el mensaje flash de la sesión si existe.
 * 
 * @return array|null ['tipo' => string, 'mensaje' => string] o null
 */
function obtenerMensajeFlash() {
  if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    return $flash;
  }
  return null;
}
