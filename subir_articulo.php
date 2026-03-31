<?php
session_start();
if (!isset($_SESSION['user_bib_id']) || !in_array($_SESSION['user_bib_rol'] ?? '', ['admin', 'investigador'])) {
    header("Location: login_biblioteca.php");
    exit;
}

require_once 'conexion.php';

$userId = $_SESSION['user_bib_id'];
$userRol = $_SESSION['user_bib_rol'];
$modulo = $_GET['modulo'] ?? 'inicio';
$id_edit = $_GET['edit'] ?? null;

// Si estamos editando, cargar el documento
$doc_edit = null;
if ($id_edit) {
    $stmt = $pdo->prepare("SELECT * FROM documentos_biblioteca WHERE id = ?");
    $stmt->execute([$id_edit]);
    $doc_edit = $stmt->fetch();
    
    // Verificar que el documento exista y sea del autor (o que sea admin)
    if (!$doc_edit || ($doc_edit['id_autor'] != $userId && $userRol !== 'admin')) {
        header("Location: subir_articulo.php?modulo=mis_documentos");
        exit;
    }
    
    // Regla de Negocio CIATA: Si está en revisión (pendiente) o publicado, el autor no puede editar
    if (in_array($doc_edit['estado_publicacion'], ['publicado', 'pendiente']) && $userRol !== 'admin') {
        $modulo = 'mis_documentos';
        $error = "Este documento está en revisión o ya ha sido publicado. No puede ser editado por el autor en este estado.";
        $id_edit = null;
    } else {
        $modulo = 'nuevo'; // Usar el mismo formulario para editar
    }
}

// Lógica de Eliminación
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT * FROM documentos_biblioteca WHERE id = ?");
    $stmt->execute([$id_del]);
    $doc_del = $stmt->fetch();
    
    if ($doc_del && ($doc_del['id_autor'] == $userId || $userRol === 'admin')) {
        // REGLA DE NEGOCIO: No permitir eliminar si está publicado (solo admin)
        if ($doc_del['estado_publicacion'] === 'publicado' && $userRol !== 'admin') {
            header("Location: subir_articulo.php?modulo=mis_documentos&error=pub_del");
            exit;
        }

        if ($doc_del['archivo_documento'] && file_exists($doc_del['archivo_documento'])) @unlink($doc_del['archivo_documento']);
        if ($doc_del['imagen_portada'] && file_exists($doc_del['imagen_portada'])) @unlink($doc_del['imagen_portada']);
        
        $pdo->prepare("DELETE FROM documentos_biblioteca WHERE id = ?")->execute([$id_del]);
        header("Location: subir_articulo.php?modulo=mis_documentos&success=del");
        exit;
    }
}

// Capturar mensajes por redirección
if (($_GET['success'] ?? '') === 'del') $success = "Documento eliminado correctamente.";
if (($_GET['error'] ?? '') === 'pub_del') $error = "Los documentos publicados solo pueden ser eliminados por un administrador.";

// --- Lógica de Búsqueda AJAX (Real-time) ---
if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $q = trim($_GET['q'] ?? '');
    $p = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
    $limit = 10;
    $offset = ($p - 1) * $limit;

    $where = "WHERE id_autor = :userId";
    $params = [':userId' => $userId];
    if ($q !== '') {
        $where .= " AND (titulo LIKE :s1 OR tipo LIKE :s2 OR estado_publicacion LIKE :s3)";
        $params[':s1'] = "%$q%";
        $params[':s2'] = "%$q%";
        $params[':s3'] = "%$q%";
    }

    // Contar total
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM documentos_biblioteca $where");
    foreach($params as $key => $val) $countStmt->bindValue($key, $val);
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    $total_pages = ceil($total / $limit);

    // Obtener resultados
    $stmt = $pdo->prepare("SELECT * FROM documentos_biblioteca $where ORDER BY fecha_subida DESC LIMIT $limit OFFSET $offset");
    foreach($params as $key => $val) $stmt->bindValue($key, $val);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'total' => $total,
        'total_pages' => $total_pages,
        'current_page' => $p,
        'results' => $results,
        'user_rol' => $userRol
    ]);
    exit;
}
// --- Fin Lógica AJAX ---

// Pasar el rol al JS
echo "<script>const userRol = '$userRol';</script>";

// Lógica de Subida / Edición
$error = $error ?? '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['subir_documento']) || isset($_POST['editar_documento']))) {
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $resumen = trim($_POST['resumen'] ?? '');
    $palabras_clave = trim($_POST['palabras_clave'] ?? '');
    $estado_post = $_POST['estado_final'] ?? 'borrador'; // borrador o pendiente
    $es_edicion = isset($_POST['editar_documento']);

    // Nuevos campos dinámicos
    $institucion = trim($_POST['institucion'] ?? '');
    $grado = trim($_POST['grado'] ?? '');
    $asesor = trim($_POST['asesor'] ?? '');
    $revista = trim($_POST['revista'] ?? '');
    $issn = trim($_POST['issn'] ?? '');
    $doi = !empty($_POST['doi']) ? trim($_POST['doi']) : null;
    $tipo_material = trim($_POST['tipo_material'] ?? '');
    $categoria_acervo = trim($_POST['categoria_acervo'] ?? '');
    $derechos = trim($_POST['derechos'] ?? '');

    // Validación de DOI Único
    if ($doi) {
        $sqlCheck = "SELECT COUNT(*) FROM documentos_biblioteca WHERE doi = ? " . ($id_edit ? "AND id != ?" : "");
        $stmtCheck = $pdo->prepare($sqlCheck);
        $paramsCheck = [$doi];
        if ($id_edit) $paramsCheck[] = $id_edit;
        $stmtCheck->execute($paramsCheck);
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "El DOI '$doi' ya se encuentra registrado en otro documento de la biblioteca.";
        }
    }
    
    // Validar campos básicos
    if ($error) {
        // Ya hay error previo (ej: DOI duplicado)
    } else if (empty($titulo) || empty($tipo) || empty($resumen)) {
        $error = "El título, tipo y resumen son obligatorios.";
    } else {
        $archivo = $_FILES['archivo'];
        $portada = $_FILES['portada'] ?? null;
        
        // Asegurar directorios
        $dirDocs = "uploads/biblioteca/documentos/";
        $dirPorts = "uploads/biblioteca/portadas/";
        if (!is_dir($dirDocs)) mkdir($dirDocs, 0777, true);
        if (!is_dir($dirPorts)) mkdir($dirPorts, 0777, true);

        $rutaArchivo = $es_edicion ? $doc_edit['archivo_documento'] : '';
        $rutaPortada = $es_edicion ? $doc_edit['imagen_portada'] : null;
        $continuar = true;

        // Procesar nuevo archivo PDF si se subió uno
        if (!empty($archivo['name'])) {
            $extArchivo = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (strtolower($extArchivo) !== 'pdf') {
                $error = "El documento debe ser un archivo PDF.";
                $continuar = false;
            } else {
                $nombreArchivo = time() . "_" . bin2hex(random_bytes(4)) . ".pdf";
                $rutaArchivo = $dirDocs . $nombreArchivo;
                if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                    $error = "Error al subir el archivo PDF.";
                    $continuar = false;
                }
            }
        } elseif (!$es_edicion) {
            $error = "Debes subir un archivo PDF.";
            $continuar = false;
        }

        // Procesar portada si se subió una
        if ($continuar && !empty($portada['name'])) {
            $extPortada = pathinfo($portada['name'], PATHINFO_EXTENSION);
            $allowedImg = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array(strtolower($extPortada), $allowedImg)) {
                $nombrePortada = time() . "_p_" . bin2hex(random_bytes(4)) . "." . $extPortada;
                $rutaPortada = $dirPorts . $nombrePortada;
                move_uploaded_file($portada['tmp_name'], $rutaPortada);
            }
        }

        if ($continuar) {
            if ($es_edicion) {
                // UPDATE
                $sql = "UPDATE documentos_biblioteca SET 
                        titulo = ?, tipo = ?, resumen = ?, palabras_clave = ?,
                        imagen_portada = ?, archivo_documento = ?, estado_publicacion = ?,
                        institucion = ?, grado = ?, asesor = ?,
                        revista = ?, issn = ?, doi = ?,
                        tipo_material = ?, categoria_acervo = ?, derechos = ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([
                    $titulo, $tipo, $resumen, $palabras_clave,
                    $rutaPortada, $rutaArchivo, $estado_post,
                    $institucion, $grado, $asesor,
                    $revista, $issn, $doi,
                    $tipo_material, $categoria_acervo, $derechos,
                    $id_edit
                ])) {
                    $success = "Documento actualizado correctamente.";
                    // Recargar datos si es necesario
                    $stmt = $pdo->prepare("SELECT * FROM documentos_biblioteca WHERE id = ?");
                    $stmt->execute([$id_edit]);
                    $doc_edit = $stmt->fetch();
                } else {
                    $error = "Error al actualizar la base de datos.";
                }
            } else {
                // INSERT
                $sql = "INSERT INTO documentos_biblioteca 
                        (id_autor, tipo, titulo, resumen, palabras_clave, imagen_portada, archivo_documento, estado_publicacion,
                         institucion, grado, asesor, revista, issn, doi, tipo_material, categoria_acervo, derechos) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([
                    $userId, $tipo, $titulo, $resumen, $palabras_clave, $rutaPortada, $rutaArchivo, $estado_post,
                    $institucion, $grado, $asesor, $revista, $issn, $doi, $tipo_material, $categoria_acervo, $derechos
                ])) {
                    $success = "Documento guardado correctamente (" . ($estado_post == 'borrador' ? 'Borrador' : 'Pendiente de revisión') . ").";
                } else {
                    $error = "Error al guardar en la base de datos.";
                }
            }
        }
    }
}

// Clases CSS
$activeClass = "bg-blue-800 text-white border-l-4 border-blue-400";
$inactiveClass = "text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Investigador | CIATA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .toast-notification {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: white;
            padding: 1rem 1.5rem;
            border-left: 4px solid #ef4444;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border-radius: 12px;
            display: flex;
            items-center: center;
            gap: 12px;
            z-index: 1000;
            animation: slideIn 0.3s ease-out forwards;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
    <!-- PDF.js para el visor protegido -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script>
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    </script>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden">

    <!-- Menú Lateral Izquierdo (Sidebar) -->
    <aside class="w-64 bg-gray-900 flex-shrink-0 flex flex-col transition-all duration-300 z-20 shadow-xl hidden md:flex">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-gray-800 bg-gray-950 gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg">
                <i data-lucide="microscope" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-lg tracking-wide leading-tight truncate">CIATA</h1>
                <p class="text-blue-400 text-[10px] uppercase tracking-widest font-bold truncate">Investigador</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto sidebar-scroll py-6 space-y-1">
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 font-bold tracking-widest">Principal</h3>
            
            <a href="subir_articulo.php?modulo=inicio" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $modulo === 'inicio' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Dashboard</span>
            </a>

            <a href="subir_articulo.php?modulo=nuevo" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= ($modulo === 'nuevo' && !$id_edit) ? $activeClass : $inactiveClass ?>">
                <i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Nuevo Documento</span>
            </a>

            <a href="subir_articulo.php?modulo=mis_documentos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= ($modulo === 'mis_documentos' || $id_edit) ? $activeClass : $inactiveClass ?>">
                <i data-lucide="library" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Mis Documentos</span>
            </a>
        </nav>

        <!-- Sección de Manual de Usuario (Fuera del scroll) -->
        <div class="px-4 py-2 border-t border-gray-800 bg-gray-900/50">
            <a href="admin_manual_investigador.php" class="flex items-center px-4 py-3 rounded-lg transition-colors text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent">
                <i data-lucide="book" class="w-5 h-5 mr-3"></i>
                <span class="font-medium text-sm">Manual de Usuario</span>
            </a>
        </div>

        <!-- User bottom part -->
        <div class="p-4 border-t border-gray-800 bg-gray-950">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs shadow-lg">
                        <?= strtoupper(substr($_SESSION['user_bib_nombre'], 0, 1)) ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white leading-none truncate w-24 border-b-transparent"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <a href="logout_biblioteca.php" title="Cerrar Sesión" class="text-gray-400 hover:text-red-400 transition-colors bg-gray-900 p-2 rounded-lg">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 flex-shrink-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-500 hover:text-gray-900">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h2 class="text-xl font-bold text-gray-800 hidden sm:block">
                    <?php 
                        if ($id_edit) echo "Editando: " . htmlspecialchars($doc_edit['titulo']);
                        else {
                            switch($modulo) {
                                case 'inicio': echo "Resumen de Actividad"; break;
                                case 'nuevo': echo "Subir Nuevo Recurso"; break;
                                case 'mis_documentos': echo "Mis Publicaciones"; break;
                            }
                        }
                    ?>
                </h2>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="biblioteca.php" class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full hover:bg-blue-100 transition-colors">
                    <i data-lucide="library" class="w-4 h-4 mr-1.5"></i> Ver Biblioteca
                </a>
                <span class="text-sm text-gray-400 hidden sm:block">|</span>
                <span class="text-sm font-medium text-gray-600 hidden sm:block"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-6xl mx-auto">
                
                <!-- Sub-Cabecera de Navegación -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center">
                        <?php if ($id_edit): ?>
                            <a href="subir_articulo.php?modulo=mis_documentos" class="mr-4 text-gray-400 hover:text-emerald-600 transition-colors p-2 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center">
                                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                            </a>
                        <?php endif; ?>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">
                                <?php 
                                    if ($id_edit) echo "Modificar Documento";
                                    else if ($modulo === 'nuevo') echo "Nuevo Registro";
                                    else echo "Mis Publicaciones";
                                ?>
                            </h2>
                            <p class="text-slate-500 text-sm mt-1">
                                <?php 
                                    if ($id_edit) echo htmlspecialchars($doc_edit['titulo']) . ' <span class="text-xs text-slate-300 ml-2">ID: #' . $doc_edit['id'] . '</span>';
                                    else echo "Portal del Investigador CIATA";
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i><span class="text-sm font-medium"><?= $error ?></span>
                </div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5"></i><span class="text-sm font-medium"><?= $success ?></span>
                </div>
                <?php endif; ?>

                <?php if ($modulo === 'inicio'): 
                    // Consultas para las métricas del dashboard
                    
                    // 1. Documentos Recientes
                    $stmtRecientes = $pdo->prepare("SELECT id, titulo, fecha_subida, estado_publicacion FROM documentos_biblioteca WHERE id_autor = ? ORDER BY fecha_subida DESC LIMIT 5");
                    $stmtRecientes->execute([$userId]);
                    $recientes = $stmtRecientes->fetchAll();

                    // 2. Favoritos
                    $stmtFavoritos = $pdo->prepare("SELECT u.nombre as usuario_nombre, d.titulo, f.fecha_agregado FROM favoritos_biblioteca f JOIN usuarios_biblioteca u ON f.id_usuario = u.id JOIN documentos_biblioteca d ON f.id_documento = d.id WHERE d.id_autor = ? ORDER BY f.fecha_agregado DESC LIMIT 10");
                    $stmtFavoritos->execute([$userId]);
                    $favoritos = $stmtFavoritos->fetchAll();

                    // 3. Vistas
                    $stmtVistas = $pdo->prepare("SELECT u.nombre as usuario_nombre, d.titulo, v.fecha_visto FROM vistos_biblioteca v JOIN usuarios_biblioteca u ON v.id_usuario = u.id JOIN documentos_biblioteca d ON v.id_documento = d.id WHERE d.id_autor = ? ORDER BY v.fecha_visto DESC LIMIT 10");
                    $stmtVistas->execute([$userId]);
                    $vistas = $stmtVistas->fetchAll();
                ?>
                    <!-- DASHBOARD INVESTIGADOR -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                            <h4 class="text-3xl font-bold text-slate-900"><?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE id_autor = $userId")->fetchColumn(); ?></h4>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-1">Total Disp.</p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-2 h-full bg-emerald-500"></div>
                            <h4 class="text-3xl font-bold text-emerald-600"><?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE id_autor = $userId AND estado_publicacion = 'publicado'")->fetchColumn(); ?></h4>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-1">Publicados</p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-2 h-full bg-amber-500"></div>
                            <h4 class="text-3xl font-bold text-amber-500"><?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE id_autor = $userId AND estado_publicacion = 'pendiente'")->fetchColumn(); ?></h4>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-1">En revisión</p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-2 h-full bg-slate-300"></div>
                            <h4 class="text-3xl font-bold text-slate-400"><?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE id_autor = $userId AND estado_publicacion = 'borrador'")->fetchColumn(); ?></h4>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-1">Borradores</p>
                        </div>
                    </div>
                    
                    <!-- MÉTRICAS DETALLADAS: FILA 1 (RECIEENTES) -->
                    <div class="mb-6">
                        <!-- Columna 1: Documentos Recientes -->
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col hover:border-blue-200 transition-colors w-full">
                            <div class="p-5 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm"><i data-lucide="file-clock" class="w-4 h-4 text-blue-500"></i> Documentos Recientes</h3>
                                <a href="subir_articulo.php?modulo=mis_documentos" class="text-xs text-blue-600 font-bold hover:underline">Ver todo</a>
                            </div>
                            <div class="p-0 flex-1 overflow-y-auto custom-scroll max-h-[300px]">
                                <?php if (empty($recientes)): ?>
                                    <div class="p-8 flex flex-col items-center justify-center text-center">
                                        <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3"><i data-lucide="folder-open" class="w-6 h-6 text-slate-300"></i></div>
                                        <p class="text-sm font-medium text-slate-500">No hay documentos</p>
                                        <p class="text-xs text-slate-400 mt-1">Sube tu primer archivo</p>
                                    </div>
                                <?php else: ?>
                                    <ul class="divide-y divide-slate-50">
                                        <?php foreach($recientes as $doc): ?>
                                            <li class="p-4 hover:bg-slate-50/80 transition-colors">
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="flex flex-col gap-1 min-w-0">
                                                        <span class="text-sm font-bold text-slate-700 truncate" title="<?= htmlspecialchars($doc['titulo']) ?>"><?= htmlspecialchars($doc['titulo']) ?></span>
                                                        <span class="text-[10px] text-slate-400 font-medium flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> <?= date('d M Y', strtotime($doc['fecha_subida'])) ?></span>
                                                    </div>
                                                    <div class="flex items-center gap-3 flex-shrink-0">
                                                        <?php 
                                                            $estadoClass = 'bg-slate-100 text-slate-500';
                                                            if($doc['estado_publicacion'] === 'publicado') $estadoClass = 'bg-emerald-100 text-emerald-700';
                                                            if($doc['estado_publicacion'] === 'pendiente') $estadoClass = 'bg-amber-100 text-amber-700';
                                                        ?>
                                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider <?= $estadoClass ?>"><?= $doc['estado_publicacion'] ?></span>
                                                        <a href="subir_articulo.php?edit=<?= $doc['id'] ?>" class="p-1.5 hover:bg-blue-50 text-blue-600 rounded-lg transition-colors">
                                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- MÉTRICAS DETALLADAS: FILA 2 (INTERACCIONES) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                        <!-- Columna 2: Interacciones (Favoritos) -->
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col hover:border-yellow-200 transition-colors">
                            <div class="p-5 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm"><i data-lucide="star" class="w-4 h-4 text-yellow-500 fill-yellow-500"></i> Guardado Por</h3>
                            </div>
                            <div class="p-0 flex-1 overflow-y-auto custom-scroll max-h-[350px]">
                                <?php if (empty($favoritos)): ?>
                                    <div class="p-8 flex flex-col items-center justify-center text-center">
                                        <div class="w-12 h-12 bg-yellow-50 rounded-full flex items-center justify-center mb-3"><i data-lucide="star-off" class="w-6 h-6 text-yellow-200"></i></div>
                                        <p class="text-sm font-medium text-slate-500">Sin favoritos</p>
                                        <p class="text-xs text-slate-400 mt-1">Tus lectores aparecerán aquí</p>
                                    </div>
                                <?php else: ?>
                                    <ul class="divide-y divide-slate-50">
                                        <?php foreach($favoritos as $fav): ?>
                                            <li class="p-4 hover:bg-slate-50/80 transition-colors">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0 mt-0.5 border border-yellow-200"><i data-lucide="user" class="w-4 h-4 text-yellow-600"></i></div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-xs text-slate-800 font-medium leading-tight"><strong class="text-slate-900"><?= htmlspecialchars($fav['usuario_nombre']) ?></strong> se interesó en:</p>
                                                        <p class="text-xs text-slate-500 line-clamp-1 mt-0.5 italic">"<?= htmlspecialchars($fav['titulo']) ?>"</p>
                                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-1.5 flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> <?= date('d M Y, H:i', strtotime($fav['fecha_agregado'])) ?></p>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Columna 3: Visualizaciones -->
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col hover:border-indigo-200 transition-colors">
                            <div class="p-5 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm"><i data-lucide="eye" class="w-4 h-4 text-indigo-500"></i> Vistos Por</h3>
                            </div>
                            <div class="p-0 flex-1 overflow-y-auto custom-scroll max-h-[350px]">
                                <?php if (empty($vistas)): ?>
                                    <div class="p-8 flex flex-col items-center justify-center text-center">
                                        <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mb-3"><i data-lucide="eye-off" class="w-6 h-6 text-indigo-200"></i></div>
                                        <p class="text-sm font-medium text-slate-500">Sin visualizaciones</p>
                                        <p class="text-xs text-slate-400 mt-1">El registro de lecturas está vacío</p>
                                    </div>
                                <?php else: ?>
                                    <ul class="divide-y divide-slate-50">
                                        <?php foreach($vistas as $vista): ?>
                                            <li class="p-4 hover:bg-slate-50/80 transition-colors">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 mt-0.5 border border-indigo-200"><i data-lucide="eye" class="w-4 h-4 text-indigo-600"></i></div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-xs text-slate-800 font-medium leading-tight">Visto por <strong class="text-slate-900"><?= htmlspecialchars($vista['usuario_nombre']) ?></strong>:</p>
                                                        <p class="text-xs text-slate-500 line-clamp-1 mt-0.5 italic">"<?= htmlspecialchars($vista['titulo']) ?>"</p>
                                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-1.5 flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i> <?= date('d M Y, H:i', strtotime($vista['fecha_visto'])) ?></p>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

                <?php if ($modulo === 'nuevo'): ?>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                        <!-- Formulario -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                                <form id="art-form" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                                    <?php if ($id_edit): ?><input type="hidden" name="editar_documento" value="1"><?php else: ?><input type="hidden" name="subir_documento" value="1"><?php endif; ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-sm font-bold text-slate-700">Título</label>
                                            <input type="text" name="titulo" id="in-titulo" required value="<?= htmlspecialchars($doc_edit['titulo'] ?? '') ?>"
                                                class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500/20 outline-none"
                                                oninput="updatePreview()">
                                        </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">Tipo de Documento</label>
                                        <div class="relative custom-select" id="select-tipo">
                                            <input type="hidden" name="tipo" id="input-tipo" value="<?= $doc_edit['tipo'] ?? 'articulo' ?>">
                                            <button type="button" class="select-trigger w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-sm font-medium text-slate-700">
                                                <span class="selected-text"><?= isset($doc_edit['tipo']) ? ($doc_edit['tipo'] == 'tesis' ? 'Tesis Académica' : ($doc_edit['tipo'] == 'acervo' ? 'Acervo General' : 'Artículo Científico')) : 'Artículo Científico' ?></span>
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-300"></i>
                                            </button>
                                            <div class="select-options absolute left-0 right-0 mt-2 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 hidden z-50 overflow-hidden scale-95 opacity-0 transition-all origin-top">
                                                <div class="option px-4 py-3 text-sm hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors" data-value="articulo">Artículo Científico</div>
                                                <div class="option px-4 py-3 text-sm hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors" data-value="tesis">Tesis Académica</div>
                                                <div class="option px-4 py-3 text-sm hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors" data-value="acervo">Acervo General</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700">Resumen</label>
                                    <textarea name="resumen" rows="4" required class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none"><?= htmlspecialchars($doc_edit['resumen'] ?? '') ?></textarea>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700">Palabras Clave (Separadas por comas)</label>
                                    <input type="text" name="palabras_clave" placeholder="ej. educación, tecnología, sinaloa" 
                                        value="<?= htmlspecialchars($doc_edit['palabras_clave'] ?? '') ?>"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-emerald-500/20">
                                </div>

                                <!-- CAMPOS DINÁMICOS POR TIPO -->
                                <!-- Tesis -->
                                <div id="fields-tesis" class="dynamic-fields grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-50 <?= ($doc_edit['tipo'] ?? '') === 'tesis' ? '' : 'hidden' ?>">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">Institución</label>
                                        <input type="text" name="institucion" value="<?= htmlspecialchars($doc_edit['institucion'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-blue-500/20">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">Grado Académico</label>
                                        <input type="text" name="grado" value="<?= htmlspecialchars($doc_edit['grado'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-blue-500/20">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">Asesor</label>
                                        <input type="text" name="asesor" value="<?= htmlspecialchars($doc_edit['asesor'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-blue-500/20">
                                    </div>
                                </div>

                                <!-- Artículo -->
                                <div id="fields-articulo" class="dynamic-fields grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-50 <?= ($doc_edit['tipo'] ?? 'articulo') === 'articulo' ? '' : 'hidden' ?>">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">Revista</label>
                                        <input type="text" name="revista" value="<?= htmlspecialchars($doc_edit['revista'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">ISSN</label>
                                        <input type="text" name="issn" value="<?= htmlspecialchars($doc_edit['issn'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">DOI</label>
                                        <input type="text" name="doi" value="<?= htmlspecialchars($doc_edit['doi'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20">
                                    </div>
                                </div>

                                <!-- Acervo -->
                                <div id="fields-acervo" class="dynamic-fields grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-50 <?= ($doc_edit['tipo'] ?? '') === 'acervo' ? '' : 'hidden' ?>">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">Tipo de Material</label>
                                        <input type="text" name="tipo_material" value="<?= htmlspecialchars($doc_edit['tipo_material'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-amber-500/20">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">Categoría</label>
                                        <input type="text" name="categoria_acervo" value="<?= htmlspecialchars($doc_edit['categoria_acervo'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-amber-500/20">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700">Derechos/Licencia</label>
                                        <input type="text" name="derechos" value="<?= htmlspecialchars($doc_edit['derechos'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 outline-none focus:ring-2 focus:ring-amber-500/20">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <label class="text-sm font-bold text-slate-700 flex justify-between items-center">
                                            Documento (PDF) <?= $id_edit ? '<span class="text-[10px] text-slate-400 font-normal">(Dejar vacío para mantener actual)</span>' : '' ?>
                                            <span class="text-[9px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded uppercase font-bold tracking-tighter">Solo PDF</span>
                                        </label>
                                        <div class="relative group">
                                            <input type="file" name="archivo" id="file-pdf" accept=".pdf" <?= $id_edit ? '' : 'required' ?> 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                onchange="updateFileName('file-pdf', 'name-pdf', false, '.pdf')">
                                            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 flex flex-col items-center justify-center transition-all group-hover:border-emerald-400 group-hover:bg-emerald-50/30">
                                                <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-3 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors">
                                                    <i data-lucide="file-text" class="w-6 h-6"></i>
                                                </div>
                                                <p class="text-xs font-bold text-slate-500 group-hover:text-emerald-700">Seleccionar PDF</p>
                                                <p id="name-pdf" class="text-[10px] text-slate-400 mt-2 truncate max-w-full italic">Ningún archivo seleccionado</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <label class="text-sm font-bold text-slate-700 flex justify-between items-center">
                                            Imagen Portada
                                            <span class="text-[9px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded uppercase font-bold tracking-tighter">JPG, PNG, WEBP</span>
                                        </label>
                                        <div class="relative group">
                                            <input type="file" name="portada" id="file-img" accept=".jpg,.jpeg,.png,.webp" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                onchange="updateFileName('file-img', 'name-img', true, '.jpg,.jpeg,.png,.webp')">
                                            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 flex flex-col items-center justify-center transition-all group-hover:border-blue-400 group-hover:bg-blue-50/30">
                                                <div id="preview-container" class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors overflow-hidden">
                                                    <i data-lucide="image" class="w-6 h-6"></i>
                                                </div>
                                                <p class="text-xs font-bold text-slate-500 group-hover:text-blue-700">Seleccionar Imagen</p>
                                                <p id="name-img" class="text-[10px] text-slate-400 mt-2 truncate max-w-full italic">Ningún archivo seleccionado</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-6 border-t flex flex-col sm:flex-row justify-end items-center gap-4">
                                    <div class="relative w-full sm:w-72 custom-select" id="select-estado">
                                        <input type="hidden" name="estado_final" id="input-estado" value="<?= $doc_edit['estado_publicacion'] ?? 'borrador' ?>">
                                        <button type="button" class="select-trigger w-full bg-slate-100 text-slate-700 px-4 py-3 rounded-xl text-sm font-bold border-none outline-none focus:ring-2 focus:ring-slate-300 transition-all flex items-center justify-between">
                                            <span class="selected-text"><?= (isset($doc_edit['estado_publicacion']) && $doc_edit['estado_publicacion'] == 'pendiente') ? 'Enviar a Revisión' : 'Guardar como Borrador' ?></span>
                                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400 transition-transform duration-300"></i>
                                        </button>
                                        <div class="select-options absolute left-0 right-0 bottom-full mb-2 bg-white border border-slate-100 rounded-2xl shadow-2xl shadow-slate-200/50 hidden z-50 overflow-hidden scale-95 opacity-0 transition-all origin-bottom">
                                            <div class="option px-4 py-3 text-sm font-bold hover:bg-slate-50 cursor-pointer transition-colors" data-value="borrador">Guardar como Borrador</div>
                                            <div class="option px-4 py-3 text-sm font-bold hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors" data-value="pendiente">Enviar a Revisión</div>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full sm:w-auto bg-emerald-600 text-white px-10 py-3 rounded-xl font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                                        <i data-lucide="<?= $id_edit ? 'save' : 'upload-cloud' ?>" class="w-5 h-5"></i>
                                        <?= $id_edit ? 'Actualizar Cambios' : 'Subir Documento' ?>
                                    </button>
                                </div>
                                </form>
                            </div>
                        </div>

                        <!-- Panel de Vista Previa -->
                        <div class="hidden lg:block lg:sticky lg:top-8">
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm mb-4">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <i data-lucide="eye" class="w-4 h-4"></i> Vista Previa en Biblioteca
                                </h3>
                                
                                <!-- Card Replicada de biblioteca.php -->
                                <div id="preview-card" class="bg-white rounded-2xl overflow-hidden shadow-md border border-slate-100 flex flex-col max-w-[280px] mx-auto transition-all duration-300">
                                    <div id="preview-bg" class="h-48 relative overflow-hidden flex items-center justify-center p-6 border-b border-slate-50 bg-emerald-50/50">
                                        <div id="preview-cover" class="w-24 h-32 bg-white rounded shadow-lg border-l-4 border-emerald-600 flex flex-col justify-end p-2 relative">
                                            <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(circle, #000 1px, transparent 1px); background-size: 10px 10px;"></div>
                                            <div class="w-full h-1 bg-slate-100 mb-1 rounded"></div>
                                            <div class="w-2/3 h-1 bg-slate-100 rounded"></div>
                                            <i data-lucide="file-text" class="absolute top-2 right-2 w-3 h-3 text-slate-200"></i>
                                        </div>
                                    </div>
                                    <div class="p-4 flex-grow flex flex-col">
                                        <div class="flex justify-between items-start mb-2">
                                            <span id="prev-cat" class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Artículo</span>
                                            <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded"><?= date('2024') ?></span>
                                        </div>
                                        <h3 id="prev-title" class="font-bold text-slate-900 text-sm leading-tight mb-2 line-clamp-2 italic text-slate-400">Título del recurso...</h3>
                                        <p class="text-xs text-slate-500 mt-auto"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></p>
                                    </div>
                                </div>

                                <p class="text-[10px] text-slate-400 mt-6 text-center italic">Así es como verán los demás usuarios tu recurso una vez publicado.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($modulo === 'mis_documentos'): ?>
                    <?php
                    // Configuración de Paginación y Búsqueda
                    $limit = 10;
                    $page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
                    $search = trim($_GET['q'] ?? '');
                    $offset = ($page - 1) * $limit;

                    $where = "WHERE id_autor = :userId";
                    $params = [':userId' => $userId];
                    if ($search !== '') {
                        $where .= " AND (titulo LIKE :s1 OR tipo LIKE :s2 OR estado_publicacion LIKE :s3)";
                        $params[':s1'] = "%$search%";
                        $params[':s2'] = "%$search%";
                        $params[':s3'] = "%$search%";
                    }

                    // Contar total para paginación
                    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM documentos_biblioteca $where");
                    foreach($params as $key => $val) $countStmt->bindValue($key, $val);
                    $countStmt->execute();
                    $totalRows = $countStmt->fetchColumn();
                    $totalPages = ceil($totalRows / $limit);
                    if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

                    // Consultar página actual
                    $stmt = $pdo->prepare("SELECT * FROM documentos_biblioteca $where ORDER BY fecha_subida DESC LIMIT $limit OFFSET $offset");
                    foreach($params as $key => $val) $stmt->bindValue($key, $val);
                    $stmt->execute();
                    $misDocs = $stmt->fetchAll();
                    ?>

                    <!-- Header y Acciones (Estilo Admin) -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                        <div>
                            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Mis Documentos</h2>
                            <p class="text-slate-500 text-sm mt-1 font-medium">Gestiona tu acervo personal (<span id="total-results-count" class="text-emerald-600 font-bold"><?= $totalRows ?></span> registros en total)</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <!-- Buscador AJAX -->
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400" id="search-spinner-icon"></i>
                                    <!-- Spinner de carga oculto por defecto -->
                                    <svg id="search-loader" class="hidden w-4 h-4 text-emerald-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                                <input type="text" id="inv-search-ajax" placeholder="Buscar documento..." autocomplete="off"
                                    value="<?= htmlspecialchars($search) ?>"
                                    class="pl-9 pr-9 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none w-64 transition-all bg-white shadow-sm">
                                <button type="button" id="clear-search-ajax" class="<?= $search === '' ? 'hidden' : '' ?> absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-red-500 transition-colors">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <a href="subir_articulo.php?modulo=nuevo" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-600/20 transition-all flex items-center text-sm">
                                <i data-lucide="plus" class="w-5 h-5 mr-2"></i> Añadir Nuevo
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden min-h-[400px] flex flex-col">
                        <div class="overflow-x-auto flex-grow">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-4 font-extrabold">Documento</th>
                                        <th class="px-6 py-4 font-extrabold">Estado</th>
                                        <th class="px-6 py-4 font-extrabold">Fecha Subida</th>
                                        <th class="px-6 py-4 text-right font-extrabold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="docs-tbody-ajax" class="divide-y divide-slate-100">
                                    <?php if(empty($misDocs)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-20 text-center">
                                                <div class="flex flex-col items-center">
                                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 mb-4 ring-8 ring-slate-50/50">
                                                        <i data-lucide="file-question" class="w-8 h-8"></i>
                                                    </div>
                                                    <p class="text-slate-500 font-bold">No hay documentos que coincidan</p>
                                                    <p class="text-slate-400 text-xs mt-1">Verifica los términos de búsqueda o añade uno nuevo.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($misDocs as $doc):
                                            $puedeEditar = (!in_array($doc['estado_publicacion'], ['publicado', 'pendiente']) || $userRol === 'admin');
                                        ?>
                                        <tr class="hover:bg-slate-50/30 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200/50 overflow-hidden group-hover:scale-105 transition-transform shadow-sm">
                                                        <?php if($doc['imagen_portada']): ?>
                                                            <img src="<?= $doc['imagen_portada'] ?>" class="w-full h-full object-cover">
                                                        <?php else: ?>
                                                            <i data-lucide="file-text" class="w-6 h-6 text-slate-300"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-bold text-slate-900 leading-tight mb-1 truncate max-w-[300px]"><?= htmlspecialchars($doc['titulo']) ?></p>
                                                        <span class="text-[9px] px-2 py-0.5 rounded-md bg-white text-slate-500 font-extrabold uppercase tracking-tight border border-slate-200 shadow-sm inline-block">
                                                            <?= $doc['tipo'] ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest
                                                    <?= $doc['estado_publicacion'] === 'publicado' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 
                                                       ($doc['estado_publicacion'] === 'borrador' ? 'bg-slate-100 text-slate-600 border border-slate-200' : 
                                                       ($doc['estado_publicacion'] === 'suspendido' ? 'bg-amber-100 text-amber-700 border border-amber-200' :
                                                       ($doc['estado_publicacion'] === 'rechazado' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-700'))) ?>">
                                                    <?= $doc['estado_publicacion'] ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-semibold text-slate-500 italic"><?= date('d M, Y', strtotime($doc['fecha_subida'])) ?></td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button onclick="openDocumentViewer('<?= $doc['archivo_documento'] ?>', '<?= addslashes($doc['titulo']) ?>')" 
                                                        class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all" 
                                                        title="Ver PDF">
                                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                                    </button>
                                                    <?php if ($puedeEditar): ?>
                                                    <a href="subir_articulo.php?edit=<?= $doc['id'] ?>" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-all" title="Editar">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </a>
                                                    <?php else: ?>
                                                    <span class="p-2 text-slate-300 cursor-not-allowed" title="Documento Protegido">
                                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                                    </span>
                                                    <?php endif; ?>

                                                    <?php if ($doc['estado_publicacion'] !== 'publicado' || $userRol === 'admin'): ?>
                                                    <button onclick="confirmarEliminar(<?= $doc['id'] ?>)" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Eliminar">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <span class="p-2 text-slate-300 cursor-not-allowed" title="Publicado - Solo Admin puede eliminar">
                                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación AJAX -->
                        <div id="pagination-container-ajax">
                            <?php if($totalPages > 1): ?>
                            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest" id="results-meta-text">
                                    Mostrando <strong class="text-slate-900"><?= $offset + 1 ?></strong> a <strong class="text-slate-900"><?= min($offset + $limit, $totalRows) ?></strong> de <strong class="text-slate-900"><?= $totalRows ?></strong> documentos
                                </span>
                                <div class="flex items-center gap-1">
                                    <?php if($page > 1): ?>
                                        <button onclick="searchDocsAJAX('<?= $search ?>', <?= $page - 1 ?>)" class="px-3 py-2 text-xs font-bold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-sm">
                                            &laquo; Anterior
                                        </button>
                                    <?php else: ?>
                                        <span class="px-3 py-2 text-xs font-bold text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed">&laquo; Anterior</span>
                                    <?php endif; ?>

                                    <div class="hidden sm:flex items-center gap-1 mx-2">
                                        <?php 
                                        $start = max(1, $page - 2);
                                        $end = min($totalPages, $start + 4);
                                        if($end - $start < 4) $start = max(1, $end - 4);
                                        for($i = $start; $i <= $end; $i++): 
                                        ?>
                                            <button onclick="searchDocsAJAX('<?= $search ?>', <?= $i ?>)" class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-extrabold transition-all <?= $i == $page ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 shadow-sm' ?>">
                                                <?= $i ?>
                                            </button>
                                        <?php endfor; ?>
                                    </div>

                                    <?php if($page < $totalPages): ?>
                                        <button onclick="searchDocsAJAX('<?= $search ?>', <?= $page + 1 ?>)" class="px-3 py-2 text-xs font-bold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-sm">
                                            Siguiente &raquo;
                                        </button>
                                    <?php else: ?>
                                        <span class="px-3 py-2 text-xs font-bold text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed">Siguiente &raquo;</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Lógica JS para Búsqueda en Tiempo Real -->
                    <script>
                        (function() {
                            const input = document.getElementById('inv-search-ajax');
                            const clearBtn = document.getElementById('clear-search-ajax');
                            const tbody = document.getElementById('docs-tbody-ajax');
                            const totalCountEl = document.getElementById('total-results-count');
                            const metaTextEl = document.getElementById('results-meta-text');
                            const paginationContainer = document.getElementById('pagination-container-ajax');
                            const searchIcon = document.getElementById('search-spinner-icon');
                            const searchLoader = document.getElementById('search-loader');
                            
                            let debounceTimeout;

                            function escapeHTML(str) {
                                return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                            }

                            function formatDate(dateStr) {
                                if (!dateStr) return '';
                                const date = new Date(dateStr);
                                const options = { day: '2-digit', month: 'short', year: 'numeric' };
                                return date.toLocaleDateString('es-ES', options).replace(/\./g, '');
                            }

                            function renderTableRows(docs) {
                                if (docs.length === 0) {
                                    tbody.innerHTML = `
                                        <tr>
                                            <td colspan="4" class="px-6 py-20 text-center">
                                                <div class="flex flex-col items-center">
                                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 mb-4 ring-8 ring-slate-50/50">
                                                        <i data-lucide="file-question" class="w-8 h-8"></i>
                                                    </div>
                                                    <p class="text-slate-500 font-bold">No hay documentos que coincidan</p>
                                                    <p class="text-slate-400 text-xs mt-1">Verifica los términos de búsqueda o añade uno nuevo.</p>
                                                </div>
                                            </td>
                                        </tr>`;
                                    lucide.createIcons();
                                    return;
                                }

                                tbody.innerHTML = docs.map(doc => {
                                    const estadoClass = doc.estado_publicacion === 'publicado' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 
                                                       (doc.estado_publicacion === 'borrador' ? 'bg-slate-100 text-slate-600 border border-slate-200' : 
                                                       (doc.estado_publicacion === 'suspendido' ? 'bg-amber-100 text-amber-700 border border-amber-200' :
                                                       (doc.estado_publicacion === 'rechazado' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-700')));
                                    
                                    const canEdit = !['publicado', 'pendiente'].includes(doc.estado_publicacion) || userRol === 'admin';
                                    const canDelete = doc.estado_publicacion !== 'publicado' || userRol === 'admin';

                                    return `
                                        <tr class="hover:bg-slate-50/30 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200/50 overflow-hidden group-hover:scale-105 transition-transform shadow-sm">
                                                        ${doc.imagen_portada ? `<img src="${doc.imagen_portada}" class="w-full h-full object-cover">` : `<i data-lucide="file-text" class="w-6 h-6 text-slate-300"></i>`}
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-bold text-slate-900 leading-tight mb-1 truncate max-w-[300px]">${escapeHTML(doc.titulo)}</p>
                                                        <span class="text-[9px] px-2 py-0.5 rounded-md bg-white text-slate-500 font-extrabold uppercase tracking-tight border border-slate-200 shadow-sm inline-block">
                                                            ${doc.tipo}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest ${estadoClass}">
                                                    ${doc.estado_publicacion}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-semibold text-slate-500 italic">${formatDate(doc.fecha_subida)}</td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button onclick="openDocumentViewer('${doc.archivo_documento}', '${doc.titulo.replace(/'/g, "\\'")}')" 
                                                        class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all" 
                                                        title="Ver PDF">
                                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                                    </button>
                                                    ${canEdit ? `
                                                    <a href="subir_articulo.php?edit=${doc.id}" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-all" title="Editar">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </a>
                                                    ` : `
                                                    <span class="p-2 text-slate-300 cursor-not-allowed" title="Documento Protegido">
                                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                                    </span>
                                                    `}
                                                    
                                                    ${canDelete ? `
                                                    <button onclick="confirmarEliminar(${doc.id})" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Eliminar">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                    ` : `
                                                    <span class="p-2 text-slate-300 cursor-not-allowed" title="Publicado - Solo Admin puede eliminar">
                                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                                    </span>
                                                    `}
                                                </div>
                                            </td>
                                        </tr>
                                    `;
                                }).join('');
                                lucide.createIcons();
                            }

                            function renderPagination(data, q) {
                                if (data.total_pages <= 1) {
                                    paginationContainer.innerHTML = '';
                                    return;
                                }

                                const startRecord = ((data.current_page - 1) * 10) + 1;
                                const endRecord = Math.min(data.current_page * 10, data.total);

                                let buttons = `
                                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                                            Mostrando <strong class="text-slate-900">${startRecord}</strong> a <strong class="text-slate-900">${endRecord}</strong> de <strong class="text-slate-900">${data.total}</strong> documentos
                                        </span>
                                        <div class="flex items-center gap-1">
                                            ${data.current_page > 1 ? `<button onclick="searchDocsAJAX('${escapeHTML(q)}', ${data.current_page - 1})" class="px-3 py-2 text-xs font-bold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-sm">&laquo; Anterior</button>` : `<span class="px-3 py-2 text-xs font-bold text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed">&laquo; Anterior</span>`}
                                            
                                            <div class="hidden sm:flex items-center gap-1 mx-2">`;
                                
                                const startPage = Math.max(1, data.current_page - 2);
                                const endPage = Math.min(data.total_pages, startPage + 4);
                                
                                for (let i = startPage; i <= endPage; i++) {
                                    buttons += `
                                        <button onclick="searchDocsAJAX('${escapeHTML(q)}', ${i})" class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-extrabold transition-all ${i === data.current_page ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 shadow-sm'}">${i}</button>
                                    `;
                                }

                                buttons += `</div>
                                            ${data.current_page < data.total_pages ? `<button onclick="searchDocsAJAX('${escapeHTML(q)}', ${data.current_page + 1})" class="px-3 py-2 text-xs font-bold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all shadow-sm">Siguiente &raquo;</button>` : `<span class="px-3 py-2 text-xs font-bold text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed">Siguiente &raquo;</span>`}
                                        </div>
                                    </div>`;
                                
                                paginationContainer.innerHTML = buttons;
                            }

                            window.searchDocsAJAX = function(q, p = 1) {
                                searchIcon.classList.add('hidden');
                                searchLoader.classList.remove('hidden');
                                
                                fetch(`subir_articulo.php?ajax_search=1&q=${encodeURIComponent(q)}&p=${p}`)
                                    .then(r => r.json())
                                    .then(data => {
                                        renderTableRows(data.results);
                                        renderPagination(data, q);
                                        totalCountEl.textContent = data.total;
                                        
                                        // Actualizar URL sin recargar
                                        const url = new URL(window.location);
                                        if (q) url.searchParams.set('q', q); else url.searchParams.delete('q');
                                        if (p > 1) url.searchParams.set('p', p); else url.searchParams.delete('p');
                                        window.history.replaceState({}, '', url);
                                    })
                                    .finally(() => {
                                        searchIcon.classList.remove('hidden');
                                        searchLoader.classList.add('hidden');
                                    });
                            };

                            input.addEventListener('input', function() {
                                const q = this.value;
                                clearBtn.classList.toggle('hidden', q === '');
                                clearTimeout(debounceTimeout);
                                debounceTimeout = setTimeout(() => searchDocsAJAX(q, 1), 300);
                            });

                            clearBtn.addEventListener('click', function() {
                                input.value = '';
                                clearBtn.classList.add('hidden');
                                searchDocsAJAX('', 1);
                            });
                        })();
                    </script>
                <?php endif; ?>
            </div>
        </main>
    </div>
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
                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 id="viewerTitle" class="font-bold text-slate-900 text-sm md:text-base line-clamp-1">Documento</h3>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3 h-3 text-emerald-500"></i> Vista Protegida de Investigador
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <a id="downloadPDFBtn" href="#" download class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-full transition-all group" title="Descargar Mi PDF Original">
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
                    <div id="pdf-loader" class="flex flex-col items-center justify-center py-32 text-emerald-900">
                        <div class="animate-spin rounded-full h-14 w-14 border-b-2 border-emerald-600 mb-6"></div>
                        <p class="text-sm font-bold uppercase tracking-widest text-slate-500">Procesando Documento...</p>
                        <p class="text-xs text-slate-400 mt-2">Preparando entorno de lectura segura</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

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
                const fileName = title.replace(/[^a-z0-9]/gi, '_').toLowerCase() + ".pdf";
                downloadBtn.setAttribute('download', fileName);
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden'; 
            scrollContainer.scrollTop = 0;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            Array.from(container.children).forEach(child => {
                if(child.id !== 'pdf-loader') child.remove();
            });
            loader.style.display = 'flex';

            try {
                const loadingTask = pdfjsLib.getDocument(url);
                const pdf = await loadingTask.promise;
                loader.style.display = 'none';

                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                    const page = await pdf.getPage(pageNum);
                    const viewport = page.getViewport({ scale: 1.5 });
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    container.appendChild(canvas);
                    await page.render({ canvasContext: context, viewport: viewport }).promise;
                }
            } catch (error) {
                console.error('Error al cargar PDF:', error);
                loader.innerHTML = `<div class="bg-red-50 p-6 rounded-2xl border border-red-100 flex flex-col items-center">
                    <i data-lucide="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
                    <p class="text-sm font-bold text-red-600 uppercase tracking-wider">Error de acceso</p>
                    <p class="text-xs text-red-400 mt-1">No se pudo cargar el archivo original</p>
                </div>`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }

        function closeDocumentViewer() {
            const modal = document.getElementById('documentViewerModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = ''; 
        }

        function confirmarEliminar(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este documento permanentemente? Esta acción no se puede deshacer.')) {
                window.location.href = `subir_articulo.php?delete=${id}`;
            }
        }

        // Custom Select Logic
        document.querySelectorAll('.custom-select').forEach(select => {
            const trigger = select.querySelector('.select-trigger');
            const options = select.querySelector('.select-options');
            const hiddenInput = select.querySelector('input[type="hidden"]');
            const selectedText = select.querySelector('.selected-text');
            const icon = trigger.querySelector('[data-lucide="chevron-down"]');

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !options.classList.contains('hidden');
                
                // Close all other selects
                document.querySelectorAll('.select-options').forEach(opt => {
                    if (opt !== options) {
                        opt.classList.add('hidden');
                        opt.classList.remove('scale-100', 'opacity-100');
                    }
                });

                if (isOpen) {
                    options.classList.add('hidden');
                    options.classList.remove('scale-100', 'opacity-100');
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    options.classList.remove('hidden');
                    setTimeout(() => {
                        options.classList.add('scale-100', 'opacity-100');
                    }, 10);
                    icon.style.transform = 'rotate(180deg)';
                }
            });

            options.querySelectorAll('.option').forEach(option => {
                option.addEventListener('click', () => {
                    const val = option.dataset.value;
                    const text = option.textContent;
                    hiddenInput.value = val;
                    selectedText.textContent = text;
                    options.classList.add('hidden');
                    options.classList.remove('scale-100', 'opacity-100');
                    icon.style.transform = 'rotate(0deg)';

                        // Trigger dynamic fields logic if it's the 'tipo' select
                        if (select.id === 'select-tipo') {
                            document.getElementById('prev-cat').textContent = text;
                            toggleDynamicFields(val);
                        }
                });
            });
        });

        document.addEventListener('click', () => {
            document.querySelectorAll('.select-options').forEach(opt => {
                opt.classList.add('hidden');
                opt.classList.remove('scale-100', 'opacity-100');
            });
            document.querySelectorAll('.select-trigger i').forEach(i => i.style.transform = 'rotate(0deg)');
        });

        function toggleDynamicFields(type) {
            document.querySelectorAll('.dynamic-fields').forEach(div => div.classList.add('hidden'));
            const target = document.getElementById('fields-' + type);
            if (target) {
                target.classList.remove('hidden');
                target.classList.add('animate-in', 'fade-in', 'slide-in-from-top-2', 'duration-300');
            }
        }

        function updatePreview() {
            const titleIn = document.getElementById('in-titulo').value;
            const titlePrev = document.getElementById('prev-title');
            
            if (titleIn.trim()) {
                titlePrev.textContent = titleIn;
                titlePrev.classList.remove('italic', 'text-slate-400');
            } else {
                titlePrev.textContent = "Título del recurso...";
                titlePrev.classList.add('italic', 'text-slate-400');
            }
        }

        function showToast(message, type = 'error') {
            const toast = document.createElement('div');
            toast.className = 'toast-notification border-red-500';
            if (type === 'success') toast.classList.replace('border-red-500', 'border-emerald-500');
            
            toast.innerHTML = `
                <i data-lucide="${type === 'error' ? 'alert-circle' : 'check-circle'}" class="w-5 h-5 ${type === 'error' ? 'text-red-500' : 'text-emerald-500'}"></i>
                <span class="text-sm font-bold text-slate-700">${message}</span>
            `;
            document.body.appendChild(toast);
            lucide.createIcons();
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function updateFileName(inputId, nameId, isImg = false, allowedExtensions = '') {
            const input = document.getElementById(inputId);
            const label = document.getElementById(nameId);
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileName = file.name;
                const fileExt = '.' + fileName.split('.').pop().toLowerCase();
                
                // Validación de extensión
                if (allowedExtensions && !allowedExtensions.split(',').includes(fileExt)) {
                    showToast(`Formato no compatible. Usa: ${allowedExtensions}`, 'error');
                    input.value = ''; // Limpiar selección
                    label.textContent = "Ningún archivo seleccionado";
                    label.classList.add('text-slate-400');
                    label.classList.remove('text-emerald-600', 'font-bold');
                    
                    if (isImg) {
                        document.getElementById('preview-container').innerHTML = '<i data-lucide="image" class="w-6 h-6"></i>';
                        lucide.createIcons();
                    }
                    return;
                }

                label.textContent = fileName;
                label.classList.remove('text-slate-400');
                label.classList.add('text-emerald-600', 'font-bold');

                if (isImg) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const container = document.getElementById('preview-container');
                        container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        
                        // Update the large preview card cover too
                        const mainPreview = document.getElementById('preview-cover');
                        mainPreview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        mainPreview.style.padding = '0';
                        mainPreview.style.border = 'none';
                        mainPreview.classList.add('shadow-xl');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        }

        // Init preview if editing
        window.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('in-titulo')) {
                updatePreview();
                // If it's an edit, the category is already there but we should sync it
                const catText = document.querySelector('#select-tipo .selected-text').textContent;
                document.getElementById('prev-cat').textContent = catText;
                
                // Existing cover
                <?php if (isset($doc_edit['imagen_portada']) && $doc_edit['imagen_portada']): ?>
                const mainPreview = document.getElementById('preview-cover');
                mainPreview.innerHTML = `<img src="<?= $doc_edit['imagen_portada'] ?>" class="w-full h-full object-cover">`;
                mainPreview.style.padding = '0';
                mainPreview.style.border = 'none';
                <?php endif; ?>
            }
        });
    </script>
</body>
</html>
