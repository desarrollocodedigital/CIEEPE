<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'conexion.php';
$modulo = $_GET['modulo'] ?? 'inicio';

// Map de módulos seguros
$modulosValidos = ['inicio', 'investigadores', 'proyectos', 'lineas', 'noticias', 'editar_investigador', 'nuevo_investigador', 'nueva_linea', 'editar_linea', 'nuevo_proyecto', 'editar_proyecto', 'nueva_noticia', 'editar_noticia', 'inicio_config', 'nosotros_config'];
if (!in_array($modulo, $modulosValidos)) {
    $modulo = 'inicio';
}

$activeClass = "bg-blue-800 text-white border-l-4 border-blue-400";
$inactiveClass = "text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIEEPE | Sistema Administrativo</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6;}
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 10px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <!-- Menú Lateral Izquierdo (Sidebar) -->
    <aside class="w-64 bg-gray-900 flex-shrink-0 flex flex-col transition-all duration-300 z-20 shadow-xl hidden md:flex">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-gray-800 bg-gray-950 gap-4">
            <img src="<?= htmlspecialchars($site_logo ?? '') ?>" alt="CIEEPE Logo" class="w-12 h-12 rounded-lg shadow-sm object-contain bg-white p-1 flex-shrink-0">
            <div class="min-w-0">
                <h1 class="text-white font-bold text-lg tracking-wide leading-tight truncate">CIEEPE</h1>
                <p class="text-blue-400 text-xs truncate">Panel Admin</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto sidebar-scroll py-6 space-y-1">
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Principal</h3>
            
            <a href="admin.php?modulo=inicio" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'inicio' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Dashboard</span>
            </a>

            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Gestión de Datos</h3>
            
            <a href="admin.php?modulo=investigadores" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'investigadores' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Investigadores</span>
            </a>

            <a href="admin.php?modulo=proyectos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'proyectos' || $modulo === 'nuevo_proyecto' || $modulo === 'editar_proyecto' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="folder-kanban" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Proyectos</span>
            </a>

            <a href="admin.php?modulo=noticias" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'noticias' || $modulo === 'nueva_noticia' || $modulo === 'editar_noticia' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="newspaper" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Noticias</span>
            </a>

            <a href="admin.php?modulo=lineas" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'lineas' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="book-open" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Líneas de Invest.</span>
            </a>

            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Sitio Web</h3>

            <a href="admin.php?modulo=inicio_config" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'inicio_config' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="settings" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Config. Inicio</span>
            </a>

            <a href="admin.php?modulo=nosotros_config" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'nosotros_config' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="info" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Config. Nosotros</span>
            </a>
        </nav>

        <!-- User bottom part -->
        <div class="p-4 border-t border-gray-800 bg-gray-950">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm">A</div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white leading-none">Administrador</p>
                    </div>
                </div>
                <a href="logout.php" title="Cerrar Sesión" class="text-gray-400 hover:text-red-400 transition-colors bg-gray-900 p-2 rounded-lg">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Contenedor Principal (Derecho) -->
    <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-hidden">
        
        <!-- Header Topbar -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 flex-shrink-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-500 hover:text-gray-900">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-800 hidden sm:block">
                    <?php 
                        switch($modulo) {
                            case 'inicio': echo "Visión General"; break;
                            case 'investigadores': echo "Directorio de Investigadores"; break;
                            case 'editar_investigador': echo "Edición de Perfil"; break;
                            case 'nuevo_investigador': echo "Añadir Nuevo Investigador"; break;
                            case 'proyectos': echo "Gestión de Proyectos"; break;
                            case 'nuevo_proyecto': echo "Añadir Nuevo Proyecto"; break;
                            case 'editar_proyecto': echo "Editar Proyecto"; break;
                            case 'lineas': echo "Líneas de Investigación"; break;
                            case 'nueva_linea': echo "Añadir Nueva Línea"; break;
                            case 'editar_linea': echo "Editar Línea de Investigación"; break;
                            case 'inicio_config': echo "Configuración del Inicio"; break;
                            case 'nosotros_config': echo "Configuración: Sobre Nosotros"; break;
                        }
                    ?>
                </h2>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="index.html" target="_blank" class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full hover:bg-blue-100 transition-colors">
                    <i data-lucide="external-link" class="w-4 h-4 mr-1.5"></i> Ver Sitio Web
                </a>
                <span class="text-sm text-gray-400">|</span>
                <span class="text-sm font-medium text-gray-600"><?= htmlspecialchars($_SESSION['email']) ?></span>
            </div>
        </header>

        <!-- Área de Contenido Principal -->
        <main class="flex-1 overflow-y-auto w-full p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <?php include "admin_{$modulo}.php"; ?>
            </div>
        </main>
        
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
