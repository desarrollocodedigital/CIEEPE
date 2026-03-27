<?php
session_start();
require_once 'conexion.php';

if (isset($_SESSION['user_bib_id'])) {
    header("Location: biblioteca.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $tipo_usuario = $_POST['tipo_usuario'] ?? '';
    $motivo = trim($_POST['motivo_acceso'] ?? '');

    if (empty($nombre) || empty($correo) || empty($password) || empty($motivo) || empty($tipo_usuario)) {
        $error = "Todos los campos son obligatorios, incluyendo el tipo de usuario y el motivo.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo electrónico no válido.";
    } else {
        // Verificar si el correo ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios_biblioteca WHERE correo = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            $error = "El correo electrónico ya está registrado.";
        } else {
            // Insertar nuevo usuario
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios_biblioteca (nombre, correo, password_hash, tipo_usuario, motivo_acceso, rol) VALUES (?, ?, ?, ?, ?, 'publico')");
            if ($stmt->execute([$nombre, $correo, $password_hash, $tipo_usuario, $motivo])) {
                $success = "¡Registro enviado! Tu solicitud ha sido recibida y está en proceso de revisión por parte del comité CIATA. Te avisaremos pronto.";
            } else {
                $error = "Hubo un error al registrar el usuario.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro CIATA | CIEEPE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-100">
        <div class="text-center mb-8">
            <img src="<?= htmlspecialchars($site_logo ?? '') ?>" alt="Logo CIEEPE" class="h-16 w-auto mb-6 mx-auto">
            <h1 class="text-2xl font-bold text-slate-900 leading-tight">Biblioteca Virtual</h1>
            <p class="text-sm text-slate-500 mt-2 font-medium">Crea una cuenta para acceder al acervo digital</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <i data-lucide="alert-circle" class="text-red-500 w-5 h-5 mr-3"></i>
                <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <i data-lucide="check-circle" class="text-emerald-500 w-5 h-5 mr-3"></i>
                <p class="text-sm text-emerald-700 font-medium"><?= htmlspecialchars($success) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="registro_biblioteca.php" class="space-y-4">
            <div>
                <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Nombre Completo</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i data-lucide="user" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="text" name="nombre" id="nombre" required
                        class="pl-11 block w-full border border-slate-200 rounded-xl shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300"
                        placeholder="Juan Pérez">
                </div>
            </div>

            <div>
                <label for="correo" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Correo Electrónico</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i data-lucide="mail" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="email" name="correo" id="correo" required autocomplete="email"
                        class="pl-11 block w-full border border-slate-200 rounded-xl shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300"
                        placeholder="tu@correo.com">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label for="tipo_usuario" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Tipo de Usuario</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="briefcase" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                        </div>
                        <select name="tipo_usuario" id="tipo_usuario" required
                            class="pl-11 block w-full border border-slate-200 rounded-xl shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none bg-white">
                            <option value="" disabled selected>Seleccione tipo de usuario</option>
                            <option value="membresia">Membresía de Biblioteca</option>
                            <option value="investigador">Investigador</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label for="motivo_acceso" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Motivo de Acceso</label>
                <div class="relative group">
                    <textarea name="motivo_acceso" id="motivo_acceso" required rows="2"
                        class="block w-full border border-slate-200 rounded-xl shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300 text-sm"
                        placeholder="Breve reseña de por qué quieres obtener el acceso..."></textarea>
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Contraseña</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i data-lucide="lock" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                        class="pl-11 block w-full border border-slate-200 rounded-xl shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300"
                        placeholder="••••••••">
                </div>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Confirmar Contraseña</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i data-lucide="lock" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="password" name="confirm_password" id="confirm_password" required
                        class="pl-11 block w-full border border-slate-200 rounded-xl shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300"
                        placeholder="••••••••">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-blue-500/20 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all active:scale-[0.98]">
                    Crear Cuenta
                </button>
            </div>
        </form>

        <div class="mt-8 text-center space-y-3">
            <p class="text-sm text-slate-600">¿Ya tienes una cuenta? <a href="login_biblioteca.php" class="text-blue-600 font-bold hover:text-blue-700 transition-colors">Inicia sesión</a></p>
            <a href="biblioteca.php" class="text-sm font-medium text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i> Volver a la Biblioteca
            </a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
