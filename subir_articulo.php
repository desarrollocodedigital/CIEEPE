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
    
    // Regla de Negocio: Si está publicado, solo el admin puede editar
    if ($doc_edit['estado_publicacion'] === 'publicado' && $userRol !== 'admin') {
        $modulo = 'mis_documentos';
        $error = "Este documento ya ha sido publicado y no puede ser editado por el autor.";
        $id_edit = null;
    } else {
        $modulo = 'nuevo'; // Usar el mismo formulario para editar
    }
}

// Lógica de Subida / Edición
$error = $error ?? '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['subir_documento']) || isset($_POST['editar_documento']))) {
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $resumen = trim($_POST['resumen'] ?? '');
    $estado_post = $_POST['estado_final'] ?? 'borrador'; // borrador o pendiente
    $es_edicion = isset($_POST['editar_documento']);
    
    // Validar campos básicos
    if (empty($titulo) || empty($tipo) || empty($resumen)) {
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
                $sql = "UPDATE documentos_biblioteca SET titulo = ?, tipo = ?, resumen = ?, imagen_portada = ?, archivo_documento = ?, estado_publicacion = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$titulo, $tipo, $resumen, $rutaPortada, $rutaArchivo, $estado_post, $id_edit])) {
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
                $stmt = $pdo->prepare("INSERT INTO documentos_biblioteca (id_autor, tipo, titulo, resumen, imagen_portada, archivo_documento, estado_publicacion) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$userId, $tipo, $titulo, $resumen, $rutaPortada, $rutaArchivo, $estado_post])) {
                    $success = "Documento guardado correctamente (" . ($estado_post == 'borrador' ? 'Borrador' : 'Pendiente de revisión') . ").";
                } else {
                    $error = "Error al guardar en la base de datos.";
                }
            }
        }
    }
}

// Clases CSS
$activeClass = "bg-emerald-800 text-white border-l-4 border-emerald-400";
$inactiveClass = "text-emerald-100 hover:bg-emerald-800 hover:text-white border-l-4 border-transparent";
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
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden">

    <!-- Sidebar Lateral -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col hidden md:flex border-r border-slate-800">
        <div class="p-6 border-b border-slate-800 flex items-center gap-3">
            <div class="p-2 bg-emerald-600 rounded-lg"><i data-lucide="microscope" class="w-6 h-6"></i></div>
            <div>
                <h2 class="font-bold text-lg leading-tight">CIATA</h2>
                <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-widest">Investigador</p>
            </div>
        </div>
        <nav class="flex-1 mt-6 px-2 space-y-1 overflow-y-auto">
            <h3 class="px-6 text-xs font-semibold text-slate-500 uppercase mb-2">Principal</h3>
            <a href="subir_articulo.php?modulo=inicio" class="mx-2 px-4 py-3 flex items-center rounded-lg <?= $modulo === 'inicio' ? $activeClass : $inactiveClass ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i><span>Dashboard</span>
            </a>
            <a href="subir_articulo.php?modulo=nuevo" class="mx-2 px-4 py-3 flex items-center rounded-lg <?= ($modulo === 'nuevo' && !$id_edit) ? $activeClass : $inactiveClass ?>">
                <i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i><span>Nuevo Documento</span>
            </a>
            <a href="subir_articulo.php?modulo=mis_documentos" class="mx-2 px-4 py-3 flex items-center rounded-lg <?= ($modulo === 'mis_documentos' || $id_edit) ? $activeClass : $inactiveClass ?>">
                <i data-lucide="library" class="w-5 h-5 mr-3"></i><span>Mis Documentos</span>
            </a>
            <h3 class="px-6 text-xs font-semibold text-slate-500 uppercase mt-6 mb-2">Cuenta</h3>
            <a href="biblioteca.php" class="mx-2 px-4 py-3 flex items-center rounded-lg text-slate-400 hover:bg-slate-800 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-3"></i><span>Volver</span>
            </a>
            <a href="logout_biblioteca.php" class="mx-2 px-4 py-3 flex items-center rounded-lg text-red-400 hover:bg-red-500/10 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i><span>Cerrar Sesión</span>
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-20 bg-white border-b border-slate-100 flex items-center justify-between px-8">
            <div class="flex items-center gap-4">
                <div class="h-10 w-1 bg-emerald-500 rounded-full"></div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">
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
            </div>
            <div class="flex items-center gap-3 border-l border-slate-100 pl-6">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-900"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></p>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase"><?= htmlspecialchars($_SESSION['user_bib_rol']) ?></p>
                </div>
                <div class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center font-bold">
                    <?= strtoupper(substr($_SESSION['user_bib_nombre'], 0, 1)) ?>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-6xl mx-auto">
                
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

                <?php if ($modulo === 'inicio'): ?>
                    <!-- DASHBOARD INVESTIGADOR -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                            <h4 class="text-2xl font-bold text-slate-900"><?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE id_autor = $userId")->fetchColumn(); ?></h4>
                            <p class="text-xs text-slate-500 mt-1">Total documentos</p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                            <h4 class="text-2xl font-bold text-emerald-600"><?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE id_autor = $userId AND estado_publicacion = 'publicado'")->fetchColumn(); ?></h4>
                            <p class="text-xs text-slate-500 mt-1">Publicados</p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                            <h4 class="text-2xl font-bold text-amber-500"><?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE id_autor = $userId AND estado_publicacion = 'pendiente'")->fetchColumn(); ?></h4>
                            <p class="text-xs text-slate-500 mt-1">En revisión</p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                            <h4 class="text-2xl font-bold text-slate-400"><?php echo $pdo->query("SELECT COUNT(*) FROM documentos_biblioteca WHERE id_autor = $userId AND estado_publicacion = 'borrador'")->fetchColumn(); ?></h4>
                            <p class="text-xs text-slate-500 mt-1">Borradores</p>
                        </div>
                    </div>
                    <!-- Recent items ... -->
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <label class="text-sm font-bold text-slate-700">Documento (PDF) <?= $id_edit ? '<span class="text-[10px] text-slate-400">(Dejar vacío para mantener actual)</span>' : '' ?></label>
                                        <div class="relative group">
                                            <input type="file" name="archivo" id="file-pdf" accept=".pdf" <?= $id_edit ? '' : 'required' ?> 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                onchange="updateFileName('file-pdf', 'name-pdf')">
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
                                        <label class="text-sm font-bold text-slate-700">Imagen Portada</label>
                                        <div class="relative group">
                                            <input type="file" name="portada" id="file-img" accept="image/*" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                onchange="updateFileName('file-img', 'name-img', true)">
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
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500 text-[11px] font-bold uppercase">
                                <tr><th class="px-6 py-4">Documento</th><th class="px-6 py-4">Estado</th><th class="px-6 py-4">Fecha</th><th class="px-6 py-4 text-right">Acciones</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php
                                $misDocs = $pdo->query("SELECT * FROM documentos_biblioteca WHERE id_autor = $userId ORDER BY fecha_subida DESC")->fetchAll();
                                foreach($misDocs as $doc):
                                    $puedeEditar = ($doc['estado_publicacion'] !== 'publicado' || $userRol === 'admin');
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded bg-slate-100 flex items-center justify-center shrink-0">
                                                <?php if($doc['imagen_portada']): ?><img src="<?= $doc['imagen_portada'] ?>" class="w-full h-full object-cover rounded"><?php else: ?><i data-lucide="file-text" class="w-5 h-5 text-slate-300"></i><?php endif; ?>
                                            </div>
                                            <div><p class="text-sm font-bold text-slate-900"><?= htmlspecialchars($doc['titulo']) ?></p><p class="text-[10px] text-blue-600 font-bold"><?= $doc['tipo'] ?></p></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase 
                                            <?= $doc['estado_publicacion'] === 'publicado' ? 'bg-emerald-100 text-emerald-700' : 
                                               ($doc['estado_publicacion'] === 'borrador' ? 'bg-slate-100 text-slate-600' : 
                                               ($doc['estado_publicacion'] === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')) ?>">
                                            <?= $doc['estado_publicacion'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500"><?= date('d/m/Y', strtotime($doc['fecha_subida'])) ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <?php if ($puedeEditar): ?>
                                        <a href="subir_articulo.php?edit=<?= $doc['id'] ?>" class="text-blue-600 hover:text-blue-800 transition-colors mr-3" title="Editar">
                                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-slate-300 cursor-not-allowed mr-3" title="Publicado - Solo lectura"><i data-lucide="lock" class="w-5 h-5"></i></span>
                                        <?php endif; ?>
                                        <button class="text-slate-400 hover:text-red-500"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        lucide.createIcons();

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
                    
                    // Update preview if it's the category select
                    if (select.id === 'select-tipo') {
                        document.getElementById('prev-cat').textContent = text;
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

        function updateFileName(inputId, nameId, isImg = false) {
            const input = document.getElementById(inputId);
            const label = document.getElementById(nameId);
            if (input.files && input.files[0]) {
                label.textContent = input.files[0].name;
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
