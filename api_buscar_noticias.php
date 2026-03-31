<?php
require_once 'conexion.php';

// Only allow authenticated requests
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$busqueda = trim($_GET['q'] ?? '');
$pagina = isset($_GET['p']) ? (int) $_GET['p'] : 1;
if ($pagina < 1)
    $pagina = 1;

$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

$params = [];
$where = '';

if ($busqueda !== '') {
    $where = "WHERE titulo LIKE ? OR descripcion_corta LIKE ?";
    $like = '%' . $busqueda . '%';
    $params = [$like, $like];
}

// Count total
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM noticias $where");
if ($busqueda !== '') {
    $stmt_count->execute($params);
} else {
    $stmt_count->execute();
}
$total = (int) $stmt_count->fetchColumn();
$total_pages = ceil($total / $por_pagina);

// Fetch results
$stmt = $pdo->prepare("SELECT id, titulo, descripcion_corta, fecha_publicacion, imagen_portada FROM noticias $where ORDER BY id DESC LIMIT ? OFFSET ?");
$bind_params = array_merge($params, [(int) $por_pagina, (int) $offset]);

// We need to manually bind to handle types correctly for LIMIT/OFFSET
$idx = 1;
foreach ($params as $p) {
    $stmt->bindValue($idx++, $p, PDO::PARAM_STR);
}
$stmt->bindValue($idx++, (int) $por_pagina, PDO::PARAM_INT);
$stmt->bindValue($idx++, (int) $offset, PDO::PARAM_INT);

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'results' => $results,
    'total' => $total,
    'total_pages' => $total_pages,
    'current_page' => $pagina
]);
