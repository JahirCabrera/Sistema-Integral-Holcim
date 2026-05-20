<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

requiereAdmin();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) header("Location: usuarios.php");

$roles = $pdo->query("SELECT * FROM roles")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $usuario_nombre = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $rol_id = $_POST['rol_id'];
    
    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE usuarios SET nombre=?, usuario=?, email=?, password=?, rol_id=? WHERE id=?")->execute([$nombre, $usuario_nombre, $email, $hash, $rol_id, $id]);
    } else {
        $pdo->prepare("UPDATE usuarios SET nombre=?, usuario=?, email=?, rol_id=? WHERE id=?")->execute([$nombre, $usuario_nombre, $email, $rol_id, $id]);
    }
    
    registrarLog('editar_usuario', 'usuarios', $id);
    header("Location: usuarios.php?mensaje=Usuario actualizado");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | Holcim Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #0f3d5e;
            --primary-dark: #0a2c44;
            --secondary: #00c896;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-500: #64748b;
            --gray-700: #334155;
            --danger: #ef4444;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --radius-lg: 12px;
            --radius-xl: 16px;
            --spacing-lg: 16px;
            --spacing-xl: 20px;
            --spacing-2xl: 24px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: linear-gradient(135deg, var(--gray-100) 0%, #e2e8f0 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; border-radius: var(--radius-xl); box-shadow: var(--shadow); max-width: 550px; width: 100%; overflow: hidden; }
        .card-header { padding: var(--spacing-xl); border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-wrap: wrap; gap: 12px; }
        .card-header h2 { font-size: 18px; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        .card-body { padding: var(--spacing-2xl); }
        .form-group { margin-bottom: var(--spacing-xl); }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: var(--gray-700); }
        .form-label .required { color: var(--danger); margin-left: 2px; }
        .form-control { width: 100%; padding: 12px 14px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-lg); font-size: 15px; transition: all 0.2s; }
        .form-control:focus { outline: none; border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(0, 200, 150, 0.1); }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E"); background-position: right 12px center; background-repeat: no-repeat; background-size: 20px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 24px; border-radius: var(--radius-lg); font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; min-height: 48px; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; }
        .btn-primary:active { transform: scale(0.97); }
        .btn-outline { background: transparent; border: 1px solid var(--gray-200); color: var(--gray-700); }
        .btn-sm { padding: 8px 16px; font-size: 13px; min-height: 38px; }
        small { font-size: 11px; color: var(--gray-500); display: block; margin-top: 6px; }
        @media (max-width: 640px) { 
            body { padding: 16px; } 
            .card-body { padding: var(--spacing-xl); } 
            .btn { width: 100%; } 
            .card-header { flex-direction: column; align-items: flex-start; } 
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-edit"></i> Editar Usuario</h2>
            <a href="usuarios.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group"><label class="form-label">Nombre <span class="required">*</span></label><input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required></div>
                <div class="form-group"><label class="form-label">Usuario <span class="required">*</span></label><input type="text" name="usuario" class="form-control" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required></div>
                <div class="form-group"><label class="form-label">Email <span class="required">*</span></label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required></div>
                <div class="form-group"><label class="form-label">Nueva Contraseña</label><input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar"><small>Mínimo 6 caracteres</small></div>
                <div class="form-group"><label class="form-label">Rol <span class="required">*</span></label><select name="rol_id" class="form-control"><?php foreach($roles as $rol): ?><option value="<?php echo $rol['id']; ?>" <?php echo $usuario['rol_id'] == $rol['id'] ? 'selected' : ''; ?>><?php echo ucfirst($rol['nombre']); ?></option><?php endforeach; ?></select></div>
                <div style="display: flex; gap: 12px; margin-top: 28px; flex-wrap: wrap;"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button><a href="usuarios.php" class="btn btn-outline">Cancelar</a></div>
            </form>
        </div>
    </div>
</body>
</html>