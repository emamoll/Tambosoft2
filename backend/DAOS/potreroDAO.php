<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla del potrero.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/potrero/potreroTabla.php';
require_once __DIR__ . '../../modelos/potrero/potreroModelo.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'potreros'.
 * Maneja las operaciones de la base de datos relacionadas con los potreros.
 */
class PotreroDAO
{
  // Propiedades privadas para la conexión y la creación de la tabla.
  private $db;
  private $conn;
  private $crearTabla;

  /**
   * Constructor de la clase.
   * Inicializa la conexión y se asegura de que la tabla 'potreros' exista.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new PotreroCrearTabla($this->db);
    $this->crearTabla->crearTablaPotrero();
    $this->conn = $this->db->connect();
  }

  /**
   * Obtiene todos los potreros de la base de datos.
   *
   * @return array Un array de objetos Potrero.
   */
  public function getAllPotreros()
  {
    $sql = "SELECT * FROM potreros";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $potreros = [];
    while ($row = $result->fetch_assoc()) {
      $potreros[] = new Potrero($row['id'], $row['nombre'], $row['superficie'], $row['pastura_id'], $row['categoria_id'], $row['campo_id']);
    }
    return $potreros;
  }

  /**
   * Obtiene un potrero por su ID.
   *
   * @param int $id El ID del potrero.
   * @return Potrero|null Un objeto Potrero si se encuentra, de lo contrario, null.
   */
  public function getPotreroById($id)
  {
    $sql = "SELECT * FROM potreros WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $superficie, $pastura_id, $categoria_id, $campo_id);
    $stmt->fetch();

    return new Potrero($id, $nombre, $superficie, $pastura_id, $categoria_id, $campo_id);
  }

  /**
   * Obtiene un potrero por su nombre.
   *
   * @param string $nombre El nombre del potrero.
   * @return Potrero|null Un objeto Potrero si se encuentra, de lo contrario, null.
   */
  public function getPotreroByNombre($nombre)
  {
    $sql = "SELECT * FROM potreros WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $superficie, $pastura_id, $categoria_id, $campo_id);
    $stmt->fetch();

    return new Potrero($id, $nombre, $superficie, $pastura_id, $categoria_id, $campo_id);
  }

  /**
   * Obtiene un potrero por el ID de la pastura.
   *
   * @param int $pastura_id El ID de la pastura.
   * @return Potrero|null Un objeto Potrero si se encuentra, de lo contrario, null.
   */
  public function getPotreroByPastura($pastura_id)
  {
    $sql = "SELECT * FROM potreros WHERE pastura = ?"; // El nombre de la columna es 'pastura_id', no 'pastura'. ¡Corregir!
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $pastura_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $superficie, $pastura_id, $categoria_id,  $campo_id);
    $stmt->fetch();

    return new Potrero($id, $nombre, $superficie, $pastura_id, $categoria_id, $campo_id);
  }

  /**
   * Registra un nuevo potrero.
   *
   * @param Potrero $p El objeto Potrero a registrar.
   * @return bool True si el registro fue exitoso, de lo contrario, false.
   */
  public function registrarPotrero(Potrero $p)
  {
    // 1. Verifica si ya existe un potrero con el mismo nombre.
    $sqlVer = "SELECT id FROM potreros WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    $nombre = $p->getNombre();
    $stmtVer->bind_param("s", $nombre);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      return false;
    }
    $stmtVer->close();

    // 2. Si no existe, inserta el nuevo potrero.
    $sql = "INSERT INTO potreros (nombre, superficie, pastura_id, categoria_id, campo_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $n = $p->getNombre();
    $s = $p->getSuperficie();
    $pas_id = $p->getPastura_id();
    $cat_i = $p->getCategoria_id();
    $c_i = $p->getCampo_id();
    $stmt->bind_param("ssiii", $n, $s, $pas_id, $cat_i, $c_i); // 's' para nombre y superficie, 'i' para los IDs.

    if (!$stmt->execute()) {
      die("Error en execute (inserción): " . $stmt->error);
    }
    $stmt->close();
    return true;
  }

  /**
   * Modifica un potrero existente.
   *
   * @param Potrero $p El objeto Potrero con los datos actualizados.
   * @return bool True si la modificación fue exitosa, de lo contrario, false.
   */
  public function modificarPotrero(Potrero $p)
  {
    $sql = "UPDATE potreros SET superficie = ?, pastura_id = ?, categoria_id = ?, campo_id = ? WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $n = $p->getNombre();
    $s = $p->getSuperficie();
    $pas_id = $p->getPastura_id();
    $cat_i = $p->getCategoria_id();
    $c_i = $p->getCampo_id();
    $stmt->bind_param("siiis", $s, $pas_id, $cat_i, $c_i, $n); // Se usa 's' para superficie, lo cual puede ser incorrecto. ¡Revisar!

    return $stmt->execute();
  }

  /**
   * Elimina un potrero por su nombre.
   *
   * @param string $nombre El nombre del potrero a eliminar.
   * @return bool True si la eliminación fue exitosa, de lo contrario, false.
   */
  public function eliminarPotrero($nombre)
  {
    $sql = "DELETE FROM potreros WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    return $stmt->execute();
  }
}
