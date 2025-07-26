<?php

class Campo
{
  private $id;
  private $nombre;
  private $ubicacion;

  public function __construct($id = null, $nombre = null, $ubicacion = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->ubicacion = $ubicacion;
  }

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

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setUbicacion($ubicacion)
  {
    $this->ubicacion = $ubicacion;
  }
}
