<?php
require_once '../includes/config.php';

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

// Obtener datos del formulario
$fecha = trim($_POST['fecha'] ?? '');
$hora = trim($_POST['hora'] ?? '');
$volumen = floatval($_POST['volumen'] ?? 0);
$destino_id = intval($_POST['destino_id'] ?? 0);
$unidad = trim($_POST['unidad'] ?? '');
$material = trim($_POST['material'] ?? '');
$chofer = trim($_POST['chofer'] ?? '');
$operador_carga = trim($_POST['operador_carga'] ?? '');
$entregado_por = trim($_POST['entregado_por'] ?? '');
$recibido_por = trim($_POST['recibido_por'] ?? '');

// Validar campos requeridos
if (
    empty($fecha) ||
    empty($hora) ||
    $volumen <= 0 ||
    $destino_id <= 0 ||
    empty($material) ||
    empty($chofer) ||
    empty($operador_carga) ||
    empty($entregado_por) ||
    empty($recibido_por)
) {
    die("Todos los campos obligatorios deben ser completados");
}

try {
    $pdo->beginTransaction();

    // 1. Insertar en despacho
    $sql = "INSERT INTO despacho (
                fecha,
                hora,
                volumen,
                destino_id,
                unidad,
                material,
                chofer,
                operador_carga,
                entregado_por,
                recibido_por
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $fecha,
        $hora,
        $volumen,
        $destino_id,
        $unidad,
        $material,
        $chofer,
        $operador_carga,
        $entregado_por,
        $recibido_por
    ]);

    // 2. Obtener ID del despacho recién creado
    $despacho_id = $pdo->lastInsertId();

    // 3. Buscar nombre del destino
    $sqlDestino = "SELECT nombre FROM destinos WHERE id = ?";
    $stmtDestino = $pdo->prepare($sqlDestino);
    $stmtDestino->execute([$destino_id]);
    $destinoData = $stmtDestino->fetch(PDO::FETCH_ASSOC);

    if (!$destinoData) {
        throw new Exception("Destino no encontrado");
    }

    $nombre_destino = $destinoData['nombre'];

    // 4. Preparar datos para histórico
    $folio = 'DSP-' . str_pad($despacho_id, 5, '0', STR_PAD_LEFT);
    $cantidad = $volumen;
    $unidad_medida = 'm3';
    $origen = 'Planta Holcim';
    $camion = !empty($unidad) ? $unidad : 'Sin unidad asignada';
    $estado = 'Registrado';
    $observaciones = 'Registro generado automáticamente desde despacho';

    // 5. Insertar en histórico
    $sqlHistorico = "INSERT INTO historico (
        despacho_id,
        folio,
        fecha,
        hora,
        material,
        cantidad,
        unidad_medida,
        origen,
        destino,
        camion,
        chofer,
        operador_carga,
        entregado_por,
        recibido_por,
        estado,
        observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtHistorico = $pdo->prepare($sqlHistorico);
    $stmtHistorico->execute([
        $despacho_id,
        $folio,
        $fecha,
        $hora,
        $material,
        $cantidad,
        $unidad_medida,
        $origen,
        $nombre_destino,
        $camion,
        $chofer,
        $operador_carga,
        $entregado_por,
        $recibido_por,
        $estado,
        $observaciones
    ]);

    $pdo->commit();

    header('Location: listar.php?msg=success');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error al guardar: " . $e->getMessage());
}
?>