<?php
// api_guardar_noticia.php - Endpoint dedicado para guardar/actualizar noticias con galería múltiple
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once 'conexion.php';

$action = $_POST['action'] ?? '';

// ===========================
// CREAR NOTICIA
// ===========================
if ($action === 'create_news') {
    $titulo   = trim($_POST['titulo'] ?? '');
    $desc_c   = trim($_POST['descripcion_corta'] ?? '');
    $desc_l   = trim($_POST['descripcion_larga'] ?? '');
    $fecha    = $_POST['fecha_publicacion'] ?? date('Y-m-d H:i:s');

    if (empty($titulo)) {
        echo json_encode(['ok' => false, 'error' => 'El título es obligatorio.']);
        exit;
    }

    $imagen_portada = './img/placeholder.jpg';
    $galeria = [];

    // Portada
    if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            if (!is_dir('img/noticias')) mkdir('img/noticias', 0755, true);
            $dest = 'img/noticias/news_p_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $dest)) {
                $imagen_portada = './' . $dest;
            }
        }
    }

    // Galería múltiple
    if (!empty($_FILES['galeria']['tmp_name'])) {
        if (!is_dir('img/noticias/galeria')) mkdir('img/noticias/galeria', 0755, true);
        foreach ($_FILES['galeria']['tmp_name'] as $key => $tmp) {
            if ($_FILES['galeria']['error'][$key] !== 0 || empty($tmp)) continue;
            $ext = strtolower(pathinfo($_FILES['galeria']['name'][$key], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;
            $dest = 'img/noticias/galeria/news_g_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($tmp, $dest)) {
                $galeria[] = './' . $dest;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO noticias (titulo, descripcion_corta, descripcion_larga, imagen_portada, galeria, fecha_publicacion) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$titulo, $desc_c, $desc_l, $imagen_portada, json_encode(array_values($galeria)), $fecha])) {
        echo json_encode(['ok' => true, 'redirect' => 'admin.php?modulo=noticias&status=created']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar en la base de datos.']);
    }
    exit;
}

// ===========================
// EDITAR NOTICIA
// ===========================
if ($action === 'edit_news') {
    $id     = intval($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $desc_c = trim($_POST['descripcion_corta'] ?? '');
    $desc_l = trim($_POST['descripcion_larga'] ?? '');
    $fecha  = $_POST['fecha_publicacion'] ?? null;

    if (!$id || empty($titulo)) {
        echo json_encode(['ok' => false, 'error' => 'Datos incompletos.']);
        exit;
    }

    // Obtener galería actual
    $stmt = $pdo->prepare("SELECT imagen_portada, galeria FROM noticias WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['ok' => false, 'error' => 'Noticia no encontrada.']);
        exit;
    }

    $imagen_portada = $row['imagen_portada'];
    $galeria = json_decode($row['galeria'] ?? '[]', true);

    // Nueva portada
    if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            if (!is_dir('img/noticias')) mkdir('img/noticias', 0755, true);
            $dest = 'img/noticias/news_p_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $dest)) {
                // Borrar anterior si no es placeholder
                if ($imagen_portada && !str_contains($imagen_portada, 'placeholder')) {
                    $old = ltrim($imagen_portada, './');
                    if (file_exists($old)) unlink($old);
                }
                $imagen_portada = './' . $dest;
            }
        }
    }

    // Nuevas fotos de galería
    if (!empty($_FILES['nueva_galeria']['tmp_name'])) {
        if (!is_dir('img/noticias/galeria')) mkdir('img/noticias/galeria', 0755, true);
        foreach ($_FILES['nueva_galeria']['tmp_name'] as $key => $tmp) {
            if ($_FILES['nueva_galeria']['error'][$key] !== 0 || empty($tmp)) continue;
            $ext = strtolower(pathinfo($_FILES['nueva_galeria']['name'][$key], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;
            $dest = 'img/noticias/galeria/news_g_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($tmp, $dest)) {
                $galeria[] = './' . $dest;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE noticias SET titulo=?, descripcion_corta=?, descripcion_larga=?, imagen_portada=?, galeria=?, fecha_publicacion=? WHERE id=?");
    if ($stmt->execute([$titulo, $desc_c, $desc_l, $imagen_portada, json_encode(array_values($galeria)), $fecha, $id])) {
        echo json_encode(['ok' => true, 'redirect' => "admin.php?modulo=editar_noticia&id=$id&status=updated"]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al actualizar.']);
    }
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Acción desconocida.']);
