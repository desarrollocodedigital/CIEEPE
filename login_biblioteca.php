<?php
session_start();
require_once 'conexion.php';

// Si ya está logueado en la biblioteca, redirigir a la misma
if (isset($_SESSION['user_bib_id'])) {
    header("Location: biblioteca.php");
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'biblioteca.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($correo) || empty($password)) {
        $error = "Por favor ingrese correo y contraseña.";
    } else {
        $stmt = $pdo->prepare("SELECT id, nombre, password_hash, rol, estado FROM usuarios_biblioteca WHERE correo = ?");
        $stmt->execute([$correo]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Verificar estado del usuario
            if ($user['estado'] === 'pendiente') {
                $error = "Tu solicitud de acceso sigue en revisión. Te avisaremos pronto.";
            } elseif ($user['estado'] === 'rechazado') {
                $error = "Tu solicitud de acceso ha sido rechazada. Contacta a soporte.";
            } else {
                // Login correcto para biblioteca
                $_SESSION['user_bib_id'] = $user['id'];
                $_SESSION['user_bib_nombre'] = $user['nombre'];
                $_SESSION['user_bib_rol'] = $user['rol'];
                $_SESSION['user_bib_correo'] = $correo;
                
                header("Location: " . $redirect);
                exit;
            }
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
    <title>Iniciar Sesión CIATA | CIEEPE</title>
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
            <p class="text-sm text-slate-500 mt-2 font-medium">Inicia sesión para continuar</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <i data-lucide="alert-circle" class="text-red-500 w-5 h-5 mr-3"></i>
                <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="login_biblioteca.php" class="space-y-5">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            
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

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Contraseña</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i data-lucide="lock" class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                        class="pl-11 block w-full border border-slate-200 rounded-xl shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300"
                        placeholder="••••••••">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-blue-500/20 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all active:scale-[0.98]">
                    Ingresar
                </button>
            </div>
        </form>

        <div class="mt-8 text-center space-y-3">
            <p class="text-sm text-slate-600">¿No tienes una cuenta? <a href="registro_biblioteca.php" class="text-blue-600 font-bold hover:text-blue-700 transition-colors">Regístrate gratis</a></p>
            <a href="biblioteca.php" class="text-sm font-medium text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i> Volver a la Biblioteca
            </a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
