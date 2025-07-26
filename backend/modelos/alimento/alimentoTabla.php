<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class AlimentoCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function crearTablaAlimentos()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS alimentos(
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL UNIQUE,
            precio DECIMAL (10,2) NOT NULL,
            descripcion VARCHAR(255) NOT NULL,
            peso DECIMAL (10,2) NOT NULL,
            fecha_vencimiento DATE NOT NULL)";
    $conn->query($sql);
    $conn->close();
  }
}