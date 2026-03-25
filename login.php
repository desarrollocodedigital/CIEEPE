<?php
session_start();
require_once 'conexion.php';

// Si ya está logueado, redirigir al admin
if (isset($_SESSION['user_id'])) {
    header("Location: admin.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Por favor ingrese correo y contraseña.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login correcto
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;
            header("Location: admin.php");
            exit;
        } else {
            $error = "Credenciales incorrectas.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | CIEEPE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <img src="<?= htmlspecialchars($site_logo ?? '') ?>" alt="Logo CIEEPE" class="h-20 w-auto mb-6 mx-auto">
            <h1 class="text-2xl font-bold text-gray-900">Panel Administrativo</h1>
            <p class="text-sm text-gray-500 mt-2">Inicia sesión para acceder</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex items-center">
                <i data-lucide="alert-circle" class="text-red-500 w-5 h-5 mr-2"></i>
                <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="email" name="email" id="email" required autocomplete="email"
                        class="pl-10 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="admin@cieepe.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                        class="pl-10 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="••••••••">
                </div>
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Iniciar Sesión
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <a href="index.html" class="text-sm font-medium text-blue-600 hover:text-blue-500 flex items-center justify-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Volver al sitio principal
            </a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
