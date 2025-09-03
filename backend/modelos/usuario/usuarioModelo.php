<?php

/**
 * Clase de modelo para la entidad 'Usuario'.
 * Representa la estructura de los datos de un usuario en el sistema.
 */
class Usuario
{
  // Propiedades privadas que corresponden a las columnas de la tabla `usuarios`.
  private $id;
  private $username;
  private $email;
  private $password;
  private $rol_id;
  private $token;

  /**
   * Constructor de la clase.
   *
   * @param int|null $id El ID del usuario.
   * @param string|null $username El nombre de usuario.
   * @param string|null $email La dirección de correo electrónico.
   * @param string|null $password La contraseña cifrada del usuario.
   * @param int|null $rol_id El ID del rol asociado.
   * @param string|null $token El token de sesión del usuario.
   */
  public function __construct($id = null, $username = null, $email = null, $password = null, $rol_id = null, $token = null)
  {
    $this->id = $id;
    $this->username = $username;
    $this->email = $email;
    $this->password = $password;
    $this->rol_id = $rol_id;
    $this->token = $token;
  }

  // Métodos "getter" para acceder a las propiedades.

  public function getId()
  {
    return $this->id;
  }

  public function getUsername()
  {
    return $this->username;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function getPassword()
  {
    return $this->password;
  }

  public function getRol_id()
  {
    return $this->rol_id;
  }

  public function getToken()
  {
    return $this->token;
  }

  // Métodos "setter" para modificar las propiedades.

  public function setUsername($username)
  {
    $this->username = $username;
  }

  public function setEmail($email)
  {
    $this->email = $email;
  }

  public function setPassword($password)
  {
    $this->password = $password;
  }

  public function setRol_id($rol_id)
  {
    $this->rol_id = $rol_id;
  }

  public function setToken($token)
  {
    $this->token = $token;
  }
}
