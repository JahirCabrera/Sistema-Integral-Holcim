<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

requiereAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rol_id = $_POST['rol_id'];
    
    $errores = [];
    if (strlen($password) < 6) $errores[] = "La contraseña debe tener al menos 6 caracteres";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    if ($stmt->fetchColumn() > 0) $errores[] = "El usuario ya existe";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) $errores[] = "El email ya está registrado";
    
    if (empty($errores)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, email, password, rol_id, estado) VALUES (?, ?, ?, ?, ?, 1)");
        if ($stmt->execute([$nombre, $usuario, $email, $hash, $rol_id])) {
            registrarLog('crear_usuario', 'usuarios', $pdo->lastInsertId());
            header("Location: usuarios.php?mensaje=Usuario creado correctamente");
            exit;
        }
    }
    header("Location: usuarios.php?error=" . urlencode(implode(", ", $errores)));
    exit;
}
?>