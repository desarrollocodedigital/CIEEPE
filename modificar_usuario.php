<?php
session_start();
// Solo admin puede entrar a esta página de edición administrativa
if (!isset($_SESSION['user_bib_id']) || ($_SESSION['user_bib_rol'] ?? '') !== 'admin') {
    header("Location: login_biblioteca.php");
    exit;
}

require_once 'conexion.php';

$userId = $_SESSION['user_bib_id'];
$userRol = $_SESSION['user_bib_rol'];
$id_edit = $_GET['edit'] ?? null;

if (!$id_edit) {
    header("Location: admin_biblioteca.php?modulo=usuarios");
    exit;
}

// Cargar el usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios_biblioteca WHERE id = ?");
$stmt->execute([$id_edit]);
$u_edit = $stmt->fetch();

if (!$u_edit) {
    header("Location: admin_biblioteca.php?modulo=usuarios");
    exit;
}

// Lógica de Edición
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_perfil'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $tipo = $_POST['tipo_usuario'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $estado = $_POST['estado'] ?? '';
    
    if (empty($nombre) || empty($correo)) {
        $error = "Nombre y correo son obligatorios.";
    } else {
        $sql = "UPDATE usuarios_biblioteca SET 
                nombre = ?, 
                correo = ?, 
                tipo_usuario = ?, 
                rol = ?, 
                estado = ? 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$nombre, $correo, $tipo, $rol, $estado, $id_edit])) {
            $success = "Perfil de usuario actualizado con éxito.";
            // Recargar datos
            $stmt = $pdo->prepare("SELECT * FROM usuarios_biblioteca WHERE id = ?");
            $stmt->execute([$id_edit]);
            $u_edit = $stmt->fetch();
        } else {
            $error = "Error al actualizar la base de datos.";
        }
    }
}

$activeClass = "bg-blue-800 text-white border-l-4 border-blue-400";
$inactiveClass = "text-blue-100 hover:bg-blue-800 hover:text-white border-l-4 border-transparent";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Editar Usuario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden">

    <!-- Sidebar Lateral -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex border-r border-gray-800">
        <div class="p-6 border-b border-gray-800 flex items-center gap-3">
            <div class="p-2 bg-blue-600 rounded-lg"><i data-lucide="shield-check" class="w-6 h-6"></i></div>
            <div>
                <h2 class="font-bold text-lg leading-tight text-white">CIATA</h2>
                <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest">Administrador</p>
            </div>
        </div>
        <nav class="flex-1 mt-6 px-2 space-y-1 overflow-y-auto">
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase mb-2">Administración</h3>
            <a href="admin_biblioteca.php?modulo=inicio" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i><span class="text-sm">Dashboard</span>
            </a>
            <a href="admin_biblioteca.php?modulo=recursos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="book-open" class="w-5 h-5 mr-3"></i><span class="text-sm">Biblioteca General</span>
            </a>
            <a href="admin_biblioteca.php?modulo=documentos" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="file-check" class="w-5 h-5 mr-3"></i><span class="text-sm">Revisión de Tesis</span>
            </a>
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase mt-6 mb-2">Comunidad</h3>
            <a href="admin_biblioteca.php?modulo=solicitudes" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $inactiveClass ?>">
                <i data-lucide="user-plus" class="w-5 h-5 mr-3"></i><span class="text-sm">Solicitudes</span>
            </a>
            <a href="admin_biblioteca.php?modulo=usuarios" class="mx-2 px-4 py-3 flex items-center rounded-lg transition-colors <?= $activeClass ?>">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i><span class="text-sm">Gestión de Usuarios</span>
            </a>
            <h3 class="px-6 text-xs font-semibold text-gray-500 uppercase mt-6 mb-2">Cuenta</h3>
            <a href="admin_biblioteca.php?modulo=usuarios" class="mx-2 px-4 py-3 flex items-center rounded-lg text-gray-400 hover:bg-gray-800 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-3"></i><span>Volver</span>
            </a>
            <a href="logout_biblioteca.php" class="mx-2 px-4 py-3 flex items-center rounded-lg text-red-400 hover:bg-red-500/10 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i><span>Cerrar Sesión</span>
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800 bg-gray-950">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs uppercase">
                        <?= strtoupper(substr($_SESSION['user_bib_nombre'], 0, 1)) ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white leading-none truncate w-24 tracking-tight"><?= htmlspecialchars($_SESSION['user_bib_nombre']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 flex-shrink-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-500 hover:text-gray-900">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold text-gray-800 hidden sm:block leading-tight">Administración de Usuarios</h2>
                    <p class="text-[10px] text-blue-600 font-bold uppercase tracking-tight">Portal Administrativo CIATA</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="biblioteca.php" class="inline-flex items-center text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full hover:bg-blue-100 transition-colors">
                    <i data-lucide="library" class="w-4 h-4 mr-1.5"></i> Ver Biblioteca
                </a>
                <span class="text-sm text-gray-400">|</span>
                <span class="text-sm font-medium text-gray-600"><?= htmlspecialchars($_SESSION['user_bib_correo']) ?></span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-4xl mx-auto">
                
                <!-- Sub-Cabecera de Navegación Estilo Investigador -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center">
                        <a href="admin_biblioteca.php?modulo=usuarios" class="mr-4 text-gray-400 hover:text-blue-600 transition-colors p-2 bg-white rounded-xl shadow-sm border border-slate-100">
                            <i data-lucide="arrow-left" class="w-6 h-6"></i>
                        </a>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">Edición de Perfil</h2>
                            <p class="text-slate-500 text-sm mt-1"><?= htmlspecialchars($u_edit['nombre']) ?> <span class="text-xs text-slate-300 ml-2">#<?= $u_edit['id'] ?></span></p>
                        </div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i><span class="text-sm font-medium"><?= $error ?></span>
                </div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i><span class="text-sm font-medium"><?= $success ?></span>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="user-cog" class="w-5 h-5 text-blue-600"></i> Información del Perfil
                        </h3>
                    </div>
                    <form method="POST" class="p-8 space-y-8">
                        <input type="hidden" name="editar_perfil" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre Completo</label>
                                <div class="relative">
                                    <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                                    <input type="text" name="nombre" required value="<?= htmlspecialchars($u_edit['nombre']) ?>"
                                        class="w-full bg-slate-50 border border-slate-100 rounded-2xl pl-11 pr-4 py-3.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white outline-none transition-all text-sm font-medium text-slate-700">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Correo Electrónico</label>
                                <div class="relative">
                                    <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                                    <input type="email" name="correo" required value="<?= htmlspecialchars($u_edit['correo']) ?>"
                                        class="w-full bg-slate-50 border border-slate-100 rounded-2xl pl-11 pr-4 py-3.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white outline-none transition-all text-sm font-medium text-slate-700">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Configuración de Membresía</label>
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="space-y-2">
                                        <p class="text-xs font-bold text-slate-600">Tipo de Perfil</p>
                                        <select name="tipo_usuario" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-sm font-medium text-slate-700 appearance-none cursor-pointer">
                                            <option value="membresia" <?= $u_edit['tipo_usuario'] === 'membresia' ? 'selected' : '' ?>>Membresía de Biblioteca</option>
                                            <option value="investigador" <?= $u_edit['tipo_usuario'] === 'investigador' ? 'selected' : '' ?>>Investigador (Autor)</option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-xs font-bold text-slate-600">Rol de Acceso</p>
                                        <select name="rol" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-sm font-medium text-slate-700 appearance-none cursor-pointer">
                                            <option value="usuario" <?= $u_edit['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario Estándar</option>
                                            <option value="investigador" <?= $u_edit['rol'] === 'investigador' ? 'selected' : '' ?>>Investigador Senior</option>
                                            <option value="admin" <?= $u_edit['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estado de la Cuenta</label>
                                <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 flex flex-col gap-4">
                                    <label class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-slate-100 cursor-pointer hover:border-emerald-500 transition-all group">
                                        <input type="radio" name="estado" value="activo" <?= $u_edit['estado'] === 'activo' ? 'checked' : '' ?> class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-700 group-hover:text-emerald-700 transition-colors">ACTIVO</p>
                                            <p class="text-[10px] text-slate-400">Acceso total a la biblioteca</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-slate-100 cursor-pointer hover:border-amber-500 transition-all group">
                                        <input type="radio" name="estado" value="pendiente" <?= $u_edit['estado'] === 'pendiente' ? 'checked' : '' ?> class="w-4 h-4 text-amber-600 focus:ring-amber-500 border-slate-300">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-700 group-hover:text-amber-700 transition-colors">EN ESPERA</p>
                                            <p class="text-[10px] text-slate-400">Pendiente de revisión administrativa</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-slate-100 cursor-pointer hover:border-red-500 transition-all group">
                                        <input type="radio" name="estado" value="rechazado" <?= $u_edit['estado'] === 'rechazado' ? 'checked' : '' ?> class="w-4 h-4 text-red-600 focus:ring-red-500 border-slate-300">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-700 group-hover:text-red-700 transition-colors">RECHAZADO</p>
                                            <p class="text-[10px] text-slate-400">Acceso denegado</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-slate-50 flex items-center justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-bold hover:bg-blue-700 shadow-xl shadow-blue-600/20 transition-all active:scale-95 flex items-center gap-2 text-sm">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                Guardar Perfil de Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
