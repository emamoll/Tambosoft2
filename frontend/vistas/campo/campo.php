<?php

require_once __DIR__ . '../../../../backend/controladores/campoController.php';

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}


$controller = new CampoController();
$campos = $controller->obtenerCampos();
$mensaje = $controller->procesarFormularios();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Campos</title>
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
  <!--	--------------->
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>

  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Campo</div>
      <form method="POST">
        <div class="form-group">
          <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
            placeholder=" ">
          <label for="nombre">Nombre de campo</label>
        </div>
        <div class="form-group">
          <input type="text" id="ubicacion" name="ubicacion" value="<?= htmlspecialchars($_POST['ubicacion'] ?? '') ?>"
            placeholder=" ">
          <label for="ubicacion">Ubicación</label>
        </div>
        <div class="botones-container">
          <button type="submit" name="accion" value="registrar">Registrar campo</button>
          <button type="submit" name="accion" value="modificar">Modificar campo</button>
          <button type="submit" name="accion" value="eliminar">Eliminar campo</button>
        </div>
        <?php if (!empty($mensaje)): ?>
          <script>
          Swal.fire({
            icon: '<?= $mensaje["tipo"] ?>',
            title: '<?= $mensaje["tipo"] === "success" ? "Éxito" : "Atención" ?>',
            text: <?= json_encode($mensaje["mensaje"]) ?>,
            confirmButtonColor: '#3085d6'
          }).then(() => {
            <?php if ($mensaje["tipo"] === "success"): ?>
              window.location.href = window.location.pathname; // recargar sin reenviar POST
            <?php endif; ?>
    });
        </script>
        <?php endif; ?>
      </form>
    </div>

    <h2 class="titulosSecciones">Campos</h2>
    <table class="tabla" id="tablaContainer">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Ubicacion</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($campos)): ?>
          <?php foreach ($campos as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c->getNombre()) ?></td>
              <td><?= htmlspecialchars($c->getUbicacion()) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3">No hay campos cargados.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>

</html>