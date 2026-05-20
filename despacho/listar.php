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
    $sql = "SELECT d.*, dest.nombre as destino_nombre 
            FROM despacho d 
            INNER JOIN destinos dest ON d.destino_id = dest.id 
            ORDER BY d.fecha DESC, d.hora DESC";
    $stmt = $pdo->query($sql);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$total_registros = count($registros);
$total_volumen = 0;
$volumen_hoy = 0;
$destinos_unicos = [];
$ultimo_registro = null;

foreach ($registros as $row) {
    $total_volumen += (float) $row['volumen'];

    if ($row['fecha'] === date('Y-m-d')) {
        $volumen_hoy += (float) $row['volumen'];
    }

    if (!in_array($row['destino_nombre'], $destinos_unicos, true)) {
        $destinos_unicos[] = $row['destino_nombre'];
    }

    if (!$ultimo_registro) {
        $ultimo_registro = $row;
    }
}

$promedio_volumen = $total_registros > 0 ? $total_volumen / $total_registros : 0;
sort($destinos_unicos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim · Despachos</title>
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
            max-width: 1450px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .page-title h2 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .page-title h2 i {
            color: var(--accent);
            margin-right: 10px;
        }

        .page-title p {
            color: var(--muted);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            line-height: 1.5;
        }

        .page-title p i {
            color: var(--accent);
        }

        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #003B5C, #1d4ed8);
            color: white;
            box-shadow: 0 4px 10px rgba(0,59,92,0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,59,92,0.3);
        }

        .btn-secondary {
            background: var(--panel);
            color: var(--text);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-2px);
        }

        .btn-action {
            padding: 9px 14px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            background: linear-gradient(135deg, #003B5C, #1d4ed8);
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            white-space: nowrap;
            justify-content: center;
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }

        .alert {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--text);
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: fadeIn 0.3s ease;
            box-shadow: var(--shadow);
        }

        .alert i {
            color: var(--success);
            font-size: 18px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 18px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary-2), var(--accent));
        }

        .stat-label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            line-height: 1.1;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .stat-text {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .filters-card {
            background: var(--panel);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 18px;
            margin-bottom: 22px;
        }

        .filters-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .filters-top h3 {
            font-size: 18px;
            color: var(--text);
        }

        .filters-top p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 4px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 1.3fr .9fr .9fr;
            gap: 14px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--muted);
        }

        .filter-input-wrap {
            position: relative;
        }

        .filter-input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            border: 1px solid var(--border);
            background: var(--panel-2);
            color: var(--text);
            border-radius: 14px;
            padding: 13px 14px 13px 42px;
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
        }

        .filter-input:focus,
        .filter-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 212, 170, 0.10);
        }

        .filter-select {
            appearance: none;
        }

        .table-card {
            background: var(--panel);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .table-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-header h3 {
            font-size: 18px;
            color: var(--text);
        }

        .table-header p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 4px;
        }

        .table-tools {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .table-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(29, 78, 216, 0.08);
            color: var(--primary-2);
            font-size: 12px;
            font-weight: 700;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1120px;
        }

        thead {
            background: var(--panel-2);
            border-bottom: 2px solid var(--border);
        }

        th {
            padding: 16px 18px;
            text-align: left;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            white-space: nowrap;
        }

        td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            font-size: 14px;
            vertical-align: middle;
        }

        tbody tr {
            transition: background 0.25s ease;
        }

        tbody tr:hover {
            background: rgba(29, 78, 216, 0.03);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            white-space: nowrap;
        }

        .badge-destino {
            background: rgba(0, 212, 170, 0.12);
            color: var(--primary);
            border: 1px solid rgba(0, 212, 170, 0.35);
        }

        .id-badge {
            background: var(--panel-2);
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            display: inline-block;
        }

        .fecha-cell {
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
        }

        .hora-cell {
            color: var(--muted);
            font-size: 13px;
            white-space: nowrap;
        }

        .hora-cell i {
            color: var(--accent);
            margin-right: 5px;
            font-size: 11px;
        }

        .volumen-cell {
            font-weight: 800;
            color: var(--primary-2);
            white-space: nowrap;
        }

        .unidad-cell {
            color: var(--text);
        }

        .unidad-cell i {
            color: var(--accent);
            margin-right: 5px;
            font-size: 12px;
        }

        .registrado-cell {
            color: var(--muted);
            font-size: 13px;
            white-space: nowrap;
        }

        .registrado-cell i {
            margin-right: 5px;
            font-size: 11px;
            color: #94a3b8;
        }

        .mobile-cards {
            display: none;
            padding: 16px;
            gap: 16px;
        }

        .mobile-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 4px 16px rgba(0,59,92,0.05);
        }

        .mobile-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .mobile-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mobile-card-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .mobile-item {
            background: var(--panel-2);
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid var(--border);
        }

        .mobile-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .mobile-value {
            font-size: 14px;
            color: var(--text);
            font-weight: 600;
            word-break: break-word;
            line-height: 1.45;
        }

        .mobile-card-actions {
            margin-top: 14px;
        }

        .mobile-btn-action {
            width: 100%;
        }

        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: var(--muted);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.4;
            color: var(--accent);
        }

        .empty-state h3 {
            font-size: 22px;
            color: var(--text);
            margin-bottom: 10px;
        }

        .empty-state p {
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .no-results {
            display: none;
            text-align: center;
            padding: 30px 20px;
            color: var(--muted);
            border-top: 1px solid var(--border);
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

        @media (max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filters-grid {
                grid-template-columns: 1fr 1fr;
            }

            .filters-grid .filter-group:first-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .corp-header {
                padding: 12px 16px;
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .logo-area {
                width: 100%;
                justify-content: flex-start;
                gap: 12px;
            }

            .empresa-info h1 {
                font-size: 18px;
            }

            .user-area {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .user-badge,
            .header-btn,
            .theme-btn {
                width: 100%;
                justify-content: center;
            }

            .main-container {
                padding: 0 15px;
                margin: 20px auto;
            }

            .page-header {
                padding: 20px;
                flex-direction: column;
                align-items: stretch;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .stats-grid,
            .filters-grid {
                grid-template-columns: 1fr;
            }

            .table-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .table-responsive {
                display: none;
            }

            .mobile-cards {
                display: grid;
            }

            .footer-corp {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .footer-links {
                justify-content: center;
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

        @media (max-width: 480px) {
            .logo-principal {
                height: 35px;
                width: 35px;
            }

            .empresa-info h1 {
                font-size: 16px;
            }

            .sistema-nombre {
                font-size: 9px;
            }

            .page-title h2 {
                font-size: 20px;
            }

            .stat-value {
                font-size: 24px;
            }

            .mobile-card {
                padding: 16px;
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
                <span class="sistema-nombre">DESPACHOS</span>
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

            <a href="../index.php" class="header-btn">
                <i class="fas fa-house"></i>
                <span>Inicio</span>
            </a>
        </div>
    </header>

    <main class="main-container">
        <div class="page-header">
            <div class="page-title">
                <h2><i class="fas fa-truck"></i>Registros de Despacho</h2>
                <p>
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo date('d/m/Y'); ?>
                    <span>·</span>
                    <i class="fas fa-chart-line"></i>
                    <?php echo $total_registros; ?> despachos
                </p>
            </div>

            <div class="header-actions">
                <a href="crear.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Nuevo Despacho
                </a>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i>
                Operación realizada exitosamente.
            </div>
        <?php endif; ?>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Despachos totales</div>
                <div class="stat-value"><?php echo $total_registros; ?></div>
                <div class="stat-text">Cantidad total de despachos registrados en el sistema.</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Volumen total</div>
                <div class="stat-value"><?php echo number_format($total_volumen, 2); ?></div>
                <div class="stat-text">Volumen total despachado en metros cúbicos (m³).</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Volumen de hoy</div>
                <div class="stat-value"><?php echo number_format($volumen_hoy, 2); ?></div>
                <div class="stat-text">Despachado únicamente con fecha de hoy.</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Promedio por despacho</div>
                <div class="stat-value"><?php echo number_format($promedio_volumen, 2); ?></div>
                <div class="stat-text">Volumen promedio considerando todos los registros.</div>
            </div>
        </section>

        <section class="filters-card">
            <div class="filters-top">
                <div>
                    <h3>Filtros rápidos</h3>
                    <p>Busca por texto o limita la vista por destino y fecha.</p>
                </div>
                <div class="table-chip">
                    <i class="fas fa-filter"></i>
                    Filtro local
                </div>
            </div>

            <div class="filters-grid">
                <div class="filter-group">
                    <label for="searchInput">Buscar</label>
                    <div class="filter-input-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="filter-input" placeholder="Buscar por ID, destino, unidad, fecha, volumen o registrado...">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="destinoFilter">Destino</label>
                    <div class="filter-input-wrap">
                        <i class="fas fa-map-marker-alt"></i>
                        <select id="destinoFilter" class="filter-select">
                            <option value="">Todos los destinos</option>
                            <?php foreach ($destinos_unicos as $destino): ?>
                                <option value="<?php echo htmlspecialchars($destino); ?>">
                                    <?php echo htmlspecialchars($destino); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-group">
                    <label for="dateFilter">Fecha</label>
                    <div class="filter-input-wrap">
                        <i class="fas fa-calendar"></i>
                        <input type="date" id="dateFilter" class="filter-input">
                    </div>
                </div>
            </div>
        </section>

        <div class="table-card">
            <?php if ($total_registros > 0): ?>
                <div class="table-header">
                    <div>
                        <h3>Listado de despachos</h3>
                        <p>Consulta el historial completo de salidas registradas.</p>
                    </div>

                    <div class="table-tools">
                        <div class="table-chip">
                            <i class="fas fa-list"></i>
                            <span id="visibleCount"><?php echo $total_registros; ?></span> visibles
                        </div>

                        <?php if ($ultimo_registro): ?>
                            <div class="table-chip">
                                <i class="fas fa-clock-rotate-left"></i>
                                Último: <?php echo date('d/m/Y', strtotime($ultimo_registro['fecha'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Escritorio -->
                <div class="table-responsive">
                    <table id="despachoTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Volumen (m³)</th>
                                <th>Destino</th>
                                <th>Unidad</th>
                                <th>Registrado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $row): ?>
                                <tr
                                    data-destino="<?php echo htmlspecialchars($row['destino_nombre']); ?>"
                                    data-fecha="<?php echo htmlspecialchars($row['fecha']); ?>"
                                >
                                    <td>
                                        <span class="id-badge">#<?php echo (int) $row['id']; ?></span>
                                    </td>
                                    <td class="fecha-cell">
                                        <?php echo date('d/m/Y', strtotime($row['fecha'])); ?>
                                    </td>
                                    <td class="hora-cell">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('H:i', strtotime($row['hora'])); ?>
                                    </td>
                                    <td class="volumen-cell">
                                        <?php echo number_format((float) $row['volumen'], 2); ?> m³
                                    </td>
                                    <td>
                                        <span class="badge badge-destino">
                                            <i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i>
                                            <?php echo htmlspecialchars($row['destino_nombre']); ?>
                                        </span>
                                    </td>
                                    <td class="unidad-cell">
                                        <?php if (!empty($row['unidad'])): ?>
                                            <i class="fas fa-truck"></i>
                                            <?php echo htmlspecialchars($row['unidad']); ?>
                                        <?php else: ?>
                                            <span style="color: var(--muted);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="registrado-cell">
                                        <i class="fas fa-calendar-check"></i>
                                        <?php echo !empty($row['fecha_registro']) ? date('d/m/Y H:i', strtotime($row['fecha_registro'])) : '—'; ?>
                                    </td>
                                    <td>
                                        <a href="../ver.php?id=<?php echo (int) $row['id']; ?>" class="btn-action">
                                            <i class="fas fa-eye"></i>
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Móvil -->
                <div class="mobile-cards" id="mobileCards">
                    <?php foreach ($registros as $row): ?>
                        <div
                            class="mobile-card"
                            data-destino="<?php echo htmlspecialchars($row['destino_nombre']); ?>"
                            data-fecha="<?php echo htmlspecialchars($row['fecha']); ?>"
                        >
                            <div class="mobile-card-top">
                                <div class="mobile-card-title">
                                    <span class="id-badge">#<?php echo (int) $row['id']; ?></span>
                                    <span class="badge badge-destino">
                                        <i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i>
                                        <?php echo htmlspecialchars($row['destino_nombre']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mobile-card-grid">
                                <div class="mobile-item">
                                    <div class="mobile-label">Fecha</div>
                                    <div class="mobile-value"><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></div>
                                </div>

                                <div class="mobile-item">
                                    <div class="mobile-label">Hora</div>
                                    <div class="mobile-value"><?php echo date('H:i', strtotime($row['hora'])); ?></div>
                                </div>

                                <div class="mobile-item">
                                    <div class="mobile-label">Volumen</div>
                                    <div class="mobile-value"><?php echo number_format((float) $row['volumen'], 2); ?> m³</div>
                                </div>

                                <div class="mobile-item">
                                    <div class="mobile-label">Unidad</div>
                                    <div class="mobile-value">
                                        <?php echo !empty($row['unidad']) ? htmlspecialchars($row['unidad']) : 'Sin unidad'; ?>
                                    </div>
                                </div>

                                <div class="mobile-item">
                                    <div class="mobile-label">Registrado</div>
                                    <div class="mobile-value">
                                        <?php echo !empty($row['fecha_registro']) ? date('d/m/Y H:i', strtotime($row['fecha_registro'])) : '—'; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mobile-card-actions">
                                <a href="../ver.php?id=<?php echo (int) $row['id']; ?>" class="btn-action mobile-btn-action">
                                    <i class="fas fa-eye"></i>
                                    Ver detalle
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="no-results" id="noResults">
                    <i class="fas fa-filter-circle-xmark" style="font-size: 28px; margin-bottom: 10px; color: var(--warning);"></i>
                    <div>No hay resultados para los filtros aplicados.</div>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>No hay registros de despacho</h3>
                    <p>Comienza registrando tu primer despacho en el sistema.</p>
                    <a href="crear.php" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-plus-circle"></i>
                        Registrar Despacho
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer-corp">
            <div>© 2026 Holcim México · Sistema de Gestión de Cemento</div>
            <div class="footer-links">
                <a href="#"><i class="fas fa-file-pdf"></i> Exportar</a>
                <a href="#"><i class="fas fa-print"></i> Imprimir</a>
                <a href="#"><i class="fas fa-question-circle"></i> Ayuda</a>
                <a href="#">v3.0</a>
            </div>
        </footer>
    </main>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        const body = document.body;
        const themeToggle = document.getElementById('themeToggle');
        const toastContainer = document.getElementById('toastContainer');
        const searchInput = document.getElementById('searchInput');
        const destinoFilter = document.getElementById('destinoFilter');
        const dateFilter = document.getElementById('dateFilter');
        const visibleCount = document.getElementById('visibleCount');
        const noResults = document.getElementById('noResults');

        const table = document.getElementById('despachoTable');
        const tableRows = table ? Array.from(table.querySelectorAll('tbody tr')) : [];
        const mobileCards = Array.from(document.querySelectorAll('#mobileCards .mobile-card'));

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

        function showToast(title, message = '', type = 'info', duration = 3200) {
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
                isDark ? 'La vista de despachos cambió a tema oscuro.' : 'La vista de despachos cambió a tema claro.',
                'info',
                2200
            );
        }

        function applyFilters() {
            const search = (searchInput?.value || '').toLowerCase().trim();
            const destino = destinoFilter?.value || '';
            const fecha = dateFilter?.value || '';

            let visibles = 0;

            tableRows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                const rowDestino = row.getAttribute('data-destino') || '';
                const rowFecha = row.getAttribute('data-fecha') || '';

                const matchSearch = !search || text.includes(search);
                const matchDestino = !destino || rowDestino === destino;
                const matchFecha = !fecha || rowFecha === fecha;

                const show = matchSearch && matchDestino && matchFecha;
                row.style.display = show ? '' : 'none';

                if (show) visibles++;
            });

            mobileCards.forEach((card) => {
                const text = card.textContent.toLowerCase();
                const cardDestino = card.getAttribute('data-destino') || '';
                const cardFecha = card.getAttribute('data-fecha') || '';

                const matchSearch = !search || text.includes(search);
                const matchDestino = !destino || cardDestino === destino;
                const matchFecha = !fecha || cardFecha === fecha;

                const show = matchSearch && matchDestino && matchFecha;
                card.style.display = show ? '' : 'none';
            });

            if (visibleCount) {
                visibleCount.textContent = visibles;
            }

            if (noResults) {
                noResults.style.display = visibles === 0 ? 'block' : 'none';
            }
        }

        updateThemeUI();

        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        if (destinoFilter) {
            destinoFilter.addEventListener('change', applyFilters);
        }

        if (dateFilter) {
            dateFilter.addEventListener('change', applyFilters);
        }

        window.addEventListener('load', () => {
            applyFilters();

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
            setTimeout(() => {
                showToast(
                    'Operación exitosa',
                    'El despacho fue guardado correctamente.',
                    'success',
                    3200
                );
            }, 500);
            <?php else: ?>
            setTimeout(() => {
                showToast(
                    'Vista lista',
                    'Ya puedes consultar y filtrar los registros de despacho.',
                    'success',
                    2400
                );
            }, 500);
            <?php endif; ?>
        });
    </script>
</body>
</html>