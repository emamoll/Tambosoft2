<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla del estado.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/estado/estadoTabla.php';
require_once __DIR__ . '../../modelos/estado/estadoModelo.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'estados'.
 * Maneja las operaciones de la base de datos relacionadas con los estados.
 */
class EstadoDAO
{
  // Propiedades privadas para la conexión y la creación de la tabla.
  private $db;
  private $conn;
  private $crearTabla;

  /**
   * Constructor de la clase.
   * Inicializa la conexión, crea la tabla 'estados' y sus valores iniciales.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new EstadoCrearTabla($this->db);
    $this->crearTabla->crearTablaEstados();
    $this->crearTabla->insertarValoresTablaEstados();
    $this->conn = $this->db->connect();
  }

  /**
   * Obtiene todos los estados de la base de datos.
   *
   * @return array Un array de objetos Estado.
   */
  public function getAllEstados()
  {
    $sql = "SELECT * FROM estados";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $estados = [];
    while ($row = $result->fetch_assoc()) {
      $estados[] = new Estado($row['id'], $row['nombre']);
    }

    return $estados;
  }

  /**
   * Obtiene un estado por su ID.
   *
   * @param int $id El ID del estado.
   * @return Estado|null Un objeto Estado si se encuentra, de lo contrario, null.
   */
  public function getEstadoById($id)
  {
    $sql = "SELECT * FROM estados WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $id); // Se usa 's' (cadena) aquí, pero debería ser 'i' (entero). ¡Revisar!
    $stmt->execute();
    $stmt->store_result();

    if (!$stmt) {
      die("Error al preparar la consulta: " . $this->conn->error);
    }

    $stmt->bind_param("i", $id); // Se corrigió el tipo de parámetro.
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
      return null;
    }

    $row = $resultado->fetch_assoc();
    return new Estado($row['id'], $row['nombre']);
  }
}