<?php
session_start();
// Solo admin puede entrar a esta página de edición administrativa
if (!isset($_SESSION['user_bib_id']) || ($_SESSION['user_bib_rol'] ?? '') !== 'admin') {
    header("Location: login_biblioteca.php");
    exit;
}

require_once 'conexion.php';

$userId = $_SESSION['user_bib_id'];
$userRol = $_SESSION['user_bib_rol'];
$id_edit = $_GET['edit'] ?? null;

if (!$id_edit) {
    header("Location: admin_biblioteca.php?modulo=recursos");
    exit;
}

// Cargar el documento
$stmt = $pdo->prepare("SELECT d.*, u.nombre as autor_nombre FROM documentos_biblioteca d JOIN usuarios_biblioteca u ON d.id_autor = u.id WHERE d.id = ?");
$stmt->execute([$id_edit]);
$doc_edit = $stmt->fetch();

if (!$doc_edit) {
    header("Location: admin_biblioteca.php?modulo=recursos");
    exit;
}

// Lógica de Edición
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_documento'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $resumen = trim($_POST['resumen'] ?? '');
    $palabras_clave = trim($_POST['palabras_clave'] ?? '');
    $estado_post = $_POST['estado_final'] ?? $doc_edit['estado_publicacion'];

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
        $sqlCheck = "SELECT COUNT(*) FROM documentos_biblioteca WHERE doi = ? AND id != ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$doi, $id_edit]); // Use $id_edit here
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "El DOI '$doi' ya se encuentra registrado en otro documento.";
        }
    }

    if ($error) {
        // Detener si ya hay un error de DOI
    } else if (empty($titulo) || empty($tipo) || empty($resumen)) {
        $error = "El título, tipo y resumen son obligatorios.";
    } else {
        $archivo = $_FILES['archivo'];
        $portada = $_FILES['portada'] ?? null;
        
        $dirDocs = "uploads/biblioteca/documentos/";
        $dirPorts = "uploads/biblioteca/portadas/";
        if (!is_dir($dirDocs)) mkdir($dirDocs, 0777, true);
        if (!is_dir($dirPorts)) mkdir($dirPorts, 0777, true);

        $rutaArchivo = $doc_edit['archivo_documento'];
        $rutaPortada = $doc_edit['imagen_portada'];
        $continuar = true;

        if (!empty($archivo['name'])) {
            $extArchivo = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (strtolower($extArchivo) !== 'pdf') {
                $error = "El documento debe ser un archivo PDF.";
                $continuar = false;
            } else {
                $nombreArchivo = time() . "_" . bin2hex(random_bytes(4)) . ".pdf";
                $rutaArchivo = $dirDocs . $nombreArchivo;
                if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                    // Borrar anterior
                    if ($doc_edit['archivo_documento'] && file_exists($doc_edit['archivo_documento'])) {
                        @unlink($doc_edit['archivo_documento']);
                    }
                } else {
                    $error = "Error al subir el archivo PDF.";
                    $continuar = false;
                }
            }
        }

        if ($continuar && !empty($portada['name'])) {
            $extPortada = strtolower(pathinfo($portada['name'], PATHINFO_EXTENSION));
            $allowedImg = ['jpg', 'jpeg', 'png'];
            if (in_array($extPortada, $allowedImg)) {
                $nombrePortada = time() . "_p_" . bin2hex(random_bytes(4)) . "." . $extPortada;
                $rutaPortada = $dirPorts . $nombrePortada;
                if (move_uploaded_file($portada['tmp_name'], $rutaPortada)) {
                    // Borrar anterior
                    if ($doc_edit['imagen_portada'] && file_exists($doc_edit['imagen_portada'])) {
                        @unlink($doc_edit['imagen_portada']);
                    }
                }
            } else {
                $error = "La portada debe ser .JPG, .JPEG o .PNG.";
                $continuar = false;
            }
        }

        if ($continuar) {
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
                // Recargar datos
                $stmt = $pdo->prepare("SELECT d.*, u.nombre as autor_nombre FROM documentos_biblioteca d JOIN usuarios_biblioteca u ON d.id_autor = u.id WHERE d.id = ?");
                $stmt->execute([$id_edit]);
                $doc_edit = $stmt->fetch();
            } else {
                $error = "Error al actualizar la base de datos.";
            }
        }
    }
}

$activeClass = "bg-blue-800 text-white border-l-4 border-blue-400";
$inactiveClass = "text-blue-100 hover:bg-blue-800 hover:text-white border-l-4 border-transparent";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Editar Documento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden">

    <!-- Sidebar Lateral (Admin) -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex border-r border-gray-800">
        <div class="p-6 border-b border-gray-800 flex items-center gap-3">
            <div class="p-2 bg-blue-600 rounded-lg"><i data-lucide="shield-check" class="w-6 h-6"></i></div>
            <div>
                <h2 class="font-bold text-lg leading-tight text-white">CIATA</h2>
                <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest">Administrador</p>
            </div>
        </div>
        <nav class="flex-1 mt-6 px-2 space-y-1 overflow-y-auto">
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase mb-2">Administración</h3>
            <a href="admin_biblioteca.php?modulo=inicio" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i><span class="text-sm">Dashboard</span>
            </a>
            <a href="admin_biblioteca.php?modulo=recursos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $activeClass ?>">
                <i data-lucide="book-open" class="w-5 h-5 mr-3"></i><span class="text-sm">Biblioteca General</span>
            </a>
            <a href="admin_biblioteca.php?modulo=documentos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="file-check" class="w-5 h-5 mr-3"></i><span class="text-sm">Revisión de Tesis</span>
            </a>
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase mt-6 mb-2">Comunidad</h3>
            <a href="admin_biblioteca.php?modulo=solicitudes" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="user-plus" class="w-5 h-5 mr-3"></i><span class="text-sm">Solicitudes</span>
            </a>
            <a href="admin_biblioteca.php?modulo=usuarios" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i><span class="text-sm">Gestión de Usuarios</span>
            </a>
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase mt-6 mb-2">Cuenta</h3>
            <a href="admin_biblioteca.php?modulo=recursos" class="mx-2 px-4 py-3 flex items-center rounded-lg text-gray-400 hover:bg-gray-800 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-3"></i><span>Volver</span>
            </a>
            <a href="logout_biblioteca.php" class="mx-2 px-4 py-3 flex items-center rounded-lg text-red-400 hover:bg-red-500/10 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i><span>Cerrar Sesión</span>
            </a>
        </nav>

        <!-- Perfil bottom part (opcional, como el padre) -->
        <div class="p-4 border-t border-gray-800 bg-gray-950">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs uppercase">
                        <?= strtoupper(substr($_SESSION['user_bib_nombre'], 0, 1)) ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white leading-none truncate w-24 tracking-tight"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></p>
                    </div>
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
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold text-gray-800 hidden sm:block leading-tight">Gestión de Biblioteca</h2>
                    <p class="text-[10px] text-blue-600 font-bold uppercase tracking-tight">Portal Administrativo CIATA</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="biblioteca.php" class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full hover:bg-blue-100 transition-colors">
                    <i data-lucide="library" class="w-4 h-4 mr-1.5"></i> Ver Biblioteca
                </a>
                <span class="text-sm text-gray-400">|</span>
                <span class="text-sm font-medium text-gray-600"><?= htmlspecialchars($_SESSION['user_bib_correo']) ?></span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-6xl mx-auto">
                
                <!-- Sub-Cabecera de Navegación Estilo Investigador -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center">
                        <a href="admin_biblioteca.php?modulo=recursos" class="mr-4 text-gray-400 hover:text-blue-600 transition-colors p-2 bg-white rounded-xl shadow-sm border border-slate-100">
                            <i data-lucide="arrow-left" class="w-6 h-6"></i>
                        </a>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">Modificar Documento</h2>
                            <p class="text-slate-500 text-sm mt-1">Autor: <?= htmlspecialchars($doc_edit['autor_nombre']) ?> <span class="text-xs text-slate-300 ml-2">ID: #<?= $doc_edit['id'] ?></span></p>
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
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i><span class="text-sm font-medium"><?= $success ?></span>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                            <form method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                                <input type="hidden" name="editar_documento" value="1">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Título del Documento</label>
                                        <input type="text" name="titulo" id="in-titulo" required value="<?= htmlspecialchars($doc_edit['titulo'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                                            oninput="updatePreview()">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Tipo de Recurso</label>
                                        <div class="relative custom-select" id="select-tipo">
                                            <input type="hidden" name="tipo" id="input-tipo" value="<?= $doc_edit['tipo'] ?? 'articulo' ?>">
                                            <button type="button" class="select-trigger w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium text-slate-700">
                                                <span class="selected-text"><?= $doc_edit['tipo'] == 'tesis' ? 'Tesis Académica' : ($doc_edit['tipo'] == 'acervo' ? 'Acervo General' : 'Artículo Científico') ?></span>
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-300"></i>
                                            </button>
                                            <div class="select-options absolute left-0 right-0 mt-2 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 hidden z-50 overflow-hidden scale-95 opacity-0 transition-all origin-top">
                                                <div class="option px-4 py-3 text-sm hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="articulo">Artículo Científico</div>
                                                <div class="option px-4 py-3 text-sm hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="tesis">Tesis Académica</div>
                                                <div class="option px-4 py-3 text-sm hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="acervo">Acervo General</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700 tracking-tight">Resumen / Descripción</label>
                                    <textarea name="resumen" rows="6" required class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none"><?= htmlspecialchars($doc_edit['resumen'] ?? '') ?></textarea>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700 tracking-tight">Palabras Clave (Separadas por comas)</label>
                                    <input type="text" name="palabras_clave" placeholder="ej. educación, tecnología, sinaloa" 
                                        value="<?= htmlspecialchars($doc_edit['palabras_clave'] ?? '') ?>"
                                        class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                </div>

                                <!-- CAMPOS DINÁMICOS POR TIPO -->
                                <!-- Tesis -->
                                <div id="fields-tesis" class="dynamic-fields grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-50 <?= ($doc_edit['tipo'] ?? '') === 'tesis' ? '' : 'hidden' ?>">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Institución</label>
                                        <input type="text" name="institucion" value="<?= htmlspecialchars($doc_edit['institucion'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Grado Académico</label>
                                        <input type="text" name="grado" value="<?= htmlspecialchars($doc_edit['grado'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Asesor</label>
                                        <input type="text" name="asesor" value="<?= htmlspecialchars($doc_edit['asesor'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                </div>

                                <!-- Artículo -->
                                <div id="fields-articulo" class="dynamic-fields grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-50 <?= ($doc_edit['tipo'] ?? 'articulo') === 'articulo' ? '' : 'hidden' ?>">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Revista</label>
                                        <input type="text" name="revista" value="<?= htmlspecialchars($doc_edit['revista'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">ISSN</label>
                                        <input type="text" name="issn" value="<?= htmlspecialchars($doc_edit['issn'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">DOI</label>
                                        <input type="text" name="doi" value="<?= htmlspecialchars($doc_edit['doi'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                </div>

                                <!-- Acervo -->
                                <div id="fields-acervo" class="dynamic-fields grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-50 <?= ($doc_edit['tipo'] ?? '') === 'acervo' ? '' : 'hidden' ?>">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Tipo de Material</label>
                                        <input type="text" name="tipo_material" value="<?= htmlspecialchars($doc_edit['tipo_material'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Categoría</label>
                                        <input type="text" name="categoria_acervo" value="<?= htmlspecialchars($doc_edit['categoria_acervo'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Derechos/Licencia</label>
                                        <input type="text" name="derechos" value="<?= htmlspecialchars($doc_edit['derechos'] ?? '') ?>"
                                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500/20 outline-none">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <label class="text-sm font-bold text-slate-700 tracking-tight">Archivo del Documento (.PDF solamente)</label>
                                        <div class="relative group">
                                            <input type="file" name="archivo" id="file-pdf" accept=".pdf" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                onchange="updateFileName('file-pdf', 'name-pdf', false)">
                                            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 flex flex-col items-center justify-center transition-all group-hover:border-red-400 group-hover:bg-red-50/30">
                                                <div id="pdf-preview-container" class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-3 group-hover:bg-red-100 group-hover:text-red-600 transition-colors">
                                                    <i data-lucide="file-text" class="w-6 h-6"></i>
                                                </div>
                                                <p class="text-xs font-bold text-slate-500 group-hover:text-red-700">Reemplazar PDF</p>
                                                <p id="name-pdf" class="text-[10px] text-slate-400 mt-2 truncate max-w-full italic">Solo archivos .PDF</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                         <label class="text-sm font-bold text-slate-700 tracking-tight">Nueva Portada (.JPG, .JPEG o .PNG)</label>
                                        <div class="relative group">
                                            <input type="file" name="portada" id="file-img" accept=".jpg,.jpeg,.png" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                onchange="updateFileName('file-img', 'name-img', true)">
                                            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 flex flex-col items-center justify-center transition-all group-hover:border-blue-400 group-hover:bg-blue-50/30">
                                                <div id="preview-container" class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors overflow-hidden">
                                                    <i data-lucide="image" class="w-6 h-6"></i>
                                                </div>
                                                <p class="text-xs font-bold text-slate-500 group-hover:text-blue-700">Cambiar Imagen</p>
                                                <p id="name-img" class="text-[10px] text-slate-400 mt-2 truncate max-w-full italic">Solo .JPG, .JPEG o .PNG</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-6 border-t flex flex-col sm:flex-row justify-end items-center gap-4">
                                    <div class="relative w-full sm:w-72 custom-select" id="select-estado">
                                        <input type="hidden" name="estado_final" id="input-estado" value="<?= $doc_edit['estado_publicacion'] ?>">
                                        <button type="button" class="select-trigger w-full bg-slate-100 text-slate-700 px-4 py-3 rounded-xl text-sm font-bold border-none outline-none focus:ring-2 focus:ring-slate-300 transition-all flex items-center justify-between">
                                            <span class="selected-text"><?= $doc_edit['estado_publicacion'] === 'suspendido' ? 'Suspendido (Oculto)' : ucfirst($doc_edit['estado_publicacion']) ?></span>
                                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400 transition-transform duration-300"></i>
                                        </button>
                                        <div class="select-options absolute left-0 right-0 bottom-full mb-2 bg-white border border-slate-100 rounded-2xl shadow-2xl shadow-slate-200/50 hidden z-50 overflow-hidden scale-95 opacity-0 transition-all origin-bottom">
                                            <div class="option px-4 py-3 text-sm font-bold hover:bg-emerald-50 hover:text-emerald-700 cursor-pointer transition-colors" data-value="publicado">Publicado (Visible)</div>
                                            <div class="option px-4 py-3 text-sm font-bold hover:bg-slate-50 cursor-pointer transition-colors" data-value="borrador">Borrador (Oculto)</div>
                                            <div class="option px-4 py-3 text-sm font-bold hover:bg-amber-50 hover:text-amber-700 cursor-pointer transition-colors" data-value="suspendido">Suspendido (Oculto)</div>
                                            <div class="option px-4 py-3 text-sm font-bold hover:bg-red-50 hover:text-red-700 cursor-pointer transition-colors" data-value="rechazado">Rechazado</div>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-10 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                                        <i data-lucide="save" class="w-5 h-5"></i>
                                        Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="hidden lg:block lg:sticky lg:top-8">
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm mb-4">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <i data-lucide="eye" class="w-4 h-4"></i> Vista Previa (Admin)
                            </h3>
                            
                            <div id="preview-card" class="bg-white rounded-2xl overflow-hidden shadow-md border border-slate-100 flex flex-col max-w-[280px] mx-auto transition-all duration-300">
                                <div id="preview-bg" class="h-48 relative overflow-hidden flex items-center justify-center p-6 border-b border-slate-50 bg-blue-50/30">
                                    <div id="preview-cover" class="w-24 h-32 bg-white rounded shadow-lg border-l-4 border-blue-600 flex flex-col justify-end p-2 relative">
                                        <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(circle, #000 1px, transparent 1px); background-size: 10px 10px;"></div>
                                        <i data-lucide="file-text" class="absolute top-2 right-2 w-3 h-3 text-slate-200"></i>
                                    </div>
                                </div>
                                <div class="p-4 flex-grow flex flex-col">
                                    <div class="flex justify-between items-start mb-2">
                                        <span id="prev-cat" class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Artículo</span>
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded"><?= date('2024') ?></span>
                                    </div>
                                    <h3 id="prev-title" class="font-bold text-slate-900 text-sm leading-tight mb-2 line-clamp-2 italic text-slate-400">Título...</h3>
                                    <p class="text-xs text-slate-500 mt-auto"><?= htmlspecialchars($doc_edit['autor_nombre']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                titlePrev.textContent = "Título...";
                titlePrev.classList.add('italic', 'text-slate-400');
            }
        }

        function updateFileName(inputId, nameId, isImg = false) {
            const input = document.getElementById(inputId);
            const label = document.getElementById(nameId);
            if (input.files && input.files[0]) {
                label.textContent = input.files[0].name;
                label.classList.remove('text-slate-400');
                label.classList.add('text-blue-600', 'font-bold');

                if (isImg) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const container = document.getElementById('preview-container');
                        container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        const mainPreview = document.getElementById('preview-cover');
                        mainPreview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        mainPreview.style.padding = '0';
                        mainPreview.style.border = 'none';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            updatePreview();
            const catText = document.querySelector('#select-tipo .selected-text').textContent;
            document.getElementById('prev-cat').textContent = catText;
            <?php if ($doc_edit['imagen_portada']): ?>
            const mainPreview = document.getElementById('preview-cover');
            mainPreview.innerHTML = `<img src="<?= $doc_edit['imagen_portada'] ?>" class="w-full h-full object-cover">`;
            mainPreview.style.padding = '0';
            mainPreview.style.border = 'none';
            <?php endif; ?>
        });
    </script>
</body>
</html>
