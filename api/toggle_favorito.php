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
    // Verificar si ya es favorito
    $stmt = $pdo->prepare("SELECT id FROM favoritos_biblioteca WHERE id_usuario = ? AND id_documento = ?");
    $stmt->execute([$userId, $docId]);
    $fav = $stmt->fetch();

    if ($fav) {
        // Quitar de favoritos
        $stmt = $pdo->prepare("DELETE FROM favoritos_biblioteca WHERE id_usuario = ? AND id_documento = ?");
        $stmt->execute([$userId, $docId]);
        echo json_encode(['success' => true, 'action' => 'removed', 'doc_id' => $docId]);
    } else {
        // Agregar a favoritos
        $stmt = $pdo->prepare("INSERT INTO favoritos_biblioteca (id_usuario, id_documento) VALUES (?, ?)");
        $stmt->execute([$userId, $docId]);
        echo json_encode(['success' => true, 'action' => 'added', 'doc_id' => $docId]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
