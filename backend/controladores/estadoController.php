<?php

require_once __DIR__ . '../../DAOS/estadoDAO.php';

class EstadoController
{
  private $estadoDAO;

  public function __construct()
  {
    $this->estadoDAO = new EstadoDAO();
  }

  public function obtenerEstados()
  {
    return $this->estadoDAO->getAllEstados();
  }
}