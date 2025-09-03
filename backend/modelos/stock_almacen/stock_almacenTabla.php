<?php

// Incluye los archivos necesarios para la fábrica de bases de datos y la interfaz de conexión.
require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

/**
 * Clase encargada de crear la tabla `stock_almacenes` en la base de datos.
 */
class Stock_AlmacenCrearTabla
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
   * Crea la tabla `stock_almacenes` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `almacen_id`, `alimento_id`: Enteros, no nulos, claves foráneas.
   * - `stock`: Entero, no nulo.
   * - Restricción `UNIQUE` para asegurar que solo haya un registro por combinación de almacén y alimento.
   */
  public function crearTablaStock()
  {
    // Crea una nueva conexión a la base de datos.
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Sentencia SQL para la creación de la tabla.
    $sql = "CREATE TABLE IF NOT EXISTS stock_almacenes(
              id INT PRIMARY KEY AUTO_INCREMENT,
              almacen_id INT NOT NULL,
              alimento_id INT NOT NULL,
              stock INT NOT NULL,
              FOREIGN KEY (almacen_id) REFERENCES almacenes(id),
              FOREIGN KEY (alimento_id) REFERENCES alimentos(id),
              UNIQUE (almacen_id, alimento_id))";

    // Ejecuta la consulta y cierra la conexión.
    $conn->query($sql);
    $conn->close();
  }
}