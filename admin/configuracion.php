<?php
// admin/configuracion.php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

requiereAdmin();

$mensaje = '';

// Guardar configuración
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach($_POST as $clave => $valor) {
        $stmt = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$clave, $valor, $valor]);
    }
    registrarLog('actualizar_configuracion', 'configuracion');
    $mensaje = "Configuración guardada correctamente";
}

// Obtener configuración actual
$config = [];
$stmt = $pdo->query("SELECT clave, valor FROM configuracion");
while($row = $stmt->fetch()) {
    $config[$row['clave']] = $row['valor'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',sans-serif;background:#f4f7fb;}
        .admin-container{display:flex;min-height:100vh;}
        .admin-sidebar{width:280px;background:linear-gradient(180deg,#0f3d5e 0%,#0a2c44 100%);color:white;padding:24px 16px;height:100vh;position:sticky;top:0;}
        .admin-logo{display:flex;align-items:center;gap:12px;padding-bottom:24px;border-bottom:1px solid rgba(255,255,255,0.1);margin-bottom:24px;}
        .admin-logo i{font-size:32px;color:#00c896;}
        .admin-nav a{display:flex;align-items:center;gap:12px;padding:12px 16px;color:rgba(255,255,255,0.8);text-decoration:none;border-radius:10px;}
        .admin-nav a:hover{background:rgba(255,255,255,0.1);}
        .admin-nav a.active{background:#00c896;color:white;}
        .admin-main{flex:1;padding:24px 32px;}
        .header-bar{margin-bottom:24px;}
        .header-bar h1{color:#0f3d5e;}
        .form-container{background:white;border-radius:16px;padding:30px;max-width:600px;}
        .form-group{margin-bottom:20px;}
        label{display:block;margin-bottom:8px;font-weight:600;color:#0f3d5e;}
        input,select{width:100%;padding:12px;border:1px solid #e2e8f0;border-radius:10px;}
        .btn{padding:12px 24px;border-radius:10px;border:none;cursor:pointer;background:#00c896;color:white;font-weight:600;}
        .mensaje{padding:12px;background:#e8f5e9;color:#2e7d32;border-radius:8px;margin-bottom:20px;}
        @media(max-width:768px){.admin-sidebar{display:none;}.admin-main{padding:16px;}}
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="admin-sidebar">
        <div class="admin-logo"><i class="fas fa-crown"></i><div><h2>Holcim Admin</h2><p>Panel de Control</p></div></div>
        <nav class="admin-nav">
            <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a>
            <a href="roles.php"><i class="fas fa-user-tag"></i> Roles</a>
            <a href="logs.php"><i class="fas fa-history"></i> Logs</a>
            <a href="configuracion.php" class="active"><i class="fas fa-cog"></i> Configuración</a>
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Volver</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="header-bar">
            <h1><i class="fas fa-cog"></i> Configuración del Sistema</h1>
        </div>
        <?php if($mensaje): ?><div class="mensaje"><?php echo $mensaje; ?></div><?php endif; ?>
        <div class="form-container">
            <form method="POST">
                <div class="form-group"><label>Nombre del Sistema</label><input type="text" name="sistema_nombre" value="<?php echo htmlspecialchars($config['sistema_nombre'] ?? 'Holcim'); ?>"></div>
                <div class="form-group"><label>Versión</label><input type="text" name="sistema_version" value="<?php echo htmlspecialchars($config['sistema_version'] ?? '4.0.0'); ?>"></div>
                <div class="form-group"><label>Intentos máximos de login</label><input type="number" name="limite_intentos_login" value="<?php echo htmlspecialchars($config['limite_intentos_login'] ?? '5'); ?>"></div>
                <div class="form-group"><label>Duración de sesión (minutos)</label><input type="number" name="tiempo_sesion" value="<?php echo htmlspecialchars($config['tiempo_sesion'] ?? '480'); ?>"></div>
                <div class="form-group"><label>Email de soporte</label><input type="email" name="email_soporte" value="<?php echo htmlspecialchars($config['email_soporte'] ?? 'soporte@holcim.com'); ?>"></div>
                <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar Configuración</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>