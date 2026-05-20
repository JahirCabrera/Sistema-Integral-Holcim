<?php
$archivo = __DIR__ . "/data.json";
echo "<h2>Verificando data.json</h2>";

if (file_exists($archivo)) {
    echo "✅ El archivo existe<br>";
    echo "📁 Ruta: " . realpath($archivo) . "<br>";
    echo "📄 Tamaño: " . filesize($archivo) . " bytes<br>";
    echo "<h3>Contenido:</h3>";
    echo "<pre>";
    $contenido = file_get_contents($archivo);
    echo htmlspecialchars($contenido);
    echo "</pre>";
} else {
    echo "❌ El archivo NO existe en: " . __DIR__ . "/data.json<br>";
}
?>