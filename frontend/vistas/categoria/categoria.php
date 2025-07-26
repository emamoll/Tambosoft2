<?php

require_once __DIR__ . '../../../../backend/controladores/categoriaController.php';

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
	header('Location: ../../../index.php');
	exit;
}

$controllerCategoria = new CategoriaController();

$mensaje = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mensaje = $controllerCategoria->procesarFormularios();
}

$categorias = $controllerCategoria->obtenerCategorias();

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tambosoft: Categorías</title>
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
			<div class="form-title">Categorías</div>
			<form method="POST">
				<div class="form-group">
					<input type="text" id="nombre" name="nombre" value="" placeholder=" ">
					<label for="nombre">Nombre de la categoría</label>
				</div>
				<div class="botones-container">
					<button type="submit" name="accion" value="registrar">Registrar categoría</button>
					<!-- <button type="submit" name="accion" value="modificar">Modificar categoría</button> -->
					<button type="submit" name="accion" value="eliminar">Eliminar categoría</button>
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
		<h2 class="titulosSecciones">Categorías</h2>
		<table class="tabla" id="tablaContainerPo">
			<thead>
				<tr>
					<th>Nombre</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($categorias)): ?>
					<?php foreach ($categorias as $c): ?>
						<tr>
							<td>
								<?= htmlspecialchars($c->getNombre()) ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="2">No hay categorías cargadas.</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

</body>

</html>