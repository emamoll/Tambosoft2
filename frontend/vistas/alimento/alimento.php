<?php

require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}


$controller = new AlimentoController();
$alimentos = $controller->obtenerAlimentos();
$mensaje = $controller->procesarFormularios();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Alimentos</title>
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
      <div class="form-title">Alimento</div>
      <form method="POST">
        <div class="form-group">
          <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
            placeholder=" ">
          <label for="nombre">Nombre de alimento</label>
        </div>
        <div class="form-group">
          <input type="number" step="0.01" id="precio" name="precio"
            value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>" placeholder=" ">
          <label for="precio">Precio</label>
        </div>
        <div class="form-group">
          <input type="text" id="descripcion" name="descripcion"
            value="<?= htmlspecialchars($_POST['descripcion'] ?? '') ?>" placeholder=" ">
          <label for="descripcion">Descripcion</label>
        </div>
        <div class="form-group">
          <input type="number" step="0.01" id="peso" name="peso" value="<?= htmlspecialchars($_POST['peso'] ?? '') ?>"
            placeholder=" ">
          <label for="peso">Peso</label>
        </div>
        <div class="form-group">
          <input type="date" id="fecha_vencimiento" name="fecha_vencimiento"
            value="<?= htmlspecialchars($_POST['fecha_vencimiento'] ?? '') ?>" placeholder=" ">
          <label for="fecha_vencimiento">Fecha de vencimiento</label>
        </div>
        <div class="botones-container">
          <button type="submit" name="accion" value="registrar">Registrar alimento</button>
          <button type="submit" name="accion" value="modificar">Modificar alimento</button>
          <button type="submit" name="accion" value="eliminar">Eliminar alimento</button>
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

    <h2 class="titulosSecciones">Alimentos</h2>
    <table class="tabla" id="tablaContainer">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Precio</th>
          <th>Descripcion</th>
          <th>Peso</th>
          <th>Fecha de vencimiento</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($alimentos)): ?>
        <?php foreach ($alimentos as $a): ?>
        <tr>
          <td>
            <?= htmlspecialchars($a->getNombre()) ?>
          </td>
          <td>$
            <?= htmlspecialchars($a->getPrecio()) ?>
          </td>
          <td>
            <?= htmlspecialchars($a->getDescripcion()) ?>
          </td>
          <td>
            <?= htmlspecialchars($a->getPeso()) ?>
          </td>
          <td>
            <?php
            $fecha = $a->getFecha_vencimiento();
            $fechaFormateada = date('d-m-Y', strtotime($fecha));
            echo htmlspecialchars($fechaFormateada);
            ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="3">No hay alimentos cargados.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>

</html>