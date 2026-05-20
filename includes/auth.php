<?php
// includes/auth.php - Sistema de autenticación y permisos

function verificarSesion() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function esAdmin() {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
}

function requiereAdmin() {
    if (!esAdmin()) {
        header("Location: index.php?error=no_autorizado");
        exit;
    }
}

function tienePermiso($permiso) {
    if (esAdmin()) return true;
    return isset($_SESSION['permisos']) && in_array($permiso, $_SESSION['permisos']);
}

function registrarLog($accion, $tabla = null, $registro_id = null, $datos_antes = null, $datos_despues = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO logs_actividad (usuario_id, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $usuario_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    return $stmt->execute([$usuario_id, $accion, $tabla, $registro_id, $datos_antes, $datos_despues, $ip, $user_agent]);
}

function obtenerPermisosUsuario($rol_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.nombre FROM permisos p
        JOIN roles_permisos rp ON p.id = rp.permiso_id
        WHERE rp.rol_id = ?
    ");
    $stmt->execute([$rol_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>