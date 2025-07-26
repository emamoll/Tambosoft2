<?php

session_start();
require_once __DIR__ . '../../../../backend/controladores/usuarioController.php';

if (!isset($_SESSION['token'])) {
  header("Location: index.php");
  exit;
}

$controller = new UsuarioController();
$usuario = $controller->getUsuarioByToken($_SESSION['token']);

if (!$usuario) {
  session_destroy();
  header("Location: index.php");
  exit;
}

if ($usuario->getRol_id() != 1) {
  header('Location: usuario.php');
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Home</title>
  <link rel="icon" href=".../../../../img/logo2.png" type="image/png">
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</head>

<body class="bodyHome">
  <?php require_once __DIR__ . '../../secciones/header.php'; ?>
  <!--	--------------->
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>
  <h1 class="mensajeBienvenida">Bienvenido <?php echo $usuario->getUsername() ?></h1>
</body>

</html>