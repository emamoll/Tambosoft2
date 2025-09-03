<?php

// Incluye los archivos necesarios para la fábrica de bases de datos y la interfaz de conexión.
require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

/**
 * Clase encargada de crear la tabla `potreros` en la base de datos.
 */
class PotreroCrearTabla
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
   * Crea la tabla `potreros` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `nombre`: Cadena de texto, no nula, única.
   * - `superficie`: Cadena de texto, no nula.
   * - `pastura_id`, `categoria_id`, `campo_id`: Enteros, no nulos, claves foráneas.
   */
  public function crearTablaPotrero()
  {
    // Crea una nueva conexión a la base de datos.
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Sentencia SQL para la creación de la tabla.
    $sql = "CREATE TABLE IF NOT EXISTS potreros (
              id INT PRIMARY KEY AUTO_INCREMENT,
              nombre VARCHAR(255) NOT NULL UNIQUE,
              superficie VARCHAR(255) NOT NULL,
              pastura_id INT NOT NULL,
              categoria_id INT NOT NULL,
              campo_id INT NOT NULL,
              FOREIGN KEY (pastura_id) REFERENCES pasturas(id),
              FOREIGN KEY (categoria_id) REFERENCES categorias(id),
              FOREIGN KEY (campo_id) REFERENCES campos(id))";

    // Ejecuta la consulta y cierra la conexión.
    $conn->query($sql);
    $conn->close();
  }
}