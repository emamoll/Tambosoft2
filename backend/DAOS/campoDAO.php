<?php

// Incluye los archivos necesarios para la conexión a la base de datos, los modelos y las tablas.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/campo/campoTabla.php';
require_once __DIR__ . '../../modelos/campo/campoModelo.php';
require_once __DIR__ . '../../modelos/almacen/almacenTabla.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'campos'.
 * Maneja las operaciones de la base de datos relacionadas con los campos.
 */
class campoDAO
{
  // Propiedades privadas para la conexión y la creación de tablas.
  private $db;
  private $conn;
  private $crearTabla;
  private $crearTablaAlmacen;

  /**
   * Constructor de la clase.
   * Inicializa la conexión y se asegura de que las tablas 'campos' y 'almacenes' existan.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new CampoCrearTabla($this->db);
    $this->crearTabla->crearTablaCampos();
    $this->crearTablaAlmacen = new AlmacenCrearTabla($this->db);
    $this->crearTablaAlmacen->crearTablaAlmacen();
    $this->conn = $this->db->connect();
  }

  /**
   * Obtiene todos los campos de la base de datos.
   *
   * @return array Un array de objetos Campo.
   */
  public function getAllCampos()
  {
    $sql = "SELECT * FROM campos";
    $result = $this->conn->query($sql);

    // Si la consulta falla, detiene la ejecución.
    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $campos = [];
    // Recorre los resultados y crea un objeto Campo por cada fila.
    while ($row = $result->fetch_assoc()) {
      $campos[] = new Campo($row['id'], $row['nombre'], $row['ubicacion']);
    }

    return $campos;
  }

  /**
   * Obtiene un campo por su ID.
   *
   * @param int $id El ID del campo.
   * @return Campo|null Un objeto Campo si se encuentra, de lo contrario, null.
   */
  public function getCampoById($id)
  {
    $sql = "SELECT * FROM campos WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id); // 'i' indica que el parámetro es un entero.
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    // Si se encuentra una fila, crea y retorna un objeto Campo.
    if ($row = $result->fetch_assoc()) {
      return new Campo($row['id'], $row['nombre'], $row['ubicacion']);
    }
    return null;
  }

  /**
   * Obtiene un campo por su nombre.
   *
   * @param string $nombre El nombre del campo.
   * @return Campo|null Un objeto Campo si se encuentra, de lo contrario, null.
   */
  public function getCampoByNombre($nombre)
  {
    $sql = "SELECT * FROM campos WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre); // 's' indica que el parámetro es una cadena.
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $ubicacion);
    $stmt->fetch();

    return new Campo($id, $nombre, $ubicacion);
  }

  /**
   * Registra un nuevo campo y un almacén asociado.
   *
   * @param Campo $c El objeto Campo a registrar.
   * @return bool True si el registro fue exitoso, de lo contrario, false.
   */
  public function registrarCampo(Campo $c)
  {
    // 1. Verifica si ya existe un campo con ese nombre.
    $sqlVer = "SELECT id FROM campos WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    $nombre = $c->getNombre();
    $stmtVer->bind_param("s", $nombre);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      $stmtVer->close();
      return false; // Retorna false si el nombre ya está en uso.
    }
    $stmtVer->close();

    // 2. Inserta el campo en la tabla 'campos'.
    $sql = "INSERT INTO campos (nombre, ubicacion) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    $n = $c->getNombre();
    $u = $c->getUbicacion();
    $stmt->bind_param("ss", $n, $u);

    if (!$stmt->execute()) {
      $stmt->close();
      return false;
    }

    // 3. Obtiene el ID del campo recién insertado.
    $campo_id = $stmt->insert_id;
    $stmt->close();

    // 4. Inserta un almacén con el mismo nombre y el ID del campo.
    $sqlAlm = "INSERT INTO almacenes (nombre, campo_id) VALUES (?, ?)";
    $stmtAlm = $this->conn->prepare($sqlAlm);
    $stmtAlm->bind_param("si", $n, $campo_id);

    $resultado = $stmtAlm->execute();
    $stmtAlm->close();

    return $resultado;
  }

  /**
   * Modifica un campo existente.
   *
   * @param Campo $c El objeto Campo con los datos actualizados.
   * @return bool True si la modificación fue exitosa, de lo contrario, false.
   */
  public function modificarCampo(Campo $c)
  {
    $sql = "UPDATE campos SET ubicacion = ? WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $n = $c->getNombre();
    $u = $c->getUbicacion();
    $stmt->bind_param("ss", $u, $n); // 's' para ubicación, 's' para nombre.

    return $stmt->execute();
  }

  /**
   * Elimina un campo por su nombre.
   *
   * @param string $nombre El nombre del campo a eliminar.
   * @return bool True si la eliminación fue exitosa, de lo contrario, false.
   */
  public function eliminarCampo($nombre)
  {
    $sql = "DELETE FROM campos WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    return $stmt->execute();
  }
}