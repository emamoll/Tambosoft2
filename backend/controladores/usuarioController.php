<?php

require_once __DIR__ . '../../DAOS/usuarioDAO.php';

class UsuarioController
{
  private $usuarioDAO;

  public function __construct()
  {
    $this->usuarioDAO = new UsuarioDAO();
  }

  public function registrarUsuario($username, $email, $password, $rol_id, $token)
  {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $existeUsername = $this->usuarioDAO->getUsuarioByUsername($username);
    $existeEmail = $this->usuarioDAO->getUsuarioByEmail($email);

    if ($existeUsername) {
      return ['success' => false, 'message' => 'Usuario ya existe'];
    }

    if ($existeEmail) {
      return ['success' => false, 'message' => 'Email ya existe'];
    }

    if (!$existeUsername && !$existeEmail) {
      $this->usuarioDAO->verificarRoles();
      $usuario = new Usuario(null, $username, $email, $hash, $rol_id, $token);
      $resultado = $this->usuarioDAO->registrarUsuario($usuario);
      if ($resultado) {
        return ['success' => true];
      } else {
        return ['success' => false, 'message' => 'Error al registrar el usuario'];
      }
    }

    return false;
  }

  public function loginUsuario($username, $password)
  {
    $usuario = $this->usuarioDAO->getUsuarioByUsername($username);

    if ($usuario && password_verify($password, $usuario->getPassword())) {
      // Generar token
      $token = bin2hex(random_bytes(32));
      $usuario->setToken($token);

      // Guardar token en base de datos
      $this->usuarioDAO->actualizarToken($usuario->getId(), $token);

      // Guardar token en la sesión
      $_SESSION['token'] = $token;

      return $usuario;
    }

    return null;
  }

  public function getUsuarioByUsername($username)
  {
    return $this->usuarioDAO->getUsuarioByUsername($username);
  }

  public function getUsuarioByEmail($email)
  {
    return $this->usuarioDAO->getUsuarioByEmail($email);
  }

  public function getUsuarioByToken($token)
  {
    return $this->usuarioDAO->getUsuarioByToken($token);
  }
}
