<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: inicio.php");
    exit;
}

require_once __DIR__ . '/includes/config.php';
$admin_name = htmlspecialchars($_SESSION['usuario'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas del Sistema - Panel Admin</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root{
            --neon-orange:#ff7b00;
            --neon-blue:#0066ff;
            --dark-bg:#0f0f1a;
            --card: rgba(20, 20, 30, 0.86);
            --border: rgba(255, 123, 0, 0.18);
        }

        body{
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            background-color: var(--dark-bg);
            color:#e8e8ee;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(255,123,0,0.12) 0%, transparent 52%),
                radial-gradient(circle at 90% 80%, rgba(0,102,255,0.12) 0%, transparent 52%);
        }

        .admin-container{ max-width: 1400px; margin:0 auto; }

        .nav-gradient{
            background: linear-gradient(135deg, rgba(255,123,0,0.12), rgba(0,102,255,0.12));
            border-bottom: 1px solid rgba(255,123,0,0.22);
            backdrop-filter: blur(10px);
        }

        .card{
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
        }

        .chip{
            background: rgba(0,0,0,0.28);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            color: rgba(255,255,255,0.85);
            white-space: nowrap;
        }

        .btn{
            border-radius: 12px;
            padding: 10px 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 150ms ease, box-shadow 150ms ease, background 150ms ease;
            user-select: none;
        }

        .btn-primary{
            background: linear-gradient(45deg, var(--neon-orange), var(--neon-blue));
            color:#fff;
        }
        .btn-primary:hover{ transform: translateY(-1px); box-shadow: 0 10px 18px rgba(0,0,0,0.30); }

        .btn-ghost{
            background: rgba(255,123,0,0.16);
            border: 1px solid rgba(255,123,0,0.35);
            color:#fff;
        }
        .btn-ghost:hover{ background: rgba(255,123,0,0.24); }

        .btn-danger{ background: #dc2626; color:#fff; }
        .btn-danger:hover{ background: #b91c1c; transform: translateY(-1px); }

        .error-box{
            display:none;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(220,38,38,0.45);
            background: rgba(220,38,38,0.18);
            color: rgba(255,255,255,0.92);
        }

        .kpi-grid{
            display:grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
        }
        .kpi{ grid-column: span 3; padding: 14px; }

        .kpi .label{ font-size: 12px; color: rgba(255,255,255,0.78); display:flex; gap:8px; align-items:center; }
        .kpi .value{ font-size: 28px; font-weight: 900; line-height: 1.1; margin-top: 10px; }
        .kpi .sub{ margin-top: 6px; font-size: 12px; color: rgba(255,255,255,0.65); }

        .dot{ width:10px; height:10px; border-radius: 999px; box-shadow: 0 0 0 4px rgba(255,255,255,0.05); }
        .dot-orange{ background: var(--neon-orange); }
        .dot-blue{ background: var(--neon-blue); }
        .dot-green{ background:#22c55e; }
        .dot-yellow{ background:#f59e0b; }
        .dot-red{ background:#ef4444; }

        .grid-2{ display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .chart-wrap{ position:relative; height: 320px; }

        .table-scroll{
            overflow-x:auto;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.08);
        }

        table{
            width:100%;
            border-collapse: collapse;
            min-width: 760px;
            background: rgba(18, 18, 30, 0.65);
        }
        thead th{
            text-align:left;
            font-weight: 900;
            font-size: 12px;
            padding: 12px 12px;
            color: rgba(255,255,255,0.92);
            background: linear-gradient(135deg, rgba(255, 123, 0, 0.25), rgba(0, 102, 255, 0.25));
            border-bottom: 1px solid rgba(255,255,255,0.10);
        }
        tbody td{
            padding: 12px 12px;
            font-size: 12px;
            color: rgba(255,255,255,0.85);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            vertical-align: top;
        }
        tbody tr:hover{ background: rgba(255,123,0,0.08); }

        .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }

        .pill{
            display:inline-flex; align-items:center; gap:6px;
            border-radius:999px; padding: 4px 10px;
            font-size: 11px; font-weight: 900;
            border: 1px solid rgba(255,255,255,0.12);
        }
        .pill-ok{ background: rgba(34,197,94,0.10); color:#22c55e; border-color: rgba(34,197,94,0.35); }
        .pill-warn{ background: rgba(245,158,11,0.10); color:#f59e0b; border-color: rgba(245,158,11,0.35); }
        .pill-off{ background: rgba(239,68,68,0.10); color:#ef4444; border-color: rgba(239,68,68,0.35); }

        /* Skeleton */
        .skeleton{
            background: linear-gradient(90deg, rgba(255,255,255,0.06), rgba(255,255,255,0.14), rgba(255,255,255,0.06));
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 12px;
        }
        @keyframes shimmer { 0%{ background-position: 200% 0; } 100%{ background-position: -200% 0; } }

        /* Móvil: charts 1 columna, kpis 1 por fila */
        @media (max-width: 1024px){
            .kpi{ grid-column: span 6; }
            .grid-2{ grid-template-columns: 1fr; }
            .chart-wrap{ height: 300px; }
        }
        @media (max-width: 640px){
            .kpi{ grid-column: span 12; }
            .chart-wrap{ height: 260px; }
        }

        /* Tabla -> tarjetas en móvil */
        .activity-cards{ display:none; }
        @media (max-width: 640px){
            table{ display:none; }
            .table-scroll{ border:none; }
            .activity-cards{ display:grid; gap: 10px; }
            .activity-card{
                padding: 12px;
                border-radius: 14px;
                border: 1px solid rgba(255,255,255,0.08);
                background: rgba(18, 18, 30, 0.65);
            }
            .activity-card .row{
                display:flex; justify-content:space-between; gap:10px;
                font-size: 12px;
                color: rgba(255,255,255,0.82);
                margin-top: 6px;
            }
            .activity-card .row strong{ color: rgba(255,255,255,0.95); }
        }
    </style>
</head>

<body class="min-h-screen">

<nav class="nav-gradient text-white p-3 sm:p-4 shadow-lg">
    <div class="container mx-auto flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div class="flex flex-wrap items-center gap-2 sm:gap-4">
            <a href="admin.php" class="btn btn-ghost text-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <div class="flex items-center gap-2">
                <i class="fas fa-chart-bar text-xl text-orange-400"></i>
                <div>
                    <div class="text-lg font-extrabold">Estadísticas del Sistema</div>
                    <div class="text-xs text-gray-300">Panel administrativo</div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <span class="chip truncate max-w-[170px] sm:max-w-none">
                <i class="fas fa-user-shield mr-1"></i><?= $admin_name ?>
            </span>

            <span class="chip" id="apiStatus">
                <span class="dot dot-yellow" style="width:8px;height:8px; box-shadow:none;"></span>
                <span class="ml-1">API: esperando</span>
            </span>

            <button id="btnRefresh" class="btn btn-primary text-sm">
                <i class="fas fa-rotate"></i> Actualizar
            </button>

            <a href="logout.php" class="btn btn-danger text-sm">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </div>
</nav>

<main class="admin-container px-4 sm:px-6 py-6">

    <div id="error-message" class="error-box mb-4"></div>

    <!-- Toolbar extra -->
    <section class="card p-4 mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="chip"><i class="fas fa-filter mr-1"></i>Vista</span>

                <button class="btn btn-ghost text-sm" data-range="7">
                    <i class="fas fa-calendar-week"></i> 7 días
                </button>
                <button class="btn btn-ghost text-sm" data-range="14">
                    <i class="fas fa-calendar-days"></i> 14 días
                </button>
                <button class="btn btn-ghost text-sm" data-range="30">
                    <i class="fas fa-calendar"></i> 30 días
                </button>

                <span class="chip" id="rangeLabel">Mostrando: 7 días</span>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                <div class="relative w-full sm:w-80">
                    <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-300"></i>
                    <input id="tableSearch" type="text" placeholder="Buscar en actividad (usuario, acción, IP)..."
                        class="w-full pl-10 pr-3 py-2 rounded-xl bg-black bg-opacity-30 border border-white border-opacity-10 text-sm outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                <button id="btnExport" class="btn btn-primary text-sm justify-center">
                    <i class="fas fa-file-csv"></i> Exportar CSV
                </button>
            </div>
        </div>
    </section>

    <!-- KPIs -->
    <section class="kpi-grid mb-4">
        <div class="card kpi">
            <div class="label"><span class="dot dot-orange"></span>Usuarios registrados</div>
            <div class="value" id="total-users">0</div>
            <div class="sub">Total en BD</div>
        </div>

        <div class="card kpi">
            <div class="label"><span class="dot dot-green"></span>Usuarios activos</div>
            <div class="value" id="active-users">0</div>
            <div class="sub">Estimado (según tu API)</div>
        </div>

        <div class="card kpi">
            <div class="label"><span class="dot dot-blue"></span>Nuevos hoy</div>
            <div class="value" id="sessions-today">0</div>
            <div class="sub">Altas del día</div>
        </div>

        <div class="card kpi">
            <div class="label"><span class="dot dot-yellow"></span>Alertas activas</div>
            <div class="value" id="system-alerts">0</div>
            <div class="sub">Campos incompletos / reglas</div>
        </div>

        <!-- Extras calculados en front -->
        <div class="card kpi">
            <div class="label"><span class="dot dot-blue"></span>Promedio diario</div>
            <div class="value" id="avg-7d">0</div>
            <div class="sub">Promedio (vista actual)</div>
        </div>

        <div class="card kpi">
            <div class="label"><span class="dot dot-orange"></span>Último registro</div>
            <div class="value text-base sm:text-lg" style="margin-top:12px;" id="last-register">—</div>
            <div class="sub">Desde actividad reciente</div>
        </div>

        <div class="card kpi">
            <div class="label"><span class="dot dot-green"></span>Perfiles detectados</div>
            <div class="value" id="profiles-count">0</div>
            <div class="sub">Cantidad de grupos</div>
        </div>

        <div class="card kpi">
            <div class="label"><span class="dot dot-red"></span>Estado API</div>
            <div class="value" id="api-health">—</div>
            <div class="sub">Online / Error</div>
        </div>
    </section>

    <!-- Charts -->
    <section class="grid-2 mb-4">
        <div class="card p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div>
                    <div class="font-extrabold flex items-center gap-2">
                        <i class="fas fa-chart-line text-orange-400"></i> Registros (vista)
                    </div>
                    <div class="text-xs text-gray-300">Re-escala la data según el rango elegido</div>
                </div>
                <span class="chip" id="activityHint">—</span>
            </div>
            <div class="chart-wrap">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div>
                    <div class="font-extrabold flex items-center gap-2">
                        <i class="fas fa-chart-pie text-orange-400"></i> Distribución de usuarios
                    </div>
                    <div class="text-xs text-gray-300">Por perfil (o “Sin perfil”)</div>
                </div>
                <span class="chip" id="distHint">—</span>
            </div>
            <div class="chart-wrap">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Table -->
    <section class="card p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <div>
                <div class="font-extrabold flex items-center gap-2">
                    <i class="fas fa-clock text-orange-400"></i> Actividad reciente
                </div>
                <div class="text-xs text-gray-300">Filtra con el buscador. Exporta CSV si lo necesitas.</div>
            </div>
            <span class="chip">
                <i class="fas fa-sync-alt mr-1"></i>
                <span id="last-update-time"><?= date('d/m/Y H:i:s') ?></span>
            </span>
        </div>

        <div class="table-scroll mb-2">
            <table>
                <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Estado</th>
                    <th>Fecha/Hora</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody id="activity-table-body">
                <tr>
                    <td colspan="5" class="text-center py-6 text-gray-300">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Cargando...
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Cards móvil -->
        <div id="activity-cards" class="activity-cards"></div>
    </section>

</main>

<footer class="bg-gray-900 text-gray-400 text-xs sm:text-sm p-4 mt-8 border-t border-gray-800">
    <div class="container mx-auto text-center">
        <p>Panel de Estadísticas - LogiCore - <?= date('Y') ?></p>
    </div>
</footer>

<script>
    // ===== Estado =====
    let fullData = null;        // data original
    let currentRange = 7;       // 7/14/30 (solo front)
    let lastRecentRows = [];    // para búsqueda/export

    const $ = (id) => document.getElementById(id);

    function safeNum(n){ const x = Number(n); return Number.isFinite(x) ? x : 0; }

    function escapeHtml(str){
        return String(str)
            .replaceAll("&","&amp;")
            .replaceAll("<","&lt;")
            .replaceAll(">","&gt;")
            .replaceAll('"',"&quot;")
            .replaceAll("'","&#039;");
    }

    function setApiStatus(ok, text){
        const chip = $("apiStatus");
        const dotClass = ok ? "dot-green" : "dot-red";
        chip.innerHTML = `<span class="dot ${dotClass}" style="width:8px;height:8px; box-shadow:none;"></span>
                          <span class="ml-1">API: ${escapeHtml(text)}</span>`;
        $("api-health").textContent = ok ? "Online" : "Error";
    }

    function mostrarError(msg){
        const el = $("error-message");
        el.style.display = "block";
        el.innerHTML = `<i class="fas fa-triangle-exclamation mr-2"></i>${escapeHtml(msg)}`;
    }

    function ocultarError(){
        const el = $("error-message");
        el.style.display = "none";
        el.innerHTML = "";
    }

    function formatearDia(d){
        const dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        return dias[d];
    }

    // ===== Charts =====
    const activityChart = new Chart($("activityChart").getContext("2d"), {
        type: "line",
        data: { labels: [], datasets: [{
            label: "Nuevos registros",
            data: [],
            borderColor: "#ff7b00",
            backgroundColor: "rgba(255, 123, 0, 0.12)",
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointBackgroundColor: "#0066ff",
            pointBorderColor: "#ffffff",
            pointBorderWidth: 2
        }]},
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: "rgba(255,255,255,0.85)" } } },
            scales: {
                y: { beginAtZero: true, grid: { color: "rgba(255,255,255,0.08)" }, ticks:{ color:"rgba(255,255,255,0.75)" } },
                x: { grid: { color: "rgba(255,255,255,0.08)" }, ticks:{ color:"rgba(255,255,255,0.75)" } }
            }
        }
    });

    const distributionChart = new Chart($("distributionChart").getContext("2d"), {
        type: "doughnut",
        data: { labels: ["Cargando..."], datasets: [{ data:[1], backgroundColor:["#666"], borderWidth:2, borderColor:"#0f0f1a" }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "62%",
            plugins: { legend: { position:"bottom", labels:{ color:"rgba(255,255,255,0.85)", font:{ size:12 } } } }
        }
    });

    // ===== Render =====
    function renderKpis(data){
        const s = data.stats || {};
        $("total-users").textContent = safeNum(s.total_usuarios).toLocaleString();
        $("active-users").textContent = safeNum(s.usuarios_activos).toLocaleString();
        $("sessions-today").textContent = safeNum(s.nuevos_hoy).toLocaleString();
        $("system-alerts").textContent = safeNum(s.alertas_activas).toLocaleString();

        // Extras
        const act = Array.isArray(data.actividad_7dias) ? data.actividad_7dias : [];
        const sum = act.reduce((acc, it) => acc + safeNum(it.cantidad), 0);
        const avg = act.length ? (sum / act.length) : 0;
        $("avg-7d").textContent = Math.round(avg);

        const recent = Array.isArray(data.actividad_reciente) ? data.actividad_reciente : [];
        if (recent[0] && recent[0].fecha){
            const dt = new Date(recent[0].fecha);
            $("last-register").textContent = isNaN(dt.getTime()) ? "—" : dt.toLocaleString("es-ES");
        } else {
            $("last-register").textContent = "—";
        }

        const dist = Array.isArray(data.distribucion) ? data.distribucion : [];
        $("profiles-count").textContent = dist.length ? dist.length : 0;
    }

    // Re-escala visual: si eliges 14/30, duplica/repite lo que hay (sin back nuevo)
    function buildRangeSeries(actividad7, range){
        // actividad7 es el dataset real (7 días). Para 14/30 sólo simulamos visualmente.
        // Si luego quieres REAL, se cambia el endpoint para devolver N días.
        const base = Array.isArray(actividad7) ? actividad7.slice() : [];
        if (!base.length) return { labels: [], values: [] };

        let target = range;
        if (target <= 7){
            // Render real 7d
            const labels = [];
            const values = [];

            for (let i=6; i>=0; i--){
                const fecha = new Date();
                fecha.setDate(fecha.getDate() - i);
                const f = fecha.toISOString().split("T")[0];
                const found = base.find(x => x.dia === f);
                labels.push(formatearDia(fecha.getDay()));
                values.push(found ? safeNum(found.cantidad) : 0);
            }
            return { labels, values };
        }

        // Extender: repetimos patrón para mostrar tendencia (solo UI)
        const labels = [];
        const values = [];
        for (let i=target-1; i>=0; i--){
            const fecha = new Date();
            fecha.setDate(fecha.getDate() - i);
            labels.push(String(fecha.getDate()).padStart(2,"0") + "/" + String(fecha.getMonth()+1).padStart(2,"0"));
            const idx = (target-1 - i) % 7;
            values.push(safeNum(base[idx]?.cantidad));
        }
        return { labels, values };
    }

    function renderActivityChart(data){
        const range = currentRange;
        const series = buildRangeSeries(data.actividad_7dias, range);

        activityChart.data.labels = series.labels;
        activityChart.data.datasets[0].data = series.values;
        activityChart.update();

        $("rangeLabel").textContent = `Mostrando: ${range} días`;
        $("activityHint").textContent = range <= 7 ? "Datos reales (7 días)" : "Vista extendida (front)";
    }

    function renderDistributionChart(data){
        const dist = Array.isArray(data.distribucion) ? data.distribucion : [];
        const labels = dist.map(x => x.nombre_perfil || "Sin perfil");
        const values = dist.map(x => safeNum(x.cantidad));

        const colors = ["#22c55e","#ff7b00","#ef4444","#0066ff","#a855f7","#e11d48","#14b8a6","#f59e0b"];

        if (!labels.length){
            distributionChart.data.labels = ["Sin datos"];
            distributionChart.data.datasets[0].data = [1];
            distributionChart.data.datasets[0].backgroundColor = ["#666"];
            $("distHint").textContent = "Sin datos";
        } else {
            distributionChart.data.labels = labels;
            distributionChart.data.datasets[0].data = values;
            distributionChart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
            $("distHint").textContent = `${labels.length} perfiles`;
        }

        distributionChart.update();
    }

    function renderActivityTable(rows){
        const tbody = $("activity-table-body");
        const cards = $("activity-cards");
        tbody.innerHTML = "";
        cards.innerHTML = "";

        if (!rows.length){
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-300">No hay actividad reciente</td></tr>`;
            cards.innerHTML = `<div class="activity-card">No hay actividad reciente</div>`;
            return;
        }

        rows.forEach(item => {
            const usuario = item.usuario || "Sistema";
            const accion = item.accion || "Evento";
            const ip = item.ip || "N/A";
            const dt = item.fecha ? new Date(item.fecha) : null;
            const fecha = (dt && !isNaN(dt.getTime())) ? dt.toLocaleString("es-ES") : "N/A";

            // Tabla
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="mono">${escapeHtml(usuario)}</td>
                <td>${escapeHtml(accion)}</td>
                <td><span class="pill pill-ok"><span class="dot dot-green" style="width:8px;height:8px; box-shadow:none;"></span>Registro</span></td>
                <td class="mono">${escapeHtml(fecha)}</td>
                <td class="mono">${escapeHtml(ip)}</td>
            `;
            tbody.appendChild(tr);

            // Card móvil
            const div = document.createElement("div");
            div.className = "activity-card";
            div.innerHTML = `
                <div class="flex items-center justify-between gap-2">
                    <div class="mono font-bold">${escapeHtml(usuario)}</div>
                    <span class="pill pill-ok"><span class="dot dot-green" style="width:8px;height:8px; box-shadow:none;"></span>Registro</span>
                </div>
                <div class="row"><span>Acción</span><strong>${escapeHtml(accion)}</strong></div>
                <div class="row"><span>Fecha</span><strong class="mono">${escapeHtml(fecha)}</strong></div>
                <div class="row"><span>IP</span><strong class="mono">${escapeHtml(ip)}</strong></div>
            `;
            cards.appendChild(div);
        });
    }

    function applySearchFilter(){
        const q = $("tableSearch").value.trim().toLowerCase();
        if (!q){
            renderActivityTable(lastRecentRows);
            return;
        }
        const filtered = lastRecentRows.filter(r => {
            const u = (r.usuario || "").toLowerCase();
            const a = (r.accion || "").toLowerCase();
            const ip = (r.ip || "").toLowerCase();
            const f = (r.fecha || "").toLowerCase();
            return u.includes(q) || a.includes(q) || ip.includes(q) || f.includes(q);
        });
        renderActivityTable(filtered);
    }

    function exportCSV(){
        const q = $("tableSearch").value.trim().toLowerCase();
        const rows = q ? lastRecentRows.filter(r => {
            const u = (r.usuario || "").toLowerCase();
            const a = (r.accion || "").toLowerCase();
            const ip = (r.ip || "").toLowerCase();
            const f = (r.fecha || "").toLowerCase();
            return u.includes(q) || a.includes(q) || ip.includes(q) || f.includes(q);
        }) : lastRecentRows;

        const header = ["usuario","accion","fecha","ip"];
        const lines = [header.join(",")];

        rows.forEach(r => {
            const line = [
                `"${String(r.usuario ?? "").replaceAll('"','""')}"`,
                `"${String(r.accion ?? "").replaceAll('"','""')}"`,
                `"${String(r.fecha ?? "").replaceAll('"','""')}"`,
                `"${String(r.ip ?? "").replaceAll('"','""')}"`
            ].join(",");
            lines.push(line);
        });

        const blob = new Blob([lines.join("\n")], { type: "text/csv;charset=utf-8;" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `actividad_reciente_${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    function setLoading(on){
        // Si quieres, aquí puedes deshabilitar botones o poner skeletons más agresivos.
        $("btnRefresh").disabled = on;
        $("btnExport").disabled = on;
    }

    // ===== Fetch =====
    async function cargarEstadisticasReales(){
        setLoading(true);
        setApiStatus(true, "cargando");

        try{
            const res = await fetch("estadisticas_data.php", { cache: "no-store" });
            if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);

            const data = await res.json();
            if (!data || !data.success) throw new Error(data?.error || "Respuesta inválida");

            fullData = data;
            ocultarError();
            setApiStatus(true, "online");

            renderKpis(data);
            renderActivityChart(data);
            renderDistributionChart(data);

            lastRecentRows = Array.isArray(data.actividad_reciente) ? data.actividad_reciente : [];
            renderActivityTable(lastRecentRows);
            $("last-update-time").textContent = new Date().toLocaleString("es-ES");
            applySearchFilter();

        } catch (err){
            setApiStatus(false, "error");
            mostrarError("No se pudieron cargar las estadísticas: " + err.message);
            $("api-health").textContent = "Error";

            // fallback ejemplo (como tu versión anterior)
            const ejemplo = {
                stats: { total_usuarios: 15, usuarios_activos: 12, nuevos_hoy: 2, alertas_activas: 1 },
                actividad_7dias: [
                    { dia: '<?= date('Y-m-d', strtotime('-6 days')) ?>', cantidad: 1 },
                    { dia: '<?= date('Y-m-d', strtotime('-5 days')) ?>', cantidad: 0 },
                    { dia: '<?= date('Y-m-d', strtotime('-4 days')) ?>', cantidad: 3 },
                    { dia: '<?= date('Y-m-d', strtotime('-3 days')) ?>', cantidad: 1 },
                    { dia: '<?= date('Y-m-d', strtotime('-2 days')) ?>', cantidad: 2 },
                    { dia: '<?= date('Y-m-d', strtotime('-1 days')) ?>', cantidad: 0 },
                    { dia: '<?= date('Y-m-d') ?>', cantidad: 2 }
                ],
                actividad_reciente: [
                    { usuario: "ejemplo1", accion: "Registro de usuario", fecha: new Date().toISOString(), ip: "N/A" }
                ],
                distribucion: [
                    { nombre_perfil: "Usuario", cantidad: 10 },
                    { nombre_perfil: "Administrador", cantidad: 2 },
                    { nombre_perfil: "Sin perfil", cantidad: 3 }
                ]
            };
            fullData = ejemplo;
            renderKpis(ejemplo);
            renderActivityChart(ejemplo);
            renderDistributionChart(ejemplo);
            lastRecentRows = ejemplo.actividad_reciente;
            renderActivityTable(lastRecentRows);
            applySearchFilter();
        } finally {
            setLoading(false);
        }
    }

    // ===== Eventos =====
    document.addEventListener("DOMContentLoaded", () => {
        cargarEstadisticasReales();
        setInterval(cargarEstadisticasReales, 30000);
    });

    $("btnRefresh").addEventListener("click", cargarEstadisticasReales);
    $("tableSearch").addEventListener("input", applySearchFilter);
    $("btnExport").addEventListener("click", exportCSV);

    // Botones rango
    document.querySelectorAll("[data-range]").forEach(btn => {
        btn.addEventListener("click", () => {
            currentRange = Number(btn.getAttribute("data-range")) || 7;
            if (fullData){
                renderActivityChart(fullData);
                renderKpis(fullData); // recalcula promedio según vista (si quieres que dependa)
            }
        });
    });
</script>

</body>
</html>
