<?php
// admin_nueva_noticia.php
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$error = '';
$not = [
    'titulo' => '',
    'descripcion_corta' => '',
    'descripcion_larga' => '',
    'fecha_publicacion' => date('Y-m-d\TH:i')
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_news') {
    $not['titulo'] = trim($_POST['titulo'] ?? '');
    $not['descripcion_corta'] = trim($_POST['descripcion_corta'] ?? '');
    $not['descripcion_larga'] = trim($_POST['descripcion_larga'] ?? '');
    $not['fecha_publicacion'] = $_POST['fecha_publicacion'] ?? date('Y-m-d H:i:s');

    if (empty($not['titulo'])) {
        $error = 'El título es obligatorio.';
    } else {
        $imagen_portada = './img/placeholder.jpg';
        $galeria = [];

        // 1. Subir Imagen Portada
        if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                if (!is_dir('img/noticias')) mkdir('img/noticias', 0755, true);
                $new_name = uniqid('news_p_') . '.' . $ext;
                $dest = 'img/noticias/' . $new_name;
                if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $dest)) {
                    $imagen_portada = './' . $dest;
                }
            }
        }

        // 2. Subir Galería (Múltiple)
        if (isset($_FILES['galeria'])) {
            if (!is_dir('img/noticias/galeria')) mkdir('img/noticias/galeria', 0755, true);
            foreach ($_FILES['galeria']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['galeria']['error'][$key] == 0) {
                    $ext = strtolower(pathinfo($_FILES['galeria']['name'][$key], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        // Nombre super-único con microtiempo y bytes aleatorios
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
            $stmt = $pdo->prepare("INSERT INTO noticias (titulo, descripcion_corta, descripcion_larga, imagen_portada, galeria, fecha_publicacion) VALUES (?, ?, ?, ?, ?, ?)");
            // Usar array_values() para asegurar que el JSON sea un array [0,1,2] y no un objeto {0:.., 1:..}
            if ($stmt->execute([
                $not['titulo'], $not['descripcion_corta'], $not['descripcion_larga'],
                $imagen_portada, json_encode(array_values($galeria)), $not['fecha_publicacion']
            ])) {
                echo "<script>window.location.href='admin.php?modulo=noticias&status=created';</script>";
                exit;
            } else {
                $error = 'Error al guardar en la base de datos.';
            }
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
            <h2 class="text-2xl font-bold text-gray-900">Crear Nueva Noticia</h2>
            <p class="text-gray-500 text-sm mt-1">Publica una noticia o comunicado académico</p>
        </div>
    </div>
</div>

<?php if($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <input type="hidden" name="action" value="create_news">
    
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 flex items-center">
                <i data-lucide="file-text" class="w-5 h-5 mr-2 text-blue-600"></i>
                <h3 class="font-bold text-blue-900">Contenido de la Noticia</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Título de la Noticia <span class="text-red-500">*</span></label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($not['titulo']) ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-gray-400"
                        placeholder="Ej. Resultados del Congreso Internacional 2024">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha y Hora de Publicación</label>
                        <input type="datetime-local" name="fecha_publicacion" value="<?= htmlspecialchars($not['fecha_publicacion']) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción Corta (Resumen cards)</label>
                    <textarea name="descripcion_corta" rows="2" maxlength="255"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        placeholder="Breve extracto que se verá en la página de inicio..."><?= htmlspecialchars($not['descripcion_corta']) ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contenido Detallado</label>
                    <textarea name="descripcion_larga" rows="10"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        placeholder="Escribe aquí todo el desarrollo de la noticia..."><?= htmlspecialchars($not['descripcion_larga']) ?></textarea>
                    
                    <div class="mt-3 flex items-center">
                        <label class="inline-flex items-center cursor-pointer group">
                             <input type="checkbox" name="es_cursiva" class="sr-only peer">
                             <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 relative"></div>
                             <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">Activar Estilo Cursivo</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-amber-50 flex items-center">
                <i data-lucide="images" class="w-5 h-5 mr-2 text-amber-600"></i>
                <h3 class="font-bold text-amber-900">Galería de Imágenes</h3>
            </div>
            <div class="p-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fotos Adicionales (.JPG, .JPEG o .PNG solamente)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-amber-400 transition-colors bg-gray-50 group">
                    <div class="space-y-1 text-center">
                        <i data-lucide="image-plus" class="mx-auto h-12 w-12 text-gray-400 group-hover:text-amber-500 transition-colors"></i>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 focus-within:outline-none px-4 py-2 shadow-sm border border-transparent transition-all">
                                <span>Seleccionar fotos</span>
                                <input name="galeria[]" type="file" class="sr-only" accept=".jpg,.jpeg,.png" multiple onchange="previewGallery(this)">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 font-medium">Puedes elegir varias fotos manteniendo presionado Ctrl o Shift</p>
                        <div id="gallery-counter" class="hidden mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                             0 fotos seleccionadas
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
                <h3 class="font-bold text-gray-900">Imagen de Portada (.JPG, .JPEG o .PNG)</h3>
            </div>
            <div class="p-6">
                <div class="aspect-video w-full rounded-lg bg-gray-100 border border-gray-200 overflow-hidden relative group mb-4 flex items-center justify-center">
                    <div id="main-preview-placeholder" class="text-center">
                        <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-300"></i>
                        <p class="text-xs text-gray-400 mt-2 font-medium">Sin imagen seleccionada</p>
                    </div>
                    <img id="main-preview" class="hidden w-full h-full object-cover">
                    <label class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                        <span class="text-white text-sm font-medium">Seleccionar Portada</span>
                        <input type="file" name="imagen_portada" class="hidden" accept=".jpg,.jpeg,.png" onchange="previewMain(this)">
                    </label>
                </div>
                <p class="text-xs text-gray-500 text-center italic">Esta es la imagen principal que se verá en la tarjeta y al inicio del detalle.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition-colors shadow-md flex items-center justify-center mb-3">
                <i data-lucide="save" class="w-5 h-5 mr-2"></i> Publicar Noticia
            </button>
            <a href="admin.php?modulo=noticias" class="w-full block text-center py-3 text-gray-500 font-medium hover:bg-gray-50 rounded-lg transition-colors border border-gray-200">
                Cancelar
            </a>
        </div>
    </div>
</form>

<script>
let fileBank = [];   // Array simple de File objects

function previewMain(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['jpg', 'jpeg', 'png'].includes(ext)) {
            alert('Formato de imagen inválido. Solo se permite .JPG, .JPEG y .PNG.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('main-preview');
            const placeholder = document.getElementById('main-preview-placeholder');
            
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
}

function updateCounter() {
    const counter = document.getElementById('gallery-counter');
    if (fileBank.length > 0) {
        counter.classList.remove('hidden');
        counter.textContent = `${fileBank.length} foto${fileBank.length === 1 ? '' : 's'} seleccionada${fileBank.length === 1 ? '' : 's'}`;
    } else {
        counter.classList.add('hidden');
    }
}

function previewGallery(input) {
    const container = document.getElementById('gallery-preview');
    Array.from(input.files).forEach(file => {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['jpg', 'jpeg', 'png'].includes(ext)) {
            alert(`El archivo "${file.name}" no es un formato válido. Solo se permite .JPG, .JPEG y .PNG.`);
            return;
        }
        if (fileBank.some(f => f.name === file.name && f.size === file.size)) return;
        fileBank.push(file);

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

// Interceptar el submit y enviar con fetch+FormData para incluir todos los archivos del banco
document.querySelector('form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Guardando...';

    const fd = new FormData(this);
    // Eliminar el campo galeria[] vacío y agregar los archivos del banco
    fd.delete('galeria[]');
    fileBank.forEach(file => fd.append('galeria[]', file, file.name));

    const res = await fetch('api_guardar_noticia.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
        window.location.href = json.redirect;
    } else {
        alert('Error: ' + json.error);
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="save" class="w-5 h-5 mr-2"></i> Publicar Noticia';
        lucide.createIcons();
    }
});
</script>

