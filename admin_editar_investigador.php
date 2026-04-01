<?php
// admin_investigador_editar.php
?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<?php

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    echo "<div class='bg-red-50 p-4 rounded-lg text-red-600 font-medium'>ID de investigador no válido.</div>";
    return;
}

$mensaje = '';
$error = '';

// Configurar directorios
$img_dir = './img/investigadores/';
$cv_dir = './docs/cvs/';
if (!file_exists($cv_dir)) @mkdir($cv_dir, 0777, true);
if (!file_exists($img_dir)) @mkdir($img_dir, 0777, true);

// ---> ACCIÓN: ACTUALIZAR PERFIL PRINCIPAL Y ARCHIVOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nombre = $_POST['nombre'] ?? '';
    $especialidad = $_POST['especialidad'] ?? '';
    $cargo = $_POST['cargo_o_grado'] ?? '';
    $badge = $_POST['etiqueta_badge'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $linkedin = $_POST['linkedin_url'] ?? '';
    $facebook = $_POST['facebook_url'] ?? '';
    $tipo_investigador = $_POST['tipo_investigador'] ?? '';
    $semblanza_corta = $_POST['semblanza_corta'] ?? '';
    $semblanza = $_POST['semblanza'] ?? '';
    $procedencia = $_POST['procedencia'] ?? 'Interno';
    
    $imagen_perfil = $_POST['imagen_actual'] ?? './img/placeholder.jpg';
    $cv_url = $_POST['cv_actual'] ?? '#';

    // Validar campos obligatorios
    if (empty($nombre)) {
        $error = "Por favor, ingresa el nombre del investigador.";
    }

    // 1. Manejo Subida de Imagen
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $nuevo_nombre = uniqid('foto_') . '.' . $ext;
            $ruta_img = $img_dir . $nuevo_nombre;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_img)) {
                // Borrar foto anterior si no es placeholder
                $old_img = trim($_POST['imagen_actual'] ?? '');
                if ($old_img && !str_contains($old_img, 'placeholder') && file_exists($old_img)) {
                    unlink($old_img);
                }
                $imagen_perfil = $ruta_img;
            } else {
                $error .= "Error guardar imagen en servidor. ";
            }
        } else {
             $error .= "Formato de imagen inválido. Solo se permite .jpg, .jpeg o .png. ";
        }
    }

    // 2. Manejo Subida de CV PDF
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $nuevo_nombre = uniqid('cv_') . '.pdf';
            $ruta_cv = $cv_dir . $nuevo_nombre;
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $ruta_cv)) {
                // Borrar CV anterior si existía
                $old_cv = trim($_POST['cv_actual'] ?? '');
                if ($old_cv && $old_cv !== '#' && file_exists($old_cv)) {
                    unlink($old_cv);
                }
                $cv_url = $ruta_cv;
            } else {
                $error .= "Error guardar CV. ";
            }
        } else {
             $error .= "El CV debe ser PDF. ";
        }
    }

    if (empty($error)) {
        $sql = "UPDATE investigadores SET nombre=?, especialidad=?, cargo_o_grado=?, tipo_investigador=?, etiqueta_badge=?, email=?, telefono=?, ubicacion=?, linkedin_url=?, facebook_url=?, cv_url=?, semblanza_corta=?, semblanza=?, imagen_perfil=?, procedencia=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$nombre, $especialidad, $cargo, $tipo_investigador, $badge, $email, $telefono, $ubicacion, $linkedin, $facebook, $cv_url, $semblanza_corta, $semblanza, $imagen_perfil, $procedencia, $id])) {
            $mensaje = 'Perfil y archivos actualizados exitosamente.';
        } else {
            $error = 'Ocurrió un error al actualizar la base de datos.';
        }
    }
}

// ---> ACCIÓN: ACTUALIZAR LÍNEA EXISTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_line') {
    $line_id = (int)$_POST['line_id'];
    $titulo_linea = trim($_POST['titulo_linea']);
    $desc_linea = trim($_POST['desc_linea']);
    $orden = (int)$_POST['orden_linea'];

    if (!empty($titulo_linea)) {
        $stmt = $pdo->prepare("UPDATE investigador_lineas SET titulo=?, descripcion=?, orden=? WHERE id=? AND investigador_id=?");
        if ($stmt->execute([$titulo_linea, $desc_linea, $orden, $line_id, $id])) {
            $mensaje = 'Especialidad de investigación actualizada.';
        }
    }
}

// ---> ACCIÓN: AÑADIR LÍNEA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_line') {
    $titulo_linea = trim($_POST['titulo_linea']);
    $desc_linea = trim($_POST['desc_linea']);
    $orden = (int)$_POST['orden_linea'];

    if (!empty($titulo_linea)) {
        $stmt = $pdo->prepare("INSERT INTO investigador_lineas (investigador_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$id, $titulo_linea, $desc_linea, $orden])) {
            $mensaje = 'Especialidad de investigación agregada.';
        }
    }
}

// ---> ACCIÓN: ELIMINAR LÍNEA
if (isset($_GET['del_line'])) {
    $line_id = (int)$_GET['del_line'];
    $pdo->prepare("DELETE FROM investigador_lineas WHERE id = ? AND investigador_id = ?")->execute([$line_id, $id]);
    $mensaje = 'Especialidad eliminada correctamente.';
    echo "<script>window.history.replaceState(null, null, 'admin.php?modulo=editar_investigador&id=$id');</script>";
}

// ---> ACCIÓN: REORDENAR LÍNEAS (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder_lines') {
    $new_order = $_POST['order'] ?? []; // Array de IDs en el nuevo orden
    if (!empty($new_order)) {
        $pdo->beginTransaction();
        try {
            foreach ($new_order as $idx => $line_id) {
                $orden = $idx + 1;
                $stmt = $pdo->prepare("UPDATE investigador_lineas SET orden = ? WHERE id = ? AND investigador_id = ?");
                $stmt->execute([$orden, (int)$line_id, $id]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Orden actualizado']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    exit; // Importante: detener ejecución para respuestas AJAX
}

// ---> ACCIÓN: AÑADIR PUBLICACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_pub') {
    $titulo_pub = trim($_POST['titulo_pub']);
    $subtitulo_pub = trim($_POST['subtitulo_pub']);
    $enlace_pub = trim($_POST['enlace_pub']);
    $orden_pub = (int)$_POST['orden_pub'];

    if (!empty($titulo_pub)) {
        $stmt = $pdo->prepare("INSERT INTO investigador_publicaciones (investigador_id, titulo, subtitulo, enlace, orden) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$id, $titulo_pub, $subtitulo_pub, $enlace_pub, $orden_pub])) {
            $mensaje = 'Publicación destacada agregada.';
        }
    }
}

// ---> ACCIÓN: ACTUALIZAR PUBLICACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_pub') {
    $pub_id = (int)$_POST['pub_id'];
    $titulo_pub = trim($_POST['titulo_pub']);
    $subtitulo_pub = trim($_POST['subtitulo_pub']);
    $enlace_pub = trim($_POST['enlace_pub']);
    $orden_pub = (int)$_POST['orden_pub'];

    if (!empty($titulo_pub)) {
        $stmt = $pdo->prepare("UPDATE investigador_publicaciones SET titulo=?, subtitulo=?, enlace=?, orden=? WHERE id=? AND investigador_id=?");
        if ($stmt->execute([$titulo_pub, $subtitulo_pub, $enlace_pub, $orden_pub, $pub_id, $id])) {
            $mensaje = 'Publicación destacada actualizada.';
        }
    }
}

// ---> ACCIÓN: ELIMINAR PUBLICACIÓN
if (isset($_GET['del_pub'])) {
    $pub_id = (int)$_GET['del_pub'];
    $pdo->prepare("DELETE FROM investigador_publicaciones WHERE id = ? AND investigador_id = ?")->execute([$pub_id, $id]);
    $mensaje = 'Publicación eliminada correctamente.';
    echo "<script>window.history.replaceState(null, null, 'admin.php?modulo=editar_investigador&id=$id');</script>";
}

// ---> ACCIÓN: REORDENAR PUBLICACIONES (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder_pubs') {
    $new_order = $_POST['order'] ?? []; 
    if (!empty($new_order)) {
        $pdo->beginTransaction();
        try {
            foreach ($new_order as $idx => $pub_id) {
                $orden = $idx + 1;
                $stmt = $pdo->prepare("UPDATE investigador_publicaciones SET orden = ? WHERE id = ? AND investigador_id = ?");
                $stmt->execute([$orden, (int)$pub_id, $id]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Orden de publicaciones actualizado']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    exit;
}


// ---> ACCIÓN: GESTIONAR LÍNEA (EXISTENTE O NUEVA)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'manage_line') {
    $is_new = isset($_POST['is_new_line']);
    $orden = (int)$_POST['orden_linea'];

    if ($is_new) {
        $titulo = trim($_POST['titulo_linea_nueva']);
        $desc = trim($_POST['desc_linea_nueva']);
        $registrar_maestra = isset($_POST['registrar_maestra']);

        if (!empty($titulo)) {
            $stmt = $pdo->prepare("INSERT INTO investigador_lineas (investigador_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$id, $titulo, $desc, $orden])) {
                $mensaje = 'Nueva especialidad creada y vinculada.';
                if ($registrar_maestra) {
                    $stmtM = $pdo->prepare("INSERT INTO lineas_investigacion (titulo, descripcion) VALUES (?, ?)");
                    $stmtM->execute([$titulo, $desc]);
                    $mensaje .= ' Añadida al catálogo general.';
                }
            }
        }
    } else {
        $titulo_maestro = trim($_POST['titulo_maestro']);
        if (!empty($titulo_maestro)) {
            $stmtM = $pdo->prepare("SELECT descripcion FROM investigador_lineas WHERE titulo = ? ORDER BY id DESC LIMIT 1");
            $stmtM->execute([$titulo_maestro]);
            $m = $stmtM->fetch();
            $desc = $m ? $m['descripcion'] : '';

            $stmt = $pdo->prepare("INSERT INTO investigador_lineas (investigador_id, titulo, descripcion, orden) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$id, $titulo_maestro, $desc, $orden])) {
                $mensaje = 'Especialidad existente vinculada.';
            }
        }
    }
}

// Obtener los datos más recientes del investigador
$query = $pdo->prepare("SELECT * FROM investigadores WHERE id = ?");
$query->execute([$id]);
$inv = $query->fetch();

if (!$inv) {
    echo "<div class='bg-red-50 p-4 rounded-lg text-red-600 font-medium'>Investigador no encontrado en la base de datos.</div>";
    return;
}

// Obtener líneas
$lineasQ = $pdo->prepare("SELECT * FROM investigador_lineas WHERE investigador_id = ? ORDER BY orden ASC");
$lineasQ->execute([$id]);
$lineas = $lineasQ->fetchAll();

// Obtener publicaciones
$pubsQ = $pdo->prepare("SELECT * FROM investigador_publicaciones WHERE investigador_id = ? ORDER BY orden ASC");
$pubsQ->execute([$id]);
$publicaciones = $pubsQ->fetchAll();

// Obtener listas disponibles para la etiqueta_badge
$lista_lineas = $pdo->query("SELECT titulo FROM lineas_investigacion ORDER BY titulo ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Cabecera -->
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center">
        <a href="admin.php?modulo=investigadores" class="mr-4 text-gray-400 hover:text-blue-600 transition-colors p-2 bg-white rounded-lg shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edición de Perfil</h2>
            <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($inv['nombre']) ?></p>
        </div>
    </div>
    <a href="perfil_generico.php?id=<?= $id ?>" target="_blank" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center text-sm">
        <i data-lucide="external-link" class="w-4 h-4 mr-2"></i> Ver Perfil Público
    </a>
</div>

<?php if($mensaje): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>


<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <!-- COLUMNA IZQUIERDA: Formulario Principal y Uploads -->
    <div class="xl:col-span-2 space-y-6">
        
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($inv['imagen_perfil']) ?>">
            <input type="hidden" name="cv_actual" value="<?= htmlspecialchars($inv['cv_url']) ?>">
            
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                <i data-lucide="user" class="w-5 h-5 mr-2 text-blue-600"></i>
                <h3 class="font-bold text-gray-800">Información General y Archivos</h3>
            </div>
            
            <div class="p-6">
                <!-- GRID CAMPOS -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nombre Completo *</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($inv['nombre']) ?>" required class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Cargo o Grado</label>
                        <input type="text" name="cargo_o_grado" value="<?= htmlspecialchars($inv['cargo_o_grado']) ?>" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 flex items-center">
                            Tipo de Investigador
                            <span class="ml-2 px-2 py-0.5 rounded text-[10px] bg-gray-200 text-gray-600 font-medium">Ej: Investigador SNII</span>
                        </label>
                        <input type="text" name="tipo_investigador" value="<?= htmlspecialchars($inv['tipo_investigador'] ?? '') ?>" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Procedencia (Interno/Externo) *</label>
                        <div class="relative">
                            <select name="procedencia" required class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 pr-10 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors appearance-none font-semibold">
                                <option value="Interno" <?= $inv['procedencia'] == 'Interno' ? 'selected' : '' ?>>Interno (CIEEPE)</option>
                                <option value="Externo" <?= $inv['procedencia'] == 'Externo' ? 'selected' : '' ?>>Externo (Otros Centros)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Etiqueta (Badge)</label>
                        <div class="relative">
                            <select name="etiqueta_badge" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 pr-10 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors appearance-none">
                                <option value="">Selecione una Linea de investigación</option>
                                <?php foreach($lista_lineas as $l): ?>
                                <option value="<?= htmlspecialchars($l['titulo']) ?>" <?= ($inv['etiqueta_badge'] === $l['titulo']) ? 'selected' : '' ?>><?= htmlspecialchars($l['titulo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Especialidad de Búsqueda</label>
                        <input type="text" name="especialidad" value="<?= htmlspecialchars($inv['especialidad']) ?>" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors">
                    </div>
                </div>

                <hr class="border-gray-100 my-8">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Email</label>
                        <div class="relative">
                            <i data-lucide="mail" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                            <input type="email" name="email" value="<?= htmlspecialchars($inv['email']) ?>" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 pl-10 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Teléfono</label>
                        <div class="relative">
                            <i data-lucide="phone" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="telefono" value="<?= htmlspecialchars($inv['telefono']) ?>" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 pl-10 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white" placeholder="Ej: 9998887779">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Ubicación</label>
                        <div class="relative">
                            <i data-lucide="map-pin" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="ubicacion" value="<?= htmlspecialchars($inv['ubicacion']) ?>" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 pl-10 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white">
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100 my-8">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 flex items-center">
                            <img src="./img/iconos/linkedin.svg" class="w-4 h-4 mr-2" alt="LI">
                            URL LinkedIn
                        </label>
                        <input type="text" name="linkedin_url" value="<?= htmlspecialchars($inv['linkedin_url']) ?>" oninput="this.value = this.value.replace(/^#/, '');" placeholder="linkedin.com/in/..." class="w-full rounded-lg border-gray-300 border focus:border-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 flex items-center">
                            <img src="./img/iconos/facebook-icon.svg" class="w-4 h-4 mr-2" alt="FB">
                            URL Facebook
                        </label>
                        <input type="text" name="facebook_url" value="<?= htmlspecialchars($inv['facebook_url']) ?>" oninput="this.value = this.value.replace(/^#/, '');" placeholder="facebook.com/..." class="w-full rounded-lg border-gray-300 border focus:border-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-all">
                    </div>
                </div>

                <!-- Archivos (Uploads) -->
                <div class="bg-blue-50 rounded-xl p-6 mb-8 border border-blue-100">
                    <h4 class="text-sm font-bold text-blue-900 mb-4 flex items-center"><i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i> GESTIÓN DE ARCHIVOS GESTOR</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Foto -->
                        <div>
                            <label class="block text-xs font-bold text-blue-800 uppercase tracking-wide mb-3">Reemplazar Foto (.JPG, .JPEG o .PNG solamente)</label>
                            <div class="flex items-center space-x-4">
                                <img src="<?= htmlspecialchars($inv['imagen_perfil']) ?>" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-sm bg-white" alt="Actual">
                                <input type="file" name="foto" accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                            </div>
                        </div>

                        <!-- CV -->
                        <div>
                            <label class="block text-xs font-bold text-blue-800 uppercase tracking-wide mb-3">Reemplazar Currículum (.PDF solamente)</label>
                            <div class="flex items-center">
                                <?php if($inv['cv_url'] && $inv['cv_url'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($inv['cv_url']) ?>" target="_blank" class="mr-4 text-red-500 hover:text-red-700" title="Ver CV actual"><i data-lucide="file-text" class="w-8 h-8"></i></a>
                                <?php else: ?>
                                    <div class="mr-4 w-8 h-8 rounded bg-gray-200 flex items-center justify-center text-gray-400" title="Sin archivo"><i data-lucide="file-x" class="w-4 h-4"></i></div>
                                <?php endif; ?>
                                <input type="file" name="cv" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-700 cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Semblanza Corta -->
                <div class="mb-8">
                     <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 flex items-center">
                        Semblanza Corta (Resumen para tarjeta)
                        <span class="ml-2 px-2 py-0.5 rounded text-[10px] bg-gray-200 text-gray-600 font-medium">Máx. 255 caracteres</span>
                     </label>
                     <textarea name="semblanza_corta" rows="2" maxlength="255" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-4 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white leading-relaxed"><?= htmlspecialchars($inv['semblanza_corta'] ?? '') ?></textarea>
                </div>

                <!-- Semblanza -->
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 flex items-center">
                        Semblanza Profesional
                        <span class="ml-2 px-2 py-0.5 rounded text-[10px] bg-gray-200 text-gray-600 font-medium">Soporta múltiples párrafos</span>
                     </label>
                     <textarea name="semblanza" rows="8" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-4 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white leading-relaxed"><?= htmlspecialchars($inv['semblanza']) ?></textarea>
                </div>

            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Guardar Cambios Principales
                </button>
            </div>
        </form>

    </div>

    <!-- COLUMNA DERECHA: Sub-módulo de Líneas de Investigación -->
    <div class="xl:col-span-1 space-y-6">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 relative">
            <div class="px-6 py-4 border-b border-gray-100 bg-purple-50 flex items-center justify-between rounded-t-xl">
                <div class="flex items-center">
                    <i data-lucide="network" class="w-5 h-5 mr-2 text-purple-600"></i>
                    <h3 class="font-bold text-gray-800">Especialidad de investigación (<?= count($lineas) ?>)</h3>
                </div>
            </div>
            
            <div class="p-4 space-y-4" id="sortable-specialties">
                <?php if(empty($lineas)): ?>
                    <p class="text-sm text-gray-500 text-center py-4">No tiene especialidades de investigación asociadas aún.</p>
                <?php else: ?>
                    <?php foreach($lineas as $l): ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 group relative hover:border-purple-300 transition-colors cursor-default" data-id="<?= $l['id'] ?>">
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <button onclick="toggleEditLine(<?= $l['id'] ?>)" class="text-gray-300 hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                            </button>
                            <a href="admin.php?modulo=editar_investigador&id=<?= $id ?>&del_line=<?= $l['id'] ?>" onclick="return confirm('¿Quitar esta especialidad del perfil?');" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div id="line-view-<?= $l['id'] ?>">
                            <div class="flex items-center mb-1">
                                <div class="drag-handle cursor-grab active:cursor-grabbing p-1 mr-2 text-gray-300 hover:text-purple-600 transition-colors">
                                    <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                </div>
                                <h4 class="font-bold text-gray-900 text-sm pr-12"><?= htmlspecialchars($l['titulo']) ?></h4>
                            </div>
                            <p class="text-xs text-gray-500 pl-8"><?= htmlspecialchars($l['descripcion']) ?></p>
                        </div>
                        <div id="line-edit-<?= $l['id'] ?>" class="hidden mt-2">
                             <form method="POST" class="space-y-2">
                                <input type="hidden" name="action" value="update_line">
                                <input type="hidden" name="line_id" value="<?= $l['id'] ?>">
                                <input type="text" name="titulo_linea" value="<?= htmlspecialchars($l['titulo']) ?>" class="w-full text-xs border rounded p-1 font-bold">
                                <textarea name="desc_linea" class="w-full text-xs border rounded p-1" rows="2"><?= htmlspecialchars($l['descripcion']) ?></textarea>
                                <input type="hidden" name="orden_linea" value="<?= $l['orden'] ?>">
                                <div class="flex items-center space-x-2">
                                    <button type="submit" class="bg-blue-600 text-white text-[10px] px-2 py-1 rounded">Guardar</button>
                                    <button type="button" onclick="toggleEditLine(<?= $l['id'] ?>)" class="bg-gray-200 text-gray-600 text-[10px] px-2 py-1 rounded">Cancelar</button>
                                </div>
                             </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Gestión de Líneas de Investigación -->
            <div class="border-t border-gray-100 bg-gray-50">
                
            <!-- Gestión Única de Líneas -->
            <div class="border-t border-gray-100 bg-gray-50 p-4 rounded-b-xl">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="manage_line">
                    
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-600 uppercase flex items-center"><i data-lucide="plus-circle" class="w-3 h-3 mr-1"></i> Vincular Especialidad de investigación</h4>
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="is_new_line" id="toggle-new-line" onchange="toggleNewLineForm()" class="sr-only peer">
                            <div class="w-7 h-4 bg-gray-200 rounded-full peer-checked:bg-purple-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-3 relative"></div>
                            <span class="ml-2 text-[10px] font-bold text-gray-500 uppercase group-hover:text-purple-600 transition-colors">Nueva Especialidad</span>
                        </label>
                    </div>

                    <!-- Selector Existente Transformado a Buscador Inteligente Premium -->
                    <div id="section-existing" class="relative group">
                        <?php 
                            $lineasCatalogo = $pdo->query("SELECT titulo FROM lineas_investigacion UNION SELECT titulo FROM investigador_lineas ORDER BY titulo ASC")->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="relative">
                            <input type="text" id="smart-search-input" name="titulo_maestro" autocomplete="off" placeholder="Escribe para buscar especialidad..." 
                                class="w-full rounded-xl border-gray-200 border bg-gray-50 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 p-3.5 outline-none text-sm shadow-sm transition-all pr-12 font-medium">
                            
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <i data-lucide="search" class="w-5 h-5"></i>
                            </div>

                            <!-- Dropdown de Resultados Premium -->
                            <div id="smart-results-container" class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-2xl z-[100] hidden max-h-64 overflow-y-auto animate-in fade-in zoom-in-95 duration-200 custom-scroll">
                                <div class="p-2 space-y-1" id="smart-results-list">
                                    <!-- Opciones inyectadas por JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos Nueva Especialidad (Ocultos por defecto) -->
                    <div id="section-new" class="hidden space-y-3 bg-white p-3 rounded-lg border border-purple-100 shadow-sm">
                        <input type="text" name="titulo_linea_nueva" placeholder="Título de la nueva Especialidad de investigación" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors">
                        <textarea name="desc_linea_nueva" placeholder="Descripción clara..." class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors" rows="2"></textarea>
                        <div class="flex items-center">
                            <input type="checkbox" name="registrar_maestra" id="reg-maestra" checked class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 mr-2">
                            <label for="reg-maestra" class="text-[10px] font-medium text-gray-500 uppercase">Guardar en catálogo general</label>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <div class="flex-1 text-[10px] text-gray-400 font-medium italic" id="hint-text">Elige una Especialidad existente de la lista o activa el switch para crear una nueva.</div>
                        <input type="hidden" name="orden_linea" value="<?= count($lineas)+1 ?>">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-transform active:scale-95 shadow-sm">Agregar</button>
                    </div>
                </form>
            </div>

            </div>
        </div>

        <!-- PUBLICACIONES DESTACADAS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-amber-50 flex items-center justify-between">
                <div class="flex items-center">
                    <i data-lucide="book-open" class="w-5 h-5 mr-2 text-amber-600"></i>
                    <h3 class="font-bold text-gray-800">Publicaciones (<?= count($publicaciones) ?>)</h3>
                </div>
            </div>
            
            <div class="p-4 space-y-4" id="sortable-publications">
                <?php if(empty($publicaciones)): ?>
                    <p class="text-sm text-gray-500 text-center py-4">No tiene publicaciones destacadas asociadas aún.</p>
                <?php else: ?>
                    <?php foreach($publicaciones as $p): ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 group relative hover:border-amber-300 transition-colors cursor-default" data-id="<?= $p['id'] ?>">
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <button onclick="toggleEditPub(<?= $p['id'] ?>)" class="text-gray-300 hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                            </button>
                            <a href="admin.php?modulo=editar_investigador&id=<?= $id ?>&del_pub=<?= $p['id'] ?>" onclick="return confirm('¿Quitar esta publicación del perfil?');" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div id="pub-view-<?= $p['id'] ?>">
                            <div class="flex items-start mb-1">
                                <div class="drag-handle cursor-grab active:cursor-grabbing p-1 mr-2 text-gray-300 hover:text-amber-600 transition-colors mt-0.5">
                                    <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                </div>
                                <div class="pr-12">
                                    <h4 class="font-bold text-gray-900 text-sm leading-tight mb-1"><?= htmlspecialchars($p['titulo']) ?></h4>
                                    <?php if(!empty($p['subtitulo'])): ?>
                                    <p class="text-xs text-gray-500 mb-1"><?= htmlspecialchars($p['subtitulo']) ?></p>
                                    <?php endif; ?>
                                    <?php if(!empty($p['enlace'])): ?>
                                    <a href="<?= htmlspecialchars($p['enlace']) ?>" target="_blank" class="text-[10px] text-blue-500 hover:underline flex items-center"><i data-lucide="external-link" class="w-3 h-3 mr-1"></i>Ver enlace</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div id="pub-edit-<?= $p['id'] ?>" class="hidden mt-2 border-t pt-3 border-amber-100">
                             <form method="POST" class="space-y-2">
                                <input type="hidden" name="action" value="update_pub">
                                <input type="hidden" name="pub_id" value="<?= $p['id'] ?>">
                                
                                <label class="block text-[10px] font-bold text-gray-400 uppercase">Título de la publicación</label>
                                <input type="text" name="titulo_pub" value="<?= htmlspecialchars($p['titulo']) ?>" placeholder="Título principal" required class="w-full text-xs border rounded p-1.5 font-bold outline-none focus:border-amber-500">
                                
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mt-2">Detalle / Subtítulo</label>
                                <input type="text" name="subtitulo_pub" value="<?= htmlspecialchars($p['subtitulo']) ?>" placeholder="Subtítulo o detalle" class="w-full text-xs border rounded p-1.5 text-gray-600 outline-none focus:border-amber-500">
                                
                                <div class="flex space-x-2 mt-2">
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Enlace (Opcional)</label>
                                        <input type="text" name="enlace_pub" value="<?= htmlspecialchars($p['enlace']) ?>" placeholder="URL" class="w-full text-xs border rounded p-1.5 outline-none focus:border-amber-500">
                                    </div>
                                    <input type="hidden" name="orden_pub" value="<?= $p['orden'] ?>">
                                </div>
                                <div class="flex space-x-2 mt-3 pt-2">
                                    <button type="submit" class="flex-1 bg-amber-600 text-white text-[10px] px-2 py-1.5 rounded font-bold hover:bg-amber-700">Guardar</button>
                                    <button type="button" onclick="toggleEditPub(<?= $p['id'] ?>)" class="flex-1 bg-gray-200 text-gray-600 text-[10px] px-2 py-1.5 rounded font-bold hover:bg-gray-300">Cancelar</button>
                                </div>
                             </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Formulario Nueva Publicación -->
            <div class="border-t border-gray-100 bg-gray-50 p-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase flex items-center mb-3"><i data-lucide="plus-circle" class="w-3 h-3 mr-1"></i> Añadir Publicación</h4>
                <form method="POST" class="space-y-2 bg-white p-3 rounded-lg border border-amber-100 shadow-sm">
                    <input type="hidden" name="action" value="add_pub">
                    <input type="text" name="titulo_pub" placeholder="Ej: Educación y valores en México..." required class="w-full rounded border-gray-300 border focus:border-amber-500 focus:ring-amber-500 p-2 outline-none text-xs shadow-sm">
                    <input type="text" name="subtitulo_pub" placeholder="Ej: Libro — Autor / Coordinador (más reciente)" class="w-full rounded border-gray-300 border focus:border-amber-500 focus:ring-amber-500 p-2 outline-none text-xs shadow-sm text-gray-600">
                    <input type="text" name="enlace_pub" placeholder="Enlace URL (Opcional)" class="w-full rounded border-gray-300 border focus:border-amber-500 focus:ring-amber-500 p-2 outline-none text-xs shadow-sm">
                    
                    <div class="flex items-center space-x-2 pt-2">
                        <input type="hidden" name="orden_pub" value="<?= count($publicaciones)+1 ?>">
                        <div class="flex-1 text-right">
                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-transform active:scale-95 shadow-sm">Añadir</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function toggleEditLine(id) {
    const view = document.getElementById('line-view-' + id);
    const edit = document.getElementById('line-edit-' + id);
    if (view.classList.contains('hidden')) {
        view.classList.remove('hidden');
        edit.classList.add('hidden');
        lucide.createIcons(); // Re-render icons if needed, though here we just hide/show
    } else {
        view.classList.add('hidden');
        edit.classList.remove('hidden');
    }
}

function toggleNewLineForm() {
    const isNew = document.getElementById('toggle-new-line').checked;
    const sectionExisting = document.getElementById('section-existing');
    const sectionNew = document.getElementById('section-new');
    const hintText = document.getElementById('hint-text');

    if (isNew) {
        sectionExisting.classList.add('hidden');
        sectionNew.classList.remove('hidden');
        hintText.innerText = "Escribe el nuevo título y descripción para esta Especialidad.";
    } else {
        sectionExisting.classList.remove('hidden');
        sectionNew.classList.add('hidden');
        hintText.innerText = "Elige una Especialidad existente de la lista o activa el switch para crear una nueva.";
    }
}

function toggleEditPub(id) {
    const view = document.getElementById('pub-view-' + id);
    const edit = document.getElementById('pub-edit-' + id);
    if (view.classList.contains('hidden')) {
        view.classList.remove('hidden');
        edit.classList.add('hidden');
    } else {
        view.classList.add('hidden');
        edit.classList.remove('hidden');
    }
}

// --- BUSCADOR INTELIGENTE PREMIUM ---
const lineasData = <?= json_encode(array_column($lineasCatalogo, 'titulo')) ?>;
const smartInput = document.getElementById('smart-search-input');
const resultsContainer = document.getElementById('smart-results-container');
const resultsList = document.getElementById('smart-results-list');

if (smartInput) {
    smartInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        resultsList.innerHTML = '';
        
        if (query.length < 1) {
            resultsContainer.classList.add('hidden');
            return;
        }

        // Filtrar y ordenar por relevancia
        const filtered = lineasData
            .filter(item => item.toLowerCase().includes(query))
            .sort((a, b) => {
                const aLow = a.toLowerCase();
                const bLow = b.toLowerCase();
                const aStarts = aLow.startsWith(query);
                const bStarts = bLow.startsWith(query);
                
                if (aStarts && !bStarts) return -1;
                if (!aStarts && bStarts) return 1;
                return aLow.localeCompare(bLow); // Alfabético si ambos (o ninguno) empiezan igual
            })
            .slice(0, 12); // Limitar a los 12 mejores resultados para enfoque

        if (filtered.length > 0) {
            resultsContainer.classList.remove('hidden');
            filtered.forEach(item => {
                const div = document.createElement('div');
                div.className = 'px-4 py-3 hover:bg-blue-50 hover:text-blue-700 rounded-xl cursor-pointer text-sm font-semibold text-gray-700 transition-all flex items-center group/item';
                div.innerHTML = `
                    <i data-lucide="hash" class="w-3.5 h-3.5 mr-2.5 text-gray-300 group-hover/item:text-blue-400"></i>
                    ${item}
                `;
                div.onclick = () => {
                    smartInput.value = item;
                    resultsContainer.classList.add('hidden');
                };
                resultsList.appendChild(div);
            });
            lucide.createIcons();
        } else {
            resultsContainer.classList.add('hidden');
        }
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!smartInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('hidden');
        }
    });

    smartInput.addEventListener('focus', () => {
        if (smartInput.value.trim().length > 0) {
            resultsContainer.classList.remove('hidden');
        }
    });
}

// --- REORDENAMIENTO SORTEABLE ---
const sortableEl = document.getElementById('sortable-specialties');
if (sortableEl) {
    new Sortable(sortableEl, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'bg-purple-50',
        onEnd: function() {
            const order = Array.from(sortableEl.children).map(el => el.getAttribute('data-id'));
            const formData = new FormData();
            formData.append('action', 'reorder_lines');
            order.forEach(id => formData.append('order[]', id));

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Notificación discreta opcional
                    console.log('Orden guardado');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
}

// --- REORDENAMIENTO PUBLICACIONES ---
const sortablePubs = document.getElementById('sortable-publications');
if (sortablePubs) {
    new Sortable(sortablePubs, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'bg-amber-50',
        onEnd: function() {
            const order = Array.from(sortablePubs.children).map(el => el.getAttribute('data-id'));
            const formData = new FormData();
            formData.append('action', 'reorder_pubs');
            order.forEach(id => formData.append('order[]', id));

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('Orden de publicaciones guardado');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
}
</script>
