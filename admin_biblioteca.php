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
$modulosValidos = ['inicio', 'usuarios', 'solicitudes', 'recursos', 'documentos', 'configuracion'];
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

// Lógica de Edición de Usuario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_usuario') {
    $id = (int)$_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $tipo = $_POST['tipo_usuario'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];
    
    $sql = "UPDATE usuarios_biblioteca SET 
            nombre = ?, 
            correo = ?, 
            tipo_usuario = ?, 
            rol = ?, 
            estado = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $correo, $tipo, $rol, $estado, $id]);
    
    $success_msg = "Perfil de usuario actualizado con éxito.";
}

// Lógica de Edición de Documento (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar_documento') {
    $id = (int)$_POST['id_doc'];
    $titulo = $_POST['titulo'];
    $tipo = $_POST['tipo_doc'] ?? '';
    $resumen = $_POST['resumen'] ?? '';
    $estado = $_POST['estado_publicacion'];
    
    // Obtener datos actuales para mantener archivos si no se suben nuevos
    $stmt_current = $pdo->prepare("SELECT archivo_documento, imagen_portada FROM documentos_biblioteca WHERE id = ?");
    $stmt_current->execute([$id]);
    $current = $stmt_current->fetch();
    
    $rutaArchivo = $current['archivo_documento'];
    $rutaPortada = $current['imagen_portada'];
    
    // Procesar nuevo PDF
    if (!empty($_FILES['nuevo_pdf']['name'])) {
        $dirDocs = "uploads/biblioteca/documentos/";
        if (!is_dir($dirDocs)) mkdir($dirDocs, 0777, true);
        $ext = pathinfo($_FILES['nuevo_pdf']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) === 'pdf') {
            $nombreArchivo = time() . "_" . bin2hex(random_bytes(4)) . ".pdf";
            if (move_uploaded_file($_FILES['nuevo_pdf']['tmp_name'], $dirDocs . $nombreArchivo)) {
                $rutaArchivo = $dirDocs . $nombreArchivo;
            }
        }
    }
    
    // Procesar nueva Portada
    if (!empty($_FILES['nueva_portada']['name'])) {
        $dirPorts = "uploads/biblioteca/portadas/";
        if (!is_dir($dirPorts)) mkdir($dirPorts, 0777, true);
        $ext = pathinfo($_FILES['nueva_portada']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $nombrePortada = time() . "_p_" . bin2hex(random_bytes(4)) . "." . $ext;
            if (move_uploaded_file($_FILES['nueva_portada']['tmp_name'], $dirPorts . $nombrePortada)) {
                $rutaPortada = $dirPorts . $nombrePortada;
            }
        }
    }

    $sql = "UPDATE documentos_biblioteca SET 
            titulo = ?, 
            tipo = ?, 
            resumen = ?, 
            archivo_documento = ?,
            imagen_portada = ?,
            estado_publicacion = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titulo, $tipo, $resumen, $rutaArchivo, $rutaPortada, $estado, $id]);
    
    $success_msg = "Documento y archivos actualizados correctamente.";
}
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
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Biblioteca</h3>
            
            <a href="admin_biblioteca.php?modulo=inicio" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'inicio' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Dashboard</span>
            </a>

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
                <span class="font-medium text-sm">Revisión de Tesis</span>
            </a>

            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Comunidad</h3>
            
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

            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Sistema</h3>

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
                            <h3 class="text-slate-500 text-sm font-medium">Libros y Tesis</h3>
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
                            
                            <div class="flex items-center gap-3 w-full md:w-auto">
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
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                        <th class="px-6 py-3 border-b">Documento</th>
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
                                        <td class="px-6 py-4 text-xs text-slate-600 font-medium"><?= htmlspecialchars($d['autor_nombre']) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 rounded-full text-[8px] font-extrabold uppercase 
                                                <?= $d['estado_publicacion'] === 'publicado' ? 'bg-emerald-100 text-emerald-700' : 
                                                   ($d['estado_publicacion'] === 'borrador' ? 'bg-slate-100 text-slate-500' : 'bg-amber-100 text-amber-700') ?>">
                                                <?= $d['estado_publicacion'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button onclick='openEditDocModal(<?= json_encode($d) ?>)' 
                                                        class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all" 
                                                        title="Modificar Metadatos">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <a href="<?= $d['archivo_documento'] ?>" target="_blank" 
                                                   class="p-2 bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-200 transition-all" 
                                                   title="Ver Documento">
                                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                </a>
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
                                                    <a href="<?= $d['archivo_documento'] ?>" target="_blank" class="text-[9px] text-blue-600 font-bold hover:underline">REVISAR CONTENIDO</a>
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
                                    $todos = $pdo->query("SELECT * FROM usuarios_biblioteca $whereF ORDER BY id DESC")->fetchAll();
                                    
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
                                                <button onclick='openEditUserModal(<?= json_encode($u) ?>)' 
                                                        class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center gap-2 text-[10px] font-bold uppercase shadow-sm border border-blue-100" 
                                                        title="Modificar Perfil">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Modificar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- OTHERS PLACEHOLDER -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 text-center">
                        <i data-lucide="construction" class="w-16 h-16 text-slate-200 mx-auto mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Módulo en Desarrollo</h3>
                        <p class="text-slate-500">Estamos trabajando para integrar la gestión de <?= $modulo ?> en el panel administrativo.</p>
                        <a href="admin_biblioteca.php?modulo=inicio" class="inline-block mt-6 text-blue-600 font-bold hover:underline">Volver al Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
    </div>

    <!-- ============================================== -->
    <!-- EDIT USER MODAL                              -->
    <!-- ============================================== -->
    <div id="editUserModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeEditUserModal()"></div>
        
        <!-- Modal Content -->
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden relative flex flex-col animate-in fade-in zoom-in duration-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-cog" class="w-5 h-5 text-blue-600"></i> Editar Usuario
                </h3>
                <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="admin_biblioteca.php?modulo=usuarios<?= isset($f) ? "&f=$f" : '' ?>" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="editar_usuario">
                <input type="hidden" name="id_usuario" id="edit_id">
                
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Nombre Completo</label>
                    <input type="text" name="nombre" id="edit_nombre" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Correo Electrónico</label>
                    <input type="email" name="correo" id="edit_correo" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none text-slate-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Perfil</label>
                        <select name="tipo_usuario" id="edit_tipo" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            <option value="membresia">Membresía de Biblioteca</option>
                            <option value="investigador">Investigador</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Rol de Acceso</label>
                        <select name="rol" id="edit_rol" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                            <option value="usuario">Usuario Estándar</option>
                            <option value="investigador">Investigador Senior</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Estado de la cuenta</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="estado" value="pendiente" id="edit_status_pendiente" class="peer sr-only">
                            <div class="px-3 py-2 text-center text-[10px] font-bold rounded-lg border border-slate-200 bg-white text-slate-600 peer-checked:bg-amber-50 peer-checked:border-amber-500 peer-checked:text-amber-700 transition-all">ESPERA</div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="estado" value="activo" id="edit_status_activo" class="peer sr-only">
                            <div class="px-3 py-2 text-center text-[10px] font-bold rounded-lg border border-slate-200 bg-white text-slate-600 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 transition-all">ACTIVO</div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="estado" value="rechazado" id="edit_status_rechazado" class="peer sr-only">
                            <div class="px-3 py-2 text-center text-[10px] font-bold rounded-lg border border-slate-200 bg-white text-slate-600 peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-700 transition-all">RECHAZO</div>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeEditUserModal()" class="flex-1 py-3 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Cancelar</button>
                    <button type="submit" class="flex-2 px-8 py-3 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openEditUserModal(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_nombre').value = user.nombre;
            document.getElementById('edit_correo').value = user.correo;
            document.getElementById('edit_tipo').value = user.tipo_usuario;
            document.getElementById('edit_rol').value = user.rol;
            
            // Radio buttons for status
            if(user.estado === 'pendiente') document.getElementById('edit_status_pendiente').checked = true;
            if(user.estado === 'activo') document.getElementById('edit_status_activo').checked = true;
            if(user.estado === 'rechazado') document.getElementById('edit_status_rechazado').checked = true;
            
            const modal = document.getElementById('editUserModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            if(typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeEditUserModal() {
            const modal = document.getElementById('editUserModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        // ============================================== 
        // EDIT DOCUMENT MODAL LOGIC                   
        // ============================================== 
        function openEditDocModal(doc) {
            document.getElementById('edit_doc_id').value = doc.id;
            document.getElementById('edit_doc_titulo').value = doc.titulo;
            document.getElementById('edit_doc_tipo').value = doc.tipo || '';
            document.getElementById('edit_doc_resumen').value = doc.resumen || '';
            document.getElementById('edit_doc_status').value = doc.estado_publicacion;
            
            // Limpiar inputs de archivo
            document.getElementById('edit_doc_pdf').value = '';
            document.getElementById('edit_doc_portada').value = '';
            
            const modal = document.getElementById('editDocModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            if(typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeEditDocModal() {
            const modal = document.getElementById('editDocModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

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

            window.searchBiblioteca = function(q, p = 1) {
                fetch(`api_buscar_biblioteca.php?q=${encodeURIComponent(q)}&p=${p}`)
                    .then(r => r.json())
                    .then(data => {
                        // Render Rows
                        if (data.results.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">No se encontraron resultados para "${escapeHTML(q)}"</td></tr>`;
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
                                <td class="px-6 py-4 text-xs text-slate-600 font-medium">${escapeHTML(d.autor_nombre)}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[8px] font-extrabold uppercase 
                                        ${d.estado_publicacion === 'publicado' ? 'bg-emerald-100 text-emerald-700' : 
                                          (d.estado_publicacion === 'borrador' ? 'bg-slate-100 text-slate-500' : 'bg-amber-100 text-amber-700')}">
                                        ${d.estado_publicacion}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick='openEditDocModal(${JSON.stringify(d)})' 
                                                class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all" 
                                                title="Modificar Metadatos">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <a href="${d.archivo_documento}" target="_blank" 
                                           class="p-2 bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-200 transition-all" 
                                           title="Ver Documento">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        </a>
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

        // Responsive Sidebar Toggle
        document.querySelector('button.md\\:hidden').addEventListener('click', () => {
             const sidebar = document.querySelector('aside');
             sidebar.classList.toggle('hidden');
             sidebar.classList.toggle('fixed');
             sidebar.classList.toggle('inset-y-0');
             sidebar.classList.toggle('left-0');
        });
    </script>
    <!-- ============================================== -->
    <!-- EDIT DOCUMENT MODAL                         -->
    <!-- ============================================== -->
    <div id="editDocModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeEditDocModal()"></div>
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative flex flex-col animate-in fade-in zoom-in duration-200">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-emerald-50/30">
                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="file-edit" class="w-5 h-5 text-emerald-600"></i> Editar Documento
                </h3>
                <button onclick="closeEditDocModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <form action="admin_biblioteca.php?modulo=recursos" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                <input type="hidden" name="action" value="editar_documento">
                <input type="hidden" name="id_doc" id="edit_doc_id">
                
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Título del Documento</label>
                    <input type="text" name="titulo" id="edit_doc_titulo" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Tipo de Recurso</label>
                        <select name="tipo_doc" id="edit_doc_tipo" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                            <option value="articulo">Artículo Científico</option>
                            <option value="tesis">Tesis Académica</option>
                            <option value="acervo">Acervo General</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Estado de Publicación</label>
                        <select name="estado_publicacion" id="edit_doc_status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 transition-all outline-none">
                            <option value="publicado">Publicado (Visible)</option>
                            <option value="borrador">Borrador (Oculto)</option>
                            <option value="rechazado">Rechazado (Oculto)</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Descripción / Resumen</label>
                    <textarea name="resumen" id="edit_doc_resumen" rows="4" 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 transition-all outline-none resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Reemplazar Portada (Imagen)</label>
                        <input type="file" name="nueva_portada" id="edit_doc_portada" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 ml-1">Reemplazar PDF</label>
                        <input type="file" name="nuevo_pdf" id="edit_doc_pdf" accept=".pdf"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all">
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeEditDocModal()" class="flex-1 py-3 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Cancelar</button>
                    <button type="submit" class="flex-2 px-8 py-3 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-all">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
