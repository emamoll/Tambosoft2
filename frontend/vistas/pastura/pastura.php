<?php

require_once __DIR__ . '../../../../backend/controladores/pasturaController.php';

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}

$controllerPastura = new PasturaController();

$mensaje = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mensaje = $controllerPastura->procesarFormularios();
}

$pasturas = $controllerPastura->obtenerPasturas();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Pasturas</title>
  <link rel="icon" href=".../../../../img/logo2.png" type="image/png">
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bodyHome">
  <?php require_once __DIR__ . '../../secciones/header.php'; ?>
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>
  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Pasturas</div>
      <form method="POST">
        <div class="form-group">
          <input type="text" id="nombre" name="nombre" value="" placeholder=" ">
          <label for="nombre">Nombre de la pastura</label>
        </div>
        <div class="botones-container">
          <button type="submit" name="accion" value="registrar">Registrar pastura</button>
          <!-- <button type="submit" name="accion" value="modificar">Modificar pastura</button> -->
          <button type="submit" name="accion" value="eliminar">Eliminar pastura</button>
        </div>
        <?php if (!empty($mensaje)): ?>
          <script>
            Swal.fire({
              icon: '<?= $mensaje["tipo"] ?>',
              title: '<?= $mensaje["tipo"] === "success" ? "Éxito" : "Atención" ?>',
              text: '<?= $mensaje["mensaje"] ?>',
              confirmButtonColor: '#3085d6'
            }).then(() => {
              // Recargar la página después de una acción exitosa para limpiar el formulario y actualizar la tabla
              <?php if ($mensaje["tipo"] === "success"): ?>
                window.location.href = window.location.pathname; // Ya no necesita window.location.search
              <?php endif; ?>
            });
          </script>
        <?php endif; ?>
      </form>
    </div>
    <h2 class="titulosSecciones">Pasturas</h2>
    <table class="tabla" id="tablaContainerPo">
      <thead>
        <tr>
          <th>Nombre</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($pasturas)): ?>
          <?php foreach ($pasturas as $p): ?>
            <tr>
              <td>
                <?= htmlspecialchars($p->getNombre()) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="2">No hay pasturas cargadas.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>

</html>