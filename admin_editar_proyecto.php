<?php
// Si no está definido el módulo o se intenta acceder directamente, redirigir
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href='admin.php?modulo=proyectos';</script>";
    exit;
}

$id_proyecto = $_GET['id'];
$error = '';
$success = '';

// Check if just created or updated
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') {
        $success = 'Proyecto creado exitosamente.';
    } elseif ($_GET['status'] === 'updated') {
        $success = 'Proyecto actualizado exitosamente.';
    } elseif ($_GET['status'] === 'obj_added') {
        $success = 'Objetivo específico agregado exitosamente.';
    } elseif ($_GET['status'] === 'obj_updated') {
        $success = 'Objetivo específico actualizado.';
    } elseif ($_GET['status'] === 'obj_deleted') {
        $success = 'Objetivo específico eliminado.';
    }
}

// Obtener datos del proyecto
$stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ?");
$stmt->execute([$id_proyecto]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    echo "<script>window.location.href='admin.php?modulo=proyectos';</script>";
    exit;
}

// Obtener objetivos específicos
$stmtObj = $pdo->prepare("SELECT * FROM proyecto_objetivos WHERE proyecto_id = ? ORDER BY orden ASC");
$stmtObj->execute([$id_proyecto]);
$objetivos = $stmtObj->fetchAll(PDO::FETCH_ASSOC);

// Manejo CRUD de Objetivos Específicos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_obj') {
        $stmt = $pdo->prepare("INSERT INTO proyecto_objetivos (proyecto_id, icono, titulo, descripcion, orden) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $id_proyecto,
            $_POST['icono_obj'] ?? 'target',
            trim($_POST['titulo_obj'] ?? ''),
            trim($_POST['desc_obj'] ?? ''),
            (int)($_POST['orden_obj'] ?? count($objetivos) + 1)
        ]);
        echo "<script>window.location.href='admin.php?modulo=editar_proyecto&id=$id_proyecto&status=obj_added';</script>";
        exit;
    }
    elseif ($_POST['action'] === 'update_obj') {
        $stmt = $pdo->prepare("UPDATE proyecto_objetivos SET icono = ?, titulo = ?, descripcion = ?, orden = ? WHERE id = ? AND proyecto_id = ?");
        $stmt->execute([
            $_POST['icono_obj'] ?? 'target',
            trim($_POST['titulo_obj'] ?? ''),
            trim($_POST['desc_obj'] ?? ''),
            (int)($_POST['orden_obj'] ?? 1),
            $_POST['obj_id'],
            $id_proyecto
        ]);
        echo "<script>window.location.href='admin.php?modulo=editar_proyecto&id=$id_proyecto&status=obj_updated';</script>";
        exit;
    }
}

// Eliminar Objetivo
if (isset($_GET['del_obj']) && is_numeric($_GET['del_obj'])) {
    $stmt = $pdo->prepare("DELETE FROM proyecto_objetivos WHERE id = ? AND proyecto_id = ?");
    $stmt->execute([$_GET['del_obj'], $id_proyecto]);
    echo "<script>window.location.href='admin.php?modulo=editar_proyecto&id=$id_proyecto&status=obj_deleted';</script>";
    exit;
}

// Obtener listas para desplegables
$lista_categorias = $pdo->query("SELECT titulo FROM lineas_investigacion ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$lista_todos = $pdo->query("SELECT nombre, procedencia FROM investigadores ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$lista_internos = array_filter($lista_todos, function($i) { return $i['procedencia'] === 'Interno'; });
$lista_externos = array_filter($lista_todos, function($i) { return $i['procedencia'] === 'Externo'; });

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_project') {
    $titulo_nuevo = trim($_POST['titulo'] ?? '');
    $categoria_nuevo = $_POST['categoria'] ?? '';
    $estado_nuevo = $_POST['estado'] ?? 'En Curso';
    $desc_nuevo = trim($_POST['descripcion_corta'] ?? '');
    $responsable_nuevo = trim($_POST['responsable'] ?? '');
    $internos_nuevo = isset($_POST['internos']) && is_array($_POST['internos']) ? implode(', ', $_POST['internos']) : '';
    $externos_nuevo = isset($_POST['externos']) && is_array($_POST['externos']) ? implode(', ', $_POST['externos']) : '';
    $anio_nuevo = trim($_POST['anio_inicio'] ?? date('Y'));
    
    $desc_larga_nuevo = trim($_POST['descripcion_larga'] ?? '');
    $objetivos_nuevo = trim($_POST['objetivos_especificos'] ?? '');
    $duracion_nuevo = trim($_POST['duracion'] ?? '');
    $financiamiento_nuevo = trim($_POST['financiamiento'] ?? '');
    $area_nuevo = trim($_POST['area'] ?? '');

    if (empty($titulo_nuevo) || empty($categoria_nuevo)) {
        $error = 'Los campos Título y Categoría son obligatorios.';
    } else {
        $imagen_portada = $pro['imagen_portada'];

        // Subir Imagen
        if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['imagen_portada']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                if (!is_dir('img/proyectos')) {
                    mkdir('img/proyectos', 0755, true);
                }
                $new_name = uniqid('proyecto_') . '.' . $ext;
                $dest = 'img/proyectos/' . $new_name;
                if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $dest)) {
                    $imagen_portada = './' . $dest;
                }
            } else {
                $error .= ' Formato de imagen no válido. ';
            }
        }

        // Manejo del PDF del Protocolo
        $pdf_protocolo = $pro['pdf_protocolo'];
        if (isset($_FILES['pdf_protocolo']) && $_FILES['pdf_protocolo']['error'] === UPLOAD_ERR_OK) {
            $pdf_nombre = time() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $_FILES['pdf_protocolo']['name']);
            $pdf_destino = 'docs/' . $pdf_nombre;
            if (move_uploaded_file($_FILES['pdf_protocolo']['tmp_name'], $pdf_destino)) {
                $pdf_protocolo = $pdf_destino;
            }
        }

        if (!$error) {
            $sql = "UPDATE proyectos SET 
                    titulo = ?, categoria = ?, estado = ?, descripcion_corta = ?, descripcion_larga = ?, 
                    objetivos_especificos = ?, imagen_portada = ?, responsable = ?, internos = ?, externos = ?, 
                    anio_inicio = ?, duracion = ?, financiamiento = ?, area = ?, pdf_protocolo = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([
                $titulo_nuevo, $categoria_nuevo, $estado_nuevo, $desc_nuevo, $desc_larga_nuevo,
                '', $imagen_portada, $responsable_nuevo, $internos_nuevo, $externos_nuevo,
                $anio_nuevo, $duracion_nuevo, $financiamiento_nuevo, $area_nuevo, $pdf_protocolo,
                $id_proyecto
            ])) {
                echo "<script>window.location.href='admin.php?modulo=editar_proyecto&id=$id_proyecto&status=updated';</script>";
                exit;
            } else {
                $error = 'Ocurrió un error al actualizar la base de datos.';
            }
        }
    }
}
?>

<!-- Cabecera -->
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center">
        <a href="admin.php?modulo=proyectos" class="mr-4 text-gray-400 hover:text-blue-600 transition-colors p-2 bg-white rounded-lg shadow-sm" title="Volver a Proyectos">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Editar Proyecto</h2>
            <p class="text-gray-500 text-sm mt-1">#<?= htmlspecialchars($pro['id']) ?> - <?= htmlspecialchars($pro['titulo']) ?></p>
        </div>
    </div>
    
    <button type="button" onclick="confirmDelete(<?= $pro['id'] ?>)" class="px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg shadow-sm hover:bg-red-100 transition-colors flex items-center font-medium text-sm">
        <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Eliminar Proyecto
    </button>
</div>

<?php if($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if($success): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm" id="success-alert">
    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($success) ?>
</div>
<script>
    setTimeout(() => {
        const el = document.getElementById('success-alert');
        if(el) { el.style.transition = 'opacity 0.5s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }
    }, 4000);
</script>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <!-- COLUMNA IZQUIERDA: Formulario Principal -->
    <div class="xl:col-span-2 space-y-6">
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <input type="hidden" name="action" value="edit_project">
            
            <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 flex items-center">
                <i data-lucide="folder-edit" class="w-5 h-5 mr-2 text-blue-600"></i>
                <h3 class="font-bold text-blue-900">1. Información Principal</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Título -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Título del Proyecto <span class="text-red-500">*</span></label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($pro['titulo']) ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-gray-400">
                </div>

                <!-- Categoría y Estado -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Linea de investigación</label>
                        <div class="relative">
                            <select name="categoria" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none bg-white pr-10">
                                <option value="">Selecciona una categoría</option>
                                <?php foreach($lista_categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['titulo']) ?>" <?= $pro['categoria'] == $cat['titulo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['titulo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Estado del Proyecto</label>
                        <div class="relative">
                            <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none bg-white pr-10">
                                <option value="En Puerta" <?= $pro['estado'] == 'En Puerta' ? 'selected' : '' ?>>En Puerta</option>
                                <option value="En Curso" <?= $pro['estado'] == 'En Curso' ? 'selected' : '' ?>>En Curso</option>
                                <option value="Terminados" <?= $pro['estado'] == 'Terminados' ? 'selected' : '' ?>>Terminados</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Imagen Portada -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Imagen de Portada (Dejar vacío para conservar actual)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition-colors bg-gray-50 group relative">
                        <div class="space-y-1 text-center">
                            <i data-lucide="image-plus" class="mx-auto h-12 w-12 text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-2 py-1 shadow-sm border border-gray-200">
                                    <span>Cambiar imagen</span>
                                    <input id="file-upload" name="imagen_portada" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, WEBP hasta 2MB</p>
                        </div>
                    </div>
                    <img id="img-preview" src="<?= htmlspecialchars($pro['imagen_portada']) ?>" alt="Vista previa" class="mt-4 h-32 w-auto object-cover rounded-lg border border-gray-200 shadow-sm">
                </div>

                <!-- Descripción Corta -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción Corta</label>
                    <textarea name="descripcion_corta" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    ><?= htmlspecialchars($pro['descripcion_corta']) ?></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-b border-gray-100 border-t bg-blue-50 flex items-center mt-4">
                <i data-lucide="users" class="w-5 h-5 mr-2 text-blue-600"></i>
                <h3 class="font-bold text-blue-900">2. Equipo Involucrado</h3>
            </div>

            <div class="p-6 space-y-6">
                <!-- Responsable y Año -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Responsable Principal</label>
                        <div class="relative">
                            <select name="responsable" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none bg-white pr-10">
                                <option value="">Selecciona responsable</option>
                                <?php foreach($lista_todos as $inv): ?>
                                    <option value="<?= htmlspecialchars($inv['nombre']) ?>" <?= $pro['responsable'] == $inv['nombre'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($inv['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Año de Inicio</label>
                        <?php 
                        $anio_mostrar = $pro['anio_inicio'];
                        if(strlen($anio_mostrar) > 4 && strpos($anio_mostrar, '-') !== false) {
                            $anio_mostrar = substr($anio_mostrar, 0, 4);
                        }
                        ?>
                        <input type="number" min="1900" max="2100" step="1" name="anio_inicio" value="<?= htmlspecialchars($anio_mostrar) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>

                <!-- Internos y Externos -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Internos -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Colaboradores Internos</label>
                        <p class="text-xs text-gray-500 mb-2">Selecciona de la lista para añadir colaboradores</p>
                        
                        <!-- Contenedor visual de las etiquetas (pills) -->
                        <div id="pills-internos" class="flex flex-wrap gap-2 mb-3 empty:hidden"></div>
                        
                        <!-- Select visible para elegir -->
                        <div class="relative">
                            <select id="dropdown-internos" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none bg-white pr-10">
                                <option value="">Añadir colaborador interno</option>
                                <?php foreach($lista_internos as $inv): ?>
                                    <option value="<?= htmlspecialchars($inv['nombre']) ?>"><?= htmlspecialchars($inv['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <!-- Inputs ocultos reales que se envían por POST -->
                        <div id="hidden-inputs-internos">
                            <?php 
                            $arr_internos = array_filter(array_map('trim', explode(',', $pro['internos'])));
                            foreach($arr_internos as $nombre): ?>
                                <input type="hidden" name="internos[]" value="<?= htmlspecialchars($nombre) ?>">
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Externos -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Colaboradores Externos</label>
                        <p class="text-xs text-gray-500 mb-2">Selecciona de la lista para añadir externos</p>
                        
                        <!-- Contenedor visual de las etiquetas (pills) -->
                        <div id="pills-externos" class="flex flex-wrap gap-2 mb-3 empty:hidden"></div>
                        
                        <!-- Select visible para elegir -->
                        <div class="relative">
                            <select id="dropdown-externos" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all appearance-none bg-white pr-10">
                                <option value="">Añadir colaborador externo</option>
                                <?php foreach($lista_externos as $inv): ?>
                                    <option value="<?= htmlspecialchars($inv['nombre']) ?>"><?= htmlspecialchars($inv['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <!-- Inputs ocultos reales que se envían por POST -->
                        <div id="hidden-inputs-externos">
                            <?php 
                            $arr_externos = array_filter(array_map('trim', explode(',', $pro['externos'])));
                            foreach($arr_externos as $nombre): ?>
                                <input type="hidden" name="externos[]" value="<?= htmlspecialchars($nombre) ?>">
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Ficha Técnica Avanzada -->
                <div class="border-t border-gray-200 mt-6 pt-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Detalles para Ficha Técnica</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción Detallada</label>
                            <textarea name="descripcion_larga" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all custom-scroll" placeholder="Escribe el resumen extendido del proyecto..."><?= htmlspecialchars($pro['descripcion_larga'] ?? '') ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Objetivos Específicos (Tarjetas)</label>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start">
                                <i data-lucide="info" class="w-5 h-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-sm text-blue-800 font-medium">Gestión de Objetivos</p>
                                    <p class="text-xs text-blue-600 mt-1">Los objetivos se administran ahora como tarjetas individuales interactivas. Dirígete a la sección <strong>"Metas y Objetivos Específicos"</strong> ubicada más abajo en esta misma página.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Duración</label>
                                <input type="text" name="duracion" value="<?= htmlspecialchars($pro['duracion'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Ej. 2 años">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Financiamiento</label>
                                <input type="text" name="financiamiento" value="<?= htmlspecialchars($pro['financiamiento'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Ej. PROFEXCE / Recursos Propios">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Área</label>
                                <input type="text" name="area" value="<?= htmlspecialchars($pro['area'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Ej. Formación Docente">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Protocolo en PDF</label>
                            <?php if (!empty($pro['pdf_protocolo'])): ?>
                                <p class="text-xs text-blue-600 mb-2 flex items-center">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Documento actual cargado. Sube otro para reemplazarlo.
                                </p>
                            <?php endif; ?>
                            <div class="flex items-center space-x-4">
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-blue-50 hover:border-blue-300 transition-colors">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-gray-500" id="pdf-upload-ui">
                                        <p class="mb-2 text-sm text-center px-4"><span class="font-semibold">Haz clic para subir un PDF</span> o arrástralo aquí</p>
                                    </div>
                                    <input type="file" name="pdf_protocolo" accept=".pdf" class="hidden" onchange="updatePdfName(this)">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="px-6 py-5 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
                <a href="admin.php?modulo=proyectos" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors font-medium text-sm">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center font-medium text-sm">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Actualizar Proyecto
                </button>
            </div>
        </form>

        <!-- METAS Y OBJETIVOS ESPECIFICOS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-gray-100 bg-amber-50 flex items-center justify-between">
                <div class="flex items-center">
                    <i data-lucide="target" class="w-5 h-5 mr-2 text-amber-600"></i>
                    <h3 class="font-bold text-gray-800">Objetivos Específicos (<?= count($objetivos) ?>)</h3>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- Add New Objective Form -->
                <div class="border border-dashed border-gray-300 rounded-lg p-4 mb-6 bg-gray-50">
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="action" value="add_obj">
                        <h4 class="text-xs font-bold text-gray-600 uppercase mb-2">Añadir Nuevo Objetivo</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-1">
                                <input type="text" name="icono_obj" placeholder="Icono (ej. book)" class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none" required>
                                <p class="text-[10px] text-gray-500 mt-1"><a href="https://lucide.dev/icons" target="_blank" class="text-blue-500 hover:underline">Ver iconos Lucide</a></p>
                            </div>
                            <div class="md:col-span-3">
                                <input type="text" name="titulo_obj" placeholder="Título corto del objetivo" class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none" required>
                            </div>
                        </div>
                        <textarea name="desc_obj" rows="2" placeholder="Descripción detallada o meta concreta..." class="w-full text-sm border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none" required></textarea>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <label class="text-xs text-gray-500">Orden:</label>
                                <input type="number" name="orden_obj" value="<?= count($objetivos) + 1 ?>" class="w-16 text-sm border border-gray-300 rounded-lg p-1 text-center" min="1">
                            </div>
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-1.5 px-4 rounded-lg text-sm transition-colors shadow-sm">
                                Añadir Objetivo
                            </button>
                        </div>
                    </form>
                </div>

                <!-- List of Objectives -->
                <?php if(empty($objetivos)): ?>
                    <p class="text-sm text-gray-500 text-center py-4">No se han registrado objetivos específicos para este proyecto.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach($objetivos as $obj): ?>
                        <div class="bg-white border border-gray-200 rounded-lg p-4 group relative hover:border-amber-300 transition-colors">
                            <div class="absolute top-2 right-2 flex space-x-2">
                                <button onclick="toggleEditObj(<?= $obj['id'] ?>)" type="button" class="text-gray-300 hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" title="Editar">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <a href="admin.php?modulo=editar_proyecto&id=<?= $id_proyecto ?>&del_obj=<?= $obj['id'] ?>" onclick="return confirm('¿Eliminar objetivo?');" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity" title="Eliminar">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                </a>
                            </div>

                            <div id="obj-view-<?= $obj['id'] ?>">
                                <div class="flex items-start mb-2">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mr-3 flex-shrink-0 mt-0.5">
                                        <i data-lucide="<?= htmlspecialchars($obj['icono']) ?>" class="w-4 h-4"></i>
                                    </div>
                                    <div class="pr-8">
                                        <h4 class="font-bold text-gray-900 text-sm leading-tight mb-1"><?= htmlspecialchars($obj['titulo']) ?></h4>
                                        <p class="text-xs text-gray-600 line-clamp-3"><?= htmlspecialchars($obj['descripcion']) ?></p>
                                    </div>
                                </div>
                                <div class="text-[10px] text-gray-400 font-medium">Orden: <?= $obj['orden'] ?></div>
                            </div>

                            <!-- Edit Form -->
                            <div id="obj-edit-<?= $obj['id'] ?>" class="hidden mt-2 border-t pt-3 border-amber-100">
                                <form method="POST" class="space-y-2">
                                    <input type="hidden" name="action" value="update_obj">
                                    <input type="hidden" name="obj_id" value="<?= $obj['id'] ?>">
                                    
                                    <div class="flex space-x-2">
                                        <input type="text" name="icono_obj" value="<?= htmlspecialchars($obj['icono']) ?>" placeholder="Icono" class="w-1/3 text-xs border rounded p-1.5 focus:border-amber-500 outline-none" required>
                                        <input type="text" name="titulo_obj" value="<?= htmlspecialchars($obj['titulo']) ?>" placeholder="Título" class="flex-1 text-xs border rounded p-1.5 font-bold focus:border-amber-500 outline-none" required>
                                    </div>
                                    
                                    <textarea name="desc_obj" rows="2" class="w-full text-xs border rounded p-1.5 focus:border-amber-500 outline-none" required><?= htmlspecialchars($obj['descripcion']) ?></textarea>
                                    
                                    <div class="flex items-center justify-between mt-2 pt-2">
                                        <input type="number" name="orden_obj" value="<?= $obj['orden'] ?>" class="w-12 text-xs border rounded p-1 text-center" min="1">
                                        <div class="flex space-x-1">
                                            <button type="submit" class="bg-amber-600 text-white text-[10px] px-2 py-1 rounded font-bold hover:bg-amber-700">Guardar</button>
                                            <button type="button" onclick="toggleEditObj(<?= $obj['id'] ?>)" class="bg-gray-200 text-gray-600 text-[10px] px-2 py-1 rounded font-bold hover:bg-gray-300">Cancelar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- COLUMNA DERECHA: Ayuda/Tips -->
    <div class="space-y-6">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 shadow-sm">
            <h4 class="font-bold text-blue-900 mb-3 flex items-center">
                <i data-lucide="info" class="w-5 h-5 mr-2"></i> Acerca del Registro
            </h4>
            <p class="text-sm text-blue-800 mb-3">
                Editando proyecto registrado el: <br>
                <strong><?= htmlspecialchars($pro['creado_en'] ?? 'Fecha no disponible') ?></strong>
            </p>
        </div>
    </div>
</div>

<script>
function toggleEditObj(id) {
    const view = document.getElementById('obj-view-' + id);
    const edit = document.getElementById('obj-edit-' + id);
    if (view.classList.contains('hidden')) {
        view.classList.remove('hidden');
        edit.classList.add('hidden');
    } else {
        view.classList.add('hidden');
        edit.classList.remove('hidden');
    }
}

function previewImage(input) {
    const preview = document.getElementById('img-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '<?= htmlspecialchars($pro['imagen_portada']) ?>';
    }
}

function updatePdfName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('pdf-upload-ui').innerHTML = `
            <svg class="w-8 h-8 mb-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="mb-2 text-sm text-blue-600 font-semibold text-center px-4">${input.files[0].name}</p>
        `;
    }
}

function confirmDelete(id) {
    if(confirm('¿Estás seguro de que deseas eliminar este proyecto de forma permanente? Esta acción no se puede deshacer.')) {
        window.location.href = `admin_proyectos.php?action=delete&id=${id}`;
    }
}

// Lógica para Multi-Select con "Pills"
function initPillSelect(type, colorBase) {
    const dropdown = document.getElementById(`dropdown-${type}`);
    const pillsContainer = document.getElementById(`pills-${type}`);
    const hiddenInputsContainer = document.getElementById(`hidden-inputs-${type}`);
    
    // Función para añadir una "pill" y ocultar la opción
    function addPill(name) {
        // Verificar si ya existe en los inputs ocultos para no duplicar en UI
        if (document.querySelector(`#hidden-inputs-${type} input[value="${name}"]`) === null) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `${type}[]`;
            input.value = name;
            hiddenInputsContainer.appendChild(input);
        }

        const pill = document.createElement('span');
        pill.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-${colorBase}-100 text-${colorBase}-800 border border-${colorBase}-200 shadow-sm`;
        pill.innerHTML = `
            ${name}
            <button type="button" class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-${colorBase}-600 hover:bg-${colorBase}-200 hover:text-${colorBase}-900 focus:outline-none focus:bg-${colorBase}-200 focus:text-${colorBase}-900">
                <span class="sr-only">Remove</span>
                <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                    <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                </svg>
            </button>
        `;
        
        // Listener para eliminar la "pill"
        pill.querySelector('button').addEventListener('click', () => {
            pill.remove();
            // Mostrar la opción de nuevo en el dropdown
             Array.from(dropdown.options).forEach(opt => {
                if (opt.value === name) opt.style.display = 'block';
            });
            // Eliminar de los inputs ocultos
            const hidden = document.querySelector(`#hidden-inputs-${type} input[value="${name}"]`);
            if(hidden) hidden.remove();
        });

        pillsContainer.appendChild(pill);
        
        // Ocultar del dropdown
        Array.from(dropdown.options).forEach(opt => {
            if (opt.value === name) opt.style.display = 'none';
        });
    }

    // Inicializar visualmente las pastillas basadas en los inputs ocultos generados por PHP
    Array.from(hiddenInputsContainer.querySelectorAll('input')).forEach(input => {
        addPill(input.value);
    });

    // Escuchar el cambio en el selector
    dropdown.addEventListener('change', (e) => {
        const selected = e.target.value;
        if (selected) {
            addPill(selected);
            e.target.value = ''; // Resetear al placeholder
        }
    });
}

// Inicializar ambos después de cargar
document.addEventListener('DOMContentLoaded', () => {
    initPillSelect('internos', 'blue');
    initPillSelect('externos', 'amber');
});
</script>
