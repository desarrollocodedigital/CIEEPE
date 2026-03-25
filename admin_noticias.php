<?php
// admin_noticias.php
$mensaje = '';

if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    
    // Primero, obtener la imagen de portada y galeria para eliminar archivos
    $stmt = $pdo->prepare("SELECT imagen_portada, galeria FROM noticias WHERE id = ?");
    $stmt->execute([$id_del]);
    $not_del = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($not_del) {
        // Eliminar portada
        if ($not_del['imagen_portada'] && !str_contains($not_del['imagen_portada'], 'placeholder') && file_exists(ltrim($not_del['imagen_portada'], './'))) {
            if (unlink(ltrim($not_del['imagen_portada'], './'))); 
        }
        // Eliminar galeria
        if ($not_del['galeria']) {
            $gal = json_decode($not_del['galeria'], true);
            if (is_array($gal)) {
                foreach ($gal as $img) {
                    if (file_exists(ltrim($img, './'))) {
                        unlink(ltrim($img, './'));
                    }
                }
            }
        }
    }

    $pdo->prepare("DELETE FROM noticias WHERE id = ?")->execute([$id_del]);
    $mensaje = 'Noticia eliminada correctamente.';
    echo "<script>window.history.replaceState(null, null, 'admin.php?modulo=noticias');</script>";
}

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') $mensaje = 'Noticia publicada con éxito.';
    if ($_GET['status'] === 'updated') $mensaje = 'Cambios guardados correctamente.';
    echo "<script>window.history.replaceState(null, null, 'admin.php?modulo=noticias');</script>";
}

$noticias = $pdo->query("SELECT * FROM noticias ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Gestión de Noticias</h2>
        <p class="text-gray-500 text-sm mt-1">Comunicados y noticias académicas (<?= count($noticias) ?>)</p>
    </div>
    <a href="admin.php?modulo=nueva_noticia" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Nueva Noticia
    </a>
</div>

<?php if($mensaje): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center" id="success-alert">
    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($mensaje) ?>
</div>
<script>
    setTimeout(() => {
        const el = document.getElementById('success-alert');
        if(el) { el.style.transition = 'opacity 0.5s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }
    }, 4000);
</script>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left align-middle">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50/80 border-b border-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold w-16">ID</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Noticia</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Fecha</th>
                    <th scope="col" class="px-6 py-4 font-semibold text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach($noticias as $not): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 text-gray-500">
                        #<?= $not['id'] ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 overflow-hidden border border-gray-200">
                                <img src="<?= htmlspecialchars($not['imagen_portada'] ?? './img/placeholder.jpg') ?>" alt="Portada" class="w-full h-full object-cover">
                            </div>
                            <div class="max-w-xs md:max-w-md truncate">
                                <div class="font-bold text-gray-900 mb-1 leading-tight truncate"><?= htmlspecialchars($not['titulo']) ?></div>
                                <div class="text-xs text-gray-500 truncate">
                                    <?= htmlspecialchars($not['descripcion_corta']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        <?= date('d/m/Y H:i', strtotime($not['fecha_publicacion'])) ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="admin.php?modulo=editar_noticia&id=<?= $not['id'] ?>" class="text-gray-400 hover:text-blue-600 transition-colors" title="Editar">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </a>
                            <a href="admin.php?modulo=noticias&delete=<?= $not['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta noticia?');" class="text-gray-400 hover:text-red-600 transition-colors" title="Eliminar">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (count($noticias) === 0): ?>
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        No hay noticias registradas. Crea una nueva para empezar.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
