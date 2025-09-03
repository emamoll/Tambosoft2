<?php

/**
 * Clase de modelo para la entidad 'Campo'.
 * Representa la estructura de los datos de un campo.
 */
class Campo
{
  // Propiedades privadas que corresponden a las columnas de la tabla `campos`.
  private $id;
  private $nombre;
  private $ubicacion;

  /**
   * Constructor de la clase.
   *
   * @param int|null $id El ID del campo.
   * @param string|null $nombre El nombre del campo.
   * @param string|null $ubicacion La ubicación del campo.
   */
  public function __construct($id = null, $nombre = null, $ubicacion = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->ubicacion = $ubicacion;
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

  public function getUbicacion()
  {
    return $this->ubicacion;
  }

  // Métodos "setter" para modificar las propiedades.

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setUbicacion($ubicacion)
  {
    $this->ubicacion = $ubicacion;
  }
}