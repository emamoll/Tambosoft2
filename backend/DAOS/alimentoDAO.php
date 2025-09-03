<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla del alimento.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/alimento/alimentoTabla.php';
require_once __DIR__ . '../../modelos/alimento/alimentoModelo.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'alimentos'.
 * Maneja todas las operaciones de la base de datos relacionadas con los alimentos.
 */
class AlimentoDAO
{
  // Propiedades privadas para la conexión y la creación de la tabla.
  private $db;
  private $conn;
  private $crearTabla;

  /**
   * Constructor de la clase.
   * Inicializa la conexión y se asegura de que la tabla 'alimentos' exista.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new AlimentoCrearTabla($this->db);
    $this->crearTabla->crearTablaAlimentos();
    $this->conn = $this->db->connect();
  }

  /**
   * Obtiene todos los alimentos de la base de datos.
   *
   * @return array Un array de objetos Alimento.
   */
  public function getAllAlimentos()
  {
    $sql = "SELECT * FROM alimentos";
    $result = $this->conn->query($sql);

    // Si la consulta falla, detiene la ejecución.
    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $alimentos = [];
    // Recorre los resultados y crea un objeto Alimento por cada fila.
    while ($row = $result->fetch_assoc()) {
      $alimentos[] = new Alimento($row['id'], $row['nombre'], $row['precio'], $row['descripcion'], $row['peso'], $row['fecha_vencimiento']);
    }

    return $alimentos;
  }

  /**
   * Obtiene un alimento por su ID.
   *
   * @param int $id El ID del alimento.
   * @return Alimento|null Un objeto Alimento si se encuentra, de lo contrario, null.
   */
  public function getAlimentoById($id)
  {
    $sql = "SELECT * FROM alimentos WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $id); // Se usa 's' (cadena) aquí, pero debería ser 'i' (entero) para un ID. ¡Revisar!
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  /**
   * Obtiene un alimento por su nombre.
   *
   * @param string $nombre El nombre del alimento.
   * @return Alimento|null Un objeto Alimento si se encuentra, de lo contrario, null.
   */
  public function getAlimentoByNombre($nombre)
  {
    $sql = "SELECT * FROM alimentos WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  /**
   * Obtiene un alimento por su precio.
   *
   * @param string $precio El precio del alimento.
   * @return Alimento|null Un objeto Alimento si se encuentra, de lo contrario, null.
   */
  public function getAlimentoByPrecio($precio)
  {
    $sql = "SELECT * FROM alimentos WHERE precio = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $precio);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  /**
   * Obtiene un alimento por su peso.
   *
   * @param string $peso El peso del alimento.
   * @return Alimento|null Un objeto Alimento si se encuentra, de lo contrario, null.
   */
  public function getAlimentoByPeso($peso)
  {
    $sql = "SELECT * FROM alimentos WHERE peso = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $peso);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  /**
   * Obtiene un alimento por su fecha de vencimiento.
   *
   * @param string $fecha_vencimiento La fecha de vencimiento.
   * @return Alimento|null Un objeto Alimento si se encuentra, de lo contrario, null.
   */
  public function getAlimentoByFechaVencimiento($fecha_vencimiento)
  {
    $sql = "SELECT * FROM alimentos WHERE fecha_vencimiento) = ?"; // Se encontró un paréntesis extra aquí. ¡Corregir!
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $fecha_vencimiento);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  /**
   * Obtiene una lista de alimentos por un array de IDs.
   *
   * @param array $ids Un array de IDs de alimentos.
   * @return array Un array de objetos Alimento.
   */
  public function getAlimentosPorIds(array $ids)
  {
    if (empty($ids)) {
      return [];
    }

    // Crea un string con los placeholders '?' para la cláusula IN.
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM alimentos WHERE id IN ($placeholders)";
    $stmt = $this->conn->prepare($sql);

    if ($stmt === false) {
      die("Error en prepare: " . $this->conn->error);
    }

    // Vincula los parámetros dinámicamente.
    $tipos = str_repeat('i', count($ids));
    $refs = [];
    foreach ($ids as $key => $value) {
      $refs[$key] = &$ids[$key];
    }
    array_unshift($refs, $tipos);
    call_user_func_array([$stmt, 'bind_param'], $refs);

    $stmt->execute();
    $result = $stmt->get_result();
    $alimentos = [];
    while ($row = $result->fetch_assoc()) {
      $alimentos[] = new Alimento(
        $row['id'],
        $row['nombre'],
        $row['precio'],
        $row['descripcion'],
        $row['peso'],
        $row['fecha_vencimiento']
      );
    }
    $stmt->close();
    return $alimentos;
  }

  /**
   * Registra un nuevo alimento.
   *
   * @param Alimento $a El objeto Alimento a registrar.
   * @return bool True si el registro fue exitoso, de lo contrario, false.
   */
  public function registrarAlimento(Alimento $a)
  {
    // Primero, verifica si ya existe un alimento con el mismo nombre.
    $sqlVer = "SELECT id FROM alimentos WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    $nombre = $a->getNombre();
    $stmtVer->bind_param("s", $nombre);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      return false;
    }
    $stmtVer->close();

    // Si no existe, procede a insertar el nuevo alimento.
    $sql = "INSERT INTO alimentos (nombre, precio, descripcion, peso, fecha_vencimiento) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $n = $a->getNombre();
    $p = $a->getPrecio();
    $d = $a->getDescripcion();
    $pe = $a->getPeso();
    $f = $a->getFecha_vencimiento();
    $stmt->bind_param("sssss", $n, $p, $d, $pe, $f); // Se usa 's' para todos los tipos, lo cual puede ser incorrecto para números. ¡Revisar!

    if (!$stmt->execute()) {
      die("Error en execute (inserción): " . $stmt->error);
    }
    $stmt->close();
    return true;
  }

  /**
   * Modifica un alimento existente.
   *
   * @param Alimento $a El objeto Alimento con los datos actualizados.
   * @return bool True si la modificación fue exitosa, de lo contrario, false.
   */
  public function modificarAlimento(Alimento $a)
  {
    $sql = "UPDATE alimentos SET precio = ?, descripcion = ?, peso = ?, fecha_vencimiento = ? WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $n = $a->getNombre();
    $p = $a->getPrecio();
    $d = $a->getDescripcion();
    $pe = $a->getPeso();
    $f = $a->getFecha_vencimiento();
    $stmt->bind_param('sssss', $p, $d, $pe, $f, $n); // Se usa 's' para todos los tipos. ¡Revisar!

    return $stmt->execute();
  }

  /**
   * Elimina un alimento por su nombre.
   *
   * @param string $nombre El nombre del alimento a eliminar.
   * @return bool True si la eliminación fue exitosa, de lo contrario, false.
   */
  public function eliminarAlimento($nombre)
  {
    $sql = "DELETE FROM alimentos WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    return $stmt->execute();
  }
}