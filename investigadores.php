<?php
require_once 'conexion.php';

// Obtener investigadores activos y su color basado en la etiqueta (línea de investigación)
// Usamos LEFT JOIN para que si no tiene línea asignada muestre 'blue' por defecto
$sql = "SELECT i.*, COALESCE(l.color, 'blue') as badge_color 
        FROM investigadores i 
        LEFT JOIN lineas_investigacion l ON i.etiqueta_badge = l.titulo
        ORDER BY i.id ASC";

$stmt = $pdo->query($sql);
$investigadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Investigadores | CIEEPE</title>
    <style>
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
                <img src="<?= htmlspecialchars($site_logo) ?>" alt="CIEEPE ENEES Logo" class="h-12 w-auto md:h-14">
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
            <a href="index.html#nosotros" class="font-medium">Nosotros</a>
            <a href="index.html#investigacion" class="font-medium">Líneas de Investigación</a>
            <a href="proyectos.php" class="font-medium">Proyectos</a>
            <a href="noticias.php" class="font-medium">Noticias</a>
            <a href="investigadores.php" class="font-medium text-blue-600">Equipo</a>
            <a href="index.html#contacto" class="font-medium">Contacto</a>
        </div>
    </nav>

    <!-- Header Interno -->
    <header class="bg-blue-900 text-white pt-24 pb-16 bg-abstract relative overflow-hidden">
        <div class="container mx-auto px-4 md:px-8 relative z-10 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Nuestro Equipo</h1>
            <p class="text-xl text-blue-100 max-w-2xl mx-auto">Investigadores comprometidos con la transformación
                educativa y la inclusión en Sinaloa.</p>
        </div>
    </header>

    <!-- Sección de Búsqueda y Filtros -->
    <section class="py-10 bg-white border-b border-gray-200 sticky top-[72px] z-30 shadow-sm">
        <div class="container mx-auto px-4 md:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Buscar investigador por nombre o especialidad..."
                        class="w-full py-4 pl-12 pr-4 bg-gray-50 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow text-lg">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="search" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Grid de Investigadores -->
    <section class="py-16">
        <div class="container mx-auto px-4 md:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" id="investigators-grid">
                
                <?php foreach ($investigadores as $inv): 
                    $color = $inv['badge_color'] ?: 'blue';
                    $nombre = htmlspecialchars($inv['nombre']);
                    $especialidad = htmlspecialchars($inv['especialidad']);
                    $img = htmlspecialchars($inv['imagen_perfil']) ?: './img/placeholder.jpg';
                    $tipo = htmlspecialchars($inv['tipo_investigador'] ?: 'Investigador');
                    $cargo = htmlspecialchars($inv['cargo_o_grado']);
                    $semb_corta = htmlspecialchars($inv['semblanza_corta'] ?: 'Miembro del equipo de investigación y proyectos de CIEEPE.');
                    $email = htmlspecialchars($inv['email']);
                    // Construir el enlace dinámico al perfil genérico
                    $perfil_url = "perfil_generico.php?id=" . $inv['id'];
                ?>
                <!-- Investigador Card -->
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-500 overflow-hidden group relative h-[420px] researcher-card"
                    data-name="<?= $nombre ?>" data-specialty="<?= $especialidad ?>">
                    <div
                        class="absolute top-0 left-0 w-full h-full group-hover:w-32 group-hover:h-32 group-hover:rounded-full group-hover:relative group-hover:mx-auto group-hover:mt-6 group-hover:top-auto group-hover:left-auto transition-all duration-500 z-10 ease-in-out border-4 border-transparent group-hover:border-<?= $color ?>-50 overflow-hidden">
                        <img src="<?= $img ?>" alt="<?= $nombre ?>"
                            class="w-full h-full object-cover object-top">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-100 group-hover:opacity-0 transition-opacity duration-300">
                        </div>
                    </div>
                    <div
                        class="absolute bottom-0 left-0 w-full p-6 text-white transform translate-y-0 group-hover:translate-y-full group-hover:opacity-0 transition-all duration-500 z-20">
                        <h3 class="text-2xl font-bold mb-1"><?= $nombre ?></h3>
                        <p class="text-<?= $color ?>-300 font-medium text-sm uppercase tracking-wide"><?= $tipo ?></p>
                    </div>
                    <div
                        class="p-6 pt-0 flex flex-col items-center text-center opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 absolute inset-0 group-hover:relative pointer-events-none group-hover:pointer-events-auto mt-32 group-hover:mt-2">
                        <div
                            class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 w-full">
                            <h3 class="text-lg font-bold text-gray-900 mb-1 leading-tight"><?= $nombre ?></h3>
                            <p class="text-<?= $color ?>-600 font-medium text-xs mb-3"><?= $cargo ?></p>
                            <p class="text-gray-500 text-xs mb-4 line-clamp-3 leading-relaxed"><?= $semb_corta ?></p>
                            
                            <?php if ($email): ?>
                            <div class="flex justify-center space-x-3 mb-4">
                                <a href="mailto:<?= $email ?>"
                                    class="text-gray-400 hover:text-<?= $color ?>-600 transition-colors"><i data-lucide="mail"
                                        class="w-4 h-4"></i></a>
                            </div>
                            <?php else: ?>
                            <div class="h-4 mb-4"></div> <!-- Spacer if no email -->
                            <?php endif; ?>

                            <a href="<?= $perfil_url ?>"
                                class="inline-block w-full py-2 px-4 border border-<?= $color ?>-600 text-<?= $color ?>-600 rounded-lg hover:bg-<?= $color ?>-600 hover:text-white transition-colors font-medium text-sm">Ver Perfil Completo</a>
                        </div>
                    </div>
                </div>
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
        // Inicializar Iconos y Año
        lucide.createIcons();
        document.getElementById('year').textContent = new Date().getFullYear();

        // Mobile Menu
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

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const researcherCards = document.querySelectorAll('.researcher-card');

        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();

            researcherCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const specialty = card.dataset.specialty.toLowerCase();

                if (name.includes(searchTerm) || specialty.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>
