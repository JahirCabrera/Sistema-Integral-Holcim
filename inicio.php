<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Redirigir si ya está logueado
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$mostrar_registro = isset($_POST['register']) || (isset($_GET['action']) && $_GET['action'] == 'register');

// Procesar Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_login = "Todos los campos son obligatorios";
    } else {
        // Buscar usuario por email
        $stmt = mysqli_prepare($conn, "SELECT id, nombre, usuario, password, rol FROM usuarios WHERE email = ? AND estado = 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_nombre'] = $row['nombre'];
                $_SESSION['user_usuario'] = $row['usuario'];
                $_SESSION['user_rol'] = $row['rol'];
                
                // Actualizar último acceso
                $stmt_update = mysqli_prepare($conn, "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                mysqli_stmt_bind_param($stmt_update, "i", $row['id']);
                mysqli_stmt_execute($stmt_update);
                
                header("Location: index.php");
                exit;
            } else {
                $error_login = "Email o contraseña incorrectos";
            }
        } else {
            $error_login = "Email o contraseña incorrectos";
        }
        mysqli_stmt_close($stmt);
    }
    $mostrar_registro = false;
}

// Procesar Registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $campos_requeridos = ['nombre', 'apellido_paterno', 'usuario', 'email', 'password', 'confirmar_password'];
    $mostrar_registro = true;

    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            $error_registro = "Todos los campos son obligatorios";
            break;
        }
    }

    if (!isset($error_registro)) {
        if ($_POST['password'] !== $_POST['confirmar_password']) {
            $error_registro = "Las contraseñas no coinciden";
        } elseif (strlen($_POST['password']) < 6) {
            $error_registro = "La contraseña debe tener al menos 6 caracteres";
        } else {
            // Verificar si existe
            $stmt = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE email = ? OR usuario = ?");
            mysqli_stmt_bind_param($stmt, "ss", $_POST['email'], $_POST['usuario']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error_registro = "El email o usuario ya está registrado";
            } else {
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $rol = 'operador'; // Por defecto
                
                $stmt_insert = mysqli_prepare($conn, "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, usuario, email, password, rol) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $apellido_materno = $_POST['apellido_materno'] ?? '';
                mysqli_stmt_bind_param($stmt_insert, "sssssss", 
                    $_POST['nombre'],
                    $_POST['apellido_paterno'],
                    $apellido_materno,
                    $_POST['usuario'],
                    $_POST['email'],
                    $hash,
                    $rol
                );
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    $_SESSION['user_id'] = mysqli_insert_id($conn);
                    $_SESSION['user_nombre'] = $_POST['nombre'];
                    $_SESSION['user_usuario'] = $_POST['usuario'];
                    $_SESSION['user_rol'] = $rol;
                    
                    header("Location: index.php");
                    exit;
                } else {
                    $error_registro = "Error al registrar: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt_insert);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim · Acceso al Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f0f2f5 0%, #e6e9f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: system-ui, -apple-system, sans-serif;
        }
        
        .auth-card {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 59, 92, 0.25);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card-header {
            background: #003B5C;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .card-header img {
            height: 50px;
            margin-bottom: 15px;
            filter: brightness(0) invert(1);
        }
        
        .card-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .card-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1e293b;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #003B5C;
            box-shadow: 0 0 0 4px rgba(0, 59, 92, 0.1);
        }
        
        .form-control.error {
            border-color: #dc2626;
            background: #fef2f2;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background: #003B5C;
            color: white;
        }
        
        .btn-primary:hover {
            background: #002f4b;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0, 59, 92, 0.3);
        }
        
        .btn-secondary {
            background: white;
            color: #003B5C;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            border-color: #003B5C;
            background: #f8fafc;
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #991b1b;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .switch-link {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 14px;
        }
        
        .switch-link a {
            color: #003B5C;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        
        .switch-link a:hover {
            text-decoration: underline;
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 500px) {
            .grid-2 {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="card-header">
            <img src="https://cdn.brandfetch.io/idZ5TRl0IY/w/1757/h/1757/theme/dark/icon.jpeg" alt="Holcim">
            <h1>Sistema de Gestión</h1>
            <p>Control integral de producción y despacho</p>
        </div>

        <div class="card-body">
            <?php if (isset($error_login) && !$mostrar_registro): ?>
                <div class="error-message">
                    ⚠️ <?= htmlspecialchars($error_login) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_registro) && $mostrar_registro): ?>
                <div class="error-message">
                    ⚠️ <?= htmlspecialchars($error_registro) ?>
                </div>
            <?php endif; ?>

            <!-- Formulario Login -->
            <form id="loginForm" method="post" class="form-section <?= !$mostrar_registro ? 'active' : '' ?>">
                <input type="hidden" name="login" value="1">
                
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" required class="form-control" placeholder="ejemplo@holcim.com" value="<?= isset($_POST['email']) && !$mostrar_registro ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required class="form-control" placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    INICIAR SESIÓN
                </button>
            </form>

            <!-- Formulario Registro -->
            <form id="registerForm" method="post" class="form-section <?= $mostrar_registro ? 'active' : '' ?>">
                <input type="hidden" name="register" value="1">
                
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required class="form-control" placeholder="Tu nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" required class="form-control" placeholder="Apellido paterno" value="<?= isset($_POST['apellido_paterno']) ? htmlspecialchars($_POST['apellido_paterno']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_materno" class="form-control" placeholder="Opcional" value="<?= isset($_POST['apellido_materno']) ? htmlspecialchars($_POST['apellido_materno']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="usuario" required class="form-control" placeholder="Nombre de usuario" value="<?= isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" required class="form-control" placeholder="ejemplo@correo.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" required class="form-control" placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar</label>
                        <input type="password" name="confirmar_password" required class="form-control" placeholder="Repite contraseña">
                    </div>
                </div>
                
                <div class="grid-2" style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary">
                        REGISTRARSE
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelBtn">
                        CANCELAR
                    </button>
                </div>
            </form>

            <div class="switch-link">
                <span id="switchText">
                    <?= $mostrar_registro ? '¿Ya tienes cuenta?' : '¿No tienes cuenta?' ?>
                </span>
                <a id="switchBtn">
                    <?= $mostrar_registro ? 'Inicia sesión' : 'Regístrate aquí' ?>
                </a>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const switchBtn = document.getElementById('switchBtn');
        const switchText = document.getElementById('switchText');

        switchBtn.addEventListener('click', function() {
            if (loginForm.classList.contains('active')) {
                loginForm.classList.remove('active');
                registerForm.classList.add('active');
                switchText.textContent = '¿Ya tienes cuenta?';
                switchBtn.textContent = 'Inicia sesión';
                history.pushState(null, null, '?action=register');
            } else {
                registerForm.classList.remove('active');
                loginForm.classList.add('active');
                switchText.textContent = '¿No tienes cuenta?';
                switchBtn.textContent = 'Regístrate aquí';
                history.pushState(null, null, window.location.pathname);
            }
        });

        document.getElementById('cancelBtn')?.addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    </script>
</body>
</html>