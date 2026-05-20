<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_nombre = $_SESSION['user_nombre'] ?? 'Operador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim · Registrar Producción</title>
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
        }

        .summary-text {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .form-card {
            background: var(--panel);
            border-radius: 24px;
            padding: 34px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 4px;
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
        input[type="number"],
        input[type="text"],
        select,
        textarea {
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

        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.8;
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }

        textarea {
            resize: vertical;
            min-height: 130px;
            line-height: 1.55;
        }

        input[type="date"]:focus,
        input[type="number"]:focus,
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 212, 170, 0.12);
        }

        input[type="date"]:focus ~ .input-icon,
        input[type="number"]:focus ~ .input-icon,
        input[type="text"]:focus ~ .input-icon,
        select:focus ~ .input-icon,
        textarea:focus ~ .input-icon {
            color: var(--accent);
        }

        .help-text {
            margin-top: 8px;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
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
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            box-shadow: var(--shadow);
        }

        .info-icon {
            width: 44px;
            height: 44px;
            background: rgba(0, 212, 170, 0.12);
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

        .info-date {
            color: var(--accent);
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(0, 212, 170, 0.08);
        }

        .char-counter {
            margin-top: 8px;
            text-align: right;
            font-size: 12px;
            color: var(--muted);
        }

        .char-counter.warning {
            color: var(--warning);
        }

        .char-counter.danger {
            color: var(--danger);
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
            border: none;
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
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(0,59,92,0.24);
        }

        .btn-primary.loading {
            opacity: 0.8;
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

        input:invalid,
        select:invalid {
            border-color: rgba(239, 68, 68, 0.3);
        }

        input.error,
        select.error,
        textarea.error {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        [data-tooltip] {
            position: relative;
            cursor: help;
        }

        [data-tooltip]:before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 11px;
            white-space: nowrap;
            display: none;
            z-index: 10;
        }

        [data-tooltip]:hover:before {
            display: block;
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
            .ai-layout,
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
            .corp-header {
                gap: 12px;
            }

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

            .btn {
                min-width: 100%;
            }

            .form-actions {
                flex-direction: column;
            }

            input[type="date"],
            input[type="number"],
            input[type="text"],
            select,
            textarea {
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
                <span class="sistema-nombre">NUEVA PRODUCCIÓN</span>
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
                <h2><i class="fas fa-plus-circle"></i>Registrar Producción</h2>
                <div class="page-subtitle">
                    Captura un nuevo registro de producción con una interfaz más clara, moderna y optimizada para escritorio y móvil.
                </div>
            </div>

            <div class="breadcrumb">
                <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <a href="listar.php"><i class="fas fa-clipboard-list"></i> Producción</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <span><i class="fas fa-file-circle-plus"></i> Nuevo Registro</span>
            </div>
        </div>

        <section class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Usuario activo</div>
                <div class="summary-value"><?php echo htmlspecialchars($user_nombre); ?></div>
                <div class="summary-text">Responsable actual de la captura del registro.</div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Fecha del sistema</div>
                <div class="summary-value"><?php echo date('d/m/Y'); ?></div>
                <div class="summary-text">La producción solo puede registrarse hasta el día actual.</div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Estado del formulario</div>
                <div class="summary-value">Listo</div>
                <div class="summary-text">Completa los campos obligatorios para guardar.</div>
            </div>
        </section>

        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-content">
                <div class="info-title">Registro de producción diaria</div>
                <div class="info-text">
                    Completa todos los campos marcados con <span style="color:#ef4444;">obligatorio</span>. El sistema validará la fecha, el turno y el volumen antes de guardar.
                </div>
            </div>
            <div class="info-date">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('d/m/Y'); ?>
            </div>
        </div>

        <div class="form-card">
            <div class="form-top">
                <div>
                    <h3>Datos de producción</h3>
                    <p>Captura la información del turno, el volumen producido y cualquier observación operativa relevante.</p>
                </div>

                <div class="form-status">
                    <i class="fas fa-circle-check"></i>
                    Formulario activo
                </div>
            </div>

            <form action="guardar.php" method="POST" id="produccionForm" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fecha">
                            <i class="fas fa-calendar-alt"></i> Fecha de Producción
                            <span class="required" data-tooltip="Campo obligatorio">Obligatorio</span>
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
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Selecciona la fecha de producción. No se permiten fechas futuras.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="periodo">
                            <i class="fas fa-clock"></i> Periodo / Turno
                            <span class="required" data-tooltip="Campo obligatorio">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <select id="periodo" name="periodo" required>
                                <option value="" disabled selected>Seleccione un periodo...</option>
                                <option value="Turno Mañana">🌅 Turno Mañana (06:00 - 14:00)</option>
                                <option value="Turno Tarde">☀️ Turno Tarde (14:00 - 22:00)</option>
                                <option value="Turno Noche">🌙 Turno Noche (22:00 - 06:00)</option>
                                <option value="Día Completo">📅 Día Completo (24 hrs)</option>
                            </select>
                            <i class="fas fa-business-time input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Elige el turno al que corresponde este registro.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="destino_id">
                            <i class="fas fa-map-marker-alt"></i> Destino
                            <span class="required" data-tooltip="Campo obligatorio">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <select id="destino_id" name="destino_id" required>
                                <option value="" disabled selected>Seleccione un destino...</option>
                                <option value="1">Planta Central</option>
                                <option value="2">Planta Norte</option>
                                <option value="3">Planta Sur</option>
                                <option value="4">Planta Oriente</option>
                                <option value="5">Planta Poniente</option>
                            </select>
                            <i class="fas fa-location-dot input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Selecciona la planta o destino de la producción.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="unidad">
                            <i class="fas fa-industry"></i> Unidad
                            <span class="required" data-tooltip="Campo obligatorio">Obligatorio</span>
                        </label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="unidad"
                                name="unidad"
                                required
                                placeholder="Ej: Planta Central, Línea 1, Producción"
                                autocomplete="off"
                            >
                            <i class="fas fa-factory input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Especifica la unidad o línea de producción.
                        </div>
                    </div>

                    <div class="form-group full">
                        <label for="volumen">
                            <i class="fas fa-chart-column"></i> Volumen Producido (m³)
                            <span class="required" data-tooltip="Campo obligatorio">Obligatorio</span>
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
                            Ingresa el volumen en metros cúbicos. Se aceptan decimales.
                        </div>
                    </div>

                    <div class="form-group full">
                        <label for="observaciones">
                            <i class="fas fa-pen-to-square"></i> Observaciones
                        </label>
                        <div class="input-group">
                            <textarea
                                id="observaciones"
                                name="observaciones"
                                placeholder="Notas adicionales sobre la producción, incidencias, calidad, maquinaria o comentarios relevantes..."
                                maxlength="500"
                            ></textarea>
                            <i class="fas fa-pen input-icon"></i>
                        </div>
                        <div class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Campo opcional. Úsalo para registrar detalles importantes del turno.
                        </div>
                        <div class="char-counter" id="charCounter">0 / 500 caracteres</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i>
                        <span id="submitText">Guardar Registro</span>
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
                <a href="#"><i class="fas fa-file-alt"></i> Guía rápida</a>
                <a href="#">v3.0</a>
            </div>
        </footer>
    </main>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        const body = document.body;
        const themeToggle = document.getElementById('themeToggle');
        const themeText = document.getElementById('themeText');
        const form = document.getElementById('produccionForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const cancelBtn = document.getElementById('cancelBtn');
        const fecha = document.getElementById('fecha');
        const periodo = document.getElementById('periodo');
        const destinoId = document.getElementById('destino_id');
        const unidad = document.getElementById('unidad');
        const volumen = document.getElementById('volumen');
        const observaciones = document.getElementById('observaciones');
        const charCounter = document.getElementById('charCounter');
        const toastContainer = document.getElementById('toastContainer');

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
            if (themeToggle && themeText) {
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

                setTimeout(() => {
                    toast.remove();
                }, 350);
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
                isDark ? 'La vista se ajustó al tema oscuro.' : 'La vista se ajustó al tema claro.',
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

        function updateCharCounter() {
            const length = observaciones.value.length;
            charCounter.textContent = `${length} / 500 caracteres`;
            charCounter.classList.remove('warning', 'danger');

            if (length >= 400 && length < 480) {
                charCounter.classList.add('warning');
            }

            if (length >= 480) {
                charCounter.classList.add('danger');
            }
        }

        updateThemeUI();
        updateCharCounter();

        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        observaciones.addEventListener('input', updateCharCounter);

        volumen.addEventListener('blur', function() {
            if (this.value && !isNaN(parseFloat(this.value))) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });

        [fecha, periodo, destinoId, unidad, volumen, observaciones].forEach((field) => {
            if (!field) return;
            field.addEventListener('input', () => markError(field, false));
            field.addEventListener('change', () => markError(field, false));
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                const hayDatos =
                    (fecha.value && fecha.value !== '<?php echo date('Y-m-d'); ?>') ||
                    (periodo.value && periodo.value !== '') ||
                    (destinoId.value && destinoId.value !== '') ||
                    (unidad.value && unidad.value !== '') ||
                    (volumen.value && volumen.value !== '') ||
                    (observaciones.value.trim() !== '');

                if (hayDatos) {
                    const confirmar = confirm('¿Estás seguro de cancelar? Los datos no guardados se perderán.');
                    if (!confirmar) {
                        e.preventDefault();
                    }
                }
            });
        }

        form.addEventListener('submit', function(e) {
            let hasError = false;

            if (!fecha.value) {
                markError(fecha, true);
                hasError = true;
            }

            if (!periodo.value) {
                markError(periodo, true);
                hasError = true;
            }

            if (!destinoId.value) {
                markError(destinoId, true);
                hasError = true;
            }

            if (!unidad.value || unidad.value.trim() === '') {
                markError(unidad, true);
                hasError = true;
            }

            if (!volumen.value || parseFloat(volumen.value) <= 0) {
                markError(volumen, true);
                hasError = true;
            }

            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            const fechaSeleccionada = fecha.value ? new Date(fecha.value + 'T00:00:00') : null;

            if (fechaSeleccionada && fechaSeleccionada > hoy) {
                markError(fecha, true);
                hasError = true;
                showToast('Fecha inválida', 'No puedes registrar producción en una fecha futura.', 'warning', 3200);
                e.preventDefault();
                return;
            }

            if (hasError) {
                e.preventDefault();
                showToast(
                    'Formulario incompleto',
                    'Revisa los campos obligatorios antes de guardar.',
                    'danger',
                    3200
                );
                return;
            }

            submitBtn.classList.add('loading');
            submitText.textContent = 'Guardando...';
            const icon = submitBtn.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-spinner fa-spin';
            }

            showToast(
                'Validación correcta',
                'El registro se está enviando al sistema.',
                'success',
                2200
            );
        });

        window.addEventListener('load', () => {
            setTimeout(() => {
                showToast(
                    'Vista lista',
                    'Ya puedes registrar una nueva producción.',
                    'success',
                    2400
                );
            }, 500);
        });
    </script>
</body>
</html>