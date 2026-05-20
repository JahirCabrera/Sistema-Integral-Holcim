<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$camionesArchivo = __DIR__ . "/../camiones/data.json";

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Camiones</title>
    <meta charset='UTF-8'>
    <style>
        body { 
            font-family: 'Segoe UI', 'Inter', monospace; 
            padding: 20px; 
            background: #f0f4f8; 
            margin: 0;
        }
        h1, h2, h3 { color: #003B5C; }
        .success { 
            color: green; 
            font-weight: bold; 
            background: #d4edda;
            padding: 10px;
            border-radius: 8px;
        }
        .error { 
            color: red; 
            font-weight: bold; 
            background: #f8d7da;
            padding: 10px;
            border-radius: 8px;
        }
        .warning {
            color: #856404;
            background: #fff3cd;
            padding: 10px;
            border-radius: 8px;
        }
        pre { 
            background: #2d2d2d; 
            color: #f8f8f2; 
            padding: 15px; 
            border-radius: 8px; 
            overflow: auto;
            font-size: 12px;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            background: white; 
            margin-top: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background: #003B5C; 
            color: white; 
        }
        tr:nth-child(even) { background: #f9f9f9; }
        .box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            background: #003B5C;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            margin-right: 10px;
            margin-top: 10px;
        }
        .btn-orange {
            background: #f59e0b;
        }
        code {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>
</head>
<body>
<h1>🔧 Diagnóstico de Camiones</h1>
<h3>Sistema de Gestión Holcim</h3>";

echo "<div class='box'>";
echo "<h2>📁 Archivo de camiones</h2>";
echo "<p><strong>Ruta:</strong> <code>" . $camionesArchivo . "</code></p>";

if (file_exists($camionesArchivo)) {
    echo "<p class='success'>✅ Archivo existe</p>";
    
    $contenido = file_get_contents($camionesArchivo);
    $tamano = filesize($camionesArchivo);
    echo "<p><strong>📄 Tamaño:</strong> " . $tamano . " bytes</p>";
    
    $camiones = json_decode($contenido, true);
    
    if (is_array($camiones)) {
        echo "<p class='success'>✅ JSON válido - <strong>" . count($camiones) . " camiones encontrados</strong></p>";
        
        // Mostrar el JSON completo
        echo "<h2>📋 Contenido completo del JSON</h2>";
        echo "<pre>" . htmlspecialchars(json_encode($camiones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
        
        // Mostrar estructura del primer camión
        echo "<h2>🔍 Estructura del primer camión</h2>";
        echo "<pre>";
        if (!empty($camiones)) {
            print_r($camiones[0]);
        } else {
            echo "No hay camiones en el array";
        }
        echo "</pre>";
        
        // Tabla de todos los camiones con sus propiedades
        echo "<h2>🚛 Listado completo de camiones</h2>";
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Valor que se mostraría</th>";
        echo "<th>Todas las propiedades</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($camiones as $i => $c) {
            // Intentar construir el texto que aparecería en el select
            $textoMostrar = '';
            
            // Buscar PLACA
            if (!empty($c['placa'])) {
                $textoMostrar = $c['placa'];
            } elseif (!empty($c['PLACA'])) {
                $textoMostrar = $c['PLACA'];
            } elseif (!empty($c['matricula'])) {
                $textoMostrar = $c['matricula'];
            } elseif (!empty($c['Matricula'])) {
                $textoMostrar = $c['Matricula'];
            } elseif (!empty($c['id'])) {
                $textoMostrar = 'ID: ' . $c['id'];
            } else {
                $textoMostrar = '❌ Sin placa';
            }
            
            // Buscar MARCA/MODELO
            if (!empty($c['marca'])) {
                $textoMostrar .= ' - ' . $c['marca'];
            } elseif (!empty($c['modelo'])) {
                $textoMostrar .= ' - ' . $c['modelo'];
            } elseif (!empty($c['MARCA'])) {
                $textoMostrar .= ' - ' . $c['MARCA'];
            } elseif (!empty($c['MODELO'])) {
                $textoMostrar .= ' - ' . $c['MODELO'];
            } elseif (!empty($c['modelo_marca'])) {
                $textoMostrar .= ' - ' . $c['modelo_marca'];
            } elseif (!empty($c['marca_modelo'])) {
                $textoMostrar .= ' - ' . $c['marca_modelo'];
            }
            
            $propiedades = implode(', ', array_keys($c));
            
            echo "<tr>";
            echo "<td><strong>#$i</strong></td>";
            echo "<td><code>" . htmlspecialchars($textoMostrar) . "</code></td>";
            echo "<td><small>" . htmlspecialchars($propiedades) . "</small></td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        
        // Sugerencias para el código
        echo "<h2>💡 Sugerencias para registrar.php</h2>";
        echo "<div class='box'>";
        if (!empty($camiones)) {
            $props = array_keys($camiones[0]);
            echo "<p>Tu JSON tiene estas propiedades: <strong>" . implode(', ', $props) . "</strong></p>";
            echo "<p>Para que el select muestre los camiones correctamente, debes usar:</p>";
            echo "<ul>";
            foreach ($props as $prop) {
                echo "<li><code>\$camion['" . htmlspecialchars($prop) . "']</code></li>";
            }
            echo "</ul>";
            
            // Verificar si tiene las propiedades necesarias
            $tienePlaca = in_array('placa', $props) || in_array('PLACA', $props) || in_array('matricula', $props);
            $tieneMarca = in_array('marca', $props) || in_array('modelo', $props) || in_array('MARCA', $props) || in_array('MODELO', $props);
            
            if (!$tienePlaca) {
                echo "<p class='warning'>⚠️ <strong>Advertencia:</strong> No se encontró una propiedad que identifique la placa del camión.</p>";
                echo "<p>Sugerencia: Usa <code>\$camion['" . $props[0] . "']</code> como identificador principal.</p>";
            }
            
            if (!$tieneMarca) {
                echo "<p class='warning'>⚠️ <strong>Advertencia:</strong> No se encontró una propiedad que identifique la marca/modelo del camión.</p>";
                echo "<p>El select mostrará solo la placa o el ID.</p>";
            }
        }
        echo "</div>";
        
        // Código listo para copiar
        echo "<h2>📝 Código listo para pegar en registrar.php</h2>";
        echo "<div class='box'>";
        echo "<p>Reemplaza el <strong>select de camiones</strong> en <code>registrar.php</code> con este código:</p>";
        echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 15px; overflow-x: auto;'>";
        echo htmlspecialchars('<?php foreach ($camiones as $camion): ?>
    <?php
    // Construir el texto a mostrar según la estructura detectada
    $textoMostrar = "";
    
    // PLACA - ajusta según tu estructura
    if (!empty($camion["placa"])) {
        $textoMostrar = $camion["placa"];
    } elseif (!empty($camion["PLACA"])) {
        $textoMostrar = $camion["PLACA"];
    } elseif (!empty($camion["matricula"])) {
        $textoMostrar = $camion["matricula"];
    } else {
        $textoMostrar = "ID: " . ($camion["id"] ?? "?");
    }
    
    // MARCA/MODELO - ajusta según tu estructura
    if (!empty($camion["marca"])) {
        $textoMostrar .= " - " . $camion["marca"];
    } elseif (!empty($camion["modelo"])) {
        $textoMostrar .= " - " . $camion["modelo"];
    } elseif (!empty($camion["MARCA"])) {
        $textoMostrar .= " - " . $camion["MARCA"];
    }
    ?>
    <option value="<?php echo htmlspecialchars($textoMostrar); ?>">
        <?php echo htmlspecialchars($textoMostrar); ?>
    </option>
<?php endforeach; ?>');
        echo "</pre>";
        echo "<p class='success'>✅ Copia este código y pégalo donde está el select de camiones.</p>";
        echo "</div>";
        
    } else {
        echo "<p class='error'>❌ Error: El JSON no es un array válido</p>";
        echo "<p>Contenido actual:</p>";
        echo "<pre>" . htmlspecialchars($contenido) . "</pre>";
    }
} else {
    echo "<p class='error'>❌ Archivo NO existe</p>";
    echo "<p>La ruta buscada es: <code>" . $camionesArchivo . "</code></p>";
    echo "<p>Verifica que la carpeta <code>camiones/</code> exista y que dentro tenga el archivo <code>data.json</code></p>";
    
    // Intentar crear el archivo
    echo "<p>Intentando crear el archivo...</p>";
    if (!is_dir(dirname($camionesArchivo))) {
        echo "<p class='error'>❌ La carpeta 'camiones/' no existe. Créala manualmente.</p>";
    } else {
        $datos = [];
        file_put_contents($camionesArchivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "<p class='success'>✅ Archivo creado. Ahora debes registrar camiones desde el módulo de camiones.</p>";
    }
}

echo "</div>";

// Información adicional
echo "<div class='box'>";
echo "<h2>📊 Información del sistema</h2>";
echo "<ul>";
echo "<li><strong>Ruta actual:</strong> " . __DIR__ . "</li>";
echo "<li><strong>Ruta raíz del proyecto:</strong> " . dirname(__DIR__) . "</li>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>Usuario logueado:</strong> " . htmlspecialchars($_SESSION['user_nombre'] ?? 'No disponible') . "</li>";
echo "</ul>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>🔧 Acciones</h2>";
echo "<a href='registrar.php' class='btn'>← Volver a Registrar Carga</a>";
echo "<a href='../camiones/listar.php' class='btn btn-orange'>🚛 Ir a Camiones</a>";
echo "<a href='listar.php' class='btn'>📋 Ver Cargas</a>";
echo "</div>";

echo "<p style='text-align: center; margin-top: 30px; color: #666;'>© 2026 Holcim México - Herramienta de Diagnóstico</p>";
echo "</body></html>";
?>