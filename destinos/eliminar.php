<?php
require_once '../includes/config.php';

// Obtener ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

try {
    // Eliminar (soft delete)
    $sql = "UPDATE destinos SET activo = 0 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    
    header('Location: listar.php?msg=success');
    exit;
    
} catch (PDOException $e) {
    die("Error al eliminar: " . $e->getMessage());
}
?>