<?php
require_once '../includes/config.php';

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

// Obtener datos del formulario
$nombre = trim($_POST['nombre']);
$direccion = trim($_POST['direccion']);
$contacto = trim($_POST['contacto']);
$telefono = trim($_POST['telefono']);

// Validar campo requerido
if (empty($nombre)) {
    die("El nombre del destino es obligatorio");
}

try {
    // Verificar si es edición o nuevo registro
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Actualizar
        $id = intval($_POST['id']);
        $sql = "UPDATE destinos SET nombre = ?, direccion = ?, contacto = ?, telefono = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $direccion, $contacto, $telefono, $id]);
    } else {
        // Insertar
        $sql = "INSERT INTO destinos (nombre, direccion, contacto, telefono) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $direccion, $contacto, $telefono]);
    }
    
    header('Location: listar.php?msg=success');
    exit;
    
} catch (PDOException $e) {
    die("Error al guardar: " . $e->getMessage());
}
?>