<?php
// Habilitar errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión y verificar admin
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: inicio.php?error=acceso_no_autorizado");
    exit;
}

// Conexión a la base de datos
try {
    require_once __DIR__ . '/includes/config.php';
    
    // Verificar y crear tabla si no existe (adaptada a tu estructura)
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'Users'")->rowCount();
    if ($tableCheck == 0) {
        $pdo->exec("CREATE TABLE Users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) NOT NULL,
            apellido_paterno VARCHAR(50) NOT NULL,
            apellido_materno VARCHAR(50),
            usuario VARCHAR(50) NOT NULL UNIQUE,
            contraseña VARCHAR(255) NOT NULL,
            correo_electronico VARCHAR(100) NOT NULL UNIQUE,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_admin TINYINT(1) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Crear usuario admin inicial
        $adminPass = password_hash('Admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO Users (nombre, apellido_paterno, usuario, contraseña, correo_electronico, is_admin) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute(['Admin', 'Sistema', 'admin', $adminPass, 'admin@dominio.com', 1]);
    }
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar acciones CRUD
$mensaje = '';
$error = '';

try {
    // Obtener lista de usuarios (adaptada a tu estructura)
    $stmt = $pdo->query("SELECT id, nombre, apellido_paterno, apellido_materno, usuario, correo_electronico, is_admin, fecha_registro FROM Users ORDER BY fecha_registro DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar eliminación
    if (isset($_POST['eliminar'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id && $id != ($_SESSION['user_id'] ?? null)) {
            $stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
            $stmt->execute([$id]);
            $mensaje = "Usuario eliminado correctamente";
            // Actualizar lista
            $stmt = $pdo->query("SELECT id, nombre, apellido_paterno, apellido_materno, usuario, correo_electronico, is_admin, fecha_registro FROM Users ORDER BY fecha_registro DESC");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = "No puedes eliminarte a ti mismo";
        }
    }
    
    // Procesar actualización de rol
    if (isset($_POST['actualizar_rol'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        if ($id && $id != ($_SESSION['user_id'] ?? null)) {
            $stmt = $pdo->prepare("UPDATE Users SET is_admin = ? WHERE id = ?");
            $stmt->execute([$is_admin, $id]);
            $mensaje = "Rol de usuario actualizado";
            // Actualizar lista
            $stmt = $pdo->query("SELECT id, nombre, apellido_paterno, apellido_materno, usuario, correo_electronico, is_admin, fecha_registro FROM Users ORDER BY fecha_registro DESC");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = "No puedes cambiar tu propio rol";
        }
    }
    
    // Procesar creación de usuario (adaptada a tu estructura)
    if (isset($_POST['crear_usuario'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
        $apellido_materno = trim($_POST['apellido_materno'] ?? '');
        $username = trim($_POST['usuario'] ?? '');
        $email = filter_input(INPUT_POST, 'correo_electronico', FILTER_VALIDATE_EMAIL);
        $password = $_POST['contraseña'] ?? '';
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        // Validaciones
        $errores = [];
        if (empty($nombre)) $errores[] = "El nombre es requerido";
        if (empty($apellido_paterno)) $errores[] = "El apellido paterno es requerido";
        if (empty($username)) $errores[] = "El nombre de usuario es requerido";
        if (!$email) $errores[] = "Email inválido";
        if (strlen($password) < 8) $errores[] = "La contraseña debe tener al menos 8 caracteres";
        
        if (empty($errores)) {
            try {
                $pdo->beginTransaction();
                
                // Verificar si usuario o email ya existen
                $stmt = $pdo->prepare("SELECT id FROM Users WHERE usuario = ? OR correo_electronico = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->rowCount() == 0) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO Users (nombre, apellido_paterno, apellido_materno, usuario, contraseña, correo_electronico, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    
                    if ($stmt->execute([$nombre, $apellido_paterno, $apellido_materno, $username, $hash, $email, $is_admin])) {
                        $pdo->commit();
                        $mensaje = "Usuario creado correctamente";
                        // Actualizar lista
                        $stmt = $pdo->query("SELECT id, nombre, apellido_paterno, apellido_materno, usuario, correo_electronico, is_admin, fecha_registro FROM Users ORDER BY fecha_registro DESC");
                        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                } else {
                    $error = "El usuario o email ya están registrados";
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Error al crear usuario: " . $e->getMessage();
            }
        } else {
            $error = implode("<br>", $errores);
        }
    }
} catch (PDOException $e) {
    $error = "Error de sistema: " . $e->getMessage();
    error_log("Error en gestión de usuarios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --neon-orange: #ff7b00;
            --neon-blue: #0066ff;
            --neon-orange-light: #ff9d42;
            --neon-blue-light: #4d94ff;
            --dark-bg: #0f0f1a;
        }
        body {
            background-color: var(--dark-bg);
            color: #e0e0e0;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 123, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 90% 80%, rgba(0, 102, 255, 0.1) 0%, transparent 50%);
        }
        .admin-card {
            background: rgba(20, 20, 30, 0.8);
            border: 1px solid rgba(255, 123, 0, 0.2);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        .btn-primary {
            background: linear-gradient(45deg, var(--neon-orange), var(--neon-blue));
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 123, 0, 0.4);
        }
        .btn-danger {
            background: linear-gradient(45deg, #dc2626, #b91c1c);
        }
        .input-field {
            background: rgba(30, 30, 30, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        .input-field:focus {
            border-color: var(--neon-orange);
            box-shadow: 0 0 0 2px rgba(255, 123, 0, 0.2);
        }
    </style>
</head>
<body class="min-h-screen">
    <nav class="bg-gray-900 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-users text-2xl text-orange-400"></i>
                <span class="text-xl font-bold">Gestión de Usuarios</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Admin') ?></span>
                <a href="admin.php" class="bg-orange-600 hover:bg-orange-700 px-4 py-2 rounded text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        <?php if ($mensaje): ?>
            <div class="bg-green-900 text-green-100 p-4 rounded mb-6">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-900 text-red-100 p-4 rounded mb-6">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <!-- Formulario para crear usuario -->
            <div class="admin-card p-6">
                <h2 class="text-xl font-semibold text-orange-300 mb-4 flex items-center">
                    <i class="fas fa-user-plus mr-2"></i> Crear Nuevo Usuario
                </h2>
                <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Nombre *</label>
                        <input type="text" name="nombre" required class="w-full input-field rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Apellido Paterno *</label>
                        <input type="text" name="apellido_paterno" required class="w-full input-field rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Apellido Materno</label>
                        <input type="text" name="apellido_materno" class="w-full input-field rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Usuario *</label>
                        <input type="text" name="usuario" required class="w-full input-field rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Email *</label>
                        <input type="email" name="correo_electronico" required class="w-full input-field rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-300 mb-1">Contraseña *</label>
                        <input type="password" name="contraseña" required minlength="8" class="w-full input-field rounded px-3 py-2">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_admin" id="is_admin" class="h-4 w-4 text-orange-600 rounded">
                        <label for="is_admin" class="ml-2 text-sm text-gray-300">Administrador</label>
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" name="crear_usuario" class="btn-primary text-white px-4 py-2 rounded">
                            <i class="fas fa-plus mr-1"></i> Crear Usuario
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de usuarios -->
            <div class="admin-card p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-orange-300 flex items-center">
                        <i class="fas fa-users mr-2"></i> Usuarios Registrados
                    </h2>
                    <span class="text-sm bg-orange-900 text-orange-100 px-3 py-1 rounded-full">
                        Total: <?= count($usuarios) ?>
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-3 px-4">ID</th>
                                <th class="text-left py-3 px-4">Nombre Completo</th>
                                <th class="text-left py-3 px-4">Usuario</th>
                                <th class="text-left py-3 px-4">Email</th>
                                <th class="text-left py-3 px-4">Rol</th>
                                <th class="text-left py-3 px-4">Registro</th>
                                <th class="text-left py-3 px-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="7" class="py-4 text-center text-gray-400">No hay usuarios registrados</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <?php $isCurrentUser = ($usuario['id'] == ($_SESSION['user_id'] ?? null)); ?>
                                    <tr class="border-b border-gray-700 <?= $isCurrentUser ? 'bg-blue-900 bg-opacity-20' : '' ?>">
                                        <td class="py-3 px-4"><?= htmlspecialchars($usuario['id']) ?></td>
                                        <td class="py-3 px-4">
                                            <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno'] . ' ' . ($usuario['apellido_materno'] ?? '')) ?>
                                            <?php if ($isCurrentUser): ?>
                                                <span class="text-xs bg-blue-900 text-blue-100 px-2 py-0.5 rounded ml-2">Tú</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4"><?= htmlspecialchars($usuario['usuario']) ?></td>
                                        <td class="py-3 px-4"><?= htmlspecialchars($usuario['correo_electronico']) ?></td>
                                        <td class="py-3 px-4">
                                            <form method="post" class="inline">
                                                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                <input type="checkbox" name="is_admin" <?= $usuario['is_admin'] ? 'checked' : '' ?>
                                                    onchange="this.form.submit()"
                                                    <?= $isCurrentUser ? 'disabled' : '' ?>
                                                    class="h-4 w-4 text-orange-600 rounded">
                                                <input type="hidden" name="actualizar_rol" value="1">
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 text-sm"><?= date('d/m/Y H:i', strtotime($usuario['fecha_registro'])) ?></td>
                                        <td class="py-3 px-4">
                                            <?php if (!$isCurrentUser): ?>
                                                <form method="post" class="inline">
                                                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                    <button type="submit" name="eliminar" 
                                                            class="btn-danger text-white px-3 py-1 rounded text-sm"
                                                            onclick="return confirm('¿Eliminar a <?= htmlspecialchars(addslashes($usuario['nombre'])) ?>?')">
                                                        <i class="fas fa-trash-alt mr-1"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>