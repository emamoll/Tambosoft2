<?php


class Proveedor
{
  private $id;
  private $nombre;
  private $direccion;
  private $telefono;
  private $email;


  public function __construct($id = null, $nombre = null, $direccion = null, $telefono = null, $email = null)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->direccion = $direccion;
    $this->telefono = $telefono;
    $this->email = $email;
  }

  // Métodos "getter" para acceder a las propiedades.

  public function getId()
  {
    return $this->id;
  }

  public function getNombre()
  {
    return $this->nombre;
  }

  public function getDireccion()
  {
    return $this->direccion;
  }

  public function getTelefono()
  {
    return $this->telefono;
  }

  public function getEmail()
  {
    return $this->email;
  }

  // Métodos "setter" para modificar las propiedades.

  public function setNombre($nombre)
  {
    $this->nombre = $nombre;
  }

  public function setDireccion($direccion)
  {
    $this->direccion = $direccion;
  }

  public function setTelefono($telefono)
  {
    $this->telefono = $telefono;
  }

  public function setEmail($email)
  {
    $this->email = $email;
  }
}