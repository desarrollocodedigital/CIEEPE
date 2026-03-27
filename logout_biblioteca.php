<?php
$redirect = $_GET['redirect'] ?? 'biblioteca.php';
session_start();
// Limpiar solo los datos de la biblioteca
unset($_SESSION['user_bib_id']);
unset($_SESSION['user_bib_nombre']);
unset($_SESSION['user_bib_rol']);
unset($_SESSION['user_bib_correo']);

header("Location: " . $redirect);
exit;
?>
