<?php

class Potrero
{
  private $id;
  private $nombre;
  private $superficie;
  private $pastura_id;
  private $categoria_id;
  private $campo_id;

  public function __construct($id = null, $nombre = null, $superficie = null, $pastura_id = null, $categoria_id = null, $campo_id = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->superficie = $superficie;
    $this->pastura_id = $pastura_id;
    $this->categoria_id = $categoria_id;
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

  public function getSuperficie()
  {
    return $this->superficie;
  }

  public function getPastura_id()
  {
    return $this->pastura_id;
  }

  public function getCategoria_id()
  {
    return $this->categoria_id;
  }

  public function getCampo_id()
  {
    return $this->campo_id;
  }

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setSuperficie($superficie)
  {
    $this->superficie = $superficie;
  }

  public function setPastura_id($pastura_id)
  {
    $this->pastura = $pastura_id;
  }

  public function setCategoria_id($categoria_id)
  {
    $this->categoria_id = $categoria_id;
  }

  public function setCampo_id($campo_id)
  {
    $this->campo_id = $campo_id;
  }
}
