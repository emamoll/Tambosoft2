<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/proveedor/proveedorTabla.php';
require_once __DIR__ . '../../modelos/proveedor/proveedorModelo.php';

class ProveedorDAO
{
  // Propiedades privadas para la conexión y la creación de tablas.
  private $db;
  private $conn;
  private $crearTabla;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new ProveedorCrearTabla($this->db);
    $this->crearTabla->crearTablaProveedor();
    $this->conn = $this->db->connect();
  }

  public function getAllProveedores()
  {
    $sql = "SELECT * FROM proveedores";
    $result = $this->conn->query($sql);

    // Si la consulta falla, detiene la ejecución y muestra el error.
    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $proveedores = [];

    while ($row = $result->fetch_assoc()) {
      $proveedores[] = new Proveedor($row['id'], $row['nombre'], $row['direccion'], $row['telefono'], $row['email']);
    }

    return $proveedores;
  }

  public function getProveedorById($id)
  {
    $sql = "SELECT * FROM proveedores WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id); 
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows() === 0) {
      return null;
    }

    // Vincula las variables a las columnas del resultado y obtiene la fila.
    $stmt->bind_result($id, $nombre, $direccion, $telefono, $email);
    $stmt->fetch();

    return new Proveedor($id, $nombre, $direccion, $telefono, $email);
  }

  public function getProveedorByNombre($nombre)
  {
    $sql = "SELECT * FROM proveedores WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows() === 0) {
      return null;
    }

    // Vincula las variables y obtiene la fila.
    $stmt->bind_result($id, $nombre, $direccion, $telefono, $email);
    $stmt->fetch();

    return new Proveedor($id, $nombre, $direccion, $telefono, $email);
  }

  public function getProveedorByEmail($email)
  {
    $sql = "SELECT * FROM proveedores WHERE email = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Si no se encuentra ninguna fila, retorna null.
    if ($stmt->num_rows() === 0) {
      return null;
    }

    // Vincula las variables y obtiene la fila.
    $stmt->bind_result($id, $nombre, $direccion, $telefono, $email);
    $stmt->fetch();

    return new Proveedor($id, $nombre, $direccion, $telefono, $email);
  }

  public function registrarProveedor(Proveedor $pro)
  {
    $sql = "INSERT INTO proveedores (nombre, direccion, telefono, email) VALUES (?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
      echo ("Error en prepare: " . $this->conn->error);
      return false;
    }
    $n = $pro->getNombre();
    $d = $pro->getDireccion();
    $t = $pro->getTelefono();
    $e = $pro->getEmail();
    $stmt->bind_param("ssis", $n, $d, $t, $e);

    $resultado = $stmt->execute();
    if (!$resultado) {
      echo ("Error en execute: " . $stmt->error);
    }

    return $resultado;
  }

  public function modificarProveedor(Proveedor $p)
  {
    $sql = "UPDATE proveedores SET direccion = ?, telefono = ?, email = ? WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $n = $p->getNombre();
    $d = $p->getDireccion();
    $t = $p->getTelefono();
    $e = $p->getEmail();
    $stmt->bind_param("siss", $d, $t, $e, $n);

    return $stmt->execute();
  }

  public function eliminarProveedor($nombre)
  {
    $sql = "DELETE FROM proveedores WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    return $stmt->execute();
  }
}