<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Archivo donde buscarán los camiones los scripts de /cargas
$archivoCamiones = __DIR__ . "/camiones.json";

// Los camiones que quieres registrar
$camiones = [
    [
        "id" => 1,
        "placa" => "234-LKN",
        "marca" => "Kenworth",
        "modelo" => "2025",
        "capacidad" => "540.51",
        "estado" => "Disponible"
    ],
    [
        "id" => 2,
        "placa" => "524-LHG",
        "marca" => "Freightliner",
        "modelo" => "2018",
        "capacidad" => "10000.00",
        "estado" => "Disponible"
    ],
    [
        "id" => 3,
        "placa" => "55VFD51",
        "marca" => "Kenworth",
        "modelo" => "2024",
        "capacidad" => "1000.00",
        "estado" => "Disponible"
    ],
    [
        "id" => 4,
        "placa" => "ABC-123",
        "marca" => "Mercedes-Benz",
        "modelo" => "2024",
        "capacidad" => "15.00",
        "estado" => "Disponible"
    ],
    [
        "id" => 5,
        "placa" => "ZWP-551",
        "marca" => "Volvo",
        "modelo" => "2021",
        "capacidad" => "50.00",
        "estado" => "Disponible"
    ]
];

$resultado = file_put_contents($archivoCamiones, json_encode($camiones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "<!DOCTYPE html>
<html>
<head>
    <title>Crear Camiones en /cargas</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f0f4f8; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 8px; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 8px; overflow: auto; }
    </style>
</head>
<body>
<h1>🚛 Creando archivo de camiones en /cargas</h1>";

if ($resultado) {
    echo "<div class='success'>";
    echo "✅ Archivo creado correctamente<br>";
    echo "📁 Ruta: " . $archivoCamiones . "<br>";
    echo "🚛 Camiones guardados: " . count($camiones);
    echo "</div>";
    
    echo "<h2>📋 Contenido:</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($camiones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
} else {
    echo "<div class='error'>❌ Error al crear el archivo</div>";
}

echo "<h2>🔧 Acciones</h2>";
echo "<p><a href='registrar.php' style='background:#003B5C; color:white; padding:10px 15px; text-decoration:none; border-radius:5px;'>← Ir a Registrar Carga</a></p>";
echo "<p><a href='listar.php' style='background:#f59e0b; color:white; padding:10px 15px; text-decoration:none; border-radius:5px;'>📋 Ver Cargas</a></p>";

echo "</body></html>";
?>