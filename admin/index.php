<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

requiereAdmin();

$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$logs_hoy = $pdo->query("SELECT COUNT(*) FROM logs_actividad WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$usuarios_activos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE DATE(ultimo_acceso) = CURDATE()")->fetchColumn();
$total_roles = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();

$usuarios_por_rol = $pdo->query("SELECT r.nombre as rol, COUNT(u.id) as total FROM roles r LEFT JOIN usuarios u ON r.id = u.rol_id GROUP BY r.id")->fetchAll();
$ultimos_accesos = $pdo->query("SELECT u.nombre, u.ultimo_acceso, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.ultimo_acceso IS NOT NULL ORDER BY u.ultimo_acceso DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Dashboard Admin | Holcim</title>
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
        
        /* Sidebar */
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
        
        /* Botón móvil */
        .mobile-menu-btn { position: fixed; bottom: var(--spacing-xl); right: var(--spacing-xl); width: 56px; height: 56px; background: var(--secondary); color: white; border: none; border-radius: 50%; font-size: 24px; cursor: pointer; box-shadow: var(--shadow-lg); z-index: 1100; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .mobile-menu-btn:active { transform: scale(0.95); }
        .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); opacity: 0; visibility: hidden; transition: all 0.2s; z-index: 999; }
        .sidebar-overlay.open { opacity: 1; visibility: visible; }
        
        /* Main Content */
        .main-content { flex: 1; min-height: 100vh; padding: var(--spacing-lg); padding-bottom: 90px; }
        .page-header { display: flex; flex-direction: column; gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl); }
        .page-title h1 { font-size: 24px; font-weight: 800; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .page-title p { font-size: 13px; color: var(--gray-500); margin-top: 4px; }
        .user-info { display: flex; align-items: center; gap: var(--spacing-md); background: white; padding: var(--spacing-sm) var(--spacing-lg) var(--spacing-sm) var(--spacing-sm); border-radius: 60px; box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200); align-self: flex-start; }
        .user-avatar { width: 44px; height: 44px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; }
        .user-details strong { font-size: 14px; font-weight: 600; display: block; }
        .user-details span { font-size: 11px; color: var(--gray-500); }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-md); margin-bottom: var(--spacing-2xl); }
        .stat-card { background: white; border-radius: var(--radius-xl); padding: var(--spacing-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200); text-align: center; transition: all 0.2s; }
        .stat-card:active { transform: scale(0.98); }
        .stat-value { font-size: 28px; font-weight: 800; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .stat-label { font-size: 12px; color: var(--gray-500); margin-top: 4px; }
        
        /* Cards */
        .card { background: white; border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl); border: 1px solid var(--gray-200); overflow: hidden; }
        .card-header { padding: var(--spacing-lg); border-bottom: 1px solid var(--gray-200); display: flex; flex-direction: column; gap: var(--spacing-md); background: var(--gray-50); }
        .card-header h2 { font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: var(--spacing-sm); }
        .card-header h2 i { color: var(--secondary); }
        .card-body { padding: var(--spacing-lg); }
        
        /* Tablas */
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 400px; }
        .data-table th { text-align: left; padding: var(--spacing-md) var(--spacing-sm); background: var(--gray-50); color: var(--gray-600); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--gray-200); }
        .data-table td { padding: var(--spacing-md) var(--spacing-sm); border-bottom: 1px solid var(--gray-200); }
        
        /* Badges */
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 40px; font-size: 10px; font-weight: 600; }
        .badge-admin { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; }
        .badge-supervisor { background: var(--secondary); color: white; }
        .badge-operador { background: var(--gray-200); color: var(--gray-600); }
        
        /* Botones */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: var(--spacing-sm); padding: 10px 18px; border-radius: var(--radius-lg); font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; min-height: 44px; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; }
        .btn-primary:active { transform: scale(0.97); }
        .btn-outline { background: transparent; border: 1px solid var(--gray-300); color: var(--gray-700); }
        .btn-sm { padding: 6px 12px; font-size: 11px; min-height: 32px; }
        
        /* Accesos rápidos */
        .quick-actions { display: flex; flex-direction: column; gap: var(--spacing-md); }
        .quick-action-card { display: flex; align-items: center; gap: var(--spacing-md); padding: var(--spacing-lg); background: var(--gray-50); border-radius: var(--radius-lg); text-decoration: none; color: var(--gray-700); border: 1px solid var(--gray-200); min-height: 70px; transition: all 0.2s; }
        .quick-action-card:active { transform: scale(0.98); background: white; }
        .quick-action-card i { font-size: 28px; color: var(--secondary); }
        .quick-action-card span { font-weight: 600; font-size: 14px; }
        
        /* Responsive */
        @media (min-width: 768px) {
            .mobile-menu-btn { display: none; }
            .sidebar { transform: translateX(0); position: sticky; top: 0; }
            .main-content { margin-left: 280px; padding: var(--spacing-2xl) var(--spacing-3xl); padding-bottom: var(--spacing-2xl); }
            .page-header { flex-direction: row; justify-content: space-between; align-items: center; }
            .user-info { align-self: auto; }
            .stats-grid { grid-template-columns: repeat(4, 1fr); gap: var(--spacing-xl); }
            .stat-value { font-size: 32px; }
            .card-header { flex-direction: row; justify-content: space-between; align-items: center; }
            .quick-actions { flex-direction: row; flex-wrap: wrap; }
            .quick-action-card { flex: 1; min-width: 180px; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: flex-start; }
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
                <a href="index.php" class="nav-item active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="usuarios.php" class="nav-item"><i class="fas fa-users"></i><span>Usuarios</span></a>
                <a href="roles.php" class="nav-item"><i class="fas fa-user-tag"></i><span>Roles</span></a>
                <a href="logs.php" class="nav-item"><i class="fas fa-history"></i><span>Logs</span></a>
                <a href="configuracion.php" class="nav-item"><i class="fas fa-cog"></i><span>Configuración</span></a>
                <div class="nav-divider"></div>
                <a href="../index.php" class="nav-item"><i class="fas fa-arrow-left"></i><span>Volver</span></a>
                <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Panel de Administración</h1>
                    <p>Bienvenido de vuelta, aquí está el resumen de tu sistema</p>
                </div>
                <div class="user-info">
                    <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
                    <div class="user-details">
                        <strong><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></strong>
                        <span>Administrador</span>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-value"><?php echo $total_usuarios; ?></div><div class="stat-label">Usuarios</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $usuarios_activos; ?></div><div class="stat-label">Activos Hoy</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $logs_hoy; ?></div><div class="stat-label">Acciones Hoy</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $total_roles; ?></div><div class="stat-label">Roles</div></div>
            </div>

            <div class="card">
                <div class="card-header"><h2><i class="fas fa-bolt"></i> Accesos Rápidos</h2></div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="usuarios.php" class="quick-action-card"><i class="fas fa-user-plus"></i><span>Nuevo Usuario</span></a>
                        <a href="roles.php" class="quick-action-card"><i class="fas fa-key"></i><span>Permisos</span></a>
                        <a href="logs.php" class="quick-action-card"><i class="fas fa-file-alt"></i><span>Auditoría</span></a>
                        <a href="configuracion.php" class="quick-action-card"><i class="fas fa-sliders-h"></i><span>Ajustes</span></a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2><i class="fas fa-clock"></i> Últimos Accesos</h2></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Usuario</th><th>Rol</th><th>Fecha</th></tr></thead>
                            <tbody>
                                <?php foreach($ultimos_accesos as $a): ?>
                                <tr><td><strong><?php echo htmlspecialchars($a['nombre']); ?></strong></td><td><span class="badge badge-<?php echo $a['rol']; ?>"><?php echo $a['rol']; ?></span></td><td><?php echo date('d/m/Y H:i', strtotime($a['ultimo_acceso'])); ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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