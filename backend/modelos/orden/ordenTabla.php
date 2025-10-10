<?php

// Incluye los archivos necesarios para la fábrica de bases de datos y la interfaz de conexión.
require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

/**
 * Clase encargada de crear las tablas `estados` y `ordenes` en la base de datos,
 * así como de insertar los valores iniciales para la tabla `estados`.
 */
class OrdenCrearTabla
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
   */
  public function crearTablaEstados()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS estados (
              id INT PRIMARY KEY AUTO_INCREMENT, 
              nombre VARCHAR(255) NOT NULL UNIQUE)";
    $conn->query($sql);
    $conn->close();
  }

  /**
   * Inserta los valores predeterminados en la tabla `estados` si no existen.
   *
   * Usa `INSERT IGNORE` para evitar errores si los valores ya están presentes.
   */
  public function insertarValoresTablaEstados()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "INSERT IGNORE INTO estados (nombre) 
            VALUES ('creada'), ('enviada'), ('en preparacion para envio'), ('trasladando a campo'), ('entregada en campo'), ('cancelado')";
    $conn->query($sql);
    $conn->close();
  }

  /**
   * Crea la tabla `ordenes` si no existe.
   *
   * La tabla tiene las siguientes columnas:
   * - `id`: Entero, clave primaria, auto-incremental.
   * - `almacen_id`, `alimento_id`, `estado_id`: Enteros, claves foráneas a sus respectivas tablas.
   * - `cantidad`: Entero, no nulo.
   * - `fecha_creacion`, `fecha_actualizacion`: Fechas, no nulas.
   * - `hora_creacion`, `hora_actualizacion`: Tiempos, no nulos.
   */
  public function crearTablaOrden()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS ordenes(
            id INT PRIMARY KEY AUTO_INCREMENT,
            almacen_id INT NOT NULL,
            alimento_id INT NOT NULL,
            cantidad INT NOT NULL,
            categoria_id INT NOT NULL,
            fecha_creacion DATE NOT NULL,
            hora_creacion TIME NOT NULL,
            fecha_actualizacion DATE NOT NULL,
            hora_actualizacion TIME NOT NULL,
            estado_id INT NOT NULL,
            FOREIGN KEY (almacen_id) REFERENCES almacenes(id),
            FOREIGN KEY (alimento_id) REFERENCES alimentos(id),
            FOREIGN KEY (categoria_id) REFERENCES categorias(id),
            FOREIGN KEY (estado_id) REFERENCES estados(id)            
            )";
    $conn->query($sql);
    $conn->close();
  }
}