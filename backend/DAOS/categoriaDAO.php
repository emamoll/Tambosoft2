<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/categoria/categoriaTabla.php';
require_once __DIR__ . '../../modelos/categoria/categoriaModelo.php';

class CategoriaDAO
{
	private $db;
	private $conn;
	private $crearTabla;

	public function __construct()
	{
		$this->db = DatabaseFactory::createDatabaseConnection('mysql');
		$this->crearTabla = new CategoriaCrearTabla($this->db);
		$this->crearTabla->crearTablaCategoria();
		$this->conn = $this->db->connect();
	}

	public function registrarCategoria(Categoria $c)
	{
		$sqlVer = "SELECT id FROM categorias WHERE nombre = ?";
		$stmtVer = $this->conn->prepare($sqlVer);
		$nombreCategoria = $c->getNombre();
		$stmtVer->bind_param("s", $nombreCategoria);
		$stmtVer->execute();
		$stmtVer->store_result();

		if ($stmtVer->num_rows > 0) {
			$stmtVer->close();
			return false; // La categoría ya existe
		}
		$stmtVer->close();

		// Eliminado potrero_id de la consulta INSERT
		$sql = "INSERT INTO categorias (nombre) VALUES (?)";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $nombreCategoria);

		if (!$stmt->execute()) {
			error_log("Error al insertar categoría: " . $stmt->error);
			$stmt->close();
			return false;
		}
		$stmt->close();
		return true;
	}

	public function getAllCategorias()
	{
		$sql = "SELECT * FROM categorias";
		$result = $this->conn->query($sql);

		if (!$result) {
			die("Error en la consulta: " . $this->conn->error);
		}

		$categorias = [];

		while ($row = $result->fetch_assoc()) {
			// Constructor modificado
			$categorias[] = new Categoria($row['id'], $row['nombre']);
		}

		return $categorias;
	}

	public function getCategoriaById($id)
	{
		$sql = "SELECT * FROM categorias WHERE id = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$stmt->close();

		if ($row) {
			// Constructor modificado
			return new Categoria($row['id'], $row['nombre']);
		}
		return null;
	}

	public function getCategoriaByNombre($nombre)
	{
		$sql = "SELECT * FROM categorias WHERE nombre = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $nombre);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$stmt->close();

		if ($row) {
			// Constructor modificado
			return new Categoria($row['id'], $row['nombre']);
		}
		return null;
	}

	public function modificarCategoria(Categoria $c)
	{
		// Eliminado potrero_id de la consulta UPDATE
		$sql = "UPDATE categorias SET nombre = ? WHERE id = ?";
		$stmt = $this->conn->prepare($sql);
		$nombre = $c->getNombre();
		$id = $c->getId();
		$stmt->bind_param("si", $nombre, $id); // Tipo de parámetro modificado

		return $stmt->execute();
	}

	public function eliminarCategoria($nombre)
	{
		$sql = "DELETE FROM categorias WHERE n$nombre = ?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $nombre);
		return $stmt->execute();
	}
}