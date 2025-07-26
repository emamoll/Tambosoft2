<?php

class Orden
{
  private $id;
  private $almacen_id;
  private $alimento_id;
  private $cantidad;
  private $fecha_creacion;
  private $hora_creacion;
  private $fecha_actualizacion;
  private $hora_actualizacion;
  private $estado_id;

  public function __construct($id = null, $almacen_id = null, $alimento_id = null, $cantidad = null, $fecha_creacion = null, $hora_creacion = null, $fecha_actualizacion = null, $hora_actualizacion = null, $estado_id = null, )
  {
    $this->id = $id;
    $this->almacen_id = $almacen_id;
    $this->alimento_id = $alimento_id;
    $this->cantidad = $cantidad;
    $this->hora_creacion = $hora_creacion;
    $this->fecha_creacion = $fecha_creacion;
    $this->fecha_actualizacion = $fecha_actualizacion;
    $this->hora_actualizacion = $hora_actualizacion;
    $this->estado_id = $estado_id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getAlmacen_id()
  {
    return $this->almacen_id;
  }

  public function getAlimento_id()
  {
    return $this->alimento_id;
  }

  public function getCantidad()
  {
    return $this->cantidad;
  }

  public function getFecha_creacion()
  {
    return $this->fecha_creacion;
  }

  public function getHora_creacion()
  {
    return $this->fecha_creacion;
  }

  public function getFecha_actualizacion()
  {
    return $this->fecha_actualizacion;
  }

  public function getHora_actualizacion()
  {
    return $this->hora_actualizacion;
  }

  public function getEstado_id()
  {
    return $this->estado_id;
  }

  public function setAlmacen_id($almacen_id)
  {
    $this->almacen_id = $almacen_id;
  }

  public function setAlimento_id($alimento_id)
  {
    $this->alimento_id = $alimento_id;
  }

  public function setCantidad($cantidad)
  {
    $this->cantidad = $cantidad;
  }

  public function setFecha_creacion($fecha_creacion)
  {
    $this->fecha_creacion = $fecha_creacion;
  }

  public function setHora_creacion($hora_creacion)
  {
    $this->hora_creacion = $hora_creacion;
  }

  public function setFecha_actualizacion($fecha_actualizacion)
  {
    $this->fecha_actualizacion = $fecha_actualizacion;
  }

  public function setHora_actualizacion($hora_actualizacion)
  {
    $this->hora_actualizacion = $hora_actualizacion;
  }

  public function setEstado_id($estado_id)
  {
    $this->estado_id = $estado_id;
  }
}