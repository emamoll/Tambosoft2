<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla del usuario.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/usuario/usuarioTabla.php';
require_once __DIR__ . '../../modelos/usuario/usuarioModelo.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'usuarios'.
 * Maneja todas las operaciones de la base de datos relacionadas con los usuarios.
 */
class UsuarioDAO
{
  // Propiedades privadas para la conexión y la creación de tablas.
  private $db;
  private $conn;
  private $crearTabla;

  /**
   * Constructor de la clase.
   * 1. Crea una conexión a la base de datos.
   * 2. Instancia la clase para crear las tablas de roles y usuarios.
   * 3. Inserta los roles y el usuario administrador si no existen.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new UsuarioCrearTabla($this->db);
    $this->crearTabla->crearTablaRoles();
    $this->crearTabla->crearTablaUsuarios();
    $this->crearTabla->insertarRolesPredeterminados();
    $this->crearTabla->insertarUsuarioAdministrador();
    $this->conn = $this->db->connect();
  }

  /**
   * Obtiene todos los usuarios de la base de datos.
   *
   * @return array Un array de objetos Usuario.
   */
  public function getAllUsuarios()
  {
    $sql = "SELECT * FROM usuarios";
    $result = $this->conn->query($sql);

    // Si la consulta falla, detiene la ejecución y muestra el error.
    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $usuarios = [];

    // Recorre los resultados y crea un objeto Usuario por cada fila.
    while ($row = $result->fetch_assoc()) {
      $usuarios[] = new Usuario($row['id'], $row['username'], $row['email'], $row['password'], $row['rol_id'], $row['token']);
    }

    return $usuarios;
  }

  /**
   * Obtiene un usuario por su ID.
   *
   * @param int $id El ID del usuario.
   * @return Usuario|null Un objeto Usuario si se encuentra, de lo contrario, null.
   */
  public function getUsuarioById($id)
  {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $id); // Se usa 's' (cadena) aquí, pero debería ser 'i' (entero). ¡Revisar!
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows() === 0) {
      return null;
    }

    // Vincula las variables a las columnas del resultado y obtiene la fila.
    $stmt->bind_result($id, $username, $email, $password, $rol_id, $token);
    $stmt->fetch();

    return new Usuario($id, $username, $email, $password, $rol_id, $token);
  }

  /**
   * Obtiene un usuario por su nombre de usuario.
   *
   * @param string $username El nombre de usuario.
   * @return Usuario|null Un objeto Usuario si se encuentra, de lo contrario, null.
   */
  public function getUsuarioByUsername($username)
  {
    $sql = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows() === 0) {
      return null;
    }

    // Vincula las variables y obtiene la fila.
    $stmt->bind_result($id, $username, $email, $password, $rol_id, $token);
    $stmt->fetch();

    return new Usuario($id, $username, $email, $password, $rol_id, $token);
  }

  /**
   * Obtiene un usuario por su dirección de correo electrónico.
   *
   * @param string $email El correo electrónico.
   * @return Usuario|null Un objeto Usuario si se encuentra, de lo contrario, null.
   */
  public function getUsuarioByEmail($email)
  {
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows() === 0) {
      return null;
    }

    // Vincula las variables y obtiene la fila.
    $stmt->bind_result($id, $username, $email, $password, $rol_id, $token);
    $stmt->fetch();

    return new Usuario($id, $username, $email, $password, $rol_id, $token);
  }

  /**
   * Obtiene un usuario por su token de sesión.
   *
   * @param string $token El token de sesión.
   * @return Usuario|null Un objeto Usuario si se encuentra, de lo contrario, null.
   */
  public function getUsuarioByToken($token)
  {
    $sql = "SELECT id, username, email, password, rol_id, token FROM usuarios WHERE token = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows === 0)
      return null;

    // Vincula las variables y obtiene la fila.
    $stmt->bind_result($id, $username, $email, $password, $rol_id, $token);
    $stmt->fetch();

    return new Usuario($id, $username, $email, $password, $rol_id, $token);
  }

  /**
   * Inserta los roles predeterminados si no existen.
   * Este método está desactualizado, ya que la lógica se movió a UsuarioCrearTabla.
   */
  public function insertarRoles()
  {
    $roles = ['Administrador', 'Tractorista', 'Gerencia'];

    foreach ($roles as $rol) {
      // 1. Verificamos si el rol ya existe.
      $sqlVerificar = "SELECT id FROM roles WHERE nombre = ?";
      $stmtVerificar = $this->conn->prepare($sqlVerificar);
      $stmtVerificar->bind_param("s", $rol);
      $stmtVerificar->execute();
      $stmtVerificar->store_result();

      if ($stmtVerificar->num_rows === 0) {
        // 2. Si no existe, lo insertamos.
        $stmtVerificar->close();

        $sqlInsertar = "INSERT INTO roles (nombre) VALUES (?)";
        $stmtInsertar = $this->conn->prepare($sqlInsertar);
        $stmtInsertar->bind_param("s", $rol);
        $stmtInsertar->execute();
        $stmtInsertar->close();
      } else {
        $stmtVerificar->close(); // Ya existe, no lo insertamos.
      }
    }
  }

  /**
   * Verifica si los roles predeterminados están presentes.
   * Este método también está desactualizado y la lógica se encuentra en el constructor.
   */
  public function verificarRoles()
  {
    $sql = "SELECT COUNT(*) as count FROM roles WHERE nombre IN ('Administrador', 'Tractorista', 'Gerencia')";
    $result = $this->conn->query($sql);
    $row = $result->fetch_assoc();
    if ($row['count'] < 2) { // La lógica de 'menor a 2' es inusual. Podría ser 'menor a 3'. ¡Revisar!
      $this->insertarRoles();
    }
  }

  /**
   * Registra un nuevo usuario en la base de datos.
   *
   * @param Usuario $u El objeto Usuario a registrar.
   * @return bool True si el registro fue exitoso, de lo contrario, false.
   */
  public function registrarUsuario(Usuario $u)
  {
    $sql = "INSERT INTO usuarios (username, email, password, rol_id, token) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
      echo ("Error en prepare: " . $this->conn->error);
      return false;
    }
    $us = $u->getUsername();
    $e = $u->getEmail();
    $p = $u->getPassword();
    $r = $u->getRol_id();
    $t = $u->getToken();
    $stmt->bind_param("sssis", $us, $e, $p, $r, $t);

    $resultado = $stmt->execute();
    if (!$resultado) {
      echo ("Error en execute: " . $stmt->error);
    }

    return $resultado;
  }

  /**
   * Intenta iniciar sesión con un usuario y contraseña.
   *
   * @param string $username El nombre de usuario.
   * @param string $password La contraseña sin cifrar.
   * @return array|null Un array con los datos del usuario y un nuevo token si el login es exitoso, de lo contrario, null.
   */
  public function loginUsuario($username, $password)
  {
    $sql = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    // Verifica si el usuario existe y si la contraseña es correcta.
    if ($usuario && password_verify($password, $usuario['password'])) {
      // Genera y actualiza un nuevo token.
      $token = bin2hex(random_bytes(32));
      $this->actualizarToken($usuario['id'], $token);
      return ['usuario' => $usuario, 'token' => $token];
    }

    return null;
  }

  /**
   * Actualiza el token de sesión de un usuario por su ID.
   *
   * @param int $id El ID del usuario.
   * @param string $token El nuevo token.
   */
  public function actualizarToken($id, $token)
  {
    $sql = "UPDATE usuarios SET token = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $token, $id);
    $stmt->execute();
  }
}