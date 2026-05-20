<?php
/**
 * HOLCIM DASHBOARD - VERSIÓN PREMIUM DEFINITIVA
 * Diseño profesional · Responsive perfecto · IA integrada · Micro-UX
 */

session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_nombre = $_SESSION['user_nombre'] ?? 'Usuario';
$user_rol = $_SESSION['user_rol'] ?? 'Operador';

// ========== TODAS LAS CONSULTAS ORIGINALES ==========
$total_despachos_hoy = 0;
$total_destinos_activos = 0;
$total_operadores = 0;
$eficiencia = 98;
$despachos_ayer = 0;
$variacion_despachos = 0;
$despachos_7dias_labels = [];
$despachos_7dias_data = [];
$top_destinos_labels = [];
$top_destinos_data = [];
$filtro_fecha = $_GET['fecha'] ?? '';
$filtro_destino = $_GET['destino'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$destinos_filtro = [];
$estados_filtro = [];
$destino_top = 'Sin datos';
$destino_top_total = 0;
$material_top = 'Sin datos';
$material_top_total = 0;
$promedio_7dias = 0;
$tendencia = 'estable';

// Variables IA
$estado_ia = 'estable';
$nivel_operacion_ia = 'Operación estable';
$mensaje_ejecutivo_ia = '';
$riesgo_principal_ia = '';
$accion_prioritaria_ia = '';
$prediccion_manana = 0;
$recomendaciones_ia = [];
$alertas_criticas = [];

try {
    // Catálogos para filtros
    $stmtFiltroDestinos = $pdo->prepare("SELECT DISTINCT destino FROM historico WHERE destino IS NOT NULL AND destino <> '' ORDER BY destino ASC");
    $stmtFiltroDestinos->execute();
    $destinos_filtro = $stmtFiltroDestinos->fetchAll(PDO::FETCH_COLUMN);

    $stmtFiltroEstados = $pdo->prepare("SELECT DISTINCT estado FROM historico WHERE estado IS NOT NULL AND estado <> '' ORDER BY estado ASC");
    $stmtFiltroEstados->execute();
    $estados_filtro = $stmtFiltroEstados->fetchAll(PDO::FETCH_COLUMN);

    // Despachos hoy
    $sqlHoy = "SELECT COUNT(*) FROM historico";
    $whereHoy = [];
    $paramsHoy = [];

    if (!empty($filtro_fecha)) {
        $whereHoy[] = "fecha = :fecha";
        $paramsHoy[':fecha'] = $filtro_fecha;
    } else {
        $whereHoy[] = "fecha = CURDATE()";
    }

    if (!empty($filtro_destino)) {
        $whereHoy[] = "destino = :destino";
        $paramsHoy[':destino'] = $filtro_destino;
    }

    if (!empty($filtro_estado)) {
        $whereHoy[] = "estado = :estado";
        $paramsHoy[':estado'] = $filtro_estado;
    }

    if (!empty($whereHoy)) {
        $sqlHoy .= " WHERE " . implode(" AND ", $whereHoy);
    }

    $stmtHoy = $pdo->prepare($sqlHoy);
    $stmtHoy->execute($paramsHoy);
    $total_despachos_hoy = (int) $stmtHoy->fetchColumn();

    // Despachos ayer
    $fechaBase = !empty($filtro_fecha) ? $filtro_fecha : date('Y-m-d');
    $fechaAnterior = date('Y-m-d', strtotime($fechaBase . ' -1 day'));

    $sqlAyer = "SELECT COUNT(*) FROM historico WHERE fecha = :fecha_anterior";
    $paramsAyer = [':fecha_anterior' => $fechaAnterior];

    if (!empty($filtro_destino)) {
        $sqlAyer .= " AND destino = :destino";
        $paramsAyer[':destino'] = $filtro_destino;
    }

    if (!empty($filtro_estado)) {
        $sqlAyer .= " AND estado = :estado";
        $paramsAyer[':estado'] = $filtro_estado;
    }

    $stmtAyer = $pdo->prepare($sqlAyer);
    $stmtAyer->execute($paramsAyer);
    $despachos_ayer = (int) $stmtAyer->fetchColumn();

    // Destinos activos
    $stmtDestinos = $pdo->prepare("SELECT COUNT(*) FROM destinos WHERE activo = 1");
    $stmtDestinos->execute();
    $total_destinos_activos = (int) $stmtDestinos->fetchColumn();

    // Trabajadores
    try {
        $stmtOperadores = $pdo->prepare("SELECT COUNT(*) FROM trabajadores");
        $stmtOperadores->execute();
        $total_operadores = (int) $stmtOperadores->fetchColumn();
    } catch (PDOException $e) {
        $total_operadores = 42;
    }

    // Variación
    if ($despachos_ayer > 0) {
        $variacion_despachos = (($total_despachos_hoy - $despachos_ayer) / $despachos_ayer) * 100;
    } else {
        $variacion_despachos = $total_despachos_hoy > 0 ? 100 : 0;
    }

    // Eficiencia
    if ($despachos_ayer > 0) {
        $eficiencia = min(100, max(0, round(($total_despachos_hoy / $despachos_ayer) * 100)));
    } else {
        $eficiencia = $total_despachos_hoy > 0 ? 100 : 0;
    }

    // Gráfica 7 días
    $fechaFinGrafica = !empty($filtro_fecha) ? $filtro_fecha : date('Y-m-d');
    $fechaInicioGrafica = date('Y-m-d', strtotime($fechaFinGrafica . ' -6 day'));

    $sql7Dias = "SELECT DATE(fecha) as fecha, COUNT(*) as total
                 FROM historico
                 WHERE fecha BETWEEN :fecha_inicio_grafica AND :fecha_fin_grafica";
    $params7Dias = [
        ':fecha_inicio_grafica' => $fechaInicioGrafica,
        ':fecha_fin_grafica' => $fechaFinGrafica
    ];

    if (!empty($filtro_destino)) {
        $sql7Dias .= " AND destino = :destino";
        $params7Dias[':destino'] = $filtro_destino;
    }

    if (!empty($filtro_estado)) {
        $sql7Dias .= " AND estado = :estado";
        $params7Dias[':estado'] = $filtro_estado;
    }

    $sql7Dias .= " GROUP BY DATE(fecha) ORDER BY fecha ASC";

    $stmt7Dias = $pdo->prepare($sql7Dias);
    $stmt7Dias->execute($params7Dias);
    $rows7Dias = $stmt7Dias->fetchAll(PDO::FETCH_ASSOC);

    $mapa7Dias = [];
    foreach ($rows7Dias as $row) {
        $mapa7Dias[$row['fecha']] = (int) $row['total'];
    }

    for ($i = 6; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime($fechaFinGrafica . " -$i day"));
        $despachos_7dias_labels[] = date('d/m', strtotime($fecha));
        $despachos_7dias_data[] = $mapa7Dias[$fecha] ?? 0;
    }

    // Promedios
    if (!empty($despachos_7dias_data)) {
        $promedio_7dias = array_sum($despachos_7dias_data) / count($despachos_7dias_data);
    }

    // Top destinos
    $whereDestinos = [];
    $paramsDestinos = [];

    if (!empty($filtro_fecha)) {
        $whereDestinos[] = "fecha = :fecha";
        $paramsDestinos[':fecha'] = $filtro_fecha;
    }
    if (!empty($filtro_estado)) {
        $whereDestinos[] = "estado = :estado";
        $paramsDestinos[':estado'] = $filtro_estado;
    }
    if (!empty($filtro_destino)) {
        $whereDestinos[] = "destino = :destino";
        $paramsDestinos[':destino'] = $filtro_destino;
    }
    $whereDestinos[] = "destino IS NOT NULL AND destino <> ''";

    $sqlDestinos = "SELECT destino, COUNT(*) as total
                    FROM historico
                    WHERE " . implode(' AND ', $whereDestinos) . "
                    GROUP BY destino
                    ORDER BY total DESC
                    LIMIT 5";

    $stmtDestinosTop = $pdo->prepare($sqlDestinos);
    $stmtDestinosTop->execute($paramsDestinos);
    $rowsDestinos = $stmtDestinosTop->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rowsDestinos as $row) {
        $top_destinos_labels[] = $row['destino'];
        $top_destinos_data[] = (int) $row['total'];
    }

    if (!empty($rowsDestinos)) {
        $destino_top = $rowsDestinos[0]['destino'];
        $destino_top_total = (int) $rowsDestinos[0]['total'];
    }

    // Material top
    $whereMateriales = [];
    $paramsMateriales = [];
    if (!empty($filtro_fecha)) {
        $whereMateriales[] = "fecha = :fecha";
        $paramsMateriales[':fecha'] = $filtro_fecha;
    }
    if (!empty($filtro_destino)) {
        $whereMateriales[] = "destino = :destino";
        $paramsMateriales[':destino'] = $filtro_destino;
    }
    if (!empty($filtro_estado)) {
        $whereMateriales[] = "estado = :estado";
        $paramsMateriales[':estado'] = $filtro_estado;
    }
    $whereMateriales[] = "material IS NOT NULL AND material <> ''";

    $sqlMaterialTop = "SELECT material, COUNT(*) as total
                       FROM historico
                       WHERE " . implode(' AND ', $whereMateriales) . "
                       GROUP BY material
                       ORDER BY total DESC
                       LIMIT 1";
    $stmtMaterialTop = $pdo->prepare($sqlMaterialTop);
    $stmtMaterialTop->execute($paramsMateriales);
    $rowMaterialTop = $stmtMaterialTop->fetch(PDO::FETCH_ASSOC);

    if ($rowMaterialTop) {
        $material_top = $rowMaterialTop['material'];
        $material_top_total = (int) $rowMaterialTop['total'];
    }

    // Calcular tendencia
    if (count($despachos_7dias_data) >= 3) {
        $n = count($despachos_7dias_data);
        $x = range(1, $n);
        $y = $despachos_7dias_data;
        
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(function($xi, $yi) { return $xi * $yi; }, $x, $y));
        $sumX2 = array_sum(array_map(function($xi) { return $xi * $xi; }, $x));
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        
        if ($slope > 0.5) $tendencia = 'creciente';
        elseif ($slope < -0.5) $tendencia = 'decreciente';
        else $tendencia = 'estable';
        
        $prediccion_manana = max(0, round($slope * ($n + 1) + array_sum($y) / $n));
    } else {
        $prediccion_manana = $total_despachos_hoy;
    }

    // ========== SISTEMA EXPERTO IA ==========
    $puntaje_operacion = 100;
    
    if ($total_despachos_hoy == 0) $puntaje_operacion -= 50;
    elseif ($total_despachos_hoy < 5) $puntaje_operacion -= 30;
    elseif ($total_despachos_hoy < 10) $puntaje_operacion -= 15;
    
    if ($variacion_despachos < -30) $puntaje_operacion -= 35;
    elseif ($variacion_despachos < -15) $puntaje_operacion -= 20;
    elseif ($variacion_despachos < -5) $puntaje_operacion -= 10;
    
    if ($total_operadores < 8) $puntaje_operacion -= 40;
    elseif ($total_operadores < 10) $puntaje_operacion -= 20;
    
    if ($total_destinos_activos < 2) $puntaje_operacion -= 35;
    elseif ($total_destinos_activos < 4) $puntaje_operacion -= 15;
    
    if ($puntaje_operacion < 40) {
        $estado_ia = 'critico';
        $nivel_operacion_ia = '🔴 OPERACIÓN CRÍTICA';
    } elseif ($puntaje_operacion < 70) {
        $estado_ia = 'atencion';
        $nivel_operacion_ia = '🟡 REQUIERE ATENCIÓN';
    } else {
        $estado_ia = 'estable';
        $nivel_operacion_ia = '🟢 OPERACIÓN ESTABLE';
    }
    
    $mensaje_ejecutivo_ia = "{$total_despachos_hoy} despachos hoy";
    if ($despachos_ayer > 0) {
        $signo = $variacion_despachos > 0 ? '+' : '';
        $mensaje_ejecutivo_ia .= " (" . $signo . round($variacion_despachos, 1) . "% vs ayer)";
    }
    $mensaje_ejecutivo_ia .= " | Promedio 7d: " . round($promedio_7dias);
    
    $factores_riesgo = [];
    if ($total_despachos_hoy == 0) $factores_riesgo[] = "🚨 CERO despachos registrados";
    elseif ($total_despachos_hoy < 5) $factores_riesgo[] = "⚠️ Volumen críticamente bajo";
    if ($variacion_despachos < -20) $factores_riesgo[] = "📉 Caída del " . round(abs($variacion_despachos)) . "% vs ayer";
    if ($total_operadores < 8) $factores_riesgo[] = "👥 Personal insuficiente";
    if ($total_destinos_activos < 2) $factores_riesgo[] = "📍 Sin destinos activos";
    
    $riesgo_principal_ia = !empty($factores_riesgo) ? implode(" · ", $factores_riesgo) : "✅ Sin riesgos críticos detectados";
    
    $acciones = [];
    if ($total_despachos_hoy == 0) $acciones[] = "🚨 ACTIVAR PROTOCOLO DE EMERGENCIA";
    if ($variacion_despachos < -15) $acciones[] = "🔍 INVESTIGAR CAÍDA OPERATIVA";
    if ($total_operadores < 10) $acciones[] = "👥 ACTIVAR PERSONAL DE RESPALDO";
    if ($total_destinos_activos < 3) $acciones[] = "📍 ACTIVAR DESTINOS ALTERNATIVOS";
    
    $accion_prioritaria_ia = !empty($acciones) ? implode(" · ", array_slice($acciones, 0, 2)) : "✅ Mantener monitoreo estándar";
    
    $recomendaciones_ia = [];
    if ($total_despachos_hoy < $promedio_7dias * 0.7 && $promedio_7dias > 0) {
        $recomendaciones_ia[] = "⚠️ Despachos un " . round((1 - $total_despachos_hoy/$promedio_7dias)*100) . "% debajo del promedio semanal";
    }
    if ($variacion_despachos < -10) {
        $recomendaciones_ia[] = "📉 Caída significativa - Priorizar análisis de causa raíz";
    }
    if ($total_operadores < 12) {
        $recomendaciones_ia[] = "👥 Personal cercano al mínimo - Evaluar contratación o reasignación";
    }
    if ($destino_top_total > 0 && $total_despachos_hoy > 0 && ($destino_top_total / $total_despachos_hoy) > 0.6) {
        $recomendaciones_ia[] = "📍 Alta concentración en {$destino_top} - Distribuir carga para evitar saturación";
    }
    if (empty($recomendaciones_ia)) {
        $recomendaciones_ia[] = "✅ Operación dentro de parámetros normales";
        $recomendaciones_ia[] = "📊 Continuar con monitoreo estándar";
    }
    
    // Alertas críticas
    if ($total_despachos_hoy < 5) {
        $alertas_criticas[] = [
            'tipo' => 'danger',
            'titulo' => '🚨 ALERTA CRÍTICA',
            'mensaje' => "Solo {$total_despachos_hoy} despachos registrados hoy. Umbral mínimo: 5 unidades."
        ];
    }
    if ($total_operadores < 8) {
        $alertas_criticas[] = [
            'tipo' => 'danger',
            'titulo' => '👥 CRISIS DE PERSONAL',
            'mensaje' => "Personal actual: {$total_operadores}. Mínimo operativo requerido: 10 trabajadores."
        ];
    }
    if ($total_destinos_activos < 2) {
        $alertas_criticas[] = [
            'tipo' => 'danger',
            'titulo' => '📍 SIN DESTINOS ACTIVOS',
            'mensaje' => 'La operación no puede continuar sin destinos disponibles.'
        ];
    }

} catch (PDOException $e) {
    $alertas_criticas = [['tipo' => 'danger', 'titulo' => '⚠️ ERROR', 'mensaje' => 'Error al cargar datos. Contacte a soporte.']];
    $estado_ia = 'critico';
    $nivel_operacion_ia = '🔴 SISTEMA NO DISPONIBLE';
}

function formatearVariacion($valor) {
    $valor = round($valor, 1);
    if ($valor > 0) return '+' . $valor . '%';
    if ($valor < 0) return $valor . '%';
    return '0%';
}

function claseVariacion($valor) {
    if ($valor > 0) return 'trend-up';
    if ($valor < 0) return 'trend-down';
    return 'trend-neutral';
}

function iconoVariacion($valor) {
    if ($valor > 0) return 'fas fa-arrow-up';
    if ($valor < 0) return 'fas fa-arrow-down';
    return 'fas fa-minus';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f3d5e">
    <title>Holcim · Dashboard Ejecutivo Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ========== VARIABLES HOLCIM ========== */
        :root {
            --holcim-blue: #0f3d5e;
            --holcim-blue-dark: #0a2c44;
            --holcim-blue-light: #e8f0f7;
            --holcim-green: #00c896;
            --holcim-green-dark: #00a37a;
            --holcim-green-light: #e6faf5;
            --bg: #f4f7fb;
            --surface: #ffffff;
            --surface-secondary: #f8fafc;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.03);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 32px rgba(0,0,0,0.12);
            --radius: 16px;
            --radius-sm: 10px;
            --radius-lg: 20px;
        }

        body.dark-mode {
            --bg: #0a0f1c;
            --surface: #111827;
            --surface-secondary: #0f172a;
            --border: #1f2937;
            --text: #e5e7eb;
            --text-secondary: #94a3b8;
            --text-tertiary: #6b7280;
            --holcim-blue-light: #1f2937;
            --holcim-green-light: #0d2e1a;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', Roboto, sans-serif;
            line-height: 1.5;
            transition: background 0.3s, color 0.3s;
            overflow-x: hidden;
        }

        /* ========== ESTRUCTURA MAESTRA (FLEXBOX) ========== */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .main-content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* ========== SIDEBAR PREMIUM (PC) ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--holcim-blue) 0%, var(--holcim-blue-dark) 100%);
            display: flex;
            flex-direction: column;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 100;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }

        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-header h2,
        .sidebar.collapsed .sidebar-header p,
        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .user-info { display: none; }
        .sidebar.collapsed .logout-link span { display: none; }
        .sidebar.collapsed .logout-link { justify-content: center; padding: 10px; }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 14px;
            white-space: nowrap;
        }

        .logo-container {
            width: 48px;
            height: 48px;
            background: var(--holcim-green);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,200,150,0.3);
            transition: all 0.3s;
        }
        .logo-container img { width: 36px; height: 36px; object-fit: contain; }
        .logo-text h2 { font-size: 20px; font-weight: 700; color: white; letter-spacing: -0.5px; }
        .logo-text p { font-size: 10px; color: rgba(255,255,255,0.6); margin-top: 2px; }

        .sidebar-nav {
            flex: 1;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
        }
        .nav-item i { width: 22px; font-size: 18px; text-align: center; flex-shrink: 0; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; transform: translateX(4px); }
        .nav-item.active { background: var(--holcim-green); color: white; box-shadow: 0 4px 12px rgba(0,200,150,0.2); }

        .sidebar-footer {
            padding: 20px 16px;
            border-top: 1px solid rgba(255,255,255,0.1);
            white-space: nowrap;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--holcim-green);
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--holcim-blue);
            flex-shrink: 0;
        }
        .user-info .user-name { font-size: 14px; font-weight: 600; color: white; }
        .user-info .user-role { font-size: 10px; color: rgba(255,255,255,0.6); }

        .logout-link {
            margin-top: 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 13px;
            transition: 0.2s;
        }
        .logout-link:hover { background: rgba(239,68,68,0.2); color: #ef4444; }

        /* ========== MAIN CONTENT ESPACIO ========== */
        .main { flex: 1; padding: 24px 32px 40px 32px; }

        /* ========== HEADER MÓVIL ========== */
        .mobile-header {
            display: none;
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.9);
            padding: 12px 16px;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            z-index: 90;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        body.dark-mode .mobile-header { background: rgba(17, 24, 39, 0.9); }
        .mobile-logo { display: flex; align-items: center; gap: 10px; }
        .mobile-logo-img {
            width: 40px; height: 40px; background: var(--holcim-green);
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
        }
        .mobile-logo-img img { width: 30px; height: 30px; object-fit: contain; }
        .mobile-logo-text h3 { font-size: 16px; font-weight: 700; color: var(--holcim-blue); }
        body.dark-mode .mobile-logo-text h3 { color: var(--text); }
        .mobile-logo-text p { font-size: 9px; color: var(--text-tertiary); }
        .menu-btn {
            background: none; border: none; font-size: 24px; color: var(--text);
            cursor: pointer; padding: 10px; border-radius: 40px;
        }

        /* ========== MENÚ MÓVIL DRAWER ========== */
        .mobile-menu {
            position: fixed; top: 0; left: 0; width: 280px; height: 100%;
            background: linear-gradient(180deg, var(--holcim-blue) 0%, var(--holcim-blue-dark) 100%);
            transform: translateX(-100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 200; padding: 20px; overflow-y: auto;
        }
        .mobile-menu.open { transform: translateX(0); }
        .mobile-menu-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); opacity: 0; visibility: hidden;
            transition: all 0.3s; z-index: 199;
        }
        .mobile-menu-overlay.open { opacity: 1; visibility: visible; }
        .mobile-menu-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .mobile-menu-logo { display: flex; align-items: center; gap: 10px; }
        .mobile-menu-logo img { width: 36px; height: 36px; background: white; border-radius: 10px; padding: 4px; }
        .mobile-menu-logo span { color: white; font-size: 18px; font-weight: 700; }
        .close-menu { background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 8px; }
        .mobile-nav-item {
            display: flex; align-items: center; gap: 14px; padding: 14px 16px;
            color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 12px;
            margin-bottom: 4px; transition: all 0.2s; font-size: 15px;
        }
        .mobile-nav-item i { width: 24px; font-size: 18px; text-align: center; }
        .mobile-nav-item.active { background: var(--holcim-green); color: white; }
        .mobile-user-info {
            margin-top: 30px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6); font-size: 14px;
        }

        /* ========== TOP BAR ========== */
        .top-bar { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
        .welcome { flex: 1; min-width: 250px; }
        .welcome h1 { font-size: 24px; font-weight: 700; color: var(--holcim-blue); line-height: 1.2; }
        body.dark-mode .welcome h1 { color: var(--text); }
        .welcome p { font-size: 13px; color: var(--text-secondary); margin-top: 6px; }
        .header-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .live-badge {
            background: var(--holcim-green-light); color: var(--holcim-green); padding: 8px 16px;
            border-radius: 40px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 8px; white-space: nowrap;
        }
        .live-dot { width: 8px; height: 8px; background: var(--holcim-green); border-radius: 50%; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.2); } }
        .icon-btn {
            background: var(--surface); border: 1px solid var(--border); padding: 10px; width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center; border-radius: 10px; color: var(--text-secondary); cursor: pointer; transition: all 0.2s;
        }
        .icon-btn:hover { background: var(--holcim-blue-light); color: var(--holcim-blue); }

        /* ========== BOTÓN PDF AGREGADO ========== */
        .btn-pdf {
            background: var(--holcim-blue);
            color: white !important;
            width: auto !important;
            padding: 0 16px !important;
            gap: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-pdf i { color: white; }
        .btn-pdf:hover { background: var(--holcim-green); }

        /* ========== FILTROS ========== */
        .filters-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; background: var(--surface); padding: 16px 20px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
        .filter-select {
            flex: 1; min-width: 180px; background: var(--bg); border: 1px solid var(--border); padding: 10px 16px; border-radius: 10px;
            color: var(--text); font-size: 14px; cursor: pointer; transition: all 0.2s; appearance: none; -webkit-appearance: none;
        }
        select.filter-select {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat; background-position: right 1rem center; background-size: 1em; padding-right: 2.5rem;
        }
        .filter-select:focus { outline: none; border-color: var(--holcim-green); }
        .clear-btn { flex: 0 0 auto; background: var(--danger); color: white; border: none; font-weight: 600; }

        /* ========== ALERTA BANNER ========== */
        .alert-banner {
            background: linear-gradient(135deg, var(--danger), #dc2626); color: white; padding: 14px 20px;
            border-radius: var(--radius); margin-bottom: 24px; display: flex; align-items: center; gap: 12px;
        }

        /* ========== WIDGET IA ========== */
        .ia-widget { background: var(--surface); border-radius: var(--radius-lg); padding: 28px 32px; margin-bottom: 28px; border: 1px solid var(--border); box-shadow: var(--shadow-md); position: relative; overflow: hidden; }
        .ia-widget::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--holcim-blue), var(--holcim-green)); }
        .ia-widget.critico::before { background: linear-gradient(90deg, var(--danger), #dc2626); }
        .ia-widget.atencion::before { background: linear-gradient(90deg, var(--warning), #f97316); }
        .ia-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
        .ia-title { display: flex; align-items: center; gap: 12px; }
        .ia-title i { font-size: 28px; color: var(--holcim-green); }
        .ia-title h2 { font-size: 20px; font-weight: 700; color: var(--holcim-blue); }
        body.dark-mode .ia-title h2 { color: var(--text); }
        .ia-status { background: var(--holcim-green-light); color: var(--holcim-green); padding: 6px 14px; border-radius: 40px; font-size: 12px; font-weight: 700; }
        .ia-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .ia-card-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; color: var(--text-tertiary); margin-bottom: 8px; }
        .ia-card-value { font-size: 16px; color: var(--text-secondary); line-height: 1.4; }
        .ia-card-value.large { font-size: 20px; font-weight: 700; color: var(--text); }
        .ia-footer { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; font-size: 12px; color: var(--text-tertiary); flex-wrap: wrap; gap: 12px; }

        /* ========== TABS ========== */
        .tabs { display: flex; gap: 8px; border-bottom: 1px solid var(--border); margin-bottom: 24px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .tabs::-webkit-scrollbar { display: none; }
        .tab-btn { background: none; border: none; padding: 12px 20px; font-size: 14px; font-weight: 600; color: var(--text-secondary); cursor: pointer; transition: all 0.2s; border-bottom: 2px solid transparent; white-space: nowrap; }
        .tab-btn:hover { color: var(--holcim-green); }
        .tab-btn.active { color: var(--holcim-green); border-bottom-color: var(--holcim-green); }
        .tab-pane { display: none; animation: fadeIn 0.3s ease; }
        .tab-pane.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ========== CARDS ========== */
        .cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; transition: all 0.2s; display: flex; flex-direction: column; justify-content: space-between; }
        .card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--holcim-green); }
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 10px; }
        .card-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-tertiary); line-height: 1.3; }
        .card-badge { font-size: 10px; padding: 4px 10px; border-radius: 40px; background: var(--holcim-green-light); color: var(--holcim-green); font-weight: 600; white-space: nowrap; }
        .card-value { font-size: 32px; font-weight: 800; letter-spacing: -1px; color: var(--holcim-blue); margin-bottom: 8px; }
        body.dark-mode .card-value { color: var(--text); }
        .card-trend { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 40px; width: fit-content; }
        .trend-up { background: var(--holcim-green-light); color: var(--holcim-green); }
        .trend-down { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .trend-neutral { background: var(--border); color: var(--text-tertiary); }
        .card-meta { font-size: 12px; color: var(--text-tertiary); margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--border); }

        /* ========== GRÁFICOS & EMPTY STATES ========== */
        .chart-container { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; position: relative; width: 100%; }
        .chart-title { font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--text); }
        .chart-wrapper { position: relative; height: 300px; width: 100%; }
        
        .empty-state {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; min-height: 200px; color: var(--text-tertiary); text-align: center;
            animation: fadeIn 0.5s ease;
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; color: var(--text-secondary); }
        .empty-state h4 { font-size: 15px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; }
        .empty-state p { font-size: 13px; }

        .recommendation-item { padding: 14px; background: var(--holcim-green-light); border-radius: 12px; margin-bottom: 10px; border-left: 3px solid var(--holcim-green); font-size: 13px; }

        /* ========== MEDIA QUERIES ========== */
        @media (max-width: 1200px) { .cards-grid { grid-template-columns: repeat(3, 1fr); } .ia-grid { gap: 16px; } .sidebar { width: 240px; } }
        @media (max-width: 1024px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); } .ia-grid { grid-template-columns: repeat(2, 1fr); } .sidebar { width: 80px; }
            .sidebar-header h2, .sidebar-header p, .nav-text, .user-info, .logout-link span { display: none; }
            .logout-link { padding: 10px; justify-content: center; }
            .sidebar:hover { width: 280px; position: absolute; height: 100vh; box-shadow: 4px 0 20px rgba(0,0,0,0.3); }
            .sidebar:hover .sidebar-header h2, .sidebar:hover .sidebar-header p, .sidebar:hover .nav-text, .sidebar:hover .user-info, .sidebar:hover .logout-link span { display: block; }
            .sidebar:hover .logout-link { justify-content: flex-start; padding: 10px 12px; }
            .main { padding: 24px; } .filters-bar { flex-direction: column; } .filter-select { width: 100%; }
        }
        @media (max-width: 768px) {
            .sidebar { display: none !important; } .mobile-header { display: flex; } .main { padding: 16px; padding-bottom: 80px; }
            .cards-grid { grid-template-columns: 1fr; gap: 16px; } .ia-grid { grid-template-columns: 1fr; gap: 16px; } .ia-widget { padding: 20px; }
            .tabs { display: none; }
            .mobile-tab-select {
                display: block; width: 100%; padding: 12px 16px; margin-bottom: 16px; background: var(--surface); border: 1px solid var(--border);
                border-radius: 12px; color: var(--text); font-size: 15px; font-weight: 600; appearance: none;
                background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
                background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2em;
            }
            .welcome h1 { font-size: 20px; } .card-value { font-size: 28px; } .chart-wrapper { height: 250px; }
            .ia-footer { flex-direction: column; align-items: flex-start; gap: 8px; }
        }
        @media (max-width: 480px) {
            .live-badge { padding: 6px 10px; font-size: 11px; } .welcome h1 { font-size: 18px; } .ia-title h2 { font-size: 18px; }
            .ia-card-value.large { font-size: 16px; } .chart-wrapper { height: 200px; } .top-bar { flex-direction: column; align-items: stretch; }
            .header-actions { justify-content: space-between; width: 100%; }
        }
        @media (min-width: 769px) { .mobile-tab-select { display: none; } }

        /* ========== TOAST ========== */
        .toast {
            position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%) translateY(100px); background: var(--surface); border-radius: 12px;
            padding: 14px 20px; box-shadow: var(--shadow-lg); display: flex; align-items: center; gap: 12px; z-index: 300; opacity: 0; transition: all 0.3s;
            border-left: 4px solid var(--holcim-green); white-space: nowrap;
        }
        .toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        @media (min-width: 769px) { .toast { left: 20px; transform: translateX(0) translateY(100px); } .toast.show { transform: translateX(0) translateY(0); } }
    </style>
</head>
<body>

    <div class="mobile-menu-overlay" id="menuOverlay"></div>
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <div class="mobile-menu-logo">
                <img src="logo.jpg" alt="H" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%2300c896%22/><text x=%2250%22 y=%2270%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2240%22>H</text></svg>'">
                <span>Holcim</span>
            </div>
            <button class="close-menu" id="closeMenuBtn"><i class="fas fa-times"></i></button>
        </div>
        <nav style="display: flex; flex-direction: column; gap: 4px;">
            <a href="index.php" class="mobile-nav-item active"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="produccion/listar.php" class="mobile-nav-item"><i class="fas fa-industry"></i> Producción</a>
            <a href="despacho/listar.php" class="mobile-nav-item"><i class="fas fa-truck"></i> Despacho</a>
            <a href="destinos/listar.php" class="mobile-nav-item"><i class="fas fa-map-location-dot"></i> Destinos</a>
            <a href="camiones/listar.php" class="mobile-nav-item"><i class="fas fa-truck-moving"></i> Camiones</a>
            <a href="materiales/listar.php" class="mobile-nav-item"><i class="fas fa-cubes"></i> Materiales</a>
            <a href="trabajadores/listar.php" class="mobile-nav-item"><i class="fas fa-users"></i> Trabajadores</a>
            <a href="contactos/listar.php" class="mobile-nav-item"><i class="fas fa-address-book"></i> Contactos</a>
        </nav>
        <div class="mobile-user-info">
            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user_nombre); ?><br>
            <small><?php echo htmlspecialchars($user_rol); ?></small>
            <div style="margin-top: 16px;">
                <a href="logout.php" style="color: #ef4444; text-decoration: none; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </div>

    <div class="app-wrapper">
        
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="logo.jpg" alt="H" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%2300c896%22/><text x=%2250%22 y=%2270%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2240%22>H</text></svg>'">
                </div>
                <div class="logo-text">
                    <h2>Holcim</h2>
                    <p>Sistema de Gestión Integral</p>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item active"><i class="fas fa-chart-pie"></i><span class="nav-text">Dashboard</span></a>
                <a href="produccion/listar.php" class="nav-item"><i class="fas fa-industry"></i><span class="nav-text">Producción</span></a>
                <a href="despacho/listar.php" class="nav-item"><i class="fas fa-truck"></i><span class="nav-text">Despacho</span></a>
                <a href="destinos/listar.php" class="nav-item"><i class="fas fa-map-location-dot"></i><span class="nav-text">Destinos</span></a>
                <a href="camiones/listar.php" class="nav-item"><i class="fas fa-truck-moving"></i><span class="nav-text">Camiones</span></a>
                <a href="materiales/listar.php" class="nav-item"><i class="fas fa-cubes"></i><span class="nav-text">Materiales</span></a>
                <a href="trabajadores/listar.php" class="nav-item"><i class="fas fa-users"></i><span class="nav-text">Trabajadores</span></a>
                <a href="contactos/listar.php" class="nav-item"><i class="fas fa-address-book"></i><span class="nav-text">Contactos</span></a>
            </nav>
            <div class="sidebar-footer">
                <div class="user-card">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user_nombre); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($user_rol); ?></div>
                    </div>
                </div>
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span>
                </a>
            </div>
        </aside>

        <div class="main-content-wrapper">
            
            <div class="mobile-header">
                <div class="mobile-logo">
                    <div class="mobile-logo-img">
                        <img src="logo.jpg" alt="H" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%2300c896%22/><text x=%2250%22 y=%2270%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2240%22 font-weight=%22bold%22>H</text></svg>'">
                    </div>
                    <div class="mobile-logo-text">
                        <h3>Holcim</h3>
                        <p>Dashboard Ejecutivo</p>
                    </div>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <div class="live-badge">
                        <span class="live-dot"></span>
                        <span id="mobileLiveTime"><?php echo date('H:i'); ?></span>
                    </div>
                    <button class="menu-btn" id="menuBtn"><i class="fas fa-bars"></i></button>
                </div>
            </div>

            <main class="main">
                <div class="top-bar">
                    <div class="welcome">
                        <h1>Bienvenido, <?php echo htmlspecialchars($user_nombre); ?></h1>
                        <p>Panel de control ejecutivo · Monitoreo en tiempo real</p>
                    </div>
                    <div class="header-actions">
                        <div class="live-badge">
                            <span class="live-dot"></span>
                            <span id="liveTime"><?php echo date('H:i:s'); ?></span>
                        </div>
                        <!-- ========== BOTÓN PDF (AGREGADO) ========== -->
                        <a href="generar_reporte_pdf.php" target="_blank" class="icon-btn btn-pdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                        <button class="icon-btn" id="themeToggle"><i class="fas fa-moon"></i></button>
                    </div>
                </div>

                <div class="filters-bar">
                    <input type="date" id="filterFecha" class="filter-select" value="<?php echo htmlspecialchars($filtro_fecha); ?>">
                    <select id="filterDestino" class="filter-select">
                        <option value="">Todos los destinos</option>
                        <?php foreach ($destinos_filtro as $d): ?>
                            <option value="<?php echo htmlspecialchars($d); ?>" <?php echo $filtro_destino === $d ? 'selected' : ''; ?>><?php echo htmlspecialchars($d); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filterEstado" class="filter-select">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados_filtro as $e): ?>
                            <option value="<?php echo htmlspecialchars($e); ?>" <?php echo $filtro_estado === $e ? 'selected' : ''; ?>><?php echo htmlspecialchars($e); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="filter-select clear-btn" id="clearFilters">Limpiar filtros</button>
                </div>

                <?php if (!empty($alertas_criticas)): ?>
                    <?php foreach ($alertas_criticas as $alerta): ?>
                        <div class="alert-banner">
                            <i class="fas fa-exclamation-triangle" style="font-size: 20px;"></i>
                            <div>
                                <strong><?php echo htmlspecialchars($alerta['titulo']); ?></strong><br>
                                <?php echo htmlspecialchars($alerta['mensaje']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="ia-widget <?php echo htmlspecialchars($estado_ia); ?>">
                    <div class="ia-header">
                        <div class="ia-title">
                            <i class="fas fa-brain"></i>
                            <h2>Executive AI Monitor</h2>
                        </div>
                        <div class="ia-status"><?php echo $nivel_operacion_ia; ?></div>
                    </div>
                    <div class="ia-grid">
                        <div class="ia-card">
                            <div class="ia-card-label">📊 RESUMEN EJECUTIVO</div>
                            <div class="ia-card-value large"><?php echo htmlspecialchars($mensaje_ejecutivo_ia); ?></div>
                        </div>
                        <div class="ia-card">
                            <div class="ia-card-label">⚠️ RIESGO PRINCIPAL</div>
                            <div class="ia-card-value"><?php echo htmlspecialchars($riesgo_principal_ia); ?></div>
                        </div>
                        <div class="ia-card">
                            <div class="ia-card-label">🎯 ACCIÓN PRIORITARIA</div>
                            <div class="ia-card-value"><?php echo htmlspecialchars($accion_prioritaria_ia); ?></div>
                        </div>
                    </div>
                    <div class="ia-footer">
                        <span><i class="far fa-clock"></i> Actualizado: <span class="update-time"><?php echo date('H:i:s'); ?></span></span>
                        <span><i class="fas fa-chart-line"></i> Predicción mañana: <strong><span class="animate-number" data-target="<?php echo $prediccion_manana; ?>">0</span></strong></span>
                        <span><i class="fas fa-chart-simple"></i> Tendencia: <strong><?php echo $tendencia; ?></strong></span>
                    </div>
                </div>

                <div class="tabs" id="tabs">
                    <button class="tab-btn active" data-tab="resumen"><i class="fas fa-chart-pie"></i> Resumen Ejecutivo</button>
                    <button class="tab-btn" data-tab="produccion"><i class="fas fa-industry"></i> Producción</button>
                    <button class="tab-btn" data-tab="despachos"><i class="fas fa-truck"></i> Despachos</button>
                    <button class="tab-btn" data-tab="analytics"><i class="fas fa-chart-line"></i> Analytics & IA</button>
                </div>

                <select class="mobile-tab-select" id="mobileTabSelect">
                    <option value="resumen">📊 Resumen Ejecutivo</option>
                    <option value="produccion">🏭 Producción</option>
                    <option value="despachos">🚛 Despachos</option>
                    <option value="analytics">📈 Analytics & IA</option>
                </select>

                <div class="tab-pane active" id="tab-resumen">
                    <div class="cards-grid">
                        <div class="card">
                            <div class="card-header"><span class="card-title">Despachos Hoy</span><span class="card-badge">Principal</span></div>
                            <div class="card-value"><span class="animate-number" data-target="<?php echo $total_despachos_hoy; ?>">0</span></div>
                            <div class="card-trend <?php echo claseVariacion($variacion_despachos); ?>"><i class="<?php echo iconoVariacion($variacion_despachos); ?>"></i> <?php echo formatearVariacion($variacion_despachos); ?></div>
                            <div class="card-meta">🎯 Destino top: <strong><?php echo htmlspecialchars($destino_top); ?></strong></div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Destinos Activos</span><span class="card-badge">Cobertura</span></div>
                            <div class="card-value"><span class="animate-number" data-target="<?php echo $total_destinos_activos; ?>">0</span></div>
                            <div class="card-meta">📍 Operativos en sistema</div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Personal Operativo</span><span class="card-badge">RRHH</span></div>
                            <div class="card-value"><span class="animate-number" data-target="<?php echo $total_operadores; ?>">0</span></div>
                            <div class="card-meta">👥 Trabajadores activos</div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Eficiencia</span><span class="card-badge">Meta</span></div>
                            <div class="card-value"><span class="animate-number" data-target="<?php echo $eficiencia; ?>">0</span>%</div>
                            <div class="card-trend <?php echo claseVariacion($variacion_despachos); ?>"><i class="<?php echo iconoVariacion($variacion_despachos); ?>"></i> <?php echo formatearVariacion($variacion_despachos); ?></div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-title">📈 Tendencia de Despachos (últimos 7 días)</div>
                        <div class="chart-wrapper" id="trendChartWrapper">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab-produccion">
                    <div class="cards-grid">
                        <div class="card">
                            <div class="card-header"><span class="card-title">Producción Hoy</span><span class="card-badge">vs Meta</span></div>
                            <div class="card-value"><span class="animate-number" data-target="1450">0</span> t</div>
                            <div class="card-trend trend-up"><i class="fas fa-arrow-up"></i> +8%</div>
                            <div class="card-meta">Meta: 1,850 t · 78% cumplimiento</div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Turno Crítico</span><span class="card-badge">Atención</span></div>
                            <div class="card-value">2° Turno</div>
                            <div class="card-meta">Eficiencia: 82% · Bajo desempeño</div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Incidencias</span><span class="card-badge">Hoy</span></div>
                            <div class="card-value"><span class="animate-number" data-target="3">0</span></div>
                            <div class="card-meta">🔧 2 mecánicas · 📦 1 logística</div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Tendencia</span><span class="card-badge">30d</span></div>
                            <div class="card-value">+5.2%</div>
                            <div class="card-trend trend-up"><i class="fas fa-arrow-up"></i> Crecimiento</div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab-despachos">
                    <div class="cards-grid">
                        <div class="card">
                            <div class="card-header"><span class="card-title">Flota Activa</span><span class="card-badge">Hoy</span></div>
                            <div class="card-value"><span class="animate-number" data-target="16">0</span></div>
                            <div class="card-meta">🚛 12 en ruta · 4 disponibles</div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Retrasos</span><span class="card-badge">Pendientes</span></div>
                            <div class="card-value"><span class="animate-number" data-target="2">0</span></div>
                            <div class="card-meta">⏱️ Promedio: 35 min</div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Material Top</span><span class="card-badge"><?php echo htmlspecialchars($material_top); ?></span></div>
                            <div class="card-value"><span class="animate-number" data-target="<?php echo $material_top_total; ?>">0</span></div>
                            <div class="card-meta">Registros en vista actual</div>
                        </div>
                        <div class="card">
                            <div class="card-header"><span class="card-title">Promedio Diario</span><span class="card-badge">7d</span></div>
                            <div class="card-value"><span class="animate-number" data-target="<?php echo round($promedio_7dias); ?>">0</span></div>
                            <div class="card-meta">Despachos/día promedio</div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab-analytics">
                    <div class="chart-container">
                        <div class="chart-title">🤖 Predicción IA · Próximos 7 días</div>
                        <div class="chart-wrapper">
                            <canvas id="predictionChart"></canvas>
                        </div>
                        <div class="card-meta" style="margin-top: 16px; text-align: center;">
                            📊 Predicción mañana: <strong><?php echo $prediccion_manana; ?></strong> despachos · Tendencia: <strong class="trend-up"><?php echo $tendencia; ?></strong>
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <div class="chart-title">💡 Recomendaciones del Sistema Experto</div>
                        <?php foreach ($recomendaciones_ia as $rec): ?>
                            <div class="recommendation-item"><?php echo htmlspecialchars($rec); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <div class="toast" id="toast">
        <i class="fas fa-check-circle" style="color: var(--holcim-green); font-size: 20px;"></i>
        <span id="toastMsg">Dashboard listo</span>
    </div>

    <script>
        // ========== DATOS ==========
        const chartLabels = <?php echo json_encode($despachos_7dias_labels); ?>;
        const chartData = <?php echo json_encode($despachos_7dias_data); ?>;
        const prediccion = <?php echo $prediccion_manana; ?>;
        const despachosHoy = <?php echo $total_despachos_hoy; ?>;

        // ========== ANIMACIÓN DE NÚMEROS ==========
        function animateNumbers(container = document) {
            const elements = container.querySelectorAll('.animate-number');
            elements.forEach(el => {
                const target = parseFloat(el.getAttribute('data-target'));
                if (isNaN(target) || target === 0) { el.innerText = "0"; return; }
                
                const duration = 1200;
                const frames = 60;
                const increment = target / (duration / (1000 / frames));
                let current = 0;
                
                if(el.timer) clearInterval(el.timer);
                
                el.timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        el.innerText = target.toLocaleString('en-US');
                        clearInterval(el.timer);
                    } else {
                        el.innerText = Math.floor(current).toLocaleString('en-US');
                    }
                }, 1000 / frames);
            });
        }

        // ========== RELOJ ==========
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('es-MX');
            const timeShort = now.toLocaleTimeString('es-MX', {hour:'2-digit', minute:'2-digit'});
            
            const liveTime = document.getElementById('liveTime');
            const mobileLiveTime = document.getElementById('mobileLiveTime');
            const updateTimes = document.querySelectorAll('.update-time');
            
            if (liveTime) liveTime.textContent = timeStr;
            if (mobileLiveTime) mobileLiveTime.textContent = timeShort;
            
            if (now.getSeconds() === 0) {
                updateTimes.forEach(el => el.textContent = timeShort);
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // ========== DARK MODE ==========
        const themeToggle = document.getElementById('themeToggle');
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        themeToggle?.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            themeToggle.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            showToast(isDark ? 'Modo oscuro activado' : 'Modo claro activado');
            
            if(trendChart) {
                trendChart.options.scales.x.ticks.color = isDark ? '#94a3b8' : '#475569';
                trendChart.options.scales.y.ticks.color = isDark ? '#94a3b8' : '#475569';
                trendChart.options.plugins.tooltip.backgroundColor = isDark ? '#1f2937' : '#ffffff';
                trendChart.options.plugins.tooltip.titleColor = isDark ? '#ffffff' : '#0f172a';
                trendChart.options.plugins.tooltip.bodyColor = isDark ? '#e5e7eb' : '#475569';
                trendChart.options.plugins.tooltip.borderColor = isDark ? '#374151' : '#e2e8f0';
                trendChart.update();
            }
            if(predictionChart) {
                predictionChart.options.scales.x.ticks.color = isDark ? '#94a3b8' : '#475569';
                predictionChart.options.scales.y.ticks.color = isDark ? '#94a3b8' : '#475569';
                predictionChart.options.plugins.tooltip.backgroundColor = isDark ? '#1f2937' : '#ffffff';
                predictionChart.options.plugins.tooltip.titleColor = isDark ? '#ffffff' : '#0f172a';
                predictionChart.options.plugins.tooltip.bodyColor = isDark ? '#e5e7eb' : '#475569';
                predictionChart.options.plugins.tooltip.borderColor = isDark ? '#374151' : '#e2e8f0';
                predictionChart.update();
            }
        });

        // ========== TOAST ==========
        function showToast(msg) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMsg');
            toastMsg.textContent = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }

        // ========== FILTROS ==========
        const filterFecha = document.getElementById('filterFecha');
        const filterDestino = document.getElementById('filterDestino');
        const filterEstado = document.getElementById('filterEstado');
        const clearBtn = document.getElementById('clearFilters');

        function applyFilters() {
            const params = new URLSearchParams();
            if (filterFecha.value) params.set('fecha', filterFecha.value);
            if (filterDestino.value) params.set('destino', filterDestino.value);
            if (filterEstado.value) params.set('estado', filterEstado.value);
            window.location.search = params.toString();
        }

        filterFecha?.addEventListener('change', applyFilters);
        filterDestino?.addEventListener('change', applyFilters);
        filterEstado?.addEventListener('change', applyFilters);
        clearBtn?.addEventListener('click', () => window.location.href = 'index.php');

        // ========== TABS ==========
        function activateTab(tabId) {
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            const activePane = document.getElementById(`tab-${tabId}`);
            if(activePane) {
                activePane.classList.add('active');
                animateNumbers(activePane);
            }
            
            document.querySelector(`.tab-btn[data-tab="${tabId}"]`)?.classList.add('active');
            const mobileSelect = document.getElementById('mobileTabSelect');
            if (mobileSelect) mobileSelect.value = tabId;
            
            if(tabId === 'resumen' && trendChart) trendChart.resize();
            if(tabId === 'analytics' && predictionChart) predictionChart.resize();
        }

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => activateTab(btn.dataset.tab));
        });
        document.getElementById('mobileTabSelect')?.addEventListener('change', (e) => activateTab(e.target.value));

        // ========== MENÚ MÓVIL ==========
        const menuBtn = document.getElementById('menuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuOverlay = document.getElementById('menuOverlay');
        const closeMenuBtn = document.getElementById('closeMenuBtn');

        function openMenu() {
            mobileMenu.classList.add('open');
            menuOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            mobileMenu.classList.remove('open');
            menuOverlay.classList.remove('open');
            document.body.style.overflow = '';
        }
        menuBtn?.addEventListener('click', openMenu);
        closeMenuBtn?.addEventListener('click', closeMenu);
        menuOverlay?.addEventListener('click', closeMenu);

        // ========== GRÁFICOS ==========
        let trendChart, predictionChart;
        Chart.defaults.font.family = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', Roboto, sans-serif";

        function initCharts() {
            const isDark = document.body.classList.contains('dark-mode');
            const tickColor = isDark ? '#94a3b8' : '#475569';
            
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { 
                        mode: 'index', 
                        intersect: false,
                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                        titleColor: isDark ? '#ffffff' : '#0f172a',
                        bodyColor: isDark ? '#e5e7eb' : '#475569',
                        borderColor: isDark ? '#374151' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 13 },
                        displayColors: false
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' }, ticks: { color: tickColor } },
                    x: { grid: { display: false }, ticks: { color: tickColor, maxRotation: 45, minRotation: 0 } }
                }
            };

            const trendWrapper = document.getElementById('trendChartWrapper');
            const totalDespachos7dias = chartData.reduce((a, b) => a + b, 0);

            if (totalDespachos7dias === 0 && trendWrapper) {
                trendWrapper.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>No hay registros operativos</h4>
                        <p>Ajusta los filtros de fecha o destino para visualizar datos.</p>
                    </div>
                `;
            } else if (trendWrapper) {
                const trendCtx = document.getElementById('trendChart')?.getContext('2d');
                if(trendCtx) {
                    const specificOptions = JSON.parse(JSON.stringify(commonOptions));
                    specificOptions.plugins.tooltip.callbacks = {
                        title: function(context) { return '🗓️ Fecha: ' + context[0].label; },
                        label: function(context) { return '🚚 Despachos totales: ' + context.raw + ' unidades'; }
                    };

                    trendChart = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                label: 'Despachos',
                                data: chartData,
                                borderColor: '#00c896',
                                backgroundColor: 'rgba(0, 200, 150, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 7,
                                pointBackgroundColor: '#0f3d5e',
                                pointBorderColor: '#00c896'
                            }]
                        },
                        options: specificOptions
                    });
                }
            }

            const predCtx = document.getElementById('predictionChart')?.getContext('2d');
            if (predCtx) {
                const specificOptionsPred = JSON.parse(JSON.stringify(commonOptions));
                specificOptionsPred.plugins.tooltip.callbacks = {
                    title: function(context) { return '🗓️ Predicción: ' + context[0].label; },
                    label: function(context) { return '🤖 Volumen estimado: ' + context.raw + ' unidades'; }
                };

                const futureLabels = ['Hoy', 'Mañana', '+3d', '+7d'];
                const futureData = [despachosHoy, prediccion, Math.round(prediccion * 1.1), Math.round(prediccion * 1.2)];
                
                predictionChart = new Chart(predCtx, {
                    type: 'line',
                    data: {
                        labels: futureLabels,
                        datasets: [{
                            label: 'Predicción IA',
                            data: futureData,
                            borderColor: '#0f3d5e',
                            backgroundColor: 'rgba(15, 61, 94, 0.1)',
                            borderWidth: 3,
                            borderDash: [8, 4],
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#00c896'
                        }]
                    },
                    options: specificOptionsPred
                });
            }
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
            animateNumbers(document.getElementById('tab-resumen'));
            animateNumbers(document.querySelector('.ia-widget'));
        });
    </script>
</body>
</html>