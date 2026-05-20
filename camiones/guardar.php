<?php
/**
 * guardar.php - Guardar camión en la base de datos
 */

session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

// Obtener y limpiar datos
$placa            = isset($_POST['placa']) ? strtoupper(trim($_POST['placa'])) : '';
$modelo           = isset($_POST['modelo']) ? trim($_POST['modelo']) : '';
$marca            = isset($_POST['marca']) ? trim($_POST['marca']) : '';
$capacidad        = isset($_POST['capacidad']) ? floatval($_POST['capacidad']) : 0;
$status           = isset($_POST['estado']) ? trim($_POST['estado']) : 'disponible';
$condicion        = isset($_POST['condicion']) ? trim($_POST['condicion']) : 'Bueno';
$prox_mantenimiento = isset($_POST['prox_mantenimiento']) && !empty($_POST['prox_mantenimiento']) ? $_POST['prox_mantenimiento'] : null;
$observaciones    = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

// Mapear estado a status (para coincidir con la BD)
$mapa_status = [
    'Disponible'    => 'disponible',
    'Ocupado'       => 'en_servicio',
    'Mantenimiento' => 'mantenimiento'
];
$status = isset($mapa_status[$status]) ? $mapa_status[$status] : 'disponible';

// Validaciones
$errores = [];

if (empty($placa)) {
    $errores[] = 'La placa es obligatoria';
} elseif (strlen($placa) < 3) {
    $errores[] = 'La placa debe tener al menos 3 caracteres';
}

if ($capacidad <= 0) {
    $errores[] = 'La capacidad debe ser mayor a 0';
}

if (empty($condicion)) {
    $errores[] = 'La condición es obligatoria';
}

// Mostrar errores si existen
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

// Guardar en base de datos
try {
    $sql = "INSERT INTO camiones (placa, modelo, marca, capacidad, status, condicion, prox_mantenimiento, observaciones) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa, $modelo, $marca, $capacidad, $status, $condicion, $prox_mantenimiento, $observaciones]);
    
    header('Location: listar.php?msg=created');
    exit;
    
} catch (PDOException $e) {
    // Verificar si es duplicado de placa
    if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo '<div style="font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; background: #fee; border: 1px solid #fcc; border-radius: 8px;">';
        echo '<h3 style="color: #c00;">❌ Error: Placa duplicada</h3>';
        echo '<p>Ya existe un camión registrado con la placa <strong>' . htmlspecialchars($placa) . '</strong>.</p>';
        echo '<a href="crear.php" style="display: inline-block; background: #003B5C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;">← Volver al formulario</a>';
        echo '</div>';
    } else {
        echo '<div style="font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; background: #fee; border: 1px solid #fcc; border-radius: 8px;">';
        echo '<h3 style="color: #c00;">❌ Error en la base de datos</h3>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<a href="crear.php" style="display: inline-block; background: #003B5C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;">← Volver al formulario</a>';
        echo '</div>';
    }
    exit;
}
?>