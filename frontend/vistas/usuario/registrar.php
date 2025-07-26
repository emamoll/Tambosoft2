<?php

require_once __DIR__ . '../../../../backend/controladores/usuarioController.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['username'])) {
  // Redirigir al login si no está logueado
  header("Location: ../../../index.php");
  exit();
}

// Verificar si es administrador
if ($_SESSION['rol_id'] != 1) {
  // Redirigir al inicio u otra página si no es admin
  header("Location: ../../../index.php");
  exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confPassword = $_POST['confPassword'];
  $rol_id = isset($_POST['rol_id']) ? (int) $_POST['rol_id'] : 0; // 1 para admin, 2 para usuario

  $controller = new UsuarioController();

  if (!empty($username) && !empty($email) && !empty($password) && !empty($confPassword) && !empty($rol_id)) {
    if (
      strlen($password) < 8 ||
      !preg_match('/[A-Z]/', $password) ||
      !preg_match('/[0-9]/', $password) ||
      !preg_match('/[^a-zA-Z0-9]/', $password)
    ) {
      $mensaje = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.";
    } elseif ($password !== $confPassword) {
      $mensaje = "Las contraseñas no coinciden.";
    } elseif ($rol_id === 0) {
      $mensaje = "Debe seleccionar un rol válido.";
    } else {
      $token = bin2hex(random_bytes(32));
      $respuesta = $controller->registrarUsuario($username, $email, $password, $rol_id, $token);
      if (is_array($respuesta) && isset($respuesta['success']) && $respuesta['success'] === true) {
        $mensajeExito = true;
        $_POST = [];
      } elseif (is_array($respuesta) && isset($respuesta['message'])) {
        $mensaje = $respuesta['message'];
      } else {
        $mensaje = "Error inesperado al registrar el usuario.";
      }
    }
  } else {
    $mensaje = "Todos los campos son obligatorios.";
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Tambosoft: Registrar usuario</title>
  <link rel="stylesheet" href="../../css/estilos.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bodyHome">
  <?php require_once __DIR__ . '../../secciones/header.php'; ?>
  <!--	--------------->
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>

  <div class="main">
    <div class="form-container">
      <div class="form-title">Registrar usuario</div>
      <form method="POST">
        <div class="form-group">
          <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            placeholder=" ">
          <label for="username">Usuario</label>
        </div>
        <div class="form-group">
          <input type="text" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            placeholder=" ">
          <label for="email">Email</label>
        </div>
        <div class="form-group">
          <input type="password" id="password" name="password" placeholder=" ">
          <label for="password">Contraseña</label>
        </div>
        <div class="form-group">
          <input type="password" id="confPassword" name="confPassword" placeholder=" ">
          <label for="confPassword">Confirmar contraseña</label>
        </div>
        <div class="form-group select-group">
          <select name="rol_id" value="<?= htmlspecialchars($_POST['rol_id'] ?? '') ?>">
            <option value="" disabled selected>Seleccionar rol</option>
            <option value="1">Administrador</option>
            <option value="3">Gerencia</option>
            <option value="2">Tractorista</option>
          </select>
        </div>
        <button type="submit">Registrar</button>
      </form>
      <!-- Mensajes de error o éxito -->
      <?php if (!empty($mensaje)): ?>
        <script>
          Swal.fire({
            icon: 'error',
            title: 'Atención',
            text: '<?= json_encode($mensaje) ?>',
            confirmButtonColor: '#3085d6'
          });
        </script>
      <?php endif; ?>
      <?php if (!empty($mensajeExito)): ?>
        <script>
          document.addEventListener("DOMContentLoaded", function () {
            Swal.fire({
              icon: 'success',
              title: 'Registro exitoso',
              text: 'El usuario fue registrado correctamente',
              confirmButtonColor: '#3085d6'
            }).then(() => {
              window.location.href = '../campo/campo.php';
            });
          });
        </script>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>