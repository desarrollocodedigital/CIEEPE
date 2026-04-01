<?php
require_once 'conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: proyectos.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ?");
$stmt->execute([$id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    header("Location: proyectos.php");
    exit;
}

// Objetivos específicos
$stmt_obj = $pdo->prepare("SELECT * FROM proyecto_objetivos WHERE proyecto_id = ? ORDER BY orden ASC");
$stmt_obj->execute([$id]);
$objetivos = $stmt_obj->fetchAll(PDO::FETCH_ASSOC);

// Convertir arrays de nombres
$internos = array_filter(array_map('trim', explode(',', $pro['internos'] ?? '')));
$externos = array_filter(array_map('trim', explode(',', $pro['externos'] ?? '')));

// --- VINCULACIÓN DE INVESTIGADORES ---
// Recolectar todos los nombres para una sola consulta
$nombres_equipo = array_unique(array_merge(
    [$pro['responsable']],
    $internos,
    $externos
));
$nombres_equipo = array_filter($nombres_equipo);

$investigadores_data = [];
if (!empty($nombres_equipo)) {
    $placeholders = implode(',', array_fill(0, count($nombres_equipo), '?'));
    $stmt_inv = $pdo->prepare("SELECT id, nombre, imagen_perfil FROM investigadores WHERE nombre IN ($placeholders)");
    $stmt_inv->execute(array_values($nombres_equipo));
    while ($row = $stmt_inv->fetch(PDO::FETCH_ASSOC)) {
        $investigadores_data[$row['nombre']] = $row;
    }
}

// Función auxiliar para renderizar enlace o nombre
function renderInvestigadorLink($nombre, $data) {
    if (isset($data[$nombre])) {
        return '<a href="perfil_generico.php?id=' . $data[$nombre]['id'] . '" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors hover:underline">' . htmlspecialchars($nombre) . '</a>';
    }
    return htmlspecialchars($nombre);
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    </script>
    <title><?= htmlspecialchars($pro['titulo']) ?> | CIEEPE</title>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .tooltip { position: relative; }
        .tooltip .tooltip-text {
            visibility: hidden; background-color: #374151; color: #fff; text-align: center;
            border-radius: 6px; padding: 8px 12px; position: absolute; z-index: 1;
            bottom: 125%; left: 50%; transform: translateX(-50%); white-space: nowrap;
            font-size: 12px; opacity: 0; transition: opacity 0.3s;
        }
        .tooltip .tooltip-text::after {
            content: ""; position: absolute; top: 100%; left: 50%; margin-left: -5px;
            border-width: 5px; border-style: solid; border-color: #374151 transparent transparent transparent;
        }
        .tooltip:hover .tooltip-text { visibility: visible; opacity: 1; }
    </style>
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
                <a href="proyectos.php" class="nav-link text-sm font-medium text-blue-600">Proyectos</a>
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
                    <a href="index.html#investigacion" class="flex items-center gap-4 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-900 transition-all group">
                        <i data-lucide="microscope" class="w-5 h-5 opacity-60 group-hover:opacity-100"></i>
                        <span class="font-semibold">Líneas de Investigación</span>
                    </a>
                    <a href="proyectos.php" class="flex items-center gap-4 px-4 py-3 rounded-xl bg-blue-50 text-blue-900 transition-all group">
                        <i data-lucide="lightbulb" class="w-5 h-5 text-blue-600"></i>
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

    <!-- Hero del Proyecto -->
    <header class="relative h-[60vh] min-h-[500px] w-full overflow-hidden flex items-center justify-center">
        <div class="absolute inset-0 z-0">
            <img src="<?= htmlspecialchars($pro['imagen_portada'] ?: './img/placeholder.jpg') ?>" alt="Portada Proyecto" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 to-blue-900/40"></div>
        </div>

        <div class="container mx-auto px-4 md:px-8 relative z-10 text-white pt-20">
            <a href="proyectos.php" class="inline-flex items-center text-blue-100 hover:text-white mb-8 transition-colors font-medium group">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform"></i> Volver a Proyectos
            </a>
            <div class="max-w-4xl">
                <div class="inline-flex items-center space-x-2 bg-blue-800/50 backdrop-blur-sm border border-blue-400/30 px-3 py-1 rounded-full text-xs uppercase tracking-wider font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full <?= $pro['estado'] == 'En Puerta' ? 'bg-amber-400 animate-pulse' : ($pro['estado'] == 'Terminados' ? 'bg-red-400' : 'bg-green-400') ?>"></span>
                    <span><?= htmlspecialchars($pro['estado']) ?></span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                    <?= htmlspecialchars($pro['titulo']) ?>
                </h1>
                <p class="text-lg md:text-xl text-blue-100 max-w-2xl leading-relaxed">
                    <?= htmlspecialchars($pro['descripcion_corta']) ?>
                </p>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="py-16 md:py-24">
        <div class="container mx-auto px-4 md:px-8">
            <div class="flex flex-col lg:flex-row gap-12">

                <!-- Columna Izquierda: Información Detallada -->
                <div class="lg:w-2/3 space-y-12">

                    <!-- Descripción -->
                    <section class="prose prose-lg text-gray-600 max-w-none">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Descripción del Proyecto</h3>
                        <?php if(!empty($pro['descripcion_larga'])): ?>
                            <p class="text-justify leading-relaxed">
                                <?= nl2br(htmlspecialchars($pro['descripcion_larga'])) ?>
                            </p>
                        <?php else: ?>
                            <p class="text-gray-400 italic">No hay descripción detallada disponible para este proyecto.</p>
                        <?php endif; ?>
                    </section>

                    <!-- Objetivos -->
                    <?php if(!empty($objetivos)): ?>
                    <section class="mb-12">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Objetivos Específicos</h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <?php foreach($objetivos as $obj): ?>
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-100 transition-all group">
                                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                    <i data-lucide="<?= htmlspecialchars($obj['icono'] ?: 'check-circle') ?>" class="w-6 h-6"></i>
                                </div>
                                <h4 class="text-lg font-bold text-gray-900 mb-2 leading-tight group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($obj['titulo']) ?></h4>
                                <p class="text-gray-600 text-sm leading-relaxed"><?= nl2br(htmlspecialchars($obj['descripcion'])) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Sección de Colaboradores -->
                    <section id="colaboradores" class="bg-blue-50/50 rounded-2xl p-8 border border-blue-100">
                        <div class="mb-8">
                            <h3 class="text-2xl font-bold text-gray-900">Equipo de Investigación</h3>
                            <p class="text-gray-500 mt-2">Investigadores que participan en este proyecto.</p>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6 items-start">
                            <?php if(!empty($pro['responsable'])): ?>
                            <!-- Responsable -->
                            <div class="bg-white rounded-xl p-4 shadow-sm border border-blue-200">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-blue-100 flex-shrink-0 shadow-sm">
                                        <?php 
                                        $res_name = $pro['responsable'];
                                        if (isset($investigadores_data[$res_name]) && !empty($investigadores_data[$res_name]['imagen_perfil'])): ?>
                                            <img src="<?= htmlspecialchars($investigadores_data[$res_name]['imagen_perfil']) ?>" alt="Foto" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-blue-50 flex items-center justify-center">
                                                <i data-lucide="user" class="w-8 h-8 text-blue-600"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide">Responsable</p>
                                        <h4 class="font-bold text-lg text-gray-900 line-clamp-1">
                                            <?= renderInvestigadorLink($res_name, $investigadores_data) ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if(count($internos) > 0): ?>
                            <!-- Colaboradores Internos -->
                            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-3">Colaboradores Internos</p>
                                <ul class="space-y-3">
                                    <?php foreach($internos as $inv): ?>
                                    <li class="flex items-center space-x-3 group">
                                        <div class="w-7 h-7 rounded-full overflow-hidden border border-gray-100 flex-shrink-0 bg-gray-50">
                                            <?php if (isset($investigadores_data[$inv]) && !empty($investigadores_data[$inv]['imagen_perfil'])): ?>
                                                <img src="<?= htmlspecialchars($investigadores_data[$inv]['imagen_perfil']) ?>" alt="Foto" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm text-gray-700 font-medium group-hover:text-blue-600 transition-colors">
                                            <?= renderInvestigadorLink($inv, $investigadores_data) ?>
                                        </span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <?php if(count($externos) > 0): ?>
                            <!-- Colaboradores Externos -->
                            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 md:col-span-2">
                                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-3">Colaboradores Externos</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6">
                                    <?php foreach($externos as $inv): ?>
                                    <div class="flex items-center space-x-3 group">
                                        <div class="w-7 h-7 rounded-full overflow-hidden border border-gray-100 flex-shrink-0 bg-gray-50">
                                            <?php if (isset($investigadores_data[$inv]) && !empty($investigadores_data[$inv]['imagen_perfil'])): ?>
                                                <img src="<?= htmlspecialchars($investigadores_data[$inv]['imagen_perfil']) ?>" alt="Foto" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm text-gray-700 font-medium group-hover:text-blue-600 transition-colors">
                                            <?= renderInvestigadorLink($inv, $investigadores_data) ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <!-- Columna Derecha: Sidebar Sticky -->
                <div class="lg:w-1/3">
                    <div class="sticky top-24 space-y-6">

                        <!-- Info Card -->
                        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-blue-600">
                            <h4 class="font-bold text-gray-900 mb-4 text-lg">Detalles Técnicos</h4>
                            <ul class="space-y-4">
                                <li class="flex items-start justify-between border-b border-gray-50 pb-2">
                                    <span class="text-sm text-gray-500">Línea:</span>
                                    <span class="text-sm font-semibold text-gray-900 text-right"><?= htmlspecialchars($pro['categoria']) ?></span>
                                </li>
                                <li class="flex items-start justify-between border-b border-gray-50 pb-2">
                                    <span class="text-sm text-gray-500">Año de Inicio:</span>
                                    <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($pro['anio_inicio'] ?? '--') ?></span>
                                </li>
                                <li class="flex items-start justify-between border-b border-gray-50 pb-2">
                                    <span class="text-sm text-gray-500">Duración:</span>
                                    <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($pro['duracion'] ?: '--') ?></span>
                                </li>
                                <li class="flex items-start justify-between border-b border-gray-50 pb-2">
                                    <span class="text-sm text-gray-500">Financiamiento:</span>
                                    <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($pro['financiamiento'] ?: '--') ?></span>
                                </li>
                                <li class="flex items-start justify-between border-b border-gray-50 pb-2">
                                    <span class="text-sm text-gray-500">Estado:</span>
                                    <span class="text-sm font-semibold <?= $pro['estado'] == 'En Puerta' ? 'text-amber-600' : ($pro['estado'] == 'Terminados' ? 'text-red-600' : 'text-green-600') ?>"><?= htmlspecialchars($pro['estado']) ?></span>
                                </li>
                                <li class="flex items-start justify-between pb-2">
                                    <span class="text-sm text-gray-500">Área:</span>
                                    <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($pro['area'] ?: '--') ?></span>
                                </li>
                            </ul>
                            
                             <div class="mt-8">
                                <?php if(!empty($pro['pdf_protocolo'])): ?>
                                    <button onclick="openProtocolModal('<?= htmlspecialchars($pro['pdf_protocolo']) ?>')"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg flex items-center justify-center transition-all shadow-md group">
                                        <i data-lucide="eye" class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform"></i> Visualizar Protocolo
                                    </button>
                                <?php else: ?>
                                    <div class="tooltip w-full">
                                        <button disabled class="w-full bg-gray-100 text-gray-400 font-bold py-3 rounded-lg cursor-not-allowed flex items-center justify-center border border-gray-200">
                                            <i data-lucide="eye-off" class="w-4 h-4 mr-2"></i> Visualizar Protocolo
                                        </button>
                                        <span class="tooltip-text">Sin protocolo para visualizar</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Banner de Ayuda -->
                        <div class="bg-gradient-to-br from-gray-900 to-blue-900 rounded-xl p-6 text-white shadow-lg relative overflow-hidden">
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                            <h4 class="font-bold text-lg mb-2 relative z-10">¿Interesado en colaborar?</h4>
                            <p class="text-blue-200 text-sm mb-4 relative z-10">Conoce más sobre este y otros proyectos contactándonos directamente.</p>
                            <a href="index.html#contacto" class="text-white font-semibold text-sm underline hover:text-blue-300 relative z-10">Contáctanos ahora</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer Section -->
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
    
    <!-- Modal de Visualización de Protocolo -->
    <div id="protocol-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-sm" onclick="closeProtocolModal()"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white w-full max-w-5xl h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in duration-300">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-blue-50/50">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white mr-4 shadow-sm">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Visor de Protocolo de Investigación</h3>
                        <p class="text-xs text-gray-500">Documento propiedad de CIEEPE - Solo lectura</p>
                    </div>
                </div>
                <button onclick="closeProtocolModal()" class="p-2 hover:bg-white rounded-full transition-colors group">
                    <i data-lucide="x" class="w-6 h-6 text-gray-400 group-hover:text-red-500"></i>
                </button>
            </div>
            
            <!-- Viewer Body -->
            <div class="flex-grow bg-gray-50 relative overflow-y-auto custom-scroll" id="modal-scroll-container">
                <div id="pdf-render-container" class="flex flex-col items-center p-4 space-y-4 shadow-inner" oncontextmenu="return false;">
                    <!-- Los cancases del PDF se insertarán aquí -->
                    <div id="pdf-loader" class="flex flex-col items-center justify-center py-20 text-blue-600">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                        <p class="text-sm font-medium text-gray-500">Cargando protocolo...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>

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

        // Lógica del Modal de Protocolo con PDF.js
        async function openProtocolModal(url) {
            const modal = document.getElementById('protocol-modal');
            const container = document.getElementById('pdf-render-container');
            const loader = document.getElementById('pdf-loader');
            const scrollContainer = document.getElementById('modal-scroll-container');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden'; 
            scrollContainer.scrollTop = 0;

            // Limpiar contenido previo excepto el loader
            Array.from(container.children).forEach(child => {
                if(child.id !== 'pdf-loader') child.remove();
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
                    canvas.className = 'max-w-full shadow-lg border border-gray-200 bg-white';
                    
                    container.appendChild(canvas);
                    
                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;
                }
            } catch (error) {
                console.error('Error al cargar PDF:', error);
                loader.innerHTML = `
                    <i data-lucide="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
                    <p class="text-sm font-medium text-red-500">Error al cargar el documento</p>
                `;
                lucide.createIcons();
            }
        }

        function closeProtocolModal() {
            const modal = document.getElementById('protocol-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = ''; 
        }

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeProtocolModal();
        });
    </script>
</body>
</html>
