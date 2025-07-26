<?php

class Stock_Almacen
{
  private $id;
  private $almacen_id;
  private $alimento_id;
  private $stock;
  private $alimento_nombre;
  private $alimento_precio;

  public function __construct($id = null, $almacen_id = null, $alimento_id = null, $stock = null, $alimento_precio = null)
  {
    $this->id = $id;
    $this->almacen_id = $almacen_id;
    $this->alimento_id = $alimento_id;
    $this->stock = $stock;
    $this->alimento_precio = $alimento_precio;
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

  public function getStock()
  {
    return $this->stock;
  }

  public function getAlimentoNombre()
  {
    return $this->alimento_nombre;
  }

  public function getAlimentoPrecio()
  {
    return $this->alimento_precio;
  }

  public function setAlmacen_id($almacen_id)
  {
    $this->almacen_id = $almacen_id;
  }

  public function setAlimento_id($alimento_id)
  {
    $this->alimento_id = $alimento_id;
  }

  public function setStock($stock)
  {
    $this->stock = $stock;
  }

  public function setAlimentoNombre($alimento_nombre)
  {
    $this->alimento_nombre = $alimento_nombre;
  }

  public function setAlimentoPrecio($alimento_precio)
  {
    $this->alimento_precio = $alimento_precio;
  }


  public function getValorEconomico()
  {
    return $this->stock * $this->alimento_precio;
  }
}