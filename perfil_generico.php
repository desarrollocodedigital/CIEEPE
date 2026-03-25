<?php
require_once 'conexion.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    die("ID de investigador no especificado.");
}

// Obtener datos del investigador
$stmt = $pdo->prepare("SELECT * FROM investigadores WHERE id = ?");
$stmt->execute([$id]);
$investigador = $stmt->fetch();

if (!$investigador) {
    die("Investigador no encontrado.");
}

// Obtener líneas de investigación
$stmt_lineas = $pdo->prepare("SELECT * FROM investigador_lineas WHERE investigador_id = ? ORDER BY orden ASC");
$stmt_lineas->execute([$id]);
$lineas = $stmt_lineas->fetchAll();

// Obtener publicaciones destacadas
$stmt_pubs = $pdo->prepare("SELECT * FROM investigador_publicaciones WHERE investigador_id = ? ORDER BY orden ASC");
$stmt_pubs->execute([$id]);
$publicaciones = $stmt_pubs->fetchAll();

// Función auxiliar para limpiar URLs
function format_social_url($url) {
    if (empty($url) || $url === '#') return '';
    $clean = ltrim($url, '#'); // Quitar # si existe al inicio
    if (!preg_match("~^(?:f|ht)tps?://~i", $clean)) {
        $clean = "https://" . $clean;
    }
    return $clean;
}

// Obtener color del badge
$color_badge = 'blue'; // por defecto
if (!empty($investigador['etiqueta_badge'])) {
    $stmt_c = $pdo->prepare("SELECT color FROM lineas_investigacion WHERE titulo = ?");
    $stmt_c->execute([$investigador['etiqueta_badge']]);
    $res_c = $stmt_c->fetch();
    if ($res_c && !empty($res_c['color'])) {
        $color_badge = $res_c['color'];
    }
}

// Obtener proyectos del investigador (Responsable o Colaborador)
$nombre_inv = $investigador['nombre'];
$sql_proyectos = "SELECT id, titulo, estado, categoria, anio_inicio, responsable, internos, externos 
                  FROM proyectos 
                  WHERE responsable = ? 
                  OR FIND_IN_SET(?, REPLACE(internos, ', ', ',')) > 0
                  OR FIND_IN_SET(?, REPLACE(externos, ', ', ',')) > 0
                  ORDER BY anio_inicio DESC";
$stmt_p = $pdo->prepare($sql_proyectos);
$stmt_p->execute([$nombre_inv, $nombre_inv, $nombre_inv]);
$proyectos = $stmt_p->fetchAll();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <title>Perfil: <?= htmlspecialchars($investigador['nombre']) ?> | CIEEPE</title>
</head>

<body class="bg-white text-gray-900 antialiased">
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
                <a href="proyectos.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Proyectos</a>
                <a href="noticias.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Noticias</a>
                <a href="investigadores.php" class="nav-link text-sm font-medium text-blue-600">Equipo</a>
                <a href="index.html#contacto" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Contacto</a>
            </div>
            <button id="mobile-menu-btn" class="md:hidden focus:outline-none text-gray-700">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white shadow-lg absolute top-full left-0 w-full py-4 px-6 flex flex-col space-y-4 text-gray-800">
            <a href="index.html#inicio" class="font-medium">Inicio</a>
            <a href="investigadores.php" class="font-medium text-blue-600">Equipo</a>
            <a href="proyectos.php" class="font-medium">Proyectos</a>
            <a href="noticias.php" class="font-medium">Noticias</a>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="pt-32 pb-20">
        <div class="container mx-auto px-4 md:px-8">

            <!-- Botón Volver -->
            <div class="mb-10">
                <a href="investigadores.php" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Volver al Directorio
                </a>
            </div>

            <div class="grid md:grid-cols-12 gap-12">

                <!-- Columna Izquierda: Foto y Contacto -->
                <div class="md:col-span-4 lg:col-span-3">
                    <div>
                        <div class="bg-gray-100 rounded-lg overflow-hidden mb-6 shadow-sm aspect-square md:aspect-auto">
                            <!-- Foto del investigador -->
                            <img src="<?= htmlspecialchars($investigador['imagen_perfil']) ?>" alt="<?= htmlspecialchars($investigador['nombre']) ?>" class="w-full h-full object-cover transition-all duration-500">
                        </div>

                        <div class="space-y-6">
                            <?php if (!empty($investigador['email'])): ?>
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Email Institucional</h4>
                                <a href="mailto:<?= htmlspecialchars($investigador['email']) ?>" class="text-sm font-medium text-gray-900 border-b border-gray-300 hover:border-blue-600 transition-colors pb-0.5"><?= htmlspecialchars($investigador['email']) ?></a>
                            </div>
                            <?php endif; ?>

                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Ubicación</h4>
                                <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($investigador['ubicacion'])) ?><br>
                                <?php if (!empty($investigador['telefono'])): ?>
                                Tel: <?= htmlspecialchars($investigador['telefono']) ?>
                                <?php endif; ?>
                                </p>
                            </div>

                            <div class="space-y-3 pt-4">
                                <?php 
                                    $link_li = format_social_url($investigador['linkedin_url']);
                                    $link_fb = format_social_url($investigador['facebook_url'] ?? '');
                                ?>
                                
                                <?php if ($link_li): ?>
                                <a href="<?= $link_li ?>" target="_blank" class="flex items-center group bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 p-2.5 rounded-lg transition-all">
                                    <div class="w-8 h-8 rounded bg-white shadow-sm flex items-center justify-center mr-3 group-hover:scale-110 transition-transform p-1.5">
                                        <img src="./img/iconos/linkedin.svg" class="w-8 h-8" alt="LinkedIn">
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-tighter">Perfil LinkedIn</span>
                                </a>
                                <?php endif; ?>

                                <?php if ($link_fb): ?>
                                <a href="<?= $link_fb ?>" target="_blank" class="flex items-center group bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-200 p-2.5 rounded-lg transition-all">
                                    <div class="w-8 h-8 rounded bg-white shadow-sm flex items-center justify-center mr-3 group-hover:scale-110 transition-transform p-1.5">
                                        <img src="./img/iconos/facebook-icon.svg" class="w-8 h-8" alt="Facebook">
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-tighter">Perfil Facebook</span>
                                </a>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($investigador['cv_url']) && $investigador['cv_url'] !== '#'): ?>
                            <div class="pt-4">
                                <a href="<?= htmlspecialchars($investigador['cv_url']) ?>" target="_blank" class="flex items-center justify-between w-full bg-black text-white px-4 py-3 rounded text-sm font-bold hover:bg-gray-800 transition-colors">
                                    <span>Descargar Currículum</span>
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Información -->
                <div class="md:col-span-8 lg:col-span-9">

                    <div class="mb-12" id="profile-header">
                        <?php if (!empty($investigador['etiqueta_badge'])): ?>
                        <span class="inline-block bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full mb-4 uppercase tracking-wider"><?= htmlspecialchars($investigador['etiqueta_badge']) ?></span>
                        <?php endif; ?>
                        
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($investigador['nombre']) ?></h1>
                        <p class="text-xl text-gray-500 font-light"><?= htmlspecialchars($investigador['cargo_o_grado']) ?></p>
                    </div>

                    <!-- Semblanza -->
                    <?php if (!empty($investigador['semblanza'])): ?>
                    <div class="mb-20">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center">
                            Semblanza Profesional
                        </h3>
                        <div class="prose max-w-none text-gray-600 leading-relaxed text-lg">
                            <p><?= nl2br(htmlspecialchars($investigador['semblanza'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Especialidad de investigación -->
                    <?php if (count($lineas) > 0): ?>
                    <div class="mb-20">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-8 flex items-center">
                            Especialidad de investigación
                        </h3>

                        <div class="space-y-8">
                            <?php 
                            $contador = 1;
                            foreach ($lineas as $linea): ?>
                            <div class="group flex gap-6 items-baseline border-b border-gray-100 pb-8">
                                <span class="text-blue-600 font-bold text-lg w-16 flex-shrink-0"><?= str_pad($contador, 2, '0', STR_PAD_LEFT) ?></span>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($linea['titulo']) ?></h4>
                                    <p class="text-sm text-gray-500 mb-0"><?= htmlspecialchars($linea['descripcion']) ?></p>
                                </div>
                            </div>
                            <?php 
                            $contador++;
                            endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Publicaciones Destacadas -->
                    <?php if (count($publicaciones) > 0): ?>
                    <div class="mb-20">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-8 flex items-center">
                            Publicaciones Destacadas
                        </h3>

                        <div class="space-y-6">
                            <?php foreach ($publicaciones as $index => $pub): 
                                $url_pub = format_social_url($pub['enlace'] ?? '');
                            ?>
                            <div class="pl-4 border-l-2 <?= $index === 0 ? 'border-blue-600' : 'border-gray-200' ?> py-1 text-gray-500">
                                <div class="flex items-center group">
                                    <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors mb-1">
                                        <?= htmlspecialchars($pub['titulo']) ?>
                                    </h4>
                                    <?php if($url_pub): ?>
                                        <a href="<?= htmlspecialchars($url_pub) ?>" target="_blank" class="ml-2 text-gray-400 hover:text-blue-600 transition-colors" title="Ver publicación">
                                            <i data-lucide="external-link" class="w-5 h-5"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if(!empty($pub['subtitulo'])): ?>
                                <p class="text-sm"><?= htmlspecialchars($pub['subtitulo']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Proyectos Participantes -->
                    <?php if (count($proyectos) > 0): ?>
                    <div class="mb-20">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-8 flex items-center">
                            Participación en Proyectos
                        </h3>
                        
                        <div class="grid gap-6">
                            <?php foreach ($proyectos as $proy): ?>
                            <a href="ficha_tecnica.php?id=<?= $proy['id'] ?>" class="group bg-gray-50 hover:bg-white border border-gray-100 hover:border-blue-200 p-6 rounded-xl transition-all duration-300 hover:shadow-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider"><?= htmlspecialchars($proy['anio_inicio']) ?></span>
                                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?= htmlspecialchars($proy['estado']) ?></span>
                                </div>
                                
                                <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors mt-2 leading-tight">
                                    <?= htmlspecialchars($proy['titulo']) ?>
                                </h4>
                                
                                <div class="flex items-center text-sm text-gray-500 mt-4">
                                    <span class="truncate"><i data-lucide="tag" class="w-3 h-3 inline mr-1 text-gray-400"></i><?= htmlspecialchars($proy['categoria']) ?></span>
                                    <span class="mx-3 opacity-50">•</span>
                                    <span class="text-blue-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity flex items-center">
                                        Ver Detalles <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                                    </span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
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
    <script>
        lucide.createIcons();
        document.getElementById('year').textContent = new Date().getFullYear();

        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        let isMenuOpen = false;

        mobileMenuBtn.addEventListener('click', () => {
            isMenuOpen = !isMenuOpen;
            if (isMenuOpen) {
                mobileMenu.classList.remove('hidden');
            } else {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
