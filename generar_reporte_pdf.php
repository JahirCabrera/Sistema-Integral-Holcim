<?php
/**
 * GENERADOR DE PDF PROFESIONAL - HOLCIM
 * Diseño tipo consultoría estratégica / tesis de grado
 * @author Holcim Executive AI Monitor
 */

session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_nombre    = $_SESSION['user_nombre'] ?? 'Usuario';
$user_rol       = $_SESSION['user_rol'] ?? 'Operador';
$seccion        = $_GET['seccion'] ?? 'dashboard';
$filtro_fecha   = $_GET['fecha'] ?? '';
$filtro_destino = $_GET['destino'] ?? '';
$filtro_estado  = $_GET['estado'] ?? '';

require_once 'tcpdf/tcpdf.php';

// ==================================================================
// CLASE PDF EXTENDIDA CON ESTILO PROFESIONAL
// ==================================================================
class PDF_Tesis extends TCPDF {
    
    protected $fecha_reporte;
    protected $logo_path = ''; // Opcional: 'images/logo.png'
    
    public function __construct() {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->fecha_reporte = date('d/m/Y H:i:s');
        $this->SetCreator('Holcim System');
        $this->SetAuthor('Executive AI Monitor');
        $this->SetMargins(18, 45, 18);
        $this->SetAutoPageBreak(true, 25);
    }
    
    // Cabecera Corporativa
    public function Header() {
        // Línea superior de color institucional
        $this->SetDrawColor(0, 112, 150); // Azul Holcim
        $this->SetLineWidth(2);
        $this->Line(15, 5, 195, 5);
        
        // Título Principal
        $this->SetY(12);
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(0, 56, 92);
        $this->Cell(0, 6, 'INFORME EJECUTIVO HOLCIM', 0, 1, 'C');
        
        // Subtítulo
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, 'Sistema de Gestion Integral - Monitoreo Operativo', 0, 1, 'C');
        
        // Línea de cierre
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.2);
        $this->Line(15, 26, 195, 26);
        $this->SetY(34);
    }
    
    // Pie de página
    public function Footer() {
        $this->SetY(-18);
        $this->SetFont('helvetica', 'I', 7);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 4, 'Documento generado por Executive AI Monitor - ' . $this->fecha_reporte, 0, 0, 'L');
        $this->Cell(0, 4, 'Pag. ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
    
    // Título de sección estandarizado
    public function TituloSeccion($titulo, $icono = '') {
        $this->Ln(8);
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(0, 56, 92);
        $this->Cell(0, 6, (!empty($icono) ? $icono . ' ' : '') . strtoupper($titulo), 0, 1, 'L');
        $this->SetDrawColor(0, 112, 150);
        $this->SetLineWidth(0.5);
        $this->Line(18, $this->GetY(), 60, $this->GetY());
        $this->Ln(5);
    }
    
    // Tarjeta de KPI (4 por fila)
    public function KPI($x, $y, $w, $label, $value, $suffix = '', $trend = '') {
        $this->SetY($y);
        $this->SetX($x);
        $this->SetFillColor(245, 248, 250);
        $this->Rect($x, $y, $w, 26, 'DF');
        
        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(80, 80, 80);
        $this->SetXY($x + 4, $y + 3);
        $this->Cell($w - 8, 4, strtoupper($label), 0, 1, 'L');
        
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(0, 56, 92);
        $this->SetXY($x + 4, $y + 9);
        $this->Cell($w - 8, 7, $value . $suffix, 0, 1, 'L');
        
        if (!empty($trend)) {
            $this->SetFont('helvetica', 'I', 6);
            $this->SetTextColor(100, 100, 100);
            $this->SetXY($x + 4, $y + 18);
            $this->Cell($w - 8, 4, $trend, 0, 1, 'L');
        }
    }
    
    // Tabla Estilo Profesional
    public function TablaProfesional($headers, $data, $widths = null) {
        if (empty($data)) return;
        
        $html = '<style>
            .tabla-profesional {
                width: 100%;
                border-collapse: collapse;
                font-family: helvetica;
                font-size: 8pt;
                color: #333333;
            }
            .tabla-profesional th {
                background-color: #00385c;
                color: white;
                padding: 6px 4px;
                text-align: left;
                font-weight: bold;
            }
            .tabla-profesional td {
                border-bottom: 0.5px solid #dddddd;
                padding: 5px 4px;
            }
            .tabla-profesional tr:hover {
                background-color: #f5f8fa;
            }
            .estado-bueno { color: #27ae60; font-weight: bold; }
            .estado-regular { color: #f39c12; font-weight: bold; }
            .estado-malo { color: #e74c3c; font-weight: bold; }
        </style>
        <table class="tabla-profesional" cellpadding="2">';
        
        // Cabeceras
        $html .= '<thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        
        // Filas
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . $cell . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        $this->writeHTML($html, true, false, true, false, '');
    }
}

// ==================================================================
// INICIALIZAR PDF
// ==================================================================
$pdf = new PDF_Tesis();
$pdf->AddPage();

// ==================================================================
// METADATOS DEL REPORTE
// ==================================================================
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(100, 100, 100);
$filtros_texto = [];
if (!empty($filtro_fecha)) $filtros_texto[] = "Fecha: " . $filtro_fecha;
if (!empty($filtro_destino)) $filtros_texto[] = "Destino: " . $filtro_destino;
if (!empty($filtro_estado)) $filtros_texto[] = "Estado: " . $filtro_estado;
$linea_filtros = !empty($filtros_texto) ? "Filtros: " . implode(" | ", $filtros_texto) : "Filtros: Ninguno";

$pdf->Cell(0, 4, "Usuario: " . htmlspecialchars($user_nombre) . " (" . htmlspecialchars($user_rol) . ")", 0, 1, 'R');
$pdf->Cell(0, 4, "Sección: " . strtoupper($seccion), 0, 1, 'R');
$pdf->Cell(0, 4, $linea_filtros, 0, 1, 'R');
$pdf->Ln(6);

// ==================================================================
// OBTENER DATOS SEGÚN SECCIÓN
// ==================================================================
try {
    
    // --------------------------------------------------------------
    // 1. DASHBOARD (RESUMEN EJECUTIVO COMPLETO)
    // --------------------------------------------------------------
    if ($seccion == 'dashboard') {
        
        // --- Métricas Principales ---
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM historico WHERE fecha = CURDATE()");
        $stmt->execute(); $desp_hoy = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM historico WHERE fecha = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
        $stmt->execute(); $desp_ayer = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM destinos WHERE activo = 1");
        $stmt->execute(); $destinos_act = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM trabajadores");
        $stmt->execute(); $personal = (int)$stmt->fetchColumn();
        
        $variacion = ($desp_ayer > 0) ? round((($desp_hoy - $desp_ayer) / $desp_ayer) * 100, 1) : ($desp_hoy > 0 ? 100 : 0);
        $eficiencia = ($desp_ayer > 0) ? min(100, round(($desp_hoy / $desp_ayer) * 100)) : ($desp_hoy > 0 ? 100 : 0);
        
        $trend_texto = ($variacion > 0) ? "▲ +" . $variacion . "% vs ayer" : (($variacion < 0) ? "▼ " . $variacion . "% vs ayer" : "→ Estable");
        
        // Grid de KPIs
        $y_kpi = $pdf->GetY();
        $w_kpi = 42;
        $pdf->KPI(18, $y_kpi, $w_kpi, "Despachos Hoy", number_format($desp_hoy), "", $trend_texto);
        $pdf->KPI(18 + $w_kpi + 3, $y_kpi, $w_kpi, "Destinos Activos", number_format($destinos_act), "", "Cobertura operativa");
        $pdf->KPI(18 + ($w_kpi+3)*2, $y_kpi, $w_kpi, "Personal", number_format($personal), "", "Trabajadores activos");
        $pdf->KPI(18 + ($w_kpi+3)*3, $y_kpi, $w_kpi, "Eficiencia", number_format($eficiencia), "%", "vs meta semanal");
        $pdf->Ln(32);
        
        // --- Top Destino y Material ---
        $stmt = $pdo->query("SELECT destino, COUNT(*) as total FROM historico GROUP BY destino ORDER BY total DESC LIMIT 1");
        $top_dest = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->query("SELECT material, COUNT(*) as total FROM historico WHERE material IS NOT NULL GROUP BY material ORDER BY total DESC LIMIT 1");
        $top_mat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $pdf->TituloSeccion("Analisis de Volumen", "📊");
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 5, "Destino con mayor actividad:", 0, 1, 'L');
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 7, htmlspecialchars($top_dest['destino'] ?? 'N/A'), 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, number_format($top_dest['total'] ?? 0) . " despachos registrados", 0, 1, 'L');
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, "Material mas despachado:", 0, 1, 'L');
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 7, htmlspecialchars($top_mat['material'] ?? 'N/A'), 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, number_format($top_mat['total'] ?? 0) . " unidades movilizadas", 0, 1, 'L');
        $pdf->Ln(10);
        
        // --- Tendencia Semanal (Tabla) ---
        $stmt = $pdo->prepare("SELECT DATE(fecha) as fecha, COUNT(*) as total FROM historico WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY fecha ORDER BY fecha ASC");
        $stmt->execute();
        $semanales = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $semanales[$row['fecha']] = $row['total']; }
        
        $data_tabla = [];
        $totales = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i day"));
            $total = $semanales[$fecha] ?? 0;
            $totales[] = $total;
            $estado = ($total == 0) ? '<span class="estado-malo">Critico</span>' : (($total < 5) ? '<span class="estado-regular">Bajo</span>' : '<span class="estado-bueno">Normal</span>');
            $data_tabla[] = [date('d/m/Y', strtotime($fecha)), number_format($total), $estado];
        }
        $promedio = round(array_sum($totales) / 7);
        $data_tabla[] = ['<strong>PROMEDIO</strong>', '<strong>' . number_format($promedio) . '</strong>', '<strong>despachos/dia</strong>'];
        
        $pdf->TituloSeccion("Tendencia Semanal", "📈");
        $pdf->TablaProfesional(["Fecha", "Despachos", "Estado Operativo"], $data_tabla);
        $pdf->Ln(6);
        
        // --- Alertas y Recomendaciones ---
        $alertas = [];
        if ($desp_hoy < 5) $alertas[] = ['nivel' => 'critico', 'msg' => "Volumen critico: Solo {$desp_hoy} despachos hoy (minimo 5)"];
        if ($personal < 10) $alertas[] = ['nivel' => 'critico', 'msg' => "Personal insuficiente: {$personal} trabajadores activos (minimo 10)"];
        if ($destinos_act < 3) $alertas[] = ['nivel' => 'atencion', 'msg' => "Red de destinos limitada: {$destinos_act} destinos activos"];
        if ($variacion < -20) $alertas[] = ['nivel' => 'atencion', 'msg' => "Caida significativa del " . abs($variacion) . "% en volumen de despachos"];
        
        $pdf->TituloSeccion("Alertas Estrategicas", "⚠️");
        if (!empty($alertas)) {
            $html_alertas = '<style>.alert-critico { background-color: #fee2e2; border-left: 3px solid #dc2626; padding: 6px; margin: 4px 0; font-size: 9pt; } .alert-atencion { background-color: #fffbeb; border-left: 3px solid #f59e0b; padding: 6px; margin: 4px 0; font-size: 9pt; }</style>';
            foreach ($alertas as $a) {
                $clase = ($a['nivel'] == 'critico') ? 'alert-critico' : 'alert-atencion';
                $html_alertas .= '<div class="' . $clase . '">⚠️ ' . htmlspecialchars($a['msg']) . '</div>';
            }
            $pdf->writeHTML($html_alertas, true, false, true, false, '');
        } else {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, "No se detectaron alertas criticas en el periodo.", 0, 1, 'L');
        }
        $pdf->Ln(8);
        
        // --- Predicción IA y Recomendaciones ---
        $prediccion = max(0, $desp_hoy + round(($desp_hoy - $desp_ayer) / 2));
        $recs = [];
        if ($desp_hoy < $promedio && $promedio > 0) $recs[] = "• Incrementar frecuencia de despachos para alcanzar el promedio semanal de " . number_format($promedio) . " uds/dia";
        if ($personal < 12) $recs[] = "• Evaluar contratacion o activacion de personal de respaldo";
        if ($destinos_act < 4) $recs[] = "• Diversificar la red de destinos para reducir riesgo logistico";
        if (empty($recs)) $recs[] = "• La operacion se encuentra dentro de parametros normales. Mantener monitoreo continuo.";
        
        $pdf->TituloSeccion("Recomendaciones IA", "🤖");
        $pdf->SetFont('helvetica', '', 9);
        foreach ($recs as $r) { $pdf->Cell(0, 5, $r, 0, 1, 'L'); }
        $pdf->Ln(5);
        
        // Bloque de Predicción
        $pdf->SetFillColor(0, 56, 92);
        $pdf->Rect(18, $pdf->GetY(), 174, 28, 'F');
        $pdf->SetY($pdf->GetY() + 6);
        $pdf->SetX(25);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(80, 5, 'PREDICCION IA', 0, 1, 'L');
        $pdf->SetX(25);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(80, 8, number_format($prediccion) . ' despachos', 0, 1, 'L');
        $pdf->SetX(25);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(80, 4, 'Estimacion para manana basada en IA', 0, 1, 'L');
        
        $pdf->SetY($pdf->GetY() - 18);
        $pdf->SetX(110);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(70, 5, 'TENDENCIA', 0, 1, 'L');
        $pdf->SetX(110);
        $pdf->SetFont('helvetica', 'B', 24);
        $color_trend = ($variacion > 0) ? [39, 174, 96] : (($variacion < 0) ? [231, 76, 60] : [149, 165, 166]);
        $pdf->SetTextColor($color_trend[0], $color_trend[1], $color_trend[2]);
        $pdf->Cell(70, 8, ($variacion > 0 ? '▲ +' : ($variacion < 0 ? '▼ ' : '→ ')) . abs($variacion) . '%', 0, 1, 'L');
        $pdf->SetX(110);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(200, 200, 200);
        $pdf->Cell(70, 4, 'Variacion vs dia anterior', 0, 1, 'L');
        $pdf->Ln(12);
    }
    
    // --------------------------------------------------------------
    // 2. DESPACHO (KARDEX COMPLETO)
    // --------------------------------------------------------------
    elseif ($seccion == 'despacho') {
        $where = []; $params = [];
        if (!empty($filtro_fecha)) { $where[] = "fecha = :fecha"; $params[':fecha'] = $filtro_fecha; }
        if (!empty($filtro_destino)) { $where[] = "destino = :destino"; $params[':destino'] = $filtro_destino; }
        if (!empty($filtro_estado)) { $where[] = "estado = :estado"; $params[':estado'] = $filtro_estado; }
        
        $sql = "SELECT fecha, destino, material, cantidad, estado, operador FROM historico";
        if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY fecha DESC LIMIT 250";
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $pdf->TituloSeccion("Kardex de Despachos", "🚚");
        if (!empty($rows)) {
            $total_unidades = array_sum(array_column($rows, 'cantidad'));
            $destinos_unico = count(array_unique(array_column($rows, 'destino')));
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, "Registros: " . count($rows), 0, 1, 'L');
            $pdf->Cell(0, 5, "Unidades totales: " . number_format($total_unidades), 0, 1, 'L');
            $pdf->Cell(0, 5, "Destinos involucrados: " . $destinos_unico, 0, 1, 'L');
            $pdf->Ln(5);
            
            $data = [];
            foreach ($rows as $r) {
                $estado_clase = ($r['estado'] == 'Completado') ? '<span class="estado-bueno">Completado</span>' : (($r['estado'] == 'Pendiente') ? '<span class="estado-regular">Pendiente</span>' : '<span class="estado-malo">Cancelado</span>');
                $data[] = [htmlspecialchars($r['fecha']), htmlspecialchars($r['destino']), htmlspecialchars($r['material']), number_format($r['cantidad']), $estado_clase, htmlspecialchars($r['operador'])];
            }
            $pdf->TablaProfesional(["Fecha", "Destino", "Material", "Cantidad", "Estado", "Operador"], $data);
        } else {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, "No se encontraron registros con los filtros seleccionados.", 0, 1, 'L');
        }
    }
    
    // --------------------------------------------------------------
    // 3. TRABAJADORES
    // --------------------------------------------------------------
    elseif ($seccion == 'trabajadores') {
        $stmt = $pdo->query("SELECT nombre, puesto, turno, estatus, telefono FROM trabajadores ORDER BY nombre");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdf->TituloSeccion("Kardex de Personal", "👥");
        if (!empty($rows)) {
            $activos = count(array_filter($rows, fn($t) => $t['estatus'] == 'Activo'));
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, "Total trabajadores: " . count($rows), 0, 1, 'L');
            $pdf->Cell(0, 5, "Personal activo: " . $activos, 0, 1, 'L');
            $pdf->Ln(5);
            $data = [];
            foreach ($rows as $t) {
                $estado_clase = ($t['estatus'] == 'Activo') ? '<span class="estado-bueno">Activo</span>' : '<span class="estado-malo">Inactivo</span>';
                $data[] = [htmlspecialchars($t['nombre']), htmlspecialchars($t['puesto']), htmlspecialchars($t['turno']), $estado_clase, htmlspecialchars($t['telefono'] ?? 'N/A')];
            }
            $pdf->TablaProfesional(["Nombre", "Puesto", "Turno", "Estatus", "Telefono"], $data);
        } else {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, "No hay trabajadores registrados.", 0, 1, 'L');
        }
    }
    
    // --------------------------------------------------------------
    // 4. DESTINOS
    // --------------------------------------------------------------
    elseif ($seccion == 'destinos') {
        $stmt = $pdo->query("SELECT nombre, ubicacion, responsable, activo FROM destinos ORDER BY nombre");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdf->TituloSeccion("Directorio de Destinos", "📍");
        if (!empty($rows)) {
            $activos = count(array_filter($rows, fn($d) => $d['activo'] == 1));
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, "Total destinos: " . count($rows), 0, 1, 'L');
            $pdf->Cell(0, 5, "Destinos activos: " . $activos, 0, 1, 'L');
            $pdf->Ln(5);
            $data = [];
            foreach ($rows as $d) {
                $estado_clase = ($d['activo'] == 1) ? '<span class="estado-bueno">Activo</span>' : '<span class="estado-malo">Inactivo</span>';
                $data[] = [htmlspecialchars($d['nombre']), htmlspecialchars($d['ubicacion'] ?? 'N/A'), htmlspecialchars($d['responsable'] ?? 'N/A'), $estado_clase];
            }
            $pdf->TablaProfesional(["Destino", "Ubicacion", "Responsable", "Estado"], $data);
        } else {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, "No hay destinos registrados.", 0, 1, 'L');
        }
    }
    
    // --------------------------------------------------------------
    // 5. MATERIALES
    // --------------------------------------------------------------
    elseif ($seccion == 'materiales') {
        try {
            $stmt = $pdo->query("SELECT nombre, stock_actual, unidad, stock_minimo FROM materiales ORDER BY nombre");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdf->TituloSeccion("Inventario de Materiales", "🏗️");
            if (!empty($rows)) {
                $bajo_stock = count(array_filter($rows, fn($m) => $m['stock_actual'] < $m['stock_minimo']));
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(0, 5, "Total materiales: " . count($rows), 0, 1, 'L');
                $pdf->Cell(0, 5, "Materiales con bajo stock: " . $bajo_stock, 0, 1, 'L');
                $pdf->Ln(5);
                $data = [];
                foreach ($rows as $m) {
                    $alerta = ($m['stock_actual'] < $m['stock_minimo']) ? '<span class="estado-malo">Bajo Stock</span>' : '<span class="estado-bueno">OK</span>';
                    $data[] = [htmlspecialchars($m['nombre']), number_format($m['stock_actual']), htmlspecialchars($m['unidad']), number_format($m['stock_minimo']), $alerta];
                }
                $pdf->TablaProfesional(["Material", "Stock Actual", "Unidad", "Stock Minimo", "Alerta"], $data);
            } else {
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(0, 5, "No hay materiales registrados.", 0, 1, 'L');
            }
        } catch (PDOException $e) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 5, "Modulo de materiales en desarrollo.", 0, 1, 'L');
        }
    }
    
    // --------------------------------------------------------------
    // 6. OTRAS SECCIONES (Placeholder)
    // --------------------------------------------------------------
    else {
        $pdf->TituloSeccion(ucfirst($seccion), "📄");
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, "El modulo de " . strtoupper($seccion) . " se encuentra en fase de implementacion.", 0, 1, 'C');
        $pdf->Cell(0, 5, "Consulte con el administrador del sistema.", 0, 1, 'C');
    }
    
} catch (PDOException $e) {
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(231, 76, 60);
    $pdf->Cell(0, 5, "Error al cargar datos: " . $e->getMessage(), 0, 1, 'L');
}

// ==================================================================
// FIRMA Y CIERRE
// ==================================================================
$pdf->Ln(12);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 4, "Este informe fue generado automaticamente por el sistema Executive AI Monitor de Holcim.", 0, 1, 'C');
$pdf->Cell(0, 4, "Los datos reflejan la informacion disponible al momento de la consulta.", 0, 1, 'C');

// ==================================================================
// SALIDA DEL PDF
// ==================================================================
$pdf->Output('Informe_Holcim_' . $seccion . '_' . date('Ymd_His') . '.pdf', 'I');
exit;