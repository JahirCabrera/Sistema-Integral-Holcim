<?php
session_start();
require_once '../includes/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Obtener ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

try {
    $sql = "SELECT * FROM destinos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $destino = $stmt->fetch();

    if (!$destino) {
        die("Destino no encontrado");
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$user_nombre = $_SESSION['user_nombre'] ?? 'Operador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Holcim · Editar Destino</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Inter', sans-serif;
    background:#f0f4f8;
}

/* HEADER */
.corp-header {
    background:#003B5C;
    color:white;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:4px solid #00D4AA;
}

.user-area {
    display:flex;
    gap:10px;
    align-items:center;
}

.back-btn {
    background:rgba(255,255,255,0.1);
    padding:8px 14px;
    border-radius:30px;
    color:white;
    text-decoration:none;
}

/* CONTENEDOR */
.container {
    max-width:700px;
    margin:30px auto;
    padding:20px;
}

/* CARD */
.card {
    background:white;
    padding:25px;
    border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

/* TITULO */
h2 {
    margin-bottom:20px;
    color:#003B5C;
}

/* FORM */
.form-group {
    margin-bottom:18px;
}

label {
    display:block;
    font-weight:600;
    margin-bottom:6px;
}

input, textarea {
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
    font-size:14px;
}

textarea {
    resize:vertical;
    min-height:80px;
}

/* BOTONES */
.buttons {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:15px;
}

.btn {
    flex:1;
    padding:12px;
    border:none;
    border-radius:30px;
    cursor:pointer;
    font-weight:600;
}

.btn-primary {
    background:#003B5C;
    color:white;
}

.btn-secondary {
    background:#e2e8f0;
}

/* RESPONSIVE */
@media(max-width:600px){
    .buttons {
        flex-direction:column;
    }
}
</style>
</head>

<body>

<header class="corp-header">
    <div>HOLCIM · Editar Destino</div>
    <div class="user-area">
        <span><?php echo htmlspecialchars($user_nombre); ?></span>
        <a href="listar.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
</header>

<div class="container">
    <div class="card">

        <h2><i class="fas fa-edit"></i> Editar Destino</h2>

        <form action="guardar.php" method="POST">

            <input type="hidden" name="id" value="<?php echo $destino['id']; ?>">

            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" required
                value="<?php echo htmlspecialchars($destino['nombre']); ?>">
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <textarea name="direccion"><?php echo htmlspecialchars($destino['direccion']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Contacto</label>
                <input type="text" name="contacto"
                value="<?php echo htmlspecialchars($destino['contacto']); ?>">
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="telefono"
                value="<?php echo htmlspecialchars($destino['telefono']); ?>">
            </div>

            <div class="buttons">
                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
                </button>

                <button type="button" class="btn btn-secondary"
                onclick="window.location.href='listar.php'">
                    Cancelar
                </button>
            </div>

        </form>

    </div>
</div>

</body>
</html>