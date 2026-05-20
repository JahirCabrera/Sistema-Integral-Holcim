<?php
session_start();
require_once 'includes/config.php';

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar_password'] ?? '';
    $terminos = isset($_POST['terminos']) ? true : false;

    // Validaciones
    if (empty($nombre) || empty($email) || empty($usuario) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ingresa un correo electrónico válido";
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } elseif (!$terminos) {
        $error = "Debes aceptar los términos y condiciones";
    } else {
        try {
            // Verificar si el email o usuario ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR usuario = ?");
            $stmt->execute([$email, $usuario]);

            if ($stmt->rowCount() > 0) {
                $error = "El email o usuario ya está registrado";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $rol = 'operador'; // Valor por defecto

                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, usuario, password, rol, estado) VALUES (?, ?, ?, ?, ?, 1)");

                if ($stmt->execute([$nombre, $email, $usuario, $hash, $rol])) {
                    $success = "¡Registro exitoso! Ahora puedes iniciar sesión.";
                    // Limpiar campos
                    $nombre = $email = $usuario = '';
                } else {
                    $error = "Error al registrar el usuario";
                }
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holcim | Crear Cuenta</title>

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
            --panel-soft:rgba(255,255,255,0.90);
            --text:#0F172A;
            --muted:#64748B;
            --line:#D9E4EE;
            --line-strong:#C8D5E3;
            --input:#F7FAFC;
            --success:#0F766E;
            --success-bg:#ECFDF5;
            --danger:#B42318;
            --danger-bg:#FFF1F2;
            --shadow:0 24px 60px rgba(2, 32, 71, 0.18);
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
            width:100%;
            overflow-x:hidden;
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

        .register-shell{
            width:100%;
            max-width:560px;
            position:relative;
            z-index:2;
            margin:8px 0 18px;
        }

        .register-container{
            background:rgba(255,255,255,0.10);
            border:1px solid rgba(255,255,255,0.16);
            border-radius:24px;
            backdrop-filter:blur(12px);
            box-shadow:var(--shadow);
            overflow:hidden;
            animation:slideUp .45s ease;
        }

        @keyframes slideUp{
            from{
                opacity:0;
                transform:translateY(18px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        .register-brand{
            position:relative;
            background:linear-gradient(160deg, rgba(0,59,92,.98) 0%, rgba(7,78,119,.95) 58%, rgba(0,212,170,.82) 145%);
            color:#fff;
            padding:22px 18px;
        }

        .register-brand::before{
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

        .register-brand h1{
            font-size:24px;
            font-weight:800;
            line-height:1.08;
            letter-spacing:-.8px;
            margin-bottom:8px;
        }

        .register-brand p{
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

        .register-body{
            background:var(--panel-soft);
            backdrop-filter:blur(14px);
            padding:18px 14px 20px;
        }

        .register-head{
            margin-bottom:20px;
        }

        .register-head .eyebrow{
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

        .register-head h2{
            font-size:24px;
            font-weight:800;
            line-height:1.1;
            color:var(--text);
            margin-bottom:8px;
            letter-spacing:-.6px;
        }

        .register-head p{
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

        .form-row{
            display:grid;
            grid-template-columns:1fr;
            gap:0;
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
            padding:0 46px 0 42px;
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

        .toggle-password{
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

        .toggle-password:hover{
            background:#EEF2F7;
            color:var(--primary);
        }

        .password-requirements{
            background:#F8FAFC;
            border-radius:12px;
            padding:12px 14px;
            margin-top:6px;
            margin-bottom:16px;
            font-size:11px;
            border:1px solid #E2E8F0;
        }

        .requirements-title{
            font-size:11px;
            font-weight:800;
            letter-spacing:.6px;
            text-transform:uppercase;
            color:#475569;
            margin-bottom:8px;
        }

        .requirement-item{
            display:flex;
            align-items:center;
            gap:8px;
            color:#64748B;
            margin-bottom:6px;
            font-size:11px;
        }

        .requirement-item:last-child{
            margin-bottom:0;
        }

        .requirement-item i{
            width:14px;
            text-align:center;
            font-size:11px;
        }

        .requirement-item.valid{
            color:var(--success);
        }

        .requirement-item.invalid{
            color:#EF4444;
        }

        .terms-check{
            margin:18px 0;
        }

        .terms-label{
            display:flex;
            align-items:flex-start;
            gap:10px;
            cursor:pointer;
            font-size:13px;
            color:#475569;
            line-height:1.5;
        }

        .terms-label input[type="checkbox"]{
            width:17px;
            height:17px;
            accent-color:var(--accent);
            cursor:pointer;
            flex-shrink:0;
            margin-top:2px;
        }

        .terms-label a{
            color:var(--primary);
            text-decoration:none;
            font-weight:700;
        }

        .terms-label a:hover{
            color:var(--accent-2);
            text-decoration:underline;
        }

        .btn-register{
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
            margin-bottom:14px;
        }

        .btn-register::before{
            content:'';
            position:absolute;
            inset:0;
            background:linear-gradient(120deg, transparent 0%, rgba(255,255,255,.18) 50%, transparent 100%);
            transform:translateX(-120%);
            transition:.7s ease;
        }

        .btn-register:hover{
            transform:translateY(-2px);
            box-shadow:0 22px 40px rgba(0,59,92,.28);
        }

        .btn-register:hover::before{
            transform:translateX(120%);
        }

        .btn-register:active{
            transform:translateY(0);
        }

        .btn-register.loading{
            pointer-events:none;
            opacity:.96;
        }

        .btn-loader{
            display:none;
        }

        .btn-register.loading .btn-loader{
            display:inline-flex;
        }

        .btn-register.loading .btn-icon{
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

        .separator{
            display:flex;
            align-items:center;
            text-align:center;
            margin:16px 0 14px;
            color:#94A3B8;
            font-size:12px;
        }

        .separator::before,
        .separator::after{
            content:'';
            flex:1;
            border-bottom:1px solid #E2E8F0;
        }

        .separator::before{
            margin-right:12px;
        }

        .separator::after{
            margin-left:12px;
        }

        .login-section{
            text-align:center;
            margin-bottom:14px;
        }

        .login-link{
            color:#64748B;
            font-size:13px;
            line-height:1.5;
        }

        .login-link a{
            color:var(--accent-2);
            text-decoration:none;
            font-weight:800;
            margin-left:5px;
        }

        .login-link a:hover{
            color:var(--primary);
            text-decoration:underline;
        }

        .register-footer{
            text-align:center;
            padding-top:15px;
            border-top:1px solid #E2E8F0;
            color:#94A3B8;
            font-size:11px;
            line-height:1.5;
        }

        .footer-links{
            display:flex;
            justify-content:center;
            gap:10px;
            flex-wrap:wrap;
            margin-bottom:8px;
        }

        .register-footer a{
            color:#64748B;
            text-decoration:none;
            transition:color .3s;
        }

        .register-footer a:hover{
            color:var(--accent-2);
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

            .register-shell{
                max-width:1040px;
            }

            .register-container{
                display:grid;
                grid-template-columns:1.02fr .98fr;
                border-radius:30px;
            }

            .register-brand{
                padding:48px 38px;
                min-height:760px;
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

            .register-brand h1{
                font-size:42px;
                margin-bottom:14px;
                letter-spacing:-1.1px;
            }

            .register-brand p{
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

            .register-body{
                padding:34px 32px 30px;
                display:flex;
                flex-direction:column;
                justify-content:center;
            }

            .register-head{
                margin-bottom:26px;
            }

            .register-head .eyebrow{
                font-size:12px;
                padding:8px 12px;
                margin-bottom:18px;
            }

            .register-head h2{
                font-size:32px;
                margin-bottom:10px;
                letter-spacing:-.8px;
            }

            .register-head p{
                font-size:15px;
                line-height:1.65;
            }

            .message{
                padding:15px 16px;
                border-radius:16px;
                margin-bottom:20px;
                font-size:14px;
            }

            .form-row{
                grid-template-columns:1fr 1fr;
                gap:12px;
            }

            .form-group{
                margin-bottom:18px;
            }

            .form-label label{
                font-size:12px;
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
                padding:0 50px 0 46px;
                border-radius:16px;
                font-size:15px;
            }

            .toggle-password{
                right:12px;
                width:36px;
                height:36px;
            }

            .password-requirements{
                padding:14px 16px;
                border-radius:14px;
                font-size:12px;
            }

            .requirements-title{
                font-size:12px;
            }

            .requirement-item{
                font-size:12px;
            }

            .terms-label{
                font-size:14px;
            }

            .btn-register{
                min-height:58px;
                border-radius:18px;
                font-size:15px;
                letter-spacing:.8px;
                margin-bottom:16px;
            }

            .separator{
                font-size:13px;
                margin:18px 0 16px;
            }

            .login-link{
                font-size:14px;
            }

            .register-footer{
                font-size:12px;
            }
        }
    </style>
</head>
<body>

<div class="register-shell">
    <div class="register-container">
        <aside class="register-brand">
            <div class="brand-content">
                <div class="brand-badge">
                    <i class="fa-solid fa-user-plus"></i>
                    Alta segura de usuarios
                </div>

                <div class="brand-logo-wrap">
                    <img src="logo.jpg" alt="Holcim">
                </div>

                <h1>Crea tu cuenta en Holcim</h1>
                <p>
                    Regístrate para acceder al sistema de gestión integral y comenzar a operar
                    módulos, reportes, cargas, materiales y seguimiento diario con una interfaz moderna.
                </p>

                <div class="brand-bottom">
                    <div class="brand-meta">Holcim México · Plataforma empresarial</div>
                    <div class="brand-status">
                        <span class="dot"></span>
                        Registro activo
                    </div>
                </div>
            </div>
        </aside>

        <section class="register-body">
            <div class="register-head">
                <div class="eyebrow">
                    <i class="fa-solid fa-id-badge"></i>
                    Crear nueva cuenta
                </div>
                <h2>Registro de usuario</h2>
                <p>Completa la información para generar tu acceso dentro de la plataforma.</p>
            </div>

            <?php if ($error): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo htmlspecialchars($success); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <div class="form-label">
                        <label for="nombre">Nombre completo</label>
                        <span class="label-helper">Identificación del usuario</span>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            class="form-control"
                            placeholder="Juan Pérez López"
                            value="<?php echo htmlspecialchars($nombre ?? ''); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="email">Correo electrónico</label>
                        <span class="label-helper">Acceso y comunicación</span>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="ejemplo@holcim.com"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label">
                        <label for="usuario">Usuario</label>
                        <span class="label-helper">Alias de ingreso</span>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-id-card input-icon"></i>
                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            class="form-control"
                            placeholder="usuario123"
                            value="<?php echo htmlspecialchars($usuario ?? ''); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="form-label">
                            <label for="password">Contraseña</label>
                            <span class="label-helper">Mínimo 6 caracteres</span>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Crea tu contraseña"
                                required
                            >
                            <button type="button" class="toggle-password" data-target="password" aria-label="Mostrar contraseña">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-label">
                            <label for="confirmar_password">Confirmar</label>
                            <span class="label-helper">Debe coincidir</span>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="confirmar_password"
                                name="confirmar_password"
                                class="form-control"
                                placeholder="Repite la contraseña"
                                required
                            >
                            <button type="button" class="toggle-password" data-target="confirmar_password" aria-label="Mostrar contraseña">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="password-requirements">
                    <div class="requirements-title">Validación de contraseña</div>

                    <div class="requirement-item" id="req-length">
                        <i class="fa-solid fa-circle"></i>
                        <span>Mínimo 6 caracteres</span>
                    </div>

                    <div class="requirement-item" id="req-match">
                        <i class="fa-solid fa-circle"></i>
                        <span>Las contraseñas deben coincidir</span>
                    </div>
                </div>

                <div class="terms-check">
                    <label class="terms-label">
                        <input type="checkbox" name="terminos" required <?php echo isset($_POST['terminos']) ? 'checked' : ''; ?>>
                        <span>
                            Acepto los <a href="#">términos y condiciones</a> y la
                            <a href="#">política de privacidad</a>
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn-register" id="registerBtn">
                    <span class="btn-text">Crear cuenta</span>
                    <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                    <span class="btn-loader"><span class="spinner"></span></span>
                </button>
            </form>

            <div class="separator">¿Ya tienes cuenta?</div>

            <div class="login-section">
                <div class="login-link">
                    <a href="login.php">Inicia sesión aquí</a>
                </div>
            </div>

            <div class="register-footer">
                <div class="footer-links">
                    <a href="#">Términos</a>
                    <a href="#">Privacidad</a>
                    <a href="#">Soporte</a>
                </div>
                <div>© 2025 Holcim México</div>
            </div>
        </section>
    </div>
</div>

<script>
    const password = document.getElementById('password');
    const confirmar = document.getElementById('confirmar_password');
    const reqLength = document.getElementById('req-length');
    const reqMatch = document.getElementById('req-match');
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');

    function setRequirementState(el, valid) {
        el.classList.remove('valid', 'invalid');
        if (valid === true) el.classList.add('valid');
        if (valid === false) el.classList.add('invalid');
    }

    function validatePasswords() {
        const pass = password.value;
        const conf = confirmar.value;

        setRequirementState(reqLength, pass.length === 0 ? null : pass.length >= 6);
        setRequirementState(reqMatch, conf.length === 0 ? null : pass === conf);

        if (conf.length > 0) {
            if (pass === conf) {
                confirmar.style.borderColor = '#00D4AA';
            } else {
                confirmar.style.borderColor = '#EF4444';
            }
        } else {
            confirmar.style.borderColor = '#D9E4EE';
        }
    }

    password.addEventListener('keyup', validatePasswords);
    confirmar.addEventListener('keyup', validatePasswords);
    password.addEventListener('change', validatePasswords);
    confirmar.addEventListener('change', validatePasswords);

    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            this.innerHTML = isPassword
                ? '<i class="fa-regular fa-eye-slash"></i>'
                : '<i class="fa-regular fa-eye"></i>';
        });
    });

    if (registerForm && registerBtn) {
        registerForm.addEventListener('submit', function () {
            registerBtn.classList.add('loading');
        });
    }
</script>

</body>
</html>