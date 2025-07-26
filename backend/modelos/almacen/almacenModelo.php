<?php

class Almacen
{
  private $id;
  private $nombre;
  private $campo_id;

  public function __construct($id = null, $nombre = null, $campo_id = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->campo_id = $campo_id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getCampo_id()
  {
    return $this->campo_id;
  }

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setCampo_id($campo_id)
  {
    $this->campo_id = $campo_id;
  }

}