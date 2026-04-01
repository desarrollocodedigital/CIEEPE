<?php
require_once 'conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.html");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$not = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$not) {
    header("Location: index.html");
    exit;
}

$galeria = json_decode($not['galeria'] ?? '[]', true);
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <title><?= htmlspecialchars($not['titulo']) ?> | CIEEPE</title>
</head>

<body class="bg-gray-50 text-gray-900 antialiased">
    <!-- Navegación -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 py-2 bg-white text-blue-900 shadow-md">
        <div class="container mx-auto px-4 md:px-8 flex justify-between items-center">
            <a href="index.html#inicio" id="nav-logo" class="flex items-center">
                <img src="<?= htmlspecialchars($site_logo) ?>" alt="CIEEPE ENEES Logo" class="h-12 w-auto md:h-14">
            </a>
            <div class="hidden md:flex space-x-6 lg:space-x-8">
                <a href="index.html#inicio" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Inicio</a>
                <a href="index.html#nosotros" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Nosotros</a>
                <a href="index.html#investigacion" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Líneas</a>
                <a href="proyectos.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Proyectos</a>
                <a href="noticias.php" class="nav-link text-sm font-medium text-blue-600">Noticias</a>
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
                    <a href="index.html#investigacion" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="microscope" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Líneas de Investigación</span>
                    </a>
                    <a href="proyectos.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="lightbulb" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Proyectos</span>
                    </a>
                    <a href="noticias.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-blue-50 text-blue-900 transition-all group">
                        <i data-lucide="newspaper" class="w-5 h-5 text-blue-600"></i>
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

    <!-- Hero de la Noticia -->
    <header class="relative h-[50vh] min-h-[400px] w-full overflow-hidden flex items-center justify-center">
        <div class="absolute inset-0 z-0">
            <img src="<?= htmlspecialchars($not['imagen_portada'] ?: './img/placeholder.jpg') ?>" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
        </div>

        <div class="container mx-auto px-4 md:px-8 relative z-10 text-white pt-20">
            <a href="noticias.php" class="inline-flex items-center text-blue-100 hover:text-white mb-8 transition-colors font-medium group">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform"></i> Volver a Noticias
            </a>
            <div class="max-w-4xl">
                <div class="flex items-center space-x-3 text-blue-300 mb-4">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span class="text-sm font-medium"><?= date('d M, Y', strtotime($not['fecha_publicacion'])) ?></span>
                    <span class="text-blue-500">•</span>
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span class="text-sm font-medium"><?= date('H:i', strtotime($not['fecha_publicacion'])) ?></span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-4">
                    <?= htmlspecialchars($not['titulo']) ?>
                </h1>
                <p class="text-lg md:text-xl text-gray-300 max-w-3xl leading-relaxed">
                    <?= htmlspecialchars($not['descripcion_corta']) ?>
                </p>
            </div>
        </div>
    </header>

    <main class="py-16">
        <div class="container mx-auto px-4 md:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Contenido -->
                <article class="prose prose-lg max-w-none text-gray-700 mb-16">
                    <div class="bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-gray-100 <?= ($not['es_cursiva'] ?? 0) == 1 ? 'italic' : '' ?> leading-loose text-lg">
                        <?= nl2br(htmlspecialchars($not['descripcion_larga'])) ?>
                    </div>
                </article>

                <!-- Galería Modernizada -->
                <?php if(!empty($galeria)): ?>
                <section class="mt-20">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 border-l-4 border-blue-600 pl-4">Galería de Imágenes</h3>
                    </div>
                    
                    <div class="relative group">
                        <!-- Contenedor Swiper -->
                        <div class="swiper main-gallery rounded-2xl shadow-xl border border-gray-100 overflow-hidden aspect-video bg-gray-100">
                            <div class="swiper-wrapper">
                                <?php foreach($galeria as $index => $img): ?>
                                <div class="swiper-slide cursor-pointer group/slide" onclick="openLightbox(<?= $index ?>)">
                                    <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover group-hover/slide:scale-105 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-black/10 opacity-0 group-hover/slide:opacity-100 transition-opacity flex items-center justify-center">
                                        <div class="bg-white/20 backdrop-blur-sm p-4 rounded-full">
                                            <i data-lucide="maximize-2" class="w-8 h-8 text-white"></i>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if(count($galeria) > 1): ?>
                            <!-- Navegación -->
                            <div class="swiper-button-next !text-white after:!text-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="swiper-button-prev !text-white after:!text-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="swiper-pagination !bottom-4"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Lightbox / Modal de Imagen -->
                <div id="lightbox" onclick="if(event.target === this) closeLightbox()" class="fixed inset-0 z-50 bg-black/95 backdrop-blur-xl hidden flex-col items-center justify-center p-4 animate-in fade-in duration-300 cursor-pointer">
                    <button onclick="closeLightbox()" class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors z-[60]">
                        <i data-lucide="x" class="w-10 h-10"></i>
                    </button>
                    
                    <div class="relative max-w-6xl w-full flex items-center justify-center">
                        <!-- Badge con Título en Lightbox -->
                        <div class="absolute top-4 left-4 z-[60] bg-blue-900/80 backdrop-blur-md text-white px-4 py-2 rounded-lg text-xs md:text-sm font-bold shadow-lg border border-blue-400/30 max-w-[70%] truncate">
                            <?= htmlspecialchars($not['titulo']) ?>
                        </div>

                        <img id="lightbox-img" src="" class="max-h-[85vh] w-auto rounded-lg shadow-2xl object-contain animate-in zoom-in duration-300">
                        
                        <div class="absolute bottom-4 right-4 text-white/50 text-[10px] md:text-xs font-medium bg-black/40 px-3 py-1.5 rounded-full backdrop-blur-sm" id="lightbox-counter">
                            Imagen 1 de 1
                        </div>

                        <?php if(count($galeria) > 1): ?>
                        <button onclick="changeLightboxImg(-1)" class="absolute left-0 -translate-x-12 md:translate-x-0 p-3 text-white/60 hover:text-white transition-colors">
                            <i data-lucide="chevron-left" class="w-12 h-12"></i>
                        </button>
                        <button onclick="changeLightboxImg(1)" class="absolute right-0 translate-x-12 md:translate-x-0 p-3 text-white/60 hover:text-white transition-colors">
                            <i data-lucide="chevron-right" class="w-12 h-12"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                

            </div>
        </div>
    </main>

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

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
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

        // Inicializar Swiper
        const swiper = new Swiper('.main-gallery', {
            loop: <?= count($galeria) > 1 ? 'true' : 'false' ?>,
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            autoplay: { delay: 4000, disableOnInteraction: true },
        });

        // Lógica de Lightbox
        const gallery = <?= json_encode($galeria) ?>;
        let currentIndex = 0;
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const lightboxCounter = document.getElementById('lightbox-counter');

        function openLightbox(index) {
            currentIndex = index;
            updateLightbox();
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function updateLightbox() {
            lightboxImg.src = gallery[currentIndex];
            lightboxCounter.textContent = `Imagen ${currentIndex + 1} de ${gallery.length}`;
        }

        function changeLightboxImg(dir) {
            currentIndex = (currentIndex + dir + gallery.length) % gallery.length;
            updateLightbox();
        }

        // Teclado
        document.addEventListener('keydown', (e) => {
            if (lightbox.classList.contains('hidden')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') changeLightboxImg(1);
            if (e.key === 'ArrowLeft') changeLightboxImg(-1);
        });
    </script>
</body>
</html>
