<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla del almacén.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/almacen/almacenTabla.php';
require_once __DIR__ . '../../modelos/almacen/almacenModelo.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'almacenes'.
 * Maneja todas las operaciones de la base de datos relacionadas con los almacenes.
 */
class AlmacenDAO
{
  // Propiedades privadas para la conexión y la creación de la tabla.
  private $db;
  private $conn;
  private $crearTabla;

  /**
   * Constructor de la clase.
   * 1. Crea una conexión a la base de datos.
   * 2. Instancia la clase para crear la tabla si no existe.
   * 3. Establece la conexión.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new AlmacenCrearTabla($this->db);
    $this->crearTabla->crearTablaAlmacen();
    $this->conn = $this->db->connect();
  }

  /**
   * Obtiene todos los almacenes de la base de datos.
   *
   * @return array Un array de objetos Almacen.
   */
  public function getAllAlmacenes()
  {
    $sql = "SELECT * FROM almacenes";
    $result = $this->conn->query($sql);

    // Si la consulta falla, detiene la ejecución y muestra el error.
    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $almacenes = [];

    // Recorre los resultados y crea un objeto Almacen por cada fila.
    while ($row = $result->fetch_assoc()) {
      $almacenes[] = new Almacen($row['id'], $row['nombre'], $row['campo_id']);
    }

    return $almacenes;
  }

  /**
   * Obtiene un almacén por su ID.
   *
   * @param int $id El ID del almacén.
   * @return Almacen|null Un objeto Almacen si se encuentra, de lo contrario, null.
   */
  public function getAlmacenById($id)
  {
    $sql = "SELECT * FROM almacenes WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id); // 'i' indica que el parámetro es un entero.
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows() === 0) {
      return null;
    }

    // Vincula las variables a las columnas del resultado y obtiene la fila.
    $stmt->bind_result($id, $nombre, $campo_id);
    $stmt->fetch();

    return new Almacen($id, $nombre, $campo_id);
  }

  /**
   * Obtiene un almacén por su nombre.
   *
   * @param string $nombre El nombre del almacén.
   * @return Almacen|null Un objeto Almacen si se encuentra, de lo contrario, null.
   */
  public function getAlmacenByNombre($nombre)
  {
    $sql = "SELECT * FROM almacenes WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre); // 's' indica que el parámetro es una cadena.
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows() === 0) {
      return null;
    }

    // Vincula las variables y obtiene la fila.
    $stmt->bind_result($id, $nombre, $campo_id);
    $stmt->fetch();

    return new Almacen($id, $nombre, $campo_id);
  }

  /**
   * Obtiene todos los almacenes asociados a un ID de campo específico.
   *
   * @param int $campo_id El ID del campo.
   * @return array Un array de objetos Almacen.
   */
  public function getAlmacenByCampoId($campo_id)
  {
    $sql = "SELECT * FROM almacenes WHERE campo_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $campo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $almacenes = [];
    // Recorre los resultados y crea objetos Almacen.
    while ($row = $result->fetch_assoc()) {
      $almacenes[] = new Almacen($row['id'], $row['campo_id']);
    }

    return $almacenes;
  }

  /**
   * Registra un nuevo almacén en la base de datos.
   *
   * @param Almacen $a El objeto Almacen a registrar.
   * @return bool True si el registro fue exitoso, de lo contrario, false.
   */
  public function registrarAlmacen(Almacen $a)
  {
    $nombre = $a->getNombre();
    $c_i = $a->getCampo_id();

    // Log de depuración para verificar los datos.
    error_log("DEBUG: AlmacenDAO - Intentando registrar almacén: " . $nombre);
    error_log("DEBUG: AlmacenDAO - campo_id recibido para el almacén: " . var_export($c_i, true) . " (Tipo: " . gettype($c_i) . ")");

    // Primero, verifica si ya existe un almacén con el mismo nombre.
    $sqlVer = "SELECT id FROM almacenes WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    if (!$stmtVer) {
      error_log("DEBUG: AlmacenDAO - Error al preparar la consulta de verificación de nombre: " . $this->conn->error);
      die("Error al preparar consulta de verificación de nombre: " . $this->conn->error);
    }
    $stmtVer->bind_param("s", $nombre);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      error_log("DEBUG: AlmacenDAO - Ya existe un almacén con el nombre '" . $nombre . "'. Devolviendo false.");
      $stmtVer->close();
      return false; // Retorna false si el nombre ya está en uso.
    }
    $stmtVer->close();

    // **NUEVA COMPROBACIÓN: Verifica si el campo_id existe en la tabla 'campos'.**
    $checkCampoSql = "SELECT COUNT(*) FROM campos WHERE id = ?";
    $checkCampoStmt = $this->conn->prepare($checkCampoSql);
    if (!$checkCampoStmt) {
      error_log("DEBUG: AlmacenDAO - Error al preparar la consulta de verificación de ID de campo: " . $this->conn->error);
      die("Error al preparar consulta de verificación de campo: " . $this->conn->error);
    }
    $checkCampoStmt->bind_param("i", $c_i);
    $checkCampoStmt->execute();
    $checkCampoResult = $checkCampoStmt->get_result();
    $row = $checkCampoResult->fetch_row();
    $campoExists = ($row[0] > 0);
    $checkCampoStmt->close();

    error_log("DEBUG: AlmacenDAO - Verificando si campo_id " . var_export($c_i, true) . " existe en la tabla 'campos': " . ($campoExists ? 'SÍ' : 'NO'));

    // Si el campo no existe, podrías devolver un error más específico.
    if (!$campoExists) {
      error_log("DEBUG: AlmacenDAO - El campo_id " . var_export($c_i, true) . " NO existe en la tabla 'campos'. Fallo de inserción prevenido.");
      // Se podría lanzar una excepción o devolver false. El código actual permite que el error
      // de clave foránea sea manejado por MySQL en el siguiente paso.
    }

    // Ahora, intenta insertar el almacén.
    $sql = "INSERT INTO almacenes (nombre, campo_id) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      error_log("DEBUG: AlmacenDAO - Error al preparar la consulta de inserción: " . $this->conn->error);
      die("Error al preparar consulta de inserción: " . $this->conn->error);
    }
    $stmt->bind_param("si", $nombre, $c_i); // 's' para nombre, 'i' para campo_id.

    if (!$stmt->execute()) { // Aquí puede ocurrir el error de clave foránea.
      error_log("DEBUG: AlmacenDAO - Error al ejecutar INSERT para el almacén '" . $nombre . "': " . $stmt->error);
      die("Error en execute (inserción): " . $stmt->error);
    }

    $stmt->close();

    error_log("DEBUG: AlmacenDAO - Almacén '" . $nombre . "' registrado exitosamente.");
    return true;
  }

  /**
   * Modifica un almacén existente.
   *
   * @param Almacen $a El objeto Almacen con los datos actualizados.
   * @return bool True si la modificación fue exitosa, de lo contrario, false.
   */
  public function modificarAlmacen(Almacen $a)
  {
    $sql = "UPDATE almacenes SET campo_id = ? WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $n = $a->getNombre();
    $c_i = $a->getCampo_id();
    $stmt->bind_param('is', $c_i, $n); // 'i' para campo_id, 's' para nombre.

    return $stmt->execute();
  }

  /**
   * Elimina un almacén por su nombre.
   *
   * @param string $nombre El nombre del almacén a eliminar.
   * @return bool True si la eliminación fue exitosa, de lo contrario, false.
   */
  public function eliminarAlmacen($nombre)
  {
    $sql = "DELETE FROM almacenes WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    return $stmt->execute();
  }
}