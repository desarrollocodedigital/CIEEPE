<?php
// admin_nuevo_investigador.php

$mensaje = '';
$error = '';

// Configurar directorios
$img_dir = './img/investigadores/';
$cv_dir = './docs/cvs/';
if (!file_exists($cv_dir)) @mkdir($cv_dir, 0777, true);
if (!file_exists($img_dir)) @mkdir($img_dir, 0777, true);

// Valores iniciales
$inv = [
    'nombre' => '',
    'especialidad' => '',
    'cargo_o_grado' => '',
    'etiqueta_badge' => '',
    'email' => '',
    'telefono' => '',
    'ubicacion' => '',
    'linkedin_url' => '',
    'facebook_url' => '',
    'semblanza' => '',
    'imagen_perfil' => './img/placeholder.jpg',
    'cv_url' => '#'
];

// Obtener listas para desplegables
$lista_lineas = $pdo->query("SELECT titulo FROM lineas_investigacion ORDER BY titulo ASC")->fetchAll(PDO::FETCH_ASSOC);

// ---> ACCIÓN: CREAR PERFIL PRINCIPAL Y ARCHIVOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_profile') {
    $inv['nombre'] = $_POST['nombre'] ?? '';
    $inv['especialidad'] = $_POST['especialidad'] ?? '';
    $inv['cargo_o_grado'] = $_POST['cargo_o_grado'] ?? '';
    $inv['etiqueta_badge'] = $_POST['etiqueta_badge'] ?? '';
    $inv['email'] = $_POST['email'] ?? '';
    $inv['telefono'] = $_POST['telefono'] ?? '';
    $inv['ubicacion'] = $_POST['ubicacion'] ?? '';
    $inv['linkedin_url'] = $_POST['linkedin_url'] ?? '';
    $inv['facebook_url'] = $_POST['facebook_url'] ?? '';
    $inv['semblanza'] = $_POST['semblanza'] ?? '';
    $inv['tipo_investigador'] = $_POST['tipo_investigador'] ?? ''; // Added
    $inv['semblanza_corta'] = $_POST['semblanza_corta'] ?? ''; // Added

    // Validar campos obligatorios
    if (empty($inv['nombre'])) {
        $error = "Por favor, ingresa el nombre del investigador.";
    }
    
    $imagen_perfil = './img/placeholder.jpg';
    $cv_url = '#';

    // 1. Manejo Subida de Imagen
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'png'])) {
            $nuevo_nombre = uniqid('foto_') . '.' . $ext;
            $ruta_img = $img_dir . $nuevo_nombre;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_img)) {
                $imagen_perfil = $ruta_img;
            } else {
                $error .= "Error guardar imagen en servidor. ";
            }
        } else {
             $error .= "Formato de imagen inválido. Solo se permite .jpg o .png. ";
        }
    }

    // 2. Manejo Subida de CV PDF
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $nuevo_nombre = uniqid('cv_') . '.pdf';
            $ruta_cv = $cv_dir . $nuevo_nombre;
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $ruta_cv)) {
                $cv_url = $ruta_cv;
            } else {
                $error .= "Error guardar CV. ";
            }
        } else {
             $error .= "El CV debe ser PDF. ";
        }
    }

    if (empty($error)) {
        // Extract values for the SQL query
        $nombre = $inv['nombre'];
        $especialidad = $inv['especialidad'];
        $cargo = $inv['cargo_o_grado'];
        $tipo_investigador = $inv['tipo_investigador']; // New
        $badge = $inv['etiqueta_badge'];
        $email = $inv['email'];
        $telefono = $inv['telefono'];
        $ubicacion = $inv['ubicacion'];
        $linkedin = $inv['linkedin_url'];
        $facebook = $inv['facebook_url'];
        $semblanza_corta = $inv['semblanza_corta']; // New
        $semblanza_corta = $inv['semblanza_corta']; // New
        $semblanza = $inv['semblanza'];

        $sql = "INSERT INTO investigadores (nombre, especialidad, cargo_o_grado, tipo_investigador, etiqueta_badge, email, telefono, ubicacion, linkedin_url, facebook_url, cv_url, semblanza_corta, semblanza, imagen_perfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            $nombre, 
            $especialidad, 
            $cargo, 
            $tipo_investigador, 
            $badge, 
            $email, 
            $telefono, 
            $ubicacion, 
            $linkedin, 
            $facebook, 
            $cv_url, 
            $semblanza_corta, 
            $semblanza, 
            $imagen_perfil
        ])) {
            $new_id = $pdo->lastInsertId();
            echo "<script>window.location.href='admin.php?modulo=editar_investigador&id=$new_id&status=created';</script>";
            exit;
        } else {
            $error = 'Ocurrió un error al guardar en la base de datos.';
        }
    }
}
?>

<!-- Cabecera -->
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center">
        <a href="admin.php?modulo=investigadores" class="mr-4 text-gray-400 hover:text-blue-600 transition-colors p-2 bg-white rounded-lg shadow-sm" title="Volver a Directorio">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Añadir Nuevo Investigador</h2>
            <p class="text-gray-500 text-sm mt-1">Completa estos datos para crear el perfil principal</p>
        </div>
    </div>
</div>

<?php if($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <!-- COLUMNA IZQUIERDA: Formulario Principal y Uploads -->
    <div class="xl:col-span-2 space-y-6">
        
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <input type="hidden" name="action" value="create_profile">
            
            <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 flex items-center">
                <i data-lucide="user-plus" class="w-5 h-5 mr-2 text-blue-600"></i>
                <h3 class="font-bold text-blue-900">1. Información General y Archivos</h3>
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
                            <input type="text" name="telefono" value="<?= htmlspecialchars($inv['telefono']) ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 pl-10 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white" placeholder="Ej: 999888777">
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
                    <h4 class="text-sm font-bold text-blue-900 mb-4 flex items-center"><i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i> SUBIR ARCHIVOS</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Foto -->
                        <div>
                            <label class="block text-xs font-bold text-blue-800 uppercase tracking-wide mb-3">Foto de Perfil (.JPG o .PNG)</label>
                            <input type="file" name="foto" accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                        </div>

                        <!-- CV -->
                        <div>
                            <label class="block text-xs font-bold text-blue-800 uppercase tracking-wide mb-3">Currículum (.PDF)</label>
                            <input type="file" name="cv" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-700 cursor-pointer">
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
                     </label>
                     <textarea name="semblanza" rows="8" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-4 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white leading-relaxed"><?= htmlspecialchars($inv['semblanza']) ?></textarea>
                </div>

            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Guardar y Continuar
                </button>
            </div>
        </form>

    </div>

    <!-- COLUMNA DERECHA: Aviso de Líneas de Investigación -->
    <div class="xl:col-span-1 space-y-6">
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-8 text-center flex flex-col items-center justify-center">
             <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4 text-gray-400">
                 <i data-lucide="network" class="w-8 h-8"></i>
             </div>
             <h3 class="text-gray-700 font-bold mb-2">2. Especialidad de investigación</h3>
             <p class="text-gray-500 text-sm">Debes guardar primero la información general del investigador para desbloquear la asignación de sus especialidades de investigación.</p>
        </div>
    </div>
</div>
