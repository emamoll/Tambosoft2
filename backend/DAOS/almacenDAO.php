<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/almacen/almacenTabla.php';
require_once __DIR__ . '../../modelos/almacen/almacenModelo.php';

class AlmacenDAO
{
  private $db;
  private $conn;
  private $crearTabla;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new AlmacenCrearTabla($this->db);
    $this->crearTabla->crearTablaAlmacen();
    $this->conn = $this->db->connect();
  }

  public function getAllAlmacenes()
  {
    $sql = "SELECT * FROM almacenes";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $almacenes = [];

    while ($row = $result->fetch_assoc()) {
      $almacenes[] = new Almacen($row['id'], $row['nombre'], $row['campo_id']);
    }

    return $almacenes;
  }

  public function getAlmacenById($id)
  {
    $sql = "SELECT * FROM almacenes WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $campo_id);
    $stmt->fetch();

    return new Almacen($id, $nombre, $campo_id);
  }

  public function getAlmacenByNombre($nombre)
  {
    $sql = "SELECT * FROM almacenes WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $campo_id);
    $stmt->fetch();

    return new Almacen($id, $nombre, $campo_id);
  }

  public function getAlmacenByCampoId($campo_id)
  {
    $sql = "SELECT * FROM almacenes WHERE campo_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $campo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $almacenes = [];
    while ($row = $result->fetch_assoc()) {
      $almacenes[] = new Almacen($row['id'], $row['campo_id']);
    }

    return $almacenes;
  }

  public function registrarAlmacen(Almacen $a)
  {
    $nombre = $a->getNombre();
    $c_i = $a->getCampo_id(); // Obtiene el campo_id del objeto Almacen

    error_log("DEBUG: AlmacenDAO - Intentando registrar almacén: " . $nombre);
    error_log("DEBUG: AlmacenDAO - campo_id recibido para el almacén: " . var_export($c_i, true) . " (Tipo: " . gettype($c_i) . ")");

    // Primero, verifica la restricción de nombre único para almacenes
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
      return false;
    }
    $stmtVer->close();

    // **NUEVA COMPROBACIÓN: Verifica si el campo_id existe en la tabla 'campos'**
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

    // Si el campo no existe, podríamos lanzar un error más claro aquí
    if (!$campoExists) {
      error_log("DEBUG: AlmacenDAO - El campo_id " . var_export($c_i, true) . " NO existe en la tabla 'campos'. Fallo de inserción prevenido.");
      // Podrías devolver false o una excepción personalizada en lugar de solo continuar al die()
      // O dejar que el FOREIGN KEY Constraint falle si quieres que MySQL lo maneje.
      // Pero esta línea de log te dirá si el problema es que el ID realmente no está.
    }


    // Ahora, intenta insertar el almacén
    $sql = "INSERT INTO almacenes (nombre, campo_id) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      error_log("DEBUG: AlmacenDAO - Error al preparar la consulta de inserción: " . $this->conn->error);
      die("Error al preparar consulta de inserción: " . $this->conn->error);
    }
    $stmt->bind_param("si", $nombre, $c_i); // $nombre es $n

    if (!$stmt->execute()) { // Aquí es donde suele ocurrir el error de clave foránea
      error_log("DEBUG: AlmacenDAO - Error al ejecutar INSERT para el almacén '" . $nombre . "': " . $stmt->error);
      die("Error en execute (inserción): " . $stmt->error);
    }

    $stmt->close();

    error_log("DEBUG: AlmacenDAO - Almacén '" . $nombre . "' registrado exitosamente.");
    return true;
  }

  public function modificarAlmacen(Almacen $a)
  {
    $sql = "UPDATE almacenes SET campo_id = ? WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $n = $a->getNombre();
    $c_i = $a->getCampo_id();
    $stmt->bind_param('is', $c_i, $n);

    return $stmt->execute();
  }

  public function eliminarAlmacen($nombre)
  {
    $sql = "DELETE FROM almacenes WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    return $stmt->execute();
  }
}