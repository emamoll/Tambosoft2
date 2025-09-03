<?php

/**
 * Clase de modelo para la entidad 'Pastura'.
 * Representa la estructura de los datos de una pastura.
 */
class Pastura
{
  // Propiedades privadas que corresponden a las columnas de la tabla `pasturas`.
  private $id;
  private $nombre;

  /**
   * Constructor de la clase.
   *
   * @param int|null $id El ID de la pastura.
   * @param string|null $nombre El nombre de la pastura.
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