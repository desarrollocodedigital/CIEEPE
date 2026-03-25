<?php
// api_hero.php — Devuelve el contenido del hero + conteos de tablas + nosotros en JSON
require_once 'conexion.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache');

// Obtener configuración del hero y sitio
$stmt = $pdo->query("SELECT clave, valor FROM site_config WHERE clave LIKE 'hero_%' OR clave = 'site_logo'");
$config = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $config[$row['clave']] = $row['valor'];
}

// Obtener configuración de "Nosotros"
$nosotros = $pdo->query("SELECT * FROM nosotros_config LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Contar registros de cada sección
$total_investigadores = (int) $pdo->query("SELECT COUNT(*) FROM investigadores")->fetchColumn();
$total_proyectos      = (int) $pdo->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
$total_lineas         = (int) $pdo->query("SELECT COUNT(*) FROM lineas_investigacion")->fetchColumn();

echo json_encode([
    'logo'        => $config['site_logo']         ?? './img/logo.png',
    'logo_plata'  => $config['hero_logo_plata']   ?? './img/LogoPlata.png',
    'badge'       => $config['hero_badge']       ?? 'ENEES',
    'titulo'      => $config['hero_titulo']       ?? '',
    'descripcion' => $config['hero_descripcion']  ?? '',
    'imagen'      => $config['hero_imagen']       ?? '',
    'stats' => [
        'investigadores' => $total_investigadores,
        'proyectos'      => $total_proyectos,
        'lineas'         => $total_lineas,
    ],
    'nosotros' => $nosotros ? [
        'section_title' => $nosotros['section_title'],
        'main_title'    => $nosotros['main_title'],
        'description_1' => $nosotros['description_1'],
        'description_2' => $nosotros['description_2'],
        'image_path'    => $nosotros['image_path'],
        'points'        => json_decode($nosotros['points'] ?? '[]', true),
    ] : null,
], JSON_UNESCAPED_UNICODE);
