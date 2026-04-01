<?php
// admin_nosotros_config.php
$mensaje = '';
$tipo_mensaje = 'success';

// --- Manejar guardado de datos generales ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_text') {
    $section_title  = trim($_POST['section_title'] ?? '');
    $main_title     = trim($_POST['main_title'] ?? '');
    $description_1  = trim($_POST['description_1'] ?? '');
    $description_2  = trim($_POST['description_2'] ?? '');

    // Puntos dinámicos
    $points_raw = $_POST['points'] ?? [];
    $points = array_values(array_filter(array_map('trim', $points_raw)));
    $points_json = json_encode($points, JSON_UNESCAPED_UNICODE);

    // Subida de imagen
    $image_path = trim($_POST['current_image'] ?? './img/Edificio.png');
    if (!empty($_FILES['nosotros_imagen']['name'])) {
        $allowed_mimes = ['image/jpeg', 'image/png'];
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['nosotros_imagen']['tmp_name']);
        finfo_close($finfo);

        $ext = strtolower(pathinfo($_FILES['nosotros_imagen']['name'], PATHINFO_EXTENSION));

        if (!in_array($mime, $allowed_mimes) || !in_array($ext, $allowed_exts)) {
            $mensaje = '❌ Tipo de archivo no permitido. Solo se acepta .JPG o .PNG';
            $tipo_mensaje = 'error';
        } else {
            $filename = 'nosotros_img_' . time() . '.' . $ext;
            $dest = __DIR__ . '/img/' . $filename;
            if (move_uploaded_file($_FILES['nosotros_imagen']['tmp_name'], $dest)) {
                // Borrar anterior si existe
                if ($image_path && file_exists($image_path) && !str_contains($image_path, 'Edificio.png')) {
                    @unlink($image_path);
                }
                $image_path = './img/' . $filename;
            }
        }
    }

    if ($mensaje === '') {
        $count = $pdo->query("SELECT COUNT(*) FROM nosotros_config")->fetchColumn();
        if ($count > 0) {
            $stmt = $pdo->prepare("UPDATE nosotros_config SET section_title=?, main_title=?, description_1=?, description_2=?, image_path=?, points=? WHERE id=1");
        } else {
            $stmt = $pdo->prepare("INSERT INTO nosotros_config (section_title, main_title, description_1, description_2, image_path, points) VALUES (?,?,?,?,?,?)");
        }
        $stmt->execute([$section_title, $main_title, $description_1, $description_2, $image_path, $points_json]);
        $mensaje = '✅ Sección Nosotros actualizada correctamente.';
    }
}

// --- Obtener datos actuales ---
$config = $pdo->query("SELECT * FROM nosotros_config LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$config) {
    $config = [
        'section_title'  => 'Sobre Nosotros',
        'main_title'     => 'Investigación con Enfoque Humanista y Científico',
        'description_1'  => '',
        'description_2'  => '',
        'image_path'     => './img/Edificio.png',
        'points'         => '[]'
    ];
}
$points = json_decode($config['points'] ?? '[]', true) ?: [];
?>

<!-- Alerta -->
<?php if ($mensaje): ?>
<div class="mb-6 px-4 py-3 rounded-lg border flex items-center gap-3 <?= $tipo_mensaje === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?>" id="alert-msg">
    <i data-lucide="<?= $tipo_mensaje === 'error' ? 'x-circle' : 'check-circle' ?>" class="w-5 h-5 flex-shrink-0"></i>
    <span><?= htmlspecialchars($mensaje) ?></span>
</div>
<script>setTimeout(() => { const el = document.getElementById('alert-msg'); if(el) el.style.opacity = '0'; }, 4000);</script>
<?php endif; ?>

<!-- Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Configuración: Sobre Nosotros</h2>
        <p class="text-gray-500 text-sm mt-1">Personaliza el contenido de la sección <em>"Sobre Nosotros"</em> de la página principal.</p>
    </div>
    <a href="index.html#nosotros" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-lg transition-colors">
        <i data-lucide="external-link" class="w-4 h-4"></i> Ver en el Sitio
    </a>
</div>

<form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_text">
    <input type="hidden" name="current_image" value="<?= htmlspecialchars($config['image_path']) ?>">

    <div class="grid lg:grid-cols-2 gap-8">

        <!-- COLUMNA IZQUIERDA: Texto -->
        <div class="space-y-6">
            <!-- Títulos -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <i data-lucide="type" class="w-4 h-4 text-blue-500"></i> Títulos
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtítulo (texto azul pequeño)</label>
                        <input type="text" name="section_title" value="<?= htmlspecialchars($config['section_title']) ?>"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="Ej. Sobre Nosotros">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título Principal</label>
                        <input type="text" name="main_title" value="<?= htmlspecialchars($config['main_title']) ?>"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="Ej. Investigación con Enfoque Humanista...">
                    </div>
                </div>
            </div>

            <!-- Descripciones -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <i data-lucide="align-left" class="w-4 h-4 text-blue-500"></i> Descripciones
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Párrafo 1</label>
                        <textarea name="description_1" rows="4"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                            placeholder="Primer párrafo de descripción..."><?= htmlspecialchars($config['description_1']) ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Párrafo 2</label>
                        <textarea name="description_2" rows="4"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                            placeholder="Segundo párrafo de descripción..."><?= htmlspecialchars($config['description_2']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Puntos clave -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-1 flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-4 h-4 text-blue-500"></i> Puntos Clave
                </h3>
                <p class="text-xs text-gray-400 mb-5">Los puntos que aparecen con la marca ✓ al lado del texto.</p>

                <div id="points-container" class="space-y-3">
                    <?php foreach ($points as $i => $point): ?>
                    <div class="flex items-center gap-2 point-row">
                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 text-xs font-bold">✓</span>
                        </div>
                        <input type="text" name="points[]" value="<?= htmlspecialchars($point) ?>"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="Escribe un punto clave...">
                        <button type="button" onclick="removePoint(this)" class="w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" onclick="addPoint()"
                    class="mt-4 w-full py-2.5 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-blue-400 hover:text-blue-500 hover:bg-blue-50 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Añadir Punto
                </button>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Imagen -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <i data-lucide="image" class="w-4 h-4 text-blue-500"></i> Imagen de la Sección
                </h3>

                <div class="grid grid-cols-2 gap-4 items-start">
                    <!-- Preview actual -->
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Imagen Actual</p>
                        <div class="rounded-lg overflow-hidden border border-gray-200 bg-gray-50 aspect-video flex items-center justify-center">
                            <img id="current-preview" src="<?= htmlspecialchars($config['image_path']) ?>"
                                alt="Imagen Actual Nosotros" class="w-full h-full object-cover">
                        </div>
                        <p class="text-xs text-gray-400 mt-1 truncate"><?= htmlspecialchars(basename($config['image_path'])) ?></p>
                    </div>

                    <!-- Drop zone nueva imagen -->
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Nueva Imagen</p>
                        <div id="dropzone-nosotros"
                            class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer aspect-video flex flex-col items-center justify-center text-center p-3"
                            onclick="document.getElementById('nosotros-image-input').click()">
                            <div id="dropzone-nosotros-placeholder">
                                <p class="text-xs text-gray-500">Clic o arrastra imagen</p>
                                <p class="text-xs text-gray-400 mt-0.5">.JPG, .JPEG o .PNG</p>
                            </div>
                            <img id="dropzone-nosotros-preview" src="" alt="Preview" class="hidden w-full h-full object-cover rounded">
                        </div>
                        <input type="file" id="nosotros-image-input" name="nosotros_imagen" accept=".jpg,.jpeg,.png" class="hidden">
                    </div>
                </div>
            </div>

            <!-- Vista previa de la sección -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="eye" class="w-4 h-4 text-blue-500"></i> Vista Previa
                </h3>
                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 text-sm">
                    <p class="text-blue-600 font-bold uppercase tracking-wider text-xs mb-1" id="preview-section-title"><?= htmlspecialchars($config['section_title']) ?></p>
                    <p class="text-gray-900 font-bold text-base mb-2" id="preview-main-title"><?= htmlspecialchars($config['main_title']) ?></p>
                    <p class="text-gray-500 text-xs leading-relaxed mb-2" id="preview-desc1"><?= htmlspecialchars($config['description_1']) ?></p>
                    <div id="preview-points" class="space-y-1">
                        <?php foreach ($points as $p): ?>
                        <div class="flex items-center gap-2">
                            <span class="text-blue-600 text-xs font-bold">✓</span>
                            <span class="text-gray-600 text-xs"><?= htmlspecialchars($p) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón guardar -->
    <div class="mt-8 flex justify-end">
        <button type="submit"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl shadow-md hover:shadow-lg transition-all">
            <i data-lucide="save" class="w-5 h-5"></i> Guardar Cambios
        </button>
    </div>
</form>

<script>
// === Drop Zone para imagen Nosotros ===
const dzNosotros = document.getElementById('dropzone-nosotros');
const inputNosotros = document.getElementById('nosotros-image-input');
const previewNosotros = document.getElementById('dropzone-nosotros-preview');
const placeholderNosotros = document.getElementById('dropzone-nosotros-placeholder');
const currentPreview = document.getElementById('current-preview');

function showNosotrosPreview(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        previewNosotros.src = e.target.result;
        previewNosotros.classList.remove('hidden');
        placeholderNosotros.classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

inputNosotros.addEventListener('change', () => showNosotrosPreview(inputNosotros.files[0]));

dzNosotros.addEventListener('dragover', e => { e.preventDefault(); dzNosotros.classList.add('border-blue-400','bg-blue-50'); });
dzNosotros.addEventListener('dragleave', () => dzNosotros.classList.remove('border-blue-400','bg-blue-50'));
dzNosotros.addEventListener('drop', e => {
    e.preventDefault();
    dzNosotros.classList.remove('border-blue-400','bg-blue-50');
    const f = e.dataTransfer.files[0];
    if (f && f.type.startsWith('image/')) {
        const dt = new DataTransfer();
        dt.items.add(f);
        inputNosotros.files = dt.files;
        showNosotrosPreview(f);
    }
});

// === Puntos dinámicos ===
function addPoint() {
    const container = document.getElementById('points-container');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 point-row';
    div.innerHTML = `
        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            <span class="text-blue-600 text-xs font-bold">✓</span>
        </div>
        <input type="text" name="points[]" value=""
            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            placeholder="Escribe un punto clave...">
        <button type="button" onclick="removePoint(this)" class="w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>`;
    container.appendChild(div);
    lucide.createIcons();
    updatePreview();
}

function removePoint(btn) {
    btn.closest('.point-row').remove();
    updatePreview();
}

// === Vista previa en tiempo real ===
function updatePreview() {
    document.getElementById('preview-section-title').textContent = document.querySelector('[name=section_title]').value;
    document.getElementById('preview-main-title').textContent = document.querySelector('[name=main_title]').value;
    document.getElementById('preview-desc1').textContent = document.querySelector('[name=description_1]').value;

    const inputs = document.querySelectorAll('#points-container input[name="points[]"]');
    const pContainer = document.getElementById('preview-points');
    pContainer.innerHTML = '';
    inputs.forEach(inp => {
        if (inp.value.trim()) {
            const d = document.createElement('div');
            d.className = 'flex items-center gap-2';
            d.innerHTML = `<span class="text-blue-600 text-xs font-bold">✓</span><span class="text-gray-600 text-xs">${inp.value}</span>`;
            pContainer.appendChild(d);
        }
    });
}

document.querySelectorAll('[name=section_title],[name=main_title],[name=description_1]').forEach(el => {
    el.addEventListener('input', updatePreview);
});
document.getElementById('points-container').addEventListener('input', updatePreview);
</script>
