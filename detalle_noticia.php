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
    <title><?= htmlspecialchars($not['titulo']) ?> | CIEEPE</title>
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
                <a href="proyectos.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Proyectos</a>
                <a href="noticias.php" class="nav-link text-sm font-medium text-blue-600">Noticias</a>
                <a href="investigadores.php" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Equipo</a>
                <a href="index.html#contacto" class="nav-link text-sm font-medium hover:text-blue-600 transition-colors text-gray-700">Contacto</a>
            </div>
            <button id="mobile-menu-btn" class="md:hidden focus:outline-none text-gray-700">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white shadow-lg absolute top-full left-0 w-full py-4 px-6 flex flex-col space-y-4 text-gray-800">
            <a href="index.html#inicio" class="font-medium">Inicio</a>
            <a href="noticias.php" class="font-medium text-blue-600">Noticias</a>
            <a href="proyectos.php" class="font-medium">Proyectos</a>
            <a href="investigadores.php" class="font-medium">Equipo</a>
        </div>
    </nav>

    <!-- Hero de la Noticia -->
    <header class="relative h-[50vh] min-h-[400px] w-full overflow-hidden flex items-center justify-center">
        <div class="absolute inset-0 z-0">
            <img src="<?= htmlspecialchars($not['imagen_portada'] ?: './img/placeholder.jpg') ?>" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
        </div>

        <div class="container mx-auto px-4 md:px-8 relative z-10 text-white pt-20">
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
                    <div class="bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-gray-100 italic leading-loose text-lg">
                        <?= nl2br(htmlspecialchars($not['descripcion_larga'])) ?>
                    </div>
                </article>

                <!-- Galería -->
                <?php if(!empty($galeria)): ?>
                <section class="mt-20">
                    <h3 class="text-2xl font-bold text-gray-900 mb-8 flex items-center">
                        <i data-lucide="images" class="w-6 h-6 mr-3 text-blue-600"></i> Galería de la Noticia
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach($galeria as $img): ?>
                        <div class="group relative overflow-hidden rounded-2xl shadow-md aspect-video">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
                
                <!-- Navegación inferior -->
                <div class="mt-20 pt-8 border-t border-gray-200 flex justify-between items-center">
                    <a href="noticias.php" class="flex items-center text-blue-600 font-bold hover:text-blue-800 transition-colors">
                        <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i> Volver a Noticias
                    </a>
                </div>
            </div>
        </div>
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
                        <li><a href="#inicio" class="text-gray-400 hover:text-white transition-colors">Inicio</a></li>
                        <li><a href="#nosotros" class="text-gray-400 hover:text-white transition-colors">Nosotros</a>
                        </li>
                        <li><a href="#investigacion" class="text-gray-400 hover:text-white transition-colors">Líneas de
                                Investigación</a></li>
                        <li><a href="proyectos.php"
                                class="text-gray-400 hover:text-white transition-colors">Noticias</a>
                        </li>
                        <li><a href="biblioteca.php"
                                class="text-blue-400 hover:text-white transition-colors font-medium">Biblioteca Virtual
                                (IA)</a>
                        </li>
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
                <p>&copy; <span id="year"></span> CIEEPE - Escuela Normal de Especialización del Estado de Sinaloa.
                    Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
