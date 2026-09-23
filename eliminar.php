<?php
/**
 * ==============================================================================
 * Procesamiento de Baja de Usuario vía POST
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';
require_once __DIR__ . '/backend/usuarios.php';

// Proteger el endpoint con autenticación
requiereAutenticacion();

// Exigir método POST estricto para operaciones destructivas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  setMensajeFlash('error', 'Método HTTP no permitido para esta operación.');
  header('Location: index.php');
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$usuarioActual = obtenerUsuarioLogueado();

if ($id <= 0) {
  setMensajeFlash('error', 'Identificador de usuario no válido.');
  header('Location: index.php');
  exit;
}

// Evitar que el usuario logueado elimine su propia cuenta en sesión
if ($id === (int)$usuarioActual['id']) {
  setMensajeFlash('advertencia', 'No puedes eliminar tu propia cuenta mientras tienes una sesión activa.');
  header('Location: index.php');
  exit;
}

// Ejecutar la eliminación en el backend
$resultado = eliminarUsuario($id);

if ($resultado['exito']) {
  setMensajeFlash('exito', $resultado['mensaje']);
} else {
  setMensajeFlash('error', $resultado['mensaje']);
}

header('Location: index.php');
exit;
