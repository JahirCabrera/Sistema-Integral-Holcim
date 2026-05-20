<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_nombre = $_SESSION['user_nombre'] ?? 'Operador';

$archivo = "data.json";
$data = [];
$mensaje = '';
$tipo_mensaje = 'success';

if (file_exists($archivo)) {
    $json = json_decode(file_get_contents($archivo), true);
    if (is_array($json)) {
        $data = $json;
    }
}

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $mensaje = 'Material registrado correctamente.';
            $tipo_mensaje = 'success';
            break;
        case 'updated':
            $mensaje = 'Material actualizado correctamente.';
            $tipo_mensaje = 'success';
            break;
        case 'deleted':
            $mensaje = 'Material eliminado correctamente.';
            $tipo_mensaje = 'success';
            break;
        case 'error':
            $mensaje = 'Ocurrió un problema al procesar la operación.';
            $tipo_mensaje = 'danger';
            break;
    }
}

$totalMateriales = count($data);
$stockBajo = 0;
$activos = 0;
$valorTotalCantidad = 0;
$tipos = [];

foreach ($data as $m) {
    $cantidad = floatval($m["cantidad"] ?? 0);
    $estado = strtolower(trim($m["estado"] ?? ""));
    $tipo = trim($m["tipo"] ?? '');

    $valorTotalCantidad += $cantidad;

    if ($cantidad <= 10) {
        $stockBajo++;
    }

    if ($estado === "activo" || $estado === "disponible") {
        $activos++;
    }

    if ($tipo !== '') {
        if (!isset($tipos[$tipo])) {
            $tipos[$tipo] = 0;
        }
        $tipos[$tipo]++;
    }
}

arsort($tipos);
$tipoPrincipal = !empty($tipos) ? array_key_first($tipos) : 'Sin datos';
$tipoPrincipalTotal = !empty($tipos) ? reset($tipos) : 0;

function porcentajeSeguro($parte, $total)
{
    if ($total <= 0) return 0;
    return round(($parte / $total) * 100);
}

function claseEstadoMaterial($estado)
{
    $estado = strtolower(trim((string)$estado));

    if ($estado === 'activo' || $estado === 'disponible') {
        return 'badge badge-success';
    }

    if ($estado === 'inactivo' || $estado === 'agotado') {
        return 'badge badge-danger';
    }

    if ($estado === 'mantenimiento' || $estado === 'pendiente') {
        return 'badge badge-warning';
    }

    return 'badge badge-neutral';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim · Inventario de Materiales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: #ffffff;
            --panel-2: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #0f3d5e;
            --primary-2: #1d4ed8;
            --accent: #00c896;
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
            --primary: #1e293b;
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
            color: var(--text);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            transition: background 0.25s ease, color 0.25s ease;
        }

        a {
            text-decoration: none;
        }

        .layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, #003b5c 0%, #0f2740 100%);
            color: #fff;
            padding: 24px 18px;
            position: sticky;
            top: 0;
            height: 100vh;
            border-right: 1px solid rgba(255,255,255,0.08);
            z-index: 1200;
        }

        .sidebar-overlay {
            display: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 8px 10px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 22px;
        }

        .brand img {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 14px;
            background: #fff;
            padding: 6px;
        }

        .brand h1 {
            font-size: 20px;
            line-height: 1.1;
            letter-spacing: 0.8px;
        }

        .brand span {
            display: block;
            color: #7dd3fc;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 4px;
        }

        .menu-group {
            margin-bottom: 20px;
        }

        .menu-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            color: rgba(255,255,255,.55);
            margin: 12px 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e2e8f0;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 14px;
            transition: .25s;
            margin-bottom: 6px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.10);
            color: #fff;
            transform: translateX(4px);
        }

        .nav-link i {
            width: 18px;
            text-align: center;
        }

        .content {
            padding: 26px;
            min-width: 0;
            padding-bottom: 100px;
        }

        .mobile-topbar {
            display: none;
        }

        .mobile-icon-btn,
        .mobile-menu-btn {
            border: none;
            background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
            color: white;
            padding: 12px 14px;
            border-radius: 14px;
            font-weight: 700;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: var(--shadow);
            font-size: 14px;
            display: none;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .welcome-box {
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: var(--radius-xl);
            padding: 24px 28px;
            flex: 1;
            min-width: 320px;
        }

        .welcome-box h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: var(--text);
            line-height: 1.2;
        }

        .welcome-box p {
            color: var(--muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .top-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-card {
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: var(--radius-lg);
            padding: 14px 18px;
            min-width: 170px;
        }

        .action-card .label {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 6px;
        }

        .action-card .value {
            font-weight: 700;
            font-size: 15px;
            color: var(--text);
        }

        .theme-btn,
        .logout-btn {
            border: none;
            background: var(--panel);
            color: var(--text);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .logout-btn {
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
        }

        .mobile-hero {
            display: none;
        }

        .hero-strip {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .hero-card {
            background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 18px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary-2), var(--accent));
        }

        .hero-card.main {
            background: linear-gradient(135deg, rgba(15,61,94,0.98), rgba(29,78,216,0.95));
            color: white;
            border: none;
        }

        .hero-card.main::before {
            background: linear-gradient(180deg, #ffffff, rgba(255,255,255,0.3));
        }

        .hero-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--muted);
            margin-bottom: 8px;
            font-weight: 700;
        }

        .hero-card.main .hero-title,
        .hero-card.main .hero-value,
        .hero-card.main .hero-sub {
            color: white;
        }

        .hero-value {
            font-size: 30px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .hero-sub {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .toolbar {
            display: grid;
            grid-template-columns: 1.1fr auto auto;
            gap: 14px;
            margin-bottom: 24px;
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 18px;
            border-radius: var(--radius-xl);
        }

        .search-group {
            position: relative;
        }

        .search-group i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 14px;
        }

        .search-input {
            width: 100%;
            border: 1px solid var(--border);
            background: var(--panel-2);
            color: var(--text);
            border-radius: 14px;
            padding: 13px 16px 13px 42px;
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
        }

        .search-input:focus {
            border-color: var(--primary-2);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.10);
        }

        .toolbar-btn {
            border: none;
            padding: 13px 18px;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.25s ease;
            min-width: 170px;
        }

        .toolbar-btn.primary {
            background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
            color: white;
        }

        .toolbar-btn.secondary {
            background: var(--panel-2);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .toolbar-btn:hover,
        .theme-btn:hover,
        .logout-btn:hover {
            transform: translateY(-2px);
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: linear-gradient(180deg, var(--panel) 0%, var(--panel-2) 100%);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::after {
            content: '';
            position: absolute;
            right: -18px;
            bottom: -18px;
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: rgba(29, 78, 216, 0.08);
        }

        .kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            position: relative;
            z-index: 2;
        }

        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(29, 78, 216, 0.10);
            color: var(--primary-2);
            font-size: 20px;
            flex-shrink: 0;
        }

        .kpi-badge {
            background: rgba(16, 185, 129, 0.10);
            color: var(--success);
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            position: relative;
            z-index: 2;
        }

        .kpi-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--muted);
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }

        .kpi-value {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
            color: var(--text);
        }

        .kpi-foot {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5;
            position: relative;
            z-index: 2;
        }

        .section {
            margin-bottom: 24px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .section-header h3 {
            font-size: 20px;
            color: var(--text);
            line-height: 1.2;
            position: relative;
        }

        .section-header h3::after {
            content: '';
            display: block;
            width: 42px;
            height: 3px;
            border-radius: 999px;
            margin-top: 8px;
            background: linear-gradient(135deg, var(--accent), var(--primary-2));
        }

        .section-header p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: var(--radius-xl);
            padding: 22px;
            min-width: 0;
            transition: all 0.25s ease;
        }

        .panel:hover {
            transform: translateY(-4px);
        }

        .table-wrap {
            overflow-x: auto;
        }

        .desktop-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1020px;
        }

        .desktop-table th,
        .desktop-table td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            vertical-align: middle;
        }

        .desktop-table th {
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .7px;
            font-size: 11px;
        }

        .desktop-table tbody tr:hover {
            background: var(--panel-2);
        }

        .id-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(29, 78, 216, 0.08);
            color: var(--primary-2);
            font-weight: 800;
            font-size: 12px;
        }

        .material-name {
            font-weight: 800;
            color: var(--text);
            font-size: 15px;
        }

        .material-name i,
        .info-flex i {
            color: var(--accent);
        }

        .info-flex {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            line-height: 1.5;
            color: var(--text);
        }

        .muted {
            color: var(--muted);
        }

        .cantidad-cell {
            font-weight: 800;
            color: var(--text);
        }

        .badge {
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.10);
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.22);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.10);
            color: var(--warning);
            border-color: rgba(245, 158, 11, 0.22);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.10);
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.22);
        }

        .badge-neutral {
            background: rgba(100, 116, 139, 0.12);
            color: var(--muted);
            border-color: rgba(100, 116, 139, 0.18);
        }

        .actions-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action {
            border: none;
            padding: 9px 14px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: all 0.25s ease;
        }

        .btn-edit {
            background: rgba(0, 200, 150, 0.10);
            color: var(--accent);
            border: 1px solid rgba(0, 200, 150, 0.25);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            background: var(--accent);
            color: white;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.08);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.18);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            background: var(--danger);
            color: white;
        }

        .mobile-cards {
            display: none;
            gap: 16px;
        }

        .material-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px;
            box-shadow: var(--shadow);
        }

        .material-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .material-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mobile-name {
            font-weight: 800;
            color: var(--text);
            font-size: 16px;
        }

        .mobile-grid {
            display: grid;
            gap: 12px;
        }

        .mobile-item {
            background: var(--panel-2);
            border-radius: 14px;
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
            line-height: 1.5;
        }

        .mobile-actions {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .mobile-actions .btn-action {
            width: 100%;
        }

        .alert-inline {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            border-radius: 16px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            background: var(--panel);
            box-shadow: var(--shadow);
        }

        .alert-inline.success {
            border-left: 5px solid var(--success);
        }

        .alert-inline.danger {
            border-left: 5px solid var(--danger);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 52px;
            margin-bottom: 14px;
            color: var(--accent);
        }

        .empty-state h3 {
            font-size: 22px;
            color: var(--text);
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .empty-search {
            display: none;
            text-align: center;
            padding: 30px 20px 10px;
            color: var(--muted);
        }

        .empty-search i {
            font-size: 34px;
            margin-bottom: 10px;
            color: var(--accent);
        }

        .hidden-row {
            display: none !important;
        }

        .footer {
            margin-top: 28px;
            color: var(--muted);
            text-align: center;
            font-size: 13px;
            line-height: 1.5;
        }

        .mobile-bottom-nav {
            display: none;
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

        .confirm-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 4000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .confirm-modal.show {
            opacity: 1;
            pointer-events: auto;
        }

        .confirm-box {
            width: 100%;
            max-width: 460px;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 24px;
            transform: translateY(12px) scale(0.98);
            transition: transform 0.25s ease;
        }

        .confirm-modal.show .confirm-box {
            transform: translateY(0) scale(1);
        }

        .confirm-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: rgba(239, 68, 68, 0.10);
            color: var(--danger);
            display: grid;
            place-items: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .confirm-box h3 {
            font-size: 22px;
            margin-bottom: 8px;
            color: var(--text);
        }

        .confirm-box p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .confirm-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .confirm-actions button {
            flex: 1;
            min-width: 140px;
            border: none;
            padding: 13px 16px;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .confirm-cancel {
            background: var(--panel-2);
            color: var(--text);
            border: 1px solid var(--border) !important;
        }

        .confirm-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .confirm-actions button:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 1300px) {
            .hero-strip,
            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .toolbar {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 285px;
                max-width: 85vw;
                height: 100vh;
                transition: left 0.3s ease;
                overflow-y: auto;
            }

            .sidebar.open {
                left: 0;
            }

            .sidebar-overlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.5);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
                z-index: 1100;
            }

            .sidebar-overlay.show {
                opacity: 1;
                pointer-events: auto;
            }

            .content {
                padding: 14px;
                padding-top: 88px;
                padding-bottom: 110px;
            }

            .mobile-topbar {
                display: flex;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                padding: 12px 14px;
                background: rgba(244, 247, 251, 0.88);
                backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(226, 232, 240, 0.8);
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }

            body.dark-mode .mobile-topbar {
                background: rgba(10, 15, 28, 0.88);
                border-bottom: 1px solid rgba(31, 41, 55, 0.8);
            }

            .mobile-topbar-left {
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 0;
            }

            .mobile-logo {
                width: 42px;
                height: 42px;
                border-radius: 12px;
                object-fit: cover;
                background: #fff;
                padding: 4px;
                flex-shrink: 0;
            }

            .mobile-brand-text {
                min-width: 0;
            }

            .mobile-brand-text strong {
                display: block;
                font-size: 14px;
                line-height: 1.2;
            }

            .mobile-brand-text span {
                display: block;
                font-size: 11px;
                color: var(--muted);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .mobile-topbar-actions {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .mobile-menu-btn,
            .mobile-icon-btn {
                display: inline-flex;
                width: 44px;
                height: 44px;
                padding: 0;
                border-radius: 12px;
                flex-shrink: 0;
            }

            .topbar {
                display: none;
            }

            .mobile-hero {
                display: block;
                background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
                color: white;
                border-radius: 24px;
                padding: 20px;
                margin-bottom: 16px;
                box-shadow: var(--shadow);
            }

            .mobile-hero h2 {
                font-size: 22px;
                margin-bottom: 8px;
                line-height: 1.2;
            }

            .mobile-hero p {
                font-size: 13px;
                line-height: 1.5;
                opacity: 0.92;
                margin-bottom: 16px;
            }

            .mobile-hero-meta {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .mobile-hero-chip {
                background: rgba(255,255,255,0.14);
                border: 1px solid rgba(255,255,255,0.15);
                padding: 12px;
                border-radius: 14px;
            }

            .mobile-hero-chip .label {
                font-size: 11px;
                opacity: 0.82;
                margin-bottom: 4px;
            }

            .mobile-hero-chip .value {
                font-size: 14px;
                font-weight: 700;
            }

            .hero-strip,
            .kpi-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .desktop-table-wrap {
                display: none;
            }

            .mobile-cards {
                display: grid;
            }

            .mobile-bottom-nav {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                position: fixed;
                left: 10px;
                right: 10px;
                bottom: 10px;
                z-index: 1000;
                background: rgba(255,255,255,0.92);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(226, 232, 240, 0.9);
                box-shadow: var(--shadow);
                border-radius: 20px;
                padding: 8px;
                gap: 6px;
            }

            body.dark-mode .mobile-bottom-nav {
                background: rgba(17, 24, 39, 0.92);
                border-color: rgba(31, 41, 55, 0.9);
            }

            .mobile-bottom-nav a {
                text-decoration: none;
                color: var(--muted);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 5px;
                padding: 8px 4px;
                border-radius: 14px;
                font-size: 10px;
                font-weight: 700;
                min-height: 58px;
            }

            .mobile-bottom-nav a i {
                font-size: 16px;
            }

            .mobile-bottom-nav a.active {
                background: linear-gradient(135deg, #0f3d5e, #1d4ed8);
                color: white;
            }

            .toast-container {
                top: 84px;
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
            .content {
                padding: 12px;
                padding-top: 84px;
                padding-bottom: 108px;
            }

            .mobile-hero {
                border-radius: 20px;
                padding: 18px;
            }

            .mobile-hero h2 {
                font-size: 20px;
            }

            .welcome-box,
            .panel,
            .hero-card,
            .kpi-card,
            .material-card {
                border-radius: 18px;
            }
        }
    </style>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="mobile-topbar">
    <div class="mobile-topbar-left">
        <img src="../logo.jpg" alt="Holcim" class="mobile-logo" onerror="this.src='https://cdn.brandfetch.io/idZ5TRl0IY/w/1757/h/1757/theme/dark/icon.jpeg'">
        <div class="mobile-brand-text">
            <strong>HOLCIM</strong>
            <span>Inventario de materiales</span>
        </div>
    </div>

    <div class="mobile-topbar-actions">
        <button class="mobile-icon-btn" id="themeToggleMobile" aria-label="Cambiar tema">
            <i class="fas fa-moon"></i>
        </button>
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</div>

<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="../logo.jpg" alt="Holcim" onerror="this.src='https://cdn.brandfetch.io/idZ5TRl0IY/w/1757/h/1757/theme/dark/icon.jpeg'">
            <div>
                <h1>HOLCIM</h1>
                <span>Sistema de gestión integral</span>
            </div>
        </div>

        <div class="menu-group">
            <div class="menu-title">Principal</div>
            <a class="nav-link" href="../index.php"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a class="nav-link" href="../produccion/listar.php"><i class="fas fa-industry"></i> Producción</a>
            <a class="nav-link" href="../despacho/listar.php"><i class="fas fa-truck"></i> Despacho</a>
            <a class="nav-link" href="../destinos/listar.php"><i class="fas fa-map-location-dot"></i> Destinos</a>
        </div>

        <div class="menu-group">
            <div class="menu-title">Logística</div>
            <a class="nav-link" href="../camiones/listar.php"><i class="fas fa-truck-moving"></i> Camiones</a>
            <a class="nav-link active" href="../materiales/listar.php"><i class="fas fa-cubes"></i> Materiales</a>
            <a class="nav-link" href="../cargas/listar.php"><i class="fas fa-weight-hanging"></i> Cargas</a>
        </div>

        <div class="menu-group">
            <div class="menu-title">Recursos</div>
            <a class="nav-link" href="../trabajadores/listar.php"><i class="fas fa-users"></i> Trabajadores</a>
            <a class="nav-link" href="../contactos/listar.php"><i class="fas fa-address-book"></i> Contactos</a>
            <a class="nav-link" href="../logout.php"><i class="fas fa-right-from-bracket"></i> Cerrar sesión</a>
        </div>
    </aside>

    <main class="content">
        <section class="mobile-hero">
            <h2>Inventario de materiales</h2>
            <p>Consulta el inventario actual, identifica stock bajo y administra rápidamente el catálogo de materiales.</p>
            <div class="mobile-hero-meta">
                <div class="mobile-hero-chip">
                    <div class="label">Fecha</div>
                    <div class="value"><?php echo date('d/m/Y'); ?></div>
                </div>
                <div class="mobile-hero-chip">
                    <div class="label">Total</div>
                    <div class="value"><?php echo $totalMateriales; ?></div>
                </div>
            </div>
        </section>

        <div class="topbar">
            <div class="welcome-box">
                <h2>Gestión de materiales</h2>
                <p>Administra el inventario registrado, identifica materiales críticos y mantén control visual de existencias y estado.</p>
            </div>

            <div class="top-actions">
                <div class="action-card">
                    <div class="label">Usuario</div>
                    <div class="value"><?php echo htmlspecialchars($user_nombre); ?></div>
                </div>

                <div class="action-card">
                    <div class="label">Fecha actual</div>
                    <div class="value"><?php echo date('d/m/Y'); ?></div>
                </div>

                <button class="theme-btn" id="themeToggle">
                    <i class="fas fa-moon"></i>
                    <span id="themeText">Modo oscuro</span>
                </button>

                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-inline <?php echo htmlspecialchars($tipo_mensaje); ?>">
                <i class="fas <?php echo $tipo_mensaje === 'danger' ? 'fa-circle-xmark' : 'fa-circle-check'; ?>"></i>
                <div><?php echo htmlspecialchars($mensaje); ?></div>
            </div>
        <?php endif; ?>

        <section class="hero-strip">
            <div class="hero-card main">
                <div class="hero-title">Vista actual</div>
                <div class="hero-value"><?php echo $totalMateriales; ?></div>
                <div class="hero-sub">Materiales registrados actualmente en el inventario del sistema.</div>
            </div>

            <div class="hero-card">
                <div class="hero-title">Stock bajo</div>
                <div class="hero-value"><?php echo $stockBajo; ?></div>
                <div class="hero-sub">Materiales que requieren atención inmediata por cantidad crítica.</div>
            </div>

            <div class="hero-card">
                <div class="hero-title">Activos</div>
                <div class="hero-value"><?php echo $activos; ?></div>
                <div class="hero-sub">Materiales marcados como activos o disponibles en la operación.</div>
            </div>

            <div class="hero-card">
                <div class="hero-title">Tipo principal</div>
                <div class="hero-value"><?php echo htmlspecialchars($tipoPrincipal); ?></div>
                <div class="hero-sub">
                    <?php echo $tipoPrincipalTotal > 0 ? $tipoPrincipalTotal . ' registro(s) en esta categoría.' : 'Sin clasificación disponible.'; ?>
                </div>
            </div>
        </section>

        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
                    <div class="kpi-badge">Inventario</div>
                </div>
                <div class="kpi-label">Total materiales</div>
                <div class="kpi-value"><?php echo $totalMateriales; ?></div>
                <div class="kpi-foot">Cantidad de registros disponibles en el módulo de materiales.</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="kpi-badge">Crítico</div>
                </div>
                <div class="kpi-label">Stock bajo</div>
                <div class="kpi-value"><?php echo $stockBajo; ?></div>
                <div class="kpi-foot">Materiales con cantidad igual o inferior a 10 unidades.</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="kpi-badge">Operación</div>
                </div>
                <div class="kpi-label">Activos</div>
                <div class="kpi-value"><?php echo $activos; ?></div>
                <div class="kpi-foot">Disponibles para uso actual dentro del sistema.</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-top">
                    <div class="kpi-icon"><i class="fas fa-scale-balanced"></i></div>
                    <div class="kpi-badge">Volumen</div>
                </div>
                <div class="kpi-label">Cantidad acumulada</div>
                <div class="kpi-value"><?php echo rtrim(rtrim(number_format($valorTotalCantidad, 2, '.', ''), '0'), '.'); ?></div>
                <div class="kpi-foot">Suma de cantidades capturadas en el inventario actual.</div>
            </div>
        </section>

        <div class="toolbar">
            <div class="search-group">
                <i class="fas fa-search"></i>
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Buscar por material, tipo, unidad, cantidad o estado..."
                >
            </div>

            <a href="crear.php" class="toolbar-btn primary">
                <i class="fas fa-plus-circle"></i>
                Nuevo material
            </a>

            <a href="../index.php" class="toolbar-btn secondary">
                <i class="fas fa-house"></i>
                Volver al dashboard
            </a>
        </div>

        <section class="section">
            <div class="section-header">
                <div>
                    <h3>Listado de materiales</h3>
                    <p>Consulta el inventario general y accede a edición o eliminación de cada registro.</p>
                </div>
            </div>

            <div class="panel">
                <?php if (!empty($data)): ?>
                    <div class="empty-search" id="emptySearchState">
                        <i class="fas fa-search"></i>
                        <h4>Sin coincidencias</h4>
                        <p>No se encontraron materiales con ese criterio de búsqueda.</p>
                    </div>

                    <div class="desktop-table-wrap">
                        <div class="table-wrap">
                            <table class="desktop-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Material</th>
                                        <th>Tipo</th>
                                        <th>Unidad</th>
                                        <th>Cantidad</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $i => $m): ?>
                                        <?php
                                            $nombre = $m["nombre"] ?? "";
                                            $tipo = $m["tipo"] ?? "";
                                            $unidad = $m["unidad"] ?? "";
                                            $cantidad = $m["cantidad"] ?? "";
                                            $estado = $m["estado"] ?? "";
                                            $searchText = strtolower(trim($nombre . ' ' . $tipo . ' ' . $unidad . ' ' . $cantidad . ' ' . $estado));
                                        ?>
                                        <tr class="search-item" data-search="<?php echo htmlspecialchars($searchText); ?>">
                                            <td>
                                                <span class="id-badge">#<?php echo $i; ?></span>
                                            </td>
                                            <td class="material-name">
                                                <span class="info-flex">
                                                    <i class="fas fa-cube"></i>
                                                    <?php echo htmlspecialchars($nombre); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo !empty($tipo) ? htmlspecialchars($tipo) : '<span class="muted">No definido</span>'; ?>
                                            </td>
                                            <td>
                                                <?php echo !empty($unidad) ? htmlspecialchars($unidad) : '<span class="muted">No definida</span>'; ?>
                                            </td>
                                            <td class="cantidad-cell">
                                                <?php echo htmlspecialchars($cantidad); ?>
                                            </td>
                                            <td>
                                                <span class="<?php echo claseEstadoMaterial($estado); ?>">
                                                    <?php echo htmlspecialchars($estado); ?>
                                                </span>
                                            </td>
                                            <td class="actions-cell">
                                                <a href="editar.php?id=<?php echo $i; ?>" class="btn-action btn-edit">
                                                    <i class="fas fa-pen"></i> Editar
                                                </a>
                                                <button
                                                    type="button"
                                                    class="btn-action btn-delete"
                                                    onclick="confirmarEliminar(<?php echo $i; ?>, '<?php echo htmlspecialchars(addslashes($nombre !== '' ? $nombre : 'este material')); ?>')"
                                                >
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mobile-cards">
                        <?php foreach ($data as $i => $m): ?>
                            <?php
                                $nombre = $m["nombre"] ?? "";
                                $tipo = $m["tipo"] ?? "";
                                $unidad = $m["unidad"] ?? "";
                                $cantidad = $m["cantidad"] ?? "";
                                $estado = $m["estado"] ?? "";
                                $searchText = strtolower(trim($nombre . ' ' . $tipo . ' ' . $unidad . ' ' . $cantidad . ' ' . $estado));
                            ?>
                            <div class="material-card search-item" data-search="<?php echo htmlspecialchars($searchText); ?>">
                                <div class="material-card-top">
                                    <div class="material-card-title">
                                        <span class="id-badge">#<?php echo $i; ?></span>
                                        <div class="mobile-name"><?php echo htmlspecialchars($nombre); ?></div>
                                    </div>
                                    <span class="<?php echo claseEstadoMaterial($estado); ?>">
                                        <?php echo htmlspecialchars($estado); ?>
                                    </span>
                                </div>

                                <div class="mobile-grid">
                                    <div class="mobile-item">
                                        <div class="mobile-label">Tipo</div>
                                        <div class="mobile-value"><?php echo !empty($tipo) ? htmlspecialchars($tipo) : 'No definido'; ?></div>
                                    </div>

                                    <div class="mobile-item">
                                        <div class="mobile-label">Unidad</div>
                                        <div class="mobile-value"><?php echo !empty($unidad) ? htmlspecialchars($unidad) : 'No definida'; ?></div>
                                    </div>

                                    <div class="mobile-item">
                                        <div class="mobile-label">Cantidad</div>
                                        <div class="mobile-value"><?php echo htmlspecialchars($cantidad); ?></div>
                                    </div>
                                </div>

                                <div class="mobile-actions">
                                    <a href="editar.php?id=<?php echo $i; ?>" class="btn-action btn-edit">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    <button
                                        type="button"
                                        class="btn-action btn-delete"
                                        onclick="confirmarEliminar(<?php echo $i; ?>, '<?php echo htmlspecialchars(addslashes($nombre !== '' ? $nombre : 'este material')); ?>')"
                                    >
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No hay materiales registrados</h3>
                        <p>Comienza agregando el primer material al inventario del sistema.</p>
                        <a href="crear.php" class="toolbar-btn primary" style="display:inline-flex; min-width:auto;">
                            <i class="fas fa-plus-circle"></i>
                            Nuevo material
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="footer">
            © 2026 Holcim México · Sistema de Gestión de Cemento · Módulo de Materiales v3.0
        </div>
    </main>
</div>

<nav class="mobile-bottom-nav">
    <a href="../index.php">
        <i class="fas fa-house"></i>
        <span>Inicio</span>
    </a>
    <a href="../produccion/listar.php">
        <i class="fas fa-industry"></i>
        <span>Producción</span>
    </a>
    <a href="../despacho/listar.php">
        <i class="fas fa-truck"></i>
        <span>Despacho</span>
    </a>
    <a href="../materiales/listar.php" class="active">
        <i class="fas fa-cubes"></i>
        <span>Materiales</span>
    </a>
    <a href="../logout.php">
        <i class="fas fa-right-from-bracket"></i>
        <span>Salir</span>
    </a>
</nav>

<div class="toast-container" id="toastContainer"></div>

<div class="confirm-modal" id="confirmModal">
    <div class="confirm-box">
        <div class="confirm-icon">
            <i class="fas fa-trash"></i>
        </div>
        <h3>Eliminar material</h3>
        <p id="confirmMessage">¿Estás seguro de eliminar este material?</p>
        <div class="confirm-actions">
            <button type="button" class="confirm-cancel" id="cancelDeleteBtn">Cancelar</button>
            <button type="button" class="confirm-delete" id="confirmDeleteBtn">Sí, eliminar</button>
        </div>
    </div>
</div>

<script>
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const themeToggleMobile = document.getElementById('themeToggleMobile');

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

        if (themeToggleMobile) {
            themeToggleMobile.innerHTML = isDark
                ? '<i class="fas fa-sun"></i>'
                : '<i class="fas fa-moon"></i>';
            themeToggleMobile.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
        }
    }

    function toggleTheme() {
        body.classList.toggle('dark-mode');
        const isDark = body.classList.contains('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeUI();

        showToast(
            isDark ? 'Modo oscuro activado' : 'Modo claro activado',
            isDark ? 'La interfaz se ajustó al tema oscuro.' : 'La interfaz se ajustó al tema claro.',
            'info',
            2500
        );
    }

    updateThemeUI();

    if (themeToggle) themeToggle.addEventListener('click', toggleTheme);
    if (themeToggleMobile) themeToggleMobile.addEventListener('click', toggleTheme);

    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('show');
    }

    function openSidebar() {
        sidebar.classList.add('open');
        sidebarOverlay.classList.add('show');
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            closeSidebar();
        }
    });

    const toastContainer = document.getElementById('toastContainer');

    function getToastIcon(type) {
        switch (type) {
            case 'success': return 'fas fa-circle-check';
            case 'warning': return 'fas fa-triangle-exclamation';
            case 'danger': return 'fas fa-circle-xmark';
            default: return 'fas fa-circle-info';
        }
    }

    function showToast(title, message = '', type = 'info', duration = 4000) {
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

        closeBtn.addEventListener('click', removeToast);
        setTimeout(removeToast, duration);
    }

    const searchInput = document.getElementById('searchInput');
    const searchItems = document.querySelectorAll('.search-item');
    const emptySearchState = document.getElementById('emptySearchState');

    function normalizeText(text) {
        return (text || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function applySearch() {
        if (!searchInput) return;

        const term = normalizeText(searchInput.value.trim());
        let visibleCount = 0;

        searchItems.forEach((item) => {
            const haystack = normalizeText(item.dataset.search || '');
            const matches = term === '' || haystack.includes(term);

            item.classList.toggle('hidden-row', !matches);

            if (matches) visibleCount++;
        });

        if (emptySearchState) {
            emptySearchState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applySearch);
    }

    let deleteUrl = '';
    const confirmModal = document.getElementById('confirmModal');
    const confirmMessage = document.getElementById('confirmMessage');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    function confirmarEliminar(id, nombre) {
        deleteUrl = 'eliminar.php?id=' + id;

        if (confirmMessage) {
            confirmMessage.textContent = '¿Estás seguro de eliminar "' + nombre + '"? Esta acción no se puede deshacer.';
        }

        if (confirmModal) {
            confirmModal.classList.add('show');
        }
    }

    function cerrarConfirmacion() {
        if (confirmModal) {
            confirmModal.classList.remove('show');
        }
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', cerrarConfirmacion);
    }

    if (confirmModal) {
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                cerrarConfirmacion();
            }
        });
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', () => {
            if (deleteUrl) {
                window.location.href = deleteUrl;
            }
        });
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarConfirmacion();
            closeSidebar();
        }
    });

    window.addEventListener('load', () => {
        <?php if (!empty($mensaje)): ?>
        showToast(
            <?php echo json_encode($tipo_mensaje === 'danger' ? 'Atención' : 'Operación exitosa'); ?>,
            <?php echo json_encode($mensaje); ?>,
            <?php echo json_encode($tipo_mensaje); ?>,
            3500
        );
        <?php else: ?>
        showToast(
            'Módulo de materiales listo',
            'Puedes buscar, editar o eliminar registros desde esta vista.',
            'info',
            3200
        );
        <?php endif; ?>
    });
</script>
</body>
</html>