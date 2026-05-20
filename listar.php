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

// Filtros por GET
$buscar = trim($_GET['buscar'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$fecha = trim($_GET['fecha'] ?? '');

// Consulta principal del histórico
$sql = "SELECT * FROM historico WHERE 1=1";
$params = [];

if (!empty($buscar)) {
    $sql .= " AND (
        folio LIKE ?
        OR material LIKE ?
        OR destino LIKE ?
        OR chofer LIKE ?
    )";
    $likeBuscar = "%{$buscar}%";
    $params[] = $likeBuscar;
    $params[] = $likeBuscar;
    $params[] = $likeBuscar;
    $params[] = $likeBuscar;
}

if (!empty($estado)) {
    $sql .= " AND estado = ?";
    $params[] = $estado;
}

if (!empty($fecha)) {
    $sql .= " AND fecha = ?";
    $params[] = $fecha;
}

$sql .= " ORDER BY fecha DESC, hora DESC, id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registros_filtrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar histórico: " . $e->getMessage());
}

// KPIs
try {
    $stmtTotal = $pdo->query("SELECT COUNT(*) FROM historico");
    $total_registros = (int) $stmtTotal->fetchColumn();

    $stmtEntregados = $pdo->prepare("SELECT COUNT(*) FROM historico WHERE estado = ?");
    $stmtEntregados->execute(['Entregado']);
    $total_entregados = (int) $stmtEntregados->fetchColumn();

    $stmtTransito = $pdo->prepare("SELECT COUNT(*) FROM historico WHERE estado = ?");
    $stmtTransito->execute(['En tránsito']);
    $total_transito = (int) $stmtTransito->fetchColumn();

    $stmtPendientes = $pdo->query("SELECT COUNT(*) FROM historico WHERE estado IN ('Registrado', 'Cargado')");
    $total_pendientes = (int) $stmtPendientes->fetchColumn();
} catch (PDOException $e) {
    $total_registros = 0;
    $total_entregados = 0;
    $total_transito = 0;
    $total_pendientes = 0;
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Operaciones · Holcim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f4f8;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1e293b;
        }

        .corp-header {
            background: #003B5C;
            color: white;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid #00D4AA;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0,59,92,0.2);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-principal {
            height: 50px;
            width: auto;
            transition: 0.3s;
        }

        .logo-principal:hover {
            transform: scale(1.02);
        }

        .empresa-info h1 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            line-height: 1.2;
            color: white;
        }

        .sistema-nombre {
            font-size: 12px;
            color: #00D4AA;
            letter-spacing: 2px;
            font-weight: 500;
            text-transform: uppercase;
            display: block;
        }

        .user-area {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-badge {
            background: rgba(0, 212, 170, 0.15);
            padding: 8px 20px;
            border-radius: 40px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(0,212,170,0.3);
            backdrop-filter: blur(5px);
        }

        .user-badge i {
            color: #00D4AA;
            font-size: 18px;
        }

        .user-name-header {
            font-weight: 600;
            color: white;
        }

        .user-role {
            color: #00D4AA;
            font-size: 12px;
            background: rgba(0,59,92,0.5);
            padding: 3px 10px;
            border-radius: 30px;
            margin-left: 5px;
        }

        .logout-btn {
            background: transparent;
            color: white;
            border: 1px solid rgba(255,255,255,0.15);
            padding: 8px 18px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 13px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #00D4AA;
            color: #003B5C;
            border-color: #00D4AA;
        }

        .main-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .hero-minimal {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,59,92,0.05);
            border: 1px solid #e9eef2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .hero-minimal h2 {
            font-size: 24px;
            font-weight: 600;
            color: #003B5C;
            margin-bottom: 8px;
        }

        .hero-minimal p {
            color: #64748b;
            font-size: 15px;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .date-card {
            background: #f8fafc;
            padding: 12px 24px;
            border-radius: 40px;
            color: #003B5C;
            font-weight: 500;
            border: 1px solid #e2e8f0;
        }

        .date-card i {
            color: #00D4AA;
            margin-right: 10px;
        }

        .btn-corp-primary,
        .btn-corp-secondary {
            padding: 12px 18px;
            text-align: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            transition: 0.3s;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-corp-primary {
            background: #003B5C;
            color: white;
        }

        .btn-corp-primary:hover {
            background: #002f4b;
        }

        .btn-corp-secondary {
            background: white;
            color: #003B5C;
            border-color: #e2e8f0;
        }

        .btn-corp-secondary:hover {
            border-color: #00D4AA;
            color: #00D4AA;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .kpi-card-corp {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,59,92,0.05);
            border: 1px solid #e9eef2;
            transition: 0.3s;
        }

        .kpi-card-corp:hover {
            border-color: #00D4AA;
            transform: translateY(-3px);
            box-shadow: 0 12px 30px -10px rgba(0,59,92,0.2);
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .kpi-icon-corp {
            width: 48px;
            height: 48px;
            background: #e6f7f2;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00D4AA;
            font-size: 20px;
        }

        .kpi-value-corp {
            font-size: 34px;
            font-weight: 700;
            color: #003B5C;
            margin-bottom: 5px;
        }

        .kpi-label-corp {
            color: #64748b;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,59,92,0.05);
            border: 1px solid #e9eef2;
            margin-bottom: 30px;
        }

        .section-title-corp {
            font-size: 20px;
            font-weight: 700;
            color: #003B5C;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e9eef2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .section-title-corp span {
            color: #00D4AA;
            font-size: 14px;
            font-weight: 500;
        }

        .section-title-corp i {
            margin-right: 8px;
            color: #00D4AA;
        }

        .filters-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #dbe4ea;
            background: #fff;
            color: #1e293b;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: #00D4AA;
            box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.10);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        thead th {
            background: #f8fafc;
            color: #003B5C;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            padding: 16px 14px;
            border-bottom: 2px solid #e9eef2;
        }

        tbody td {
            padding: 16px 14px;
            border-bottom: 1px solid #eef2f6;
            color: #334155;
            font-size: 14px;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8fcfb;
        }

        .folio-cell {
            font-weight: 700;
            color: #003B5C;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-success {
            background: #e6f7f2;
            color: #0f766e;
        }

        .status-warning {
            background: #fff7e6;
            color: #b45309;
        }

        .status-info {
            background: #eaf2ff;
            color: #1d4ed8;
        }

        .status-danger {
            background: #feeceb;
            color: #b91c1c;
        }

        .status-neutral {
            background: #eef2f7;
            color: #475569;
        }

        .action-btn {
            padding: 9px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            background: #003B5C;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .action-btn:hover {
            background: #002f4b;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 42px;
            color: #00D4AA;
            margin-bottom: 14px;
        }

        .footer-corp {
            margin-top: 50px;
            padding: 30px 0;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #64748b;
            font-size: 13px;
        }

        .footer-links {
            display: flex;
            gap: 30px;
        }

        .footer-links a {
            color: #475569;
            text-decoration: none;
            transition: 0.3s;
        }

        .footer-links a:hover {
            color: #00D4AA;
        }

        @media (max-width: 1200px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-form {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .corp-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
                padding: 15px;
            }

            .logo-area {
                justify-content: center;
            }

            .empresa-info h1 {
                font-size: 20px;
            }

            .user-area {
                flex-direction: column;
            }

            .user-badge,
            .logout-btn {
                width: 100%;
                justify-content: center;
            }

            .hero-minimal {
                flex-direction: column;
                align-items: flex-start;
            }

            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .filters-form {
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
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span>
        </a>
    </div>
</header>

<main class="main-container">

    <div class="hero-minimal">
        <div>
            <h2>Histórico de operaciones</h2>
            <p>Trazabilidad completa de despachos y entregas de cemento en Holcim.</p>
        </div>

        <div class="hero-actions">
            <div class="date-card">
                <i class="far fa-calendar-alt"></i>
                <?php echo date('d/m/Y'); ?>
            </div>
            <a href="index.php" class="btn-corp-secondary">
                <i class="fas fa-arrow-left"></i> Volver al panel
            </a>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card-corp">
            <div class="kpi-header">
                <div class="kpi-icon-corp">
                    <i class="fas fa-database"></i>
                </div>
            </div>
            <div class="kpi-value-corp"><?php echo $total_registros; ?></div>
            <div class="kpi-label-corp">Total de operaciones</div>
        </div>

        <div class="kpi-card-corp">
            <div class="kpi-header">
                <div class="kpi-icon-corp">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
            <div class="kpi-value-corp"><?php echo $total_entregados; ?></div>
            <div class="kpi-label-corp">Entregados</div>
        </div>

        <div class="kpi-card-corp">
            <div class="kpi-header">
                <div class="kpi-icon-corp">
                    <i class="fas fa-truck-moving"></i>
                </div>
            </div>
            <div class="kpi-value-corp"><?php echo $total_transito; ?></div>
            <div class="kpi-label-corp">En tránsito</div>
        </div>

        <div class="kpi-card-corp">
            <div class="kpi-header">
                <div class="kpi-icon-corp">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="kpi-value-corp"><?php echo $total_pendientes; ?></div>
            <div class="kpi-label-corp">Pendientes</div>
        </div>
    </div>

    <div class="content-card">
        <div class="section-title-corp">
            <div><i class="fas fa-filter"></i> Filtros de búsqueda</div>
            <span>Consulta por folio, fecha o estado</span>
        </div>

        <form method="GET" class="filters-form">
            <div class="form-group">
                <label for="buscar">Buscar</label>
                <input
                    type="text"
                    id="buscar"
                    name="buscar"
                    class="form-control"
                    placeholder="Folio, material, destino o chofer"
                    value="<?php echo htmlspecialchars($buscar); ?>"
                >
            </div>

            <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="Registrado" <?php echo ($estado === 'Registrado') ? 'selected' : ''; ?>>Registrado</option>
                    <option value="Cargado" <?php echo ($estado === 'Cargado') ? 'selected' : ''; ?>>Cargado</option>
                    <option value="En tránsito" <?php echo ($estado === 'En tránsito') ? 'selected' : ''; ?>>En tránsito</option>
                    <option value="Entregado" <?php echo ($estado === 'Entregado') ? 'selected' : ''; ?>>Entregado</option>
                    <option value="Cancelado" <?php echo ($estado === 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha">Fecha</label>
                <input
                    type="date"
                    id="fecha"
                    name="fecha"
                    class="form-control"
                    value="<?php echo htmlspecialchars($fecha); ?>"
                >
            </div>

            <button type="submit" class="btn-corp-primary">
                <i class="fas fa-magnifying-glass"></i> Filtrar
            </button>

            <a href="listar.php" class="btn-corp-secondary">
                <i class="fas fa-rotate-left"></i> Limpiar
            </a>
        </form>
    </div>

    <div class="content-card">
        <div class="section-title-corp">
            <div><i class="fas fa-clock-rotate-left"></i> Registro histórico</div>
            <span><?php echo count($registros_filtrados); ?> resultado(s)</span>
        </div>

        <div class="table-wrapper">
            <?php if (!empty($registros_filtrados)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Material</th>
                            <th>Cantidad</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Camión</th>
                            <th>Chofer</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros_filtrados as $registro): ?>
                            <tr>
                                <td class="folio-cell"><?php echo htmlspecialchars($registro['folio']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($registro['hora']); ?></td>
                                <td><?php echo htmlspecialchars($registro['material']); ?></td>
                                <td><?php echo htmlspecialchars($registro['cantidad'] . ' ' . $registro['unidad_medida']); ?></td>
                                <td><?php echo htmlspecialchars($registro['origen']); ?></td>
                                <td><?php echo htmlspecialchars($registro['destino']); ?></td>
                                <td><?php echo htmlspecialchars($registro['camion']); ?></td>
                                <td><?php echo htmlspecialchars($registro['chofer']); ?></td>
                                <td>
                                    <span class="<?php echo badgeClass($registro['estado']); ?>">
                                        <?php echo htmlspecialchars($registro['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="ver.php?id=<?php echo $registro['id']; ?>" class="action-btn">
                                        <i class="fas fa-eye"></i> Ver detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No se encontraron registros</h3>
                    <p>No hay operaciones que coincidan con los filtros seleccionados.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer-corp">
        <div>© 2025 Holcim México · Sistema de Gestión de Cemento</div>
        <div class="footer-links">
            <a href="#">Términos</a>
            <a href="#">Privacidad</a>
            <a href="#">Soporte</a>
            <a href="#">v2.0</a>
        </div>
    </footer>
</main>

</body>
</html>