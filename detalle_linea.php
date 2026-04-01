<?php
require_once 'conexion.php';

$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: index.html#investigacion');
    exit;
}

// Obtener detalles de la línea de investigación
$stmt = $pdo->prepare("SELECT * FROM lineas_investigacion WHERE id = ?");
$stmt->execute([$id]);
$linea = $stmt->fetch();

if (!$linea) {
    echo "Línea de investigación no encontrada.";
    exit;
}

$titulo_linea = $linea['titulo'];
$color = $linea['color'] ?: 'blue';
$icono = $linea['icono'] ?: 'book';

// Obtener investigadores de esta línea
$stmt_inv = $pdo->prepare("SELECT * FROM investigadores WHERE etiqueta_badge = ? ORDER BY nombre ASC");
$stmt_inv->execute([$titulo_linea]);
$investigadores = $stmt_inv->fetchAll();

// Obtener proyectos de esta línea
$stmt_pro = $pdo->prepare("SELECT * FROM proyectos WHERE categoria = ? ORDER BY anio_inicio DESC");
$stmt_pro->execute([$titulo_linea]);
$proyectos = $stmt_pro->fetchAll();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <title><?= $linea['titulo'] ?> | CIEEPE</title>
    <style>
        .bg-abstract {
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 100 C 20 0 50 0 100 100 Z' fill='white' fill-opacity='0.1'/%3E%3C/svg%3E");
            background-size: cover;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 antialiased flex flex-col min-h-screen">

    <!-- Navegación -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 py-2 bg-white text-blue-900 shadow-md">
        <div class="container mx-auto px-4 md:px-8 flex justify-between items-center">
            <a href="index.html#inicio" id="nav-logo" class="flex items-center">
                <img src="<?= htmlspecialchars($site_logo) ?>" alt="CIEEPE ENEES Logo" class="h-12 w-auto md:h-14">
            </a>
            <div class="hidden md:flex space-x-6 lg:space-x-8">
                <a href="index.html#inicio" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Inicio</a>
                <a href="index.html#investigacion" class="nav-link text-sm font-medium text-blue-600">Líneas</a>
                <a href="proyectos.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Proyectos</a>
                <a href="noticias.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Noticias</a>
                <a href="investigadores.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Equipo</a>
                <a href="index.html#contacto" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Contacto</a>
            </div>
            <!-- Botón Menú Móvil -->
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none text-gray-700">
                <i data-lucide="menu" class="w-7 h-7"></i>
            </button>
        </div>
    </nav>

    <!-- Menú Móvil Premium (Sidebar) -->
    <div id="mobile-sidebar" class="fixed inset-0 z-[100] pointer-events-none transition-all duration-300">
        <!-- Backdrop con desenfoque -->
        <div id="mobile-sidebar-backdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm opacity-0 transition-opacity duration-300 pointer-events-none"></div>
        
        <!-- Panel Lateral -->
        <div id="mobile-sidebar-panel" class="absolute inset-y-0 right-0 w-80 bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 pointer-events-auto">
            <!-- Header del Sidebar -->
            <div class="px-6 py-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img id="mobile-sidebar-logo" src="<?= htmlspecialchars($site_logo) ?>" alt="Logo CIEEPE" class="h-10 w-auto">
                    <span class="font-bold text-blue-950 text-lg tracking-tight">Portal CIEEPE</span>
                </div>
                <button id="mobile-sidebar-close" class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-500 hover:text-red-500">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Cuerpo del Sidebar (Enlaces) -->
            <div class="flex-grow overflow-y-auto py-6 px-4">
                <nav class="space-y-2">
                    <a href="index.html#inicio" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="home" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Inicio</span>
                    </a>
                    <a href="index.html#nosotros" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="info" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Nosotros</span>
                    </a>
                    <a href="index.html#investigacion" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-blue-50 text-blue-900 transition-all group">
                        <i data-lucide="microscope" class="w-5 h-5 text-blue-600"></i>
                        <span class="font-semibold">Líneas de Investigación</span>
                    </a>
                    <a href="proyectos.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="lightbulb" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Proyectos</span>
                    </a>
                    <a href="noticias.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="newspaper" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Noticias</span>
                    </a>
                    <a href="investigadores.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="users" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Equipo</span>
                    </a>
                    <a href="index.html#contacto" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="mail" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Contacto</span>
                    </a>
                </nav>

                <!-- CTA Biblioteca -->
                <div class="mt-8 p-6 bg-gradient-to-br from-blue-900 to-indigo-900 rounded-3xl text-white relative overflow-hidden shadow-xl shadow-blue-900/20">
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-blue-600/20 blur-2xl"></div>
                    <p class="text-[10px] font-bold text-blue-300 uppercase tracking-widest mb-2">Biblioteca Virtual</p>
                    <h4 class="text-lg font-bold mb-3 leading-tight tracking-tight">Accede a Tesis e IA (CIATA)</h4>
                    <a href="biblioteca.php" class="inline-flex items-center gap-2 py-2 px-4 bg-white text-blue-900 rounded-lg text-xs font-bold hover:bg-blue-50 transition-colors">
                        <i data-lucide="sparkles" class="w-4 h-4"></i> Entrar ahora
                    </a>
                </div>
            </div>

            <!-- Footer del Sidebar -->
            <div class="p-6 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                <div class="flex gap-4">
                    <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors"><i data-lucide="facebook" class="w-5 h-5"></i></a>
                    <a href="#" class="text-gray-400 hover:text-red-500 transition-colors"><i data-lucide="instagram" class="w-5 h-5"></i></a>
                    <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                </div>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">© CIEEPE 2026</span>
            </div>
        </div>
    </div>

    <!-- Header Interno -->
    <header class="bg-<?= $color ?>-900 text-white pt-32 pb-16 bg-abstract relative overflow-hidden">
        <div class="container mx-auto px-4 md:px-8 relative z-10">
            <a href="index.html#investigacion" class="inline-flex items-center text-<?= $color ?>-200 hover:text-white mb-8 transition-colors font-medium group">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform"></i> Volver a Líneas
            </a>
            
            <div class="max-w-4xl">
                <div class="inline-block p-3 rounded-full bg-<?= $color ?>-800/50 backdrop-blur-sm mb-6 border border-<?= $color ?>-400/30">
                    <i data-lucide="<?= htmlspecialchars($icono) ?>" class="w-8 h-8 text-<?= $color ?>-300"></i>
                </div>
                <h1 class="text-3xl md:text-5xl font-bold mb-4"><?= htmlspecialchars($titulo_linea) ?></h1>
                <p class="text-xl text-<?= $color ?>-100 leading-relaxed max-w-3xl">
                    <?= nl2br(htmlspecialchars($linea['descripcion'])) ?>
                </p>
            </div>
        </div>
    </header>

    <!-- Sección de Investigadores -->
    <section class="py-16 bg-white flex-grow">
        <div class="container mx-auto px-4 md:px-8">
            <div class="flex items-center mb-10">
                <div class="h-8 w-1 bg-<?= $color ?>-600 rounded-full mr-4"></div>
                <h2 class="text-2xl font-bold text-gray-900">Investigadores Colaboradores</h2>
            </div>

            <?php if(empty($investigadores)): ?>
                <div class="text-center py-12 bg-gray-50 rounded-xl border border-gray-100">
                    <i data-lucide="users" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-gray-500 font-medium">No hay investigadores asociados a esta línea actualmente.</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <?php foreach($investigadores as $inv): ?>
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group border border-gray-100 p-6 flex flex-col items-center text-center">
                        <div class="w-24 h-24 rounded-full mb-4 overflow-hidden border-4 border-gray-50 shadow-sm group-hover:scale-105 transition-transform duration-500">
                            <img src="<?= htmlspecialchars($inv['imagen_perfil']) ?>" alt="<?= htmlspecialchars($inv['nombre']) ?>" class="w-full h-full object-cover object-center">
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 leading-tight w-full truncate"><?= htmlspecialchars($inv['nombre']) ?></h3>
                        <?php if(stripos($inv['tipo_investigador'], 'Responsable') !== false): ?>
                            <span class="bg-<?= $color ?>-100 text-<?= $color ?>-700 text-xs px-2 py-1 rounded-full font-semibold mb-3 inline-block">Responsable</span>
                        <?php elseif(!empty($inv['tipo_investigador'])): ?>
                            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full font-semibold mb-3 inline-block"><?= htmlspecialchars($inv['tipo_investigador']) ?></span>
                        <?php endif; ?>
                        
                        <p class="text-<?= $color ?>-600 font-medium text-xs mb-3 truncate w-full"><?= htmlspecialchars($inv['cargo_o_grado']) ?></p>
                        <a href="perfil_generico.php?id=<?= $inv['id'] ?>" class="text-<?= $color ?>-600 text-sm font-semibold hover:underline mt-auto">Ver Perfil</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Sección de Proyectos -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4 md:px-8">
            <div class="flex items-center mb-10">
                <div class="h-8 w-1 bg-amber-500 rounded-full mr-4"></div>
                <h2 class="text-2xl font-bold text-gray-900">Proyectos Asociados</h2>
            </div>

            <?php if(empty($proyectos)): ?>
                <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                    <i data-lucide="folder-open" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-gray-500 font-medium">No hay proyectos registrados bajo esta línea actualmente.</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach($proyectos as $pro): ?>
                    <article class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 flex flex-col group border border-gray-100">
                        <div class="relative h-56 overflow-hidden">
                            <img src="<?= htmlspecialchars($pro['imagen_portada'] ?? './img/placeholder.jpg') ?>" alt="<?= htmlspecialchars($pro['titulo']) ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-4 left-4 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm uppercase tracking-wide">
                                <?= htmlspecialchars($pro['estado']) ?>
                            </div>
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center text-xs text-gray-500 mb-3 space-x-2">
                                <i data-lucide="tag" class="w-3 h-3 text-<?= $color ?>-500"></i>
                                <span class="font-medium text-<?= $color ?>-600"><?= htmlspecialchars($titulo_linea) ?></span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3 leading-tight group-hover:text-<?= $color ?>-600 transition-colors">
                                <?= htmlspecialchars($pro['titulo']) ?>
                            </h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4 flex-1">
                                <?= htmlspecialchars($pro['descripcion_corta']) ?>
                            </p>
                            <div class="border-t border-gray-100 pt-4 flex justify-between items-center mt-auto">
                                <span class="text-xs text-gray-500 font-bold"><?= htmlspecialchars($pro['anio_inicio']) ?></span>
                                <a href="ficha_tecnica.php?id=<?= $pro['id'] ?>" class="text-<?= $color ?>-600 font-bold text-sm hover:underline flex items-center">
                                    Ver Ficha Técnica <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 pt-20 pb-10 text-white border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-16">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <img src="<?= htmlspecialchars($site_logo) ?>" alt="CIEEPE" class="h-12 w-auto brightness-0 invert">
                        <div>
                            <h3 class="text-xl font-bold tracking-tight">CIEEPE</h3>
                            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Excelencia Educativa</p>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                        Impulsando la investigación y formación docente en educación especial para transformar el futuro de Sinaloa.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="index.html" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-indigo-600 transition-all hover:scale-110 shadow-lg" title="Inicio"><i data-lucide="globe" class="w-5 h-5"></i></a>
                        <a href="index.html#contacto" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-emerald-600 transition-all hover:scale-110 shadow-lg" title="Contacto"><i data-lucide="mail" class="w-5 h-5"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-widest text-white mb-8">Navegación</h4>
                    <ul class="space-y-4">
                        <li><a href="index.html" class="text-slate-400 hover:text-white transition-colors text-sm flex items-center gap-2 group"><span class="w-1 h-1 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></span> Inicio</a></li>
                        <li><a href="proyectos.php" class="text-slate-400 hover:text-white transition-colors text-sm flex items-center gap-2 group"><span class="w-1 h-1 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></span> Proyectos</a></li>
                        <li><a href="noticias.php" class="text-slate-400 hover:text-white transition-colors text-sm flex items-center gap-2 group"><span class="w-1 h-1 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></span> Noticias</a></li>
                        <li><a href="investigadores.php" class="text-slate-400 hover:text-white transition-colors text-sm flex items-center gap-2 group"><span class="w-1 h-1 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></span> Investigadores</a></li>
                        <li><a href="biblioteca.php" class="text-blue-400 hover:text-white transition-colors text-sm font-bold flex items-center gap-2 group"><span class="w-1 h-1 bg-blue-400 rounded-full opacity-100"></span> Biblioteca Virtual</a></li>
                    </ul>
                </div>
                <div class="bg-white/5 rounded-3xl p-8 border border-white/5">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-white mb-6">Ubicación</h4>
                    <address class="not-italic space-y-4">
                        <div class="flex gap-4">
                            <i data-lucide="map-pin" class="w-5 h-5 text-blue-500 shrink-0"></i>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                Carretera Imala Km 2 Col. Los Ángeles, <br>
                                C.P. 80014 Culiacán Rosales, Sinaloa, México.
                            </p>
                        </div>
                    </address>
                </div>
            </div>
            <div class="border-t border-white/5 pt-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-slate-500 text-xs font-medium">
                    &copy; <span id="year"></span> CIEEPE - Escuela Normal de Especialización del Estado de Sinaloa.
                </p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
        document.getElementById('year').textContent = new Date().getFullYear();

        // Mobile Menu Premium Logic
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const mobileSidebarBackdrop = document.getElementById('mobile-sidebar-backdrop');
        const mobileSidebarPanel = document.getElementById('mobile-sidebar-panel');
        const mobileSidebarClose = document.getElementById('mobile-sidebar-close');
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');

        function openMobileMenu() {
            mobileSidebar.classList.remove('pointer-events-none');
            mobileSidebarBackdrop.classList.remove('pointer-events-none', 'opacity-0');
            mobileSidebarBackdrop.classList.add('opacity-100');
            mobileSidebarPanel.classList.remove('translate-x-full');
            mobileSidebarPanel.classList.add('translate-x-0');
            document.body.style.overflow = 'hidden'; // Prevent scroll
        }

        function closeMobileMenu() {
            mobileSidebarBackdrop.classList.remove('opacity-100');
            mobileSidebarBackdrop.classList.add('opacity-0');
            mobileSidebarPanel.classList.remove('translate-x-0');
            mobileSidebarPanel.classList.add('translate-x-full');
            document.body.style.overflow = ''; // Restore scroll
            
            setTimeout(() => {
                mobileSidebar.classList.add('pointer-events-none');
                mobileSidebarBackdrop.classList.add('pointer-events-none');
            }, 300);
        }

        mobileMenuBtn.addEventListener('click', openMobileMenu);
        mobileSidebarClose.addEventListener('click', closeMobileMenu);
        mobileSidebarBackdrop.addEventListener('click', closeMobileMenu);

        // Cerrar menú al hacer click en un enlace
        mobileSidebarPanel.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                setTimeout(closeMobileMenu, 150);
            });
        });
    </script>
</body>
</html>
