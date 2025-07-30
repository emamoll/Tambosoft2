<?php

class Orden_cancelada
{
  private $id;
  private $orden_id;
  private $descripcion;
  private $fecha;
  private $hora;

  public function __construct($id = null, $orden_id = null, $descripcion = null, $fecha = null, $hora = null)
  {
    $this->id = $id;
    $this->orden_id = $orden_id;
    $this->descripcion = $descripcion;
    $this->fecha = $fecha;
    $this->hora = $hora;
  }

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