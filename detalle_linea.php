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
                <img src="./img/logo.png" alt="CIEEPE ENEES Logo" class="h-12 w-auto md:h-14">
            </a>
            <div class="hidden md:flex space-x-6 lg:space-x-8">
                <a href="index.html#inicio" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Inicio</a>
                <a href="index.html#investigacion" class="nav-link text-sm font-medium text-blue-600">Líneas</a>
                <a href="proyectos.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Proyectos</a>
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
            <a href="index.html#investigacion" class="font-medium text-blue-600">Líneas de Investigación</a>
            <a href="proyectos.php" class="font-medium">Proyectos</a>
            <a href="noticias.php" class="font-medium">Noticias</a>
            <a href="investigadores.php" class="font-medium">Equipo</a>
        </div>
    </nav>

    <!-- Header Interno -->
    <header class="bg-<?= $color ?>-900 text-white pt-32 pb-16 bg-abstract relative overflow-hidden">
        <div class="container mx-auto px-4 md:px-8 relative z-10 text-center">
            <div class="inline-block p-3 rounded-full bg-<?= $color ?>-800/50 backdrop-blur-sm mb-6 border border-<?= $color ?>-400/30">
                <i data-lucide="<?= htmlspecialchars($icono) ?>" class="w-8 h-8 text-<?= $color ?>-300"></i>
            </div>
            <br>
            <a href="index.html#investigacion" class="inline-flex items-center text-<?= $color ?>-200 hover:text-white mb-6 transition-colors font-medium">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i> Volver a Líneas
            </a>
            <h1 class="text-3xl md:text-5xl font-bold mb-4"><?= htmlspecialchars($titulo_linea) ?></h1>
            <p class="text-xl text-<?= $color ?>-100 max-w-3xl mx-auto leading-relaxed">
                <?= nl2br(htmlspecialchars($linea['descripcion'])) ?>
            </p>
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
            mobileMenu.classList.toggle('hidden', !isMenuOpen);
        });
    </script>
</body>
</html>
