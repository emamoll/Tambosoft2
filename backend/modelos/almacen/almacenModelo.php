<?php

/**
 * Clase de modelo para la entidad 'Almacen'.
 * Representa la estructura de los datos de un almacén.
 */
class Almacen
{
  // Propiedades privadas que corresponden a las columnas de la tabla `almacenes`.
  private $id;
  private $nombre;
  private $campo_id;

  /**
   * Constructor de la clase.
   *
   * @param int|null $id El ID del almacén.
   * @param string|null $nombre El nombre del almacén.
   * @param int|null $campo_id El ID del campo al que pertenece el almacén.
   */
  public function __construct($id = null, $nombre = null, $campo_id = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->campo_id = $campo_id;
  }

  /**
   * Obtiene el ID del almacén.
   *
   * @return int|null El ID del almacén.
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Obtiene el nombre del almacén.
   *
   * @return string|null El nombre del almacén.
   */
  public function getNombre()
  {
    return $this->nombre;
  }

  /**
   * Obtiene el ID del campo asociado.
   *
   * @return int|null El ID del campo.
   */
  public function getCampo_id()
  {
    return $this->campo_id;
  }

  /**
   * Establece el nombre del almacén.
   *
   * @param string $nombre El nuevo nombre.
   */
  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  /**
   * Establece el ID del campo.
   *
   * @param int $campo_id El nuevo ID del campo.
   */
  public function setCampo_id($campo_id)
  {
    $this->campo_id = $campo_id;
  }
}