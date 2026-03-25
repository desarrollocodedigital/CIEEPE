<?php
require_once 'conexion.php';
$stmt = $pdo->query("SELECT * FROM proyectos ORDER BY id DESC");
$proyectos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <title>Proyectos | CIEEPE</title>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-abstract {
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 100 C 20 0 50 0 100 100 Z' fill='white' fill-opacity='0.1'/%3E%3C/svg%3E");
            background-size: cover;
        }
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
            <a href="proyectos.php" class="font-medium text-blue-600">Proyectos</a>
            <a href="noticias.php" class="font-medium">Noticias</a>
            <a href="investigadores.php" class="font-medium">Equipo</a>
        </div>
    </nav>

    <header class="bg-blue-900 text-white pt-32 pb-16 bg-abstract relative overflow-hidden">
        <div class="container mx-auto px-4 md:px-8 relative z-10 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Proyectos de Investigación</h1>
            <p class="text-xl text-blue-100 max-w-2xl mx-auto">Explora nuestras iniciativas actuales para mejorar la educación especial en la región.</p>
        </div>
    </header>

    <div class="bg-white border-b border-gray-200 py-4">
        <div class="container mx-auto px-4 md:px-8 flex flex-wrap gap-4 justify-center" id="filters">
            <button data-filter="all" class="filter-btn px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-medium shadow-sm">Todos</button>
            <button data-filter="En Curso" class="filter-btn px-4 py-2 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors">En Curso</button>
            <button data-filter="En Puerta" class="filter-btn px-4 py-2 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors">En Puerta</button>
            <button data-filter="Terminados" class="filter-btn px-4 py-2 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors">Terminados</button>
        </div>
    </div>

    <section class="py-16">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" id="projects-grid">
                <?php foreach($proyectos as $pro): ?>
                <article data-status="<?= htmlspecialchars($pro['estado']) ?>" class="project-card bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 flex flex-col group">
                    <div class="relative h-56 overflow-hidden">
                        <img src="<?= htmlspecialchars($pro['imagen_portada'] ?? './img/placeholder.jpg') ?>" alt="<?= htmlspecialchars($pro['titulo']) ?>"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-4 left-4 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm uppercase tracking-wide">
                            <?= htmlspecialchars($pro['estado']) ?>
                        </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center text-xs text-gray-500 mb-3 space-x-2">
                            <span class="flex items-center"><i data-lucide="tag" class="w-3 h-3 mr-1"></i> <?= htmlspecialchars($pro['categoria']) ?></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 leading-tight group-hover:text-blue-600 transition-colors">
                            <?= htmlspecialchars($pro['titulo']) ?></h3>
                        <p class="text-gray-600 text-sm leading-relaxed mb-4 flex-1">
                            <?= htmlspecialchars($pro['descripcion_corta']) ?>
                        </p>
                        <div class="text-xs text-gray-500 mb-4 space-y-1">
                            <p><strong>Responsable:</strong> <?= htmlspecialchars($pro['responsable']) ?></p>
                            <p><strong>Internos:</strong> <?= htmlspecialchars($pro['internos']) ?></p>
                            <p><strong>Externos:</strong> <?= htmlspecialchars($pro['externos']) ?></p>
                        </div>
                        <div class="border-t border-gray-100 pt-4 flex justify-between items-center">
                            <span class="text-sm text-gray-500">Inicio: <?= htmlspecialchars($pro['anio_inicio']) ?></span>
                            <a href="ficha_tecnica.php?id=<?= $pro['id'] ?>" class="text-blue-600 font-medium text-sm hover:underline">Ver ficha técnica</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
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
        document.getElementById('year').textContent = new Date().getFullYear();
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        const filterBtns = document.querySelectorAll('.filter-btn');
        const projects = document.querySelectorAll('.project-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Update active button styles
                filterBtns.forEach(b => {
                    b.classList.remove('bg-blue-600', 'text-white');
                    b.classList.add('bg-white', 'text-gray-600');
                });
                btn.classList.remove('bg-white', 'text-gray-600');
                btn.classList.add('bg-blue-600', 'text-white');

                const filterValue = btn.getAttribute('data-filter');

                projects.forEach(project => {
                    if (filterValue === 'all' || project.getAttribute('data-status') === filterValue) {
                        project.style.display = 'flex';
                    } else {
                        project.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
