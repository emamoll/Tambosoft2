<?php

// Incluye los archivos necesarios para la fábrica de bases de datos y la interfaz de conexión.
require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

/**
 * Clase encargada de crear la tabla `alimentos` en la base de datos.
 */
class AlimentoCrearTabla
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
   * Crea la tabla `alimentos` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `nombre`: Cadena de texto, no nula, única.
   * - `precio`: Número decimal, no nulo.
   * - `descripcion`: Cadena de texto, no nula.
   * - `peso`: Número decimal, no nulo.
   * - `fecha_vencimiento`: Fecha, no nula.
   */
  public function crearTablaAlimentos()
  {
    // Crea una nueva conexión a la base de datos.
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Sentencia SQL para la creación de la tabla.
    $sql = "CREATE TABLE IF NOT EXISTS alimentos(
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL UNIQUE,
            precio DECIMAL (10,2) NOT NULL,
            descripcion VARCHAR(255) NOT NULL,
            peso DECIMAL (10,2) NOT NULL,
            fecha_vencimiento DATE NOT NULL)";

    // Ejecuta la consulta y cierra la conexión.
    $conn->query($sql);
    $conn->close();
  }
}