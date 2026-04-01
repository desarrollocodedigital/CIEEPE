<?php
// admin_editar_linea.php

$error = '';
$mensaje = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href='admin.php?modulo=lineas';</script>";
    exit;
}

$id = (int)$_GET['id'];

// Obtener la línea actual
$stmt = $pdo->prepare("SELECT * FROM lineas_investigacion WHERE id = ?");
$stmt->execute([$id]);
$linea = $stmt->fetch();

if (!$linea) {
    echo "<script>window.location.href='admin.php?modulo=lineas';</script>";
    exit;
}

// ---> ACCIÓN: EDITAR LÍNEA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_linea') {
    $linea['titulo'] = trim($_POST['titulo'] ?? '');
    $linea['descripcion'] = trim($_POST['descripcion'] ?? '');
    $linea['icono'] = trim($_POST['icono'] ?? 'book');
    $linea['color'] = trim($_POST['color'] ?? 'blue');

    // Validación básica
    if (empty($linea['titulo'])) {
        $error = "Por favor, ingresa el título de la línea de investigación.";
    }

    if (empty($error)) {
        // Obtener el título antiguo para la actualización en cascada
        $old_stmt = $pdo->prepare("SELECT titulo FROM lineas_investigacion WHERE id = ?");
        $old_stmt->execute([$id]);
        $titulo_antiguo = $old_stmt->fetchColumn();
        $titulo_nuevo = $linea['titulo'];

        $sql = "UPDATE lineas_investigacion SET titulo = ?, descripcion = ?, icono = ?, color = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            $titulo_nuevo, 
            $linea['descripcion'], 
            $linea['icono'], 
            $linea['color'],
            $id
        ])) {
            // Si el nombre de la línea cambió, actualizamos las referencias en otras tablas (Cascada)
            if ($titulo_antiguo && $titulo_nuevo !== $titulo_antiguo) {
                // 1. Actualizar Investigadores (Columna etiqueta_badge)
                $upd_inv = $pdo->prepare("UPDATE investigadores SET etiqueta_badge = ? WHERE etiqueta_badge = ?");
                $upd_inv->execute([$titulo_nuevo, $titulo_antiguo]);

                // 2. Actualizar Proyectos (Columna categoria)
                $upd_pro = $pdo->prepare("UPDATE proyectos SET categoria = ? WHERE categoria = ?");
                $upd_pro->execute([$titulo_nuevo, $titulo_antiguo]);
            }

            $mensaje = "Línea de investigación actualizada exitosamente.";
        } else {
            $error = 'Ocurrió un error al actualizar la base de datos.';
        }
    }
}
?>

<!-- Cabecera -->
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center">
        <a href="admin.php?modulo=lineas" class="mr-4 text-gray-400 hover:text-blue-600 transition-colors p-2 bg-white rounded-lg shadow-sm" title="Volver a Líneas">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Editar Línea de Investigación</h2>
            <p class="text-gray-500 text-sm mt-1">Actualiza los datos de esta línea</p>
        </div>
    </div>
</div>

<?php if($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if($mensaje): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <!-- COLUMNA IZQUIERDA: Formulario Principal -->
    <div class="xl:col-span-2 space-y-6">
        
        <form method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <input type="hidden" name="action" value="edit_linea">
            
            <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 flex items-center justify-between">
                <div class="flex items-center">
                    <i data-lucide="edit-3" class="w-5 h-5 mr-2 text-blue-600"></i>
                    <h3 class="font-bold text-blue-900">Detalles de la Línea</h3>
                </div>
                <div class="text-xs text-blue-600 font-semibold bg-blue-100 px-3 py-1 rounded-full">
                    ID: <?= $id ?>
                </div>
            </div>
            
            <div class="p-6">
                <!-- GRID CAMPOS -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Título de la Línea *</label>
                        <input type="text" name="titulo" value="<?= htmlspecialchars($linea['titulo']) ?>" required class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white transition-colors">
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 flex items-center">
                        Descripción General
                    </label>
                    <textarea name="descripcion" rows="4" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 focus:ring-blue-500 p-4 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white leading-relaxed"><?= htmlspecialchars($linea['descripcion']) ?></textarea>
                </div>

                <hr class="border-gray-100 my-8">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Ícono (Lucide) *</label>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="<?= htmlspecialchars($linea['icono']) ?>" class="w-5 h-5 text-gray-600"></i>
                            </div>
                            <div class="relative flex-1">
                                <i data-lucide="feather" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
                                <input type="text" name="icono" value="<?= htmlspecialchars($linea['icono']) ?>" required class="w-full rounded-lg border-gray-300 border focus:border-blue-500 pl-10 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white">
                            </div>
                        </div>
                         <p class="text-[10px] text-gray-400 mt-2">Nombre del ícono de <a href="https://lucide.dev/icons/" target="_blank" class="text-blue-500 hover:underline">Lucide Icons</a>.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Color *</label>
                        <div class="relative mt-2">
                            <div id="color-preview" class="absolute left-3 top-3 w-4 h-4 rounded-full bg-<?= $linea['color'] ?>-500 border border-white shadow-sm transition-colors duration-300"></div>
                            <select name="color" onchange="updateColorPreview(this.value)" class="w-full rounded-lg border-gray-300 border focus:border-blue-500 pl-10 pr-10 p-2.5 outline-none text-sm shadow-sm bg-gray-50 focus:bg-white appearance-none">
                                <?php
                                $colores = [
                                    'blue' => 'Azul (Blue)',
                                    'teal' => 'Verde Azulado (Teal)',
                                    'indigo' => 'Índigo (Indigo)',
                                    'amber' => 'Ámbar (Amber)',
                                    'rose' => 'Rosa (Rose)',
                                    'emerald' => 'Esmeralda (Emerald)',
                                    'purple' => 'Morado (Purple)'
                                ];
                                foreach($colores as $val => $label) {
                                    $sel = ($linea['color'] === $val) ? 'selected' : '';
                                    echo "<option value=\"$val\" $sel>$label</option>";
                                }
                                ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
    
    <!-- COLUMNA DERECHA: Ayuda -->
    <div class="xl:col-span-1 space-y-6">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6">
            <h4 class="text-blue-900 font-bold mb-3 flex items-center"><i data-lucide="info" class="w-4 h-4 mr-2"></i> Recordatorio</h4>
            <p class="text-sm text-blue-800 leading-relaxed">Al guardar los cambios, esta información se actualizará para cualquier investigador que tenga asignada esta línea, así como en la página principal.</p>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
    function updateColorPreview(color) {
        const preview = document.getElementById('color-preview');
        const colors = ['blue', 'teal', 'indigo', 'amber', 'rose', 'emerald', 'purple'];
        colors.forEach(c => preview.classList.remove(`bg-${c}-500`));
        preview.classList.add(`bg-${color}-500`);
    }
</script>
