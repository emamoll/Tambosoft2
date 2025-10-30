<?php

require_once __DIR__ . '../../../../backend/controladores/proveedorController.php';


session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}

$controllerProveedor = new ProveedorController();

$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mensaje = $controllerProveedor->procesarFormularios();
}

$proveedores = $controllerProveedor->obtenerProveedores();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Proveedores</title>
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
      <div class="form-title">Proveedores</div>
      <form method="POST">
        <div class="form-group">
          <input type="text" id="nombre" name="nombre" value="" placeholder=" ">
          <label for="nombre">Nombre del proveedor</label>
        </div>
        <div class="form-group">
          <input type="text" id="direccion" name="direccion" value="" placeholder=" ">
          <label for="direccion">Dirección del proveedor</label>
        </div>
        <div class="form-group">
          <input type="number" id="telefono" name="telefono" value="" placeholder=" ">
          <label for="telefono">Telefono del proveedor</label>
        </div>
        <div class="form-group">
          <input type="email" id="email" name="email" value="" placeholder=" ">
          <label for="email">Email del proveedor</label>
        </div>
        <div class="botones-container">
          <button type="submit" name="accion" value="registrar">Registrar proveedor</button>
          <button type="submit" name="accion" value="modificar">Modificar proveedor</button>
          <button type="submit" name="accion" value="eliminar">Eliminar proveedor</button>
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
    <h2 class="titulosSecciones">Proveedores</h2>
    <table class="tabla" id="tablaContainerPo">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Dirección</th>
          <th>Telefono</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($proveedores)): ?>
          <?php foreach ($proveedores as $p): ?>
            <tr>
              <td>
                <?= htmlspecialchars($p->getNombre()) ?>
              </td>
              <td>
                <?= htmlspecialchars($p->getDireccion()) ?>
              </td>
              <td>
                <?= htmlspecialchars($p->getTelefono()) ?>
              </td>
              <td>
                <?= htmlspecialchars($p->getEmail()) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">No hay proveedores cargados.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <script>
    // Array JS con los nombres de proveedores actuales (todos en minúsculas)
    const existingProveedores = <?=
      json_encode(array_map(function ($p) {
            return mb_strtolower($p->getNombre()); }, $proveedores ?? []));
    ?>;

    const form = document.querySelector('form[method="POST"]');
    if (form) {
      form.addEventListener('submit', function (e) {
        // Determinar qué botón disparó el submit (modern browsers soportan e.submitter)
        let action = (e.submitter && e.submitter.value) ? e.submitter.value : (document.activeElement && document.activeElement.value) ? document.activeElement.value : null;

        const nombre = (document.getElementById('nombre').value || '').trim();
        const telefonoRaw = (document.getElementById('telefono').value || '').toString().trim();
        // Normalizamos teléfono a sólo dígitos (por si el input es type=number o con espacios)
        const telefono = telefonoRaw.replace(/\D/g, '');

        // Validación teléfono: 7 a 11 dígitos
        if (!/^\d{7,11}$/.test(telefono)) {
          e.preventDefault();
          Swal.fire({
            icon: 'error',
            title: 'Teléfono inválido',
            text: 'El teléfono debe contener sólo dígitos y tener entre 7 y 11 caracteres.'
          });
          return;
        }

        // Si la acción es "registrar", comprobamos duplicado por nombre
        if (action === 'registrar') {
          if (!nombre) {
            e.preventDefault();
            Swal.fire({
              icon: 'error',
              title: 'Falta nombre',
              text: 'Completá el nombre del proveedor.'
            });
            return;
          }
          if (existingProveedores.includes(nombre.toLowerCase())) {
            e.preventDefault();
            Swal.fire({
              icon: 'error',
              title: 'Proveedor duplicado',
              text: 'Ya existe un proveedor con ese nombre.'
            });
            return;
          }
        }

        // Si querés que al enviar se preserve el formato del teléfono en el servidor,
        // podés escribir el valor 'telefono' solo con dígitos:
        document.getElementById('telefono').value = telefono;
        // y permitir el submit
      });
    }
  </script>
</body>

</html>