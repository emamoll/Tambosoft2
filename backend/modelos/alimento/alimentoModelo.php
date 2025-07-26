<?php

class Alimento
{
  private $id;
  private $nombre;
  private $precio;
  private $descripcion;
  private $peso;
  private $fecha_vencimiento;

  public function __construct($id = null, $nombre = null, $precio = null, $descripcion = null, $peso = null, $fecha_vencimiento = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->precio = $precio;
    $this->descripcion = $descripcion;
    $this->peso = $peso;
    $this->fecha_vencimiento = $fecha_vencimiento;
  }

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