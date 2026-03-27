<?php
// api_buscar_biblioteca.php
require_once 'conexion.php';

session_start();
// Protección: Solo admin o investigador de biblioteca
if (!isset($_SESSION['user_bib_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
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
    // Buscar por título del documento o nombre del autor
    $where = "WHERE (d.titulo LIKE ? OR u.nombre LIKE ?)";
    $like = '%' . $busqueda . '%';
    $params = [$like, $like];
}

// Count total
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM documentos_biblioteca d JOIN usuarios_biblioteca u ON d.id_autor = u.id $where");
$stmt_count->execute($params);
$total = (int)$stmt_count->fetchColumn();
$total_pages = ceil($total / $por_pagina);

// Fetch results
$query = "SELECT d.*, u.nombre as autor_nombre 
          FROM documentos_biblioteca d 
          JOIN usuarios_biblioteca u ON d.id_autor = u.id 
          $where 
          ORDER BY d.fecha_subida DESC 
          LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($query);
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
