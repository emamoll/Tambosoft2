<?php

/**
 * Clase de modelo para la entidad 'Stock_Almacen'.
 * Representa la estructura de los datos del stock de un alimento en un almacén.
 */
class Stock_Almacen
{
  // Propiedades privadas que corresponden a las columnas de la tabla `stock_almacenes` y otras propiedades adicionales.
  private $id;
  private $almacen_id;
  private $alimento_id;
  private $stock;
  private $alimento_nombre;
  private $alimento_precio;

  /**
   * Constructor de la clase.
   *
   * @param int|null $id El ID del registro de stock.
   * @param int|null $almacen_id El ID del almacén.
   * @param int|null $alimento_id El ID del alimento.
   * @param int|null $stock La cantidad de stock.
   * @param float|null $alimento_precio El precio del alimento (opcional).
   */
  public function __construct($id = null, $almacen_id = null, $alimento_id = null, $stock = null, $alimento_precio = null)
  {
    $this->id = $id;
    $this->almacen_id = $almacen_id;
    $this->alimento_id = $alimento_id;
    $this->stock = $stock;
    $this->alimento_precio = $alimento_precio;
  }

  // Métodos "getter" para acceder a las propiedades.

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

  // Métodos "setter" para modificar las propiedades.

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

  /**
   * Calcula el valor económico total del stock de este alimento.
   *
   * @return float El valor total (stock * precio).
   */
  public function getValorEconomico()
  {
    return $this->stock * $this->alimento_precio;
  }
}