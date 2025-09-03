<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla de la pastura.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/pastura/pasturaModelo.php';
require_once __DIR__ . '../../modelos/pastura/pasturaTabla.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'pasturas'.
 * Maneja las operaciones de la base de datos relacionadas con las pasturas.
 */
class PasturaDAO
{
  // Propiedades privadas para la conexión y la creación de la tabla.
  private $db;
  private $conn;
  private $crearTabla;

  /**
   * Constructor de la clase.
   * Inicializa la conexión y se asegura de que la tabla 'pasturas' exista.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new PasturaCrearTabla($this->db);
    $this->crearTabla->crearTablaPastura();
    $this->conn = $this->db->connect();
  }

  /**
   * Registra una nueva pastura en la base de datos.
   *
   * @param Pastura $p El objeto Pastura a registrar.
   * @return bool True si el registro fue exitoso, de lo contrario, false.
   */
  public function registrarPastura(Pastura $p)
  {
    // 1. Verifica si ya existe una pastura con el mismo nombre.
    $sqlVer = "SELECT id FROM pasturas WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    $nombrePastura = $p->getNombre();
    $stmtVer->bind_param("s", $nombrePastura);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      $stmtVer->close();
      return false; // La pastura ya existe.
    }
    $stmtVer->close();

    // 2. Si no existe, inserta la nueva pastura.
    $sql = "INSERT INTO pasturas (nombre) VALUES (?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombrePastura);

    if (!$stmt->execute()) {
      error_log("Error al insertar pastura: " . $stmt->error);
      $stmt->close();
      return false;
    }
    $stmt->close();
    return true;
  }

  /**
   * Obtiene todas las pasturas de la base de datos.
   *
   * @return array Un array de objetos Pastura.
   */
  public function getAllPasturas()
  {
    $sql = "SELECT * FROM pasturas";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $pasturas = [];
    while ($row = $result->fetch_assoc()) {
      $pasturas[] = new Pastura($row['id'], $row['nombre']);
    }
    return $pasturas;
  }

  /**
   * Obtiene una pastura por su ID.
   *
   * @param int $id El ID de la pastura.
   * @return Pastura|null Un objeto Pastura si se encuentra, de lo contrario, null.
   */
  public function getPasturaById($id)
  {
    $sql = "SELECT * FROM pasturas WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
      return new Pastura($row['id'], $row['nombre']);
    }
    return null;
  }

  /**
   * Obtiene una pastura por su nombre.
   *
   * @param string $nombre El nombre de la pastura.
   * @return Pastura|null Un objeto Pastura si se encuentra, de lo contrario, null.
   */
  public function getPasturaByNombre($nombre)
  {
    $sql = "SELECT * FROM pasturas WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
      return new Pastura($row['id'], $row['nombre']);
    }
    return null;
  }

  /**
   * Modifica una pastura existente.
   *
   * @param Pastura $p El objeto Pastura con los datos actualizados.
   * @return bool True si la modificación fue exitosa, de lo contrario, false.
   */
  public function modificarPastura(Pastura $p)
  {
    $sql = "UPDATE pasturas SET nombre = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $nombre = $p->getNombre();
    $id = $p->getId();
    $stmt->bind_param("si", $nombre, $id);

    return $stmt->execute();
  }

  /**
   * Elimina una pastura por su nombre.
   *
   * @param string $nombre El nombre de la pastura a eliminar.
   * @return bool True si la eliminación fue exitosa, de lo contrario, false.
   */
  public function eliminarPastura($nombre)
  {
    $sql = "DELETE FROM pasturas WHERE n$nombre = ?"; // Se encontró un error de sintaxis aquí. ¡Corregir!
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    return $stmt->execute();
  }
}