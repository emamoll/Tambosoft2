<?php

// Incluye el archivo de la capa de acceso a datos para los estados.
require_once __DIR__ . '../../DAOS/estadoDAO.php';

/**
 * Clase controladora para gestionar las operaciones relacionadas con los estados.
 */
class EstadoController
{
  // Propiedad privada para la instancia de EstadoDAO.
  private $estadoDAO;

  /**
   * Constructor de la clase.
   * Inicializa la propiedad `$estadoDAO`.
   */
  public function __construct()
  {
    $this->estadoDAO = new EstadoDAO();
  }

  /**
   * Obtiene todos los estados de la base de datos.
   *
   * @return array Un array de objetos Estado.
   */
  public function obtenerEstados()
  {
    return $this->estadoDAO->getAllEstados();
  }

    public function getEstadoById($id)
  {
    return $this->estadoDAO->getEstadoById($id);
  }
}

