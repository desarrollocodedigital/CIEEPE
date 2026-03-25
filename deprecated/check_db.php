<?php
require_once 'conexion.php';
$stmt = $pdo->query("DESCRIBE proyectos");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
?>
