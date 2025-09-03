<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla de la orden cancelada.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/orden_cancelada/orden_canceladaTabla.php';
require_once __DIR__ . '../../modelos/orden_cancelada/orden_canceladaModelo.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'ordenes_canceladas'.
 * Maneja las operaciones de la base de datos relacionadas con las órdenes canceladas.
 */
class Orden_canceladaDAO
{
  // Propiedades privadas para la conexión.
  private $db;
  private $conn;

  /**
   * Constructor de la clase.
   * Inicializa la conexión a la base de datos.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    // Falta la línea para crear la tabla de órdenes canceladas. ¡Revisar!
    $this->conn = $this->db->connect();
  }

  /**
   * Registra una nueva orden cancelada en la base de datos.
   *
   * @param Orden_cancelada $o_c El objeto Orden_cancelada a registrar.
   * @return bool True si el registro fue exitoso, de lo contrario, false.
   */
  public function registrarOrden_cancelada(Orden_cancelada $o_c)
  {
    $sql = "INSERT INTO ordenes_canceladas (orden_id, descripcion, fecha, hora) VALUES (?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $orden_id = $o_c->getOrden_id();
    $descripcion = $o_c->getDescripcion();
    $fecha = $o_c->getFecha();
    $hora = $o_c->getHora();
    $stmt->bind_param("isss", $orden_id, $descripcion, $fecha, $hora);

    if (!$stmt->execute()) {
      error_log("Error al registrar cancelación: " . $stmt->error);
      return false;
    }
    $stmt->close();
    return true;
  }

  /**
   * Obtiene los detalles de una orden cancelada por el ID de la orden original.
   *
   * @param int $orden_id El ID de la orden original.
   * @return Orden_cancelada|null Un objeto Orden_cancelada si se encuentra, de lo contrario, null.
   */
  public function getOrden_canceladaByOrdenId($orden_id)
  {
    $sql = "SELECT * FROM ordenes_canceladas WHERE orden_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $orden_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      return new Orden_cancelada($row['id'], $row['orden_id'], $row['descripcion'], $row['fecha'], $row['hora']);
    }
    return null;
  }
}