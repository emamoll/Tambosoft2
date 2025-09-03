<?php

/**
 * Clase de modelo para la entidad 'Alimento'.
 * Representa la estructura de los datos de un alimento.
 */
class Alimento
{
  // Propiedades privadas que corresponden a las columnas de la tabla `alimentos`.
  private $id;
  private $nombre;
  private $precio;
  private $descripcion;
  private $peso;
  private $fecha_vencimiento;

  /**
   * Constructor de la clase.
   *
   * @param int|null $id El ID del alimento.
   * @param string|null $nombre El nombre del alimento.
   * @param float|null $precio El precio del alimento.
   * @param string|null $descripcion La descripción del alimento.
   * @param float|null $peso El peso del alimento.
   * @param string|null $fecha_vencimiento La fecha de vencimiento.
   */
  public function __construct($id = null, $nombre = null, $precio = null, $descripcion = null, $peso = null, $fecha_vencimiento = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->precio = $precio;
    $this->descripcion = $descripcion;
    $this->peso = $peso;
    $this->fecha_vencimiento = $fecha_vencimiento;
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

  public function getPrecio()
  {
    return $this->precio;
  }

  public function getDescripcion()
  {
    return $this->descripcion;
  }

  public function getPeso()
  {
    return $this->peso;
  }

  public function getFecha_vencimiento()
  {
    return $this->fecha_vencimiento;
  }

  // Métodos "setter" para modificar las propiedades.

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setPrecio($precio)
  {
    $this->precio = $precio;
  }

  public function setDescripcion($descripcion)
  {
    $this->descripcion = $descripcion;
  }

  public function setPeso($peso)
  {
    $this->peso = $peso;
  }

  public function setFecha_vencimiento($fecha_vencimiento)
  {
    $this->fecha_vencimiento = $fecha_vencimiento;
  }
}