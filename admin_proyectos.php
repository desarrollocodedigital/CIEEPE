<?php
// admin_proyectos.php
$mensaje = '';

if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    
    // Primero, obtener la imagen para eliminarla si no es placeholder
    $stmt = $pdo->prepare("SELECT imagen_portada FROM proyectos WHERE id = ?");
    $stmt->execute([$id_del]);
    $pro_del = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pro_del && $pro_del['imagen_portada'] !== './img/placeholder.jpg' && file_exists(ltrim($pro_del['imagen_portada'], './'))) {
        unlink(ltrim($pro_del['imagen_portada'], './'));
    }

    $pdo->prepare("DELETE FROM proyectos WHERE id = ?")->execute([$id_del]);
    $mensaje = 'Proyecto eliminado correctamente.';
    echo "<script>window.history.replaceState(null, null, window.location.pathname + '?modulo=proyectos');</script>";
}

$proyectos = $pdo->query("SELECT * FROM proyectos ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Gestión de Proyectos</h2>
        <p class="text-gray-500 text-sm mt-1">Proyectos de investigación registrados (<?= count($proyectos) ?>)</p>
    </div>
    <a href="admin.php?modulo=nuevo_proyecto" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Nuevo Proyecto
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
                    <th scope="col" class="px-6 py-4 font-semibold">Proyecto</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Estado</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Responsable</th>
                    <th scope="col" class="px-6 py-4 font-semibold text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach($proyectos as $pro): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 text-gray-500">
                        #<?= $pro['id'] ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 overflow-hidden border border-gray-200">
                                <img src="<?= htmlspecialchars($pro['imagen_portada'] ?? './img/placeholder.jpg') ?>" alt="Portada" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 mb-1 leading-tight"><?= htmlspecialchars($pro['titulo']) ?></div>
                                <div class="text-xs text-gray-500 flex items-center">
                                    <i data-lucide="tag" class="w-3 h-3 mr-1"></i> <?= htmlspecialchars($pro['categoria']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                        <?= $pro['estado'] == 'En Puerta' ? 'bg-amber-100 text-amber-800' : ($pro['estado'] == 'En Curso' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                            <?= htmlspecialchars($pro['estado']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-700 font-medium">
                        <?= htmlspecialchars($pro['responsable'] ?? 'N/A') ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="admin.php?modulo=editar_proyecto&id=<?= $pro['id'] ?>" class="text-gray-400 hover:text-blue-600 transition-colors" title="Editar">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </a>
                            <a href="admin.php?modulo=proyectos&delete=<?= $pro['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este proyecto de forma permanente?');" class="text-gray-400 hover:text-red-600 transition-colors" title="Eliminar">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (count($proyectos) === 0): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        No hay proyectos registrados. Crea uno nuevo para empezar.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
