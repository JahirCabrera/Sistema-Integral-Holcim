<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

requiereAdmin();

$mensaje = '';
$error = '';

// Guardar configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $clave => $valor) {
        $stmt = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$clave, $valor, $valor]);
    }
    registrarLog('actualizar_configuracion', 'configuracion');
    $mensaje = "Configuración guardada correctamente";
}

// Obtener configuración actual
$config = [];
$stmt = $pdo->query("SELECT clave, valor FROM configuracion");
while ($row = $stmt->fetch()) {
    $config[$row['clave']] = $row['valor'];
}

// Valores por defecto
$config = array_merge([
    'sistema_nombre' => 'Holcim Gestión Integral',
    'sistema_version' => '4.0.0',
    'sistema_theme' => 'dark',
    'limite_intentos_login' => '5',
    'tiempo_sesion' => '480',
    'forzar_cambio_password' => '90',
    'bloqueo_ip' => '1',
    'email_soporte' => 'soporte@holcim.com',
    'notificar_login_fallido' => '1',
    'notificar_nuevo_usuario' => '1',
    'backup_auto' => '1',
    'backup_frecuencia' => 'daily',
    'backup_hora' => '02:00',
], $config);

// Información del sistema
$php_version = phpversion();
$mysql_version = $pdo->query("SELECT VERSION()")->fetchColumn();
$db_size = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_logs = $pdo->query("SELECT COUNT(*) FROM logs_actividad")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Configuración | Holcim Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #0f3d5e;
            --primary-dark: #0a2c44;
            --secondary: #00c896;
            --secondary-dark: #00a37a;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --success: #10b981;
            --danger: #ef4444;
            --info: #3b82f6;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-2xl: 20px;
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 12px;
            --spacing-lg: 16px;
            --spacing-xl: 20px;
            --spacing-2xl: 24px;
            --spacing-3xl: 32px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--gray-100); color: var(--gray-800); line-height: 1.5; }
        
        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 280px; background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; transform: translateX(-100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 1000; overflow-y: auto; }
        .sidebar.open { transform: translateX(0); }
        .sidebar-header { padding: var(--spacing-2xl) var(--spacing-xl); border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: var(--spacing-xl); }
        .sidebar-logo { display: flex; align-items: center; gap: var(--spacing-md); }
        .sidebar-logo i { font-size: 32px; color: var(--secondary); }
        .sidebar-logo h2 { font-size: 20px; font-weight: 700; }
        .sidebar-logo p { font-size: 10px; opacity: 0.7; margin-top: 2px; }
        .sidebar-nav { padding: 0 var(--spacing-lg) var(--spacing-xl); }
        .nav-item { display: flex; align-items: center; gap: var(--spacing-md); padding: var(--spacing-md) var(--spacing-lg); color: rgba(255,255,255,0.8); text-decoration: none; border-radius: var(--radius-lg); margin-bottom: 4px; transition: all 0.2s; }
        .nav-item i { width: 22px; font-size: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: var(--secondary); color: white; }
        .nav-divider { margin: var(--spacing-xl) 0 var(--spacing-md); padding-top: var(--spacing-lg); border-top: 1px solid rgba(255,255,255,0.08); }
        
        .mobile-menu-btn { position: fixed; bottom: var(--spacing-xl); right: var(--spacing-xl); width: 56px; height: 56px; background: var(--secondary); color: white; border: none; border-radius: 50%; font-size: 24px; cursor: pointer; box-shadow: var(--shadow-lg); z-index: 1100; display: flex; align-items: center; justify-content: center; }
        .mobile-menu-btn:active { transform: scale(0.95); }
        .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); opacity: 0; visibility: hidden; transition: all 0.2s; z-index: 999; }
        .sidebar-overlay.open { opacity: 1; visibility: visible; }
        
        .main-content { flex: 1; min-height: 100vh; padding: var(--spacing-lg); padding-bottom: 90px; }
        .page-header { display: flex; flex-direction: column; gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl); }
        .page-title h1 { font-size: 24px; font-weight: 800; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .page-title p { font-size: 13px; color: var(--gray-500); margin-top: 4px; }
        .user-info { display: flex; align-items: center; gap: var(--spacing-md); background: white; padding: var(--spacing-sm) var(--spacing-lg) var(--spacing-sm) var(--spacing-sm); border-radius: 60px; box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200); align-self: flex-start; }
        .user-avatar { width: 44px; height: 44px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; }
        .user-details strong { font-size: 14px; font-weight: 600; display: block; }
        .user-details span { font-size: 11px; color: var(--gray-500); }
        
        .config-grid { display: grid; grid-template-columns: 1fr; gap: var(--spacing-xl); margin-bottom: var(--spacing-2xl); }
        .config-card { background: white; border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200); overflow: hidden; }
        .config-card-header { padding: var(--spacing-lg); border-bottom: 1px solid var(--gray-200); background: var(--gray-50); }
        .config-card-header h2 { font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: var(--spacing-sm); }
        .config-card-header h2 i { color: var(--secondary); }
        .config-card-body { padding: var(--spacing-lg); }
        
        .form-group { margin-bottom: var(--spacing-lg); }
        .form-label { display: block; margin-bottom: var(--spacing-sm); font-weight: 600; font-size: 13px; color: var(--gray-700); }
        .form-control { width: 100%; padding: 12px 14px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-lg); font-size: 14px; transition: all 0.2s; }
        .form-control:focus { outline: none; border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(0, 200, 150, 0.1); }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E"); background-position: right 12px center; background-repeat: no-repeat; background-size: 20px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-md); margin-top: var(--spacing-md); }
        .info-item { background: var(--gray-50); padding: var(--spacing-md); border-radius: var(--radius-lg); border: 1px solid var(--gray-200); }
        .info-label { font-size: 11px; color: var(--gray-500); margin-bottom: var(--spacing-xs); text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 14px; font-weight: 600; color: var(--gray-800); }
        
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: var(--spacing-sm); padding: 12px 24px; border-radius: var(--radius-lg); font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; min-height: 48px; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; }
        .btn-primary:active { transform: scale(0.97); }
        .btn-success { background: var(--success); color: white; }
        
        .alert { padding: var(--spacing-md) var(--spacing-lg); border-radius: var(--radius-lg); margin-bottom: var(--spacing-lg); display: flex; align-items: center; gap: var(--spacing-md); font-size: 13px; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
        
        @media (min-width: 768px) {
            .mobile-menu-btn { display: none; }
            .sidebar { transform: translateX(0); position: sticky; top: 0; }
            .main-content { margin-left: 280px; padding: var(--spacing-2xl) var(--spacing-3xl); padding-bottom: var(--spacing-2xl); }
            .page-header { flex-direction: row; justify-content: space-between; align-items: center; }
            .config-grid { grid-template-columns: repeat(2, 1fr); }
            .info-grid { grid-template-columns: repeat(4, 1fr); }
        }
        @media (max-width: 480px) {
            .info-grid { grid-template-columns: 1fr; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="admin-layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo"><i class="fas fa-crown"></i><div><h2>Holcim</h2><p>Administración</p></div></div>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="usuarios.php" class="nav-item"><i class="fas fa-users"></i><span>Usuarios</span></a>
                <a href="roles.php" class="nav-item"><i class="fas fa-user-tag"></i><span>Roles</span></a>
                <a href="logs.php" class="nav-item"><i class="fas fa-history"></i><span>Logs</span></a>
                <a href="configuracion.php" class="nav-item active"><i class="fas fa-cog"></i><span>Configuración</span></a>
                <div class="nav-divider"></div>
                <a href="../index.php" class="nav-item"><i class="fas fa-arrow-left"></i><span>Volver</span></a>
                <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Configuración del Sistema</h1>
                    <p>Personaliza el comportamiento y ajustes generales de la plataforma</p>
                </div>
                <div class="user-info">
                    <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
                    <div class="user-details">
                        <strong><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></strong>
                        <span>Administrador</span>
                    </div>
                </div>
            </div>

            <?php if($mensaje): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="config-grid">
                    <!-- Configuración General -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h2><i class="fas fa-globe"></i> Configuración General</h2>
                        </div>
                        <div class="config-card-body">
                            <div class="form-group">
                                <label class="form-label">Nombre del Sistema</label>
                                <input type="text" name="sistema_nombre" class="form-control" value="<?php echo htmlspecialchars($config['sistema_nombre']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Versión</label>
                                <input type="text" name="sistema_version" class="form-control" value="<?php echo htmlspecialchars($config['sistema_version']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tema por defecto</label>
                                <select name="sistema_theme" class="form-control">
                                    <option value="dark" <?php echo $config['sistema_theme'] == 'dark' ? 'selected' : ''; ?>>Oscuro</option>
                                    <option value="light" <?php echo $config['sistema_theme'] == 'light' ? 'selected' : ''; ?>>Claro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Seguridad -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h2><i class="fas fa-shield-alt"></i> Seguridad</h2>
                        </div>
                        <div class="config-card-body">
                            <div class="form-group">
                                <label class="form-label">Intentos máximos de login</label>
                                <input type="number" name="limite_intentos_login" class="form-control" value="<?php echo $config['limite_intentos_login']; ?>" min="3" max="10">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tiempo de sesión (minutos)</label>
                                <input type="number" name="tiempo_sesion" class="form-control" value="<?php echo $config['tiempo_sesion']; ?>" min="30" max="1440">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Forzar cambio de contraseña (días)</label>
                                <input type="number" name="forzar_cambio_password" class="form-control" value="<?php echo $config['forzar_cambio_password']; ?>">
                                <small style="font-size: 11px; color: var(--gray-500);">0 = desactivado</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bloqueo por IP</label>
                                <select name="bloqueo_ip" class="form-control">
                                    <option value="1" <?php echo $config['bloqueo_ip'] == 1 ? 'selected' : ''; ?>>Activado</option>
                                    <option value="0" <?php echo $config['bloqueo_ip'] == 0 ? 'selected' : ''; ?>>Desactivado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Notificaciones -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h2><i class="fas fa-envelope"></i> Notificaciones</h2>
                        </div>
                        <div class="config-card-body">
                            <div class="form-group">
                                <label class="form-label">Email de soporte</label>
                                <input type="email" name="email_soporte" class="form-control" value="<?php echo htmlspecialchars($config['email_soporte']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Notificar login fallido</label>
                                <select name="notificar_login_fallido" class="form-control">
                                    <option value="1" <?php echo $config['notificar_login_fallido'] == 1 ? 'selected' : ''; ?>>Sí</option>
                                    <option value="0" <?php echo $config['notificar_login_fallido'] == 0 ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Notificar nuevo usuario</label>
                                <select name="notificar_nuevo_usuario" class="form-control">
                                    <option value="1" <?php echo $config['notificar_nuevo_usuario'] == 1 ? 'selected' : ''; ?>>Sí</option>
                                    <option value="0" <?php echo $config['notificar_nuevo_usuario'] == 0 ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Respaldos -->
                    <div class="config-card">
                        <div class="config-card-header">
                            <h2><i class="fas fa-database"></i> Respaldos Automáticos</h2>
                        </div>
                        <div class="config-card-body">
                            <div class="form-group">
                                <label class="form-label">Respaldos automáticos</label>
                                <select name="backup_auto" class="form-control">
                                    <option value="1" <?php echo $config['backup_auto'] == 1 ? 'selected' : ''; ?>>Activado</option>
                                    <option value="0" <?php echo $config['backup_auto'] == 0 ? 'selected' : ''; ?>>Desactivado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Frecuencia</label>
                                <select name="backup_frecuencia" class="form-control">
                                    <option value="daily" <?php echo $config['backup_frecuencia'] == 'daily' ? 'selected' : ''; ?>>Diario</option>
                                    <option value="weekly" <?php echo $config['backup_frecuencia'] == 'weekly' ? 'selected' : ''; ?>>Semanal</option>
                                    <option value="monthly" <?php echo $config['backup_frecuencia'] == 'monthly' ? 'selected' : ''; ?>>Mensual</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hora del respaldo</label>
                                <input type="time" name="backup_hora" class="form-control" value="<?php echo $config['backup_hora']; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="config-card" style="margin-bottom: var(--spacing-xl);">
                    <div class="config-card-header">
                        <h2><i class="fas fa-chart-simple"></i> Información del Sistema</h2>
                    </div>
                    <div class="config-card-body">
                        <div class="info-grid">
                            <div class="info-item"><div class="info-label">PHP Version</div><div class="info-value"><?php echo $php_version; ?></div></div>
                            <div class="info-item"><div class="info-label">MySQL Version</div><div class="info-value"><?php echo $mysql_version; ?></div></div>
                            <div class="info-item"><div class="info-label">Tamaño BD</div><div class="info-value"><?php echo $db_size; ?> MB</div></div>
                            <div class="info-item"><div class="info-label">Total Usuarios</div><div class="info-value"><?php echo $total_usuarios; ?></div></div>
                            <div class="info-item"><div class="info-label">Total Logs</div><div class="info-value"><?php echo $total_logs; ?></div></div>
                            <div class="info-item"><div class="info-label">Último respaldo</div><div class="info-value">Pendiente</div></div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Configuración</button>
                    <button type="button" class="btn btn-success" onclick="alert('Funcionalidad en desarrollo')"><i class="fas fa-database"></i> Realizar Respaldo Ahora</button>
                </div>
            </form>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const mobileBtn = document.getElementById('mobileMenuBtn');
        function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow = ''; }
        mobileBtn?.addEventListener('click', openSidebar);
        overlay?.addEventListener('click', closeSidebar);
        window.addEventListener('resize', () => { if(window.innerWidth >= 768) closeSidebar(); });
    </script>
</body>
</html>