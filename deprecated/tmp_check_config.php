<?php
require_once 'c:\xampp\htdocs\CIEEPE-David\conexion.php';
$stmt = $pdo->query("SELECT * FROM site_config WHERE clave = 'site_logo'");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
