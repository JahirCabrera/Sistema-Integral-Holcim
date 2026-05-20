<?php
$archivo = __DIR__ . "/data.json";
echo "Ruta: " . $archivo . "<br>";

if (file_exists($archivo)) {
    echo "✅ Archivo existe<br>";
    echo "Contenido: <pre>";
    echo file_get_contents($archivo);
    echo "</pre>";
} else {
    echo "❌ Archivo NO existe<br>";
    
    // Intentar crear
    file_put_contents($archivo, json_encode([["test" => "Contacto de prueba"]], JSON_PRETTY_PRINT));
    echo "Archivo creado. Recarga esta página.";
}
?>