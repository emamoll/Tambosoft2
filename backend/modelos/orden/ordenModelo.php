<?php

class Orden
{
  private $id;
  private $categoria_id;
  private $alimento_id;
  private $cantidad;
  private $fecha_creacion;
  private $hora_creacion;
  private $estado_id;
  private $categoria_nombre;
  private $alimento_nombre;
  private $alimento_precio;
  private $estado_nombre;

  public function __construct($id = null, $categoria_id = null, $alimento_id = null, $cantidad = null, $fecha_creacion = null, $hora_creacion = null, $estado_id = null, $categoria_nombre = null, $alimento_nombre = null, $alimento_precio = null, $estado_nombre = null)
  {
    $this->id = $id;
    $this->categoria_id = $categoria_id;
    $this->alimento_id = $alimento_id;
    $this->cantidad = $cantidad;
    $this->fecha_creacion = $fecha_creacion;
    $this->hora_creacion = $hora_creacion;
    $this->estado_id = $estado_id;
    $this->categoria_nombre = $categoria_nombre;
    $this->alimento_nombre = $alimento_nombre;
    $this->alimento_precio = $alimento_precio;
    $this->estado_nombre = $estado_nombre;

  }

  public function getId()
  {
    return $this->id;
  }

  public function getCategoriaId()
  {
    return $this->categoria_id;
  }

  public function getAlimentoId()
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
    return $this->hora_creacion;
  }

  public function getEstadoId()
  {
    return $this->estado_id;
  }

  public function getCategoriaNombre()
  {
    return $this->categoria_nombre;
  }

  public function getAlimentoNombre()
  {
    return $this->alimento_nombre;
  }

  public function getAlimentoPrecio()
  {
    return $this->alimento_precio;
  }

  public function getEstadoNombre()
  {
    return $this->estado_nombre;
  }

  public function setCategoriaId($categoria_id)
  {
    $this->categoria_id = $categoria_id;
  }

  public function setAlimentoId($alimento_id)
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

  public function setEstadoId($estado_id)
  {
    $this->estado = $estado_id;
  }

  public function setCategoriaNombre($categoria_nombre)
  {
    $this->categoria_nombre = $categoria_nombre;
  }

  public function setAlimentoNombre($alimento_nombre)
  {
    $this->$alimento_nombre = $alimento_nombre;
  }

  public function setAlimentoPrecio($alimento_precio)
  {
    $this->$alimento_precio = $alimento_precio;
  }

  public function setEstadoNombre($estado_nombre)
  {
    $this->estado_nombre = $estado_nombre;
  }
}