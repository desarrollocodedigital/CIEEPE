<?php
session_start();
if (!isset($_SESSION['user_bib_id'])) {
    header("Location: login_biblioteca.php");
    exit;
}
$modulo = 'manual';
$id_edit = null;
$activeClass = "bg-blue-800 text-white border-l-4 border-blue-400";
$inactiveClass = "text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual del Usuario | CIATA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 10px;
        }

        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        html {
            scroll-behavior: smooth;
        }

        section {
            scroll-margin-top: 80px;
        }
    </style>
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">
    <!-- Menú Lateral Izquierdo (Sidebar) -->
    <aside
        class="w-64 bg-gray-900 flex-shrink-0 flex flex-col transition-all duration-300 z-20 shadow-xl hidden md:flex">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-gray-800 bg-gray-950 gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg">
                <i data-lucide="library" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-lg tracking-wide leading-tight truncate">CIATA</h1>
                <p class="text-blue-400 text-[10px] uppercase tracking-widest font-bold truncate">Ayuda</p>
            </div>
        </div>
        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto sidebar-scroll py-6 space-y-1">
            <h3
                class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 font-bold tracking-widest">
                Portal</h3>

            <a href="admin_manual_usuario.php"
                class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $activeClass ?>">
                <i data-lucide="help-circle" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Manual de Ayuda</span>
            </a>
        </nav>
        <!-- User bottom part -->
        <div class="p-4 border-t border-gray-800 bg-gray-950">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div
                        class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs shadow-lg">
                        <?= strtoupper(substr($_SESSION['user_bib_nombre'], 0, 1)) ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white leading-none truncate w-24 border-b-transparent">
                            <?= htmlspecialchars($_SESSION['user_bib_nombre']) ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <a href="logout_biblioteca.php" title="Cerrar Sesión"
                        class="text-gray-400 hover:text-red-400 transition-colors bg-gray-900 p-2 rounded-lg">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 flex-shrink-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-500 hover:text-gray-900">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center">
                    <h2 class="text-xl font-bold text-gray-800 hidden sm:block font-extrabold tracking-tight">
                        Manual de Usuario
                    </h2>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <a href="biblioteca.php"
                    class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full hover:bg-blue-100 transition-colors">
                    <i data-lucide="library" class="w-4 h-4 mr-1.5"></i> Ver Biblioteca
                </a>
                <span class="text-sm font-medium text-gray-600 hidden sm:block">
                    <?= htmlspecialchars($_SESSION['user_bib_nombre']) ?>
                </span>
            </div>
        </header>
        <!-- Contenido principal -->
        <main class="flex-1 overflow-y-auto w-full p-6 lg:p-8 bg-slate-50 custom-scroll relative">
            <div class="max-w-7xl mx-auto flex flex-col xl:flex-row min-h-full gap-8 relative">

                <!-- ÁREA DE LECTURA -->
                <div class="flex-1 min-w-0 pb-20">

                    <!-- ENCABEZADO PRINCIPAL -->
                    <div id="inicio"
                        class="bg-white rounded-2xl shadow-xl border border-slate-200 p-12 mb-10 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-16 -mt-16"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-6">
                                <div
                                    class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                    <i data-lucide="info" class="w-6 h-6"></i>
                                </div>
                                <span class="text-emerald-600 font-bold tracking-widest text-xs uppercase">Ayuda al
                                    Lector</span>
                            </div>
                            <h1 class="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Guía del Usuario</h1>
                            <p class="text-lg text-slate-500 leading-relaxed max-w-2xl font-medium">
                                Todo lo que necesitas saber para navegar, investigar e interactuar con el acervo digital
                                del CIATA.
                            </p>
                        </div>
                    </div>
                    <!-- SECCIÓN 1: Perfil y Membresía -->
                    <section id="cuenta"
                        class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-blue-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span
                                class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección
                                1</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mi Membresía de Lector
                            </h2>
                        </div>
                        <div class="text-slate-600 space-y-6">
                            <p class="text-base leading-relaxed">Tu cuenta de biblioteca te otorga beneficios exclusivos
                                para tu formación académica e investigación pedagógica.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-blue-600 shadow-sm">
                                            <i data-lucide="user-check" class="w-5 h-5"></i></div>
                                        <h4 class="font-bold text-slate-900 text-sm">Estado de Cuenta</h4>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">Solo los usuarios con sesión
                                        iniciada pueden acceder al visor de documentos PDF y al asistente de
                                        inteligencia artificial CIATA.</p>
                                </div>
                                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 shadow-sm">
                                            <i data-lucide="shield-check" class="w-5 h-5"></i></div>
                                        <h4 class="font-bold text-slate-900 text-sm">Protección de Datos</h4>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">Tus lecturas y favoritos son
                                        privados. El sistema utiliza cifrado para proteger el acceso al material
                                        intelectual de alto valor.</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- SECCIÓN 2: Búsqueda y Navegación -->
                    <section id="busqueda"
                        class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-emerald-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span
                                class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección
                                2</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Motor de Búsqueda</h2>
                        </div>
                        <div class="text-slate-600 space-y-6">
                            <p class="text-base leading-relaxed">Localizar un documento es más sencillo que nunca
                                gracias a nuestro sistema de filtrado estructural.</p>

                            <div class="space-y-4">
                                <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-emerald-500">
                                        <i data-lucide="search" class="w-6 h-6"></i></div>
                                    <div>
                                        <h5 class="font-bold text-slate-900 text-sm">Búsqueda Inteligente</h5>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Puedes buscar por título,
                                            nombre de autor o palabras clave. El sistema filtrará los resultados
                                            conforme escribas en la barra principal.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-blue-500">
                                        <i data-lucide="filter" class="w-6 h-6"></i></div>
                                    <div>
                                        <h5 class="font-bold text-slate-900 text-sm">Pestañas de Filtrado</h5>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Utiliza los botones de
                                            categoría para navegar exclusivamente entre <strong>Tesis</strong>,
                                            <strong>Artículos</strong> de investigación o el <strong>Acervo
                                                General</strong>.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-indigo-500">
                                        <i data-lucide="layers" class="w-6 h-6"></i></div>
                                    <div>
                                        <h5 class="font-bold text-slate-900 text-sm">Paginación Dinámica</h5>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">El catálogo se organiza
                                            de 20 en 20 documentos para una carga fluida. Desplázate hacia abajo para
                                            cargar más recursos automáticamente o mediante controles manuales.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- SECCIÓN 3: Analista AI (CIATA) -->
                    <section id="ciata"
                        class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-indigo-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span
                                class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección
                                3</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Interacción con CIATA AI
                            </h2>
                        </div>
                        <div class="relative p-8 bg-slate-900 rounded-3xl overflow-hidden mb-8">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-600/20 blur-3xl"></div>
                            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                                <div
                                    class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center shadow-[0_0_20px_rgba(37,99,235,0.5)]">
                                    <i data-lucide="bot" class="w-10 h-10 text-white"></i>
                                </div>
                                <div class="text-center md:text-left text-white">
                                    <h4 class="text-xl font-bold mb-2">Asistente Artificial Inteligente</h4>
                                    <p class="text-sm text-blue-200 leading-relaxed max-w-xl">CIATA es el analista
                                        diseñado para responder dudas complejas sobre el contenido del catálogo
                                        bibliotecario.</p>
                                </div>
                            </div>
                        </div>

                        <div class="text-slate-600 space-y-6">
                            <h4
                                class="font-bold text-slate-900 text-sm italic uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                                Capacidades de la IA:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Capacidad 1 -->
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 flex items-start gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-indigo-500">
                                        <i data-lucide="database" class="w-7 h-7"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-slate-900 text-xs text-blue-700">Conocimiento
                                            Específico</h5>
                                        <p class="text-[11px] text-slate-500 mt-1">CIATA opera bajo un modelo de
                                            conocimiento cerrado; sus respuestas se basan exclusivamente en el acervo de
                                            nuestra biblioteca, garantizando precisión sin interferencias externas.</p>
                                    </div>
                                </div>
                                <!-- Capacidad 2 -->
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 flex items-start gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-emerald-500">
                                        <i data-lucide="map-pinned" class="w-7 h-7"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-slate-900 text-xs text-blue-700">Navegación Asistida
                                        </h5>
                                        <p class="text-[11px] text-slate-500 mt-1">Más allá de responder, la IA puede
                                            guiarte paso a paso hasta localizar el archivo exacto que necesitas,
                                            facilitando el acceso a recursos específicos.</p>
                                    </div>
                                </div>
                                <!-- Capacidad 3 -->
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 flex items-start gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-amber-500">
                                        <i data-lucide="shield-alert" class="w-7 h-7"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-slate-900 text-xs text-blue-700">Transparencia de
                                            Alcance</h5>
                                        <p class="text-[11px] text-slate-500 mt-1">Si consultas un tema que no se
                                            encuentra en nuestro catálogo, CIATA te lo informará honestamente, centrando
                                            su análisis solo en información verificada.</p>
                                    </div>
                                </div>
                                <!-- Capacidad 4 -->
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 flex items-start gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-purple-500">
                                        <i data-lucide="mic" class="w-7 h-7"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-slate-900 text-xs text-blue-700">Interacción por Voz
                                        </h5>
                                        <p class="text-[11px] text-slate-500 mt-1">Puedes utilizar el botón del
                                            micrófono para dictar tus consultas por voz, ideal para una interacción más
                                            rápida y accesible.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-xl">
                                <p class="text-xs font-medium text-amber-800 italic">
                                    Tip: Puedes abrir a CIATA desde el botón flotante circular ubicado permanentemente
                                    en la esquina inferior derecha de la biblioteca.
                                </p>
                            </div>
                        </div>
                    </section>
                    <!-- SECCIÓN 4: Colección Personal -->
                    <section id="coleccion"
                        class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-amber-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span
                                class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección
                                4</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mi Biblioteca Privada</h2>
                        </div>
                        <div class="text-slate-600 space-y-6">
                            <p class="text-base leading-relaxed">Mantén un registro de tus estudios y guarda recursos
                                valiosos para consultarlos después.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center">
                                            <i data-lucide="star" class="w-4 h-4 fill-yellow-500"></i></div>
                                        <div>
                                            <h5 class="font-bold text-slate-900 text-sm">Mis Favoritos</h5>
                                            <p class="text-xs text-slate-500">Documentos marcados con la estrella que
                                                deseas tener siempre a mano.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                            <i data-lucide="history" class="w-4 h-4"></i></div>
                                        <div>
                                            <h5 class="font-bold text-slate-900 text-sm">Vistos Recientemente</h5>
                                            <p class="text-xs text-slate-500">Trazabilidad automática de los últimos
                                                documentos que has abierto.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                                    <i data-lucide="glasses"
                                        class="w-12 h-12 text-blue-600 mx-auto mb-4 opacity-50"></i>
                                    <p class="text-xs text-slate-600 italic">"La lectura es para la mente lo que el
                                        ejercicio es para el cuerpo."</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- Sección de Soporte WhatsApp -->
                    <div
                        class="mt-12 p-8 bg-emerald-600 rounded-2xl text-white flex flex-col md:flex-row items-center justify-between shadow-lg shadow-emerald-200">
                        <div class="mb-6 md:mb-0 text-center md:text-left">
                            <h2 class="text-2xl font-bold mb-2">¿Necesitas ayuda con el acceso?</h2>
                            <p class="text-emerald-100 italic">Si tienes problemas al visualizar un documento o dudas
                                sobre el uso de la IA, nuestro equipo te ayudará.</p>
                        </div>
                        <a href="https://wa.me/526672644610" target="_blank"
                            class="px-8 py-4 bg-white text-emerald-600 rounded-xl font-extrabold hover:bg-gray-100 transition-all flex items-center gap-3 shadow-md">
                            <i data-lucide="message-circle" class="w-6 h-6"></i>
                            Chatear con Soporte
                        </a>
                    </div>
                </div>
                <!-- ÍNDICE FLOTANTE (DERECHA) -->
                <div class="hidden xl:block lg:w-72 flex-shrink-0">
                    <div
                        class="sticky top-10 mt-12 bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex flex-col max-h-[calc(100vh-160px)]">
                        <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 flex-shrink-0">
                            <i data-lucide="list-tree" class="w-5 h-5 text-emerald-600"></i>
                            <h3 class="font-bold text-gray-900 leading-none">Índice del Doc.</h3>
                        </div>
                        <div class="flex-1 overflow-y-auto pr-2 custom-scroll">
                            <nav class="space-y-1">
                                <a href="#inicio"
                                    class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-blue-50 border-l-2 border-transparent hover:border-blue-500 group">
                                    <span
                                        class="text-xs font-bold text-blue-600 uppercase tracking-tighter opacity-70 mb-0.5">Inicio</span>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-900">Bienvenida
                                        Lector</span>
                                </a>
                                <a href="#cuenta"
                                    class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-slate-50 border-l-2 border-transparent hover:border-slate-800 group">
                                    <span
                                        class="text-xs font-bold text-slate-500 uppercase tracking-tighter opacity-70 mb-0.5">Sección
                                        1</span>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-slate-900">Mi
                                        Membresía</span>
                                </a>
                                <a href="#busqueda"
                                    class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-emerald-50 border-l-2 border-transparent hover:border-emerald-500 group">
                                    <span
                                        class="text-xs font-bold text-emerald-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección
                                        2</span>
                                    <span
                                        class="text-sm font-medium text-gray-700 group-hover:text-emerald-900">Navegación</span>
                                </a>
                                <a href="#ciata"
                                    class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-indigo-50 border-l-2 border-transparent hover:border-indigo-500 group">
                                    <span
                                        class="text-xs font-bold text-indigo-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección
                                        3</span>
                                    <span
                                        class="text-sm font-medium text-gray-700 group-hover:text-indigo-900">Interacción
                                        AI</span>
                                </a>
                                <a href="#coleccion"
                                    class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-amber-50 border-l-2 border-transparent hover:border-amber-500 group">
                                    <span
                                        class="text-xs font-bold text-amber-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección
                                        4</span>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-amber-900">Mi
                                        Biblioteca</span>
                                </a>
                            </nav>
                        </div>
                        <!-- Soporte Widget -->
                        <div class="mt-8 pt-8 border-t border-gray-100 flex-shrink-0">
                            <div class="bg-slate-900 rounded-xl p-4 text-white hover:bg-black transition-colors">
                                <p class="text-xs font-bold text-emerald-400 uppercase mb-2 leading-none">Ayuda Directa
                                </p>
                                <p class="text-[10px] text-gray-400 mb-3 leading-relaxed">¿Dificultades técnicas o de
                                    acceso?</p>
                                <a href="https://wa.me/526672644610" target="_blank"
                                    class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 transition-colors rounded-lg text-xs font-bold flex items-center justify-center gap-2">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    Contactar Soporte
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Scrollspy Logic
        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('section[id], div[id="inicio"]');
            const navLinks = document.querySelectorAll('nav a[href^="#"]');
            
            const observerOptions = {
                rootMargin: '-10% 0px -80% 0px',
                threshold: 0
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        navLinks.forEach(link => {
                            // Limpiar estados activos
                            link.classList.remove('bg-blue-50', 'bg-slate-50', 'bg-emerald-50', 'bg-indigo-50', 'bg-amber-50');
                            link.classList.remove('border-blue-500', 'border-slate-800', 'border-emerald-500', 'border-indigo-500', 'border-amber-500');
                            link.classList.add('border-transparent');

                            if (link.getAttribute('href') === `#${id}`) {
                                link.classList.remove('border-transparent');
                                // Aplicar color según el href
                                if(id === 'inicio') link.classList.add('bg-blue-50', 'border-blue-500');
                                if(id === 'cuenta') link.classList.add('bg-slate-50', 'border-slate-800');
                                if(id === 'busqueda') link.classList.add('bg-emerald-50', 'border-emerald-500');
                                if(id === 'ciata') link.classList.add('bg-indigo-50', 'border-indigo-500');
                                if(id === 'coleccion') link.classList.add('bg-amber-50', 'border-amber-500');
                            }
                        });
                    }
                });
            }, observerOptions);

            sections.forEach(section => observer.observe(section));
        });
    </script>
</body>

</html>