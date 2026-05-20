<?php
session_start();
require_once '../includes/config.php';

$user_nombre = $_SESSION['user_nombre'] ?? 'Operador';

try {
    $sql = "SELECT 
                id, 
                fecha, 
                hora,
                CASE 
                    WHEN hora = '06:00:00' THEN 'Turno Mañana'
                    WHEN hora = '14:00:00' THEN 'Turno Tarde'
                    WHEN hora = '22:00:00' THEN 'Turno Noche'
                    ELSE 'Día Completo'
                END as periodo,
                volumen,
                destino_id,
                unidad,
                observaciones,
                fecha_registro
            FROM despacho 
            ORDER BY fecha DESC, id DESC";
    $stmt = $pdo->query($sql);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$total_registros = count($registros);
$total_volumen = 0;
$produccion_hoy = 0;
$ultimo_registro = null;
$turnos_unicos = [];

foreach ($registros as $row) {
    $total_volumen += (float) $row['volumen'];

    if ($row['fecha'] === date('Y-m-d')) {
        $produccion_hoy += (float) $row['volumen'];
    }

    if (!$ultimo_registro) {
        $ultimo_registro = $row;
    }

    if (!empty($row['periodo']) && !in_array($row['periodo'], $turnos_unicos, true)) {
        $turnos_unicos[] = $row['periodo'];
    }
}

$promedio_volumen = $total_registros > 0 ? ($total_volumen / $total_registros) : 0;
sort($turnos_unicos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim · Producción</title>
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
            grid-template-columns: 1.3fr .8fr .8fr;
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
            min-width: 950px;
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
            transition: background 0.25s ease, transform 0.2s ease;
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

        .badge-turno-manana {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffe69c;
        }

        .badge-turno-tarde {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #9ec5fe;
        }

        .badge-turno-noche {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .badge-dia-completo {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        body.dark-mode .badge-turno-manana,
        body.dark-mode .badge-turno-tarde,
        body.dark-mode .badge-turno-noche,
        body.dark-mode .badge-dia-completo {
            filter: brightness(0.92);
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
        }

        .volumen-cell {
            font-weight: 800;
            color: var(--primary-2);
            white-space: nowrap;
        }

        .observaciones-cell {
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--muted);
        }

        .registro-cell {
            color: var(--muted);
            font-size: 13px;
            white-space: nowrap;
        }

        .unidad-cell {
            color: var(--muted);
            font-size: 13px;
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
            .filters-card,
            .table-card {
                border-radius: 18px;
            }

            .page-header {
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
            }

            .page-title h2 {
                font-size: 24px;
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
                height: 40px;
                width: 40px;
            }

            .empresa-info h1 {
                font-size: 17px;
            }

            .sistema-nombre {
                font-size: 9px;
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

            .page-title h2 {
                font-size: 22px;
            }

            .stat-value {
                font-size: 24px;
            }

            .table-responsive {
                margin: 0;
            }

            table {
                min-width: 820px;
            }

            th, td {
                padding: 13px 14px;
                font-size: 13px;
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
                <span class="sistema-nombre">PRODUCCIÓN</span>
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
                <h2>Registros de Producción</h2>
                <p>
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo date('d/m/Y'); ?>
                    <span>·</span>
                    <i class="fas fa-database"></i>
                    <?php echo $total_registros; ?> registros totales
                </p>
            </div>

            <div class="header-actions">
                <a href="crear.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Nuevo Registro
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
                <div class="stat-label">Registros totales</div>
                <div class="stat-value"><?php echo $total_registros; ?></div>
                <div class="stat-text">Cantidad total de producciones almacenadas en el sistema.</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Producción acumulada</div>
                <div class="stat-value"><?php echo number_format($total_volumen, 2); ?> m³</div>
                <div class="stat-text">Volumen total registrado en metros cúbicos.</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Producción de hoy</div>
                <div class="stat-value"><?php echo number_format($produccion_hoy, 2); ?> m³</div>
                <div class="stat-text">Volumen capturado con fecha de hoy.</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Promedio por registro</div>
                <div class="stat-value"><?php echo number_format($promedio_volumen, 2); ?> m³</div>
                <div class="stat-text">Media de volumen considerando todos los registros.</div>
            </div>
        </section>

        <section class="filters-card">
            <div class="filters-top">
                <div>
                    <h3>Filtros rápidos</h3>
                    <p>Busca por texto o limita la vista por turno y fecha.</p>
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
                        <input type="text" id="searchInput" class="filter-input" placeholder="Buscar por fecha, observación, turno, volumen, unidad o ID...">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="periodFilter">Periodo</label>
                    <div class="filter-input-wrap">
                        <i class="fas fa-clock"></i>
                        <select id="periodFilter" class="filter-select">
                            <option value="">Todos los periodos</option>
                            <?php foreach ($turnos_unicos as $turno): ?>
                                <option value="<?php echo htmlspecialchars($turno); ?>">
                                    <?php echo htmlspecialchars($turno); ?>
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
            <div class="table-header">
                <div>
                    <h3>Listado de producción</h3>
                    <p>Consulta el historial completo de registros capturados.</p>
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

            <?php if ($total_registros > 0): ?>
                <div class="table-responsive">
                    <table id="produccionTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Periodo</th>
                                <th>Unidad</th>
                                <th>Volumen (m³)</th>
                                <th>Observaciones</th>
                                <th>Registrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $row):
                                $badge_class = 'badge-dia-completo';
                                if ($row['periodo'] === 'Turno Mañana') {
                                    $badge_class = 'badge-turno-manana';
                                } elseif ($row['periodo'] === 'Turno Tarde') {
                                    $badge_class = 'badge-turno-tarde';
                                } elseif ($row['periodo'] === 'Turno Noche') {
                                    $badge_class = 'badge-turno-noche';
                                }
                            ?>
                                <tr
                                    data-periodo="<?php echo htmlspecialchars($row['periodo']); ?>"
                                    data-fecha="<?php echo htmlspecialchars($row['fecha']); ?>"
                                >
                                    <td>
                                        <span class="id-badge">#<?php echo (int) $row['id']; ?></span>
                                    </td>
                                    <td class="fecha-cell">
                                        <?php echo date('d/m/Y', strtotime($row['fecha'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['periodo']); ?>
                                        </span>
                                    </td>
                                    <td class="unidad-cell">
                                        <?php echo htmlspecialchars($row['unidad'] ?? '—'); ?>
                                    </td>
                                    <td class="volumen-cell">
                                        <?php echo number_format((float) $row['volumen'], 2); ?> m³
                                    </td>
                                    <td class="observaciones-cell" title="<?php echo htmlspecialchars($row['observaciones'] ?? ''); ?>">
                                        <?php echo !empty($row['observaciones']) ? htmlspecialchars($row['observaciones']) : '—'; ?>
                                    </td>
                                    <td class="registro-cell">
                                        <i class="fas fa-clock" style="margin-right: 5px; font-size: 11px;"></i>
                                        <?php 
                                        if (!empty($row['fecha_registro'])) {
                                            echo date('d/m/Y H:i', strtotime($row['fecha_registro']));
                                        } elseif (!empty($row['created_at'])) {
                                            echo date('d/m/Y H:i', strtotime($row['created_at']));
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="no-results" id="noResults">
                    <i class="fas fa-filter-circle-xmark" style="font-size: 28px; margin-bottom: 10px; color: var(--warning);"></i>
                    <div>No hay resultados para los filtros aplicados.</div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>No hay registros de producción</h3>
                    <p>Comienza registrando tu primera producción en el sistema.</p>
                    <a href="crear.php" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-plus-circle"></i>
                        Registrar Producción
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
        const periodFilter = document.getElementById('periodFilter');
        const dateFilter = document.getElementById('dateFilter');
        const table = document.getElementById('produccionTable');
        const rows = table ? Array.from(table.querySelectorAll('tbody tr')) : [];
        const visibleCount = document.getElementById('visibleCount');
        const noResults = document.getElementById('noResults');

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
                isDark ? 'La lista de producción cambió a tema oscuro.' : 'La lista de producción cambió a tema claro.',
                'info',
                2200
            );
        }

        function applyFilters() {
            if (!rows.length) return;

            const search = (searchInput.value || '').toLowerCase().trim();
            const periodo = periodFilter.value;
            const fecha = dateFilter.value;

            let visibles = 0;

            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                const rowPeriodo = row.getAttribute('data-periodo') || '';
                const rowFecha = row.getAttribute('data-fecha') || '';

                const matchSearch = !search || text.includes(search);
                const matchPeriodo = !periodo || rowPeriodo === periodo;
                const matchFecha = !fecha || rowFecha === fecha;

                const show = matchSearch && matchPeriodo && matchFecha;
                row.style.display = show ? '' : 'none';

                if (show) visibles++;
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

        if (periodFilter) {
            periodFilter.addEventListener('change', applyFilters);
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
                    'El registro de producción fue guardado correctamente.',
                    'success',
                    3200
                );
            }, 500);
            <?php else: ?>
            setTimeout(() => {
                showToast(
                    'Vista lista',
                    'Ya puedes consultar y filtrar los registros de producción.',
                    'success',
                    2400
                );
            }, 500);
            <?php endif; ?>
        });
    </script>
</body>
</html>