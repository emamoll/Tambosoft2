<?php

// Incluye los archivos necesarios para la fábrica de bases de datos y la interfaz de conexión.
require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

/**
 * Clase encargada de crear la tabla `almacenes` en la base de datos.
 */
class AlmacenCrearTabla
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
   * Crea la tabla `almacenes` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `nombre`: Cadena de texto, no nula, única.
   * - `campo_id`: Entero, no nulo, clave foránea que referencia a `campos(id)`.
   */
  public function crearTablaAlmacen()
  {
    // Crea una nueva conexión a la base de datos.
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Sentencia SQL para la creación de la tabla.
    $sql = "CREATE TABLE IF NOT EXISTS almacenes(
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL UNIQUE,
            campo_id INT NOT NULL,
            FOREIGN KEY (campo_id) REFERENCES campos(id))";

    // Ejecuta la consulta y cierra la conexión.
    $conn->query($sql);
    $conn->close();
  }
}