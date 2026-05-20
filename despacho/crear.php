<?php
session_start();
require_once '../includes/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_nombre = $_SESSION['user_nombre'] ?? 'Operador';

try {
    $sql = "SELECT id, nombre FROM destinos WHERE activo = 1 ORDER BY nombre";
    $stmt = $pdo->query($sql);
    $destinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$total_destinos = count($destinos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim · Registrar Despacho</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: #ffffff;
            --panel-2: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #003B5C;
            --primary-2: #1d4ed8;
            --accent: #00D4AA;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
        }

        body.dark-mode {
            --bg: #0a0f1c;
            --panel: #111827;
            --panel-2: #0f172a;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --primary: #0f2740;
            --primary-2: #3b82f6;
            --accent: #22c55e;
            --border: #1f2937;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            color: var(--text);
            transition: background 0.25s ease, color 0.25s ease;
        }

        .corp-header {
            background: linear-gradient(135deg, #003B5C, #0f2740);
            color: white;
            padding: 14px 26px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid var(--accent);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0,59,92,0.2);
            gap: 16px;
            flex-wrap: wrap;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .logo-principal {
            height: 46px;
            width: 46px;
            object-fit: cover;
            border-radius: 14px;
            background: white;
            padding: 4px;
            transition: 0.3s;
            flex-shrink: 0;
        }

        .logo-principal:hover {
            transform: scale(1.03);
        }

        .empresa-info h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 1px;
            line-height: 1.2;
            color: white;
        }

        .sistema-nombre {
            font-size: 11px;
            color: var(--accent);
            letter-spacing: 2px;
            font-weight: 600;
            text-transform: uppercase;
            display: block;
            margin-top: 4px;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .user-badge {
            background: rgba(0, 212, 170, 0.15);
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(0,212,170,0.3);
            backdrop-filter: blur(5px);
        }

        .user-badge i {
            color: var(--accent);
            font-size: 16px;
        }

        .user-name-header {
            font-weight: 600;
            color: white;
        }

        .header-btn,
        .theme-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.15);
            padding: 10px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 13px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .header-btn:hover,
        .theme-btn:hover {
            background: var(--accent);
            color: #003B5C;
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .main-container {
            max-width: 1100px;
            margin: 28px auto;
            padding: 0 24px;
        }

        .page-header {
            background: var(--panel);
            border-radius: 20px;
            padding: 24px 26px;
            margin-bottom: 22px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .page-title h2 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .page-title h2 i {
            color: var(--accent);
            margin-right: 10px;
        }

        .page-subtitle {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 14px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 14px;
            flex-wrap: wrap;
        }

        .breadcrumb a {
            color: var(--accent);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: 0.3s;
        }

        .breadcrumb a:hover {
            color: var(--primary-2);
        }

        .breadcrumb i {
            font-size: 12px;
        }

        .breadcrumb .separator {
            color: #94a3b8;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .summary-card {
            background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 18px;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary-2), var(--accent));
        }

        .summary-label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 800;
            color: var(--text);
            line-height: 1.1;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .summary-text {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: var(--text);
            padding: 18px 20px;
            border-radius: 16px;
            margin-bottom: 22px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            font-size: 14px;
            line-height: 1.6;
            border-left: 5px solid var(--warning);
            box-shadow: var(--shadow);
        }

        .alert-warning i {
            color: var(--warning);
            font-size: 20px;
            margin-top: 2px;
        }

        .alert-warning a {
            color: var(--primary-2);
            font-weight: 700;
            text-decoration: none;
        }

        .alert-warning a:hover {
            text-decoration: underline;
        }

        .form-card {
            background: var(--panel);
            border-radius: 24px;
            padding: 34px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .form-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .form-top h3 {
            font-size: 20px;
            color: var(--text);
            line-height: 1.2;
        }

        .form-top p {
            color: var(--muted);
            font-size: 14px;
            margin-top: 6px;
            line-height: 1.5;
        }

        .form-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.12);
            color: var(--success);
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .section-title {
            margin: 30px 0 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border);
            color: var(--text);
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .section-title i {
            color: var(--accent);
            margin-right: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 2px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: var(--text);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        label .required {
            color: #ef4444;
            background: #fee2e2;
            padding: 2px 8px;
            border-radius: 40px;
            font-size: 11px;
            margin-left: 10px;
            text-transform: none;
        }

        body.dark-mode label .required {
            background: rgba(239, 68, 68, 0.16);
            color: #fca5a5;
        }

        label i {
            color: var(--accent);
            margin-right: 8px;
            width: 18px;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: #94a3b8;
            font-size: 16px;
            transition: color 0.3s;
            pointer-events: none;
        }

        .input-icon.right {
            left: auto;
            right: 15px;
            color: var(--accent);
        }

        input[type="date"],
        input[type="time"],
        input[type="number"],
        input[type="text"],
        select {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 2px solid var(--border);
            border-radius: 14px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            background: var(--panel);
            color: var(--text);
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 212, 170, 0.1);
        }

        input:focus ~ .input-icon,
        select:focus ~ .input-icon {
            color: var(--accent);
        }

        .help-text {
            margin-top: 8px;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 5px;
            line-height: 1.45;
        }

        .help-text i {
            color: var(--accent);
            font-size: 12px;
        }

        .info-card {
            background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            box-shadow: var(--shadow);
        }

        .info-icon {
            width: 44px;
            height: 44px;
            background: rgba(0, 212, 170, 0.14);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 18px;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
            min-width: 220px;
        }

        .info-title {
            font-weight: 700;
            color: var(--text);
            margin-bottom: 5px;
            font-size: 15px;
        }

        .info-text {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .stats-mini {
            display: flex;
            gap: 16px;
            background: var(--panel);
            padding: 12px 16px;
            border-radius: 999px;
            border: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .stat-mini-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text);
        }

        .stat-mini-item i {
            color: var(--accent);
        }

        .form-actions {
            display: flex;
            gap: 14px;
            margin-top: 34px;
            padding-top: 24px;
            border-top: 2px solid var(--border);
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 24px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
            min-width: 220px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #003B5C, #1d4ed8);
            color: white;
            box-shadow: 0 8px 20px rgba(0,59,92,0.18);
            border: none;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(0,59,92,0.24);
        }

        .btn-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .btn-primary.loading {
            opacity: 0.85;
            pointer-events: none;
        }

        .btn-secondary {
            background: var(--panel);
            color: var(--text);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.06);
            transform: translateY(-2px);
        }

        .footer-corp {
            margin-top: 40px;
            padding: 25px 0;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--muted);
            font-size: 12px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .footer-links {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--muted);
            text-decoration: none;
            transition: 0.3s;
        }

        .footer-links a:hover {
            color: var(--accent);
        }

        input.error, select.error {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 3000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }

        .toast {
            min-width: 320px;
            max-width: 380px;
            background: var(--panel);
            color: var(--text);
            border: 1px solid var(--border);
            border-left: 5px solid var(--primary-2);
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transform: translateX(120%);
            opacity: 0;
            pointer-events: auto;
            transition: transform 0.35s ease, opacity 0.35s ease;
            backdrop-filter: blur(10px);
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.hide {
            transform: translateX(120%);
            opacity: 0;
        }

        .toast-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 16px;
            flex-shrink: 0;
            background: rgba(29, 78, 216, 0.10);
            color: var(--primary-2);
        }

        .toast-content {
            flex: 1;
            min-width: 0;
        }

        .toast-title {
            font-size: 14px;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 3px;
        }

        .toast-message {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5;
        }

        .toast-close {
            background: transparent;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 14px;
            padding: 2px;
            flex-shrink: 0;
        }

        .toast.success {
            border-left-color: var(--success);
        }

        .toast.success .toast-icon {
            background: rgba(16, 185, 129, 0.12);
            color: var(--success);
        }

        .toast.warning {
            border-left-color: var(--warning);
        }

        .toast.warning .toast-icon {
            background: rgba(245, 158, 11, 0.12);
            color: var(--warning);
        }

        .toast.danger {
            border-left-color: var(--danger);
        }

        .toast.danger .toast-icon {
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
        }

        .toast.info {
            border-left-color: var(--primary-2);
        }

        .toast.info .toast-icon {
            background: rgba(29, 78, 216, 0.10);
            color: var(--primary-2);
        }

        @media (max-width: 900px) {
            .summary-grid,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .corp-header {
                padding: 12px 16px;
                align-items: stretch;
            }

            .logo-area,
            .user-area {
                width: 100%;
                justify-content: space-between;
            }

            .main-container {
                padding: 0 14px;
                margin: 18px auto;
            }

            .page-header,
            .form-card {
                padding: 22px;
            }

            .page-title h2 {
                font-size: 24px;
            }

            .info-card {
                align-items: flex-start;
            }

            .stats-mini {
                width: 100%;
                border-radius: 16px;
            }

            .toast-container {
                top: 90px;
                right: 12px;
                left: 12px;
            }

            .toast {
                min-width: 0;
                max-width: 100%;
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            .logo-principal {
                height: 40px;
                width: 40px;
            }

            .empresa-info h1 {
                font-size: 17px;
            }

            .sistema-nombre {
                font-size: 9px;
                letter-spacing: 1.5px;
            }

            .user-area {
                flex-direction: column;
                align-items: stretch;
            }

            .user-badge,
            .header-btn,
            .theme-btn {
                justify-content: center;
                width: 100%;
            }

            .page-header,
            .form-card,
            .info-card {
                padding: 18px;
            }

            .page-title h2 {
                font-size: 22px;
            }

            .summary-value {
                font-size: 22px;
            }

            .stats-mini {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
                border-radius: 14px;
            }

            .btn {
                min-width: 100%;
            }

            .form-actions {
                flex-direction: column;
            }

            input[type="date"],
            input[type="time"],
            input[type="number"],
            input[type="text"],
            select {
                padding: 13px 14px 13px 42px;
                font-size: 14px;
            }

            .input-icon {
                left: 13px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header class="corp-header">
        <div class="logo-area">
            <img src="https://cdn.brandfetch.io/idZ5TRl0IY/w/1757/h/1757/theme/dark/icon.jpeg" alt="Holcim" class="logo-principal">
            <div class="empresa-info">
                <h1>HOLCIM</h1>
                <span class="sistema-nombre">NUEVO DESPACHO</span>
            </div>
        </div>

        <div class="user-area">
            <div class="user-badge">
                <i class="fas fa-user-circle"></i>
                <span class="user-name-header"><?php echo htmlspecialchars($user_nombre); ?></span>
            </div>

            <button class="theme-btn" id="themeToggle" type="button">
                <i class="fas fa-moon"></i>
                <span id="themeText">Modo oscuro</span>
            </button>

            <a href="listar.php" class="header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </header>

    <main class="main-container">
        <div class="page-header">
            <div class="page-title">
                <h2><i class="fas fa-truck"></i>Registrar Despacho</h2>
                <div class="page-subtitle">
                    Captura un nuevo despacho con sus datos operativos, responsables y unidad asignada.
                </div>
            </div>

            <div class="breadcrumb">
                <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <a href="listar.php"><i class="fas fa-clipboard-list"></i> Despachos</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <span><i class="fas fa-plus"></i> Nuevo Despacho</span>
            </div>
        </div>

        <section class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Usuario activo</div>
                <div class="summary-value"><?php echo htmlspecialchars($user_nombre); ?></div>
                <div class="summary-text">Responsable actual de la captura del despacho.</div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Destinos activos</div>
                <div class="summary-value"><?php echo $total_destinos; ?></div>
                <div class="summary-text">Destinos disponibles para asignar al despacho.</div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Fecha del sistema</div>
                <div class="summary-value"><?php echo date('d/m/Y'); ?></div>
                <div class="summary-text">Solo se permiten registros con fecha actual o anterior.</div>
            </div>
        </section>

        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-truck"></i>
            </div>
            <div class="info-content">
                <div class="info-title">Registro de despacho</div>
                <div class="info-text">Completa los campos requeridos para registrar una salida de material a destino.</div>
            </div>
            <div class="stats-mini">
                <div class="stat-mini-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo $total_destinos; ?> destinos</span>
                </div>
                <div class="stat-mini-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?php echo date('d/m/Y'); ?></span>
                </div>
            </div>
        </div>

        <?php if ($total_destinos === 0): ?>
            <div class="alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    No hay destinos registrados. Primero debes
                    <a href="../destinos/crear.php">crear al menos un destino</a>
                    antes de capturar un despacho.
                </div>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <div class="form-top">
                <div>
                    <h3>Captura del despacho</h3>
                    <p>Ingresa la información logística, el material, el destino y las personas responsables.</p>
                </div>

                <div class="form-status">
                    <i class="fas fa-circle-check"></i>
                    <?php echo $total_destinos > 0 ? 'Formulario activo' : 'Pendiente de destinos'; ?>
                </div>
            </div>

            <form action="guardar.php" method="POST" id="despachoForm" novalidate>
                <div class="section-title">
                    <i class="fas fa-clipboard-list"></i> Datos del despacho
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="fecha">
                            <i class="fas fa-calendar-alt"></i> Fecha de Despacho
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="date"
                                id="fecha"
                                name="fecha"
                                value="<?php echo date('Y-m-d'); ?>"
                                required
                                max="<?php echo date('Y-m-d'); ?>"
                            >
                            <i class="fas fa-calendar input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="hora">
                            <i class="fas fa-clock"></i> Hora de Despacho
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="time"
                                id="hora"
                                name="hora"
                                value="<?php echo date('H:i'); ?>"
                                required
                            >
                            <i class="fas fa-clock input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="volumen">
                            <i class="fas fa-chart-bar"></i> Volumen Despachado (m³)
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="number"
                                id="volumen"
                                name="volumen"
                                step="0.01"
                                min="0.01"
                                required
                                placeholder="0.00"
                                autocomplete="off"
                            >
                            <i class="fas fa-weight-hanging input-icon"></i>
                            <i class="fas fa-cube input-icon right"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Ingresa el volumen en metros cúbicos (m³).
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="destino_id">
                            <i class="fas fa-map-marker-alt"></i> Destino
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <select id="destino_id" name="destino_id" required <?php echo $total_destinos === 0 ? 'disabled' : ''; ?>>
                                <option value="" disabled selected>Seleccione un destino...</option>
                                <?php foreach ($destinos as $destino): ?>
                                    <option value="<?php echo (int) $destino['id']; ?>">
                                        <?php echo htmlspecialchars($destino['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-map-pin input-icon"></i>
                        </div>
                        <?php if ($total_destinos > 0): ?>
                            <div class="help-text">
                                <i class="fas fa-info-circle"></i>
                                <?php echo $total_destinos; ?> destinos activos disponibles.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full">
                        <label for="material">
                            <i class="fas fa-cubes"></i> Material
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="material"
                                name="material"
                                placeholder="Ej: Cemento Portland"
                                maxlength="100"
                                required
                            >
                            <i class="fas fa-cube input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Especifica el tipo de material despachado.
                        </div>
                    </div>
                </div>

                <div class="section-title">
                    <i class="fas fa-users"></i> Responsables
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="operador_carga">
                            <i class="fas fa-user-cog"></i> Operador de carga
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="operador_carga"
                                name="operador_carga"
                                placeholder="Ej: Carlos López"
                                maxlength="100"
                                required
                            >
                            <i class="fas fa-user input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Persona que cargó o llenó la unidad.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="chofer">
                            <i class="fas fa-id-card"></i> Chofer
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="chofer"
                                name="chofer"
                                placeholder="Ej: Juan Pérez"
                                maxlength="100"
                                required
                            >
                            <i class="fas fa-id-card input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Nombre del conductor asignado.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="entregado_por">
                            <i class="fas fa-truck-loading"></i> Entregado por
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="entregado_por"
                                name="entregado_por"
                                placeholder="Ej: Juan Pérez"
                                maxlength="100"
                                required
                            >
                            <i class="fas fa-user-check input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Persona responsable de realizar la entrega.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="recibido_por">
                            <i class="fas fa-handshake"></i> Recibido por
                            <span class="required">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="recibido_por"
                                name="recibido_por"
                                placeholder="Ej: Ing. Martínez"
                                maxlength="100"
                                required
                            >
                            <i class="fas fa-user-tag input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Persona que recibe el material en destino.
                        </div>
                    </div>
                </div>

                <div class="section-title">
                    <i class="fas fa-truck-moving"></i> Unidad de transporte
                </div>

                <div class="form-grid">
                    <div class="form-group full">
                        <label for="unidad">
                            <i class="fas fa-truck"></i> Unidad (Placa / Código)
                        </label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="unidad"
                                name="unidad"
                                placeholder="Ej: ABC-123 / UN-01"
                                maxlength="20"
                            >
                            <i class="fas fa-truck-moving input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Campo opcional para identificar la unidad de transporte.
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn" <?php echo $total_destinos === 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-save"></i>
                        <span id="submitText">Guardar Despacho</span>
                    </button>

                    <a href="listar.php" class="btn btn-secondary" id="cancelBtn">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        <footer class="footer-corp">
            <div>© 2026 Holcim México · Sistema de Gestión de Cemento</div>
            <div class="footer-links">
                <a href="#"><i class="fas fa-question-circle"></i> Ayuda</a>
                <a href="#"><i class="fas fa-truck"></i> Despachos</a>
                <a href="#">v3.0</a>
            </div>
        </footer>
    </main>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        const body = document.body;
        const themeToggle = document.getElementById('themeToggle');
        const form = document.getElementById('despachoForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const cancelBtn = document.getElementById('cancelBtn');
        const toastContainer = document.getElementById('toastContainer');

        const fecha = document.getElementById('fecha');
        const hora = document.getElementById('hora');
        const volumen = document.getElementById('volumen');
        const destino = document.getElementById('destino_id');
        const material = document.getElementById('material');
        const operadorCarga = document.getElementById('operador_carga');
        const chofer = document.getElementById('chofer');
        const entregadoPor = document.getElementById('entregado_por');
        const recibidoPor = document.getElementById('recibido_por');

        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
        } else if (savedTheme === 'light') {
            body.classList.remove('dark-mode');
        } else if (prefersDark) {
            body.classList.add('dark-mode');
        }

        function updateThemeUI() {
            const isDark = body.classList.contains('dark-mode');
            if (themeToggle) {
                themeToggle.innerHTML = isDark
                    ? '<i class="fas fa-sun"></i><span id="themeText">Modo claro</span>'
                    : '<i class="fas fa-moon"></i><span id="themeText">Modo oscuro</span>';
            }
        }

        function getToastIcon(type) {
            switch (type) {
                case 'success':
                    return 'fas fa-circle-check';
                case 'warning':
                    return 'fas fa-triangle-exclamation';
                case 'danger':
                    return 'fas fa-circle-xmark';
                default:
                    return 'fas fa-circle-info';
            }
        }

        function showToast(title, message = '', type = 'info', duration = 3500) {
            if (!toastContainer) return;

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="${getToastIcon(type)}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" aria-label="Cerrar notificación">
                    <i class="fas fa-xmark"></i>
                </button>
            `;

            toastContainer.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.add('show');
            });

            const closeBtn = toast.querySelector('.toast-close');

            function removeToast() {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 350);
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', removeToast);
            }

            setTimeout(removeToast, duration);
        }

        function toggleTheme() {
            body.classList.toggle('dark-mode');
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeUI();

            showToast(
                isDark ? 'Modo oscuro activado' : 'Modo claro activado',
                isDark ? 'La vista de despacho cambió a tema oscuro.' : 'La vista de despacho cambió a tema claro.',
                'info',
                2200
            );
        }

        function markError(field, hasError) {
            if (!field) return;
            if (hasError) {
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        }

        function validateField(field) {
            if (!field || field.disabled) return true;

            const value = field.value.trim();
            const isRequired = field.hasAttribute('required');

            if (isRequired && value === '') {
                markError(field, true);
                return false;
            }

            if (field === volumen && value !== '' && parseFloat(value) <= 0) {
                markError(field, true);
                return false;
            }

            markError(field, false);
            return true;
        }

        function validateFutureDate() {
            if (!fecha.value) return true;

            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            const fechaSeleccionada = new Date(fecha.value + 'T00:00:00');
            const valida = fechaSeleccionada <= hoy;

            markError(fecha, !valida);
            return valida;
        }

        updateThemeUI();

        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        [fecha, hora, volumen, destino, material, operadorCarga, chofer, entregadoPor, recibidoPor].forEach((field) => {
            if (!field) return;

            field.addEventListener('input', () => validateField(field));
            field.addEventListener('change', () => validateField(field));
        });

        volumen.addEventListener('blur', function() {
            if (this.value && !isNaN(parseFloat(this.value))) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });

        cancelBtn.addEventListener('click', function(e) {
            const hayDatos =
                (fecha.value && fecha.value !== '<?php echo date('Y-m-d'); ?>') ||
                (hora.value && hora.value !== '<?php echo date('H:i'); ?>') ||
                (volumen.value !== '') ||
                (destino && destino.value !== '') ||
                (material.value.trim() !== '') ||
                (operadorCarga.value.trim() !== '') ||
                (chofer.value.trim() !== '') ||
                (entregadoPor.value.trim() !== '') ||
                (recibidoPor.value.trim() !== '') ||
                (document.getElementById('unidad').value.trim() !== '');

            if (hayDatos) {
                const confirmar = confirm('¿Estás seguro de cancelar? Los datos no guardados se perderán.');
                if (!confirmar) {
                    e.preventDefault();
                }
            }
        });

        form.addEventListener('submit', function(e) {
            let valid = true;

            [fecha, hora, volumen, destino, material, operadorCarga, chofer, entregadoPor, recibidoPor].forEach((field) => {
                if (!validateField(field)) {
                    valid = false;
                }
            });

            if (!validateFutureDate()) {
                valid = false;
                showToast('Fecha inválida', 'No puedes registrar un despacho con fecha futura.', 'warning', 3200);
            }

            if (!valid) {
                e.preventDefault();
                showToast(
                    'Formulario incompleto',
                    'Revisa los campos obligatorios y vuelve a intentarlo.',
                    'danger',
                    3200
                );
                return;
            }

            if (submitBtn.disabled) {
                e.preventDefault();
                showToast(
                    'No disponible',
                    'Debes tener destinos activos antes de guardar un despacho.',
                    'warning',
                    3200
                );
                return;
            }

            submitBtn.classList.add('loading');
            submitText.textContent = 'Guardando...';
            submitBtn.querySelector('i').className = 'fas fa-spinner fa-spin';

            showToast(
                'Validación correcta',
                'El despacho se está enviando al sistema.',
                'success',
                2200
            );
        });

        window.addEventListener('load', () => {
            <?php if ($total_destinos === 0): ?>
            setTimeout(() => {
                showToast(
                    'Sin destinos activos',
                    'Primero debes registrar un destino para habilitar esta captura.',
                    'warning',
                    4000
                );
            }, 500);
            <?php else: ?>
            setTimeout(() => {
                showToast(
                    'Vista lista',
                    'Ya puedes registrar un nuevo despacho.',
                    'success',
                    2400
                );
            }, 500);
            <?php endif; ?>
        });
    </script>
</body>
</html>