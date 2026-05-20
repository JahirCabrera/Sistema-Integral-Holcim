<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$archivo = __DIR__ . "/data.json";

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

$contacto = $data[$id];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data[$id] = [
        "nombre" => trim($_POST["nombre"] ?? ''),
        "empresa" => trim($_POST["empresa"] ?? ''),
        "tipo" => trim($_POST["tipo"] ?? ''),
        "telefono" => trim($_POST["telefono"] ?? ''),
        "correo" => trim($_POST["correo"] ?? ''),
        "direccion" => trim($_POST["direccion"] ?? '')
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
    <title>Editar Contacto</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #f0f4f8;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: auto;
            padding: 30px;
        }
        .page-header {
            background: #f4f7fa;
            padding: 20px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .page-header h2 {
            color: #003B5C;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-header i {
            color: #00D4AA;
        }
        .btn-volver {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #003B5C;
            padding: 8px 18px;
            border-radius: 30px;
            text-decoration: none;
            display: flex;
            gap: 6px;
            align-items: center;
            transition: 0.2s;
        }
        .btn-volver:hover {
            border-color: #00D4AA;
            color: #00D4AA;
            background: #ecfdf5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        label {
            font-size: 13px;
            font-weight: 600;
            color: #003B5C;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        label i {
            color: #00D4AA;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Inter';
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        button {
            background: #003B5C;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 30px;
            flex: 1;
            font-weight: 500;
            cursor: pointer;
        }
        .btn-cancel {
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 30px;
            text-align: center;
            flex: 1;
            text-decoration: none;
            color: #003B5C;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <h2>
            <i class="fas fa-user-edit"></i>
            Editar Contacto
        </h2>
        <a href="listar.php" class="btn-volver">
            <i class="fas fa-home"></i> Volver
        </a>
    </div>

    <div class="card">
        <form method="POST">
            <div class="grid">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre</label>
                    <input name="nombre" value="<?php echo htmlspecialchars($contacto["nombre"] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-building"></i> Empresa</label>
                    <input name="empresa" value="<?php echo htmlspecialchars($contacto["empresa"] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-id-badge"></i> Tipo</label>
                    <select name="tipo">
                        <option value="Cliente" <?php echo (($contacto["tipo"] ?? '') == "Cliente") ? "selected" : ""; ?>>Cliente</option>
                        <option value="Proveedor" <?php echo (($contacto["tipo"] ?? '') == "Proveedor") ? "selected" : ""; ?>>Proveedor</option>
                        <option value="Transportista" <?php echo (($contacto["tipo"] ?? '') == "Transportista") ? "selected" : ""; ?>>Transportista</option>
                        <option value="Contratista" <?php echo (($contacto["tipo"] ?? '') == "Contratista") ? "selected" : ""; ?>>Contratista</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Teléfono</label>
                    <input name="telefono" value="<?php echo htmlspecialchars($contacto["telefono"] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Correo</label>
                    <input name="correo" value="<?php echo htmlspecialchars($contacto["correo"] ?? ''); ?>">
                </div>

                <div class="form-group full-width">
                    <label><i class="fas fa-map-marker-alt"></i> Dirección</label>
                    <input name="direccion" value="<?php echo htmlspecialchars($contacto["direccion"] ?? ''); ?>">
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