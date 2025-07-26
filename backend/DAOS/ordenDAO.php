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
    $sql = "SELECT * FROM ordenes ORDER BY id";

    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $ordenes = [];

    while ($row = $result->fetch_assoc()) {
      // Suponiendo que tu modelo Orden acepta esos parámetros o creas un constructor más flexible
      $orden = new Orden(
        $row['id'],
        $row['almacen_id'],
        $row['alimento_id'],
        $row['cantidad'],
        $row['fecha_creacion'],
        $row['hora_creacion'],
        $row['fecha_actualizacion'],
        $row['hora_actualizacion'],
        $row['estado_id']
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

    $stmt->bind_result(
      $id,
      $almacen_id,
      $alimento_id,
      $cantidad,
      $fecha_creacion,
      $hora_creacion,
      $fecha_actualizacion,
      $hora_actualizacion,
      $estado_id
    );
    $stmt->fetch();

    return new Orden(
      $id,
      $almacen_id,
      $alimento_id,
      $cantidad,
      $fecha_creacion,
      $hora_creacion,
      $fecha_actualizacion,
      $hora_actualizacion,
      $estado_id
    );
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
      $ordenes[] = new Orden(
        $row['id'],
        $row['almacen_id'],
        $row['alimento_id'],
        $row['cantidad'],
        $row['fecha_creacion'],
        $row['hora_creacion'],
        $row['fecha_actualizacion'],
        $row['hora_actualizacion'],
        $row['estado_id']
      );
    }

    return $ordenes;
  }

  public function registrarOrden(Orden $o)
  {
    $sql = "INSERT INTO ordenes (almacen_id, alimento_id, cantidad, fecha_creacion, hora_creacion, fecha_actualizacion, hora_actualizacion, estado_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $alm_i = $o->getAlmacen_id();
    $ali_i = $o->getAlimento_id();
    $c = $o->getCantidad();
    $fc = $o->getFecha_creacion();
    $hc = $o->getHora_creacion();
    $fa = $o->getFecha_actualizacion();
    $ha = $o->getHora_actualizacion();
    $e_i = $o->getEstado_id();
    $stmt->bind_param("iiissssi", $alm_i, $ali_i, $c, $fc, $hc, $fa, $ha, $e_i);

    if (!$stmt->execute()) {
      die("Error en execute (inserción): " . $stmt->error);
    }

    $stmt->close();

    return true;
  }

  public function modificarOrden(Orden $o)
  {
    $sql = "UPDATE ordenes SET almacen_id = ?, alimento_id = ?, cantidad = ?, fecha_creacion = ?, hora_creacion = ?,fecha_actualizacion = ?, hora_actualizacion = ?, estado_id = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $alm_i = $o->getAlmacen_id();
    $ali_i = $o->getAlimento_id();
    $c = $o->getCantidad();
    $fc = $o->getFecha_creacion();
    $hc = $o->getHora_creacion();
    $fa = $o->getFecha_actualizacion();
    $ha = $o->getHora_actualizacion();
    $e_i = $o->getEstado_id();
    $id = $o->getId();
    $stmt->bind_param("iiissssii", $alm_i, $ali_i, $c, $fc, $hc, $fa, $ha, $e_i, $id);

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

  public function getOrdenesFiltradas($almacen_id, $alimento_id, $estado_id)
  {
    $sql = "SELECT * FROM ordenes WHERE 1=1";

    $params = [];
    $tipos = '';

    if (!empty($almacen_id)) {
      $placeholders = implode(',', array_fill(0, count($almacen_id), '?'));
      $sql .= " AND almacen_id IN ($placeholders)";
      $params = array_merge($params, $almacen_id);
      $tipos .= str_repeat('i', count($almacen_id));
    }

    if (!empty($alimento_id)) {
      $placeholders = implode(',', array_fill(0, count($alimento_id), '?'));
      $sql .= " AND alimento_id IN ($placeholders)";
      $params = array_merge($params, $alimento_id);
      $tipos .= str_repeat('i', count($alimento_id));
    }

    if (!empty($estado_id)) {
      $placeholders = implode(',', array_fill(0, count($estado_id), '?'));
      $sql .= " AND estado_id IN ($placeholders)";
      $params = array_merge($params, $estado_id);
      $tipos .= str_repeat('i', count($estado_id));
    }

    $sql .= " ORDER BY id";

    $stmt = $this->conn->prepare($sql);
    if ($stmt === false) {
      die("Error en prepare: " . $this->conn->error);
    }

    if (!empty($params)) {
      $bind_names[] = $tipos;
      foreach ($params as $key => $value) {
        $bind_names[] = &$params[$key];
      }
      call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    $ordenes = [];
    while ($row = $resultado->fetch_assoc()) {
      $orden = new Orden(
        $row['id'],
        $row['almacen_id'],
        $row['alimento_id'],
        $row['cantidad'],
        $row['fecha_creacion'],
        $row['hora_creacion'],
        $row['fecha_actualizacion'],
        $row['hora_actualizacion'],
        $row['estado_id']
      );
      $ordenes[] = $orden;
    }

    $stmt->close();

    return $ordenes;
  }
}