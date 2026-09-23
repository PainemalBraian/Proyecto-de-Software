<?php
/**
 * ==============================================================================
 * Módulo de Backend para la Entidad User (Usuarios)
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * Implementación de funciones de acceso a datos, validación y hashing seguro
 * ==============================================================================
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/roles.php';

/**
 * Obtiene la lista de usuarios con INNER JOIN a la tabla role para mostrar el nombre del rol.
 * 
 * @return array Lista de usuarios con datos de su rol asociado.
 */
function listarUsuarios() {
  $pdo = obtenerConexion();
  $sql = 'SELECT u.id, u.username, u.email, u.name, u.last_name, u.role, u.created_at,
                 r.name AS role_name, r.description AS role_description
          FROM user u
          INNER JOIN role r ON u.role = r.id
          ORDER BY u.id DESC';
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  return $stmt->fetchAll();
}

/**
 * Obtiene un usuario específico por su ID junto a los datos de su rol.
 * 
 * @param int $id Identificador del usuario.
 * @return array|false Datos del usuario o false si no existe.
 */
function obtenerUsuarioPorId($id) {
  $pdo = obtenerConexion();
  $sql = 'SELECT u.id, u.username, u.email, u.name, u.last_name, u.role, u.created_at,
                 r.name AS role_name
          FROM user u
          INNER JOIN role r ON u.role = r.id
          WHERE u.id = :id
          LIMIT 1';
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':id' => (int)$id]);
  return $stmt->fetch();
}

/**
 * Obtiene un usuario por su username o email (útil para autenticación).
 * 
 * @param string $identificador Nombre de usuario o correo electrónico.
 * @return array|false Datos del usuario o false si no existe.
 */
function obtenerUsuarioPorIdentificador($identificador) {
  $pdo = obtenerConexion();
  $sql = 'SELECT u.id, u.username, u.email, u.name, u.last_name, u.password, u.role, u.created_at,
                 r.name AS role_name
          FROM user u
          INNER JOIN role r ON u.role = r.id
          WHERE u.username = :ident_user OR u.email = :ident_email
          LIMIT 1';
  $stmt = $pdo->prepare($sql);
  $ident = trim($identificador);
  $stmt->execute([
    ':ident_user' => $ident,
    ':ident_email' => $ident
  ]);
  return $stmt->fetch();
}

/**
 * Valida los datos comunes de un usuario (para creación y edición).
 * 
 * @param string $username Nombre de usuario.
 * @param string $email Correo electrónico.
 * @param string $name Nombre de pila.
 * @param string $lastName Apellido.
 * @param int $role ID del rol asignado.
 * @param int|null $excludeId ID de usuario a excluir en la verificación de unicidad.
 * @return array Lista de mensajes de error indexada por campo.
 */
function validarDatosUsuario($username, $email, $name, $lastName, $role, $excludeId = null) {
  $errores = [];

  // 1. Validación de nombre
  if (empty($name)) {
    $errores['name'] = 'El nombre es obligatorio.';
  } elseif (strlen($name) > 100) {
    $errores['name'] = 'El nombre no puede exceder 100 caracteres.';
  }

  // 2. Validación de apellido
  if (empty($lastName)) {
    $errores['last_name'] = 'El apellido es obligatorio.';
  } elseif (strlen($lastName) > 100) {
    $errores['last_name'] = 'El apellido no puede exceder 100 caracteres.';
  }

  // 3. Validación de username
  if (empty($username)) {
    $errores['username'] = 'El nombre de usuario es obligatorio.';
  } elseif (strlen($username) < 3 || strlen($username) > 50) {
    $errores['username'] = 'El usuario debe tener entre 3 y 50 caracteres.';
  } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
    $errores['username'] = 'Solo se permiten letras, números, puntos, guiones y guiones bajos.';
  }

  // 4. Validación de email
  if (empty($email)) {
    $errores['email'] = 'El correo electrónico es obligatorio.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores['email'] = 'El formato del correo electrónico no es válido.';
  } elseif (strlen($email) > 100) {
    $errores['email'] = 'El correo electrónico no puede superar 100 caracteres.';
  }

  // 5. Validación de Rol
  $roleId = (int)$role;
  if ($roleId <= 0 || !obtenerRolPorId($roleId)) {
    $errores['role'] = 'Debes seleccionar un rol válido existente.';
  }

  // 6. Validación de unicidad de username y email en la BD
  $pdo = obtenerConexion();
  if (empty($errores['username'])) {
    $sqlUser = 'SELECT id FROM user WHERE username = :username' . ($excludeId ? ' AND id != :exclude_id' : '');
    $params = [':username' => $username];
    if ($excludeId) $params[':exclude_id'] = (int)$excludeId;
    $stmt = $pdo->prepare($sqlUser);
    $stmt->execute($params);
    if ($stmt->fetch()) {
      $errores['username'] = 'Este nombre de usuario ya está registrado por otra cuenta.';
    }
  }

  if (empty($errores['email'])) {
    $sqlEmail = 'SELECT id FROM user WHERE email = :email' . ($excludeId ? ' AND id != :exclude_id' : '');
    $params = [':email' => $email];
    if ($excludeId) $params[':exclude_id'] = (int)$excludeId;
    $stmt = $pdo->prepare($sqlEmail);
    $stmt->execute($params);
    if ($stmt->fetch()) {
      $errores['email'] = 'Este correo electrónico ya se encuentra registrado.';
    }
  }

  return $errores;
}

/**
 * Crea un nuevo usuario en la base de datos con contraseña cifrada y validación de duplicados.
 * 
 * @param string $username
 * @param string $email
 * @param string $name
 * @param string $lastName
 * @param int $role
 * @param string $password
 * @return array ['exito' => bool, 'mensaje' => string, 'errores' => array, 'id' => int|null]
 */
function crearUsuario($username, $email, $name, $lastName, $role, $password = '') {
  $username = trim($username ?? '');
  $email = trim(strtolower($email ?? ''));
  $name = trim($name ?? '');
  $lastName = trim($lastName ?? '');
  $role = (int)$role;
  $password = $password ?? '';

  $errores = validarDatosUsuario($username, $email, $name, $lastName, $role);

  // Validación de contraseña para creación
  if (empty($password)) {
    $errores['password'] = 'La contraseña es obligatoria para nuevos usuarios.';
  } elseif (strlen($password) < 6) {
    $errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
  }

  if (!empty($errores)) {
    return [
      'exito' => false,
      'mensaje' => 'Por favor corrige los campos indicados.',
      'errores' => $errores,
      'id' => null
    ];
  }

  try {
    $pdo = obtenerConexion();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = 'INSERT INTO user (username, email, name, last_name, password, role) 
            VALUES (:username, :email, :name, :last_name, :password, :role)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':username' => $username,
      ':email' => $email,
      ':name' => $name,
      ':last_name' => $lastName,
      ':password' => $passwordHash,
      ':role' => $role
    ]);

    return [
      'exito' => true,
      'mensaje' => 'Usuario registrado exitosamente.',
      'errores' => [],
      'id' => (int)$pdo->lastInsertId()
    ];
  } catch (PDOException $e) {
    error_log('Error al crear usuario: ' . $e->getMessage());
    return [
      'exito' => false,
      'mensaje' => 'Error al guardar el usuario en la base de datos.',
      'errores' => ['general' => 'Ocurrió un error inesperado al procesar la solicitud.'],
      'id' => null
    ];
  }
}

/**
 * Actualiza los datos de un usuario existente, permitiendo actualizar o conservar su clave.
 * 
 * @param int $id ID del usuario.
 * @param string $username
 * @param string $email
 * @param string $name
 * @param string $lastName
 * @param int $role
 * @param string|null $password Si se especifica, actualiza la clave; si está vacío, conserva la actual.
 * @return array ['exito' => bool, 'mensaje' => string, 'errores' => array]
 */
function actualizarUsuario($id, $username, $email, $name, $lastName, $role, $password = null) {
  $id = (int)$id;
  $username = trim($username ?? '');
  $email = trim(strtolower($email ?? ''));
  $name = trim($name ?? '');
  $lastName = trim($lastName ?? '');
  $role = (int)$role;

  if ($id <= 0 || !obtenerUsuarioPorId($id)) {
    return ['exito' => false, 'mensaje' => 'El usuario a editar no existe.', 'errores' => ['id' => 'Usuario no encontrado.']];
  }

  $errores = validarDatosUsuario($username, $email, $name, $lastName, $role, $id);

  // Si se envió una nueva contraseña, validarla
  $actualizarPassword = false;
  $passwordHash = null;
  if (!empty($password)) {
    if (strlen($password) < 6) {
      $errores['password'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } else {
      $actualizarPassword = true;
      $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }
  }

  if (!empty($errores)) {
    return [
      'exito' => false,
      'mensaje' => 'Por favor corrige los errores antes de guardar.',
      'errores' => $errores
    ];
  }

  try {
    $pdo = obtenerConexion();
    if ($actualizarPassword) {
      $sql = 'UPDATE user 
              SET username = :username, email = :email, name = :name, last_name = :last_name, role = :role, password = :password 
              WHERE id = :id';
      $params = [
        ':username' => $username,
        ':email' => $email,
        ':name' => $name,
        ':last_name' => $lastName,
        ':role' => $role,
        ':password' => $passwordHash,
        ':id' => $id
      ];
    } else {
      $sql = 'UPDATE user 
              SET username = :username, email = :email, name = :name, last_name = :last_name, role = :role 
              WHERE id = :id';
      $params = [
        ':username' => $username,
        ':email' => $email,
        ':name' => $name,
        ':last_name' => $lastName,
        ':role' => $role,
        ':id' => $id
      ];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return [
      'exito' => true,
      'mensaje' => 'Usuario actualizado correctamente.',
      'errores' => []
    ];
  } catch (PDOException $e) {
    error_log('Error al actualizar usuario: ' . $e->getMessage());
    return [
      'exito' => false,
      'mensaje' => 'Error al actualizar los datos en el servidor.',
      'errores' => ['general' => 'Ocurrió un error inesperado al actualizar el usuario.']
    ];
  }
}

/**
 * Elimina un usuario por su ID.
 * 
 * @param int $id Identificador del usuario.
 * @return array ['exito' => bool, 'mensaje' => string]
 */
function eliminarUsuario($id) {
  $id = (int)$id;
  if ($id <= 0) {
    return ['exito' => false, 'mensaje' => 'ID de usuario no válido.'];
  }

  try {
    $pdo = obtenerConexion();
    $stmt = $pdo->prepare('DELETE FROM user WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
      return ['exito' => true, 'mensaje' => 'Usuario eliminado correctamente.'];
    } else {
      return ['exito' => false, 'mensaje' => 'El usuario no fue encontrado o ya fue eliminado.'];
    }
  } catch (PDOException $e) {
    error_log('Error al eliminar usuario: ' . $e->getMessage());
    return ['exito' => false, 'mensaje' => 'No se pudo eliminar el usuario.'];
  }
}
