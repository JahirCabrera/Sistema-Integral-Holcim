<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/config.php';

// Si NO está logueado, lo manda al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_nombre = $_SESSION['user_nombre'] ?? 'Usuario';

// Validar ID recibido
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

try {
    /*
        Funciona en ambos casos:
        1. Si vienes desde /listar.php -> usa historico.id
        2. Si vienes desde /despacho/listar.php -> usa despacho.id y busca en historico.despacho_id
    */
    $sql = "SELECT * 
            FROM historico 
            WHERE id = ? OR despacho_id = ?
            ORDER BY CASE WHEN id = ? THEN 0 ELSE 1 END, id DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $id, $id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error al consultar el registro: " . $e->getMessage());
}

function badgeClass($estado)
{
    switch ($estado) {
        case 'Entregado':
            return 'status-badge status-success';
        case 'En tránsito':
            return 'status-badge status-warning';
        case 'Cargado':
            return 'status-badge status-info';
        case 'Cancelado':
            return 'status-badge status-danger';
        default:
            return 'status-badge status-neutral';
    }
}

function valorSeguro($valor, $fallback = 'No registrado')
{
    return (!empty($valor) && trim((string)$valor) !== '') ? $valor : $fallback;
}

function origenOperacion($registro)
{
    if (!empty($registro['despacho_id'])) {
        return 'Despacho';
    }
    return 'Histórico';
}

$estado_actual = valorSeguro($registro['estado'], 'Sin estado');
$folio = valorSeguro($registro['folio'], 'Sin folio');
$fecha_formateada = !empty($registro['fecha']) ? date('d/m/Y', strtotime($registro['fecha'])) : 'No registrada';
$hora_formateada = valorSeguro($registro['hora'], 'No registrada');
$cantidad = valorSeguro($registro['cantidad'], '0');
$unidad_medida = valorSeguro($registro['unidad_medida'], '');
$origen_operacion = origenOperacion($registro);

// Detectar mejor botón de regreso
$volver_url = 'index.php';
$volver_texto = 'Ir al panel';

if (!empty($registro['despacho_id'])) {
    $volver_url = 'despacho/listar.php';
    $volver_texto = 'Volver a despachos';
} else {
    $volver_url = 'listar.php';
    $volver_texto = 'Volver al histórico';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de operación · Holcim</title>
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
            background: var(--bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
            flex-shrink: 0;
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

        .user-role {
            color: var(--accent);
            font-size: 11px;
            background: rgba(255,255,255,0.08);
            padding: 3px 10px;
            border-radius: 999px;
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
            max-width: 1280px;
            margin: 28px auto;
            padding: 0 24px;
        }

        .hero-card {
            background: var(--panel);
            border-radius: 24px;
            padding: 26px;
            margin-bottom: 22px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .hero-title h2 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .hero-title p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-corp-primary,
        .btn-corp-secondary {
            padding: 12px 18px;
            text-align: center;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13px;
            transition: 0.3s;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            justify-content: center;
        }

        .btn-corp-primary {
            background: linear-gradient(135deg, #003B5C, #1d4ed8);
            color: white;
        }

        .btn-corp-primary:hover {
            transform: translateY(-2px);
        }

        .btn-corp-secondary {
            background: var(--panel);
            color: var(--text);
            border-color: var(--border);
        }

        .btn-corp-secondary:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
            font-size: 26px;
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

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .content-card {
            background: var(--panel);
            border-radius: 20px;
            padding: 22px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .content-card.full-width {
            grid-column: 1 / -1;
        }

        .section-title-corp {
            font-size: 18px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            line-height: 1.3;
        }

        .section-title-corp i {
            margin-right: 10px;
            color: var(--accent);
        }

        .detail-list {
            display: grid;
            gap: 14px;
        }

        .detail-item {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 15px;
            align-items: start;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .detail-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-label {
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 15px;
            color: var(--text);
            font-weight: 500;
            word-break: break-word;
            line-height: 1.55;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-success {
            background: rgba(16, 185, 129, 0.12);
            color: #0f766e;
        }

        .status-warning {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .status-info {
            background: rgba(59, 130, 246, 0.14);
            color: #1d4ed8;
        }

        .status-danger {
            background: rgba(239, 68, 68, 0.14);
            color: #b91c1c;
        }

        .status-neutral {
            background: rgba(100, 116, 139, 0.14);
            color: #475569;
        }

        .timeline {
            display: grid;
            gap: 16px;
        }

        .timeline-item {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .timeline-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--accent);
            margin-top: 6px;
            flex-shrink: 0;
            box-shadow: 0 0 0 6px rgba(0, 212, 170, 0.10);
        }

        .timeline-content h4 {
            font-size: 14px;
            color: var(--text);
            margin-bottom: 4px;
        }

        .timeline-content p {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.6;
        }

        .obs-box {
            background: var(--panel-2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px;
            line-height: 1.7;
            color: var(--text);
            white-space: pre-line;
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
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .detail-item {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .hero-card {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 768px) {
            .corp-header {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
                padding: 15px;
            }

            .logo-area,
            .user-area {
                width: 100%;
            }

            .user-area {
                flex-direction: column;
            }

            .user-badge,
            .header-btn,
            .theme-btn {
                width: 100%;
                justify-content: center;
            }

            .main-container {
                padding: 0 15px;
            }

            .hero-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn-corp-primary,
            .btn-corp-secondary {
                width: 100%;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .footer-corp {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .footer-links {
                flex-wrap: wrap;
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
                font-size: 18px;
            }

            .sistema-nombre {
                font-size: 10px;
            }

            .hero-title h2 {
                font-size: 24px;
            }

            .summary-value {
                font-size: 22px;
            }

            .content-card,
            .hero-card {
                padding: 18px;
            }
        }
    </style>
</head>
<body>

<header class="corp-header">
    <div class="logo-area">
        <img src="logo.jpg" alt="Holcim" class="logo-principal">
        <div class="empresa-info">
            <h1>HOLCIM</h1>
            <span class="sistema-nombre">SISTEMA DE GESTIÓN INTEGRAL</span>
        </div>
    </div>

    <div class="user-area">
        <div class="user-badge">
            <i class="fas fa-user-circle"></i>
            <span class="user-name-header"><?php echo htmlspecialchars($user_nombre); ?></span>
            <span class="user-role">Administrador</span>
        </div>

        <button class="theme-btn" id="themeToggle" type="button">
            <i class="fas fa-moon"></i>
            <span id="themeText">Modo oscuro</span>
        </button>

        <a href="logout.php" class="header-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar sesión</span>
        </a>
    </div>
</header>

<main class="main-container">
    <div class="hero-card">
        <div class="hero-title">
            <h2>Detalle de operación</h2>
            <p>Consulta completa del movimiento logístico registrado en el histórico operativo.</p>
        </div>

        <div class="hero-actions">
            <a href="<?php echo htmlspecialchars($volver_url); ?>" class="btn-corp-secondary">
                <i class="fas fa-arrow-left"></i>
                <?php echo htmlspecialchars($volver_texto); ?>
            </a>

            <a href="index.php" class="btn-corp-primary">
                <i class="fas fa-house"></i>
                Ir al panel
            </a>
        </div>
    </div>

    <section class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Folio</div>
            <div class="summary-value"><?php echo htmlspecialchars($folio); ?></div>
            <div class="summary-text">Identificador principal del movimiento registrado.</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Estado actual</div>
            <div class="summary-value"><?php echo htmlspecialchars($estado_actual); ?></div>
            <div class="summary-text">Situación operativa actual del registro.</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Cantidad</div>
            <div class="summary-value"><?php echo htmlspecialchars(trim($cantidad . ' ' . $unidad_medida)); ?></div>
            <div class="summary-text">Volumen o cantidad asociada al movimiento.</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Origen del detalle</div>
            <div class="summary-value"><?php echo htmlspecialchars($origen_operacion); ?></div>
            <div class="summary-text">Fuente principal desde la que se relaciona esta operación.</div>
        </div>
    </section>

    <div class="content-grid">
        <div class="content-card">
            <div class="section-title-corp">
                <i class="fas fa-file-lines"></i> Información general
            </div>

            <div class="detail-list">
                <div class="detail-item">
                    <div class="detail-label">Folio</div>
                    <div class="detail-value"><?php echo htmlspecialchars($folio); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Fecha</div>
                    <div class="detail-value"><?php echo htmlspecialchars($fecha_formateada); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Hora</div>
                    <div class="detail-value"><?php echo htmlspecialchars($hora_formateada); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Estado</div>
                    <div class="detail-value">
                        <span class="<?php echo badgeClass($estado_actual); ?>">
                            <?php echo htmlspecialchars($estado_actual); ?>
                        </span>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Despacho ID</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['despacho_id'] ?? null)); ?></div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="section-title-corp">
                <i class="fas fa-cubes"></i> Material y volumen
            </div>

            <div class="detail-list">
                <div class="detail-item">
                    <div class="detail-label">Material</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['material'] ?? null)); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Cantidad</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['cantidad'] ?? null)); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Unidad de medida</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['unidad_medida'] ?? null)); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Origen</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['origen'] ?? null)); ?></div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="section-title-corp">
                <i class="fas fa-truck"></i> Logística y transporte
            </div>

            <div class="detail-list">
                <div class="detail-item">
                    <div class="detail-label">Destino</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['destino'] ?? null)); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Camión</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['camion'] ?? null)); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Chofer</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['chofer'] ?? null)); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Operador de carga</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['operador_carga'] ?? null)); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Entregado por</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['entregado_por'] ?? null)); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Recibido por</div>
                    <div class="detail-value"><?php echo htmlspecialchars(valorSeguro($registro['recibido_por'] ?? null)); ?></div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="section-title-corp">
                <i class="fas fa-circle-info"></i> Seguimiento
            </div>

            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Registro identificado</h4>
                        <p>La operación fue localizada en el histórico con el folio <strong><?php echo htmlspecialchars($folio); ?></strong>.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Estado actual</h4>
                        <p>El movimiento se encuentra actualmente en estado <strong><?php echo htmlspecialchars($estado_actual); ?></strong>.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Ruta logística</h4>
                        <p>El despacho está asociado al destino <strong><?php echo htmlspecialchars(valorSeguro($registro['destino'] ?? null)); ?></strong>.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Volumen registrado</h4>
                        <p>Se documentó una cantidad de <strong><?php echo htmlspecialchars(trim($cantidad . ' ' . $unidad_medida)); ?></strong>.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card full-width">
            <div class="section-title-corp">
                <i class="fas fa-note-sticky"></i> Observaciones
            </div>

            <div class="obs-box">
                <?php echo !empty($registro['observaciones']) ? nl2br(htmlspecialchars($registro['observaciones'])) : 'Sin observaciones registradas.'; ?>
            </div>
        </div>
    </div>

    <footer class="footer-corp">
        <div>© 2026 Holcim México · Sistema de Gestión de Cemento</div>
        <div class="footer-links">
            <a href="#">Términos</a>
            <a href="#">Privacidad</a>
            <a href="#">Soporte</a>
            <a href="#">v3.0</a>
        </div>
    </footer>
</main>

<div class="toast-container" id="toastContainer"></div>

<script>
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
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
            isDark ? 'La vista de detalle cambió a tema oscuro.' : 'La vista de detalle cambió a tema claro.',
            'info',
            2200
        );
    }

    updateThemeUI();

    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    window.addEventListener('load', () => {
        setTimeout(() => {
            showToast(
                'Detalle cargado',
                'Ya puedes revisar toda la información de la operación.',
                'success',
                2400
            );
        }, 500);
    });
</script>

</body>
</html>