<?php
// admin_investigadores.php
$mensaje = '';

// Handling deletes
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM investigadores WHERE id = ?")->execute([$id_del]);
    $mensaje = 'Investigador eliminado correctamente.';
    // Remove delete parameter from URL
    echo "<script>window.history.replaceState(null, null, window.location.pathname + '?modulo=investigadores');</script>";
}

// Paginación y búsqueda
$por_pagina = 10;
$pagina_actual = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $por_pagina;
$busqueda = trim($_GET['q'] ?? '');

// Base de condición de búsqueda
$where = '';
$params = [];
if ($busqueda !== '') {
    $where = "WHERE nombre LIKE ?";
    $like = '%' . $busqueda . '%';
    $params = [$like];
}

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM investigadores $where");
$stmt_count->execute($params);
$total_investigadores = $stmt_count->fetchColumn();
$total_paginas = ceil($total_investigadores / $por_pagina);
if ($pagina_actual > $total_paginas && $total_paginas > 0) $pagina_actual = $total_paginas;

$stmt_inv = $pdo->prepare("SELECT * FROM investigadores $where ORDER BY nombre ASC LIMIT ? OFFSET ?");
$bind_params = array_merge($params, [$por_pagina, $offset]);
foreach ($bind_params as $k => $v) {
    $type = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt_inv->bindValue($k + 1, $v, $type);
}
$stmt_inv->execute();
$investigadores = $stmt_inv->fetchAll(PDO::FETCH_ASSOC);

// URL base para paginación conservando búsqueda
$base_url = 'admin.php?modulo=investigadores' . ($busqueda !== '' ? '&q=' . urlencode($busqueda) : '');
?>

<!-- Header y Acciones -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Directorio de Investigadores</h2>
        <p class="text-gray-500 text-sm mt-1">Gestiona el equipo académico (<span id="inv-count"><?= $total_investigadores ?></span> registros en total)</p>
    </div>
    <div class="flex items-center gap-3">
        <!-- Buscador en tiempo real -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400" id="search-icon"></i>
            </div>
            <input type="text" id="inv-search" placeholder="Buscar investigador..." autocomplete="off"
                class="pl-9 pr-9 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-56 transition-all">
            <button type="button" id="clear-search" class="hidden absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <a href="admin.php?modulo=nuevo_investigador" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Añadir Nuevo
        </a>
    </div>
</div>

<?php if($mensaje): ?>
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<!-- Tabla de Datos -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50/80 border-b border-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold">Investigador</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Contacto</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Redes</th>
                    <th scope="col" class="px-6 py-4 font-semibold text-right">Acciones</th>
                </tr>
            </thead>
            <tbody id="inv-tbody" class="divide-y divide-gray-100">
                <?php foreach($investigadores as $inv): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0">
                                <img class="h-10 w-10 rounded-full object-cover ring-2 ring-gray-100" src="<?= htmlspecialchars($inv['imagen_perfil']) ?>" onerror="this.src='./img/placeholder.jpg'" alt="<?= htmlspecialchars($inv['nombre']) ?>">
                            </div>
                            <div class="ml-4">
                                <div class="font-bold text-gray-900"><?= htmlspecialchars($inv['nombre']) ?></div>
                                <div class="text-xs text-blue-600 mt-0.5 font-medium"><?= htmlspecialchars($inv['cargo_o_grado']) ?></div>
                                <div class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($inv['etiqueta_badge']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900"><?= !empty($inv['email']) ? htmlspecialchars($inv['email']) : '<span class="text-gray-400 italic">Sin correo electrónico</span>' ?></div>
                        <div class="text-xs text-gray-500 mt-0.5"><?= !empty($inv['telefono']) ? htmlspecialchars($inv['telefono']) : '<span class="text-gray-400 italic">Sin Número Telefónico</span>' ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-3">
                           <?php if($inv['linkedin_url'] && $inv['linkedin_url'] !== '#'): ?>
                           <a href="<?= htmlspecialchars($inv['linkedin_url']) ?>" target="_blank" class="hover:scale-110 transition-transform" title="LinkedIn">
                               <img src="./img/iconos/linkedin.svg" class="w-5 h-5" alt="LI">
                           </a>
                           <?php endif; ?>
                           <?php if(isset($inv['facebook_url']) && $inv['facebook_url'] && $inv['facebook_url'] !== '#'): ?>
                           <a href="<?= htmlspecialchars($inv['facebook_url']) ?>" target="_blank" class="hover:scale-110 transition-transform" title="Facebook">
                               <img src="./img/iconos/facebook-icon.svg" class="w-5 h-5" alt="FB">
                           </a>
                           <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="perfil_generico.php?id=<?= $inv['id'] ?>" target="_blank" class="text-gray-400 hover:text-blue-600 transition-colors" title="Ver Perfil">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <a href="admin.php?modulo=editar_investigador&id=<?= $inv['id'] ?>" class="text-gray-400 hover:text-amber-500 transition-colors" title="Editar Completo">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </a>
                            <a href="admin.php?modulo=investigadores&delete=<?= $inv['id'] ?>" onclick="return confirm('¿Eliminar a <?= htmlspecialchars($inv['nombre']) ?>? Esta acción no se puede deshacer.');" class="text-gray-400 hover:text-red-600 transition-colors" title="Eliminar">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div id="pagination-container">
    <?php if ($total_paginas > 1): ?>
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <span class="text-sm text-gray-500">
            Mostrando <strong><?= $offset + 1 ?></strong> a <strong><?= min($offset + $por_pagina, $total_investigadores) ?></strong> de <strong><?= $total_investigadores ?></strong> investigadores
        </span>
        <div class="flex space-x-1">
            <!-- Botón Anterior -->
            <?php if ($pagina_actual > 1): ?>
                <a href="<?= $base_url ?>&p=<?= $pagina_actual - 1 ?>" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors">
                    &laquo; Anterior
                </a>
            <?php else: ?>
                <span class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                    &laquo; Anterior
                </span>
            <?php endif; ?>

            <!-- Números de Página -->
            <div class="hidden sm:flex space-x-1 mx-2">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <?php if ($i == $pagina_actual): ?>
                        <span class="px-3 py-1.5 text-sm font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= $base_url ?>&p=<?= $i ?>" class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <!-- Botón Siguiente -->
            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="<?= $base_url ?>&p=<?= $pagina_actual + 1 ?>" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors">
                    Siguiente &raquo;
                </a>
            <?php else: ?>
                <span class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                    Siguiente &raquo;
                </span>
            <?php endif; ?>
        </div>
        </div>
    <?php endif; ?>
    </div><!-- /pagination-container -->
</div>

<script>
(function() {
    const input = document.getElementById('inv-search');
    const clearBtn = document.getElementById('clear-search');
    const tbody = document.getElementById('inv-tbody');
    const countEl = document.getElementById('inv-count');
    const paginationContainer = document.getElementById('pagination-container');
    const originalPaginationHTML = paginationContainer.innerHTML;
    let debounce;

    function escapeHTML(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderRows(results) {
        if (results.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No se encontraron investigadores.</td></tr>`;
            return;
        }
        tbody.innerHTML = results.map(inv => `
            <tr class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                            <img class="h-10 w-10 rounded-full object-cover ring-2 ring-gray-100" src="${escapeHTML(inv.imagen_perfil)}" onerror="this.src='./img/placeholder.jpg'" alt="${escapeHTML(inv.nombre)}">
                        </div>
                        <div class="ml-4">
                            <div class="font-bold text-gray-900">${escapeHTML(inv.nombre)}</div>
                            <div class="text-xs text-blue-600 mt-0.5 font-medium">${escapeHTML(inv.cargo_o_grado)}</div>
                            <div class="text-xs text-gray-500 mt-0.5">${escapeHTML(inv.etiqueta_badge)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">${inv.email ? escapeHTML(inv.email) : '<span class="text-gray-400 italic">Sin correo</span>'}</div>
                    <div class="text-xs text-gray-500 mt-0.5">${inv.telefono ? escapeHTML(inv.telefono) : '<span class="text-gray-400 italic">Sin teléfono</span>'}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex space-x-3">
                        ${inv.linkedin_url && inv.linkedin_url !== '#' ? `<a href="${escapeHTML(inv.linkedin_url)}" target="_blank" class="hover:scale-110 transition-transform" title="LinkedIn"><img src="./img/iconos/linkedin.svg" class="w-5 h-5" alt="LI"></a>` : ''}
                        ${inv.facebook_url && inv.facebook_url !== '#' ? `<a href="${escapeHTML(inv.facebook_url)}" target="_blank" class="hover:scale-110 transition-transform" title="Facebook"><img src="./img/iconos/facebook-icon.svg" class="w-5 h-5" alt="FB"></a>` : ''}
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end space-x-3">
                        <a href="perfil_generico.php?id=${inv.id}" target="_blank" class="text-gray-400 hover:text-blue-600 transition-colors" title="Ver Perfil"><i data-lucide="eye" class="w-4 h-4"></i></a>
                        <a href="admin.php?modulo=editar_investigador&id=${inv.id}" class="text-gray-400 hover:text-amber-500 transition-colors" title="Editar"><i data-lucide="edit-2" class="w-4 h-4"></i></a>
                        <a href="admin.php?modulo=investigadores&delete=${inv.id}" onclick="return confirm('\u00bfEliminar a ${escapeHTML(inv.nombre)}?');" class="text-gray-400 hover:text-red-600 transition-colors" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
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
                <span class="text-sm text-gray-500">
                    Mostrando <strong>${start}</strong> a <strong>${end}</strong> de <strong>${data.total}</strong> resultados
                </span>
                <div class="flex space-x-1">`;

        // Previous button
        if (data.current_page > 1) {
            html += `<button onclick="window.searchInvestigadores('${escapeHTML(q)}', ${data.current_page - 1})" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors">&laquo; Anterior</button>`;
        } else {
            html += `<span class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">&laquo; Anterior</span>`;
        }

        // Page numbers
        html += `<div class="hidden sm:flex space-x-1 mx-2">`;
        for (let i = 1; i <= data.total_pages; i++) {
            if (i === data.current_page) {
                html += `<span class="px-3 py-1.5 text-sm font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg">${i}</span>`;
            } else {
                html += `<button onclick="window.searchInvestigadores('${escapeHTML(q)}', ${i})" class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors">${i}</button>`;
            }
        }
        html += `</div>`;

        // Next button
        if (data.current_page < data.total_pages) {
            html += `<button onclick="window.searchInvestigadores('${escapeHTML(q)}', ${data.current_page + 1})" class="px-3 py-1.5 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-colors">Siguiente &raquo;</button>`;
        } else {
            html += `<span class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">Siguiente &raquo;</span>`;
        }

        html += `</div></div>`;
        paginationContainer.innerHTML = html;
    }

    window.searchInvestigadores = function(q, p = 1) {
        if (!q) {
            paginationContainer.innerHTML = originalPaginationHTML;
            // Need to fetch original page 1 if q is cleared? 
            // Better to reload or just use the PHP rendered default if p=1 is the goal.
            // For simplicity, let's just use the original HTML which shows the initial 10.
            if (p === 1 && !q) {
                 location.reload(); // Simplest way to restore initial state
                 return;
            }
        }

        fetch(`api_buscar_investigadores.php?q=${encodeURIComponent(q)}&p=${p}`)
            .then(r => r.json())
            .then(data => {
                renderRows(data.results);
                renderPagination(data, q);
                countEl.textContent = data.total + (q ? ' resultados encontrados' : ' registros');
            });
    };

    input.addEventListener('input', function() {
        const q = this.value.trim();
        clearBtn.classList.toggle('hidden', q === '');
        clearTimeout(debounce);
        debounce = setTimeout(() => window.searchInvestigadores(q, 1), 280);
    });

    clearBtn.addEventListener('click', function() {
        input.value = '';
        clearBtn.classList.add('hidden');
        window.searchInvestigadores('');
    });
})();
</script>
