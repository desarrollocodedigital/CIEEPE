<?php
session_start();
require_once '../conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_bib_id'])) {
    echo json_encode(['success' => false, 'error' => 'No has iniciado sesión']);
    exit;
}

$userId = $_SESSION['user_bib_id'];
$data = json_decode(file_get_contents('php://input'), true);
$docId = isset($data['doc_id']) ? intval($data['doc_id']) : 0;

if ($docId <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de documento no válido']);
    exit;
}

try {
    // Registrar o actualizar fecha de visto (historial)
    $stmt = $pdo->prepare("INSERT INTO vistos_biblioteca (id_usuario, id_documento, fecha_visto) 
                           VALUES (?, ?, CURRENT_TIMESTAMP)
                           ON DUPLICATE KEY UPDATE fecha_visto = CURRENT_TIMESTAMP");
    $stmt->execute([$userId, $docId]);
    
    echo json_encode(['success' => true, 'doc_id' => $docId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
