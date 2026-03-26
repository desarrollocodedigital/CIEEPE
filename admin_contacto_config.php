<?php
// admin_contacto_config.php

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mensaje = '';
$error = '';

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $direccion       = $_POST['direccion'] ?? '';
    $direccion_info  = $_POST['direccion_info'] ?? '';
    $email           = $_POST['email'] ?? '';
    $email_desc      = $_POST['email_desc'] ?? '';
    $telefono        = $_POST['telefono'] ?? '';
    $telefono_desc   = $_POST['telefono_desc'] ?? '';
    $horario         = $_POST['horario'] ?? '';
    $mapa_url        = $_POST['mapa_url'] ?? '';

    try {
        $stmt = $pdo->prepare("UPDATE contacto_config SET 
            direccion = ?, 
            direccion_info = ?, 
            email = ?, 
            email_desc = ?, 
            telefono = ?, 
            telefono_desc = ?, 
            horario = ?, 
            mapa_url = ? 
            WHERE id = 1");
        
        $stmt->execute([
            $direccion, $direccion_info, 
            $email, $email_desc, $telefono, $telefono_desc, $horario, $mapa_url
        ]);
        
        $mensaje = "Configuración de contacto actualizada correctamente.";
    } catch (Exception $e) {
        $error = "Error al actualizar la configuración: " . $e->getMessage();
    }
}

// Obtener valores actuales
$config = $pdo->query("SELECT * FROM contacto_config WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Editar Sección de Contacto</h3>
            <p class="text-sm text-gray-500">Personaliza la información de contacto y el mapa que aparecen en el inicio.</p>
        </div>
        <i data-lucide="map-pin" class="text-blue-600 w-8 h-8"></i>
    </div>

    <div class="p-8">
        <?php if ($mensaje): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 flex items-center">
                <i data-lucide="check-circle" class="mr-3 w-5 h-5"></i>
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 flex items-center">
                <i data-lucide="alert-circle" class="mr-3 w-5 h-5"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">

            <!-- Dirección -->
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Dirección Principal</label>
                    <textarea name="direccion" rows="3" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"><?= htmlspecialchars($config['direccion'] ?? '') ?></textarea>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Información Adicional (Sede/Lugar)</label>
                    <input type="text" name="direccion_info" 
                        value="<?= htmlspecialchars($config['direccion_info'] ?? '') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                        placeholder="Ej: Ciudad Educadora del Saber...">
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- Email, Teléfono y Horario -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Columna Email -->
                <div class="space-y-4 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex items-center text-blue-800 font-bold mb-2">
                        <i data-lucide="mail" class="w-5 h-5 mr-2"></i> Correo Electrónico
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-blue-700 uppercase">Email</label>
                        <input type="email" name="email" required
                            value="<?= htmlspecialchars($config['email'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-blue-700 uppercase">Descripción Email</label>
                        <input type="text" name="email_desc"
                            value="<?= htmlspecialchars($config['email_desc'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <!-- Columna Teléfono -->
                <div class="space-y-4 p-4 bg-teal-50 rounded-xl border border-teal-100">
                    <div class="flex items-center text-teal-800 font-bold mb-2">
                        <i data-lucide="phone" class="w-5 h-5 mr-2"></i> Teléfono
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-teal-700 uppercase">Número</label>
                        <input type="text" name="telefono" required
                            value="<?= htmlspecialchars($config['telefono'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-teal-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-teal-700 uppercase">Comentario Teléfono</label>
                        <input type="text" name="telefono_desc"
                            value="<?= htmlspecialchars($config['telefono_desc'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-teal-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none"
                            placeholder="Ej: Para atención técnica...">
                    </div>
                </div>

                <!-- Columna Horario -->
                <div class="space-y-4 p-4 bg-purple-50 rounded-xl border border-purple-100">
                    <div class="flex items-center text-purple-800 font-bold mb-2">
                        <i data-lucide="clock" class="w-5 h-5 mr-2"></i> Horario
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-purple-700 uppercase">Horario de Atención</label>
                        <input type="text" name="horario" required
                            value="<?= htmlspecialchars($config['horario'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none"
                            placeholder="Ej: Lunes a Viernes, 9am - 6pm">
                    </div>
                    <div class="p-3 bg-white/50 rounded-lg border border-purple-100 mt-2">
                        <p class="text-[10px] text-purple-600 leading-tight italic">
                            Este horario se mostrará con un icono de reloj en la página principal.
                        </p>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- Mapa -->
            <div class="space-y-3">
                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center">
                    <i data-lucide="map" class="w-5 h-5 mr-2 text-red-500"></i> URL de Google Maps (Iframe SRC)
                </label>
                <div class="relative">
                    <input type="text" name="mapa_url" required
                        value="<?= htmlspecialchars($config['mapa_url'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none pr-12">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <i data-lucide="link" class="w-5 h-5"></i>
                    </div>
                </div>
                <p class="text-[10px] text-gray-400 italic">Copia la URL dentro del atributo 'src' del código de inserción que proporciona Google Maps.</p>
            </div>

            <div class="pt-6">
                <button type="submit" 
                    class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-blue-200 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

<div class="mt-8 grid md:grid-cols-2 gap-8">
    <!-- Preview Info -->
    <div class="bg-gray-800 text-white p-6 rounded-xl shadow-inner border border-gray-700">
        <h4 class="text-gray-400 font-bold uppercase text-xs mb-4 flex items-center">
            <i data-lucide="eye" class="w-4 h-4 mr-2"></i> Vista Previa (Datos)
        </h4>
        <div class="space-y-3 text-sm">
            <p><span class="text-blue-400 font-bold">Email:</span> <?= htmlspecialchars($config['email'] ?? '') ?></p>
            <p><span class="text-blue-400 font-bold">Tel:</span> <?= htmlspecialchars($config['telefono'] ?? '') ?></p>
            <p><span class="text-blue-400 font-bold">Horario:</span> <?= htmlspecialchars($config['horario'] ?? '') ?></p>
        </div>
    </div>
    
    <!-- Mapa Preview -->
    <div class="bg-white rounded-xl overflow-hidden shadow-lg border border-gray-200 h-48 md:h-auto">
        <?php if (!empty($config['mapa_url'])): ?>
            <iframe src="<?= htmlspecialchars($config['mapa_url']) ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        <?php else: ?>
            <div class="h-full flex items-center justify-center text-gray-400 italic text-sm p-4 text-center">Configura una URL de mapa para visualizar la vista previa.</div>
        <?php endif; ?>
    </div>
</div>
