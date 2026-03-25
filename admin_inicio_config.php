<?php
// admin_inicio_config.php - Módulo de configuración del Hero del sitio
$mensaje = '';
$tipo_msg = 'green';

// Cargar config actual
$config_rows = $pdo->query("SELECT clave, valor FROM site_config WHERE clave LIKE 'hero_%' OR clave IN ('site_logo', 'hero_logo_plata')")->fetchAll(PDO::FETCH_KEY_PAIR);
$badge       = $config_rows['hero_badge']       ?? 'ENEES';
$titulo      = $config_rows['hero_titulo']       ?? '';
$descripcion = $config_rows['hero_descripcion']  ?? '';
$imagen_actual = $config_rows['hero_imagen']     ?? '';
$logo_actual   = $config_rows['site_logo']       ?? './img/logo.png';
$logo_plata_actual = $config_rows['hero_logo_plata'] ?? './img/LogoPlata.png';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_badge       = trim($_POST['hero_badge']       ?? '');
    $new_titulo      = trim($_POST['hero_titulo']       ?? '');
    $new_descripcion = trim($_POST['hero_descripcion']  ?? '');
    $new_imagen      = $imagen_actual; 
    $new_logo        = $logo_actual;
    $new_logo_plata  = $logo_plata_actual;

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

    // Subir nueva imagen de fondo
    if (!empty($_FILES['hero_imagen']['name'])) {
        $ext = strtolower(pathinfo($_FILES['hero_imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'hero_bg_' . time() . '.' . $ext;
            $destino  = './img/' . $filename;
            if (move_uploaded_file($_FILES['hero_imagen']['tmp_name'], $destino)) {
                $new_imagen = $destino;
            } else {
                $mensaje = 'Error al subir la imagen de fondo.';
                $tipo_msg = 'red';
            }
        }
    }

    // Subir logo principal (color)
    if (empty($mensaje) && !empty($_FILES['site_logo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'logo_color_' . time() . '.' . $ext;
            $destino  = './img/' . $filename;
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $destino)) {
                $new_logo = $destino;
            } else {
                $mensaje = 'Error al subir el logo principal.';
                $tipo_msg = 'red';
            }
        }
    }

    // Subir logo plata (para fondos oscuros)
    if (empty($mensaje) && !empty($_FILES['hero_logo_plata']['name'])) {
        $ext = strtolower(pathinfo($_FILES['hero_logo_plata']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'logo_plata_' . time() . '.' . $ext;
            $destino  = './img/' . $filename;
            if (move_uploaded_file($_FILES['hero_logo_plata']['tmp_name'], $destino)) {
                $new_logo_plata = $destino;
            } else {
                $mensaje = 'Error al subir el logo plata.';
                $tipo_msg = 'red';
            }
        }
    }

    if (empty($mensaje)) {
        $stmt = $pdo->prepare("INSERT INTO site_config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $stmt->execute(['hero_badge',       $new_badge]);
        $stmt->execute(['hero_titulo',      $new_titulo]);
        $stmt->execute(['hero_descripcion', $new_descripcion]);
        $stmt->execute(['hero_imagen',      $new_imagen]);
        $stmt->execute(['site_logo',        $new_logo]);
        $stmt->execute(['hero_logo_plata',  $new_logo_plata]);

        // Actualizar variables locales para la vista
        $badge       = $new_badge;
        $titulo      = $new_titulo;
        $descripcion = $new_descripcion;
        $imagen_actual = $new_imagen;
        $logo_actual   = $new_logo;
        $logo_plata_actual = $new_logo_plata;

        $mensaje = '¡Configuración actualizada con éxito!';
        $tipo_msg = 'green';
    }
}
?>

<!-- Header -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Configuración del Inicio</h2>
        <p class="text-gray-500 text-sm mt-1">Personaliza el contenido del hero y los logotipos institucionales.</p>
    </div>
    <a href="index.html" target="_blank" class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-2 rounded-lg hover:bg-blue-100 transition-colors">
        <i data-lucide="external-link" class="w-4 h-4 mr-2"></i> Ver Sitio
    </a>
</div>

<?php if($mensaje): ?>
<div class="bg-<?= $tipo_msg ?>-50 border border-<?= $tipo_msg ?>-200 text-<?= $tipo_msg ?>-700 px-4 py-3 rounded-lg mb-6 flex items-center">
    <i data-lucide="<?= $tipo_msg === 'green' ? 'check-circle' : 'alert-triangle' ?>" class="w-5 h-5 mr-2"></i>
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Formulario -->
    <div class="lg:col-span-2">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">

            <!-- Sección: Logotipos -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-6 flex items-center">
                    <i data-lucide="image-upscale" class="w-5 h-5 mr-2 text-indigo-500"></i> Logotipos Institucionales
                </h3>

                <div class="space-y-8">
                    <!-- Logo Principal (Color) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Logotipo Principal (Color)</label>
                            <p class="text-xs text-gray-400 mb-3">Se usa en fondo blanco y páginas secundarias.</p>
                            <div class="p-4 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-200 mb-2">
                                <img src="<?= htmlspecialchars($logo_actual) ?>" alt="Logo color" class="h-12 w-auto object-contain">
                            </div>
                        </div>
                        <div>
                            <div id="logo-drop" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-indigo-400 transition-colors">
                                <i data-lucide="upload" class="w-6 h-6 mx-auto text-gray-400 mb-1"></i>
                                <p id="logo-filename" class="text-xs text-gray-500">Haz clic o arrastra el logo color</p>
                                <input type="file" name="site_logo" id="logo-img-input" accept="image/*" class="hidden">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6"></div>

                    <!-- Logo Plata (Invertido) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Logotipo Hero (Blanco/Plata)</label>
                            <p class="text-xs text-gray-400 mb-3">Se usa sobre el fondo del hero (transparente).</p>
                            <div class="p-4 rounded-lg bg-gray-800 flex items-center justify-center border border-gray-700 mb-2">
                                <img src="<?= htmlspecialchars($logo_plata_actual) ?>" alt="Logo plata" class="h-12 w-auto object-contain">
                            </div>
                        </div>
                        <div>
                            <div id="logo-plata-drop" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 transition-colors">
                                <i data-lucide="upload" class="w-6 h-6 mx-auto text-gray-400 mb-1"></i>
                                <p id="logo-plata-filename" class="text-xs text-gray-500">Haz clic o arrastra el logo plata</p>
                                <input type="file" name="hero_logo_plata" id="logo-plata-img-input" accept="image/*" class="hidden">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Texto Hero -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                    <i data-lucide="type" class="w-5 h-5 mr-2 text-blue-500"></i> Textos del Hero
                </h3>

                <div class="space-y-4">
                    <!-- Badge -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Badge (etiqueta pequeña)</label>
                        <p class="text-xs text-gray-400 mb-2">Texto breve que aparece sobre el título principal (ej. "ENEES").</p>
                        <input type="text" name="hero_badge" value="<?= htmlspecialchars($badge) ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            maxlength="30" required>
                    </div>

                    <!-- Título -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título Principal</label>
                        <textarea name="hero_titulo" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-y"
                            required><?= htmlspecialchars($titulo) ?></textarea>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <p class="text-xs text-gray-400 mb-2">Subtítulo que aparece debajo del título principal.</p>
                        <textarea name="hero_descripcion" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-y"
                            required><?= htmlspecialchars($descripcion) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Sección: Imagen de Fondo -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-6 flex items-center">
                    <i data-lucide="image" class="w-5 h-5 mr-2 text-purple-500"></i> Imagen de Fondo del Hero
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div>
                        <?php if($imagen_actual): ?>
                        <div class="mb-2 rounded-lg overflow-hidden border border-gray-200">
                            <img src="<?= htmlspecialchars($imagen_actual) ?>" alt="Imagen actual" class="w-full h-32 object-cover">
                        </div>
                        <p class="text-xs text-gray-500">Imagen actual: <?= basename($imagen_actual) ?></p>
                        <?php else: ?>
                        <div class="mb-2 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 h-32 flex items-center justify-center">
                            <i data-lucide="image" class="w-8 h-8 text-gray-300"></i>
                        </div>
                        <p class="text-xs text-gray-500 italic text-center">Sin imagen configurada</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Subir nueva imagen</label>
                        <p class="text-xs text-gray-400 mb-3">Se recomienda 1920×1080px (JPG/PNG).</p>
                        <div id="hero-drop" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-purple-400 transition-colors">
                            <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-gray-400 mb-2"></i>
                            <p id="hero-filename" class="text-sm text-gray-500">Haz clic o arrastra la foto</p>
                            <input type="file" name="hero_imagen" id="hero-img-input" accept="image/*" class="hidden">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón guardar -->
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2.5 rounded-lg shadow-sm transition-colors flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <!-- Panel lateral: Estadísticas automáticas -->
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center">
                <i data-lucide="bar-chart-2" class="w-5 h-5 mr-2 text-green-500"></i> Estadísticas (Auto)
            </h3>
            <p class="text-xs text-gray-500 mb-5">Estos valores se calculan automáticamente desde la base de datos y se muestran en el sitio.</p>
            <?php
            $n_inv  = (int) $pdo->query("SELECT COUNT(*) FROM investigadores")->fetchColumn();
            $n_proy = (int) $pdo->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
            $n_lin  = (int) $pdo->query("SELECT COUNT(*) FROM lineas_investigacion")->fetchColumn();
            ?>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <div class="flex items-center">
                        <i data-lucide="users" class="w-4 h-4 text-blue-600 mr-3"></i>
                        <span class="text-sm font-medium text-gray-700">Investigadores</span>
                    </div>
                    <span class="text-2xl font-extrabold text-blue-600"><?= $n_inv ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                    <div class="flex items-center">
                        <i data-lucide="folder-kanban" class="w-4 h-4 text-amber-600 mr-3"></i>
                        <span class="text-sm font-medium text-gray-700">Proyectos</span>
                    </div>
                    <span class="text-2xl font-extrabold text-amber-600"><?= $n_proy ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <i data-lucide="book-open" class="w-4 h-4 text-green-600 mr-3"></i>
                        <span class="text-sm font-medium text-gray-700">Líneas</span>
                    </div>
                    <span class="text-2xl font-extrabold text-green-600"><?= $n_lin ?></span>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
            <h4 class="font-bold text-blue-900 mb-2 flex items-center text-sm">
                <i data-lucide="info" class="w-4 h-4 mr-2"></i> ¿Cómo funciona?
            </h4>
            <ul class="text-xs text-blue-800 space-y-2">
                <li>• Los cambios se aplican <strong>inmediatamente</strong> al guardar.</li>
                <li>• El logo se actualiza en la cabecera de todas las páginas que lo soporten.</li>
                <li>• La imagen de fondo solo afecta a la sección de inicio (Hero).</li>
            </ul>
        </div>
    </div>
</div>

<script>
(function(){
    function setupDropzone(dropId, inputId, labelId) {
        const drop = document.getElementById(dropId);
        const input = document.getElementById(inputId);
        const label = document.getElementById(labelId);

        drop.addEventListener('click', () => input.click());
        input.addEventListener('change', function() {
            if (this.files[0]) {
                label.textContent = this.files[0].name;
                label.classList.add('text-blue-600', 'font-medium');

                // Preview
                const reader = new FileReader();
                reader.onload = e => {
                    const prev = drop.querySelector('img');
                    if (prev) prev.remove();
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-24 object-contain rounded mt-2';
                    if (dropId === 'hero-drop') img.className = 'w-full h-32 object-cover rounded mt-2';
                    drop.appendChild(img);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
        drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('border-blue-400'); });
        drop.addEventListener('dragleave', () => drop.classList.remove('border-blue-400'));
        drop.addEventListener('drop', e => {
            e.preventDefault();
            drop.classList.remove('border-blue-400');
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    }

    setupDropzone('hero-drop', 'hero-img-input', 'hero-filename');
    setupDropzone('logo-drop', 'logo-img-input', 'logo-filename');
    setupDropzone('logo-plata-drop', 'logo-plata-img-input', 'logo-plata-filename');
})();
</script>

