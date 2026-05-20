<?php
/**
 * guardar.php
 * Guarda los registros de producción
 */

require_once '../includes/config.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

// Obtener datos
$fecha        = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$periodo      = isset($_POST['periodo']) ? $_POST['periodo'] : '';
$destino_id   = isset($_POST['destino_id']) ? intval($_POST['destino_id']) : 0;
$unidad       = isset($_POST['unidad']) ? trim($_POST['unidad']) : '';
$volumen      = isset($_POST['volumen']) ? floatval($_POST['volumen']) : 0;
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

// Validar campos obligatorios
$errores = array();

if (empty($fecha)) {
    $errores[] = 'La fecha es obligatoria';
}
if (empty($periodo)) {
    $errores[] = 'El periodo/turno es obligatorio';
}
if ($destino_id <= 0) {
    $errores[] = 'El destino es obligatorio';
}
if (empty($unidad)) {
    $errores[] = 'La unidad es obligatoria';
}
if ($volumen <= 0) {
    $errores[] = 'El volumen debe ser mayor a 0';
}

// Validar que la fecha no sea futura
if (!empty($fecha) && $fecha > date('Y-m-d')) {
    $errores[] = 'No se permiten fechas futuras';
}

// Si hay errores, mostrarlos
if (!empty($errores)) {
    echo '<div style="font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; background: #fee; border: 1px solid #fcc; border-radius: 8px;">';
    echo '<h3 style="color: #c00; margin: 0 0 15px 0;">❌ Error al guardar</h3>';
    echo '<ul style="margin: 0 0 20px 0; padding-left: 20px;">';
    foreach ($errores as $error) {
        echo "<li style='color: #c00; margin: 5px 0;'>$error</li>";
    }
    echo '</ul>';
    echo '<a href="crear.php" style="display: inline-block; background: #003B5C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">← Volver al formulario</a>';
    echo '</div>';
    exit;
}

// Convertir periodo a hora
if ($periodo == 'Turno Mañana') {
    $hora = '06:00:00';
} elseif ($periodo == 'Turno Tarde') {
    $hora = '14:00:00';
} elseif ($periodo == 'Turno Noche') {
    $hora = '22:00:00';
} elseif ($periodo == 'Día Completo') {
    $hora = '00:00:00';
} else {
    $hora = '00:00:00';
}

// Guardar en base de datos
try {
    $sql = "INSERT INTO despacho (fecha, hora, volumen, destino_id, unidad, observaciones) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fecha, $hora, $volumen, $destino_id, $unidad, $observaciones]);
    
    header('Location: listar.php?msg=success');
    exit;
    
} catch (PDOException $e) {
    die("❌ Error en la base de datos: " . $e->getMessage());
}
?>