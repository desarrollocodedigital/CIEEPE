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

// Paginación y búsqueda
$por_pagina = 10;
$pagina_actual = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $por_pagina;
$busqueda = trim($_GET['q'] ?? '');

$where = '';
$params = [];
if ($busqueda !== '') {
    $where = "WHERE titulo LIKE ? OR categoria LIKE ? OR responsable LIKE ?";
    $like = '%' . $busqueda . '%';
    $params = [$like, $like, $like];
}

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM proyectos $where");
$stmt_count->execute($params);
$total_registros = $stmt_count->fetchColumn();
$total_paginas = ceil($total_registros / $por_pagina);
if ($pagina_actual > $total_paginas && $total_paginas > 0) $pagina_actual = $total_paginas;

$stmt_pro = $pdo->prepare("SELECT * FROM proyectos $where ORDER BY id DESC LIMIT ? OFFSET ?");
$idx = 1;
foreach ($params as $p) { $stmt_pro->bindValue($idx++, $p, PDO::PARAM_STR); }
$stmt_pro->bindValue($idx++, (int)$por_pagina, PDO::PARAM_INT);
$stmt_pro->bindValue($idx++, (int)$offset, PDO::PARAM_INT);
$stmt_pro->execute();
$proyectos = $stmt_pro->fetchAll();

$base_url = 'admin.php?modulo=proyectos' . ($busqueda !== '' ? '&q=' . urlencode($busqueda) : '');
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Gestión de Proyectos</h2>
        <p class="text-gray-500 text-sm mt-1">Proyectos registrados (<span id="pro-count"><?= $total_registros ?></span>)</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <!-- Buscador -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
            </div>
            <input type="text" id="pro-search" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar proyecto..." autocomplete="off"
                class="pl-9 pr-9 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-64 transition-all">
            <button type="button" id="clear-search" class="<?= empty($busqueda) ? 'hidden' : '' ?> absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <a href="admin.php?modulo=nuevo_proyecto" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Nuevo Proyecto
        </a>
    </div>
</div>

<?php if($mensaje): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center" id="success-alert">
    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($mensaje) ?>
</div>
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
            <tbody id="pro-tbody" class="divide-y divide-gray-100">
                <?php foreach($proyectos as $pro): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 text-gray-500">#<?= $pro['id'] ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 overflow-hidden border border-gray-200">
                                <img src="<?= htmlspecialchars($pro['imagen_portada'] ?? './img/placeholder.jpg') ?>" class="w-full h-full object-cover">
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
                    <td class="px-6 py-4 text-gray-700 font-medium"><?= htmlspecialchars($pro['responsable'] ?? 'N/A') ?></td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="admin.php?modulo=editar_proyecto&id=<?= $pro['id'] ?>" class="text-gray-400 hover:text-blue-600 transition-colors" title="Editar"><i data-lucide="edit-2" class="w-4 h-4"></i></a>
                            <a href="admin.php?modulo=proyectos&delete=<?= $pro['id'] ?>" onclick="return confirm('¿Eliminar proyecto?');" class="text-gray-400 hover:text-red-600 transition-colors" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($proyectos)): ?>
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay proyectos para mostrar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación UI -->
    <div id="pagination-container">
        <?php if ($total_paginas > 1): ?>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <span class="text-sm text-gray-500">
                Mostrando <strong><?= $offset + 1 ?></strong> a <strong><?= min($offset + $por_pagina, $total_registros) ?></strong> de <strong><?= $total_registros ?></strong>
            </span>
            <div class="flex space-x-1">
                <?php if ($pagina_actual > 1): ?>
                    <a href="<?= $base_url ?>&p=<?= $pagina_actual - 1 ?>" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors">&laquo; Anterior</a>
                <?php endif; ?>
                <div class="hidden sm:flex space-x-1 mx-2">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="<?= $base_url ?>&p=<?= $i ?>" class="px-3 py-1.5 text-sm font-medium <?= $i == $pagina_actual ? 'text-blue-600 bg-blue-50 border-blue-200 font-bold' : 'text-gray-600 bg-white border-gray-300 hover:bg-gray-50' ?> border rounded-lg transition-colors"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?= $base_url ?>&p=<?= $pagina_actual + 1 ?>" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    const input = document.getElementById('pro-search');
    const clearBtn = document.getElementById('clear-search');
    const tbody = document.getElementById('pro-tbody');
    const countEl = document.getElementById('pro-count');
    const paginationContainer = document.getElementById('pagination-container');
    let debounce;

    function escapeHTML(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderRows(results) {
        if (results.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">No se encontraron proyectos.</td></tr>`;
            return;
        }
        tbody.innerHTML = results.map(pro => `
            <tr class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4 text-gray-500">#${pro.id}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 overflow-hidden border border-gray-200">
                            <img src="${escapeHTML(pro.imagen_portada || './img/placeholder.jpg')}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 mb-1 leading-tight">${escapeHTML(pro.titulo)}</div>
                            <div class="text-xs text-gray-500 flex items-center">
                                <i data-lucide="tag" class="w-3 h-3 mr-1"></i> ${escapeHTML(pro.categoria)}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full ${pro.estado === 'En Puerta' ? 'bg-amber-100 text-amber-800' : (pro.estado === 'En Curso' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')}">
                        ${escapeHTML(pro.estado)}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-700 font-medium">${escapeHTML(pro.responsable || 'N/A')}</td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end space-x-3">
                        <a href="admin.php?modulo=editar_proyecto&id=${pro.id}" class="text-gray-400 hover:text-blue-600 transition-colors"><i data-lucide="edit-2" class="w-4 h-4"></i></a>
                        <a href="admin.php?modulo=proyectos&delete=${pro.id}" onclick="return confirm('¿Eliminar proyecto?');" class="text-gray-400 hover:text-red-600 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                    </div>
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
    }

    function renderPagination(data, q) {
        if (data.total_pages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }
        const start = ((data.current_page - 1) * 10) + 1;
        const end = Math.min(data.current_page * 10, data.total);
        let html = `
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <span class="text-sm text-gray-500">Mostrando <strong>${start}</strong> a <strong>${end}</strong> de <strong>${data.total}</strong></span>
                <div class="flex space-x-1">`;
        if (data.current_page > 1) {
            html += `<button onclick="window.searchPro(1, '${q}')" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border rounded-lg hover:bg-gray-50">&laquo; Anterior</button>`;
        }
        html += `<div class="hidden sm:flex space-x-1 mx-2">`;
        for (let i = 1; i <= data.total_pages; i++) {
            html += `<button onclick="window.searchPro(${i}, '${q}')" class="px-3 py-1.5 text-sm border rounded-lg ${i === data.current_page ? 'text-blue-600 bg-blue-50 border-blue-200 font-bold' : 'text-gray-600 bg-white border-gray-300 hover:bg-gray-50'}">${i}</button>`;
        }
        html += `</div>`;
        if (data.current_page < data.total_pages) {
            html += `<button onclick="window.searchPro(${data.current_page + 1}, '${q}')" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border rounded-lg hover:bg-gray-50">Siguiente &raquo;</button>`;
        }
        html += `</div></div>`;
        paginationContainer.innerHTML = html;
    }

    window.searchPro = function(p = 1, q = '') {
        fetch(`api_buscar_proyectos.php?q=${encodeURIComponent(q)}&p=${p}`)
            .then(r => r.json())
            .then(data => {
                renderRows(data.results);
                renderPagination(data, q);
                countEl.textContent = data.total;
            });
    };

    input.addEventListener('input', function() {
        const q = this.value.trim();
        clearBtn.classList.toggle('hidden', q === '');
        clearTimeout(debounce);
        debounce = setTimeout(() => window.searchPro(1, q), 280);
    });

    clearBtn.addEventListener('click', () => {
        input.value = '';
        clearBtn.classList.add('hidden');
        window.searchPro(1, '');
    });
})();
</script>
