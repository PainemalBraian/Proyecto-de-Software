<?php
/**
 * ==============================================================================
 * Módulo de Backend para la Entidad Role (Roles)
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * Implementación de funciones de acceso a datos con sentencias preparadas (PDO)
 * ==============================================================================
 */

require_once __DIR__ . '/../config/conexion.php';

/**
 * Obtiene la lista completa de roles ordenados por ID.
 * 
 * @return array Lista de roles asociativos.
 */
function listarRoles() {
  $pdo = obtenerConexion();
  $stmt = $pdo->prepare('SELECT id, name, description FROM role ORDER BY id ASC');
  $stmt->execute();
  return $stmt->fetchAll();
}

/**
 * Obtiene un rol específico por su identificador primario.
 * 
 * @param int $id Identificador del rol.
 * @return array|false Datos del rol o false si no existe.
 */
function obtenerRolPorId($id) {
  $pdo = obtenerConexion();
  $stmt = $pdo->prepare('SELECT id, name, description FROM role WHERE id = :id LIMIT 1');
  $stmt->execute([':id' => (int)$id]);
  return $stmt->fetch();
}

/**
 * Crea un nuevo rol en la base de datos tras validar los campos.
 * 
 * @param string $name Nombre del rol.
 * @param string $description Descripción del rol.
 * @return array ['exito' => bool, 'mensaje' => string, 'errores' => array, 'id' => int|null]
 */
function crearRol($name, $description) {
  $name = trim($name ?? '');
  $description = trim($description ?? '');
  $errores = [];

  if (empty($name)) {
    $errores['name'] = 'El nombre del rol es obligatorio.';
  } elseif (strlen($name) > 100) {
    $errores['name'] = 'El nombre no puede exceder los 100 caracteres.';
  }

  if (strlen($description) > 255) {
    $errores['description'] = 'La descripción no puede exceder los 255 caracteres.';
  }

  if (!empty($errores)) {
    return ['exito' => false, 'mensaje' => 'Por favor corrige los errores.', 'errores' => $errores, 'id' => null];
  }

  try {
    $pdo = obtenerConexion();
    $stmt = $pdo->prepare('INSERT INTO role (name, description) VALUES (:name, :description)');
    $stmt->execute([
      ':name' => $name,
      ':description' => $description !== '' ? $description : null
    ]);
    return [
      'exito' => true,
      'mensaje' => 'Rol creado exitosamente.',
      'errores' => [],
      'id' => (int)$pdo->lastInsertId()
    ];
  } catch (PDOException $e) {
    error_log('Error al crear rol: ' . $e->getMessage());
    return [
      'exito' => false,
      'mensaje' => 'Error en el servidor al intentar guardar el rol.',
      'errores' => ['general' => 'Ocurrió un error inesperado.'],
      'id' => null
    ];
  }
}

/**
 * Actualiza los datos de un rol existente.
 * 
 * @param int $id Identificador del rol a actualizar.
 * @param string $name Nombre del rol.
 * @param string $description Descripción del rol.
 * @return array ['exito' => bool, 'mensaje' => string, 'errores' => array]
 */
function actualizarRol($id, $name, $description) {
  $id = (int)$id;
  $name = trim($name ?? '');
  $description = trim($description ?? '');
  $errores = [];

  if ($id <= 0) {
    return ['exito' => false, 'mensaje' => 'Identificador de rol inválido.', 'errores' => ['id' => 'ID inválido.']];
  }

  if (empty($name)) {
    $errores['name'] = 'El nombre del rol es obligatorio.';
  } elseif (strlen($name) > 100) {
    $errores['name'] = 'El nombre no puede exceder los 100 caracteres.';
  }

  if (strlen($description) > 255) {
    $errores['description'] = 'La descripción no puede exceder los 255 caracteres.';
  }

  if (!empty($errores)) {
    return ['exito' => false, 'mensaje' => 'Por favor corrige los errores.', 'errores' => $errores];
  }

  try {
    $pdo = obtenerConexion();
    $stmt = $pdo->prepare('UPDATE role SET name = :name, description = :description WHERE id = :id');
    $stmt->execute([
      ':name' => $name,
      ':description' => $description !== '' ? $description : null,
      ':id' => $id
    ]);
    return ['exito' => true, 'mensaje' => 'Rol actualizado correctamente.', 'errores' => []];
  } catch (PDOException $e) {
    error_log('Error al actualizar rol: ' . $e->getMessage());
    return ['exito' => false, 'mensaje' => 'Error al actualizar el rol.', 'errores' => ['general' => 'Ocurrió un error inesperado.']];
  }
}

/**
 * Elimina un rol comprobando previamente que no tenga usuarios asociados (Integridad referencial).
 * 
 * @param int $id Identificador del rol a eliminar.
 * @return array ['exito' => bool, 'mensaje' => string]
 */
function eliminarRol($id) {
  $id = (int)$id;
  if ($id <= 0) {
    return ['exito' => false, 'mensaje' => 'ID de rol no válido.'];
  }

  $pdo = obtenerConexion();

  // Verificar si existen usuarios asignados a este rol
  $stmtCheck = $pdo->prepare('SELECT COUNT(*) AS total FROM user WHERE role = :role_id');
  $stmtCheck->execute([':role_id' => $id]);
  $resultado = $stmtCheck->fetch();

  if ($resultado && (int)$resultado['total'] > 0) {
    $cant = (int)$resultado['total'];
    return [
      'exito' => false,
      'mensaje' => "No se puede eliminar el rol porque tiene {$cant} usuario(s) asignado(s). Reasigna los usuarios primero."
    ];
  }

  try {
    $stmtDelete = $pdo->prepare('DELETE FROM role WHERE id = :id');
    $stmtDelete->execute([':id' => $id]);
    return ['exito' => true, 'mensaje' => 'Rol eliminado con éxito.'];
  } catch (PDOException $e) {
    error_log('Error al eliminar rol: ' . $e->getMessage());
    return ['exito' => false, 'mensaje' => 'No fue posible eliminar el rol debido a una restricción de la base de datos.'];
  }
}
