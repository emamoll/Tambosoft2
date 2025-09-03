<?php

// Incluye los archivos necesarios para la fábrica de bases de datos y la interfaz de conexión.
require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

/**
 * Clase encargada de crear la tabla `estados` y de insertar sus valores iniciales.
 */
class EstadoCrearTabla
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
   * Crea la tabla `estados` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `nombre`: Cadena de texto, no nula, única.
   */
  public function crearTablaEstados()
  {
    // Crea una nueva conexión a la base de datos.
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Sentencia SQL para la creación de la tabla.
    $sql = "CREATE TABLE IF NOT EXISTS estados (
              id INT PRIMARY KEY AUTO_INCREMENT, 
              nombre VARCHAR(255) NOT NULL UNIQUE)";

    // Ejecuta la consulta y cierra la conexión.
    $conn->query($sql);
    $conn->close();
  }

  /**
   * Inserta los valores predeterminados en la tabla `estados` si está vacía.
   *
   * Los valores insertados son:
   * - 'creada'
   * - 'preparacion'
   * - 'en traslado'
   * - 'entregada'
   * - 'cancelada'
   */
  public function insertarValoresTablaEstados()
  {
    // Crea una nueva conexión a la base de datos.
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();

    // Consulta para contar el número de filas en la tabla.
    $resultado = $conn->query("SELECT COUNT(*) as total FROM estados");
    $fila = $resultado->fetch_assoc();

    // Si la tabla está vacía, inserta los valores predeterminados.
    if ($fila['total'] == 0) {
      $sql = "INSERT INTO estados (nombre) VALUES 
          ('creada'), 
          ('preparacion'), 
          ('en traslado'), 
          ('entregada'), 
          ('cancelada')";
      $conn->query($sql);
    }

    // Cierra la conexión.
    $conn->close();
  }
}