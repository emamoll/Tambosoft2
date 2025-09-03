<?php

/**
 * Clase de modelo para la entidad 'Orden_cancelada'.
 * Representa la estructura de los datos de una orden de compra cancelada.
 */
class Orden_cancelada
{
  // Propiedades privadas que corresponden a las columnas de la tabla `ordenes_canceladas`.
  private $id;
  private $orden_id;
  private $descripcion;
  private $fecha;
  private $hora;

  /**
   * Constructor de la clase.
   *
   * @param int|null $id El ID del registro de cancelación.
   * @param int|null $orden_id El ID de la orden de compra original.
   * @param string|null $descripcion La razón o descripción de la cancelación.
   * @param string|null $fecha La fecha en que se realizó la cancelación.
   * @param string|null $hora La hora en que se realizó la cancelación.
   */
  public function __construct($id = null, $orden_id = null, $descripcion = null, $fecha = null, $hora = null)
  {
    $this->id = $id;
    $this->orden_id = $orden_id;
    $this->descripcion = $descripcion;
    $this->fecha = $fecha;
    $this->hora = $hora;
  }

  // Métodos "getter" para acceder a las propiedades.

  public function getId()
  {
    return $this->id;
  }

  public function getOrden_id()
  {
    return $this->orden_id;
  }

  public function getDescripcion()
  {
    return $this->descripcion;
  }

  public function getFecha()
  {
    return $this->fecha;
  }

  public function getHora()
  {
    return $this->hora;
  }

  // Métodos "setter" para modificar las propiedades.

  public function setOrden_id($orden_id)
  {
    $this->orden_id = $orden_id;
  }

  public function setDescripcion($descripcion)
  {
    $this->descripcion = $descripcion;
  }

  public function setFecha($fecha)
  {
    $this->fecha = $fecha;
  }

  public function setHora($hora)
  {
    $this->hora = $hora;
  }
}