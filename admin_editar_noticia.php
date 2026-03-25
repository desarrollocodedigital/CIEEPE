<?php
// admin_editar_noticia.php
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href='admin.php?modulo=noticias';</script>";
    exit;
}

$id_noticia = $_GET['id'];
$error = '';
$success = '';

if (isset($_GET['status']) && $_GET['status'] === 'updated') {
    $success = 'Noticia actualizada correctamente.';
}

// Obtener noticia
$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id_noticia]);
$not = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$not) {
    echo "<script>window.location.href='admin.php?modulo=noticias';</script>";
    exit;
}

$galeria = json_decode($not['galeria'] ?? '[]', true);

// Procesar Actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'edit_news') {
        $titulo = trim($_POST['titulo'] ?? '');
        $desc_c = trim($_POST['descripcion_corta'] ?? '');
        $desc_l = trim($_POST['descripcion_larga'] ?? '');
        $fecha = $_POST['fecha_publicacion'] ?? $not['fecha_publicacion'];
        
        if (empty($titulo)) {
            $error = 'El título es obligatorio.';
        } else {
            $imagen_portada = $not['imagen_portada'];
            
            // Subir Nueva Portada
            if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    if (!is_dir('img/noticias')) mkdir('img/noticias', 0755, true);
                    $new_name = uniqid('news_p_') . '.' . $ext;
                    $dest = 'img/noticias/' . $new_name;
                    if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $dest)) {
                        // Eliminar anterior si no es placeholder
                        if ($not['imagen_portada'] && !str_contains($not['imagen_portada'], 'placeholder') && file_exists(ltrim($not['imagen_portada'], './'))) {
                            unlink(ltrim($not['imagen_portada'], './'));
                        }
                        $imagen_portada = './' . $dest;
                    }
                }
            }

            // Subir más a la Galería
            if (isset($_FILES['nueva_galeria'])) {
                if (!is_dir('img/noticias/galeria')) mkdir('img/noticias/galeria', 0755, true);
                foreach ($_FILES['nueva_galeria']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['nueva_galeria']['error'][$key] == 0) {
                        $ext = strtolower(pathinfo($_FILES['nueva_galeria']['name'][$key], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $unique_id = bin2hex(random_bytes(8));
                            $new_name = 'news_g_' . $unique_id . '.' . $ext;
                            $dest = 'img/noticias/galeria/' . $new_name;
                            if (move_uploaded_file($tmp_name, $dest)) {
                                $galeria[] = './' . $dest;
                            }
                        }
                    }
                }
            }

            if (!$error) {
                $stmt = $pdo->prepare("UPDATE noticias SET titulo = ?, descripcion_corta = ?, descripcion_larga = ?, imagen_portada = ?, galeria = ?, fecha_publicacion = ? WHERE id = ?");
                // Usar array_values() para asegurar que no se guarde un objeto JSON con índices numéricos
                if ($stmt->execute([$titulo, $desc_c, $desc_l, $imagen_portada, json_encode(array_values($galeria)), $fecha, $id_noticia])) {
                    echo "<script>window.location.href='admin.php?modulo=editar_noticia&id=$id_noticia&status=updated';</script>";
                    exit;
                }
            }
        }
    }

    // Eliminar imagen específica de la galería
    if ($_POST['action'] === 'delete_gal_img') {
        $img_to_delete = $_POST['img_path'];
        $key = array_search($img_to_delete, $galeria);
        if ($key !== false) {
            unset($galeria[$key]);
            if (file_exists(ltrim($img_to_delete, './'))) {
                unlink(ltrim($img_to_delete, './'));
            }
            $stmt = $pdo->prepare("UPDATE noticias SET galeria = ? WHERE id = ?");
            // Usar array_values() aquí también (aunque ya estaba en el commit anterior lo reforzaré si acaso)
            $stmt->execute([json_encode(array_values($galeria)), $id_noticia]);
            echo "<script>window.location.href='admin.php?modulo=editar_noticia&id=$id_noticia&status=updated';</script>";
            exit;
        }
    }
}
?>

<div class="flex items-center justify-between mb-8">
    <div class="flex items-center">
        <a href="admin.php?modulo=noticias" class="mr-4 text-gray-400 hover:text-blue-600 transition-colors p-2 bg-white rounded-lg shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Editar Noticia</h2>
            <p class="text-gray-500 text-sm mt-1">#<?= $id_noticia ?> - Actualiza el contenido y las imágenes</p>
        </div>
    </div>
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
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <input type="hidden" name="action" value="edit_news">
    <input type="hidden" name="id" value="<?= $id_noticia ?>">
    
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 flex items-center">
                <i data-lucide="file-edit" class="w-5 h-5 mr-2 text-blue-600"></i>
                <h3 class="font-bold text-blue-900">Contenido Localizado</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Título de la Noticia <span class="text-red-500">*</span></label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($not['titulo']) ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha y Hora</label>
                        <input type="datetime-local" name="fecha_publicacion" value="<?= date('Y-m-d\TH:i', strtotime($not['fecha_publicacion'])) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción Corta</label>
                    <textarea name="descripcion_corta" rows="2" maxlength="255"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"><?= htmlspecialchars($not['descripcion_corta']) ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contenido Detallado</label>
                    <textarea name="descripcion_larga" rows="12"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"><?= htmlspecialchars($not['descripcion_larga']) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Gestión de Galería -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-amber-50 flex items-center">
                <i data-lucide="images" class="w-5 h-5 mr-2 text-amber-600"></i>
                <h3 class="font-bold text-amber-900">Galería de Imágenes</h3>
            </div>
            <div class="p-6">
                <!-- Imágenes Actuales -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <?php foreach($galeria as $img): ?>
                        <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="deleteGalImage('<?= htmlspecialchars($img) ?>')" class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">Añadir más fotos</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-amber-400 transition-colors bg-gray-50 group">
                    <div class="space-y-1 text-center">
                        <i data-lucide="upload-cloud" class="mx-auto h-12 w-12 text-gray-400 group-hover:text-amber-500 transition-colors"></i>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 focus-within:outline-none px-4 py-2 shadow-sm border border-transparent transition-all">
                                <span>Seleccionar fotos</span>
                                <input name="nueva_galeria[]" type="file" class="sr-only" accept=".jpg,.jpeg,.png" multiple onchange="previewGallery(this)">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 font-medium">Puedes elegir varias fotos manteniendo presionado Ctrl o Shift</p>
                        <div id="gallery-counter" class="hidden mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                             0 nuevas fotos seleccionadas
                        </div>
                    </div>
                </div>
                <div id="gallery-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6"></div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                <i data-lucide="image" class="w-5 h-5 mr-2 text-gray-600"></i>
                <h3 class="font-bold text-gray-900">Portada Actual</h3>
            </div>
            <div class="p-6">
                <div class="aspect-video w-full rounded-lg bg-gray-100 border border-gray-200 overflow-hidden relative group mb-4">
                    <img id="main-preview" src="<?= htmlspecialchars($not['imagen_portada']) ?>" class="w-full h-full object-cover">
                    <label class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                        <span class="text-white text-sm font-medium">Reemplazar Portada</span>
                        <input type="file" name="imagen_portada" class="hidden" accept="image/*" onchange="previewMain(this)">
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition-colors shadow-md flex items-center justify-center mb-3">
                <i data-lucide="save" class="w-5 h-5 mr-2"></i> Guardar Cambios
            </button>
            <a href="admin.php?modulo=noticias" class="w-full block text-center py-3 text-gray-500 font-medium hover:bg-gray-50 rounded-lg transition-colors border border-gray-200">
                Volver
            </a>
        </div>
    </div>
</form>

<form id="delete-gal-form" method="POST" class="hidden">
    <input type="hidden" name="action" value="delete_gal_img">
    <input type="hidden" name="img_path" id="delete-img-path">
</form>

<script>
let fileBank = [];   // Array simple de File objects

function deleteGalImage(path) {
    if(confirm('¿Seguro que quieres eliminar esta imagen de la galería?')) {
        document.getElementById('delete-img-path').value = path;
        document.getElementById('delete-gal-form').submit();
    }
}

function previewMain(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['jpg', 'jpeg', 'png'].includes(ext)) {
            alert('Formato de imagen inválido. Solo se permite JPG y PNG.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => document.getElementById('main-preview').src = e.target.result;
        reader.readAsDataURL(file);
    }
}

function updateCounter() {
    const counter = document.getElementById('gallery-counter');
    if (fileBank.length > 0) {
        counter.classList.remove('hidden');
        counter.textContent = `${fileBank.length} nueva${fileBank.length === 1 ? '' : 's'} foto${fileBank.length === 1 ? '' : 's'} seleccionada${fileBank.length === 1 ? '' : 's'}`;
    } else {
        counter.classList.add('hidden');
    }
}

function previewGallery(input) {
    const container = document.getElementById('gallery-preview');
    Array.from(input.files).forEach(file => {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['jpg', 'jpeg', 'png'].includes(ext)) {
            alert(`El archivo "${file.name}" no es un formato válido. Solo se permite JPG y PNG.`);
            return;
        }
        if (fileBank.some(f => f.name === file.name && f.size === file.size)) return;
        fileBank.push(file);
        console.log('File added to bank:', file.name, 'Total:', fileBank.length);

        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'relative group aspect-square rounded-lg border border-gray-200 overflow-hidden shadow-sm animate-in fade-in zoom-in duration-300';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover">
                <button type="button" onclick="removeStagedFile('${file.name}', ${file.size}, this)"
                    class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </button>
            `;
            container.appendChild(div);
            lucide.createIcons();
            updateCounter();
        };
        reader.readAsDataURL(file);
    });
    input.value = '';
}

function removeStagedFile(name, size, btn) {
    fileBank = fileBank.filter(f => !(f.name === name && f.size === size));
    btn.parentElement.remove();
    updateCounter();
}

// Interceptar el submit del form principal (edit_news) con fetch+FormData
document.querySelector('form:not(#delete-gal-form)').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Guardando...';

    const fd = new FormData(this);
    fd.delete('nueva_galeria[]');
    console.log('Submitting bank with', fileBank.length, 'files');
    fileBank.forEach(file => {
        fd.append('nueva_galeria[]', file, file.name);
        console.log('Appended to FormData:', file.name);
    });

    const res = await fetch('api_guardar_noticia.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
        window.location.href = json.redirect;
    } else {
        alert('Error: ' + json.error);
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="save" class="w-5 h-5 mr-2"></i> Guardar Cambios';
        lucide.createIcons();
    }
});
</script>

