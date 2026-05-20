<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/config.php';

// Si ya está logueado, va al index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$mensaje = '';

// Solo muestra el mensaje si viene de cerrar sesión
if (isset($_GET['msg']) && $_GET['msg'] == 'sesion_cerrada') {
    echo "<script>history.replaceState({}, '', 'login.php');</script>";
    $mensaje = "Sesión cerrada correctamente";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    if (empty($usuario) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } else {
        try {
            // MODIFICADO: Ahora también obtenemos el rol_id y nos unimos con la tabla roles
            $stmt = $pdo->prepare("
                SELECT u.id, u.nombre, u.password, u.rol, u.rol_id, r.nombre as rol_nombre 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id
                WHERE (u.usuario = ? OR u.email = ?) AND u.estado = 1
            ");
            $stmt->execute([$usuario, $usuario]);
            $row = $stmt->fetch();

            if ($row) {
                if (password_verify($password, $row['password'])) {
                    // Guardar datos básicos en sesión
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_nombre'] = $row['nombre'];
                    $_SESSION['user_usuario'] = $usuario;
                    $_SESSION['user_rol'] = $row['rol']; // columna existente
                    $_SESSION['user_rol_id'] = $row['rol_id'];
                    
                    // Si tiene rol_id, obtener permisos (para admin)
                    if ($row['rol_id']) {
                        require_once 'includes/auth.php';
                        $_SESSION['permisos'] = obtenerPermisosUsuario($row['rol_id']);
                        registrarLog('login_exitoso');
                    }
                    
                    // Actualizar último acceso e IP
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $stmt2 = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW(), ultimo_ip = ? WHERE id = ?");
                    $stmt2->execute([$ip, $row['id']]);
                    
                    // REDIRECCIÓN SEGÚN ROL
                    // Si es admin (rol_id = 1 o rol = 'admin'), va al panel admin
                    if ($row['rol_id'] == 1 || $row['rol'] == 'admin') {
                        header("Location: admin/index.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $error = "Usuario/Email o contraseña incorrectos";
                }
            } else {
                $error = "Usuario/Email o contraseña incorrectos";
            }
        } catch (PDOException $e) {
            $error = "Error en la consulta: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim | Iniciar Sesión</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root{
            --primary:#003B5C;
            --primary-2:#0A5E8C;
            --accent:#00D4AA;
            --accent-2:#00B894;
            --bg:#EEF3F8;
            --panel:#FFFFFF;
            --panel-soft:rgba(255,255,255,0.88);
            --text:#0F172A;
            --muted:#64748B;
            --line:#D9E4EE;
            --line-strong:#C8D5E3;
            --input:#F7FAFC;
            --success:#0F766E;
            --success-bg:#ECFDF5;
            --danger:#B42318;
            --danger-bg:#FFF1F2;
            --shadow:0 20px 60px rgba(2, 32, 71, 0.18);
            --shadow-soft:0 10px 30px rgba(2, 32, 71, 0.10);
            --radius-xl:28px;
            --radius-lg:20px;
            --radius-md:16px;
            --radius-sm:12px;
            --transition:.28s ease;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        html, body{
            min-height:100%;
            overflow-x:hidden;
            width:100%;
        }

        body{
            font-family:'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(0,212,170,.16), transparent 28%),
                radial-gradient(circle at bottom right, rgba(10,94,140,.15), transparent 24%),
                linear-gradient(135deg, #ECF2F7 0%, #EAF0F5 45%, #F7FAFC 100%);
            color:var(--text);
            min-height:100dvh;
            display:flex;
            align-items:flex-start;
            justify-content:center;
            padding:14px;
            position:relative;
            overflow-x:hidden;
        }

        body::before{
            content:'';
            position:absolute;
            inset:0;
            background:linear-gradient(135deg, rgba(0,59,92,.96) 0%, rgba(8,78,118,.90) 45%, rgba(0,212,170,.18) 100%);
            clip-path:ellipse(120% 58% at 20% 0%);
            z-index:0;
        }

        body::after{
            content:'';
            position:absolute;
            width:320px;
            height:320px;
            right:-110px;
            bottom:-110px;
            border-radius:50%;
            background:radial-gradient(circle, rgba(0,212,170,.20) 0%, rgba(0,212,170,.06) 42%, transparent 72%);
            z-index:0;
            pointer-events:none;
        }

        .login-shell{
            width:100%;
            max-width:540px;
            position:relative;
            z-index:2;
            background:rgba(255,255,255,0.10);
            border:1px solid rgba(255,255,255,0.16);
            border-radius:24px;
            backdrop-filter:blur(12px);
            box-shadow:var(--shadow);
            overflow:hidden;
            margin-top:8px;
        }

        .login-brand{
            position:relative;
            background:linear-gradient(160deg, rgba(0,59,92,.98) 0%, rgba(7,78,119,.95) 58%, rgba(0,212,170,.82) 145%);
            color:#fff;
            padding:22px 18px;
        }

        .login-brand::before{
            content:'';
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at 15% 18%, rgba(255,255,255,.16), transparent 18%),
                radial-gradient(circle at 82% 78%, rgba(255,255,255,.10), transparent 20%);
            pointer-events:none;
        }

        .brand-content{
            position:relative;
            z-index:2;
        }

        .brand-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:8px 12px;
            border:1px solid rgba(255,255,255,.18);
            border-radius:999px;
            background:rgba(255,255,255,.08);
            backdrop-filter:blur(10px);
            font-size:11px;
            font-weight:700;
            letter-spacing:.4px;
            margin-bottom:14px;
        }

        .brand-logo-wrap{
            width:70px;
            height:70px;
            border-radius:18px;
            background:rgba(255,255,255,.12);
            border:1px solid rgba(255,255,255,.14);
            display:flex;
            align-items:center;
            justify-content:center;
            margin-bottom:14px;
            box-shadow:0 14px 35px rgba(0,0,0,.12);
            overflow:hidden;
        }

        .brand-logo-wrap img{
            max-width:50px;
            max-height:50px;
            object-fit:contain;
        }

        .login-brand h1{
            font-size:24px;
            font-weight:800;
            line-height:1.08;
            letter-spacing:-.8px;
            margin-bottom:8px;
        }

        .login-brand p{
            font-size:13px;
            line-height:1.55;
            color:rgba(255,255,255,.84);
            max-width:100%;
        }

        .brand-bottom{
            display:flex;
            flex-direction:column;
            align-items:flex-start;
            gap:8px;
            margin-top:14px;
        }

        .brand-meta{
            font-size:11px;
            color:rgba(255,255,255,.76);
        }

        .brand-status{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:8px 12px;
            border-radius:999px;
            background:rgba(255,255,255,.12);
            font-size:11px;
            font-weight:700;
        }

        .brand-status .dot{
            width:8px;
            height:8px;
            border-radius:50%;
            background:#7CFFC4;
            box-shadow:0 0 0 6px rgba(124,255,196,.16);
        }

        .login-panel{
            background:var(--panel-soft);
            backdrop-filter:blur(14px);
            padding:18px 14px 20px;
        }

        .login-card{
            width:100%;
        }

        .login-head{
            margin-bottom:20px;
        }

        .login-head .eyebrow{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:7px 11px;
            border-radius:999px;
            background:#E8F8F4;
            color:var(--success);
            font-size:11px;
            font-weight:800;
            letter-spacing:.5px;
            text-transform:uppercase;
            margin-bottom:12px;
        }

        .login-head h2{
            font-size:24px;
            font-weight:800;
            line-height:1.1;
            color:var(--text);
            margin-bottom:8px;
            letter-spacing:-.6px;
        }

        .login-head p{
            color:var(--muted);
            font-size:13px;
            line-height:1.55;
        }

        .message{
            display:flex;
            align-items:flex-start;
            gap:12px;
            padding:13px 14px;
            border-radius:14px;
            margin-bottom:16px;
            font-size:13px;
            line-height:1.5;
            border:1px solid transparent;
            animation:fadeUp .35s ease;
            box-shadow:var(--shadow-soft);
        }

        .message i{
            margin-top:2px;
            font-size:15px;
        }

        .message.success{
            background:var(--success-bg);
            border-color:#B7F0DF;
            color:var(--success);
        }

        .message.error{
            background:var(--danger-bg);
            border-color:#FECDD3;
            color:var(--danger);
        }

        @keyframes fadeUp{
            from{
                opacity:0;
                transform:translateY(8px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        .form-group{
            margin-bottom:16px;
        }

        .form-label{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            margin-bottom:8px;
        }

        .form-label label{
            font-size:11px;
            font-weight:800;
            color:#243447;
            text-transform:uppercase;
            letter-spacing:.7px;
        }

        .label-helper{
            font-size:11px;
            color:#94A3B8;
        }

        .input-group{
            position:relative;
        }

        .input-icon{
            position:absolute;
            top:50%;
            left:14px;
            transform:translateY(-50%);
            color:#94A3B8;
            font-size:14px;
            pointer-events:none;
            transition:var(--transition);
        }

        .form-control{
            width:100%;
            height:52px;
            padding:0 48px 0 42px;
            border:1.5px solid var(--line);
            background:var(--input);
            border-radius:14px;
            font-size:16px;
            font-weight:500;
            color:var(--text);
            transition:var(--transition);
            box-shadow:inset 0 1px 2px rgba(15,23,42,.02);
        }

        .form-control::placeholder{
            color:#94A3B8;
        }

        .form-control:focus{
            outline:none;
            border-color:rgba(0,212,170,.92);
            box-shadow:
                0 0 0 4px rgba(0,212,170,.12),
                0 10px 24px rgba(0,59,92,.08);
            transform:translateY(-1px);
            background:#fff;
        }

        .input-group:focus-within .input-icon{
            color:var(--primary);
        }

        .input-focus-border{
            position:absolute;
            inset:0;
            border-radius:14px;
            pointer-events:none;
            box-shadow:0 0 0 1px rgba(0,212,170,.30) inset;
            opacity:0;
            transition:var(--transition);
        }

        .form-control:focus + .input-focus-border,
        .input-group:focus-within .input-focus-border{
            opacity:1;
        }

        .password-toggle{
            position:absolute;
            top:50%;
            right:10px;
            transform:translateY(-50%);
            width:36px;
            height:36px;
            border:none;
            border-radius:10px;
            background:transparent;
            color:#64748B;
            cursor:pointer;
            transition:var(--transition);
        }

        .password-toggle:hover{
            background:#EEF2F7;
            color:var(--primary);
        }

        .form-options{
            display:flex;
            flex-direction:column;
            align-items:flex-start;
            gap:10px;
            margin:4px 0 18px;
        }

        .check-control{
            display:inline-flex;
            align-items:center;
            gap:10px;
            cursor:pointer;
            user-select:none;
            color:#475569;
            font-size:13px;
            font-weight:500;
        }

        .check-control input{
            display:none;
        }

        .check-mark{
            width:20px;
            height:20px;
            border-radius:6px;
            border:1.5px solid var(--line-strong);
            background:#fff;
            position:relative;
            transition:var(--transition);
            flex-shrink:0;
        }

        .check-mark::after{
            content:'';
            position:absolute;
            left:6px;
            top:2px;
            width:5px;
            height:10px;
            border:solid white;
            border-width:0 2px 2px 0;
            transform:rotate(45deg) scale(0);
            transition:var(--transition);
        }

        .check-control input:checked + .check-mark{
            background:linear-gradient(135deg, var(--primary), var(--primary-2));
            border-color:transparent;
            box-shadow:0 6px 18px rgba(0,59,92,.18);
        }

        .check-control input:checked + .check-mark::after{
            transform:rotate(45deg) scale(1);
        }

        .forgot-link{
            color:var(--primary);
            text-decoration:none;
            font-size:13px;
            font-weight:700;
            transition:var(--transition);
        }

        .forgot-link:hover{
            color:var(--accent-2);
        }

        .btn-login{
            width:100%;
            min-height:52px;
            border:none;
            border-radius:15px;
            background:linear-gradient(135deg, var(--primary) 0%, var(--primary-2) 100%);
            color:#fff;
            font-size:14px;
            font-weight:800;
            letter-spacing:.7px;
            text-transform:uppercase;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:12px;
            cursor:pointer;
            box-shadow:0 16px 34px rgba(0,59,92,.22);
            transition:var(--transition);
            position:relative;
            overflow:hidden;
        }

        .btn-login::before{
            content:'';
            position:absolute;
            inset:0;
            background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.18) 50%, transparent 100%);
            transform:translateX(-120%);
            transition:.7s ease;
        }

        .btn-login:hover{
            transform:translateY(-2px);
            box-shadow:0 22px 40px rgba(0,59,92,.28);
        }

        .btn-login:hover::before{
            transform:translateX(120%);
        }

        .btn-login:active{
            transform:translateY(0);
        }

        .btn-login.loading{
            pointer-events:none;
            opacity:.96;
        }

        .btn-login .btn-text,
        .btn-login .btn-icon,
        .btn-login .btn-loader{
            position:relative;
            z-index:2;
        }

        .btn-loader{
            display:none;
        }

        .btn-login.loading .btn-loader{
            display:inline-flex;
        }

        .btn-login.loading .btn-icon{
            display:none;
        }

        .spinner{
            width:18px;
            height:18px;
            border:2px solid rgba(255,255,255,.28);
            border-top-color:#fff;
            border-radius:50%;
            animation:spin .8s linear infinite;
        }

        @keyframes spin{
            to{
                transform:rotate(360deg);
            }
        }

        .login-footer{
            margin-top:18px;
            text-align:center;
        }

        .footer-line{
            display:flex;
            align-items:center;
            gap:14px;
            color:#94A3B8;
            font-size:12px;
            margin-bottom:14px;
        }

        .footer-line::before,
        .footer-line::after{
            content:'';
            flex:1;
            height:1px;
            background:linear-gradient(90deg, transparent, #D9E3EC, transparent);
        }

        .register-link{
            font-size:13px;
            color:#64748B;
            line-height:1.5;
            margin-bottom:14px;
        }

        .register-link a{
            color:var(--accent-2);
            font-weight:800;
            text-decoration:none;
        }

        .register-link a:hover{
            color:var(--primary);
        }

        .copy{
            font-size:11px;
            color:#94A3B8;
            line-height:1.4;
        }

        @media (max-width: 480px){
            body::after{
                width:220px;
                height:220px;
                right:-90px;
                bottom:-90px;
            }
        }

        @media (min-width: 768px){
            body{
                align-items:center;
                padding:24px;
            }

            body::after{
                width:380px;
                height:380px;
                right:-130px;
                bottom:-130px;
            }

            .login-shell{
                max-width:980px;
                display:grid;
                grid-template-columns:1.02fr .98fr;
                border-radius:30px;
                margin-top:0;
            }

            .login-brand{
                padding:48px 38px;
                min-height:650px;
                display:flex;
                flex-direction:column;
                justify-content:space-between;
            }

            .brand-badge{
                margin-bottom:22px;
                font-size:12px;
                padding:9px 14px;
            }

            .brand-logo-wrap{
                width:84px;
                height:84px;
                border-radius:22px;
                margin-bottom:24px;
            }

            .brand-logo-wrap img{
                max-width:60px;
                max-height:60px;
            }

            .login-brand h1{
                font-size:42px;
                margin-bottom:14px;
                letter-spacing:-1.1px;
            }

            .login-brand p{
                font-size:16px;
                line-height:1.7;
                max-width:500px;
            }

            .brand-bottom{
                flex-direction:row;
                justify-content:space-between;
                align-items:center;
                gap:16px;
                margin-top:26px;
                flex-wrap:wrap;
            }

            .brand-meta{
                font-size:13px;
            }

            .brand-status{
                font-size:13px;
                padding:10px 14px;
            }

            .login-panel{
                display:flex;
                align-items:center;
                justify-content:center;
                padding:38px;
            }

            .login-card{
                max-width:430px;
            }

            .login-head{
                margin-bottom:28px;
            }

            .login-head .eyebrow{
                font-size:12px;
                padding:8px 12px;
                margin-bottom:18px;
            }

            .login-head h2{
                font-size:32px;
                margin-bottom:10px;
                letter-spacing:-.8px;
            }

            .login-head p{
                font-size:15px;
                line-height:1.65;
            }

            .message{
                padding:15px 16px;
                border-radius:16px;
                margin-bottom:20px;
                font-size:14px;
            }

            .form-group{
                margin-bottom:20px;
            }

            .form-label{
                margin-bottom:10px;
            }

            .form-label label{
                font-size:13px;
            }

            .label-helper{
                font-size:12px;
            }

            .input-icon{
                left:16px;
                font-size:15px;
            }

            .form-control{
                height:56px;
                padding:0 52px 0 46px;
                border-radius:16px;
                font-size:15px;
            }

            .input-focus-border{
                border-radius:16px;
            }

            .password-toggle{
                right:14px;
                width:34px;
                height:34px;
            }

            .form-options{
                flex-direction:row;
                align-items:center;
                justify-content:space-between;
                gap:14px;
                margin:6px 0 26px;
                flex-wrap:wrap;
            }

            .check-control{
                font-size:14px;
            }

            .forgot-link{
                font-size:14px;
            }

            .btn-login{
                min-height:58px;
                border-radius:18px;
                font-size:15px;
                letter-spacing:.8px;
            }

            .login-footer{
                margin-top:26px;
            }

            .footer-line{
                font-size:13px;
                margin-bottom:18px;
            }

            .register-link{
                font-size:14px;
                margin-bottom:18px;
            }

            .copy{
                font-size:12px;
            }
        }
    </style>
</head>
<body>

<div class="login-shell">
    <aside class="login-brand">
        <div class="brand-content">
            <div class="brand-badge">
                <i class="fa-solid fa-shield-halved"></i>
                Plataforma segura
            </div>

            <div class="brand-logo-wrap">
                <img src="logo.jpg" alt="Holcim">
            </div>

            <h1>Bienvenido a Holcim</h1>
            <p>
                Accede a tu sistema de gestión integral para operar cargas, materiales,
                destinos, reportes y seguimiento diario con una experiencia moderna y profesional.
            </p>

            <div class="brand-bottom">
                <div class="brand-meta">Holcim México · Sistema empresarial</div>
                <div class="brand-status">
                    <span class="dot"></span>
                    Operativo
                </div>
            </div>
        </div>
    </aside>

    <section class="login-panel">
        <div class="login-card">
            <div class="login-head">
                <div class="eyebrow">
                    <i class="fa-solid fa-user-shield"></i>
                    Acceso de usuarios
                </div>
                <h2>Iniciar sesión</h2>
                <p>Ingresa tus credenciales para continuar dentro de la plataforma.</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo htmlspecialchars($mensaje); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <div class="form-label">
                        <label for="usuario">Usuario o Email</label>
                        <span class="label-helper">Acceso corporativo</span>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            class="form-control"
                            placeholder="Ingresa tu usuario o correo"
                            value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                            required
                        >
                        <span class="input-focus-border"></span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="password">Contraseña</label>
                        <span class="label-helper">Acceso protegido</span>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar contraseña">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                        <span class="input-focus-border"></span>
                    </div>
                </div>

                <div class="form-options">
                    <label class="check-control">
                        <input type="checkbox" name="remember">
                        <span class="check-mark"></span>
                        <span>Recordarme</span>
                    </label>

                    <a href="recuperar.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">Iniciar Sesión</span>
                    <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                    <span class="btn-loader"><span class="spinner"></span></span>
                </button>
            </form>

            <div class="login-footer">
                <div class="footer-line">Acceso seguro</div>

                <div class="register-link">
                    ¿No tienes una cuenta?
                    <a href="registro.php">Regístrate aquí</a>
                </div>

                <div class="copy">
                    © 2025 Holcim México · Todos los derechos reservados
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            this.innerHTML = isPassword
                ? '<i class="fa-regular fa-eye-slash"></i>'
                : '<i class="fa-regular fa-eye"></i>';
        });
    }

    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function () {
            loginBtn.classList.add('loading');
        });
    }
</script>

</body>
</html>