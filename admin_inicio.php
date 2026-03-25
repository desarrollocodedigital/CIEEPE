<?php
// admin_inicio.php
// Estadísticas Rápidas
$countInvestigadores = $pdo->query("SELECT COUNT(*) FROM investigadores")->fetchColumn();
$countProyectos = $pdo->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
$countLineas = $pdo->query("SELECT COUNT(*) FROM lineas_investigacion")->fetchColumn();

// Últimos Investigadores
$ultimosInvestigadores = $pdo->query("SELECT nombre, cargo_o_grado, imagen_perfil, creado_en FROM investigadores ORDER BY id DESC LIMIT 5")->fetchAll();
// Últimos Proyectos
$ultimosProyectos = $pdo->query("SELECT titulo, estado, categoria, creado_en FROM proyectos ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<!-- Tarjetas de Métricas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center hover:shadow-md transition-shadow">
        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 mr-4">
            <i data-lucide="users" class="w-7 h-7"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Investigadores</p>
            <h3 class="text-3xl font-bold text-gray-900"><?= number_format($countInvestigadores) ?></h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center hover:shadow-md transition-shadow">
        <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 mr-4">
            <i data-lucide="folder-kanban" class="w-7 h-7"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Proyectos Activos</p>
            <h3 class="text-3xl font-bold text-gray-900"><?= number_format($countProyectos) ?></h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center hover:shadow-md transition-shadow">
        <div class="w-14 h-14 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 mr-4">
            <i data-lucide="book-open" class="w-7 h-7"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Líneas de Investigación</p>
            <h3 class="text-3xl font-bold text-gray-900"><?= number_format($countLineas) ?></h3>
        </div>
    </div>
</div>

<!-- Grid Principal -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- Últimos Investigadores -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-lg">Investigadores Recientes</h3>
            <a href="admin.php?modulo=investigadores" class="text-sm font-medium text-blue-600 hover:underline">Ver todos</a>
        </div>
        <div class="divide-y divide-gray-100">
            <?php if(empty($ultimosInvestigadores)): ?>
                <div class="p-6 text-center text-gray-500 text-sm">No hay investigadores registrados.</div>
            <?php else: ?>
                <?php foreach($ultimosInvestigadores as $inv): ?>
                <div class="px-6 py-4 flex items-center hover:bg-gray-50 transition-colors group">
                    <img src="<?= htmlspecialchars($inv['imagen_perfil']) ?>" onerror="this.src='./img/placeholder.jpg'" alt="<?= htmlspecialchars($inv['nombre']) ?>" class="w-10 h-10 rounded-full object-cover mr-4 ring-2 ring-gray-100">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($inv['nombre']) ?></p>
                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($inv['cargo_o_grado']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Últimos Proyectos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-lg">Últimos Proyectos</h3>
            <a href="admin.php?modulo=proyectos" class="text-sm font-medium text-blue-600 hover:underline">Ver todos</a>
        </div>
        <div class="divide-y divide-gray-100">
            <?php if(empty($ultimosProyectos)): ?>
                <div class="p-6 text-center text-gray-500 text-sm">No hay proyectos registrados.</div>
            <?php else: ?>
                <?php foreach($ultimosProyectos as $pro): ?>
                <div class="px-6 py-4 flex items-center hover:bg-gray-50 transition-colors">
                    <div class="w-2 h-2 rounded-full <?= $pro['estado'] == 'En Puerta' ? 'bg-amber-500' : ($pro['estado'] == 'Terminados' ? 'bg-green-500' : 'bg-blue-500') ?> mr-4"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($pro['titulo']) ?></p>
                        <p class="text-xs text-gray-500 flex items-center mt-0.5">
                            <i data-lucide="tag" class="w-3 h-3 mr-1"></i> <?= htmlspecialchars($pro['categoria']) ?>
                        </p>
                    </div>
                    <span class="ml-4 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                        <?= htmlspecialchars($pro['estado']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
