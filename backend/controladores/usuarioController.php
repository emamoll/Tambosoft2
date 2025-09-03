<?php

// Incluye el archivo de la capa de acceso a datos para los usuarios.
require_once __DIR__ . '../../DAOS/usuarioDAO.php';

/**
 * Clase controladora para gestionar las operaciones relacionadas con los usuarios,
 * como el registro, el inicio de sesión y la obtención de datos de usuario.
 */
class UsuarioController
{
  // Propiedad privada para la instancia de UsuarioDAO.
  private $usuarioDAO;

  /**
   * Constructor de la clase.
   * Inicializa la propiedad `$usuarioDAO`.
   */
  public function __construct()
  {
    $this->usuarioDAO = new UsuarioDAO();
  }

  /**
   * Registra un nuevo usuario en la base de datos.
   *
   * @param string $username El nombre de usuario.
   * @param string $email El correo electrónico del usuario.
   * @param string $password La contraseña del usuario (sin cifrar).
   * @param int $rol_id El ID del rol del usuario.
   * @param string $token Un token para la sesión.
   * @return array Un array con el resultado del registro (éxito o error).
   */
  public function registrarUsuario($username, $email, $password, $rol_id, $token)
  {
    // Cifra la contraseña utilizando `password_hash`.
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Verifica si el nombre de usuario o el email ya existen.
    $existeUsername = $this->usuarioDAO->getUsuarioByUsername($username);
    $existeEmail = $this->usuarioDAO->getUsuarioByEmail($email);

    // Si el usuario ya existe, devuelve un mensaje de error.
    if ($existeUsername) {
      return ['success' => false, 'message' => 'Usuario ya existe'];
    }

    // Si el email ya existe, devuelve un mensaje de error.
    if ($existeEmail) {
      return ['success' => false, 'message' => 'Email ya existe'];
    }

    // Si no existen, procede con el registro.
    $this->usuarioDAO->verificarRoles();
    $usuario = new Usuario(null, $username, $email, $hash, $rol_id, $token);
    $resultado = $this->usuarioDAO->registrarUsuario($usuario);

    // Devuelve el resultado del registro.
    if ($resultado) {
      return ['success' => true];
    } else {
      return ['success' => false, 'message' => 'Error al registrar el usuario'];
    }
  }

  /**
   * Inicia sesión de un usuario.
   *
   * @param string $username El nombre de usuario.
   * @param string $password La contraseña del usuario.
   * @return Usuario|null El objeto Usuario si las credenciales son válidas, de lo contrario, null.
   */
  public function loginUsuario($username, $password)
  {
    // Obtiene el usuario por nombre.
    $usuario = $this->usuarioDAO->getUsuarioByUsername($username);

    // Verifica si el usuario existe y si la contraseña es correcta.
    if ($usuario && password_verify($password, $usuario->getPassword())) {
      // Genera un token de sesión.
      $token = bin2hex(random_bytes(32));
      $usuario->setToken($token);

      // Guarda el token en la base de datos y en la sesión.
      $this->usuarioDAO->actualizarToken($usuario->getId(), $token);
      $_SESSION['token'] = $token;

      return $usuario;
    }
    return null;
  }

  /**
   * Obtiene un usuario por su nombre de usuario.
   *
   * @param string $username El nombre de usuario.
   * @return Usuario|null El objeto Usuario o null.
   */
  public function getUsuarioByUsername($username)
  {
    return $this->usuarioDAO->getUsuarioByUsername($username);
  }

  /**
   * Obtiene un usuario por su dirección de correo electrónico.
   *
   * @param string $email El correo electrónico.
   * @return Usuario|null El objeto Usuario o null.
   */
  public function getUsuarioByEmail($email)
  {
    return $this->usuarioDAO->getUsuarioByEmail($email);
  }

  /**
   * Obtiene un usuario por su token de sesión.
   *
   * @param string $token El token de sesión.
   * @return Usuario|null El objeto Usuario o null.
   */
  public function getUsuarioByToken($token)
  {
    return $this->usuarioDAO->getUsuarioByToken($token);
  }
}
