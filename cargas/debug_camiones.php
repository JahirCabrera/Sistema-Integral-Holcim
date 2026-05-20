<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Depurar camiones</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f0f4f8; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #fff; padding: 15px; border-radius: 8px; overflow: auto; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #003B5C; color: white; }
    </style>
</head>
<body>
<h1>🔍 Depuración de Camiones</h1>";

$camionesArchivo = __DIR__ . "/../camiones/data.json";
echo "<h2>📁 Archivo de camiones</h2>";
echo "<p>Ruta: <code>" . $camionesArchivo . "</code></p>";

if (file_exists($camionesArchivo)) {
    echo "<p class='success'>✅ Archivo existe</p>";
    $contenido = file_get_contents($camionesArchivo);
    $tamano = filesize($camionesArchivo);
    echo "<p>📄 Tamaño: ". $tamano . " bytes</p>";
    
    $camiones = json_decode($contenido, true);
    
    if (is_array($camiones)) {
        echo "<p class='success'>✅ JSON válido - " . count($camiones) . " camiones encontrados</p>";
        
        echo "<h2>📋 Lista completa de camiones</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Placa</th><th>Marca</th><th>Modelo</th><th>Estado</th><th>Status</th><th>Condicion</th></tr>";
        
        foreach ($camiones as $i => $c) {
            $estado = $c['estado'] ?? 'NO DEFINIDO';
            $status = $c['status'] ?? 'NO DEFINIDO';
            $condicion = $c['condicion'] ?? 'NO DEFINIDO';
            $color = ($estado === "Disponible" || $status === "disponible") ? "style='background:#d4edda'" : "";
            
            echo "<tr $color>";
            echo "<td>$i</td>";
            echo "<td><strong>" . htmlspecialchars($c['placa'] ?? 'SIN PLACA') . "</strong></td>";
            echo "<td>" . htmlspecialchars($c['marca'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($c['modelo'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($estado) . "</td>";
            echo "<td>" . htmlspecialchars($status) . "</td>";
            echo "<td>" . htmlspecialchars($condicion) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2>🚛 Camiones que deben aparecer en el select (Disponibles)</h2>";
        echo "<ul>";
        $disponibles = 0;
        foreach ($camiones as $i => $c) {
            $estado = $c['estado'] ?? $c['status'] ?? '';
            if (strtolower($estado) === "disponible") {
                echo "<li class='success'>✅ ID $i: " . htmlspecialchars($c['placa'] ?? 'SIN PLACA') . " - Estado: $estado</li>";
                $disponibles++;
            } else {
                echo "<li class='error'>❌ ID $i: ". htmlspecialchars($c['placa'] ?? 'SIN PLACA') . " - Estado: $estado (no disponible)</li>";
            }
        }
        if ($disponibles == 0) {
            echo "<li class='error'>⚠️ NO HAY camiones con estado 'Disponible'</li>";
            echo "<li class='info'>💡 Solución: Edita algún camión y cámbiale el estado a 'Disponible'</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p class='error'>❌ Error: El JSON no es un array válido</p>";
        echo "<pre>" . htmlspecialchars($contenido) . "</pre>";
    }
} else {
    echo "<p class='error'>❌ Archivo NO existe</p>";
    echo "<p>Creando archivo...</p>";
    $datos = [];
    file_put_contents($camionesArchivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "<p class='success'>✅ Archivo creado. Ahora debes registrar camiones desde el módulo de camiones.</p>";
}

echo "<h2>🔧 Acciones</h2>";
echo "<p><a href='registrar.php' style='background:#003B5C; color:white; padding:10px 15px; text-decoration:none; border-radius:5px;'>← Volver a Registrar Carga</a></p>";
echo "<p><a href='../camiones/listar.php' style='background:#f59e0b; color:white; padding:10px 15px; text-decoration:none; border-radius:5px;'>🚛 Ir al módulo de camiones</a></p>";

echo "</body></html>";
?>