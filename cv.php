<?php
// === CERTIFICADOS (NO TOCAR: esto es lo que hace que abra bien con espacios, comas, & etc.) ===
$certDir = __DIR__ . "/certificadosj";
$webDir  = "certificadosj/";

$certs = [];
if (is_dir($certDir)) {
    $files = glob($certDir . "/*.pdf");
    natsort($files);
    $files = array_values($files);

    foreach ($files as $file) {
        $base  = basename($file); // nombre real del pdf
        $title = pathinfo($base, PATHINFO_FILENAME);

        $certs[] = [
            "title" => $title,
            "href"  => $webDir . rawurlencode($base) // <- clave para espacios/comas/&
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jaret Gonzalez | Ingeniero en Sistemas</title>

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Librerías -->
    <script src="https://unpkg.com/scrollreveal" defer></script>

    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }

        body {
            background:#0a0f1f;
            color:#fff;
            overflow-x:hidden;
            line-height:1.6;
        }

        /* Partículas */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -1;
            top: 0;
            left: 0;
            background: #0a0f1f;
        }

        /* Navegación flotante */
        .navbar{
            position:fixed;
            top:25px;
            left:50%;
            transform:translateX(-50%);
            padding:12px 35px;
            border-radius:50px;
            background:rgba(20, 30, 50, 0.40);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border:1px solid rgba(255,255,255,0.08);
            display:flex;
            gap:28px;
            z-index:1000;
            box-shadow:0 10px 30px rgba(0,0,0,0.2);
            max-width:min(1050px, calc(100% - 24px));
            overflow-x:auto;
            scrollbar-width:none;
        }
        .navbar::-webkit-scrollbar{display:none;}

        .navbar a{
            text-decoration:none;
            color:rgba(255,255,255,0.85);
            font-size:0.95rem;
            font-weight:500;
            letter-spacing:0.3px;
            transition: all 0.3s ease;
            padding: 5px 0;
            border-bottom: 2px solid transparent;
            white-space:nowrap;
        }
        .navbar a:hover{
            color:white;
            border-bottom-color:#3b82f6;
        }

        /* Cursor personalizado */
        .custom-cursor{
            width:24px;
            height:24px;
            border:2px solid rgba(59,130,246,0.6);
            border-radius:50%;
            position:fixed;
            pointer-events:none;
            transform:translate(-50%, -50%);
            transition: all 0.08s ease;
            z-index:9999;
            mix-blend-mode: screen;
        }

        /* Evita que el navbar tape */
        #inicio, #perfil, #formacion, #experiencia, #proyectos, #habilidades, #certificados{
            scroll-margin-top: 120px;
        }

        /* Hero */
        .hero{
            min-height:100vh;
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            text-align:center;
            padding: 0 20px;
            position:relative;
        }

        .profile-wrap{
            width:160px;
            height:160px;
            border-radius:50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            margin-bottom:25px;
            border:3px solid rgba(255,255,255,0.2);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            overflow:hidden;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .profile-wrap img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        .profile-initials{
            font-size:3rem;
            font-weight:700;
            color:#fff;
        }

        .hero h1{
            font-size:4rem;
            font-weight:700;
            letter-spacing:-1px;
            margin-bottom:15px;
            background: linear-gradient(to right, #ffffff, #b0c4ff);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            min-height:1.1em;
        }

        .typing-caret{
            display:inline-block;
            width:10px;
            margin-left:6px;
            -webkit-text-fill-color:rgba(255,255,255,0.9);
            animation: blink 0.9s infinite;
        }
        @keyframes blink{0%,50%{opacity:1}51%,100%{opacity:0}}

        .hero .subtitle{
            font-size:1.2rem;
            color:rgba(255,255,255,0.7);
            max-width:650px;
            margin-bottom:30px;
            font-weight:300;
        }

        .contact-badge{
            display:flex;
            gap:14px;
            flex-wrap:wrap;
            justify-content:center;
            margin-top:10px;
            max-width:900px;
        }

        .contact-badge span{
            background: rgba(255,255,255,0.05);
            padding: 10px 16px;
            border-radius: 40px;
            font-size: 0.95rem;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
        }
        .contact-badge a{color:inherit; text-decoration:none;}
        .contact-badge a:hover{text-decoration:underline;}

        /* Contenedor */
        .container{
            max-width:1100px;
            margin:0 auto;
            padding:60px 30px;
        }

        /* Tarjetas glass */
        .glass-card{
            background: rgba(20, 30, 45, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 35px;
            margin-bottom: 40px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .glass-card:hover{
            transform: translateY(-8px) scale(1.01);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.4);
        }

        .section-title{
            font-size:2rem;
            font-weight:600;
            margin-bottom:30px;
            position:relative;
            display:inline-block;
        }

        .section-title::after{
            content:'';
            position:absolute;
            bottom:-8px;
            left:0;
            width:60px;
            height:3px;
            background:#3b82f6;
            border-radius:3px;
        }

        /* Grid */
        .grid-2{
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap:30px;
            margin-top: 22px;
        }

        .exp-item{
            background: rgba(255,255,255,0.03);
            border-radius: 18px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s;
        }

        .exp-item:hover{
            background: rgba(59,130,246,0.1);
            border-color: rgba(59,130,246,0.3);
        }

        .exp-company{
            color:#3b82f6;
            font-weight:600;
            font-size:1.1rem;
            margin-bottom:5px;
        }

        .exp-role{
            font-weight:600;
            font-size:1.2rem;
            margin-bottom:10px;
        }

        .exp-desc{
            color: rgba(255,255,255,0.7);
            font-size:0.95rem;
            list-style:none;
        }

        .exp-desc li{
            margin-bottom:8px;
            padding-left:20px;
            position:relative;
        }

        .exp-desc li::before{
            content:"▹";
            color:#3b82f6;
            position:absolute;
            left:0;
        }

        /* Skills */
        .skills-container{
            display:flex;
            flex-wrap:wrap;
            gap:15px;
            margin-top:20px;
        }

        .skill-tag{
            background: rgba(59,130,246,0.15);
            border: 1px solid rgba(59,130,246,0.3);
            padding: 10px 22px;
            border-radius: 40px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .skill-tag:hover{
            background: rgba(59,130,246,0.3);
            transform: scale(1.05);
        }

        /* Certificados */
        .cert-controls{
            display:flex;
            gap:12px;
            align-items:center;
            justify-content:space-between;
            flex-wrap:wrap;
            margin-top:10px;
            margin-bottom:10px;
        }

        .cert-search{
            flex:1;
            min-width:220px;
            padding:12px 14px;
            border-radius:16px;
            border:1px solid rgba(255,255,255,0.10);
            background:rgba(255,255,255,0.04);
            color:#fff;
            outline:none;
        }
        .cert-search::placeholder{ color: rgba(255,255,255,0.55); }

        .btn{
            background: rgba(59,130,246,0.18);
            border: 1px solid rgba(59,130,246,0.35);
            color:white;
            padding:10px 16px;
            border-radius:999px;
            cursor:pointer;
            font-weight:600;
            transition:.2s;
        }
        .btn:hover{
            background: rgba(59,130,246,0.35);
            transform: translateY(-1px);
        }

        .cert-card h3{
            font-size:1.05rem;
            margin-bottom:12px;
        }

        .cert-actions{
            display:flex;
            gap:10px;
            align-items:center;
            justify-content:flex-start;
        }

        .extra-cert{ display:none; }
        .show-all .extra-cert{ display:block; }

        /* Footer */
        .footer{
            text-align:center;
            padding:40px 0;
            color: rgba(255,255,255,0.5);
            font-size:0.9rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            margin-top: 60px;
        }

        /* Responsive */
        @media (max-width: 768px){
            .navbar{
                padding:10px 16px;
                gap:16px;
                width: calc(100% - 24px);
                top:14px;
            }

            .hero{
                padding-top: 110px; /* para que el navbar no tape */
                min-height: 100svh;
            }

            .hero h1{ font-size: 2.45rem; }
            .hero .subtitle{ font-size: 1.05rem; }

            .container{ padding: 30px 18px; }
            .glass-card{ padding: 22px; }

            .grid-2{ grid-template-columns: 1fr; gap:16px; }

            .contact-badge span{
                width:100%;
                text-align:center;
                border-radius:16px;
            }

            .custom-cursor{ display:none; }

            .cert-actions .btn{ width:auto; }
        }

        @media (prefers-reduced-motion: reduce){
            .glass-card, .exp-item, .skill-tag, .btn { transition:none !important; }
            .glass-card:hover{ transform:none !important; }
        }
    </style>
</head>
<body>

    <div id="particles-js"></div>
    <div class="custom-cursor"></div>

    <nav class="navbar">
        <a href="#inicio">Inicio</a>
        <a href="#perfil">Perfil</a>
        <a href="#formacion">Formación</a>
        <a href="#experiencia">Experiencia</a>
        <a href="#proyectos">Proyectos</a>
        <a href="#habilidades">Habilidades</a>
        <a href="#certificados">Certificados</a>
    </nav>

    <section id="inicio" class="hero">
        <div class="profile-wrap">
            <!-- Cambia foto.jpg por el nombre real de tu foto (en tu captura se ve "foto.jpg" en public_html) -->
            <img src="foto.jpg" alt="Foto de Jaret" onerror="this.remove(); this.parentElement.innerHTML='<div class=&quot;profile-initials&quot;>JG</div>';">
        </div>

        <h1 id="typed-name" data-text="Jaret D. Gonzalez Croroy"></h1>

        <div class="subtitle">
            Ingeniero en Sistemas Computacionales (en proceso de titulación)
            · Especializado en desarrollo web y bases de datos
        </div>

        <div class="contact-badge">
            <span>📱 <a href="tel:+527293934426">729 393 4426</a></span>
            <span>✉️ <a href="mailto:Jcroroy@gmail.com">Jcroroy@gmail.com</a></span>
            <span>📍 Estado de México</span>
        </div>
    </section>

    <div class="container">

        <div id="perfil" class="glass-card" data-scroll>
            <h2 class="section-title">Perfil Profesional</h2>
            <p style="font-size: 1.1rem; line-height: 1.7; color: rgba(255,255,255,0.9);">
                Estudiante de Ingeniería en Sistemas Computacionales en proceso de titulación,
                con enfoque en desarrollo web y programación orientada a objetos.
                Interesado en desarrollarme como Desarrollador Web Junior,
                aplicando buenas prácticas de desarrollo y bases de datos.
            </p>
        </div>

        <div id="formacion" class="glass-card" data-scroll>
            <h2 class="section-title">Formación Académica</h2>
            <div style="display:flex; flex-direction:column; gap:5px;">
                <h3 style="font-size: 1.4rem; font-weight: 600;">Ingeniería en Sistemas Computacionales</h3>
                <p style="color: #3b82f6; font-size: 1.1rem;">Universidad Politécnica de Texcoco</p>
                <p style="color: rgba(255,255,255,0.6); font-style: italic;">En proceso de titulación (Tesis)</p>
            </div>
        </div>

        <div id="experiencia" class="glass-card" data-scroll>
            <h2 class="section-title">Experiencia Profesional</h2>

            <div class="grid-2">
                <div class="exp-item">
                    <div class="exp-company">Holcim</div>
                    <div class="exp-role">Estadía Profesional</div>
                    <ul class="exp-desc">
                        <li>Apoyo en actividades del área tecnológica.</li>
                        <li>Participación en procesos internos relacionados con sistemas.</li>
                    </ul>
                </div>

                <div class="exp-item">
                    <div class="exp-company">JTecnologia</div>
                    <div class="exp-role">Estancias Profesionales</div>
                    <ul class="exp-desc">
                        <li>Soporte técnico a equipos de cómputo.</li>
                        <li>Apoyo en implementación y pruebas de sistemas.</li>
                    </ul>
                </div>

                <div class="exp-item">
                    <div class="exp-company">Ayuntamiento de Chicoloapan</div>
                    <div class="exp-role">Servicio Social</div>
                    <ul class="exp-desc">
                        <li>Mantenimiento preventivo y correctivo de equipos.</li>
                        <li>Soporte técnico a usuarios.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="proyectos" class="glass-card" data-scroll>
            <h2 class="section-title">Proyectos Académicos</h2>

            <div class="grid-2">
                <div class="exp-item">
                    <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Página web con BD</h3>
                    <p style="color: rgba(255,255,255,0.7);">Desarrollo de página web con conexión a base de datos.</p>
                </div>

                <div class="exp-item">
                    <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Sistema CRUD</h3>
                    <p style="color: rgba(255,255,255,0.7);">Sistema CRUD con Java y MySQL.</p>
                </div>

                <div class="exp-item">
                    <h3 style="font-size: 1.3rem; margin-bottom: 10px;">Validaciones JS</h3>
                    <p style="color: rgba(255,255,255,0.7);">Validaciones y lógica de negocio con JavaScript.</p>
                </div>
            </div>
        </div>

        <div id="habilidades" class="glass-card" data-scroll>
            <h2 class="section-title">Habilidades Técnicas</h2>

            <div class="skills-container">
                <span class="skill-tag">HTML</span>
                <span class="skill-tag">CSS</span>
                <span class="skill-tag">JavaScript</span>
                <span class="skill-tag">Java</span>
                <span class="skill-tag">Python</span>
                <span class="skill-tag">MySQL</span>
                <span class="skill-tag">SQL Server</span>
                <span class="skill-tag">Programación Orientada a Objetos</span>
                <span class="skill-tag">Fundamentos de Redes (Cisco)</span>
            </div>
        </div>

        <!-- CERTIFICADOS (aquí NO cambiamos lo del PDF, solo diseño y UX) -->
        <div id="certificados" class="glass-card" data-scroll>
            <h2 class="section-title">Certificados</h2>
            <p style="color: rgba(255,255,255,0.7); margin-top:-10px;">
                Se muestran 6 destacados. Usa el buscador o “Ver todos”.
            </p>

            <div class="cert-controls">
                <input id="certSearch" class="cert-search" type="text" placeholder="Buscar certificado..." />
                <button id="toggleCertBtn" class="btn" type="button">Ver todos</button>
            </div>

            <div class="grid-2" id="certGrid">
                <?php
                $top = 6;
                foreach ($certs as $i => $c) {
                    $extraClass = ($i >= $top) ? " extra-cert" : "";
                    ?>
                    <div class="exp-item cert-item<?php echo $extraClass; ?>">
                        <h3 style="font-size: 1.2rem; margin-bottom: 10px;">
                            <?php echo htmlspecialchars($c["title"], ENT_QUOTES, "UTF-8"); ?>
                        </h3>

                        <div class="cert-actions">
                            <a class="btn" href="<?php echo htmlspecialchars($c["href"], ENT_QUOTES, "UTF-8"); ?>" target="_blank" rel="noopener">
                                📄 Ver PDF
                            </a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <p style="margin-top:14px;color:rgba(255,255,255,.55);font-size:.92rem;">
    
            </p>
        </div>

        <div class="footer">
            © 2026 Jaret D. Gonzalez Croroy · Todos los derechos reservados
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        // Partículas
        particlesJS('particles-js', {
            particles: {
                number: { value: 60, density: { enable: true, value_area: 800 } },
                color: { value: '#3b82f6' },
                shape: { type: 'circle' },
                opacity: { value: 0.3, random: false },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#3b82f6',
                    opacity: 0.2,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: false,
                    straight: false,
                    out_mode: 'out',
                    bounce: false
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: { enable: true, mode: 'repulse' },
                    onclick: { enable: true, mode: 'push' },
                    resize: true
                }
            },
            retina_detect: true
        });

        // Cursor personalizado (solo desktop)
        const cursor = document.querySelector('.custom-cursor');
        if(cursor){
            document.addEventListener('mousemove', (e) => {
                cursor.style.left = e.clientX + 'px';
                cursor.style.top = e.clientY + 'px';
            });

            document.addEventListener('mouseleave', () => cursor.style.opacity = '0');
            document.addEventListener('mouseenter', () => cursor.style.opacity = '1');
        }

        // Animaciones ScrollReveal
        window.addEventListener('load', () => {
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if(!prefersReduced && typeof ScrollReveal === 'function'){
                ScrollReveal().reveal('[data-scroll]', {
                    delay: 150,
                    distance: '34px',
                    origin: 'bottom',
                    interval: 90,
                    duration: 750,
                    easing: 'cubic-bezier(0.5, 0, 0, 1)'
                });
            }
        });

        // Smooth scroll navbar
        document.querySelectorAll('.navbar a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                if (targetSection) {
                    targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Nombre escribiéndose
        (function(){
            const el = document.getElementById('typed-name');
            if(!el) return;

            const text = el.getAttribute('data-text') || '';
            el.textContent = '';
            const caret = document.createElement('span');
            caret.className = 'typing-caret';
            caret.textContent = '|';
            el.appendChild(caret);

            let i = 0;
            function type(){
                if(i < text.length){
                    caret.insertAdjacentText('beforebegin', text.charAt(i));
                    i++;
                    setTimeout(type, 70);
                }else{
                    setTimeout(()=>caret.remove(), 1400);
                }
            }
            setTimeout(type, 250);
        })();

        // Certificados: ver todos / ver menos
        (function(){
            const section = document.getElementById('certificados');
            const btn = document.getElementById('toggleCertBtn');
            if(!section || !btn) return;

            let showAll = false;
            btn.addEventListener('click', ()=>{
                showAll = !showAll;
                section.classList.toggle('show-all', showAll);
                btn.textContent = showAll ? 'Ver menos' : 'Ver todos';
            });
        })();

        // Buscador certificados
        (function(){
            const input = document.getElementById('certSearch');
            const grid = document.getElementById('certGrid');
            if(!input || !grid) return;

            input.addEventListener('input', ()=>{
                const q = input.value.trim().toLowerCase();
                const items = grid.querySelectorAll('.cert-item');

                items.forEach(item=>{
                    const t = (item.innerText || '').toLowerCase();
                    if(q === ''){
                        item.style.removeProperty('display');
                        return;
                    }
                    item.style.display = t.includes(q) ? 'block' : 'none';
                });
            });
        })();
    </script>
</body>
</html>