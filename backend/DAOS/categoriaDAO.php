<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla de la categoría.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/categoria/categoriaTabla.php';
require_once __DIR__ . '../../modelos/categoria/categoriaModelo.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'categorias'.
 * Maneja las operaciones de la base de datos relacionadas con las categorías.
 */
class CategoriaDAO
{
  // Propiedades privadas para la conexión y la creación de la tabla.
  private $db;
  private $conn;
  private $crearTabla;

  /**
   * Constructor de la clase.
   * Inicializa la conexión y se asegura de que la tabla 'categorias' exista.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new CategoriaCrearTabla($this->db);
    $this->crearTabla->crearTablaCategoria();
    $this->conn = $this->db->connect();
  }

  /**
   * Registra una nueva categoría en la base de datos.
   *
   * @param Categoria $c El objeto Categoria a registrar.
   * @return bool True si el registro fue exitoso, de lo contrario, false.
   */
  public function registrarCategoria(Categoria $c)
  {
    // 1. Verifica si ya existe una categoría con el mismo nombre.
    $sqlVer = "SELECT id FROM categorias WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    $nombreCategoria = $c->getNombre();
    $stmtVer->bind_param("s", $nombreCategoria);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      $stmtVer->close();
      return false; // La categoría ya existe.
    }
    $stmtVer->close();

    // 2. Si no existe, inserta la nueva categoría.
    $sql = "INSERT INTO categorias (nombre) VALUES (?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombreCategoria);

    if (!$stmt->execute()) {
      error_log("Error al insertar categoría: " . $stmt->error);
      $stmt->close();
      return false;
    }
    $stmt->close();
    return true;
  }

  /**
   * Obtiene todas las categorías de la base de datos.
   *
   * @return array Un array de objetos Categoria.
   */
  public function getAllCategorias()
  {
    $sql = "SELECT * FROM categorias";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $categorias = [];
    while ($row = $result->fetch_assoc()) {
      $categorias[] = new Categoria($row['id'], $row['nombre']);
    }

    return $categorias;
  }

  /**
   * Obtiene una categoría por su ID.
   *
   * @param int $id El ID de la categoría.
   * @return Categoria|null Un objeto Categoria si se encuentra, de lo contrario, null.
   */
  public function getCategoriaById($id)
  {
    $sql = "SELECT * FROM categorias WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
      return new Categoria($row['id'], $row['nombre']);
    }
    return null;
  }

  /**
   * Obtiene una categoría por su nombre.
   *
   * @param string $nombre El nombre de la categoría.
   * @return Categoria|null Un objeto Categoria si se encuentra, de lo contrario, null.
   */
  public function getCategoriaByNombre($nombre)
  {
    $sql = "SELECT * FROM categorias WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
      return new Categoria($row['id'], $row['nombre']);
    }
    return null;
  }

  /**
   * Modifica una categoría existente.
   *
   * @param Categoria $c El objeto Categoria con los datos actualizados.
   * @return bool True si la modificación fue exitosa, de lo contrario, false.
   */
  public function modificarCategoria(Categoria $c)
  {
    $sql = "UPDATE categorias SET nombre = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $nombre = $c->getNombre();
    $id = $c->getId();
    $stmt->bind_param("si", $nombre, $id);

    return $stmt->execute();
  }

  /**
   * Elimina una categoría por su nombre.
   *
   * @param string $nombre El nombre de la categoría a eliminar.
   * @return bool True si la eliminación fue exitosa, de lo contrario, false.
   */
  public function eliminarCategoria($nombre)
  {
    $sql = "DELETE FROM categorias WHERE n$nombre = ?"; // Se encontró un error de sintaxis aquí. ¡Corregir!
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    return $stmt->execute();
  }
}