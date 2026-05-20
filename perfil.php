<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: inicio.php");
    exit;
}

require __DIR__ . '/includes/config.php';

$userId = $_SESSION['user_id'];

// ================== USUARIO ==================
$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: Usuario no encontrado.");
}

// Avatar seguro (fallback)
$avatarFile = !empty($user['avatar']) ? $user['avatar'] : 'avatar1.png';
$avatarPath = "uploads/avatars/" . $avatarFile;

// Nombre completo seguro (evita dobles espacios si falta apellido)
$nombreCompleto = trim(
    ($user['nombre'] ?? '') . ' ' .
    ($user['apellido_paterno'] ?? '') . ' ' .
    ($user['apellido_materno'] ?? '')
);
if ($nombreCompleto === '') $nombreCompleto = 'Sin nombre';

// ================== ESTADÍSTICAS ==================
// Juegos completados por tipo
$stmt = $pdo->prepare("
    SELECT g.name AS game_name, COUNT(*) AS total
    FROM game_sessions gs
    INNER JOIN games g ON gs.game_id = g.id
    WHERE gs.user_id = ? AND gs.completed = 1
    GROUP BY g.name
");
$stmt->execute([$userId]);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sudoku_completed = 0;
$hanoi_completed  = 0;

foreach ($stats as $g) {
    if (($g['game_name'] ?? '') === 'sudoku') $sudoku_completed = (int)$g['total'];
    if (($g['game_name'] ?? '') === 'hanoi')  $hanoi_completed  = (int)$g['total'];
}

// ================== LOGROS ==================
$stmt = $pdo->prepare("
    SELECT a.title, a.description
    FROM user_achievements ua
    INNER JOIN achievements a ON ua.achievement_id = a.id
    WHERE ua.user_id = ?
    ORDER BY a.id ASC
");
$stmt->execute([$userId]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
$achievementsCount = count($achievements);

// ================== XP ==================
$XP_SUDOKU = 20;
$XP_HANOI  = 20;
$XP_LOGRO  = 5;

$totalXp =
    ($sudoku_completed * $XP_SUDOKU) +
    ($hanoi_completed  * $XP_HANOI) +
    ($achievementsCount * $XP_LOGRO);

$XP_LEVEL  = 100;
$level     = intdiv($totalXp, $XP_LEVEL) + 1;
$xpInLevel = $totalXp % $XP_LEVEL;
$xpPercent = ($xpInLevel / $XP_LEVEL) * 100;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Logicore</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root{
            /* Tema oscuro por defecto */
            --bg: #000000;
            --text-main: #ffffff;
            --text-muted: #9ca3af;
            --nav-bg: #000000;
            --card-bg: rgba(255,255,255,0.05);
            --card-border: rgba(255,255,255,0.08);
            --achievement-bg: rgba(255,255,255,0.06);
            --achievement-border: rgba(255,255,255,0.08);
        }

        body{
            background: radial-gradient(circle at center top, #1a1a1a 10%, #000000 70%);
            background-color: var(--bg);
            color: var(--text-main);
            font-family: 'Segoe UI', sans-serif;
            padding-bottom: 80px;
        }

        /* 🌞 MODO CLARO */
        body.light-theme{
            --bg: #f3f4f6;
            --text-main: #1f2937;
            --text-muted: #4b5563;
            --nav-bg: #ffffff;
            --card-bg: rgba(255,255,255,0.95);
            --card-border: rgba(209,213,219,1);
            --achievement-bg: rgba(243,244,246,1);
            --achievement-border: rgba(209,213,219,1);

            background: radial-gradient(circle at center top, #ffffff 10%, #e5e7eb 70%);
        }

        /* NAV */
        .navbar{
            background: var(--nav-bg) !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        body.light-theme .navbar{
            border-bottom-color: rgba(0,0,0,0.08);
        }

        .navbar-brand{
            font-size: 22px;
            letter-spacing: 3px;
            font-weight: 800;
            color: #ffae00 !important;
            white-space: nowrap;
        }

        .navbar-text-custom{
            color: var(--text-main);
        }

        /* Evita que se rompa el header en móvil */
        .nav-right{
            min-width: 0;
        }
        .nav-user{
            min-width: 0;
            max-width: 52vw;
        }

        /* AVATAR */
        .avatar-wrapper{
            text-align: center;
            margin-top: 22px;
        }
        .avatar-img{
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 4px solid #ffae00;
            box-shadow: 0 0 25px rgba(255,174,0,0.75);
            object-fit: cover;
        }

        .username{
            font-size: 32px;
            font-weight: 800;
            line-height: 1.1;
            text-shadow: 0 0 12px rgba(255, 136, 0, 0.45);
            word-break: break-word;
        }

        /* TARJETAS */
        .card-custom{
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            backdrop-filter: blur(6px);
            transition: 0.25s;
            color: var(--text-main);
            height: 100%;
        }
        body.light-theme .card-custom{
            box-shadow: 0 10px 25px rgba(15,23,42,0.15);
        }
        .card-custom:hover{
            transform: translateY(-3px);
            box-shadow: 0 0 25px rgba(255, 140, 0, 0.25);
        }

        .section-title{
            color: #ffae42;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 0 8px rgba(255,140,0,0.35);
        }

        /* BARRA DE XP */
        .progress{
            background: rgba(255,255,255,0.1);
            height: 10px;
            border-radius: 50px;
        }
        body.light-theme .progress{
            background: rgba(15,23,42,0.08);
        }
        .progress-bar{
            background: linear-gradient(90deg, #ff8a00, #ffae00);
            border-radius: 50px;
        }

        /* BOTONES */
        .btn-orange{
            background: linear-gradient(90deg, #ff8a00, #ffae00);
            color: #000;
            border: none;
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 0 12px rgba(255,170,0,0.45);
            transition: .2s;
        }
        .btn-orange:hover{
            transform: translateY(-1px);
            box-shadow: 0 0 20px rgba(255,170,0,0.75);
        }

        .btn-red{
            background: linear-gradient(90deg, #ff4444, #ff0000);
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 0 12px rgba(255,0,0,0.35);
        }
        .btn-red:hover{
            transform: translateY(-1px);
            box-shadow: 0 0 20px rgba(255,0,0,0.7);
        }

        /* LOGROS */
        .achievement-box{
            background: var(--achievement-bg);
            border: 1px solid var(--achievement-border);
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 10px;
        }
        .achievement-title{
            color: #ffd28a;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .text-muted-custom{
            color: var(--text-muted) !important;
        }

        /* ======= RESPONSIVE (móvil) ======= */
        @media (max-width: 576px){
            .navbar-brand{ font-size: 18px; letter-spacing: 2px; }
            .avatar-img{ width: 110px; height: 110px; border-width: 3px; }
            .username{ font-size: 24px; }
            .card-custom{ padding: 14px; border-radius: 16px; }
            .section-title{ font-size: 16px; }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg p-3">
    <div class="container-fluid">

        <a href="index.php" class="navbar-brand">LOGICORE</a>

        <!-- Botón hamburger (solo se muestra en móvil) -->
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarProfile"
                aria-controls="navbarProfile" aria-expanded="false" aria-label="Toggle navigation"
                style="filter: invert(1);">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarProfile">
            <div class="ms-auto nav-right d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 gap-lg-3 mt-3 mt-lg-0">

                <!-- 🌗 Botón tema -->
                <button id="themeToggle" class="btn btn-sm btn-secondary d-flex align-items-center justify-content-center">
                    <i id="themeIcon" class="fas fa-moon me-2"></i>
                    <span id="themeText">Oscuro</span>
                </button>

                <!-- Usuario (se corta bonito en móvil) -->
                <div class="navbar-text-custom nav-user text-truncate">
                    Conectado como <strong><?= htmlspecialchars($user['usuario'] ?? 'Usuario') ?></strong>
                </div>

                <!-- Acciones rápidas -->
                <div class="d-flex gap-2">
                    <a href="editar_perfil.php" class="btn btn-sm btn-outline-warning">Editar</a>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger">Salir</a>
                </div>

            </div>
        </div>

    </div>
</nav>

<div class="container">

    <!-- AVATAR + NOMBRE -->
    <div class="avatar-wrapper">
        <img class="avatar-img" src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar">
        <h1 class="username mt-3 mb-1"><?= htmlspecialchars($user['nombre_perfil'] ?? 'Jugador') ?></h1>

        <p class="text-muted-custom mb-1">
            Miembro desde: <?= !empty($user['fecha_registro']) ? date('d/m/Y', strtotime($user['fecha_registro'])) : 'N/A' ?>
        </p>

        <p class="text-muted-custom mb-0">
            Sudokus completados: <?= (int)$sudoku_completed ?> · Torres completadas: <?= (int)$hanoi_completed ?>
        </p>
    </div>

    <!-- INFORMACIÓN -->
    <div class="row mt-4 g-3 g-md-4">
        <div class="col-12 col-md-6">
            <div class="card-custom">
                <div class="section-title">Nombre completo</div>
                <p class="mb-0"><?= htmlspecialchars($nombreCompleto) ?></p>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card-custom">
                <div class="section-title">Usuario</div>
                <p class="mb-0"><?= htmlspecialchars($user['usuario'] ?? 'N/A') ?></p>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card-custom">
                <div class="section-title">Email</div>
                <p class="mb-0"><?= htmlspecialchars($user['correo_electronico'] ?? 'N/A') ?></p>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card-custom">
                <div class="section-title">ID Usuario</div>
                <p class="mb-0"><?= htmlspecialchars((string)($user['id'] ?? 'N/A')) ?></p>
            </div>
        </div>
    </div>

    <!-- NIVEL + LOGROS -->
    <div class="row mt-3 g-3 g-md-4">
        <div class="col-12 col-lg-6">
            <div class="card-custom">
                <div class="section-title">Nivel Logicore</div>

                <div class="d-flex align-items-baseline justify-content-between flex-wrap gap-2">
                    <h3 class="mb-0">Nivel <?= (int)$level ?></h3>
                    <div class="text-muted-custom small">Total XP: <?= (int)$totalXp ?></div>
                </div>

                <div class="mt-3">
                    <p class="mb-1">XP del nivel: <?= (int)$xpInLevel ?> / <?= (int)$XP_LEVEL ?></p>
                    <div class="progress mb-2">
                        <div class="progress-bar" style="width: <?= (float)$xpPercent ?>%"></div>
                    </div>
                    <div class="text-muted-custom small">
                        Te faltan <?= (int)($XP_LEVEL - $xpInLevel) ?> XP para el siguiente nivel.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card-custom">
                <div class="section-title">Logros desbloqueados (<?= (int)$achievementsCount ?>)</div>

                <?php if ($achievementsCount === 0): ?>
                    <p class="text-muted-custom mb-0">
                        Aún no has desbloqueado logros… empieza a jugar para conseguir tus primeras medallas.
                    </p>
                <?php else: ?>
                    <div class="mt-2">
                        <?php foreach ($achievements as $a): ?>
                            <div class="achievement-box">
                                <div class="achievement-title"><?= htmlspecialchars($a['title'] ?? 'Logro') ?></div>
                                <div><?= htmlspecialchars($a['description'] ?? '') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- BOTONES (responsivos) -->
    <div class="text-center mt-4">
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="index.php" class="btn-orange">Volver al menú principal</a>
            <a href="editar_perfil.php" class="btn-orange">Editar perfil</a>
            <a href="logout.php" class="btn-red">Cerrar sesión</a>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function applySavedThemeProfile() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        const icon = document.getElementById('themeIcon');
        const text = document.getElementById('themeText');

        if (savedTheme === 'light') {
            document.body.classList.add('light-theme');
            if (icon) { icon.classList.remove('fa-moon'); icon.classList.add('fa-sun'); }
            if (text) text.textContent = 'Claro';
        } else {
            document.body.classList.remove('light-theme');
            if (icon) { icon.classList.remove('fa-sun'); icon.classList.add('fa-moon'); }
            if (text) text.textContent = 'Oscuro';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        applySavedThemeProfile();

        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const isLight = document.body.classList.toggle('light-theme');
                localStorage.setItem('theme', isLight ? 'light' : 'dark');
                applySavedThemeProfile();
            });
        }
    });
</script>

</body>
</html>
