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
                <img src="./img/logo.png" alt="CIEEPE ENEES Logo" class="h-12 w-auto md:h-14">
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
            <button id="mobile-menu-btn" class="md:hidden focus:outline-none text-gray-700">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white shadow-lg absolute top-full left-0 w-full py-4 px-6 flex flex-col space-y-4 text-gray-800">
            <a href="index.html#inicio" class="font-medium">Inicio</a>
            <a href="index.html#nosotros" class="font-medium">Nosotros</a>
            <a href="index.html#investigacion" class="font-medium">Líneas</a>
            <a href="proyectos.php" class="font-medium text-blue-600">Proyectos</a>
            <a href="noticias.php" class="font-medium">Noticias</a>
            <a href="investigadores.php" class="font-medium">Equipo</a>
        </div>
    </nav>

    <!-- Hero del Proyecto -->
    <header class="relative h-[60vh] min-h-[500px] w-full overflow-hidden flex items-center justify-center">
        <div class="absolute inset-0 z-0">
            <img src="<?= htmlspecialchars($pro['imagen_portada'] ?: './img/placeholder.jpg') ?>" alt="Portada Proyecto" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 to-blue-900/40"></div>
        </div>

        <div class="container mx-auto px-4 md:px-8 relative z-10 text-white pt-20">
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

                        <div class="grid md:grid-cols-2 gap-6">
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
                            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 md:col-span-<?= empty($pro['responsable']) ? '2' : '1' ?>">
                                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-3">Colaboradores Internos</p>
                                <ul class="space-y-2 text-sm text-gray-700">
                                    <?php foreach($internos as $inv): ?>
                                    <li class="flex items-center space-x-2">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500 flex-shrink-0"></i>
                                        <span class="text-gray-700"><?= renderInvestigadorLink($inv, $investigadores_data) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <?php if(count($externos) > 0): ?>
                            <!-- Colaboradores Externos -->
                            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 md:col-span-2">
                                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-3">Colaboradores Externos</p>
                                <ul class="space-y-2 text-sm text-gray-700">
                                    <?php foreach($externos as $inv): ?>
                                    <li class="flex items-center space-x-2">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-teal-500 flex-shrink-0"></i>
                                        <span class="text-gray-700"><?= renderInvestigadorLink($inv, $investigadores_data) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
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
                        <div class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors cursor-pointer">
                            <i data-lucide="globe" class="w-5 h-5"></i>
                        </div>
                        <div class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors cursor-pointer">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-gray-200">Enlaces Rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="index.html#inicio" class="text-gray-400 hover:text-white transition-colors">Inicio</a></li>
                        <li><a href="index.html#nosotros" class="text-gray-400 hover:text-white transition-colors">Nosotros</a></li>
                        <li><a href="index.html#investigacion" class="text-gray-400 hover:text-white transition-colors">Líneas de Investigación</a></li>
                        <li><a href="noticias.php" class="text-gray-400 hover:text-white transition-colors">Noticias</a></li>
                        <li><a href="biblioteca.php" class="text-blue-400 hover:text-white transition-colors font-medium">Biblioteca Virtual (IA)</a></li>
                        <li><a href="investigadores.php" class="text-gray-400 hover:text-white transition-colors">Equipo</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-gray-200">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Aviso de Privacidad</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Transparencia</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">ENEES Oficial</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-500 text-sm">
                <p>&copy; <?= date('Y') ?> CIEEPE - Escuela Normal de Especialización del Estado de Sinaloa. Todos los derechos reservados.</p>
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
        
        // Menu movil simplificado
        document.getElementById('mobile-menu-btn').addEventListener('click', () => {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
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
