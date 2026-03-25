<?php
// admin_lineas.php
$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error']);

if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM lineas_investigacion WHERE id = ?")->execute([$id_del]);
    $mensaje = 'Línea eliminada correctamente.';
    echo "<script>window.history.replaceState(null, null, window.location.pathname + '?modulo=lineas');</script>";
}

$lineas = $pdo->query("SELECT * FROM lineas_investigacion ORDER BY id DESC")->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Líneas de Investigación Generales</h2>
        <p class="text-gray-500 text-sm mt-1">Líneas maestras del instituto (<?= count($lineas) ?>)</p>
    </div>
    <a href="admin.php?modulo=nueva_linea" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Añadir Línea
    </a>
</div>

<?php if($mensaje): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($lineas as $lin): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow relative group">
        <div class="absolute top-4 right-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <a href="admin.php?modulo=editar_linea&id=<?= $lin['id'] ?>" class="text-gray-500 hover:text-blue-600 bg-gray-50 p-1.5 rounded-md transition-colors" title="Editar Línea">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
            </a>
            <a href="admin.php?modulo=lineas&delete=<?= $lin['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta línea de investigación?');" class="text-gray-400 hover:text-red-500 bg-gray-50 p-1.5 rounded-md transition-colors" title="Eliminar Línea">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </a>
        </div>
        <?php $color = $lin['color'] ?: 'blue'; ?>
        <div class="w-12 h-12 rounded-lg bg-<?= $color ?>-50 text-<?= $color ?>-600 flex items-center justify-center mb-4">
            <i data-lucide="<?= htmlspecialchars($lin['icono'] ?: 'book') ?>" class="w-6 h-6"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2"><?= htmlspecialchars($lin['titulo']) ?></h3>
        <p class="text-sm text-gray-600 leading-relaxed"><?= htmlspecialchars($lin['descripcion']) ?></p>
    </div>
    <?php endforeach; ?>
</div>

