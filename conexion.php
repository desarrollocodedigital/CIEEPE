<?php
// conexion.php

$host = 'localhost';
$db   = 'cieepe_bd';
$user = 'root'; // Usuario por defecto de XAMPP
$pass = '';     // Contraseña por defecto de XAMPP suele ser vacía

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Configuración global del sitio (Logo)
$site_logo = './img/logo.png';
try {
    $stmt_logo = $pdo->query("SELECT valor FROM site_config WHERE clave = 'site_logo'");
    $row_logo = $stmt_logo->fetch();
    if ($row_logo) $site_logo = $row_logo['valor'];
} catch (Exception $e) {
    // Si la tabla no existe aún, se usa el fallback por defecto
}
?>
