<?php
// api_contacto.php
header('Content-Type: application/json');
require_once 'conexion.php';

try {
    $res = $pdo->query("SELECT * FROM contacto_config WHERE id = 1");
    $config = $res->fetch(PDO::FETCH_ASSOC);
    
    // Convertir nombres de columnas a las claves esperadas por el frontend si es necesario
    // En este caso, el frontend usa los mismos nombres que las columnas (pero sin prefijos)
    echo json_encode($config);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
