<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class UsuarioCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

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

  public function insertarRolesPredeterminados()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Roles a insertar
    $roles = ['Administrador', 'Gerencia', 'Tractorista'];

    foreach ($roles as $rol) {
      $stmt = $conn->prepare("INSERT IGNORE INTO roles (nombre) VALUES (?)");
      $stmt->bind_param("s", $rol);
      $stmt->execute();
      $stmt->close();
    }

    $conn->close();
  }

  public function insertarUsuarioAdministrador()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Verificar si ya existe un usuario con username "walter"
    $adminUsername = "walter";
    $verificar = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
    $verificar->bind_param("s", $adminUsername);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows === 0) {
      // Insertar usuario administrador con rol_id = 1
      $email = "walter@gmail.com";
      $password = password_hash("Plm_2429", PASSWORD_DEFAULT); // contraseña segura
      $rol_id = 1;
      $token = bin2hex(random_bytes(32));

      $insertar = $conn->prepare("INSERT INTO usuarios (username, email, password, rol_id, token) VALUES (?, ?, ?, ?, ?)");
      $insertar->bind_param("sssis", $adminUsername, $email, $password, $rol_id, $token);
      $insertar->execute();
      $insertar->close();
    }

    $verificar->close();
    $conn->close();
  }

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
