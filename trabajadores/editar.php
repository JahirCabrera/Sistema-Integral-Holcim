<?php
session_start();

$archivo = "data.json";

if (!file_exists($archivo)) {
    die("No existe el archivo de trabajadores.");
}

$trabajadores = json_decode(file_get_contents($archivo), true);

if (!is_array($trabajadores)) {
    $trabajadores = [];
}

$id = isset($_GET["id"]) ? intval($_GET["id"]) : -1;

if (!isset($trabajadores[$id])) {
    header("Location: listar.php");
    exit;
}

$trabajador = $trabajadores[$id];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trabajadores[$id]["nombre"] = trim($_POST["nombre"] ?? "");
    $trabajadores[$id]["apellido"] = trim($_POST["apellido"] ?? "");
    $trabajadores[$id]["puesto"] = trim($_POST["puesto"] ?? "");
    $trabajadores[$id]["telefono"] = trim($_POST["telefono"] ?? "");
    $trabajadores[$id]["area"] = trim($_POST["area"] ?? "");
    $trabajadores[$id]["estado"] = trim($_POST["estado"] ?? "Activo");

    file_put_contents($archivo, json_encode($trabajadores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header("Location: listar.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Holcim · Editar Trabajador</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    background:#eef2f6;
    min-height:100vh;
    color:#1e293b;
}

.container{
    max-width:950px;
    margin:auto;
    padding:30px 20px;
}

.page-header{
    background:#f4f7fa;
    padding:20px 24px;
    border-radius:16px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    gap:15px;
    flex-wrap:wrap;
    border:1px solid #e2e8f0;
    box-shadow:0 4px 20px rgba(0,59,92,0.05);
}

.page-header h2{
    color:#003B5C;
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:700;
    font-size:24px;
}

.page-header h2 i{
    color:#00D4AA;
}

.subtitle{
    color:#64748b;
    font-size:14px;
    margin-top:6px;
}

.btn-volver{
    background:#f1f5f9;
    border:1px solid #cbd5e1;
    color:#003B5C;
    padding:10px 18px;
    border-radius:30px;
    text-decoration:none;
    font-size:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    transition:0.2s;
    font-weight:600;
}

.btn-volver i{
    color:#003B5C;
    transition:0.2s;
}

.btn-volver:hover{
    border-color:#00D4AA;
    color:#00D4AA;
    background:#ecfdf5;
}

.btn-volver:hover i{
    color:#00D4AA;
}

.card{
    background:white;
    padding:30px;
    border-radius:18px;
    box-shadow:0 8px 24px rgba(0,0,0,0.05);
    border:1px solid #e2e8f0;
}

.section-title{
    font-size:16px;
    font-weight:700;
    color:#003B5C;
    margin-bottom:20px;
    padding-bottom:10px;
    border-bottom:2px solid #eef2f6;
    display:flex;
    align-items:center;
    gap:8px;
}

.section-title i{
    color:#00D4AA;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.form-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.form-group.full{
    grid-column:1 / -1;
}

label{
    font-size:13px;
    font-weight:700;
    color:#475569;
    text-transform:uppercase;
    letter-spacing:0.5px;
}

input, select{
    width:100%;
    padding:12px 14px;
    border:1px solid #dbe4ea;
    border-radius:12px;
    font-family:'Inter',sans-serif;
    font-size:14px;
    outline:none;
    transition:0.3s;
    background:white;
}

input:focus, select:focus{
    border-color:#00D4AA;
    box-shadow:0 0 0 3px rgba(0,212,170,0.10);
}

.helper{
    font-size:12px;
    color:#64748b;
}

.actions{
    display:flex;
    gap:12px;
    margin-top:25px;
    flex-wrap:wrap;
}

button{
    background:#003B5C;
    color:white;
    border:none;
    padding:14px 18px;
    border-radius:30px;
    flex:1;
    font-weight:600;
    font-size:14px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    transition:0.2s;
    min-width:200px;
}

button:hover{
    background:#002f4b;
    transform:translateY(-1px);
}

.btn-cancel{
    border:1px solid #dbe4ea;
    padding:14px 18px;
    border-radius:30px;
    text-align:center;
    flex:1;
    text-decoration:none;
    color:#003B5C;
    background:white;
    font-weight:600;
    font-size:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    transition:0.2s;
    min-width:200px;
}

.btn-cancel:hover{
    border-color:#00D4AA;
    color:#00D4AA;
    background:#f8fffd;
}

.id-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#e9eef2;
    color:#475569;
    border-radius:999px;
    padding:6px 12px;
    font-size:12px;
    font-weight:600;
    margin-bottom:20px;
}

@media (max-width: 768px){
    .container{
        padding:20px 15px;
    }

    .page-header{
        padding:18px;
        flex-direction:column;
        align-items:stretch;
    }

    .page-header h2{
        font-size:22px;
    }

    .btn-volver{
        width:100%;
    }

    .card{
        padding:20px;
    }

    .grid{
        grid-template-columns:1fr;
        gap:16px;
    }

    .form-group.full{
        grid-column:auto;
    }

    .actions{
        flex-direction:column;
    }

    button,
    .btn-cancel{
        width:100%;
        min-width:unset;
    }
}

@media (max-width: 480px){
    .page-header h2{
        font-size:20px;
    }

    .card{
        padding:16px;
    }

    input, select{
        padding:11px 12px;
        font-size:14px;
    }
}
</style>
</head>

<body>

<div class="container">

    <div class="page-header">
        <div>
            <h2>
                <i class="fas fa-user-edit"></i>
                Editar Trabajador
            </h2>
            <div class="subtitle">Actualiza la información del trabajador seleccionado</div>
        </div>

        <a href="listar.php" class="btn-volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card">
        <div class="id-badge">
            <i class="fas fa-id-badge"></i>
            Registro #<?php echo $id; ?>
        </div>

        <div class="section-title">
            <i class="fas fa-address-card"></i>
            Datos del trabajador
        </div>

        <form method="POST">
            <div class="grid">

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input
                        id="nombre"
                        name="nombre"
                        value="<?php echo htmlspecialchars($trabajador["nombre"] ?? ""); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input
                        id="apellido"
                        name="apellido"
                        value="<?php echo htmlspecialchars($trabajador["apellido"] ?? ""); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="puesto">Puesto</label>
                    <input
                        id="puesto"
                        name="puesto"
                        value="<?php echo htmlspecialchars($trabajador["puesto"] ?? ""); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input
                        id="telefono"
                        name="telefono"
                        value="<?php echo htmlspecialchars($trabajador["telefono"] ?? ""); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="Activo" <?php if (($trabajador["estado"] ?? "") == "Activo") echo "selected"; ?>>Activo</option>
                        <option value="Inactivo" <?php if (($trabajador["estado"] ?? "") == "Inactivo") echo "selected"; ?>>Inactivo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="area">Área</label>
                    <input
                        id="area"
                        name="area"
                        value="<?php echo htmlspecialchars($trabajador["area"] ?? ""); ?>"
                    >
                    <div class="helper">Ejemplo: Producción, Logística, Calidad</div>
                </div>

            </div>

            <div class="actions">
                <button type="submit">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>

                <a href="listar.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

</div>

</body>
</html>