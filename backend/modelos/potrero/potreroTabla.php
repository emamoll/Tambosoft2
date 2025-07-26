<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class PotreroCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function crearTablaPotrero()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS potreros (
              id INT PRIMARY KEY AUTO_INCREMENT,
              nombre VARCHAR(255) NOT NULL UNIQUE,
              superficie VARCHAR(255) NOT NULL,
              pastura_id INT NOT NULL,
              categoria_id INT NOT NULL,
              campo_id INT NOT NULL,
              FOREIGN KEY (pastura_id) REFERENCES pasturas(id),
              FOREIGN KEY (categoria_id) REFERENCES categorias(id),
              FOREIGN KEY (campo_id) REFERENCES campos(id))";
    $conn->query($sql);
    $conn->close();
  }
}