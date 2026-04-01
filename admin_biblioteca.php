<?php
session_start();
// Protección de ruta específica para la biblioteca
if (!isset($_SESSION['user_bib_id']) || ($_SESSION['user_bib_rol'] ?? '') !== 'admin') {
    header("Location: login_biblioteca.php");
    exit;
}

require_once 'conexion.php';
$modulo = $_GET['modulo'] ?? 'inicio';

// Map de módulos seguros para la biblioteca
$modulosValidos = ['inicio', 'usuarios', 'solicitudes', 'recursos', 'documentos', 'configuracion', 'manual'];
if (!in_array($modulo, $modulosValidos)) {
    $modulo = 'inicio';
}

$activeClass = "bg-blue-800 text-white border-l-4 border-blue-400";
$inactiveClass = "text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent";
// Lógica de Acciones (Aceptar/Rechazar)
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $accion = $_GET['accion'];
    $id = (int)$_GET['id'];
    
    if ($accion === 'aceptar') {
        $stmt = $pdo->prepare("UPDATE usuarios_biblioteca SET estado = 'activo', fecha_aprobacion = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        
        // Si el usuario marcó 'investigador', le damos el rol
        $uInfo = $pdo->query("SELECT tipo_usuario FROM usuarios_biblioteca WHERE id = $id")->fetch();
        if ($uInfo && $uInfo['tipo_usuario'] === 'investigador') {
            $pdo->query("UPDATE usuarios_biblioteca SET rol = 'investigador' WHERE id = $id");
        }
        $success_msg = "Usuario activado correctamente.";
    } elseif ($accion === 'rechazar') {
        $stmt = $pdo->prepare("UPDATE usuarios_biblioteca SET estado = 'rechazado' WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = "Solicitud rechazada.";
    } elseif ($accion === 'aprobar_doc') {
        $stmt = $pdo->prepare("UPDATE documentos_biblioteca SET estado_publicacion = 'publicado', fecha_aprobacion = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = "Documento publicado con éxito.";
    } elseif ($accion === 'rechazar_doc') {
        $stmt = $pdo->prepare("UPDATE documentos_biblioteca SET estado_publicacion = 'rechazado' WHERE id = ?");
        $stmt->execute([$id]);
        $success_msg = "Documento rechazado.";
    }
}

// Lógica de Guardado de Configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_config'])) {
    if (isset($_POST['config']) && is_array($_POST['config'])) {
        foreach($_POST['config'] as $clave => $valor) {
            $stmt = $pdo->prepare("INSERT INTO site_config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
            $stmt->execute([$clave, $valor, $valor]);
        }
    }
    // Caso especial para checkboxes (si no vienen en el POST, es que están desactivados)
    // Para el botón CIATA:
    $btn_val = isset($_POST['show_ciata_button']) ? '1' : '0';
    $stmt = $pdo->prepare("INSERT INTO site_config (clave, valor) VALUES ('show_ciata_button', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt->execute([$btn_val, $btn_val]);

    $success_msg = "Configuración del sistema actualizada correctamente.";
}

// Lógica de Edición de Usuario (Migrada a modificar_usuario.php)

// Lógica de Edición de Documento (POST eliminada, se usa subir_articulo.php)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIATA | Administración de Biblioteca</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6;}
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        #pdf-render-container canvas {
            max-width: 100%;
            height: auto !important;
            margin-bottom: 2rem;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <!-- Menú Lateral Izquierdo (Sidebar) -->
    <aside class="w-64 bg-gray-900 flex-shrink-0 flex flex-col transition-all duration-300 z-20 shadow-xl hidden md:flex">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-gray-800 bg-gray-950 gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg">
                <i data-lucide="library" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-lg tracking-wide leading-tight truncate">CIATA</h1>
                <p class="text-blue-400 text-[10px] uppercase tracking-widest font-bold truncate">Admin Biblio</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto sidebar-scroll py-6 space-y-1">
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 font-bold tracking-widest">Principal</h3>
            
            <a href="admin_biblioteca.php?modulo=inicio" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'inicio' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Dashboard</span>
            </a>

            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6 font-bold tracking-widest">Biblioteca</h3>

            <a href="admin_biblioteca.php?modulo=recursos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'recursos' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="book-open" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Biblioteca General</span>
            </a>

            <a href="admin_biblioteca.php?modulo=documentos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'documentos' ? $activeClass : $inactiveClass ?>">
                <div class="relative">
                    <i data-lucide="file-check" class="w-5 h-5 mr-3"></i>
                    <?php 
                        $docsPendientes = $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE estado_publicacion = 'pendiente'")->fetchColumn();
                        if ($docsPendientes > 0): 
                    ?>
                    <span class="absolute -top-1 right-2 w-4 h-4 bg-emerald-500 text-white text-[8px] flex items-center justify-center rounded-full border border-gray-900"><?= $docsPendientes ?></span>
                    <?php endif; ?>
                </div>
                <span class="font-medium text-sm">Revisión de Documentos</span>
            </a>

            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6 font-bold tracking-widest">Comunidad</h3>
            
            <a href="admin_biblioteca.php?modulo=solicitudes" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'solicitudes' ? $activeClass : $inactiveClass ?>">
                <div class="relative">
                    <i data-lucide="user-plus" class="w-5 h-5 mr-3"></i>
                    <?php 
                        $pendientesCount = $pdo->query("SELECT COUNT(*) FROM usuarios_biblioteca WHERE estado = 'pendiente'")->fetchColumn();
                        if ($pendientesCount > 0): 
                    ?>
                    <span class="absolute -top-1 right-2 w-4 h-4 bg-red-500 text-white text-[8px] flex items-center justify-center rounded-full border border-gray-900"><?= $pendientesCount ?></span>
                    <?php endif; ?>
                </div>
                <span class="font-medium text-sm">Solicitudes</span>
            </a>

            <a href="admin_biblioteca.php?modulo=usuarios" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'usuarios' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Gestión de Usuarios</span>
            </a>

            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6 font-bold tracking-widest">Sistema</h3>

            <a href="admin_biblioteca.php?modulo=configuracion" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'configuracion' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="settings" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Configuración</span>
            </a>
        </nav>

        <?php
        // Pre-fetch search/pagination for 'recursos' to avoid duplicate logic
        if ($modulo === 'recursos') {
            $por_pagina = 10;
            $p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
            if ($p < 1) $p = 1;
            $offset = ($p - 1) * $por_pagina;
            $q = trim($_GET['q'] ?? '');
            
            $where = '';
            $params = [];
            if ($q !== '') {
                $where = "WHERE (d.titulo LIKE ? OR u.nombre LIKE ?)";
                $like = '%' . $q . '%';
                $params = [$like, $like];
            }
            
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM documentos_biblioteca d JOIN usuarios_biblioteca u ON d.id_autor = u.id $where");
            $stmt_count->execute($params);
            $total_docs = $stmt_count->fetchColumn();
            $total_paginas = ceil($total_docs / $por_pagina);
            
            $stmt_all = $pdo->prepare("SELECT d.*, u.nombre as autor_nombre FROM documentos_biblioteca d JOIN usuarios_biblioteca u ON d.id_autor = u.id $where ORDER BY d.fecha_subida DESC LIMIT $por_pagina OFFSET $offset");
            $stmt_all->execute($params);
            $allDocs = $stmt_all->fetchAll();
            $base_url = "admin_biblioteca.php?modulo=recursos" . ($q !== '' ? "&q=".urlencode($q) : "");
        }
        ?>

        <!-- Sección de Manual de Usuario (Fuera del scroll) -->
        <div class="px-4 py-2 border-t border-gray-800 bg-gray-900/50">
            <a href="admin_biblioteca.php?modulo=manual" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= $modulo === 'manual' ? $activeClass : 'text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent' ?>">
                <i data-lucide="book" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Manual de Usuario</span>
            </a>
        </div>

        <!-- User bottom part -->
        <div class="p-4 border-t border-gray-800 bg-gray-950">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs">
                        <?= strtoupper(substr($_SESSION['user_bib_nombre'], 0, 1)) ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white leading-none truncate w-24"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></p>
                    </div>
                </div>
                <a href="logout_biblioteca.php" title="Cerrar Sesión" class="text-gray-400 hover:text-red-400 transition-colors bg-gray-900 p-2 rounded-lg">
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
                            case 'inicio': echo "Dashboard CIATA"; break;
                            case 'usuarios': echo "Gestión de Usuarios Activos"; break;
                            case 'solicitudes': echo "Revisión de Solicitudes"; break;
                            case 'documentos': echo "Aprobación de Contenidos"; break;
                            case 'recursos': echo "Catálogo de Recursos"; break;
                            case 'configuracion': echo "Configuración General"; break;
                            case 'manual': echo "Manual de Usuario"; break;
                        }
                    ?>
                </h2>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="biblioteca.php" class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full hover:bg-blue-100 transition-colors">
                    <i data-lucide="library" class="w-4 h-4 mr-1.5"></i> Ver Biblioteca
                </a>
                <span class="text-sm text-gray-400">|</span>
                <span class="text-sm font-medium text-gray-600"><?= htmlspecialchars($_SESSION['user_bib_correo']) ?></span>
            </div>
        </header>

        <!-- Área de Contenido Principal -->
        <main class="flex-1 overflow-y-auto w-full p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <?php if (isset($success_msg)): ?>
                    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
                        <span class="text-sm font-medium"><?= $success_msg ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($modulo === 'inicio'): ?>
                    <!-- DASHBOARD CONTENT -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-4">
                                <i data-lucide="book-open" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-slate-500 text-sm font-medium">Libros y Documentos</h3>
                            <p class="text-3xl font-bold text-slate-900">
                                <?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca")->fetchColumn(); ?>
                            </p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-4">
                                <i data-lucide="users" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-slate-500 text-sm font-medium">Lectores Activos</h3>
                            <p class="text-3xl font-bold text-slate-900">
                                <?php 
                                    $countActivos = $pdo->query("SELECT COUNT(*) FROM usuarios_biblioteca WHERE estado = 'activo'")->fetchColumn();
                                    echo $countActivos;
                                ?>
                            </p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mb-4">
                                <i data-lucide="trending-up" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-slate-500 text-sm font-medium">Nuevos Registros</h3>
                            <p class="text-3xl font-bold text-slate-900">
                                <?php echo $pdo->query("SELECT COUNT(*) FROM usuarios_biblioteca WHERE estado = 'pendiente'")->fetchColumn(); ?>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Últimos Usuarios -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                    <i data-lucide="history" class="w-5 h-5 text-blue-600"></i> Últimos Usuarios
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider font-bold">
                                            <th class="px-6 py-3 border-b">Nombre</th>
                                            <th class="px-6 py-3 border-b text-right">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php
                                        $recaentes = $pdo->query("SELECT nombre, tipo_usuario, estado, fecha_creacion FROM usuarios_biblioteca ORDER BY fecha_creacion DESC LIMIT 5")->fetchAll();
                                        foreach($recaentes as $u):
                                        ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-slate-900"><?= htmlspecialchars($u['nombre']) ?></div>
                                                <div class="text-[10px] text-slate-400 uppercase font-bold"><?= htmlspecialchars($u['tipo_usuario']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="px-2 py-0.5 rounded-full text-[8px] font-bold uppercase <?= $u['estado'] === 'activo' ? 'bg-emerald-100 text-emerald-700' : ($u['estado'] === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') ?>">
                                                    <?= $u['estado'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Últimos Documentos -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                    <i data-lucide="file-text" class="w-5 h-5 text-emerald-600"></i> Últimos Documentos
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider font-bold">
                                            <th class="px-6 py-3 border-b">Documento</th>
                                            <th class="px-6 py-3 border-b text-right">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php
                                        $ultimosDocs = $pdo->query("SELECT d.titulo, u.nombre as autor, d.estado_publicacion, d.fecha_subida 
                                            FROM documentos_biblioteca d 
                                            JOIN usuarios_biblioteca u ON d.id_autor = u.id 
                                            ORDER BY d.fecha_subida DESC LIMIT 5")->fetchAll();
                                        
                                        if (empty($ultimosDocs)): ?>
                                            <tr><td colspan="2" class="px-6 py-10 text-center text-slate-400 text-xs italic">No hay documentos registrados aún.</td></tr>
                                        <?php else: foreach($ultimosDocs as $d): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-slate-900 line-clamp-1" title="<?= htmlspecialchars($d['titulo']) ?>"><?= htmlspecialchars($d['titulo']) ?></div>
                                                <div class="text-[10px] text-slate-400 font-medium">Por: <?= htmlspecialchars($d['autor']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="px-2 py-0.5 rounded-full text-[8px] font-bold uppercase 
                                                    <?= $d['estado_publicacion'] === 'publicado' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                                    <?= $d['estado_publicacion'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif ($modulo === 'solicitudes'): ?>
                    <!-- SOLICITUDES CONTENT -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="user-check" class="w-5 h-5 text-blue-600"></i> Solicitudes de Acceso Pendientes
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                        <th class="px-6 py-3 border-b">Usuario</th>
                                        <th class="px-6 py-3 border-b">Perfil</th>
                                        <th class="px-6 py-3 border-b w-1/3">Motivo de Acceso</th>
                                        <th class="px-6 py-3 border-b">Fecha Registro</th>
                                        <th class="px-6 py-3 border-b text-right">Decisión</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php
                                    $pendientes = $pdo->query("SELECT * FROM usuarios_biblioteca WHERE estado = 'pendiente' ORDER BY fecha_creacion ASC")->fetchAll();
                                    if (empty($pendientes)):
                                    ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                            <i data-lucide="check-square" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                            <p>No hay solicitudes pendientes en este momento.</p>
                                        </td>
                                    </tr>
                                    <?php else: foreach($pendientes as $u): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-slate-900"><?= htmlspecialchars($u['nombre']) ?></div>
                                            <div class="text-[10px] text-slate-500"><?= htmlspecialchars($u['correo']) ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-600">
                                                <?= $u['tipo_usuario'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-xs text-slate-600 italic line-clamp-2" title="<?= htmlspecialchars($u['motivo_acceso']) ?>">
                                                "<?= htmlspecialchars($u['motivo_acceso']) ?>"
                                            </p>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-slate-500">
                                            <?= date('d/m/Y H:i', strtotime($u['fecha_creacion'])) ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="admin_biblioteca.php?modulo=solicitudes&accion=aceptar&id=<?= $u['id'] ?>" 
                                                   class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-1.5">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Aceptar
                                                </a>
                                                <a href="admin_biblioteca.php?modulo=solicitudes&accion=rechazar&id=<?= $u['id'] ?>" 
                                                   onclick="return confirm('¿Estás seguro de rechazar esta solicitud?')"
                                                   class="px-3 py-1.5 bg-red-50 text-red-600 text-xs font-bold rounded-lg hover:bg-red-100 transition-colors flex items-center gap-1.5">
                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Rechazar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($modulo === 'recursos'): ?>
                    <!-- BIBLIOTECA GENERAL CONTENT -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center bg-slate-50/50 gap-4">
                            <div>
                                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                    <i data-lucide="library" class="w-5 h-5 text-emerald-600"></i> Biblioteca General de Documentos
                                </h3>
                                <p class="text-[11px] text-slate-500 mt-1">Gestión completa del acervo digital (<span id="docs-count-total"><?= $total_docs ?></span> registros)</p>
                            </div>
                            
                            <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                                <!-- Filtros de Estado -->
                                <div class="flex bg-slate-100 p-1 rounded-xl" id="bib-filters">
                                    <?php 
                                        $f_bib = 'todos';
                                        $filterOptionsBib = [
                                            'todos' => 'Todos',
                                            'pendiente' => 'Pendientes',
                                            'publicado' => 'Publicados',
                                            'suspendido' => 'Suspendidos',
                                            'rechazado' => 'Rechazados',
                                            'borrador' => 'Borradores'
                                        ];
                                        foreach($filterOptionsBib as $val => $lab):
                                            $isActiveF = ($f_bib === $val);
                                    ?>
                                    <button onclick="changeBibFilter('<?= $val ?>')" data-status="<?= $val ?>"
                                       class="bib-filter-btn px-3 py-1.5 text-[10px] font-bold uppercase rounded-lg transition-all <?= $isActiveF ? 'bg-white text-emerald-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' ?>">
                                        <?= $lab ?>
                                    </button>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Buscador Dinámico -->
                                <div class="relative w-full md:w-64">
                                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                                    <input type="text" id="bib-search" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Título o Investigador..." autocomplete="off"
                                        class="w-full pl-9 pr-9 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none shadow-sm">
                                    <button id="clear-bib-search" class="<?= ($q ?? '') === '' ? 'hidden' : '' ?> absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 transition-colors">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros por Tipo (Nueva Fila de Filtros) -->
                        <div class="px-6 py-3 bg-white border-b border-slate-100 flex items-center gap-4 overflow-x-auto custom-scrollbar">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">Tipo de Documento:</span>
                            <div class="flex gap-2" id="bib-type-filters">
                                <?php 
                                    $typeOptions = [
                                        'todos' => ['lab' => 'Todos', 'icon' => 'layers'],
                                        'articulo' => ['lab' => 'Artículos', 'icon' => 'file-text'],
                                        'tesis' => ['lab' => 'Tesis', 'icon' => 'graduation-cap'],
                                        'acervo' => ['lab' => 'Acervos', 'icon' => 'archive']
                                    ];
                                    foreach($typeOptions as $val => $opt):
                                ?>
                                <button onclick="changeBibTypeFilter('<?= $val ?>')" data-type="<?= $val ?>"
                                    class="bib-type-btn px-4 py-1.5 rounded-full text-[10px] font-bold border transition-all flex items-center gap-2 <?= $val === 'todos' ? 'bg-slate-900 text-white border-slate-900 shadow-md shadow-slate-900/10' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' ?>">
                                    <i data-lucide="<?= $opt['icon'] ?>" class="w-3 h-3"></i>
                                    <?= $opt['lab'] ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                        <th class="px-6 py-3 border-b">Documento</th>
                                        <th class="px-6 py-3 border-b">Tipo</th>
                                        <th class="px-6 py-3 border-b">Investigador</th>
                                        <th class="px-6 py-3 border-b">Estado</th>
                                        <th class="px-6 py-3 border-b text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bib-tbody" class="divide-y divide-slate-100">
                                    <?php if (empty($allDocs)): ?>
                                    <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400">No se encontraron documentos.</td></tr>
                                    <?php else: foreach($allDocs as $d): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-10 bg-slate-100 rounded border border-slate-200 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                                                    <?php if($d['imagen_portada']): ?><img src="<?= $d['imagen_portada'] ?>" class="w-full h-full object-cover"><?php else: ?><i data-lucide="file-text" class="w-4 h-4 text-slate-300"></i><?php endif; ?>
                                                </div>
                                                <div class="text-xs font-bold text-slate-900"><?= htmlspecialchars($d['titulo']) ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase transition-all
                                                <?= $d['tipo'] === 'articulo' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 
                                                   ($d['tipo'] === 'tesis' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-purple-50 text-purple-600 border border-purple-100') ?>">
                                                <?= $d['tipo'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-slate-600 font-medium"><?= htmlspecialchars($d['autor_nombre']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 rounded-full text-[8px] font-extrabold uppercase 
                                                <?= $d['estado_publicacion'] === 'publicado' ? 'bg-emerald-100 text-emerald-700' : 
                                                   ($d['estado_publicacion'] === 'borrador' ? 'bg-slate-100 text-slate-600' : 
                                                   ($d['estado_publicacion'] === 'suspendido' ? 'bg-amber-100 text-amber-700' :
                                                   ($d['estado_publicacion'] === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'))) ?>">
                                                <?= $d['estado_publicacion'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="modificar_articulo.php?edit=<?= $d['id'] ?>" 
                                                   class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all" 
                                                   title="Modificar Documento">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                </a>
                                                <button onclick="openDocumentViewer('<?= $d['archivo_documento'] ?>', '<?= addslashes($d['titulo']) ?>')" 
                                                   class="p-2 bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-200 transition-all" 
                                                   title="Ver Documento">
                                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación Footer -->
                        <div id="bib-pagination" class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                            <?php if ($total_paginas > 1): ?>
                            <span class="text-[11px] text-slate-500">
                                Mostrando <strong id="p-start"><?= $offset + 1 ?></strong> - <strong id="p-end"><?= min($offset + $por_pagina, $total_docs) ?></strong> de <strong><?= $total_docs ?></strong>
                            </span>
                            <div class="flex gap-1">
                                <?php if ($p > 1): ?>
                                    <a href="<?= $base_url ?>&p=<?= $p - 1 ?>" class="px-3 py-1 bg-white border border-slate-200 rounded-md text-[10px] font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">&laquo; Anterior</a>
                                <?php endif; ?>
                                
                                <div class="hidden sm:flex gap-1">
                                    <?php for($i=1; $i<=$total_paginas; $i++): ?>
                                        <a href="<?= $base_url ?>&p=<?= $i ?>" class="px-3 py-1 <?= $i == $p ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600' ?> border border-slate-200 rounded-md text-[10px] font-bold hover:bg-emerald-50 hover:text-emerald-700 transition-colors"><?= $i ?></a>
                                    <?php endfor; ?>
                                </div>

                                <?php if ($p < $total_paginas): ?>
                                    <a href="<?= $base_url ?>&p=<?= $p + 1 ?>" class="px-3 py-1 bg-white border border-slate-200 rounded-md text-[10px] font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">Siguiente &raquo;</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($modulo === 'documentos'): ?>
                    <!-- DOCUMENTOS REVISION CONTENT -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-emerald-50/30">
                            <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="file-search" class="w-5 h-5 text-emerald-600"></i> Revisión de Nuevos Documentos
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                        <th class="px-6 py-3 border-b">Documento</th>
                                        <th class="px-6 py-3 border-b">Investigador</th>
                                        <th class="px-6 py-3 border-b">Fecha Envío</th>
                                        <th class="px-6 py-3 border-b text-right">Gobernanza</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php
                                    $pDocs = $pdo->query("SELECT d.*, u.nombre as autor_nombre FROM documentos_biblioteca d JOIN usuarios_biblioteca u ON d.id_autor = u.id WHERE d.estado_publicacion = 'pendiente' ORDER BY d.fecha_subida ASC")->fetchAll();
                                    if (empty($pDocs)):
                                    ?>
                                    <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400">Todo al día. No hay documentos pendientes de revisión.</td></tr>
                                    <?php else: foreach($pDocs as $d): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-12 bg-slate-100 rounded border border-slate-200 flex items-center justify-center shrink-0">
                                                    <?php if($d['imagen_portada']): ?><img src="<?= $d['imagen_portada'] ?>" class="w-full h-full object-cover"><?php else: ?><i data-lucide="file-text" class="w-5 h-5 text-slate-300"></i><?php endif; ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-900"><?= htmlspecialchars($d['titulo']) ?></p>
                                                    <button onclick="openDocumentViewer('<?= $d['archivo_documento'] ?>', '<?= addslashes($d['titulo']) ?>')" class="text-[9px] text-blue-600 font-bold hover:underline uppercase">REVISAR CONTENIDO</button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-slate-600"><?= htmlspecialchars($d['autor_nombre']) ?></td>
                                        <td class="px-6 py-4 text-xs text-slate-500"><?= date('d/m/Y', strtotime($d['fecha_subida'])) ?></td>
                                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                                            <a href="admin_biblioteca.php?modulo=documentos&accion=aprobar_doc&id=<?= $d['id'] ?>" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700">Aprobar</a>
                                            <a href="admin_biblioteca.php?modulo=documentos&accion=rechazar_doc&id=<?= $d['id'] ?>" onclick="return confirm('¿Rechazar?')" class="px-3 py-1.5 bg-red-50 text-red-600 text-xs font-bold rounded-lg hover:bg-red-100 transition-colors">Rechazar</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($modulo === 'usuarios'): ?>
                    <!-- USUARIOS CONTENT (Gestión Integral) -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div>
                                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                        <i data-lucide="users" class="w-5 h-5 text-blue-600"></i> Gestión de Usuarios de Biblioteca
                                    </h3>
                                    <p class="text-[11px] text-slate-500 mt-1">Supervisión de toda la comunidad registrada</p>
                                </div>
                                
                                <!-- Filtros de Estado -->
                                <div class="flex bg-slate-100 p-1 rounded-xl">
                                    <?php 
                                        $f = $_GET['f'] ?? 'todos';
                                        $filterOptions = [
                                            'todos' => 'Todos',
                                            'pendiente' => 'Pendientes',
                                            'activo' => 'Activos',
                                            'rechazado' => 'Rechazados'
                                        ];
                                        foreach($filterOptions as $val => $lab):
                                            $isActiveF = ($f === $val);
                                    ?>
                                    <a href="admin_biblioteca.php?modulo=usuarios&f=<?= $val ?>" 
                                       class="px-4 py-1.5 text-[10px] font-bold uppercase rounded-lg transition-all <?= $isActiveF ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' ?>">
                                        <?= $lab ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                        <th class="px-6 py-3 border-b">Usuario</th>
                                        <th class="px-6 py-3 border-b">Perfil / Rol</th>
                                        <th class="px-6 py-3 border-b">Estado</th>
                                        <th class="px-6 py-3 border-b">Registro / Aprobación</th>
                                        <th class="px-6 py-3 border-b text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php
                                    $whereF = "";
                                    if ($f !== 'todos') {
                                        $whereF = "WHERE estado = '$f'";
                                    }

                                    // Paginación
                                    $por_pagina = 10;
                                    $pagina_actual = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                                    if ($pagina_actual < 1) $pagina_actual = 1;
                                    $offset = ($pagina_actual - 1) * $por_pagina;

                                    $total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios_biblioteca $whereF")->fetchColumn();
                                    $total_paginas = ceil($total_usuarios / $por_pagina);

                                    $todos = $pdo->query("SELECT * FROM usuarios_biblioteca $whereF ORDER BY id DESC LIMIT $por_pagina OFFSET $offset")->fetchAll();
                                    
                                    if (empty($todos)):
                                    ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                            <i data-lucide="user-minus" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                                            <p>No se encontraron usuarios con este filtro.</p>
                                        </td>
                                    </tr>
                                    <?php else: foreach($todos as $u): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs uppercase">
                                                    <?= substr($u['nombre'], 0, 1) ?>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($u['nombre']) ?></div>
                                                    <div class="text-[10px] text-slate-500 font-medium"><?= htmlspecialchars($u['correo']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-[10px] text-slate-600 font-semibold capitalize"><?= $u['tipo_usuario'] ?></span>
                                                <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[9px] font-bold uppercase w-fit">
                                                    <?= $u['rol'] ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if($u['estado'] === 'activo'): ?>
                                                <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full text-[9px] font-bold uppercase flex items-center w-fit gap-1">
                                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Activo
                                                </span>
                                            <?php elseif($u['estado'] === 'pendiente'): ?>
                                                <span class="bg-amber-100 text-amber-700 px-2 py-1 rounded-full text-[9px] font-bold uppercase flex items-center w-fit gap-1">
                                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span> Pendiente
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-[9px] font-bold uppercase flex items-center w-fit gap-1 text-red-500">
                                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Rechazado
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-[10px] text-slate-500">Reg: <?= date('d/m/Y', strtotime($u['fecha_creacion'])) ?></div>
                                            <div class="text-[10px] text-slate-400 italic">Apr: <?= $u['fecha_aprobacion'] ? date('d/m/Y', strtotime($u['fecha_aprobacion'])) : '-' ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-2">
                                                <a href="modificar_usuario.php?edit=<?= $u['id'] ?>" 
                                                   class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center gap-2 text-[10px] font-bold uppercase shadow-sm border border-blue-100" 
                                                   title="Modificar Perfil">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Modificar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Controles de Paginación -->
                        <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                        <div class="px-6 py-4 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-50/30">
                            <span class="text-xs text-slate-500 font-medium tracking-tight">
                                Mostrando <?= ($offset + 1) ?> a <?= min($offset + count($todos), $total_usuarios) ?> de <?= $total_usuarios ?> usuarios
                            </span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <?php if ($pagina_actual > 1): ?>
                                    <a href="admin_biblioteca.php?modulo=usuarios&f=<?= $f ?>&p=<?= $pagina_actual - 1 ?>" class="px-3 py-1.5 border border-slate-200 bg-white text-slate-600 rounded-lg text-[11px] font-bold hover:bg-slate-50 shadow-sm transition-colors">&laquo; Anterior</a>
                                <?php endif; ?>
                                
                                <?php 
                                // Rango logico para evitar ensanchamiento de UI
                                $start_page = max(1, $pagina_actual - 2);
                                $end_page = min($total_paginas, $pagina_actual + 2);
                                
                                if ($start_page > 1) {
                                    echo '<a href="admin_biblioteca.php?modulo=usuarios&f=' . $f . '&p=1" class="px-3 py-1.5 border border-slate-200 bg-white text-slate-600 rounded-lg text-[11px] font-bold hover:bg-slate-50 shadow-sm transition-colors">1</a>';
                                    if ($start_page > 2) echo '<span class="px-2 text-slate-400 font-black">...</span>';
                                }

                                for ($i = $start_page; $i <= $end_page; $i++): 
                                ?>
                                    <a href="admin_biblioteca.php?modulo=usuarios&f=<?= $f ?>&p=<?= $i ?>" class="px-3 py-1.5 border <?= $i === $pagina_actual ? 'border-blue-500 bg-blue-50 text-blue-700 ring-2 ring-blue-100/50' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' ?> rounded-lg text-[11px] font-bold transition-all shadow-sm"><?= $i ?></a>
                                <?php endfor; 
                                
                                if ($end_page < $total_paginas) {
                                    if ($end_page < $total_paginas - 1) echo '<span class="px-2 text-slate-400 font-black">...</span>';
                                    echo '<a href="admin_biblioteca.php?modulo=usuarios&f=' . $f . '&p=' . $total_paginas . '" class="px-3 py-1.5 border border-slate-200 bg-white text-slate-600 rounded-lg text-[11px] font-bold hover:bg-slate-50 shadow-sm transition-colors">' . $total_paginas . '</a>';
                                }
                                ?>

                                <?php if ($pagina_actual < $total_paginas): ?>
                                    <a href="admin_biblioteca.php?modulo=usuarios&f=<?= $f ?>&p=<?= $pagina_actual + 1 ?>" class="px-3 py-1.5 border border-slate-200 bg-white text-slate-600 rounded-lg text-[11px] font-bold hover:bg-slate-50 shadow-sm transition-colors">Siguiente &raquo;</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($modulo === 'configuracion'): ?>
                    <!-- CONFIGURACIÓN DEL SISTEMA -->
                    <div class="w-full space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div class="flex flex-col lg:flex-row gap-8">
                            
                            <!-- Columna Principal: Ajustes -->
                            <div class="flex-grow lg:w-2/3 space-y-8">
                                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                                    <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                                        <h3 class="font-bold text-slate-900 flex items-center gap-3 text-lg">
                                            <i data-lucide="settings-2" class="w-6 h-6 text-blue-600"></i> Ajustes de la Biblioteca CIATA
                                        </h3>
                                        <p class="text-sm text-slate-500 mt-1">Personaliza la experiencia visual y funcional del portal público.</p>
                                    </div>
                                    
                                    <form method="POST" class="p-8 space-y-8">
                                        <input type="hidden" name="guardar_config" value="1">
                                        
                                        <!-- Sección: Interfaz Pública -->
                                        <div class="space-y-6">
                                            <div class="flex items-center justify-between p-8 bg-slate-50 rounded-2xl border border-slate-100 hover:border-blue-200 transition-all group">
                                                <div class="flex items-start gap-5">
                                                    <div class="w-14 h-14 bg-white rounded-2xl shadow-sm flex items-center justify-center text-blue-600 border border-slate-100 group-hover:scale-110 transition-transform">
                                                        <i data-lucide="bot" class="w-7 h-7"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-slate-900 text-lg">Botón Flotante CIATA (Asistente AI)</h4>
                                                        <p class="text-sm text-slate-500 mt-1 max-w-xl leading-relaxed">Controla la visibilidad del chatbot inteligente "Pregúntale al CIATA". Al desactivarlo, se removerán todos los scripts y elementos visuales relacionados con la IA en la biblioteca pública.</p>
                                                    </div>
                                                </div>
                                                
                                                <?php 
                                                    $showBtn = '1';
                                                    try {
                                                        $st = $pdo->query("SELECT valor FROM site_config WHERE clave = 'show_ciata_button'");
                                                        $row = $st->fetch();
                                                        if ($row) $showBtn = $row['valor'];
                                                    } catch (Exception $e) {}
                                                ?>
                                                <div class="flex items-center gap-4 bg-white p-4 rounded-2xl border border-slate-200">
                                                    <span class="text-xs font-bold uppercase tracking-wider <?= $showBtn === '1' ? 'text-emerald-600' : 'text-slate-400' ?>">
                                                        <?= $showBtn === '1' ? 'Activo' : 'Inactivo' ?>
                                                    </span>
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" name="show_ciata_button" value="1" class="sr-only peer" <?= $showBtn === '1' ? 'checked' : '' ?>>
                                                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-8 border-t border-slate-100 flex justify-end">
                                            <button type="submit" class="bg-blue-900 text-white px-10 py-4 rounded-2xl font-bold hover:bg-black transition-all shadow-xl shadow-blue-900/20 active:scale-95 flex items-center gap-3 text-lg">
                                                <i data-lucide="save" class="w-6 h-6"></i> Guardar Configuración
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Columna Lateral: Información y Estado -->
                            <div class="lg:w-1/3 space-y-6">
                                <div class="bg-blue-900 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl shadow-blue-900/20">
                                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                                    <div class="relative z-10">
                                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-6">
                                            <i data-lucide="info" class="w-6 h-6 text-white"></i>
                                        </div>
                                        <h4 class="text-xl font-bold mb-3">Información del Sistema</h4>
                                        <p class="text-blue-100 text-sm leading-relaxed mb-6">
                                            Los cambios realizados en esta sección se aplican de forma global e instantánea. Recuerde verificar la biblioteca pública después de guardar.
                                        </p>
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-3 text-xs bg-black/20 p-3 rounded-xl border border-white/10">
                                                <i data-lucide="database" class="w-4 h-4 text-blue-300"></i>
                                                <span>Base de Datos: Sincronizada</span>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs bg-black/20 p-3 rounded-xl border border-white/10">
                                                <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
                                                <span>Cache: Limpieza automática activa</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm">
                                    <h4 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                                        <i data-lucide="help-circle" class="w-5 h-5 text-slate-400"></i> Ayuda Rápida
                                    </h4>
                                    <ul class="space-y-4">
                                        <li class="flex items-start gap-3">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">1</div>
                                            <p class="text-xs text-slate-500 leading-relaxed">Marque o desmarque los interruptores según la función deseada.</p>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">2</div>
                                            <p class="text-xs text-slate-500 leading-relaxed">Presione "Guardar Configuración" para persistir los cambios.</p>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">3</div>
                                            <p class="text-xs text-slate-500 leading-relaxed">Verifique en modo incógnito si desea ver el cambio inmediato sin cache local.</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>

                <?php elseif ($modulo === 'manual'): ?>
                    <div class="max-w-7xl mx-auto">
                        <?php include 'admin_manual_biblioteca.php'; ?>
                    </div>
                <?php else: ?>
                    <div class="p-12 text-center text-slate-400">
                        <i data-lucide="help-circle" class="w-12 h-12 mx-auto mb-4 opacity-20"></i>
                        <p>Seleccione un módulo válido del menú lateral.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
    </div>


    <script>
        lucide.createIcons();


        // Biblioteca Search Logic
        (function() {
            const input = document.getElementById('bib-search');
            const clearBtn = document.getElementById('clear-bib-search');
            const tbody = document.getElementById('bib-tbody');
            const countEl = document.getElementById('docs-count-total');
            const paginationEl = document.getElementById('bib-pagination');
            let debounce;

            function escapeHTML(str) {
                return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }

            let currentBibStatus = 'todos';
            let currentBibType = 'todos';

            window.changeBibTypeFilter = function(type) {
                currentBibType = type;
                
                // Update UI: Toggle classes on type buttons
                document.querySelectorAll('.bib-type-btn').forEach(btn => {
                    const isActive = btn.dataset.type === type;
                    if (isActive) {
                        btn.classList.add('bg-slate-900', 'text-white', 'border-slate-900', 'shadow-md', 'shadow-slate-900/10');
                        btn.classList.remove('bg-white', 'text-slate-600', 'border-slate-200', 'hover:border-slate-300');
                    } else {
                        btn.classList.remove('bg-slate-900', 'text-white', 'border-slate-900', 'shadow-md', 'shadow-slate-900/10');
                        btn.classList.add('bg-white', 'text-slate-600', 'border-slate-200', 'hover:border-slate-300');
                    }
                });

                // Trigger Search
                window.searchBiblioteca(input.value.trim(), 1);
            };

            window.changeBibFilter = function(status) {
                currentBibStatus = status;
                
                // Update UI: Toggle classes on buttons
                document.querySelectorAll('.bib-filter-btn').forEach(btn => {
                    const isActive = btn.dataset.status === status;
                    if (isActive) {
                        btn.classList.add('bg-white', 'text-emerald-900', 'shadow-sm');
                        btn.classList.remove('text-slate-500', 'hover:text-slate-700');
                    } else {
                        btn.classList.remove('bg-white', 'text-emerald-900', 'shadow-sm');
                        btn.classList.add('text-slate-500', 'hover:text-slate-700');
                    }
                });

                // Trigger Search
                window.searchBiblioteca(input.value.trim(), 1);
            };

            window.searchBiblioteca = function(q, p = 1) {
                fetch(`api_buscar_biblioteca.php?q=${encodeURIComponent(q)}&p=${p}&status=${currentBibStatus}&type=${currentBibType}`)
                    .then(r => r.json())
                    .then(data => {
                        // Render Rows
                        if (data.results.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">No se encontraron resultados para "${escapeHTML(q)}"</td></tr>`;
                            paginationEl.innerHTML = '';
                            return;
                        }
                        
                        tbody.innerHTML = data.results.map(d => `
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-10 bg-slate-100 rounded border border-slate-200 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                                            ${d.imagen_portada ? `<img src="${d.imagen_portada}" class="w-full h-full object-cover">` : `<i data-lucide="file-text" class="w-4 h-4 text-slate-300"></i>`}
                                        </div>
                                        <div class="text-xs font-bold text-slate-900">${escapeHTML(d.titulo)}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase transition-all
                                        ${d.tipo === 'articulo' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 
                                          d.tipo === 'tesis' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-purple-50 text-purple-600 border border-purple-100'}">
                                        ${escapeHTML(d.tipo)}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 font-medium">${escapeHTML(d.autor_nombre)}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[8px] font-extrabold uppercase 
                                        ${d.estado_publicacion === 'publicado' ? 'bg-emerald-100 text-emerald-700' : 
                                          d.estado_publicacion === 'borrador' ? 'bg-slate-100 text-slate-600' : 
                                          d.estado_publicacion === 'suspendido' ? 'bg-amber-100 text-amber-700' :
                                          d.estado_publicacion === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'}">
                                        ${d.estado_publicacion}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="modificar_articulo.php?edit=${d.id}" 
                                                class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all" 
                                                title="Modificar Documento">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </a>
                                        <button onclick="openDocumentViewer('${d.archivo_documento}', '${d.titulo.replace(/'/g, "\\'")}')" 
                                                class="p-2 bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-200 transition-all" 
                                                title="Ver Documento">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('');

                        // Render Pagination
                        if (data.total_pages > 1) {
                            const start = ((data.current_page - 1) * 10) + 1;
                            const end = Math.min(data.current_page * 10, data.total);
                            
                            let pagHTML = `
                                <span class="text-[11px] text-slate-500">
                                    Mostrando <strong>${start}</strong> - <strong>${end}</strong> de <strong>${data.total}</strong>
                                </span>
                                <div class="flex gap-1">`;
                            
                            if (data.current_page > 1) {
                                pagHTML += `<button onclick="window.searchBiblioteca('${escapeHTML(q)}', ${data.current_page - 1})" class="px-3 py-1 bg-white border border-slate-200 rounded-md text-[10px] font-bold text-slate-600 hover:bg-emerald-50">&laquo; Anterior</button>`;
                            }

                            pagHTML += `<div class="hidden sm:flex gap-1">`;
                            for(let i=1; i<=data.total_pages; i++) {
                                pagHTML += `<button onclick="window.searchBiblioteca('${escapeHTML(q)}', ${i})" class="px-3 py-1 ${i === data.current_page ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600'} border border-slate-200 rounded-md text-[10px] font-bold hover:bg-emerald-50">${i}</button>`;
                            }
                            pagHTML += `</div>`;

                            if (data.current_page < data.total_pages) {
                                pagHTML += `<button onclick="window.searchBiblioteca('${escapeHTML(q)}', ${data.current_page + 1})" class="px-3 py-1 bg-white border border-slate-200 rounded-md text-[10px] font-bold text-slate-600 hover:bg-emerald-50">Siguiente &raquo;</button>`;
                            }
                            pagHTML += `</div>`;
                            paginationEl.innerHTML = pagHTML;
                        } else {
                            paginationEl.innerHTML = '';
                        }

                        countEl.textContent = data.total;
                        lucide.createIcons();
                    });
            };

            if (input) {
                input.addEventListener('input', function() {
                    const q = this.value.trim();
                    clearBtn.classList.toggle('hidden', q === '');
                    clearTimeout(debounce);
                    debounce = setTimeout(() => window.searchBiblioteca(q, 1), 300);
                });

                clearBtn.addEventListener('click', function() {
                    input.value = '';
                    this.classList.add('hidden');
                    window.searchBiblioteca('', 1);
                });
            }
        })();
    </script>

    <!-- ============================================== -->
    <!-- SECURE DOCUMENT VIEWER MODAL (PDF.js)        -->
    <!-- ============================================== -->
    <div id="documentViewerModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 md:p-6 lg:p-10">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="closeDocumentViewer()"></div>
        
        <!-- Modal Content -->
        <div class="bg-white w-full max-w-6xl h-full rounded-2xl shadow-2xl overflow-hidden relative flex flex-col animate-in fade-in zoom-in duration-300">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-blue-900 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-900/20">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 id="viewerTitle" class="font-bold text-slate-900 text-sm md:text-base line-clamp-1">Documento</h3>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3 h-3 text-green-500"></i> Vista Protegida Administrativa
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <a id="downloadPDFBtn" href="#" download class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-full transition-all group" title="Descargar PDF Original">
                        <i data-lucide="download" class="w-6 h-6"></i>
                    </a>
                    <button onclick="closeDocumentViewer()" class="p-2 hover:bg-slate-100 rounded-full transition-colors group">
                        <i data-lucide="x" class="w-6 h-6 text-slate-400 group-hover:text-slate-600"></i>
                    </button>
                </div>
            </div>
            
            <!-- Viewer Body -->
            <div class="flex-grow bg-slate-100 relative overflow-y-auto custom-scrollbar" id="modal-scroll-container">
                <div id="pdf-render-container" class="flex flex-col items-center p-6 md:p-10" oncontextmenu="return false;">
                    <!-- PDF pages will spend here -->
                    <div id="pdf-loader" class="flex flex-col items-center justify-center py-32 text-blue-900">
                        <div class="animate-spin rounded-full h-14 w-14 border-b-2 border-blue-900 mb-6"></div>
                        <p class="text-sm font-bold uppercase tracking-widest text-slate-500">Procesando Documento...</p>
                        <p class="text-xs text-slate-400 mt-2">Preparando entorno de lectura segura</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Responsive Sidebar Toggle
        const menuBtn = document.querySelector('button.md\\:hidden');
        if (menuBtn) {
            menuBtn.addEventListener('click', () => {
                const sidebar = document.querySelector('aside');
                sidebar.classList.toggle('hidden');
                sidebar.classList.toggle('fixed');
                sidebar.classList.toggle('inset-y-0');
                sidebar.classList.toggle('left-0');
            });
        }
        
        // VISOR DE DOCUMENTOS PROTEGIDO (PDF.js)
        async function openDocumentViewer(url, title = "Documento") {
            const modal = document.getElementById('documentViewerModal');
            const container = document.getElementById('pdf-render-container');
            const loader = document.getElementById('pdf-loader');
            const scrollContainer = document.getElementById('modal-scroll-container');
            const viewerTitle = document.getElementById('viewerTitle');
            
            if (viewerTitle) viewerTitle.textContent = title;
            const downloadBtn = document.getElementById('downloadPDFBtn');
            if (downloadBtn) {
                downloadBtn.href = url;
                // Intentar poner un nombre de archivo amigable
                const fileName = title.replace(/[^a-z0-9]/gi, '_').toLowerCase() + ".pdf";
                downloadBtn.setAttribute('download', fileName);
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden'; 
            scrollContainer.scrollTop = 0;
            
            // Forzar renderizado de iconos Lucide (incluyendo el de descarga)
            if (typeof lucide !== 'undefined') lucide.createIcons();

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
                    
                    container.appendChild(canvas);
                    
                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;
                }
            } catch (error) {
                console.error('Error al cargar PDF:', error);
                loader.innerHTML = `
                    <div class="bg-red-50 p-6 rounded-2xl border border-red-100 flex flex-col items-center">
                        <i data-lucide="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
                        <p class="text-sm font-bold text-red-600 uppercase tracking-wider">Error de acceso</p>
                        <p class="text-xs text-red-400 mt-1">No se pudo cargar el documento original</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }

        function closeDocumentViewer() {
            const modal = document.getElementById('documentViewerModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = ''; 
        }
    </script>
</body>
</html>
