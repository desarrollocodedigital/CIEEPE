<?php
session_start();
if (!isset($_SESSION['user_bib_id']) || !in_array($_SESSION['user_bib_rol'] ?? '', ['admin', 'investigador'])) {
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
    <title>Manual del Investigador | CIATA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        html { scroll-behavior: smooth; }
        section { scroll-margin-top: 80px; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden">

    <!-- Menú Lateral Izquierdo (Sidebar) -->
    <aside class="w-64 bg-gray-900 flex-shrink-0 flex flex-col transition-all duration-300 z-20 shadow-xl hidden md:flex">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-gray-800 bg-gray-950 gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg">
                <i data-lucide="microscope" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-lg tracking-wide leading-tight truncate">CIATA</h1>
                <p class="text-blue-400 text-[10px] uppercase tracking-widest font-bold truncate">Investigador</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto sidebar-scroll py-6 space-y-1">
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 font-bold tracking-widest">Principal</h3>
            
            <a href="subir_articulo.php?modulo=inicio" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Dashboard</span>
            </a>

            <a href="subir_articulo.php?modulo=nuevo" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Nuevo Documento</span>
            </a>

            <a href="subir_articulo.php?modulo=mis_documentos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="library" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Mis Documentos</span>
            </a>
        </nav>

        <!-- Sección de Manual de Usuario (Fuera del scroll) -->
        <div class="px-4 py-2 border-t border-gray-800 bg-gray-900/50">
            <a href="admin_manual_investigador.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= $activeClass ?>">
                <i data-lucide="book" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Manual de Usuario</span>
            </a>
        </div>

        <!-- User bottom part -->
        <div class="p-4 border-t border-gray-800 bg-gray-950">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs shadow-lg">
                        <?= strtoupper(substr($_SESSION['user_bib_nombre'], 0, 1)) ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white leading-none truncate w-24 border-b-transparent"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <a href="logout_biblioteca.php" title="Cerrar Sesión" class="text-gray-400 hover:text-red-400 transition-colors bg-gray-900 p-2 rounded-lg">
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
                    <h2 class="text-xl font-bold text-gray-800 hidden sm:block">
                        Manual del Investigador
                    </h2>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="biblioteca.php" class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full hover:bg-blue-100 transition-colors">
                    <i data-lucide="library" class="w-4 h-4 mr-1.5"></i> Ver Biblioteca
                </a>
                <span class="text-sm text-gray-400 hidden sm:block">|</span>
                <span class="text-sm font-medium text-gray-600 hidden sm:block"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></span>
            </div>
        </header>

        <!-- Contenido principal interactivo del manual -->
        <main class="flex-1 overflow-y-auto w-full p-6 lg:p-8 bg-slate-50 custom-scroll relative">
            <div class="max-w-7xl mx-auto flex flex-col xl:flex-row min-h-full gap-8 relative">
                
                <!-- Área de lectura -->
                <div class="flex-1 min-w-0 pb-20">
                    
                    <!-- ENCABEZADO PRINCIPAL -->
                    <div id="inicio" class="bg-white rounded-2xl shadow-xl border border-slate-200 p-12 mb-10 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-16 -mt-16"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                    <i data-lucide="microscope" class="w-6 h-6"></i>
                                </div>
                                <span class="text-emerald-600 font-bold tracking-widest text-xs uppercase">Documentación Técnica</span>
                            </div>
                            <h1 class="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Manual del Investigador</h1>
                            <p class="text-lg text-slate-500 leading-relaxed max-w-2xl font-medium">
                                Guía integral para la integración de archivos y seguimiento del acervo digital de la plataforma.
                            </p>
                        </div>
                    </div>

                    <!-- SECCIÓN 0: Introducción -->
                    <section id="introduccion" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-emerald-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Inicio</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Introducción al sistema</h2>
                        </div>
                        <div class="text-slate-600 space-y-6">
                            <p class="text-base leading-relaxed font-medium">Este sistema ha sido diseñado para centralizar el acervo investigativo y facilitar la aprobación técnica de documentos científicos, tesis y materiales generales.</p>
                            
                            <h3 class="text-lg font-bold text-slate-800 mt-8 mb-4">¿Quiénes interactúan en la plataforma?</h3>
                            <div class="overflow-hidden border border-slate-100 rounded-2xl">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-[10px] font-bold tracking-widest">
                                        <tr>
                                            <th class="px-6 py-4">Rol del Usuario</th>
                                            <th class="px-6 py-4">Facultades y Responsabilidades</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600"><i data-lucide="shield-check" class="w-4 h-4"></i></div>
                                                    <span class="font-bold text-slate-900">Administrador</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-500 italic leading-relaxed">
                                                Posee control total. Aprueba o rechaza publicaciones, gestiona el inventario global, edita cualquier registro y administra las cuentas de todos los investigadores.
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600"><i data-lucide="microscope" class="w-4 h-4"></i></div>
                                                    <span class="font-bold text-slate-900">Investigador</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-500 italic leading-relaxed">
                                                Autor de los materiales. Sube nuevas investigaciones, edita sus propios borradores y monitorea el impacto (vistas/guardados) de sus publicaciones aprobadas.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                                  <!-- SECCIÓN 1: Acceso -->
                    <section id="acceso" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-blue-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 1</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Acceso al sistema</h2>
                        </div>
                        <div class="text-slate-600 space-y-8">
                            <p class="text-sm leading-relaxed text-slate-500 max-w-2xl">Sigue estos pasos obligatorios para habilitar tus credenciales de investigador en nuestra red académica.</p>
                            
                            <!-- Path Vertical Style -->
                            <div class="relative pl-8 border-l-2 border-blue-500 ml-2">
                                <div class="absolute -left-[11px] top-0 w-5 h-5 bg-blue-500 rounded-full border-4 border-white shadow-sm"></div>
                                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2 italic uppercase text-xs tracking-widest">
                                    <i data-lucide="user-plus" class="w-4 h-4 text-blue-600"></i> Proceso de Registro
                                </h3>
                                <ol class="space-y-4 text-slate-600 text-sm list-decimal list-inside font-medium border-l-2 border-blue-100 pl-4 ml-2 mt-4">
                                    <li class="pl-2">Acceder al portal de registro: <code class="bg-slate-100 px-2 py-0.5 rounded text-blue-600 uppercase text-[10px] font-bold tracking-widest">DominioPrincipal.com/registro_biblioteca.php</code>.</li>
                                    <li class="pl-2">Proporcionar su <strong>Nombre Completo</strong>, <strong>Correo Institucional</strong> y una contraseña segura.</li>
                                    <li class="pl-2">En el selector de "Tipo de Usuario", elegir específicamente la opción: <span class="text-blue-600 font-bold tracking-tight">Investigador</span>.</li>
                                    <li class="pl-2">Describir de forma exhaustiva su <strong>Motivo de acceso</strong> institucional y su línea de investigación.</li>
                                    <li class="pl-2">Tras enviar la solicitud, el comité CIATA procederá a la <strong>validación de identidad</strong> (Ver Sección 4).</li>
                                    <li class="pl-2">Una vez aprobada la cuenta, podrá ingresar en <code class="bg-slate-100 px-2 py-0.5 rounded text-blue-600 uppercase text-[10px] font-bold tracking-widest">DominioPrincipal.com/login_biblioteca.php</code> para gestionar recursos y consultar el catálogo protegido.</li>
                                </ol>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN 2: Dashboard -->
                    <section id="dashboard" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-slate-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 2</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Dashboard</h2>
                        </div>
                        <div class="text-slate-600 space-y-6">
                            <p class="text-base leading-relaxed">El dashboard principal es tu centro de monitoreo. Desde ahí puedes visualizar en tiempo real cómo progresan tus contribuciones a la biblioteca.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-100 hover:shadow-md transition-shadow">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center"><i data-lucide="check-circle" class="w-4 h-4"></i></div>
                                        <h4 class="font-bold text-slate-900 text-sm">Documentos Publicados</h4>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">Material que ya ha sido evaluado y listado públicamente en la biblioteca para toda la audiencia.</p>
                                </div>
                                <div class="bg-amber-50 p-5 rounded-2xl border border-amber-100 hover:shadow-md transition-shadow">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center"><i data-lucide="clock" class="w-4 h-4"></i></div>
                                        <h4 class="font-bold text-slate-900 text-sm">En Revisión (Pendientes)</h4>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">Documentos que has mandado a evaluar y que un administrador revisará en el corto plazo.</p>
                                </div>
                            </div>

                            <!-- Nuevas métricas añadidas -->
                            <div class="mt-8 pt-8 border-t border-slate-50">
                                <h4 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-blue-600"></i> Métricas de Interacción Real
                                </h4>
                                <p class="text-sm text-slate-600 mb-6">Tu panel ahora incluye registros detallados sobre cómo la comunidad consume tu investigación.</p>
                                
                                <div class="space-y-4">
                                    <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-blue-500"><i data-lucide="file-clock" class="w-5 h-5"></i></div>
                                        <div>
                                            <h5 class="font-bold text-slate-900 text-sm">Documentos Recientes</h5>
                                            <p class="text-xs text-slate-500 mt-1">Acceso directo a tus últimas 5 cargas para edición rápida o monitoreo de estado.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-yellow-500"><i data-lucide="star" class="w-5 h-5 fill-yellow-500"></i></div>
                                        <div>
                                            <h5 class="font-bold text-slate-900 text-sm">Guardado Por (Favoritos)</h5>
                                            <p class="text-xs text-slate-500 mt-1">Identifica nominativamente qué lectores han guardado tus obras en su colección personal de "Mis Favoritos".</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm flex-shrink-0 text-indigo-500"><i data-lucide="eye" class="w-5 h-5"></i></div>
                                        <div>
                                            <h5 class="font-bold text-slate-900 text-sm">Vistos Por (Trazabilidad)</h5>
                                            <p class="text-xs text-slate-500 mt-1">Bitácora cronológica que te indica qué usuarios han abierto y consultado tus documentos recientemente.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECCIÓN 3: Nuevo Documento -->
                    <section id="novedades" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-indigo-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 3</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Nuevo Documento</h2>
                        </div>
                        <p class="text-slate-600 mb-6 font-medium leading-relaxed italic border-l-4 border-indigo-500 pl-4 bg-indigo-50/50 py-3 rounded-r-lg">
                            Este módulo es el corazón de la plataforma. Aquí es donde inicias la carga de tu material intelectual para ser procesado por el comité editorial.
                        </p>
                        
                        <div class="space-y-6 text-sm text-slate-600">
                            <p>Dentro de este apartado, puedes realizar las siguientes acciones críticas:</p>
                            
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 list-none pl-0">
                                <li class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-start gap-3">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                                    <span><strong>Clasificación Inteligente:</strong> Selecciona si tu documento es un artículo, tesis o acervo. El sistema cambiará los campos automáticamente.</span>
                                </li>
                                <li class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-start gap-3">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                                    <span><strong>Metadatos Académicos:</strong> Ingresa el título oficial, autores, resumen (abstract) y palabras clave para facilitar su búsqueda.</span>
                                </li>
                                <li class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-start gap-3">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                                    <span><strong>Carga Segura de PDF:</strong> Sube tu archivo final. El sistema verificará que sea un formato válido para su procesamiento.</span>
                                </li>
                                <li class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-start gap-3">
                                    <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                                    <span><strong>Previsualización de Portada:</strong> Observa cómo se verá la portada institucional generada automáticamente antes de enviar.</span>
                                </li>
                            </ul>

                            <h4 class="text-slate-900 font-bold mt-8 flex items-center gap-2">
                                <i data-lucide="layers" class="w-4 h-4 text-indigo-600"></i> Campos según tu clasificación:
                            </h4>
                            <div class="space-y-3">
                                <div class="border border-slate-100 p-4 rounded-xl hover:bg-slate-50 transition-all">
                                    <h5 class="text-sm font-bold text-slate-900 mb-1 flex items-center"><i data-lucide="file-text" class="w-4 h-4 text-blue-500 mr-2"></i> Artículo Científico</h5>
                                    <p class="text-xs text-slate-500">Solicita datos técnicos como: Nombre de la Revista, ISSN y el identificador DOI.</p>
                                </div>
                                <div class="border border-slate-100 p-4 rounded-xl hover:bg-slate-50 transition-all">
                                    <h5 class="text-sm font-bold text-slate-900 mb-1 flex items-center"><i data-lucide="graduation-cap" class="w-4 h-4 text-emerald-500 mr-2"></i> Tesis Académica</h5>
                                    <p class="text-xs text-slate-500">Requiere información institucional: Grado académico, Institución de egreso y Asesor asignado.</p>
                                </div>
                                <div class="border border-slate-100 p-4 rounded-xl hover:bg-slate-50 transition-all">
                                    <h5 class="text-sm font-bold text-slate-900 mb-1 flex items-center"><i data-lucide="library" class="w-4 h-4 text-amber-500 mr-2"></i> Acervo General</h5>
                                    <p class="text-xs text-slate-500">Permite una carga libre con categorización temática y elección de tipo de licencia de uso.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-xl">
                            <p class="text-xs font-medium text-yellow-800">
                                <strong>Nota Adicional:</strong> El archivo PDF es un requisito indispensable. Cuentas con una simulación en la vista de carga la cual modelará tu portada automáticamente basado en tu color, categoría e iniciales antes de compartirla al mundo.
                            </p>
                        </div>
                    </section>
                    
                    <!-- SECCIÓN 4: Mis Documentos -->
                    <section id="estado" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-emerald-200">
                        <div class="flex items-center gap-2 mb-8">
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 4</span>
                            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Mis documentos</h2>
                        </div>
                        <div class="text-slate-600 space-y-6">
                            <p class="text-base leading-relaxed">Este módulo es tu inventario personal de investigación. Aquí puedes gestionar, buscar y monitorear el estado de cada uno de tus archivos cargados. Además, puedes iniciar la carga de un nuevo material presionando el botón <strong class="text-emerald-600 uppercase tracking-tighter">"Añadir Nuevo"</strong> ubicado sobre la tabla de registros.</p>
                            
                            <div class="space-y-6">
                                <!-- Búsqueda en Tiempo Real -->
                                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-4 opacity-10 text-emerald-600">
                                        <i data-lucide="search" class="w-12 h-12"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-900 mb-2 flex items-center gap-2 text-sm uppercase tracking-wider">
                                        <i data-lucide="zap" class="w-4 h-4 text-emerald-500"></i> Búsqueda en Tiempo Real
                                    </h4>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        No necesitas presionar Enter. Conforme escribas el título, tipo o estado en el buscador, la tabla se filtrará automáticamente para encontrar tu recurso al instante.
                                    </p>
                                </div>

                                <!-- Paginación Profesional -->
                                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-4 opacity-10 text-blue-600">
                                        <i data-lucide="layers" class="w-12 h-12"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-900 mb-2 flex items-center gap-2 text-sm uppercase tracking-wider">
                                        <i data-lucide="list-ordered" class="w-4 h-4 text-blue-500"></i> Paginación Inteligente
                                    </h4>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        Para mantener la velocidad y el orden, tus documentos se agrupan en bloques de <b>10 registros por página</b>. Al final de la tabla encontrarás controles numerados para navegar fácilmente entre tus archivos sin saturar la vista.
                                    </p>
                                </div>

                                <!-- Gestión de Estados -->
                                <h4 class="text-slate-900 font-bold mt-8 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2 text-sm">
                                    <i data-lucide="activity" class="w-4 h-4 text-blue-500"></i> Control de Vida Útil y Estados:
                                </h4>
                                <ul class="space-y-4">
                                    <li class="flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-1"><span class="px-2 py-1 rounded bg-slate-100 text-slate-600 text-[9px] font-bold uppercase tracking-wider border border-slate-200">Borrador</span></div>
                                        <div class="text-sm text-slate-600">
                                            <b>Control Total:</b> Puedes editar el contenido y eliminar el registro si decides no publicarlo.
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-1"><span class="px-2 py-1 rounded bg-amber-100 text-amber-700 text-[9px] font-bold uppercase tracking-wider border border-amber-200">Pendiente</span></div>
                                        <div class="text-sm text-slate-600">
                                            <b>Bloqueado por Revisión:</b> El documento está siendo evaluado. No permite ediciones para asegurar la integridad de la revisión.
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-4">
                                        <div class="flex-shrink-0 mt-1"><span class="px-2 py-1 rounded bg-emerald-100 text-emerald-700 text-[9px] font-bold uppercase tracking-wider border border-emerald-200">Publicado</span></div>
                                        <div class="text-sm text-slate-600">
                                            <b>Recurso Protegido:</b> Una vez publicado, el sistema bloquea la eliminación por parte del investigador. Solo un administrador puede retirar un documento publicado para garantizar el orden del catálogo institucional.
                                        </div>
                                    </li>
                                </ul>

                                <!-- Acciones Disponibles -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8">
                                    <div class="p-4 rounded-xl border border-slate-100 bg-white">
                                        <i data-lucide="eye" class="w-5 h-5 text-emerald-500 mb-2"></i>
                                        <h5 class="font-bold text-slate-900 text-xs">Visualizar</h5>
                                        <p class="text-[10px] text-slate-500 mt-1">Abre el visor de PDF integrado para revisar tu documento sin descargarlo.</p>
                                    </div>
                                    <div class="p-4 rounded-xl border border-slate-100 bg-white">
                                        <i data-lucide="edit-3" class="w-5 h-5 text-amber-500 mb-2"></i>
                                        <h5 class="font-bold text-slate-900 text-xs">Editar</h5>
                                        <p class="text-[10px] text-slate-500 mt-1">Habilitado solo para borradores o si el administrador solicita cambios.</p>
                                    </div>
                                    <div class="p-4 rounded-xl border border-slate-100 bg-white">
                                        <i data-lucide="trash-2" class="w-5 h-5 text-red-500 mb-2"></i>
                                        <h5 class="font-bold text-slate-900 text-xs">Eliminar</h5>
                                        <p class="text-[10px] text-slate-500 mt-1">Acción irreversible. Se bloquea automáticamente si el recurso ya es público.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- Sección de Soporte WhatsApp -->
                    <div class="mt-12 p-8 bg-emerald-600 rounded-2xl text-white flex flex-col md:flex-row items-center justify-between shadow-lg shadow-emerald-200">
                        <div class="mb-6 md:mb-0 text-center md:text-left">
                            <h2 class="text-2xl font-bold mb-2">¿Necesitas ayuda con el acceso?</h2>
                            <p class="text-emerald-100 italic">Si tienes problemas al visualizar un documento o dudas sobre el uso de la IA, nuestro equipo te ayudará.</p>
                        </div>
                        <a href="https://wa.me/526672644610?text=Hola%20buenas,%20te%20hablo%20desde%20el%20sistema%20de%20CIEEPE%20por%20qu%C3%A9%20necesito%20soporte" target="_blank"
                            class="px-8 py-4 bg-white text-emerald-600 rounded-xl font-extrabold hover:bg-gray-100 transition-all flex items-center gap-3 shadow-md">
                            <i data-lucide="message-circle" class="w-6 h-6"></i>
                            Chatear con Soporte
                        </a>
                    </div>
                </div>

                <div class="hidden xl:block lg:w-72 flex-shrink-0">
                    <div class="sticky top-10 mt-12 bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex flex-col max-h-[calc(100vh-160px)]">
                        <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 flex-shrink-0">
                            <i data-lucide="list-tree" class="w-5 h-5 text-emerald-600"></i>
                            <h3 class="font-bold text-gray-900 leading-none">Índice del Doc.</h3>
                        </div>
                        <div class="flex-1 overflow-y-auto pr-2 custom-scroll">
                            <nav class="space-y-1">
                                <a href="#introduccion" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-emerald-50 border-l-2 border-transparent hover:border-emerald-500 group">
                                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-tighter opacity-70 mb-0.5">Inicio</span>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-900">Introducción al sistema</span>
                                </a>

                                <a href="#acceso" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-blue-50 border-l-2 border-transparent hover:border-blue-500 group">
                                    <span class="text-xs font-bold text-blue-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 1</span>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-900">Acceso al sistema</span>
                                </a>

                                <a href="#dashboard" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-slate-50 border-l-2 border-transparent hover:border-slate-900 group">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-tighter opacity-70 mb-0.5">Sección 2</span>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-slate-900">Dashboard</span>
                                </a>

                                <a href="#novedades" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-indigo-50 border-l-2 border-transparent hover:border-indigo-500 group">
                                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 3</span>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-900">Nuevo Documento</span>
                                </a>

                                <a href="#estado" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-emerald-50 border-l-2 border-transparent hover:border-emerald-500 group">
                                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 4</span>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-900">Mis documentos</span>
                                </a>
                            </nav>
                        </div>

                        <!-- Soporte Widget -->
                        <div class="mt-8 pt-8 border-t border-gray-100 flex-shrink-0">
                            <div class="bg-gray-900 rounded-xl p-4 text-white">
                                <p class="text-xs font-bold text-emerald-400 uppercase mb-2">Ayuda Directa</p>
                                <p class="text-[10px] text-gray-400 mb-3 leading-relaxed">¿Dudas sobre la publicación?</p>
                                <a href="https://wa.me/526672644610?text=Hola%20buenas,%20te%20hablo%20desde%20el%20sistema%20de%20CIEEPE%20por%20qu%C3%A9%20necesito%20soporte" target="_blank" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 transition-colors rounded-lg text-xs font-bold flex items-center justify-center gap-2">
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
                            // Limpiar estados activos (Investigador usa principalmente esmeralda y blue)
                            link.classList.remove('bg-emerald-50', 'bg-blue-50', 'bg-slate-50', 'bg-indigo-50');
                            link.classList.remove('border-emerald-500', 'border-blue-500', 'border-slate-900', 'border-indigo-500');
                            link.classList.add('border-transparent');

                            if (link.getAttribute('href') === `#${id}`) {
                                link.classList.remove('border-transparent');
                                // Aplicar color según el href definido en el HTML
                                if(id === 'introduccion') link.classList.add('bg-emerald-50', 'border-emerald-500');
                                if(id === 'acceso') link.classList.add('bg-blue-50', 'border-blue-500');
                                if(id === 'dashboard') link.classList.add('bg-slate-50', 'border-slate-900');
                                if(id === 'novedades') link.classList.add('bg-indigo-50', 'border-indigo-500');
                                if(id === 'estado') link.classList.add('bg-emerald-50', 'border-emerald-500');
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
