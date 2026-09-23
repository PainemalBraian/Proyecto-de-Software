<?php
/**
 * ==============================================================================
 * Conexión a la Base de Datos mediante PDO
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * Única fuente de conexión del sistema. Manejo centralizado y resiliente.
 * ==============================================================================
 */

// Constantes de configuración de la base de datos (WAMP / entorno local)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'sprint2');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * Obtiene o reutiliza la instancia de conexión PDO a MySQL.
 * 
 * @return PDO Instancia de PDO lista para operar.
 * @throws Exception Si ocurre un fallo crítico de conexión.
 */
function obtenerConexion() {
  static $pdo = null;

  if ($pdo === null) {
    $opciones = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Intentos de conexión: Configuración definida -> WAMP MariaDB (3307) -> WAMP MySQL (root/root)
    $intentos = [
      ['host' => DB_HOST, 'port' => DB_PORT, 'user' => DB_USER, 'pass' => DB_PASS],
      ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
      ['host' => 'localhost', 'port' => 3307, 'user' => 'root', 'pass' => ''],
      ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ];

    $ultimoError = '';

    foreach ($intentos as $config) {
      try {
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, $config['user'], $config['pass'], $opciones);
        
        // Verificar y asegurar columnas necesarias para el Login y ABM
        asegurarEsquemaBaseDatos($pdo);

        return $pdo;
      } catch (PDOException $e) {
        $ultimoError = $e->getMessage();
      }
    }

    // Si fallan todas las combinaciones locales
    error_log('Error crítico de conexión PDO: ' . $ultimoError);
    
    die('
      <div style="font-family: system-ui, sans-serif; max-width: 600px; margin: 50px auto; padding: 24px; border: 1px solid #fed7aa; background-color: #fffbeb; color: #9a3412; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <h2 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
          ⚠️ Error de Conexión a la Base de Datos
        </h2>
        <p>No se pudo conectar con el servidor MySQL (<strong>sprint2</strong>). Verifica que:</p>
        <ul style="line-height: 1.6;">
          <li>El servidor <strong>WAMP / MySQL / MariaDB</strong> esté iniciado en verde.</li>
          <li>La base de datos <strong>sprint2</strong> haya sido creada importando <code>sql/sprint2.sql</code> en phpMyAdmin.</li>
          <li>Las credenciales configuradas en <code>config/conexion.php</code> sean las correctas.</li>
        </ul>
      </div>
    ');
  }

  return $pdo;
}

/**
 * Garantiza de forma no destructiva que la tabla user cuente con las columnas de contraseña y fecha.
 * 
 * @param PDO $pdo Instancia de base de datos activa.
 */
function asegurarEsquemaBaseDatos($pdo) {
  try {
    $columnas = $pdo->query('DESCRIBE user')->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('password', $columnas)) {
      $pdo->exec('ALTER TABLE user ADD COLUMN password VARCHAR(255) NULL AFTER last_name');
      // Asignar contraseña por defecto al admin existente si no la tenía
      $hashAdmin = password_hash('admin123', PASSWORD_DEFAULT);
      $pdo->exec("UPDATE user SET password = '{$hashAdmin}' WHERE password IS NULL OR password = ''");
    }

    if (!in_array('created_at', $columnas)) {
      $pdo->exec('ALTER TABLE user ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER role');
    }
  } catch (Exception $e) {
    // Si la tabla aún no existe, no bloquear (se creará al importar sql)
  }
}
