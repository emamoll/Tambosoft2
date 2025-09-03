<?php

/**
 * Clase de modelo para la entidad 'Estado'.
 * Representa la estructura de los datos de un estado de orden.
 */
class Estado
{
  // Propiedades privadas que corresponden a las columnas de la tabla `estados`.
  private $id;
  private $nombre;

  /**
   * Constructor de la clase.
   *
   * @param int|null $id El ID del estado.
   * @param string|null $nombre El nombre del estado.
   */
  public function __construct($id = null, $nombre = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
  }

  // Métodos "getter" para acceder a las propiedades.

  public function getId()
  {
    return $this->id;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  // Método "setter" para modificar la propiedad `nombre`.

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }
}