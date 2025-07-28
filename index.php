<?php

session_start();
require_once __DIR__ . '../backend/controladores/usuarioController.php';
require_once __DIR__ . '../backend/controladores/alimentoController.php';
require_once __DIR__ . '../backend/controladores/almacenController.php';
require_once __DIR__ . '../backend/controladores/campoController.php';
require_once __DIR__ . '../backend/controladores/categoriaController.php';
require_once __DIR__ . '../backend/controladores/estadoController.php';
require_once __DIR__ . '../backend/controladores/ordenController.php';
require_once __DIR__ . '../backend/controladores/pasturaController.php';
require_once __DIR__ . '../backend/controladores/potreroController.php';
require_once __DIR__ . '../backend/controladores/stock_almacenController.php';

try {
  new UsuarioController();
  new AlimentoController();
  new AlmacenController();
  new CampoController();
  new CategoriaController();
  new EstadoController();
  new OrdenController();
  new PasturaController();
  new PotreroController();
  new Stock_almacenController();
  // No es necesario guardar las instancias en variables si solo es para ejecutar el constructor.
} catch (Exception $e) {
  error_log("Error al crear tablas: " . $e->getMessage());
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $controller = new UsuarioController();
  $usuario = $controller->loginUsuario($_POST['username'], $_POST['password']);

  if ($usuario) {
    $_SESSION['username'] = $usuario->getUsername();
    $_SESSION['rol_id'] = $usuario->getRol_id();
    $_SESSION['token'] = $usuario->getToken();

    if ($usuario->getRol_id() == 1) {
      header('Location: frontend/vistas/usuario/adminHome.php');
    } elseif ($usuario->getRol_id() == 2) {
      header('Location: frontend/vistasTractorista/usuario/tractoristaHome.php');
    } else {
      header('Location: frontend/vistasGerencia/usuario/gerenciaHome.php');
    }
    exit;
  } else {
    $mensaje = "Usuario o contraseña incorrectos.";
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Iniciar sesión</title>
  <link rel="icon" href="frontend/img/logo2.png" type="image/png">
  <link rel="stylesheet" href="frontend/css/estilos.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <div class="main inputIndex">
    <div class="form-container">
      <div class="logo-container"><img src="frontend/img/logo2.png" alt="Icono Tambosoft" class="logoIndex"></div>
      <div class="form-title">Iniciar sesión</div>
      <form method="POST">
        <div class="form-group">
          <input type="text" id="username" name="username" placeholder=" ">
          <label for="username">Usuario</label>
        </div>
        <div class="form-group">
          <input type="password" id="password" name="password" placeholder=" ">
          <label for="password">Contraseña</label>
        </div>
        <button type="submit">Ingresar</button>
      </form>
      <!-- Mensajes de error o éxito -->
      <?php if (!empty($mensaje)): ?>
        <script>
          Swal.fire({
            icon: 'info',
            title: 'Atención',
            text: '<?= json_encode($mensaje) ?>',
            confirmButtonColor: '#3085d6'
          });
        </script>
      <?php endif; ?>
    </div>
  </div>
  <!-- <div class="content">
                <div class="checkbox">
                    <input type="checkbox" id="remember-me">
                    <label for="remember-me">Recordar</label>
                </div>
                <div class="pass-link"><a href="#">Olvide mi contraseña</a></div>
            </div> -->
  </div>
  </div>
</body>

</html>