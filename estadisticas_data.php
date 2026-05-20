<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/json');

try {

    // Estadísticas básicas
    $sql = "
        SELECT 
            COUNT(*) AS total_usuarios,
            COUNT(CASE WHEN is_admin = 0 THEN 1 END) AS usuarios_normales,
            COUNT(CASE WHEN is_admin = 1 THEN 1 END) AS administradores,
            COUNT(CASE WHEN DATE(fecha_registro) = CURDATE() THEN 1 END) AS nuevos_hoy
        FROM Users
    ";

    $stats = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

    if (!$stats) {
        $stats = [
            'total_usuarios' => 0,
            'usuarios_normales' => 0,
            'administradores' => 0,
            'nuevos_hoy' => 0
        ];
    }

    // Usuarios activos (sin last_login, colocamos total)
    $usuarios_activos = $stats['total_usuarios'];

    // Alertas activas
    $sql_alertas = "SELECT COUNT(*) FROM Users WHERE apellido_materno IS NULL OR apellido_materno = ''";
    $alertas_activas = (int)$pdo->query($sql_alertas)->fetchColumn();

    // Actividad últimos 7 días
    $sql_actividad = "
        SELECT 
            DATE(fecha_registro) AS dia,
            COUNT(*) AS cantidad
        FROM Users
        WHERE fecha_registro >= CURDATE() - INTERVAL 7 DAY
        GROUP BY DATE(fecha_registro)
        ORDER BY dia ASC
    ";
    $actividad_7dias = $pdo->query($sql_actividad)->fetchAll(PDO::FETCH_ASSOC);

    // Actividad reciente
    $sql_reciente = "
        SELECT 
            usuario,
            'Registro de usuario' AS accion,
            fecha_registro AS fecha,
            'N/A' AS ip,
            CONCAT(nombre, ' ', apellido_paterno) AS detalles
        FROM Users
        ORDER BY fecha_registro DESC
        LIMIT 8
    ";
    $actividad_reciente = $pdo->query($sql_reciente)->fetchAll(PDO::FETCH_ASSOC);

    // Distribución por perfil
    $sql_dist = "
        SELECT 
            COALESCE(nombre_perfil, 'Sin perfil') AS nombre_perfil,
            COUNT(*) AS cantidad
        FROM Users
        GROUP BY nombre_perfil
    ";
    $distribucion = $pdo->query($sql_dist)->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_usuarios'   => (int)$stats['total_usuarios'],
            'usuarios_normales'=> (int)$stats['usuarios_normales'],
            'administradores'  => (int)$stats['administradores'],
            'usuarios_activos' => (int)$usuarios_activos,
            'nuevos_hoy'       => (int)$stats['nuevos_hoy'],
            'alertas_activas'  => (int)$alertas_activas
        ],
        'actividad_7dias'    => $actividad_7dias,
        'actividad_reciente' => $actividad_reciente,
        'distribucion'       => $distribucion
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'error' => 'Error en consulta: ' . $e->getMessage()
    ]);
}
