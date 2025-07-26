<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class CategoriaCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function crearTablaCategoria()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS categorias (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombre VARCHAR(255) NOT NULL)";
    $conn->query($sql);
    $conn->close();
  }
}