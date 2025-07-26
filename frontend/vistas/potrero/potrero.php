<?php

require_once __DIR__ . '../../../../backend/controladores/campoController.php';
require_once __DIR__ . '../../../../backend/controladores/categoriaController.php';
require_once __DIR__ . '../../../../backend/controladores/potreroController.php';
require_once __DIR__ . '../../../../backend/controladores/pasturaController.php';


session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}

$controllerPotrero = new PotreroController();
$controllerPastura = new PasturaController();
$controllerCategoria = new CategoriaController();
$controllerCampo = new CampoController();

$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mensaje = $controllerPotrero->procesarFormularios();
}

$potreros = $controllerPotrero->obtenerPotreros();
$pasturas = $controllerPastura->obtenerPasturas();
$categorias = $controllerCategoria->obtenerCategorias();
$campos = $controllerCampo->obtenerCampos();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Potreros</title>
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
      <div class="form-title">Potreros</div>
      <form method="POST">
        <div class="form-group">
          <input type="text" id="nombre" name="nombre" value="" placeholder=" ">
          <label for="nombre">Nombre del potrero</label>
        </div>
        <div class="form-group">
          <input type="number" id="superficie" name="superficie"
            value="<?= htmlspecialchars($_POST['superficie'] ?? '') ?>" placeholder=" ">
          <label for="superficie">Superficie en ha</label>
        </div>
        <div class="form-group select-group">
          <select name="pastura_nombre">
            <option value="" disabled selected>Seleccione una pastura</option>
            <?php foreach ($pasturas as $p): ?>
              <option value="<?= htmlspecialchars($p->getNombre()) ?>">
                <?= htmlspecialchars($p->getNombre()) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group select-group">
          <select name="categoria_nombre">
            <option value="" disabled selected>Seleccione un categoria</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= htmlspecialchars($c->getNombre()) ?>">
                <?= htmlspecialchars($c->getNombre()) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group select-group">
          <select name="campo_nombre">
            <option value="" disabled selected>Seleccione un campo</option>
            <?php foreach ($campos as $c): ?>
              <option value="<?= htmlspecialchars($c->getNombre()) ?>">
                <?= htmlspecialchars($c->getNombre()) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="botones-container">
          <button type="submit" name="accion" value="registrar">Registrar potrero</button>
          <button type="submit" name="accion" value="modificar">Modificar potrero</button>
          <button type="submit" name="accion" value="eliminar">Eliminar potrero</button>
        </div>
        <?php if (!empty($mensaje)): ?>
          <script>
            Swal.fire({
              icon: '<?= $mensaje["tipo"] ?>',
              title: '<?= $mensaje["tipo"] === "success" ? "Éxito" : "Atención" ?>',
              text: '<?= $mensaje["mensaje"] ?>',
              confirmButtonColor: '#3085d6'
            }).then(() => {
              // Simplemente recargar la página después de cualquier acción
              window.location.href = window.location.pathname;
            });
          </script>
        <?php endif; ?>
      </form>
    </div>
    <h2 class="titulosSecciones">Potreros</h2>
    <table class="tabla" id="tablaContainerPo">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Superficie</th>
          <th>Pastura</th>
          <th>Categoria</th>
          <th>Campo</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($potreros)): ?>
          <?php foreach ($potreros as $p): ?>
            <tr>
              <td>
                <?= htmlspecialchars($p->getNombre()) ?>
              </td>
              <td>
                <?= htmlspecialchars($p->getSuperficie()) ?>
              </td>
              <td>
                <?php
                foreach ($pasturas as $pas) {
                  if ($pas->getId() === $p->getPastura_id()) {
                    echo htmlspecialchars($pas->getNombre());
                    break;
                  }
                }
                ?>
              </td>
              <td>
                <?php
                foreach ($categorias as $cat) {
                  if ($cat->getId() === $p->getCategoria_id()) {
                    echo htmlspecialchars($cat->getNombre());
                    break;
                  }
                }
                ?>
              </td>
              <td>
                <?php
                foreach ($campos as $campo) {
                  if ($campo->getId() === $p->getCampo_id()) {
                    echo htmlspecialchars($campo->getNombre());
                    break;
                  }
                }
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No hay potreros cargados.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>

</html>