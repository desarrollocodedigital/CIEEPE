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

// Configuración global del sitio (Logos)
$site_logo = './img/logo.png';
$hero_logo_plata = './img/LogoPlata.png';

try {
    $stmt_config = $pdo->query("SELECT clave, valor FROM site_config WHERE clave IN ('site_logo', 'hero_logo_plata')");
    $configs = $stmt_config->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (isset($configs['site_logo']) && !empty(trim($configs['site_logo']))) {
        $site_logo = trim($configs['site_logo']);
    }
    
    if (isset($configs['hero_logo_plata']) && !empty(trim($configs['hero_logo_plata']))) {
        $hero_logo_plata = trim($configs['hero_logo_plata']);
    }
} catch (Exception $e) {
    // Si la tabla no existe aún, se usan los fallbacks
}
?>
