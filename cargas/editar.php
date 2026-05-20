<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$archivo = __DIR__ . "/datos.json";

if (!file_exists($archivo)) {
    header("Location: listar.php?msg=error");
    exit;
}

$data = json_decode(file_get_contents($archivo), true);

if (!isset($_GET['id'])) {
    header("Location: listar.php?msg=error");
    exit;
}

$id = intval($_GET['id']);

if (!isset($data[$id])) {
    header("Location: listar.php?msg=error");
    exit;
}

$carga = $data[$id];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data[$id] = [
        "material" => trim($_POST["material"] ?? ''),
        "cantidad" => trim($_POST["cantidad"] ?? ''),
        "unidad" => trim($_POST["unidad"] ?? ''),
        "camion" => trim($_POST["camion"] ?? ''),
        "destino" => trim($_POST["destino"] ?? ''),
        "fecha" => trim($_POST["fecha"] ?? ''),
        "obs" => trim($_POST["obs"] ?? '')
    ];

    file_put_contents($archivo, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header("Location: listar.php?msg=updated");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Carga</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body{
            background:#eef2f6;
            font-family:'Inter',sans-serif;
            margin:0;
        }
        .container{
            max-width:900px;
            margin:auto;
            padding:30px;
        }
        .page-header{
            background:#f4f7fa;
            padding:20px;
            border-radius:16px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }
        .page-header h2{
            color:#003B5C;
            display:flex;
            gap:10px;
            align-items:center;
        }
        .page-header i{
            color:#00D4AA;
        }
        .btn-volver{
            background:#f1f5f9;
            border:1px solid #cbd5e1;
            color:#003B5C;
            padding:8px 18px;
            border-radius:30px;
            text-decoration:none;
            display:flex;
            align-items:center;
            gap:6px;
            transition:0.2s;
        }
        .btn-volver:hover{
            border-color:#00D4AA;
            color:#00D4AA;
            background:#ecfdf5;
        }
        .card{
            background:white;
            padding:30px;
            border-radius:16px;
            box-shadow:0 4px 20px rgba(0,0,0,0.05);
        }
        .grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        }
        .form-group{
            display:flex;
            flex-direction:column;
            gap:6px;
        }
        label{
            font-size:13px;
            font-weight:600;
            color:#003B5C;
            display:flex;
            gap:6px;
            align-items:center;
        }
        label i{
            color:#00D4AA;
        }
        input,select{
            width:100%;
            padding:12px;
            border:1px solid #ddd;
            border-radius:8px;
            font-family:'Inter';
        }
        .actions{
            display:flex;
            gap:10px;
            margin-top:20px;
        }
        button{
            background:#003B5C;
            color:white;
            border:none;
            padding:12px;
            border-radius:30px;
            flex:1;
            font-weight:500;
            cursor:pointer;
        }
        .btn-cancel{
            border:1px solid #ddd;
            padding:12px;
            border-radius:30px;
            text-align:center;
            flex:1;
            text-decoration:none;
            color:#003B5C;
        }
        .full-width{
            grid-column:1/-1;
        }
        @media (max-width:640px){
            .grid{
                grid-template-columns:1fr;
            }
            .container{
                padding:15px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <h2>
            <i class="fas fa-edit"></i>
            Editar Carga
        </h2>
        <a href="listar.php" class="btn-volver">
            <i class="fas fa-home"></i> Volver
        </a>
    </div>

    <div class="card">
        <form method="POST">
            <div class="grid">
                <div class="form-group">
                    <label><i class="fas fa-box"></i>Material</label>
                    <select name="material">
                        <option value="Cemento" <?php if(($carga["material"]??'')=="Cemento") echo "selected"; ?>>Cemento</option>
                        <option value="Arena" <?php if(($carga["material"]??'')=="Arena") echo "selected"; ?>>Arena</option>
                        <option value="Grava" <?php if(($carga["material"]??'')=="Grava") echo "selected"; ?>>Grava</option>
                        <option value="Cal" <?php if(($carga["material"]??'')=="Cal") echo "selected"; ?>>Cal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-cubes"></i>Cantidad</label>
                    <input name="cantidad" value="<?php echo htmlspecialchars($carga["cantidad"] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-weight-hanging"></i>Unidad</label>
                    <input name="unidad" value="<?php echo htmlspecialchars($carga["unidad"] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-truck"></i>Camión</label>
                    <input name="camion" value="<?php echo htmlspecialchars($carga["camion"] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i>Destino</label>
                    <input name="destino" value="<?php echo htmlspecialchars($carga["destino"] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-calendar"></i>Fecha</label>
                    <input type="date" name="fecha" value="<?php echo htmlspecialchars($carga["fecha"] ?? ''); ?>">
                </div>

                <div class="form-group full-width">
                    <label><i class="fas fa-pen"></i>Observaciones</label>
                    <input name="obs" value="<?php echo htmlspecialchars($carga["obs"] ?? ''); ?>">
                </div>
            </div>

            <div class="actions">
                <button><i class="fas fa-save"></i> Guardar Cambios</button>
                <a href="listar.php" class="btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>