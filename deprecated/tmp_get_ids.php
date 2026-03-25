<?php
require_once 'c:\xampp\htdocs\CIEEPE-David\conexion.php';
$names = ['Martha Lorena Solís Aragón', 'Rodrigo López Zavala', 'Antonio Gómez Nashiki'];
$ids = [];
foreach ($names as $name) {
    $stmt = $pdo->prepare("SELECT id FROM investigadores WHERE nombre = ?");
    $stmt->execute([$name]);
    $res = $stmt->fetch();
    $ids[$name] = $res ? $res['id'] : null;
}
echo json_encode($ids);
?>
