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
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pagina < 1) $pagina = 1;

$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

$params = [];
$where = '';

if ($busqueda !== '') {
    $where = "WHERE nombre LIKE ?";
    $like = '%' . $busqueda . '%';
    $params = [$like];
}

// Count total
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM investigadores $where");
$stmt_count->execute($params);
$total = (int)$stmt_count->fetchColumn();
$total_pages = ceil($total / $por_pagina);

// Fetch results
$stmt = $pdo->prepare("SELECT id, nombre, cargo_o_grado, etiqueta_badge, email, telefono, imagen_perfil, linkedin_url, facebook_url FROM investigadores $where ORDER BY nombre ASC LIMIT ? OFFSET ?");
$bind_params = array_merge($params, [$por_pagina, $offset]);
foreach ($bind_params as $k => $v) {
    $type = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($k + 1, $v, $type);
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'results' => $results,
    'total' => $total,
    'total_pages' => $total_pages,
    'current_page' => $pagina
]);
