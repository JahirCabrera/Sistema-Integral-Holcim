<?php
// admin/index.php - Panel de Control de Administración
require_once '../includes/config.php';
require_once '../includes/auth.php';

session_start();

// Verificar que sea administrador
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    header("Location: ../index.php?error=no_autorizado");
    exit;
}

// Estadísticas
$stats = [];

// Total usuarios
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
$stats['total_usuarios'] = $stmt->fetchColumn();

// Usuarios por rol
$stmt = $pdo->query("
    SELECT r.nombre, COUNT(u.id) as total 
    FROM roles r 
    LEFT JOIN usuarios u ON r.id = u.rol_id 
    GROUP BY r.id
");
$stats['usuarios_por_rol'] = $stmt->fetchAll();

// Logs de hoy
$stmt = $pdo->query("SELECT COUNT(*) FROM logs_actividad WHERE DATE(created_at) = CURDATE()");
$stats['logs_hoy'] = $stmt->fetchColumn();

// Últimos accesos
$stmt = $pdo->query("
    SELECT nombre, ultimo_acceso, ultimo_ip 
    FROM usuarios 
    WHERE ultimo_acceso IS NOT NULL 
    ORDER BY ultimo_acceso DESC 
    LIMIT 5
");
$stats['ultimos_accesos'] = $stmt->fetchAll();

// Alertas del sistema
$alertas = [];

if ($stats['total_usuarios'] < 3) {
    $alertas[] = "⚠️ Pocos usuarios registrados en el sistema";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Holcim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f7fb;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Admin */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0f3d5e 0%, #0a2c44 100%);
            color: white;
            padding: 24px 16px;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        
        .admin-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 24px;
        }
        
        .admin-logo i {
            font-size: 32px;
            color: #00c896;
        }
        
        .admin-logo h2 {
            font-size: 18px;
        }
        
        .admin-logo p {
            font-size: 10px;
            color: rgba(255,255,255,0.6);
        }
        
        .admin-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s;
        }
        
        .admin-nav a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-nav a.active {
            background: #00c896;
            color: white;
        }
        
        .admin-nav a i {
            width: 22px;
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            padding: 24px 32px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .admin-header h1 {
            font-size: 24px;
            color: #0f3d5e;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #00c896;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .menu-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }
        
        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
            border-color: #00c896;
        }
        
        .menu-card i {
            font-size: 40px;
            color: #00c896;
            margin-bottom: 16px;
        }
        
        .menu-card h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }
        
        .menu-card p {
            font-size: 13px;
            color: #666;
        }
        
        .alert-banner {
            background: #ffebee;
            border-left: 4px solid #ef4444;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                display: none;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .admin-main {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <!-- Sidebar Admin -->
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <i class="fas fa-crown"></i>
            <div>
                <h2>Holcim Admin</h2>
                <p>Panel de Control</p>
            </div>
        </div>
        <nav class="admin-nav">
            <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a>
            <a href="roles.php"><i class="fas fa-user-tag"></i> Roles y Permisos</a>
            <a href="logs.php"><i class="fas fa-history"></i> Logs de Actividad</a>
            <a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a>
            <a href="respaldos.php"><i class="fas fa-database"></i> Respaldos</a>
            <a href="notificaciones.php"><i class="fas fa-bell"></i> Notificaciones</a>
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-tachometer-alt"></i> Panel de Administración</h1>
            <div>
                <span class="live-badge">👑 <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></span>
            </div>
        </div>

        <!-- Alertas -->
        <?php foreach ($alertas as $alerta): ?>
            <div class="alert-banner"><?php echo $alerta; ?></div>
        <?php endforeach; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_usuarios']; ?></div>
                <div>Usuarios Registrados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['logs_hoy']; ?></div>
                <div>Acciones Hoy</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($stats['usuarios_por_rol']); ?></div>
                <div>Roles Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">📊</div>
                <div>Sistema Operativo</div>
            </div>
        </div>

        <!-- Módulos de Administración -->
        <div class="menu-grid">
            <a href="usuarios.php" class="menu-card">
                <i class="fas fa-users"></i>
                <h3>Gestión de Usuarios</h3>
                <p>Crear, editar y eliminar usuarios del sistema</p>
            </a>
            <a href="roles.php" class="menu-card">
                <i class="fas fa-user-tag"></i>
                <h3>Roles y Permisos</h3>
                <p>Configurar roles y permisos de acceso</p>
            </a>
            <a href="logs.php" class="menu-card">
                <i class="fas fa-history"></i>
                <h3>Logs de Actividad</h3>
                <p>Auditoría completa del sistema</p>
            </a>
            <a href="configuracion.php" class="menu-card">
                <i class="fas fa-cog"></i>
                <h3>Configuración</h3>
                <p>Ajustes generales del sistema</p>
            </a>
            <a href="respaldos.php" class="menu-card">
                <i class="fas fa-database"></i>
                <h3>Respaldos</h3>
                <p>Gestionar respaldos de la base de datos</p>
            </a>
            <a href="notificaciones.php" class="menu-card">
                <i class="fas fa-bell"></i>
                <h3>Notificaciones</h3>
                <p>Enviar notificaciones a usuarios</p>
            </a>
        </div>

        <!-- Últimos Accesos -->
        <div class="stat-card">
            <h3 style="margin-bottom: 16px;">📋 Últimos Accesos al Sistema</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <th style="text-align: left; padding: 8px;">Usuario</th>
                        <th style="text-align: left; padding: 8px;">Último Acceso</th>
                        <th style="text-align: left; padding: 8px;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['ultimos_accesos'] as $acceso): ?>
                    <tr>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($acceso['nombre']); ?></td>
                        <td style="padding: 8px;"><?php echo date('d/m/Y H:i', strtotime($acceso['ultimo_acceso'])); ?></td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($acceso['ultimo_ip']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>