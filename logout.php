<?php
/**
 * ==============================================================================
 * Cierre de Sesión Seguro (Logout)
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

require_once __DIR__ . '/backend/auth.php';

cerrarSesion();

// Iniciar sesión temporal para guardar el mensaje flash
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
setMensajeFlash('info', 'Has cerrado sesión correctamente.');

header('Location: login.php');
exit;
