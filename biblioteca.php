<?php
session_start();
require_once 'conexion.php';
// Datos del usuario logueado en la biblioteca
$isLoggedBib = isset($_SESSION['user_bib_id']);
$userNameBib = $isLoggedBib ? $_SESSION['user_bib_nombre'] : '';
// Obtener documentos publicados
$stmt = $pdo->prepare("SELECT d.*, u.nombre as autor_nombre FROM documentos_biblioteca d JOIN usuarios_biblioteca u ON d.id_autor = u.id WHERE d.estado_publicacion = 'publicado' ORDER BY d.fecha_subida DESC");
$stmt->execute();
$documentosDB = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Obtener favoritos y vistos del usuario si está logueado
$userFavs = [];
$userVistos = [];
if ($isLoggedBib) {
    $stmtFav = $pdo->prepare("SELECT id_documento FROM favoritos_biblioteca WHERE id_usuario = ?");
    $stmtFav->execute([$_SESSION['user_bib_id']]);
    $userFavs = $stmtFav->fetchAll(PDO::FETCH_COLUMN);
    // Obtener historial de vistos (ordenado por fecha_visto DESC)
    $stmtVistos = $pdo->prepare("SELECT id_documento FROM vistos_biblioteca WHERE id_usuario = ? ORDER BY fecha_visto DESC");
    $stmtVistos->execute([$_SESSION['user_bib_id']]);
    $userVistos = $stmtVistos->fetchAll(PDO::FETCH_COLUMN);
}
// Configuración de sitio (CIATA)
$showCiataBtn = true;
try {
    $stConfig = $pdo->query("SELECT valor FROM site_config WHERE clave = 'show_ciata_button'");
    $row_c = $stConfig->fetch();
    if ($row_c && $row_c['valor'] === '0') {
        $showCiataBtn = false;
    }
} catch (Exception $e) {
}
// 5. Sugerencias Dinámicas basadas en Popularidad (Palabras Clave)
$stmtSugerencias = $pdo->query("SELECT d.palabras_clave 
                               FROM vistos_biblioteca v 
                               JOIN documentos_biblioteca d ON v.id_documento = d.id 
                               WHERE d.estado_publicacion = 'publicado'
                               ORDER BY v.fecha_visto DESC LIMIT 50"); // Tomamos las últimas 50 vistas para tendencia
$allKeywordsRaw = $stmtSugerencias->fetchAll(PDO::FETCH_COLUMN);
$keywordCounts = [];
foreach ($allKeywordsRaw as $kwString) {
    if (!$kwString)
        continue;
    $tags = explode(',', $kwString);
    foreach ($tags as $tag) {
        $tag = trim($tag);
        if ($tag && strlen($tag) > 2) {
            $tagLower = mb_strtolower($tag, 'UTF-8');
            $keywordCounts[$tagLower] = ($keywordCounts[$tagLower] ?? 0) + 1;
        }
    }
}
arsort($keywordCounts);
$sugerenciasPopulares = array_slice(array_keys($keywordCounts), 0, 8);
// Fallback si no hay suficientes vistas aún
if (count($sugerenciasPopulares) < 4) {
    $defaultKws = ['Inclusión', 'Neuroeducación', 'Pedagogía', 'Políticas Públicas', 'Formación Docente', 'Tesis', 'Educación Especial'];
    $sugerenciasPopulares = array_unique(array_merge($sugerenciasPopulares, $defaultKws));
    $sugerenciasPopulares = array_slice($sugerenciasPopulares, 0, 8);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- PDF.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    </script>
    <title>Biblioteca Digital | CIEEPE</title>
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }

        .serif {
            font-family: 'Playfair Display', serif;
        }

        .book-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .book-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1);
        }

        .filter-tag.active {
            background-color: #1e3a8a;
            color: white;
            border-color: #1e3a8a;
        }

        .modal-overlay {
            background-color: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(4px);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* ---- CIATA Floating Button & Modal ---- */
        .circle-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: #3b82f6;
            border-left-color: #8b5cf6;
            animation: spin 3s linear infinite;
        }

        .ring-1 {
            width: 100%;
            height: 100%;
            animation-duration: 3s;
            opacity: 0.7;
        }

        .ring-2 {
            width: 75%;
            height: 75%;
            animation-duration: 5s;
            animation-direction: reverse;
            border-top-color: #06b6d4;
            border-left-color: transparent;
            border-right-color: #ec4899;
            opacity: 0.6;
        }

        .ring-3 {
            width: 50%;
            height: 50%;
            animation-duration: 2s;
            border-left-color: #10b981;
            border-top-color: transparent;
            opacity: 0.8;
        }

        .core-pulse {
            width: 35%;
            height: 35%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.8) 0%, rgba(139, 92, 246, 0.4) 70%, transparent 100%);
            border-radius: 50%;
            animation: corePulse 2s ease-in-out infinite;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes corePulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.8;
            }

            50% {
                transform: scale(1.15);
                opacity: 1;
                box-shadow: 0 0 25px rgba(139, 92, 246, 0.6);
            }
        }

        .fab-pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid rgba(59, 130, 246, 0.5);
            animation: fabPulse 2s infinite;
        }

        @keyframes fabPulse {
            0% {
                width: 100%;
                height: 100%;
                opacity: 1;
            }

            100% {
                width: 170%;
                height: 170%;
                opacity: 0;
            }
        }

        /* CIATA Overlay Modal */
        #ciata-overlay {
            transition: opacity 0.4s ease;
        }

        #ciata-overlay.closed {
            opacity: 0;
            pointer-events: none;
        }

        #ciata-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        #ciata-modal-inner {
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
        }

        #ciata-overlay.closed #ciata-modal-inner {
            transform: scale(0.95) translateY(30px);
            opacity: 0;
        }

        #ciata-overlay.open #ciata-modal-inner {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        /* Thinking Circle (Landing inside modal) */
        .thinking-circle-container {
            position: relative;
            width: 130px;
            height: 130px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Typing dots */
        .typing-dot {
            width: 6px;
            height: 6px;
            background: #94a3b8;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: scale(0);
            }

            40% {
                transform: scale(1);
            }
        }

        /* Hidden sources */
        .sources-container {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">
    <!-- Header / Nav -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="index.html">
                    <img src="<?= $site_logo ?>" alt="CIEEPE" class="h-12 w-auto">
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 leading-tight">CIEEPE</h1>
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 font-semibold">Biblioteca Virtual</p>
                </div>
            </div>
            <!-- Navegación -->
            <nav id="navbar" class="flex items-center">
                <div class="hidden md:flex items-center space-x-6 lg:space-x-8 mr-6">
                    <a href="index.html#inicio"
                        class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Inicio</a>
                    <a href="proyectos.php"
                        class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Proyectos</a>
                    <a href="noticias.php"
                        class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Noticias</a>
                    <a href="investigadores.php"
                        class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Equipo</a>
                    <a href="biblioteca.php" class="nav-link text-sm font-medium text-blue-600">CIATA</a>
                    <?php if (isset($_SESSION['user_bib_rol']) && $_SESSION['user_bib_rol'] === 'admin'): ?>
                        <a href="admin_biblioteca.php"
                            class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Administración</a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_bib_rol']) && in_array($_SESSION['user_bib_rol'], ['admin', 'investigador'])): ?>
                        <a href="subir_articulo.php"
                            class="nav-link text-sm font-medium text-emerald-600 hover:text-emerald-700 font-bold border-b-2 border-emerald-500">Añadir
                            Artículo</a>
                    <?php endif; ?>

                    <?php if ($isLoggedBib): ?>
                        <div class="flex items-center gap-4 ml-4 pl-4 border-l border-slate-200">
                            <span class="text-sm font-semibold text-slate-700">Hola, <?= htmlspecialchars($userNameBib) ?></span>
                            <a href="logout_biblioteca.php?redirect=biblioteca.php"
                                class="text-xs font-bold text-red-600 hover:text-red-700 uppercase tracking-wider">Salir</a>
                            <span class="h-4 w-[1px] bg-slate-200"></span>
                            <a href="admin_manual_usuario.php"
                                class="text-xs font-bold text-blue-600 hover:text-blue-700 uppercase tracking-wider">Ayuda</a>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-4 ml-4 pl-4 border-l border-slate-200">
                            <a href="login_biblioteca.php?redirect=biblioteca.php"
                                class="text-sm font-bold text-blue-900 border border-blue-900 px-4 py-1.5 rounded-lg hover:bg-blue-900 hover:text-white transition-all">Ingresar</a>
                            <a href="registro_biblioteca.php"
                                class="text-sm font-bold text-white bg-blue-900 px-4 py-1.5 rounded-lg hover:bg-blue-800 transition-all shadow-md">Registrarse</a>
                        </div>
                    <?php endif; ?>
                </div>
                    <button id="mobile-menu-btn" class="md:hidden focus:outline-none text-gray-700">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <!-- Menú Móvil -->
                    <div id="mobile-menu"
                        class="hidden md:hidden bg-white shadow-lg absolute top-full left-0 w-full py-4 px-6 flex flex-col space-y-4 text-gray-800 border-t border-gray-100">
                        <a href="index.html#inicio" class="font-medium">Inicio</a>
                        <a href="proyectos.php" class="font-medium">Proyectos</a>
                        <a href="noticias.php" class="font-medium">Noticias</a>
                        <a href="investigadores.php" class="font-medium">Equipo</a>
                        <a href="biblioteca.php" class="font-medium text-blue-600">CIATA</a>
                        <?php if (isset($_SESSION['user_bib_rol']) && $_SESSION['user_bib_rol'] === 'admin'): ?>
                            <a href="admin_biblioteca.php" class="font-medium text-gray-700">Administración</a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_bib_rol']) && in_array($_SESSION['user_bib_rol'], ['admin', 'investigador'])): ?>
                            <a href="subir_articulo.php" class="font-bold text-emerald-600">Añadir Artículo</a>
                        <?php endif; ?>

                        <?php if ($isLoggedBib): ?>
                            <div class="pt-4 border-t border-slate-100 flex flex-col space-y-4">
                                <p class="text-sm font-bold text-slate-900">Hola, <?= htmlspecialchars($userNameBib) ?></p>
                                <a href="logout_biblioteca.php?redirect=biblioteca.php" class="text-red-600 font-bold flex items-center gap-2">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Cerrar Sesión
                                </a>
                                <a href="admin_manual_usuario.php" class="text-blue-600 font-bold flex items-center gap-2">
                                    <i data-lucide="help-circle" class="w-4 h-4"></i> Ayuda
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="pt-4 border-t border-slate-100 flex flex-col space-y-3">
                                <a href="login_biblioteca.php?redirect=biblioteca.php" class="font-bold text-blue-900">Iniciar Sesión</a>
                                <a href="registro_biblioteca.php" class="font-bold text-blue-900">Registrarse</a>
                            </div>
                        <?php endif; ?>
                    </div>
            </nav>
        </div>
    </header>
    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="bg-white border-b border-slate-200 py-16">
            <div class="max-w-4xl mx-auto px-4 text-center">
                <h2 class="serif text-4xl md:text-5xl text-slate-900 mb-6 italic">Explora el Conocimiento Académico</h2>
                <p class="text-slate-600 mb-10 max-w-2xl mx-auto">Accede a nuestra colección especializada en educación
                    especial, investigación pedagógica y tesis institucionales del CIEEPE.</p>
                <!-- Search Bar -->
                <div class="relative max-w-2xl mx-auto">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="text-slate-400 w-5 h-5"></i>
                    </div>
                    <input type="text" id="searchInput"
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 pl-12 pr-44 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900 transition-all text-slate-800 shadow-sm"
                        placeholder="Buscar por título, autor o tema...">
                    <button id="clearSearch" class="hidden absolute right-32 top-2 bottom-2 px-3 text-slate-400 hover:text-slate-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                    <button
                        class="absolute right-2 top-2 bottom-2 bg-blue-900 text-white px-6 rounded-xl font-medium hover:bg-blue-800 transition-colors">
                        Buscar
                    </button>
                </div>
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase mr-2 pt-1">Sugerencias:</span>
                    <?php foreach ($sugerenciasPopulares as $kw): ?>
                        <button class="search-tag text-xs bg-slate-100 text-slate-600 px-3 py-1.5 rounded-full hover:bg-slate-200 transition-colors capitalize"><?= htmlspecialchars($kw) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <!-- Recent Documents Section -->
        <section id="recentSection" class="max-w-7xl mx-auto px-4 pt-12 transition-all duration-300">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="serif text-3xl text-slate-900 italic">Últimos agregados</h3>
                    <div class="h-1 w-20 bg-blue-900 mt-2 rounded"></div>
                </div>
                <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Novedades del Portal</span>
            </div>
            <div id="recentGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Recent books will be injected here -->
            </div>
        </section>
        <!-- Catalog Section -->
        <section class="max-w-7xl mx-auto px-4 py-12">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div class="flex flex-wrap gap-2" id="filterContainer">
                    <button
                        class="filter-tag active px-4 py-2 rounded-lg border border-slate-200 text-sm font-medium transition-all"
                        data-category="all">Todos</button>
                    <button
                        class="filter-tag px-4 py-2 rounded-lg border border-slate-200 text-sm font-medium bg-white text-slate-600 hover:border-slate-300 transition-all"
                        data-category="tesis">Tesis</button>
                    <button
                        class="filter-tag px-4 py-2 rounded-lg border border-slate-200 text-sm font-medium bg-white text-slate-600 hover:border-slate-300 transition-all"
                        data-category="articulo">Artículos</button>
                    <button
                        class="filter-tag px-4 py-2 rounded-lg border border-slate-200 text-sm font-medium bg-white text-slate-600 hover:border-slate-300 transition-all"
                        data-category="acervo">Acervos</button>
                    <?php if ($isLoggedBib): ?>
                        <button
                            class="filter-tag px-4 py-2 rounded-lg border border-yellow-100 text-sm font-medium bg-yellow-50/30 text-yellow-600 hover:bg-yellow-50 transition-all flex items-center gap-2"
                            data-category="favorites">
                            <i data-lucide="star" class="w-4 h-4 fill-yellow-500 text-yellow-500"></i> Mis Favoritos
                        </button>
                        <button
                            class="filter-tag px-4 py-2 rounded-lg border border-indigo-100 text-sm font-medium bg-indigo-50/30 text-indigo-600 hover:bg-indigo-50 transition-all flex items-center gap-2"
                            data-category="recent_views">
                            <i data-lucide="history" class="w-4 h-4"></i> Últimos Vistos
                        </button>
                    <?php endif; ?>
                </div>
                <div class="text-sm text-slate-500 font-medium">
                    Mostrando <span id="itemCount" class="text-slate-900">0</span> resultados
                </div>
            </div>
            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-8" id="booksGrid"></div>
            <!-- Pagination Container -->
            <div id="paginationContainer" class="mt-12 flex flex-col items-center gap-4">
                <!-- Pagination buttons will be injected here -->
            </div>
            <div id="noResults" class="hidden py-20 text-center">
                <div class="bg-slate-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="search-x" class="text-slate-400"></i>
                </div>
                <p class="text-slate-500 font-medium">No se encontraron documentos con esos criterios.</p>
            </div>
        </section>
    </main>
    <footer class="bg-gray-900 text-white py-12 border-t border-gray-800">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4">CIEEPE | ENEES</h3>
                    <p class="text-gray-400 max-w-sm mb-6">
                        Centro de Investigación de Educación Especial y Políticas Educativas.
                        Generando conocimiento para una educación más inclusiva y justa.
                    </p>
                    <div class="flex space-x-4">
                        <div
                            class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors cursor-pointer">
                            <i data-lucide="globe" class="w-5 h-5"></i>
                        </div>
                        <div
                            class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors cursor-pointer">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-gray-200">Enlaces Rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="index.html#inicio"
                                class="text-gray-400 hover:text-white transition-colors">Inicio</a></li>
                        <li><a href="index.html#nosotros"
                                class="text-gray-400 hover:text-white transition-colors">Nosotros</a></li>
                        <li><a href="index.html#investigacion"
                                class="text-gray-400 hover:text-white transition-colors">Líneas de Investigación</a>
                        </li>
                        <li><a href="proyectos.php"
                                class="text-gray-400 hover:text-white transition-colors">Proyectos</a></li>
                        <li><a href="noticias.php" class="text-gray-400 hover:text-white transition-colors">Noticias</a>
                        </li>
                        <li><a href="biblioteca.php"
                                class="text-blue-400 hover:text-white transition-colors font-medium">Biblioteca Virtual
                                (IA)</a></li>
                        <li><a href="investigadores.php"
                                class="text-gray-400 hover:text-white transition-colors">Equipo</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-gray-200">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Aviso de Privacidad</a>
                        </li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Transparencia</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">ENEES Oficial</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-500 text-sm">
                <p>&copy; <span id="footerYear"></span> CIEEPE - Escuela Normal de Especialización del Estado de
                    Sinaloa. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    <!-- Book Detail Modal -->
    <div id="bookModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="modal-overlay absolute inset-0" onclick="closeBookModal()"></div>
        <div
            class="bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden relative shadow-2xl flex flex-col md:flex-row">
            <button onclick="closeBookModal()"
                class="absolute top-4 right-4 z-10 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                <i data-lucide="x" class="w-5 h-5 text-slate-600"></i>
            </button>
            <div class="w-full md:w-1/3 bg-slate-100 p-8 flex items-center justify-center border-r border-slate-200">
                <div id="modalCover"
                    class="w-full aspect-[3/4] rounded-lg shadow-xl relative overflow-hidden flex flex-col items-center justify-center text-center p-6 border border-white/50">
                    <i data-lucide="book" class="w-12 h-12 mb-4 opacity-30"></i>
                    <p id="modalCoverTitle" class="text-xs font-bold uppercase"></p>
                </div>
            </div>
            <div class="w-full md:w-2/3 p-8 md:p-12 overflow-y-auto custom-scrollbar">
                <div id="modalCategory"
                    class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-800 text-[10px] font-bold uppercase mb-4 tracking-wider">
                </div>
                <h2 id="modalTitle" class="serif text-3xl text-slate-900 mb-2"></h2>
                <p id="modalAuthor" class="text-lg text-slate-600 mb-6"></p>
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Año</p>
                        <p id="modalYear" class="text-slate-900 font-semibold"></p>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Formato</p>
                        <p class="text-slate-900 font-semibold">PDF</p>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Idioma</p>
                        <p class="text-slate-900 font-semibold">Español</p>
                    </div>
                </div>
                <div class="mb-8">
                    <h4 class="font-bold text-slate-900 mb-2">Resumen Académico</h4>
                    <p id="modalDesc" class="text-slate-600 text-sm leading-relaxed mb-4"></p>
                    <div id="modalKeywords" class="flex flex-wrap gap-2"></div>
                </div>
                <!-- INFO EXTRA DINÁMICA -->
                <div id="modalExtraInfo" class="mb-8 p-4 bg-slate-50 rounded-2xl border border-slate-100 hidden">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i> Información Técnica
                    </h4>
                    <div id="extraInfoContent" class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6"></div>
                </div>
                <div class="flex flex-col gap-6">
                    <?php if ($isLoggedBib): ?>
                        <div class="flex flex-wrap gap-4">
                            <button id="readBtn"
                                class="flex-1 bg-blue-900 text-white py-3 rounded-xl font-bold hover:bg-blue-800 transition-all flex items-center justify-center gap-2">
                                <i data-lucide="book-open" class="w-4 h-4"></i> Leer Documento
                            </button>
                            <button id="favBtn"
                                class="bg-slate-100 text-slate-700 p-3 rounded-xl hover:bg-slate-200 transition-all"
                                title="Guardar a favoritos">
                                <i id="favIcon" data-lucide="bookmark" class="w-5 h-5"></i>
                            </button>
                            <button id="citeBtn" class="bg-slate-100 text-slate-700 p-3 rounded-xl hover:bg-slate-200 transition-all"
                                title="Citar (APA 7)">
                                <i data-lucide="quote" class="w-5 h-5"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <div
                            class="bg-slate-50 border border-slate-200 rounded-3xl p-8 text-center relative overflow-hidden group">
                            <div
                                class="absolute top-0 right-0 w-24 h-24 bg-blue-100/50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110">
                            </div>
                            <div class="relative z-10">
                                <div
                                    class="w-14 h-14 bg-white text-blue-900 rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-4 border border-blue-50/50">
                                    <i data-lucide="lock" class="w-7 h-7"></i>
                                </div>
                                <h4 class="text-slate-900 font-bold text-lg mb-2">Lectura Protegida</h4>
                                <p class="text-slate-500 text-sm mb-6 max-w-sm mx-auto">Para acceder a este documento y ver
                                    el material completo del acervo CIATA, es necesario contar con una cuenta.</p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <a href="login_biblioteca.php?redirect=biblioteca.php"
                                        class="bg-blue-900 text-white px-8 py-3 rounded-xl font-bold text-sm hover:bg-black transition-all shadow-lg shadow-blue-900/10 active:scale-95 text-center">
                                        Iniciar Sesión
                                    </a>
                                    <a href="registro_biblioteca.php"
                                        class="bg-white text-blue-900 border border-blue-100 px-8 py-3 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all active:scale-95 text-center">
                                        Crear Cuenta
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($isLoggedBib && $showCiataBtn): ?>
        <!-- ============================================== -->
        <!-- FLOATING CIATA BUTTON -->
        <!-- ============================================== -->
        <div class="fixed bottom-8 right-8 z-[55] group">
            <div class="fab-pulse-ring"></div>
            <button id="ciata-fab"
                class="relative w-[72px] h-[72px] bg-[#0f172a] rounded-full shadow-2xl shadow-blue-900/50 flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300 z-50 group-hover:shadow-[0_0_50px_rgba(37,99,235,0.5)] border-2 border-blue-500/40 overflow-hidden">
                <div class="relative w-12 h-12 flex items-center justify-center">
                    <div class="circle-ring ring-1" style="width:100%;height:100%;border-width:2px;"></div>
                    <div class="circle-ring ring-2" style="width:75%;height:75%;border-width:1.5px;"></div>
                    <div class="circle-ring ring-3" style="width:50%;height:50%;border-width:1.5px;"></div>
                    <div class="core-pulse" style="width:35%;height:35%;"></div>
                </div>
            </button>
            <div
                class="absolute bottom-[88px] right-0 bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none shadow-lg">
                Pregúntale al CIATA
            </div>
        </div>
    <?php endif; ?>
    <?php if ($isLoggedBib && $showCiataBtn): ?>
        <!-- ============================================== -->
        <!-- CIATA FULL-SCREEN MODAL OVERLAY -->
        <!-- ============================================== -->
        <div id="ciata-overlay" class="fixed inset-0 z-[60] flex items-center justify-center p-4 md:p-8 closed"
            style="background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);">
            <div id="ciata-modal-inner"
                class="relative w-full h-full max-w-6xl max-h-[92vh] rounded-2xl overflow-hidden shadow-2xl border border-white/10 flex flex-col"
                style="background: linear-gradient(-45deg, #0f172a, #1e1b4b, #1e3a8a); background-size: 300% 300%; animation: gradientBG 15s ease infinite;">
                <!-- Modal Header Bar -->
                <div class="flex items-center justify-between px-6 py-3 bg-black/30 border-b border-white/5 flex-shrink-0">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-9 h-9 rounded-full bg-blue-600/20 flex items-center justify-center border border-blue-400/30">
                            <i data-lucide="bot" class="w-5 h-5 text-blue-300"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-sm leading-tight">CIATA</h3>
                            <p
                                class="text-[10px] text-blue-200/60 uppercase tracking-widest font-semibold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span> Analista AI en
                                Línea
                            </p>
                        </div>
                    </div>
                    <button id="close-ciata-btn"
                        class="p-2 hover:bg-white/10 rounded-full transition text-slate-400 hover:text-white"
                        title="Cerrar">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <!-- LANDING VIEW -->
                <div id="ciata-landing"
                    class="flex-1 flex flex-col items-center justify-center text-center px-6 transition-all duration-500"
                    style="font-family:'Inter',sans-serif;">
                    <h2
                        class="text-5xl md:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-200 via-white to-purple-200 mb-2 drop-shadow-lg">
                        CIATA
                    </h2>
                    <p class="text-lg md:text-xl text-blue-200/80 font-light tracking-widest uppercase mb-10">
                        Centro de Inteligencia Artificial de Tesis y Acervos
                    </p>
                    <div class="thinking-circle-container mb-12">
                        <div class="circle-ring ring-1"></div>
                        <div class="circle-ring ring-2"></div>
                        <div class="circle-ring ring-3"></div>
                        <div class="core-pulse"></div>
                    </div>
                    <div class="w-full max-w-3xl relative">
                        <div
                            class="bg-white/10 backdrop-blur-md border border-white/10 rounded-full p-2 flex items-center transition-all duration-300 hover:bg-white/15">
                            <i data-lucide="search" class="w-6 h-6 text-blue-300 ml-4 mr-2"></i>
                            <input type="text" id="landingInput" placeholder="Pregunta sobre tesis, autores o temas..."
                                class="w-full bg-transparent border-none text-xl py-4 px-2 focus:ring-0 rounded-full text-white placeholder-blue-200/50 outline-none text-center"
                                autocomplete="off" />
                            <button id="landingSearchBtn"
                                class="ml-2 bg-blue-600 hover:bg-blue-500 text-white p-3 rounded-full transition-colors shadow-lg shadow-blue-600/30">
                                <i data-lucide="arrow-right" class="w-6 h-6"></i>
                            </button>
                            <button id="landing-mic-btn"
                                class="hidden ml-1 bg-purple-600/80 hover:bg-purple-500 text-white p-3 rounded-full transition-all shadow-lg shadow-purple-600/30"
                                title="Hablar con CIATA">
                                <i data-lucide="mic" class="w-6 h-6"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- CHAT VIEW (hidden initially) -->
                <div id="ciata-chat" class="hidden flex-1 flex flex-col overflow-hidden">
                    <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-4 scroll-smooth flex flex-col"></div>
                    <div class="p-4 bg-black/30 border-t border-white/5 flex-shrink-0">
                        <div class="max-w-4xl mx-auto relative flex items-center">
                            <textarea id="chatInput" rows="1"
                                class="w-full bg-white/5 border border-white/10 rounded-xl pl-4 pr-14 py-3 text-base text-white focus:outline-none focus:ring-1 focus:ring-blue-500/50 resize-none transition-colors placeholder-slate-500"
                                placeholder="Escribe tu siguiente consulta..."
                                style="font-family:'Inter',sans-serif;"></textarea>
                            <button id="chatSendBtn"
                                class="absolute right-2 p-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg shadow-lg transition transform active:scale-95">
                                <i data-lucide="send-horizontal" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <!-- Mic button for voice mode -->
                        <div class="flex justify-center mt-2">
                            <button id="chat-mic-btn"
                                class="hidden bg-purple-600/80 hover:bg-purple-500 text-white px-5 py-2 rounded-full transition-all shadow-lg shadow-purple-600/30 flex items-center gap-2 text-sm font-medium"
                                title="Hablar con CIATA">
                                <i data-lucide="mic" class="w-4 h-4"></i>
                                <span id="mic-status-label">Toca para hablar</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-center text-slate-500 mt-2">La IA puede cometer errores. Verifica la
                            información.</p>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes gradientBG {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }
        </style>
    <?php endif; ?>
    <script>
        const userFavs = <?php echo json_encode($userFavs); ?>;
        const userVistos = <?php echo json_encode($userVistos); ?>;
        const books = <?php echo json_encode($documentosDB); ?>.map(doc => {
            // Asignar un color aleatorio si no tiene uno definido (para el diseño de portadas genéricas)
            const colors = ["#1e3a8a", "#6b21a8", "#15803d", "#c2410c", "#0e7490", "#be123c"];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];

            return {
                id: doc.id,
                title: doc.titulo,
                author: doc.autor_nombre,
                year: new Date(doc.fecha_subida).getFullYear(),
                category: doc.tipo,
                color: randomColor,
                desc: doc.resumen,
                cover: doc.imagen_portada,
                file: doc.archivo_documento,
                institucion: doc.institucion,
                grado: doc.grado,
                asesor: doc.asesor,
                revista: doc.revista,
                issn: doc.issn,
                doi: doc.doi,
                tipo_material: doc.tipo_material,
                categoria_acervo: doc.categoria_acervo,
                derechos: doc.derechos,
                keywords: doc.palabras_clave,
                isFavorite: userFavs.includes(doc.id.toString()) || userFavs.includes(parseInt(doc.id)),
                lastViewedOrder: userVistos.indexOf(doc.id.toString()) !== -1 ?
                    userVistos.indexOf(doc.id.toString()) :
                    (userVistos.indexOf(parseInt(doc.id)) !== -1 ?
                        userVistos.indexOf(parseInt(doc.id)) : 999999)
            };
        });
        // Mapeo de categorías para visualización amigable
        const categoryLabels = {
            'articulo': 'Artículos',
            'tesis': 'Tesis',
            'acervo': 'Acervos'
        };
        const categorySingular = {
            'articulo': 'Artículo',
            'tesis': 'Tesis',
            'acervo': 'Acervo'
        };
        let currentCategory = 'all';
        let searchQuery = '';
        let currentPage = 1;
        const itemsPerPage = 20;
        const booksGrid = document.getElementById('booksGrid');
        const itemCount = document.getElementById('itemCount');
        const searchInput = document.getElementById('searchInput');
        const filterContainer = document.getElementById('filterContainer');
        const noResults = document.getElementById('noResults');
        const paginationContainer = document.getElementById('paginationContainer');
        function renderBooks() {
            const filtered = books.filter(book => {
                const matchCat = currentCategory === 'all' ||
                    (currentCategory === 'favorites' ? book.isFavorite :
                        (currentCategory === 'recent_views' ? book.lastViewedOrder < 999999 :
                            book.category === currentCategory));
                const matchSearch = book.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    book.author.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    (book.keywords && book.keywords.toLowerCase().includes(searchQuery.toLowerCase()));
                return matchCat && matchSearch;
            });
            if (currentCategory === 'recent_views') {
                filtered.sort((a, b) => a.lastViewedOrder - b.lastViewedOrder);
            }

            // Ocultar sección de recientes si hay búsqueda o filtro activo
            const recentSection = document.getElementById('recentSection');
            if (recentSection) {
                if (searchQuery.trim() !== '' || currentCategory !== 'all') {
                    recentSection.classList.add('hidden');
                } else {
                    recentSection.classList.remove('hidden');
                }
            }

            const totalItems = filtered.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            // Ajustar página actual si excede el total
            if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
            // Segmentar para paginación
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedItems = filtered.slice(start, end);
            booksGrid.innerHTML = '';
            itemCount.textContent = totalItems;

            if (totalItems === 0) {
                noResults.classList.remove('hidden');
                paginationContainer.innerHTML = '';
            } else {
                noResults.classList.add('hidden');
                paginatedItems.forEach(book => {
                    const card = document.createElement('div');
                    card.className = 'book-card bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 flex flex-col cursor-pointer';
                    card.onclick = () => openBookModal(book);
                    card.innerHTML = `
                        <div class="h-48 relative overflow-hidden flex items-center justify-center p-6 border-b border-slate-50" style="background-color: ${book.color}15">
                            ${book.cover ?
                                    `<img src="${book.cover}" class="w-24 h-32 object-cover rounded shadow-lg border border-white/50">` :
                                    `<div class="w-24 h-32 bg-white rounded shadow-lg border-l-4 border-slate-900 flex flex-col justify-end p-2 relative" style="border-left-color: ${book.color}">
                                    <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(circle, #000 1px, transparent 1px); background-size: 10px 10px;"></div>
                                    <div class="w-full h-1 bg-slate-100 mb-1 rounded"></div>
                                    <div class="w-2/3 h-1 bg-slate-100 rounded"></div>
                                    <i data-lucide="file-text" class="absolute top-2 right-2 w-3 h-3 text-slate-300"></i>
                                </div>`
                                }
                        </div>
                        <div class="p-4 flex-grow flex flex-col">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">${categorySingular[book.category] || book.category}</span>
                                <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">${book.year}</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-sm leading-tight mb-2 line-clamp-2">${book.title}</h3>
                            <p class="text-xs text-slate-500 mt-auto">${book.author}</p>
                        </div>
                    `;
                    booksGrid.appendChild(card);
                });
                renderPagination(totalItems);
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
                function renderPagination(totalItems) {
                    const totalPages = Math.ceil(totalItems / itemsPerPage);
                    if (totalPages <= 1) {
                        paginationContainer.innerHTML = '';
                        return;
                    }
                    let html = `
                <div class="flex items-center justify-center gap-2">
                    <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} 
                        class="p-2 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>
            `;
                    // Lógica para mostrar números de página (máximo 5 visibles)
                    let startPage = Math.max(1, currentPage - 2);
                    let endPage = Math.min(totalPages, startPage + 4);
                    if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
                    for (let i = startPage; i <= endPage; i++) {
                        html += `
                    <button onclick="changePage(${i})" 
                        class="w-10 h-10 rounded-lg border ${currentPage === i ? 'bg-blue-900 text-white border-blue-900 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'} font-bold text-sm transition-all">
                        ${i}
                    </button>
                `;
                    }
                    html += `
                    <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} 
                        class="p-2 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                    Página ${currentPage} de ${totalPages} • Mostrando ${Math.min(totalItems, (currentPage - 1) * itemsPerPage + 1)} a ${Math.min(totalItems, currentPage * itemsPerPage)} de ${totalItems}
                </p>
            `;
                    paginationContainer.innerHTML = html;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
                window.changePage = function (page) {
                    const totalItems = itemCount.textContent; // Ya filtrado
                    const totalPages = Math.ceil(totalItems / itemsPerPage);
                    if (page < 1 || page > totalPages) return;

                    currentPage = page;
                    renderBooks();

                    // Scroll suave hacia la sección de catálogo
                    document.getElementById('filterContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
                };
                function renderRecent() {
                    const recentGrid = document.getElementById('recentGrid');
                    if (!recentGrid) return;

                    const recent = books.slice(0, 6);
                    recentGrid.innerHTML = '';

                    recent.forEach(book => {
                        const card = document.createElement('div');
                        card.className = 'book-card bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 flex flex-col cursor-pointer hover:shadow-md transition-all scale-95 hover:scale-100';
                        card.onclick = () => openBookModal(book);
                        card.innerHTML = `
                    <div class="h-32 relative overflow-hidden flex items-center justify-center p-4 border-b border-slate-50" style="background-color: ${book.color}10">
                        ${book.cover ?
                                `<img src="${book.cover}" class="w-16 h-20 object-cover rounded shadow-md border border-white/50">` :
                                `<div class="w-16 h-20 bg-white rounded shadow-md border-l-4 border-slate-900 flex flex-col justify-end p-1.5 relative" style="border-left-color: ${book.color}">
                                <div class="w-full h-0.5 bg-slate-100 mb-0.5 rounded"></div>
                                <div class="w-2/3 h-0.5 bg-slate-100 rounded"></div>
                            </div>`
                            }
                    </div>
                    <div class="p-3 flex-grow flex flex-col">
                        <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 mb-1">${categorySingular[book.category] || book.category}</span>
                        <h4 class="font-bold text-slate-900 text-[10px] leading-tight line-clamp-2">${book.title}</h4>
                    </div>
                `;
                        recentGrid.appendChild(card);
                    });
                }
                // Search
                const clearBtn = document.getElementById('clearSearch');
                searchInput.addEventListener('input', (e) => {
                    searchQuery = e.target.value;
                    currentPage = 1; // Reiniciar paginación
                    renderBooks();

                    // Mostrar/Ocultar botón X
                    if (searchQuery.length > 0) {
                        clearBtn.classList.remove('hidden');
                    } else {
                        clearBtn.classList.add('hidden');
                    }
                });

                // Clear Search Button Logic
                clearBtn.addEventListener('click', () => {
                    searchInput.value = '';
                    searchQuery = '';
                    currentPage = 1;
                    clearBtn.classList.add('hidden');
                    renderBooks();
                    searchInput.focus();
                });
                // Search Tags
                document.querySelectorAll('.search-tag').forEach(tag => {
                    tag.addEventListener('click', () => {
                        const val = tag.textContent.trim();
                        searchInput.value = val;
                        searchQuery = val;
                        currentPage = 1; // Reiniciar paginación
                        if (clearBtn) clearBtn.classList.remove('hidden');
                        renderBooks();
                        
                        // Scroll suave hacia la sección de resultados
                        const filterPos = document.getElementById('filterContainer');
                        if (filterPos) {
                            filterPos.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                });
                // Filters
                filterContainer.addEventListener('click', (e) => {
                    if (e.target.classList.contains('filter-tag')) {
                        document.querySelectorAll('.filter-tag').forEach(t => t.classList.remove('active'));
                        e.target.classList.add('active');
                        currentCategory = e.target.dataset.category;
                        currentPage = 1; // Reiniciar paginación
                        renderBooks();
                    }
                });
                // Book Modal
                const bookModal = document.getElementById('bookModal');
                function openBookModal(book) {
                    document.getElementById('modalTitle').textContent = book.title;
                    document.getElementById('modalAuthor').textContent = book.author;
                    document.getElementById('modalYear').textContent = book.year;
                    document.getElementById('modalDesc').textContent = book.desc;
                    document.getElementById('modalCategory').textContent = categorySingular[book.category] || book.category;
                    const cover = document.getElementById('modalCover');
                    if (book.cover) {
                        cover.innerHTML = `<img src="${book.cover}" class="w-full h-full object-cover rounded-lg">`;
                        cover.style.backgroundColor = 'transparent';
                        cover.style.borderColor = 'transparent';
                    } else {
                        cover.innerHTML = `
                    <i data-lucide="book" class="w-12 h-12 mb-4 opacity-30"></i>
                    <p id="modalCoverTitle" class="text-xs font-bold uppercase">${categorySingular[book.category] || book.category}</p>
                `;
                        cover.style.backgroundColor = book.color + '20';
                        cover.style.color = book.color;
                        cover.style.borderColor = book.color;
                    }
                    const readBtn = document.getElementById('readBtn');
                    if (readBtn) {
                        readBtn.onclick = () => {
                            registerView(book.id);
                            openDocumentViewer(book.file, book.title);
                        };
                    }
                    const favBtn = document.getElementById('favBtn');
                    if (favBtn) {
                        favBtn.onclick = () => toggleFavorite(book);
                        updateFavBtnUI(book.isFavorite);
                    }
                    const citeBtn = document.getElementById('citeBtn');
                    if (citeBtn) {
                        citeBtn.onclick = () => copyCitation(book);
                    }
                    // Información Extra Dinámica
                    const extraContainer = document.getElementById('modalExtraInfo');
                    const extraContent = document.getElementById('extraInfoContent');
                    extraContent.innerHTML = '';
                    let hasExtra = false;
                    const addExtraItem = (icon, label, value) => {
                        if (!value || value.trim() === '') return;
                        hasExtra = true;
                        const div = document.createElement('div');
                        div.className = 'flex flex-col';
                        div.innerHTML = `
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="${icon}" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">${label}</span>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 leading-tight">${value}</p>
                `;
                        extraContent.appendChild(div);
                    };
                    if (book.category === 'tesis') {
                        addExtraItem('building', 'Institución', book.institucion);
                        addExtraItem('graduation-cap', 'Grado', book.grado);
                        addExtraItem('user-check', 'Asesor', book.asesor);
                    } else if (book.category === 'articulo') {
                        addExtraItem('library', 'Revista', book.revista);
                        addExtraItem('hash', 'ISSN', book.issn);
                        if (book.doi) {
                            const doiVal = book.doi.startsWith('http') ? book.doi : `https://doi.org/${book.doi}`;
                            addExtraItem('link', 'DOI', `<a href="${doiVal}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1">${book.doi} <i data-lucide="external-link" class="w-3 h-3"></i></a>`);
                        }
                    } else if (book.category === 'acervo') {
                        addExtraItem('box', 'Tipo Material', book.tipo_material);
                        addExtraItem('folder', 'Categoría', book.categoria_acervo);
                        addExtraItem('shield-alert', 'Derechos', book.derechos);
                    }
                    if (hasExtra) {
                        extraContainer.classList.remove('hidden');
                    } else {
                        extraContainer.classList.add('hidden');
                    }
                    // Palabras Clave
                    const kwContainer = document.getElementById('modalKeywords');
                    kwContainer.innerHTML = '';
                    if (book.keywords) {
                        book.keywords.split(',').forEach(kw => {
                            const tag = document.createElement('span');
                            tag.className = 'px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 cursor-pointer hover:bg-blue-100 transition-colors';
                            tag.textContent = kw.trim();
                            tag.onclick = () => {
                                closeBookModal();
                                document.getElementById('searchInput').value = kw.trim();
                                searchQuery = kw.trim();
                                renderBooks();
                            };
                            kwContainer.appendChild(tag);
                        });
                    }
                    bookModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    lucide.createIcons();
                }
                function closeBookModal() {
                    bookModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
                async function registerView(bookId) {
                    if (!<?php echo $isLoggedBib ? 'true' : 'false'; ?>) return;
                    // Actualización local para reflejar el cambio en tiempo real (Optimista)
                    const bookIdx = books.findIndex(b => b.id == bookId);
                    if (bookIdx !== -1) {
                        const prevOrder = books[bookIdx].lastViewedOrder;
                        // Incrementar el orden de los que eran más recientes que este o estaban en el historial
                        books.forEach(b => {
                            if (b.lastViewedOrder < prevOrder) {
                                b.lastViewedOrder++;
                            }
                        });
                        // Poner este como el más reciente
                        books[bookIdx].lastViewedOrder = 0;
                        // Si estamos en el filtro de 'Últimos Vistos', refrescar la vista
                        if (currentCategory === 'recent_views') renderBooks();
                    }
                    try {
                        fetch('api/registrar_visto.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ doc_id: bookId })
                        });
                    } catch (error) {
                        console.warn("Error registrar visto:", error);
                    }
                }
                async function toggleFavorite(book) {
                    if (!<?php echo $isLoggedBib ? 'true' : 'false'; ?>) {
                        alert("Debes iniciar sesión para guardar favoritos.");
                        return;
                    }
                    try {
                        const response = await fetch('api/toggle_favorito.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ doc_id: book.id })
                        });
                        const data = await response.json();

                        if (data.success) {
                            book.isFavorite = (data.action === 'added');
                            updateFavBtnUI(book.isFavorite);
                            if (currentCategory === 'favorites') renderBooks();
                        } else {
                            alert(data.error || "Error al actualizar favoritos");
                        }
                    } catch (error) {
                        console.error("Error toggle favorite:", error);
                    }
                }
                function updateFavBtnUI(isFav) {
                    const btn = document.getElementById('favBtn');
                    if (!btn) return;
                    if (isFav) {
                        btn.classList.add('bg-yellow-50', 'text-yellow-600', 'border-yellow-200');
                        btn.classList.remove('bg-slate-100', 'text-slate-700');
                        btn.innerHTML = `<i data-lucide="star" class="w-5 h-5 fill-yellow-500"></i>`;
                        btn.title = "Quitar de favoritos";
                    } else {
                        btn.classList.remove('bg-yellow-50', 'text-yellow-600', 'border-yellow-200');
                        btn.classList.add('bg-slate-100', 'text-slate-700');
                        btn.innerHTML = `<i data-lucide="star" class="w-5 h-5 text-slate-400"></i>`;
                        btn.title = "Guardar a favoritos";
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
        <?php if ($isLoggedBib && $showCiataBtn): ?>
            // ============================
            // CIATA AI CHAT LOGIC
            // ============================
            const fab = document.getElementById('ciata-fab');
                    const overlay = document.getElementById('ciata-overlay');
                    const closeCiataBtn = document.getElementById('close-ciata-btn');
                    const ciataLanding = document.getElementById('ciata-landing');
                    const ciataChat = document.getElementById('ciata-chat');
                    const landingInput = document.getElementById('landingInput');
                    const landingSearchBtn = document.getElementById('landingSearchBtn');
                    const chatInput = document.getElementById('chatInput');
                    const chatSendBtn = document.getElementById('chatSendBtn');
                    const messagesContainer = document.getElementById('chat-messages');
                    let hasStartedChat = false;
                    fab.addEventListener('click', () => {
                        overlay.classList.remove('closed');
                        overlay.classList.add('open');
                        setTimeout(() => (hasStartedChat ? chatInput : landingInput).focus(), 200);
                    });
                    closeCiataBtn.addEventListener('click', () => {
                        overlay.classList.remove('open');
                        overlay.classList.add('closed');
                    });
                    overlay.addEventListener('click', (e) => {
                        if (e.target === overlay) {
                            overlay.classList.remove('open');
                            overlay.classList.add('closed');
                        }
                    });
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            if (overlay && overlay.classList.contains('open')) {
                                overlay.classList.remove('open');
                                overlay.classList.add('closed');
                            } else {
                                if (typeof closeBookModal === 'function') closeBookModal();
                            }
                        }
                    });
                    if (landingInput) {
                        landingInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') handleCiataSearch(landingInput.value); });
                        landingSearchBtn.addEventListener('click', () => handleCiataSearch(landingInput.value));
                        chatInput.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleCiataSearch(chatInput.value); } });
                        chatSendBtn.addEventListener('click', () => handleCiataSearch(chatInput.value));
                    }
                    function transitionToChat() {
                        if (!ciataLanding || !ciataChat) return;
                        ciataLanding.style.opacity = '0';
                        ciataLanding.style.transform = 'translateY(-30px)';
                        setTimeout(() => {
                            ciataLanding.classList.add('hidden');
                            ciataChat.classList.remove('hidden');
                            ciataChat.style.display = 'flex';
                            chatInput.focus();
                        }, 300);
                        hasStartedChat = true;
                    }
                    async function handleCiataSearch(query) {
                        const text = query.trim();
                        if (!text) return;
                        if (!hasStartedChat) {
                            landingInput.value = '';
                            transitionToChat();
                        }
                        addMessage(text, 'user');
                        chatInput.value = '';
                        const typingId = showTypingIndicator();
                        try {
                            const response = await fetch('api/buscar_cieta.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ query: text })
                            });
                            const data = await response.json();
                            removeTypingIndicator(typingId);
                            if (data.error) {
                                addMessage("Lo siento, hubo un error técnico (" + data.error + ").", 'bot');
                                return;
                            }
                            let replyText = data.reply?.reply || data.answer || "No encontré información específica, pero puedo buscar otro tema.";
                            addMessage(replyText, 'bot', data.searchResults);
                        } catch (error) {
                            removeTypingIndicator(typingId);
                            addMessage("Error de conexión. Verifica tu internet.", 'bot');
                            console.error(error);
                        }
                    }
                    function addMessage(text, sender, sources = []) {
                        const div = document.createElement('div');
                        const isBot = sender === 'bot';
                        div.className = `message ${sender} p-4 max-w-[80%] text-sm leading-relaxed mb-3 ${isBot ? 'self-start bg-white/5 border border-white/5 text-slate-200 rounded-xl rounded-tl-none' : 'self-end bg-blue-600 text-white rounded-xl rounded-tr-none'}`;
                        let content = '';
                        if (isBot) {
                            content += `<div class="font-semibold text-blue-300 mb-1 text-xs uppercase tracking-wide flex items-center gap-1"><i data-lucide="bot" class="w-3 h-3"></i> Analista CIATA</div>`;
                        }
                        content += `<div>${text.replace(/\n/g, '<br>')}</div>`;
                        if (sources && sources.length > 0) {
                            content += `<div class="sources-container mt-3 pt-3 border-t border-white/10"><p class="text-[10px] text-slate-400 mb-1 font-bold uppercase">Fuentes:</p>`;
                            sources.forEach(src => {
                                const title = src.document?.derivedStructData?.title || "Documento";
                                const link = src.document?.derivedStructData?.link || "#";
                                content += `<a href="${link}" target="_blank" class="block text-xs text-blue-400 hover:text-blue-300 truncate">${title}</a>`;
                            });
                            content += `</div>`;
                        }
                        div.innerHTML = content;
                        messagesContainer.appendChild(div);
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                    function showTypingIndicator() {
                        const id = 'typing-' + Date.now();
                        const div = document.createElement('div');
                        div.id = id;
                        div.className = 'self-start bg-white/5 border border-white/5 rounded-xl rounded-tl-none p-3 mb-3 flex gap-1 items-center';
                        div.innerHTML = `<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>`;
                        messagesContainer.appendChild(div);
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        return id;
                    }
                    function removeTypingIndicator(id) {
                        const el = document.getElementById(id);
                        if (el) el.remove();
                    }
        <?php endif; ?>
                    // Init
                    document.addEventListener('DOMContentLoaded', () => {
                        if (typeof renderBooks === 'function') renderBooks();
                        if (typeof renderRecent === 'function') renderRecent();
                        if (typeof lucide !== 'undefined') lucide.createIcons();

                        const fy = document.getElementById('footerYear');
                        if (fy) fy.textContent = new Date().getFullYear();
                    });
    </script>
    <!-- ============================================== -->
    <!-- ACCESSIBILITY FLOATING BUTTON + PANEL          -->
    <!-- ============================================== -->
    <style>
        /* Accessibility states - only active when class is added to <html> */
        html.a11y-contrast body {
            background-color: #000 !important;
            color: #fff !important;
        }

        html.a11y-contrast header,
        html.a11y-contrast footer,
        html.a11y-contrast section,
        html.a11y-contrast main,
        html.a11y-contrast .bg-white,
        html.a11y-contrast .bg-slate-50,
        html.a11y-contrast .bg-slate-100 {
            background-color: #111 !important;
            color: #fff !important;
            border-color: #555 !important;
        }

        html.a11y-contrast h1,
        html.a11y-contrast h2,
        html.a11y-contrast h3,
        html.a11y-contrast p,
        html.a11y-contrast span,
        html.a11y-contrast a,
        html.a11y-contrast label,
        html.a11y-contrast li {
            color: #fff !important;
        }

        html.a11y-contrast input,
        html.a11y-contrast textarea {
            background-color: #222 !important;
            color: #fff !important;
            border-color: #777 !important;
        }

        html.a11y-contrast .book-card {
            background-color: #1a1a1a !important;
            border-color: #444 !important;
        }

        html.a11y-contrast img {
            filter: brightness(0.85) contrast(1.1);
        }

        /* Large text */
        html.a11y-large body,
        html.a11y-large p,
        html.a11y-large span,
        html.a11y-large li,
        html.a11y-large a {
            font-size: 1.15rem !important;
            line-height: 1.8 !important;
        }

        html.a11y-large h1 {
            font-size: 2.6rem !important;
        }

        html.a11y-large h2 {
            font-size: 2.2rem !important;
        }

        html.a11y-large h3 {
            font-size: 1.5rem !important;
        }

        html.a11y-large input,
        html.a11y-large textarea,
        html.a11y-large button {
            font-size: 1.1rem !important;
        }

        /* Panel */
        #a11y-panel {
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        #a11y-panel.a11y-hidden {
            opacity: 0;
            transform: translateY(10px) scale(0.97);
            pointer-events: none;
        }

        .a11y-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            background: #f1f5f9;
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
            color: #1e293b;
            gap: 8px;
        }

        .a11y-toggle-btn:hover {
            background: #e2e8f0;
        }

        .a11y-toggle-btn.active {
            background: #1e3a8a14;
            border-color: #1e3a8a;
            color: #1e3a8a;
        }

        .a11y-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 20px;
            border-radius: 99px;
            background: #cbd5e1;
            transition: background 0.25s;
            flex-shrink: 0;
            position: relative;
        }

        .a11y-pill::after {
            content: '';
            position: absolute;
            left: 3px;
            width: 14px;
            height: 14px;
            background: white;
            border-radius: 50%;
            transition: transform 0.25s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .a11y-toggle-btn.active .a11y-pill {
            background: #1e3a8a;
        }

        .a11y-toggle-btn.active .a11y-pill::after {
            transform: translateX(16px);
        }
    </style>
    <?php if ($isLoggedBib): ?>
        <!-- Accessibility Button -->
        <div class="fixed z-[54]" style="bottom: 108px; right: 32px;">
            <button id="a11y-fab" title="Opciones de Accesibilidad"
                class="w-12 h-12 rounded-full bg-white border-2 border-slate-200 shadow-lg flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:border-blue-400 hover:text-blue-700 transition-all duration-200 hover:scale-110 active:scale-95"
                aria-label="Abrir panel de accesibilidad">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="7" r="2" />
                    <path d="M12 22V13M9 9l-3 3M15 9l3 3M9 13l-3 3M15 13l3 3" />
                </svg>
            </button>
            <!-- Panel -->
            <div id="a11y-panel"
                class="a11y-hidden absolute right-0 bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 w-64"
                style="bottom: 56px;" role="dialog" aria-label="Panel de accesibilidad">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3 px-1">Accesibilidad</p>
                <!-- Toggle 1: Alto contraste -->
                <button id="btn-contrast" class="a11y-toggle-btn mb-2" aria-pressed="false">
                    <span>🌑 Alto Contraste</span>
                    <span class="a11y-pill"></span>
                </button>
                <!-- Toggle 2: Texto grande -->
                <button id="btn-large" class="a11y-toggle-btn mb-2" aria-pressed="false">
                    <span>🔡 Texto Grande</span>
                    <span class="a11y-pill"></span>
                </button>
                <?php if ($showCiataBtn): ?>
                    <!-- Toggle 3: Leer respuestas IA -->
                    <button id="btn-tts" class="a11y-toggle-btn" aria-pressed="false">
                        <span>🔊 CIATA por Voz</span>
                        <span class="a11y-pill"></span>
                    </button>
                <?php endif; ?>
                <p class="text-[10px] text-slate-400 mt-3 px-1 leading-relaxed">
                    Las preferencias son independientes y se desactivan al recargar la página.
                </p>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($isLoggedBib): ?>
        <script>
                    // ============================
                    // ACCESSIBILITY PANEL + CIATA POR VOZ
                    // ============================
                    (function () {
                        const fab = document.getElementById('a11y-fab');
                        const panel = document.getElementById('a11y-panel');
                        const btnContrast = document.getElementById('btn-contrast');
                        const btnLarge = document.getElementById('btn-large');
                        const btnTts = document.getElementById('btn-tts');
                        const html = document.documentElement;
                        // Mic buttons
                        const landingMicBtn = document.getElementById('landing-mic-btn');
                        const chatMicBtn = document.getElementById('chat-mic-btn');
                        const micStatusLabel = document.getElementById('mic-status-label');
                        let panelOpen = false;
                        let voiceModeEnabled = false;
                        let isListening = false;
                        let recognition = null;
                        // ---- Panel open/close ----
                        fab.addEventListener('click', (e) => {
                            e.stopPropagation();
                            panelOpen = !panelOpen;
                            panel.classList.toggle('a11y-hidden', !panelOpen);
                        });
                        document.addEventListener('click', (e) => {
                            if (!panel.contains(e.target) && e.target !== fab) {
                                panelOpen = false;
                                panel.classList.add('a11y-hidden');
                            }
                        });
                        // ---- High contrast ----
                        btnContrast.addEventListener('click', () => {
                            const on = html.classList.toggle('a11y-contrast');
                            btnContrast.classList.toggle('active', on);
                            btnContrast.setAttribute('aria-pressed', on);
                        });
                        // ---- Large text ----
                        btnLarge.addEventListener('click', () => {
                            const on = html.classList.toggle('a11y-large');
                            btnLarge.classList.toggle('active', on);
                            btnLarge.setAttribute('aria-pressed', on);
                        });
                <?php if ($showCiataBtn): ?>
                                // ---- TTS helper: speak text ----
                                function speak(text) {
                                    if (!voiceModeEnabled || !text) return;
                                    window.speechSynthesis.cancel();
                                    const utt = new SpeechSynthesisUtterance(text);
                                    utt.lang = 'es-MX';
                                    utt.rate = 0.95;
                                    utt.pitch = 1;
                                    window.speechSynthesis.speak(utt);
                                }
                    // ---- Auto-read bot responses via MutationObserver ----
                    const chatMessages = document.getElementById('chat-messages');
                            if (chatMessages) {
                                const observer = new MutationObserver((mutations) => {
                                    if (!voiceModeEnabled) return;
                                    mutations.forEach(m => {
                                        m.addedNodes.forEach(node => {
                                            if (node.classList && node.classList.contains('message') && node.classList.contains('bot')) {
                                                // Wait a tick so innerHTML is fully set
                                                setTimeout(() => {
                                                    const text = node.innerText || node.textContent || '';
                                                    speak(text);
                                                }, 100);
                                            }
                                        });
                                    });
                                });
                                observer.observe(chatMessages, { childList: true });
                            }
                            // ---- Speech Recognition setup ----
                            const SpeechRecognitionAPI = window.SpeechRecognition || window.webkitSpeechRecognition;
                            function setMicUI(listening) {
                                isListening = listening;
                                [landingMicBtn, chatMicBtn].forEach(btn => {
                                    if (!btn) return;
                                    btn.classList.toggle('bg-red-500', listening);
                                    btn.classList.toggle('bg-purple-600\/80', !listening);
                                    btn.classList.toggle('hover:bg-red-600', listening);
                                    btn.classList.toggle('hover:bg-purple-500', !listening);
                                });
                                if (micStatusLabel) {
                                    micStatusLabel.textContent = listening ? '🔴 Escuchando...' : 'Toca para hablar';
                                }
                            }
                            function createRecognition(targetInput, onFinalResult) {
                                if (!SpeechRecognitionAPI) {
                                    alert('Tu navegador no soporta reconocimiento de voz. Usa Google Chrome o Microsoft Edge.');
                                    return null;
                                }
                                const rec = new SpeechRecognitionAPI();
                                rec.lang = 'es-MX';
                                rec.continuous = false;
                                rec.interimResults = false;
                                rec.onstart = () => setMicUI(true);
                                rec.onresult = (e) => {
                                    const transcript = e.results[0][0].transcript.trim();
                                    if (targetInput) targetInput.value = transcript;
                                    if (transcript) onFinalResult(transcript);
                                };
                                rec.onerror = (e) => {
                                    console.warn('Voz error:', e.error);
                                    setMicUI(false);
                                    if (e.error === 'not-allowed') {
                                        alert('Permiso de micrófono denegado. Habilítalo en la configuración del navegador.');
                                    }
                                };
                                rec.onend = () => setMicUI(false);
                                return rec;
                            }
                            function startListening(inputEl, searchFn) {
                                if (!voiceModeEnabled) return;
                                if (isListening) {
                                    if (recognition) recognition.stop();
                                    return;
                                }
                                // Reference the current inputs (they may not exist when voice is toggled)
                                const lInput = document.getElementById('landingInput');
                                const cInput = document.getElementById('chatInput');
                                const activeInput = inputEl || (document.getElementById('ciata-chat').classList.contains('hidden') ? lInput : cInput);
                                const activeFn = searchFn || ((txt) => {
                                    // Simulate sending via the active input
                                    if (activeInput) {
                                        activeInput.value = txt;
                                        // Trigger the right send function
                                        if (typeof handleCiataSearch === 'function') handleCiataSearch(txt);
                                    }
                                });
                                recognition = createRecognition(activeInput, activeFn);
                                if (recognition) recognition.start();
                            }
                            // Wire mic buttons
                            if (landingMicBtn) {
                                landingMicBtn.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    startListening(
                                        document.getElementById('landingInput'),
                                        (txt) => { if (typeof handleCiataSearch === 'function') handleCiataSearch(txt); }
                                    );
                                });
                            }
                            if (chatMicBtn) {
                                chatMicBtn.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    startListening(
                                        document.getElementById('chatInput'),
                                        (txt) => { if (typeof handleCiataSearch === 'function') handleCiataSearch(txt); }
                                    );
                                });
                            }
                            // ---- CIATA por Voz toggle ----
                            if (btnTts) {
                                btnTts.addEventListener('click', () => {
                                    voiceModeEnabled = !voiceModeEnabled;
                                    btnTts.classList.toggle('active', voiceModeEnabled);
                                    btnTts.setAttribute('aria-pressed', voiceModeEnabled);
                                    // Show/hide mic buttons
                                    [landingMicBtn, chatMicBtn].forEach(btn => {
                                        if (btn) btn.classList.toggle('hidden', !voiceModeEnabled);
                                    });
                                    if (!voiceModeEnabled) {
                                        window.speechSynthesis.cancel();
                                        if (recognition) { recognition.stop(); recognition = null; }
                                        setMicUI(false);
                                    }
                                    // Re-init lucide icons for the new mic buttons
                                    if (typeof lucide !== 'undefined') lucide.createIcons();
                                });
                            }
                <?php endif; ?>
            })();
        </script>
    <?php endif; ?>
    <script>
                // ============================
                // VISOR DE DOCUMENTOS PROTEGIDO (PDF.js)
                // ============================
                async function openDocumentViewer(url, title = "Documento") {
                    const modal = document.getElementById('documentViewerModal');
                    const container = document.getElementById('pdf-render-container');
                    const loader = document.getElementById('pdf-loader');
                    const scrollContainer = document.getElementById('modal-scroll-container');
                    const viewerTitle = document.getElementById('viewerTitle');

                    if (viewerTitle) viewerTitle.textContent = title;

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                    scrollContainer.scrollTop = 0;
                    // Limpiar contenido previo excepto el loader
                    Array.from(container.children).forEach(child => {
                        if (child.id !== 'pdf-loader') child.remove();
                    });
                    loader.style.display = 'flex';
                    try {
                        const loadingTask = pdfjsLib.getDocument(url);
                        const pdf = await loadingTask.promise;

                        loader.style.display = 'none';
                        // Renderizar todas las páginas
                        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                            const page = await pdf.getPage(pageNum);
                            const viewport = page.getViewport({ scale: 1.5 });

                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            canvas.className = 'max-w-full shadow-2xl border border-slate-200 bg-white mb-8 rounded-sm';

                            container.appendChild(canvas);

                            await page.render({
                                canvasContext: context,
                                viewport: viewport
                            }).promise;
                        }
                    } catch (error) {
                        console.error('Error al cargar PDF:', error);
                        loader.innerHTML = `
                    <div class="bg-red-50 p-6 rounded-2xl border border-red-100 flex flex-col items-center">
                        <i data-lucide="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
                        <p class="text-sm font-bold text-red-600 uppercase tracking-wider">Error de acceso</p>
                        <p class="text-xs text-red-400 mt-1">No se pudo cargar el documento original</p>
                    </div>
                `;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                }
                function closeDocumentViewer() {
                    const modal = document.getElementById('documentViewerModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                }
    </script>
    <!-- ============================================== -->
    <!-- SECURE DOCUMENT VIEWER MODAL (PDF.js)        -->
    <!-- ============================================== -->
    <div id="documentViewerModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 md:p-6 lg:p-10">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="closeDocumentViewer()"></div>

        <!-- Modal Content -->
        <div
            class="bg-white w-full max-w-6xl h-full rounded-2xl shadow-2xl overflow-hidden relative flex flex-col animate-in fade-in zoom-in duration-300">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-10 h-10 bg-blue-900 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-900/20">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 id="viewerTitle" class="font-bold text-slate-900 text-sm md:text-base line-clamp-1">
                            Documento</h3>
                        <p
                            class="text-[10px] text-slate-400 uppercase font-bold tracking-widest flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3 h-3 text-green-500"></i> Vista Protegida CIEEPE
                        </p>
                    </div>
                </div>

                <button onclick="closeDocumentViewer()"
                    class="p-2 hover:bg-slate-100 rounded-full transition-colors group">
                    <i data-lucide="x" class="w-6 h-6 text-slate-400 group-hover:text-slate-600"></i>
                </button>
            </div>

            <!-- Viewer Body -->
            <div class="flex-grow bg-slate-100 relative overflow-y-auto custom-scrollbar" id="modal-scroll-container">
                <div id="pdf-render-container" class="flex flex-col items-center p-6 md:p-10"
                    oncontextmenu="return false;">
                    <!-- PDF pages will spend here -->
                    <div id="pdf-loader" class="flex flex-col items-center justify-center py-32 text-blue-900">
                        <div class="animate-spin rounded-full h-14 w-14 border-b-2 border-blue-900 mb-6"></div>
                        <p class="text-sm font-bold uppercase tracking-widest text-slate-500">Procesando Documento...
                        </p>
                        <p class="text-xs text-slate-400 mt-2">Preparando entorno de lectura segura</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-8 right-8 z-[200] flex flex-col gap-3"></div>

    <style>
        .toast-notification {
            animation: slideIn 0.3s ease-out forwards;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `toast-notification bg-white border-l-4 ${type === 'success' ? 'border-emerald-500' : 'border-blue-900'} p-4 rounded-xl shadow-xl flex items-center gap-3 min-w-[300px] transition-all duration-300`;
            
            toast.innerHTML = `
                <div class="w-8 h-8 rounded-full ${type === 'success' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-900'} flex items-center justify-center shrink-0">
                    <i data-lucide="${type === 'success' ? 'check-circle' : 'info'}" class="w-5 h-5"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-xs font-bold text-slate-900 uppercase tracking-wider">${type === 'success' ? 'Éxito' : 'Información'}</p>
                    <p class="text-[11px] text-slate-500 font-medium">${message}</p>
                </div>
            `;
            
            container.appendChild(toast);
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function copyCitation(book) {
            let authorText = book.author || "Anónimo";
            let formattedAuthor = authorText;
            
            // Intentar formatear Autor de "Nombre Apellido" a "Apellido, N."
            const parts = authorText.trim().split(/\s+/);
            if (parts.length > 1) {
                const lastName = parts[parts.length - 1];
                const firstName = parts[0];
                formattedAuthor = `${lastName}, ${firstName.charAt(0)}.`;
            }

            const year = book.year || new Date().getFullYear();
            const title = book.title;
            let citation = "";

            if (book.category === 'articulo') {
                // APA 7 Artículo: Apellido, N. (Año). Título. Revista. DOI
                citation = `${formattedAuthor} (${year}). ${title}. `;
                if (book.revista) {
                    citation += `${book.revista}. `;
                }
                if (book.doi) {
                    const cleanDoi = book.doi.startsWith('http') ? book.doi : `https://doi.org/${book.doi}`;
                    citation += `${cleanDoi}`;
                }
            } else if (book.category === 'tesis') {
                // APA 7 Tesis: Apellido, N. (Año). Título [Tesis de Grado, Institución].
                citation = `${formattedAuthor} (${year}). ${title} [Tesis de ${book.grado || 'Grado'}, ${book.institucion || 'CIEEPE'}].`;
            } else {
                // APA 7 Genérico/Acervo: Apellido, N. (Año). Título. Institución.
                citation = `${formattedAuthor} (${year}). ${title}. ${book.institucion || 'CIEEPE'}.`;
            }

            // Copiar al portapapeles
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(citation).then(() => {
                    showToast("Cita científica (APA 7) copiada al portapapeles.");
                }).catch(err => {
                    console.error('Error al copiar:', err);
                    fallbackCopy(citation);
                });
            } else {
                fallbackCopy(citation);
            }
        }

        function fallbackCopy(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showToast("Cita científica (APA 7) copiada.");
            } catch (err) {
                console.error('Fallback error:', err);
                alert("La cita científica es:\n\n" + text);
            }
            document.body.removeChild(textArea);
        }
    </script>
</body>

</html>