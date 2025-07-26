<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class OrdenCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

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

  public function insertarValoresTablaEstados()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "INSERT IGNORE INTO estados (nombre) 
            VALUES ('creada'), ('enviada'), ('en preparacion'), ('en traslado'), ('entregada'), ('cancelada')";
    $conn->query($sql);
    $conn->close();
  }

  public function crearTablaOrden()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS ordenes(
            id INT PRIMARY KEY AUTO_INCREMENT,
            almacen_id INT NOT NULL,
            alimento_id INT NOT NULL,
            cantidad INT NOT NULL,
            fecha_creacion DATE NOT NULL,
            hora_creacion TIME NOT NULL,
            fecha_actualizacion DATE NOT NULL,
            hora_actualizacion TIME NOT NULL,
            estado_id INT NOT NULL,
            FOREIGN KEY (almacen_id) REFERENCES almacenes(id),
            FOREIGN KEY (alimento_id) REFERENCES alimentos(id),
            FOREIGN KEY (estado_id) REFERENCES estados(id)            
            )";
    $conn->query($sql);
    $conn->close();
  }
}