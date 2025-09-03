<?php

// Incluye los archivos necesarios para la fábrica de bases de datos y la interfaz de conexión.
require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

/**
 * Clase encargada de crear la tabla `ordenes_canceladas` en la base de datos.
 */
class Orden_canceladaCrearTabla
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
   * Crea la tabla `ordenes_canceladas` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `orden_id`: Entero, no nulo, clave foránea que referencia a `ordenes(id)`.
   * - `descripcion`: Cadena de texto para la razón de la cancelación.
   * - `fecha`: Fecha de la cancelación.
   * - `hora`: Hora de la cancelación.
   */
  public function crearTablaOrdenes_canceladas()
  {
    // Crea una nueva conexión a la base de datos.
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Sentencia SQL para la creación de la tabla.
    $sql = "CREATE TABLE IF NOT EXISTS ordenes_canceladas (
              id INT PRIMARY KEY AUTO_INCREMENT,
              orden_id INT NOT NULL,
              descripcion VARCHAR(255) NOT NULL,
              fecha DATE NOT NULL,
              hora TIME NOT NULL,
              FOREIGN KEY (orden_id) REFERENCES ordenes(id)
            )";

    // Ejecuta la consulta y cierra la conexión.
    $conn->query($sql);
    $conn->close();
  }
}