<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/orden/ordenTabla.php';
require_once __DIR__ . '../../modelos/orden/ordenModelo.php';

class OrdenDAO
{
  private $db;
  private $conn;
  private $crearTabla;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new OrdenCrearTabla($this->db);
    $this->crearTabla->crearTablaEstados();
    $this->crearTabla->insertarValoresTablaEstados();
    $this->crearTabla->crearTablaOrden();
    $this->conn = $this->db->connect();
  }

  public function getAllOrdenes()
  {
    $sql = "SELECT o.*, c.nombre AS categoria_nombre, a.nombre AS alimento_nombre, a.precio AS alimento_precio, e.nombre AS estado_nombre
            FROM ordenes o
            INNER JOIN categorias c ON o.categoria_id = c.id
            INNER JOIN alimentos a ON o.alimento_id = a.id
            INNER JOIN estados e ON o.estado_id = e.id
            ORDER BY o.id";

    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $ordenes = [];

    while ($row = $result->fetch_assoc()) {
      // Suponiendo que tu modelo Orden acepta esos parámetros o creas un constructor más flexible
      $orden = new Orden(
        $row['id'],
        $row['categoria_id'],
        $row['alimento_id'],
        $row['cantidad'],
        $row['fecha_creacion'],
        $row['hora_creacion'],
        $row['estado_id'],
        $row['categoria_nombre'],
        $row['alimento_nombre'],
        $row['alimento_precio'],
        $row['estado_nombre'],
      );
      $ordenes[] = $orden;
    }

    return $ordenes;
  }

  public function getOrdenById($id)
  {
    $sql = "SELECT * FROM ordenes WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $categoria_id, $alimento_id, $cantidad, $fecha_creacion, $hora_creacion, $estado_id);
    $stmt->fetch();

    return new Orden($id, $categoria_id, $alimento_id, $cantidad, $fecha_creacion, $hora_creacion, $estado_id);
  }

  public function getOrdenByEstadoId($estado_id)
  {
    $sql = "SELECT * FROM ordenes WHERE estado_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $estado_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $ordenes = [];
    while ($row = $result->fetch_assoc()) {
      $ordenes[] = new Orden($row['id'], $row['categoria_id'], $row['alimento_id'], $row['cantidad'], $row['fecha_creacion'], $row['hora_creacion'], $row['estado_id']);
    }

    return $ordenes;
  }

  public function obtenerCampoPorCategoriaId($categoria_id)
  {
    $sql = "SELECT c.nombre 
            FROM categorias cat
            JOIN potreros p ON cat.potrero_id = p.id
            JOIN campos c ON p.campo_id = c.id
            WHERE cat.id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $stmt->bind_result($nombreCampo);

    if ($stmt->fetch()) {
      return $nombreCampo;
    }

    return null;
  }

  public function registrarOrden(Orden $o)
  {
    $sql = "INSERT INTO ordenes (categoria_id, alimento_id, cantidad, fecha_creacion, hora_creacion, estado_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $c_i = $o->getCategoriaId();
    $a_i = $o->getAlimentoId();
    $c = $o->getCantidad();
    $fc = $o->getFecha_creacion();
    $hc = $o->getHora_creacion();
    $e_i = $o->getEstadoId();
    $stmt->bind_param("iisssi", $c_i, $a_i, $c, $fc, $hc, $e_i);

    if (!$stmt->execute()) {
      die("Error en execute (inserción): " . $stmt->error);
    }

    $stmt->close();

    return true;
  }

  public function modificarOrden(Orden $o)
  {
    $sql = "UPDATE ordenes SET categoria_id = ?, alimento_id = ?, cantidad = ?, fecha_creacion = ?, hora_creacion = ?, estado_id = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $c_i = $o->getCategoriaId();
    $a_i = $o->getAlimentoId();
    $c = $o->getCantidad();
    $fc = $o->getFecha_creacion();
    $hc = $o->getHora_creacion();
    $e_i = $o->getEstadoId();
    $id = $o->getId();
    $stmt->bind_param("iiissii", $c_i, $a_i, $c, $fc, $hc, $e_i, $id);

    return $stmt->execute();
  }

  public function eliminarOrden($id)
  {
    $sql = "DELETE FROM ordenes WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);

    return $stmt->execute();
  }

  public function actualizarEstado($id, $estado_id)
  {
    $sql = "UPDATE ordenes SET estado_id = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $estado_id, $id);

    return $stmt->execute();
  }

  public function getOrdenesFiltradas($estado_id, $categoria_id, $alimento_id)
  {
    $sql = "SELECT o.*, c.nombre AS categoria_nombre, a.nombre AS alimento_nombre, e.nombre AS estado_nombre
            FROM ordenes o
            INNER JOIN categorias c ON o.categoria_id = c.id
            INNER JOIN alimentos a ON o.alimento_id = a.id
            INNER JOIN estados e ON o.estado_id = e.id
            WHERE 1=1";

    $params = [];
    $tipos = '';

    if (!empty($estado_id)) {
      $placeholders = implode(',', array_fill(0, count($estado_id), '?'));
      $sql .= " AND estado_id IN ($placeholders)";
      $params = array_merge($params, $estado_id);
      $tipos .= str_repeat('i', count($estado_id));
    }

    if (!empty($categoria_id)) {
      $placeholders = implode(',', array_fill(0, count($categoria_id), '?'));
      $sql .= " AND categoria_id IN ($placeholders)";
      $params = array_merge($params, $categoria_id);
      $tipos .= str_repeat('i', count($categoria_id));
    }

    if (!empty($alimento_id)) {
      $placeholders = implode(',', array_fill(0, count($alimento_id), '?'));
      $sql .= " AND alimento_id IN ($placeholders)";
      $params = array_merge($params, $alimento_id);
      $tipos .= str_repeat('i', count($alimento_id));
    }
    $sql .= " ORDER BY o.id";

    $stmt = $this->conn->prepare($sql);
    if ($stmt === false) {
      die("Error en prepare: " . $this->conn->error);
    }

    if (!empty($params)) {
      $bind_names[] = $tipos;
      foreach ($params as $key => $value) {
        $bind_names[] = &$params[$key]; // pasar por referencia
      }
      call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    $ordenes = [];
    while ($row = $resultado->fetch_assoc()) {
      // Crea tu objeto Orden correctamente según tu constructor
      $orden = new Orden(
        $row['id'],
        $row['categoria_id'],
        $row['alimento_id'],
        $row['cantidad'],
        $row['fecha_creacion'],
        $row['hora_creacion'],
        $row['estado_id'],
        $row['categoria_nombre'],
        $row['alimento_nombre'],
        $row['estado_nombre']
      );
      $ordenes[] = $orden;
    }

    $stmt->close();

    return $ordenes;
  }

  public function obtenerCampoPorCategoriaNombre($nombreCategoria)
  {
    $sql = "
        SELECT ca.nombre AS nombre_campo
        FROM categorias c
        JOIN potreros p ON c.potrero_id = p.id
        JOIN campos ca ON p.campo_id = ca.id
        WHERE c.nombre = ?
        LIMIT 1
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombreCategoria);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      return $row['nombre_campo'];
    }
    return null;
  }

  public function obtenerAlmacenPorCategoriaNombre($nombreCategoria)
  {
    $sql = "
        SELECT a.nombre AS nombre_almacen
        FROM categorias c
        JOIN potreros p ON c.potrero_id = p.id
        JOIN campos ca ON p.campo_id = ca.id
        JOIN almacenes a ON a.campo_id = ca.id
        WHERE c.nombre = ?
        LIMIT 1
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombreCategoria);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      return $row['nombre_almacen'];
    }
    return null;
  }

}