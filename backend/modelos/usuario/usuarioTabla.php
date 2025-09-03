<?php

// Incluye los archivos necesarios para la fábrica de bases de datos y la interfaz de conexión.
require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

/**
 * Clase encargada de crear las tablas `roles` y `usuarios` en la base de datos,
 * así como de insertar los datos iniciales.
 */
class UsuarioCrearTabla
{
  // Propiedad para la instancia de conexión a la base de datos.
  private $db;

  /**
   * Constructor de la clase.
   *
   * @param object $db La instancia de la conexión a la base de datos.
   */
  public function __construct($db)
  {
    $this->db = $db;
  }

  /**
   * Crea la tabla `roles` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `nombre`: Cadena de texto, no nula, única.
   */
  public function crearTablaRoles()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS roles (
              id INT PRIMARY KEY AUTO_INCREMENT, 
              nombre VARCHAR(255) NOT NULL UNIQUE)";

    $conn->query($sql);
    $conn->close();
  }

  /**
   * Inserta los roles predeterminados si no existen.
   * Utiliza `INSERT IGNORE` para evitar errores si los roles ya están en la tabla.
   */
  public function insertarRolesPredeterminados()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Roles a insertar.
    $roles = ['Administrador', 'Gerencia', 'Tractorista'];

    foreach ($roles as $rol) {
      $stmt = $conn->prepare("INSERT IGNORE INTO roles (nombre) VALUES (?)");
      $stmt->bind_param("s", $rol);
      $stmt->execute();
      $stmt->close();
    }

    $conn->close();
  }

  /**
   * Inserta un usuario administrador si no existe.
   * Verifica la existencia por el nombre de usuario "walter".
   */
  public function insertarUsuarioAdministrador()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // 1. Verificar si ya existe un usuario con username "walter".
    $adminUsername = "walter";
    $verificar = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
    $verificar->bind_param("s", $adminUsername);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows === 0) {
      // 2. Si no existe, se procede a insertar el usuario.
      $email = "walter@gmail.com";
      $password = password_hash("Plm_2429", PASSWORD_DEFAULT); // Cifra la contraseña.
      $rol_id = 1; // El ID del rol 'Administrador'.
      $token = bin2hex(random_bytes(32)); // Genera un token aleatorio.

      $insertar = $conn->prepare("INSERT INTO usuarios (username, email, password, rol_id, token) VALUES (?, ?, ?, ?, ?)");
      $insertar->bind_param("sssis", $adminUsername, $email, $password, $rol_id, $token);
      $insertar->execute();
      $insertar->close();
    }

    $verificar->close();
    $conn->close();
  }

  /**
   * Crea la tabla `usuarios` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `username`, `email`, `password`: Cadenas de texto, no nulas, `username` y `email` son únicos.
   * - `rol_id`: Entero, no nulo, clave foránea que referencia a `roles(id)`.
   * - `token`: Cadena de texto para el token de sesión.
   */
  public function crearTablaUsuarios()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS  usuarios (
              id INT PRIMARY KEY AUTO_INCREMENT, 
              username VARCHAR(255) NOT NULL UNIQUE, 
              email VARCHAR(255) NOT NULL UNIQUE, 
              password VARCHAR(255) NOT NULL,
              rol_id INT NOT NULL,
              token VARCHAR(64),
              FOREIGN KEY (rol_id) REFERENCES roles(id))";

    $conn->query($sql);
    $conn->close();
  }
}