<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Seguridad: Si no hay sesión, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<style>
    .manual-scroll-container { scroll-behavior: smooth; }
    .manual-content::-webkit-scrollbar { width: 6px; }
    .manual-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .manual-content::-webkit-scrollbar-track { background: #f1f5f9; }
    
    /* Scroll personalizado para el índice */
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
</style>

<div class="flex flex-col lg:flex-row gap-8 h-full max-w-7xl mx-auto">
    <!-- ÁREA DE CONTENIDO (IZQUIERDA) -->
    <div class="flex-1 manual-content manual-scroll-container overflow-y-auto pr-4 pb-20">
        
        <!-- Header del Manual (Simulado PDF) -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-12 mb-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-blue-600 rounded flex items-center justify-center text-white">
                        <i data-lucide="book" class="w-6 h-6"></i>
                    </div>
                    <span class="text-blue-600 font-bold tracking-widest text-xs uppercase">Documentación Oficial</span>
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 mb-4">Manual del Sistema Administrativo</h1>
                <p class="text-lg text-gray-500 leading-relaxed max-w-2xl">
                    Esta guía detallada te ayudará a comprender y utilizar todas las herramientas disponibles en el panel de control del CIEEPE.
                </p>
            </div>
        </div>

        <!-- SECCIÓN: Introducción -->
        <section id="introduccion" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Inicio</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Introducción al Sistema</h2>
            </div>
            <div class="prose prose-blue max-w-none text-gray-600 space-y-4">
                <p>El Sistema Administrativo del CIEEPE es una plataforma centralizada diseñada para simplificar la gestión de contenidos académicos, investigadores, proyectos y noticias.</p>
                <p>Este sistema permite a los administradores mantener la información del portal actualizada sin necesidad de conocimientos técnicos avanzados, utilizando una interfaz intuitiva y herramientas de edición dinámica.</p>
                
                <!-- Tabla de Roles -->
                <div class="mt-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-5 h-5 text-blue-600"></i>
                        Roles y Permisos
                    </h3>
                    <div class="overflow-hidden border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rol</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Acceso</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold leading-5 text-blue-800 bg-blue-100 rounded-full italic">Administrador</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Gestor principal con control total de la plataforma.</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">Gestión de investigadores, proyectos, líneas, noticias y configuración de la web.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mt-8">
                    <p class="text-sm text-blue-700 font-medium">
                        <strong>Nota importante:</strong> Todos los cambios realizados en este panel se reflejan instantáneamente en el sitio web público.
                    </p>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 1: Acceso -->
        <section id="acceso" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 1</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Acceso al Sistema</h2>
            </div>
            <div class="prose max-w-none text-gray-600 space-y-6">
                <p>Sigue estos pasos detallados para ingresar de manera segura al panel administrativo del CIEEPE:</p>
                
                <ol class="space-y-4">
                    <li class="flex gap-4">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">1</span>
                        <div>
                            <p class="font-bold text-gray-800">Navegar a la Dirección de Acceso</p>
                            <p class="text-sm">Abre tu navegador habitual e ingresa la dirección: <code class="bg-gray-100 px-2 py-0.5 rounded text-indigo-600">DominioPrincipal.com/login.php</code></p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">2</span>
                        <div>
                            <p class="font-bold text-gray-800">Credenciales de Usuario</p>
                            <p class="text-sm">Introduce tu correo electrónico institucional o registrado y tu contraseña secreta en los campos correspondientes.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">3</span>
                        <div>
                            <p class="font-bold text-gray-800">Validación de Seguridad</p>
                            <p class="text-sm">El sistema verificará tus datos. Asegúrate de que la sesión esté protegida y evita compartir tus claves de acceso.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">4</span>
                        <div>
                            <p class="font-bold text-gray-800">Ingreso al Panel</p>
                            <p class="text-sm">Una vez validado, serás redirigido automáticamente a la <strong>Visión General (Dashboard)</strong>.</p>
                        </div>
                    </li>
                </ol>

                <div class="mt-6 p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg">
                    <div class="flex items-center gap-2 mb-1 text-amber-800 font-bold">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        <span>Importante:</span>
                    </div>
                    <p class="text-sm text-amber-700">Por seguridad, las sesiones inactivas caducarán automáticamente. Si el sistema te redirige al login, simplemente vuelve a ingresar tus credenciales.</p>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 2: Dashboard -->
        <section id="dashboard" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 2</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Dashboard</h2>
            </div>
            <div class="prose max-w-none text-gray-600 space-y-6">
                <p>El Dashboard es el centro de mando donde obtendrás una visión rápida del estado general del portal. Desde aquí puedes monitorear la actividad reciente y acceder a todas las herramientas de gestión.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="block font-bold text-gray-800 mb-1">Estadísticas Rápidas:</span>
                        <span class="text-sm">Muestra el número total de investigadores, proyectos activos y noticias publicadas.</span>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="block font-bold text-gray-800 mb-1">Accesos Directos:</span>
                        <span class="text-sm">Botones para saltar rápidamente a la creación de nuevos registros desde la página principal.</span>
                    </div>
                </div>

                <!-- Detalle del Menú Lateral -->
                <div class="mt-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="layout-list" class="w-5 h-5 text-emerald-600"></i>
                        Estructura del Menú Lateral
                    </h3>
                    <div class="overflow-hidden border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Apartado</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Elemento</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Descripción Detallada</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400 uppercase tracking-wider">Principal</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">Dashboard</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Página de inicio con resumen estadístico y accesos rápidos.</td>
                                </tr>
                                <tr class="bg-gray-50/30">
                                    <td rowspan="4" class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400 uppercase tracking-wider border-r border-gray-100">Gestión de Datos</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">Investigadores</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Alta y edición de perfiles académicos, fotografías y archivos CV.</td>
                                </tr>
                                <tr class="bg-gray-50/30">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">Proyectos</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Control de proyectos de investigación, estados y colaboradores.</td>
                                </tr>
                                <tr class="bg-gray-50/30">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">Noticias</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Publicador de novedades con gestión de imágenes y fechas.</td>
                                </tr>
                                <tr class="bg-gray-50/30">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">Líneas de Invest.</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Categorización y definición de áreas temáticas del centro.</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400 uppercase tracking-wider italic">Sitio Web</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-700">Configuraciones</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Edición de textos, banners e información de contacto del sitio público.</td>
                                </tr>
                                <tr class="bg-blue-50/30">
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-blue-400 uppercase tracking-wider">Soporte</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">Manual de Usuario</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Acceso a esta guía interactiva y soporte técnico por WhatsApp.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Elementos Globales (Integrados en Dashboard) -->
                <div class="mt-12 pt-8 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i data-lucide="layout" class="w-5 h-5 text-emerald-600"></i>
                        Barra Superior y Gestión de Sesión
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">Además del menú lateral, el sistema cuenta con elementos persistentes en la parte superior e inferior para facilitar tu trabajo diario:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Detalle Header -->
                        <div class="p-5 bg-gray-50 rounded-xl border border-gray-100 leading-relaxed shadow-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <i data-lucide="monitor" class="w-4 h-4 text-emerald-600"></i>
                                <p class="font-bold text-gray-800 text-sm uppercase tracking-wide text-[10px]">Barra Superior (Header)</p>
                            </div>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span><strong>Título Dinámico:</strong> Indica la página actual.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span><strong>Ver Sitio Web:</strong> Enlace para previsualizar cambios.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span><strong>Email:</strong> Tu identificador de usuario activo.</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Detalle Sesión -->
                        <div class="p-5 bg-gray-50 rounded-xl border border-gray-100 leading-relaxed shadow-sm border-l-4 border-l-red-500">
                            <div class="flex items-center gap-2 mb-3">
                                <i data-lucide="log-out" class="w-4 h-4 text-red-600"></i>
                                <p class="font-bold text-gray-800 text-sm uppercase tracking-wide text-[10px]">Seguridad y Salida</p>
                            </div>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <span class="text-red-500 font-bold">•</span>
                                    <span><strong>Perfil:</strong> Localizado en la base del menú lateral.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-red-500 font-bold">•</span>
                                    <span><strong>Cerrar Sesión:</strong> Usa el botón rojo para salir con seguridad.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 3: Gestión de Investigadores -->
        <section id="investigadores" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 3</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Gestión de Investigadores</h2>
            </div>
            
            <div class="prose max-w-none text-gray-600 space-y-8">
                <p>Esta sección permite administrar el capital humano del centro, controlando la información pública de cada académico, sus redes de contacto y su trayectoria profesional.</p>

                <!-- Sub-sección: Directorio -->
                <div id="directorio-investigadores" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="search" class="w-5 h-5 text-blue-500"></i>
                        3.1 Directorio y Búsqueda
                    </h3>
                    <p>Al entrar en el módulo de Investigadores, verás una tabla con el listado completo. Puedes utilizar el <strong>Buscador en tiempo real</strong> ubicado en la parte superior derecha para filtrar por nombre sin necesidad de recargar la página.</p>
                    
                    <!-- Iconos de acción rápida (Ahora arriba) -->
                    <div class="bg-blue-50 p-4 rounded-lg flex gap-4 text-sm italic border-l-4 border-blue-600 mb-6">
                        <span>Iconos de acción rápida:</span>
                        <span class="flex items-center gap-1"><i data-lucide="eye" class="w-4 h-4 text-blue-600"></i> Ver Perfil</span>
                        <span class="flex items-center gap-1"><i data-lucide="edit-2" class="w-4 h-4 text-amber-600"></i> Editar datos</span>
                        <span class="flex items-center gap-1"><i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i> Eliminar Registro</span>
                    </div>

                    <!-- Detalle de Navegación -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 mb-4">
                        <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <i data-lucide="layers" class="w-4 h-4 text-blue-500"></i>
                            Navegación por páginas
                        </h4>
                        <p class="text-sm mb-4">Para mantener la velocidad del sistema, el directorio muestra los investigadores en bloques de <strong>10 registros por página</strong>. Puedes navegar usando los siguientes controles situados al final de la tabla:</p>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">•</span>
                                <span><strong>Botones de Navegación:</strong> Usa los botones <strong>« Anterior</strong> y <strong>Siguiente »</strong> para moverte entre las páginas de resultados.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">•</span>
                                <span><strong>Números de Página:</strong> Haz clic directamente en un número para saltar a esa página específica.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">•</span>
                                <span><strong>Resumen de Registros:</strong> El sistema indica claramente cuántos investigadores estás visualizando y el total disponible (ej. <em>"Mostrando 1 a 10 de 30"</em>).</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Sub-sección: 3.2 Registro -->
                <div id="alta-investigador" class="space-y-4 pt-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-5 h-5 text-blue-500"></i>
                        3.2 Registro de Nuevo Investigador
                    </h3>
                    <p>Al hacer clic en <strong>"Añadir Nuevo"</strong>, el sistema desplegará un formulario completo segmentado en tres bloques clave para el perfil profesional:</p>
                    
                    <!-- Grid de 3 Columnas (Unificado) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        
                        <!-- Col 1: Perfil Académico -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors">
                            <div class="absolute top-0 right-0 p-3 opacity-5 group-hover/card:opacity-10 transition-opacity">
                                <i data-lucide="award" class="w-12 h-12 text-blue-900"></i>
                            </div>
                            <p class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-blue-100 pb-2">
                                <i data-lucide="user-check" class="w-3 h-3"></i>
                                Ficha Académica
                            </p>
                            <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                                <li><strong class="text-gray-800">• Nombre Completo:</strong> (Obligatorio).</li>
                                <li><strong class="text-gray-800">• Cargo y Grado:</strong> Título académico actual (ej. Dr., Director).</li>
                                <li><strong class="text-gray-800">• Tipo:</strong> Categoría del investigador (ej. SNII).</li>
                                <li><strong class="text-gray-800">• Procedencia:</strong> Determina si es Interno o Externo.</li>
                                <li><strong class="text-gray-800">• Etiqueta (Badge):</strong> Línea de investigación principal.</li>
                                <li><strong class="text-gray-800">• Especialidad:</strong> Palabras clave para el buscador.</li>
                            </ul>
                        </div>

                        <!-- Col 2: Contacto y Ubicación -->
                        <div class="bg-amber-50/30 p-6 rounded-xl border border-amber-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors">
                            <div class="absolute top-0 right-0 p-3 opacity-5 group-hover/card:opacity-10 transition-opacity">
                                <i data-lucide="mail" class="w-12 h-12 text-amber-800"></i>
                            </div>
                            <p class="font-bold text-amber-800 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-amber-100 pb-2">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                Contacto y Redes
                            </p>
                            <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                                <li><strong class="text-gray-800">• Email:</strong> Correo institucional (CIEEPE).</li>
                                <li><strong class="text-gray-800">• Teléfono:</strong> Solo números (Sin guiones ni espacios).</li>
                                <li><strong class="text-gray-800">• Ubicación:</strong> Centro de trabajo u oficina física.</li>
                                <li><strong class="text-gray-800">• LinkedIn:</strong> Dirección completa al perfil profesional.</li>
                                <li><strong class="text-gray-800">• Facebook:</strong> Dirección completa al perfil social.</li>
                            </ul>
                        </div>

                        <!-- Col 3: Multimedia y Semblanza -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors border-l-4 border-l-emerald-500">
                            <div class="absolute top-0 right-0 p-3 opacity-5 group-hover/card:opacity-10 transition-opacity">
                                <i data-lucide="file-text" class="w-12 h-12 text-emerald-900"></i>
                            </div>
                            <p class="font-bold text-emerald-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-emerald-100 pb-2">
                                <i data-lucide="image" class="w-3 h-3"></i>
                                Semblanza y Archivos
                            </p>
                            <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                                <li><strong class="text-gray-800">• Semblanza Corta:</strong> Breve descripción (Máx 255 carac.)</li>
                                <li><strong class="text-gray-800">• Perfil Profesional:</strong> Trayectoria detallada del académico.</li>
                                <li><strong class="text-gray-800">• Foto de Perfil:</strong> Archivo únicamente JPG o PNG.</li>
                                <li><strong class="text-gray-800">• Currículum Vitae:</strong> Archivo únicamente en PDF.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Sub-sección: 3.3 Edición -->
                <div id="edicion-investigador" class="space-y-4 pt-8 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-5 h-5 text-blue-500"></i>
                        3.3 Edición de Investigador
                    </h3>
                    <p>Para modificar un registro, haz clic en el icono de edición <i data-lucide="edit-2" class="w-4 h-4 text-amber-600 inline mx-1"></i> en el directorio. El sistema cargará todos los datos actuales para su revisión completa.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="bg-white p-5 rounded-xl border border-blue-100 shadow-sm">
                            <h4 class="font-bold text-blue-900 mb-2 flex items-center gap-2 text-xs uppercase tracking-tight">
                                <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                                Actualización de Datos
                            </h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Puedes cambiar cualquier campo base. Si dejas la <strong>"Foto de Perfil"</strong> o el <strong>"Currículum"</strong> vacío, el sistema conservará los archivos actuales sin borrarlos.
                            </p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-red-100 shadow-sm group hover:border-red-200 transition-colors">
                            <h4 class="font-bold text-red-700 mb-2 flex items-center gap-2 text-xs uppercase tracking-tight">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Eliminación Segura
                            </h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Al usar el icono de papelera <i data-lucide="trash-2" class="w-3 h-3 text-red-500 mx-1"></i>, se lanzará una confirmación para evitar borrados accidentales de investigadores con trayectoria vinculada.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sub-sección: 3.4 Funciones Avanzadas -->
                <div id="investigador-avanzado" class="p-8 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 shadow-sm font-sans">
                    <h3 class="text-xl font-bold text-blue-900 mb-6 flex items-center gap-2">
                        <i data-lucide="award" class="w-6 h-6 text-blue-600"></i>
                        3.4 Especialidades y Publicaciones
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Especialidades -->
                        <div class="bg-white p-5 rounded-xl border border-blue-200 shadow-sm">
                            <h4 class="font-bold text-blue-900 mb-2 flex items-center gap-2 text-sm uppercase">
                                <i data-lucide="network" class="w-4 h-4"></i>
                                Especialidades de Investigación
                            </h4>
                            <ul class="text-xs space-y-1.5 text-gray-700">
                                <li>• Elige o crea <strong>nuevas especialidades</strong> mediante el interruptor superior.</li>
                                <li>• Define el <strong>orden</strong> para controlar cómo se listan en el perfil público.</li>
                            </ul>
                        </div>

                        <!-- Publicaciones -->
                        <div class="bg-white p-5 rounded-xl border border-blue-200 shadow-sm border-l-4 border-l-amber-500">
                            <h4 class="font-bold text-gray-800 mb-2 flex items-center gap-2 text-sm uppercase">
                                <i data-lucide="book-open" class="w-4 h-4 text-amber-600"></i>
                                Publicaciones Destacadas
                            </h4>
                            <ul class="text-xs space-y-1.5 text-gray-700">
                                <li><strong>• Título y Enlace:</strong> Proporciona el nombre de la obra y link directo si existe.</li>
                                <li><strong>• Orden de jerarquía:</strong> Prioriza las publicaciones más relevantes al inicio.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center gap-3 p-4 bg-white/60 rounded-xl border border-white/80 text-xs text-blue-700 leading-relaxed italic">
                        <i data-lucide="lightbulb" class="w-10 h-10 text-blue-500 flex-shrink-0"></i>
                        Recuerda: El botón "Actualizar" al final del formulario principal guarda los datos generales, mientras que las especialidades y publicaciones se guardan individualmente.
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 4: Gestión de Proyectos -->
        <section id="proyectos" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 4</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Gestión de Proyectos</h2>
            </div>
            
            <div class="prose max-w-none text-gray-600 space-y-8">
                <p>El módulo de proyectos permite documentar y dar seguimiento a las investigaciones en curso, permitiendo asignar equipos de trabajo y metas específicas que se visualizan en la ficha técnica de cada proyecto.</p>

                <!-- Sub-sección: Directorio -->
                <div id="directorio-proyectos" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="search" class="w-5 h-5 text-amber-500"></i>
                        4.1 Directorio y Búsqueda
                    </h3>
                    <p>Al igual que en investigadores, el directorio de proyectos cuenta con herramientas avanzadas para localizar información rápidamente:</p>
                    
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">•</span>
                            <span><strong>Buscador Inteligente:</strong> Filtra proyectos por <strong>Título, Categoría o Responsable</strong> de forma instantánea.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">•</span>
                            <span><strong>Estados Visuales:</strong> Identifica el avance mediante etiquetas de color: 
                                <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-[10px] font-bold mx-1">En Puerta</span>
                                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-[10px] font-bold mx-1">En Curso</span>
                                <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-[10px] font-bold mx-1">Terminado</span>
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-amber-500 font-bold">•</span>
                            <span><strong>Navegación por páginas:</strong> Navega entre grupos de 10 registros para una carga optimizada.</span>
                        </li>
                    </ul>
                </div>

                <!-- Sub-sección: Registro -->
                <div id="registro-proyecto" class="space-y-4 pt-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="file-plus" class="w-5 h-5 text-amber-500"></i>
                        4.2 Registro de Nuevo Proyecto
                    </h3>
                    <p>Al hacer clic en <strong>"Añadir Nuevo"</strong>, el sistema te guiará a través de tres áreas fundamentales para construir una ficha de investigación exhaustiva:</p>
                    
                    <!-- Grid de 3 Columnas (Nuevo Diseño) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        
                        <!-- Col 1: Datos Identificación -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors">
                            <div class="absolute top-0 right-0 p-3 opacity-5 group-hover/card:opacity-10 transition-opacity">
                                <i data-lucide="folder-search" class="w-12 h-12 text-blue-900"></i>
                            </div>
                            <p class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-blue-100 pb-2">
                                <i data-lucide="info" class="w-3 h-3"></i>
                                Identificación
                            </p>
                            <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                                <li><strong class="text-gray-800">• Título:</strong> Nombre oficial (Evitar mayúsculas sostenidas).</li>
                                <li><strong class="text-gray-800">• Categoría (Línea):</strong> Clasifica el proyecto para los filtros de la web pública.</li>
                                <li><strong class="text-gray-800">• Estado Actual:</strong> Define si está En Puerta, En Curso o Terminado.</li>
                                <li><strong class="text-gray-800">• Año de Inicio:</strong> Proporciona el contexto temporal.</li>
                                <li><strong class="text-gray-800">• Imagen de Portada:</strong> Fotografía que represente visualmente el estudio.</li>
                            </ul>
                        </div>

                        <!-- Col 2: Equipo Trabajo -->
                        <div class="bg-amber-50/30 p-6 rounded-xl border border-amber-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors">
                            <div class="absolute top-0 right-0 p-3 opacity-5 group-hover/card:opacity-10 transition-opacity">
                                <i data-lucide="users-2" class="w-12 h-12 text-amber-800"></i>
                            </div>
                            <p class="font-bold text-amber-800 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-amber-100 pb-2">
                                <i data-lucide="contacts" class="w-3 h-3"></i>
                                Equipo Humano
                            </p>
                            <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                                <li><strong class="text-gray-800">• Responsable Principal:</strong> Académico líder seleccionado del catálogo general.</li>
                                <li><strong class="text-gray-800">• Colaboradores Internos:</strong> Investigadores del CIEEPE involucrados.</li>
                                <li><strong class="text-gray-800">• Colaboradores Externos:</strong> Aliados estratégicos de otras instituciones.</li>
                                <li class="bg-amber-100/50 p-2 rounded italic text-[10px] mt-2">
                                    <strong>Nota:</strong> Al escribir los nombres se generarán automáticamente las etiquetas que puedes eliminar con el icono (X).
                                </li>
                            </ul>
                        </div>

                        <!-- Col 3: Ficha Técnica -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors">
                            <div class="absolute top-0 right-0 p-3 opacity-5 group-hover/card:opacity-10 transition-opacity">
                                <i data-lucide="clipboard-check" class="w-12 h-12 text-emerald-900"></i>
                            </div>
                            <p class="font-bold text-emerald-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-emerald-100 pb-2">
                                <i data-lucide="settings" class="w-3 h-3"></i>
                                Detalles Técnicos
                            </p>
                            <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                                <li><strong class="text-gray-800">• Descripción Corta:</strong> Resumen conciso para las tarjetas del catálogo.</li>
                                <li><strong class="text-gray-800">• Cuerpo del Proyecto:</strong> Detalle amplio de metodología y alcance.</li>
                                <li><strong class="text-gray-800">• Métricas:</strong> Duración, Financiamiento y Área Temática.</li>
                                <li><strong class="text-gray-800">• Protocolo PDF:</strong> Archivo oficial que los usuarios podrán descargar.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Sub-sección: 4.3 Edición de Proyecto -->
                <div id="edicion-proyecto" class="space-y-4 pt-8 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-5 h-5 text-amber-500"></i>
                        4.3 Edición de Proyecto
                    </h3>
                    <p>La edición permite actualizar el avance de una investigación. Accede mediante el icono <i data-lucide="edit-2" class="w-4 h-4 text-amber-600 inline mx-1"></i> en el directorio de proyectos.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="bg-white p-5 rounded-xl border border-amber-100 shadow-sm hover:border-amber-200 transition-colors">
                            <h4 class="font-bold text-amber-900 mb-2 flex items-center gap-2 text-xs uppercase tracking-tight">
                                <i data-lucide="file-edit" class="w-4 h-4"></i>
                                Actualización de Ficha
                            </h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Cambia el estado (ej: a <strong>"Terminado"</strong>) o añade nuevos colaboradores. El sistema conserva el histórico sin perder información previa.
                            </p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-amber-100 shadow-sm">
                            <h4 class="font-bold text-amber-900 mb-2 flex items-center gap-2 text-xs uppercase tracking-tight">
                                <i data-lucide="file-check" class="w-4 h-4"></i>
                                Conservación de PDF
                            </h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Si hay un protocolo cargado, se mostrará una marca de verificación. Solo sube un archivo si necesitas <strong>reemplazarlo</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sub-sección: 4.4 Metas y Objetivos -->
                <div id="proyecto-avanzado" class="p-8 bg-gradient-to-br from-amber-50 to-orange-100 rounded-2xl border border-amber-200 shadow-sm">
                    <h3 class="text-xl font-bold text-amber-900 mb-6 flex items-center gap-2">
                        <i data-lucide="target" class="w-6 h-6 text-amber-600"></i>
                        4.4 Metas y Objetivos Específicos
                    </h3>
                    
                    <div class="bg-white p-6 rounded-xl border border-amber-200 shadow-sm relative overflow-hidden">
                        <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
                            <i data-lucide="layout-grid" class="w-4 h-4 text-amber-600"></i>
                            Gestión de Tarjetas
                        </h4>
                        <ul class="text-sm space-y-4 text-gray-700 relative z-10">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0 mt-0.5 text-xs font-bold font-sans">1</div>
                                <div>
                                    <strong class="text-xs text-gray-900 tracking-tight">Iconografía:</strong>
                                    <p class="text-[10px] mt-1 text-gray-500 italic">Escribe nombres de iconos Lucide (ej: 'users', 'globe') para fortalecer el lenguaje visual.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0 mt-0.5 text-xs font-bold font-sans">2</div>
                                <div>
                                    <strong class="text-xs text-gray-900 tracking-tight">Jerarquía:</strong>
                                    <p class="text-[10px] mt-1 text-gray-500 italic">Usa el campo de orden para priorizar qué objetivos se ven primero en la ficha pública.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 flex items-center gap-3 p-4 bg-white/60 rounded-xl border border-white/80 text-xs text-amber-800 leading-relaxed italic">
                        <i data-lucide="info" class="w-8 h-8 text-amber-500 flex-shrink-0"></i>
                        Recuerda: Las tarjetas de objetivos aparecen al final de la página de edición con sus propios botones de guardado.
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 5: Gestión de Noticias -->
        <section id="noticias" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 5</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Gestión de Noticias</h2>
            </div>
            
            <div class="prose max-w-none text-gray-600 space-y-8">
                <p>El módulo de noticias permite difundir boletines, eventos y logros institucionales. Este apartado es vital para mantener la dinámica de actualización del portal principal.</p>

                <!-- Sub-sección: 5.1 Directorio -->
                <div id="directorio-noticias" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="search" class="w-5 h-5 text-indigo-500"></i>
                        5.1 Directorio y Búsqueda
                    </h3>
                    <p>Al igual que otros módulos, el directorio muestra las noticias en orden cronológico inverso (las más recientes primero).</p>
                    
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-500 font-bold">•</span>
                            <span><strong>Buscador Inteligente:</strong> Localiza noticias por su <strong>Título o Resumen</strong> de forma instantánea.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-500 font-bold">•</span>
                            <span><strong>Control Temporal:</strong> Las noticias se paginan cada <strong>10 registros</strong> para facilitar la administración histórica.</span>
                        </li>
                    </ul>
                </div>

                <!-- Sub-sección: 5.2 Registro (2 Columnas) -->
                <div id="registro-noticia" class="space-y-4 pt-4 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="file-plus-2" class="w-5 h-5 text-indigo-500"></i>
                        5.2 Registro de Nueva Noticia
                    </h3>
                    <p>El registro de noticias utiliza un diseño simplificado de <strong>2 columnas</strong> para separar el contenido intelectual del material visual:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                        <!-- Col 1: Bloque Editorial -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors">
                            <div class="absolute top-0 right-0 p-3 opacity-5">
                                <i data-lucide="type" class="w-12 h-12 text-blue-900"></i>
                            </div>
                            <p class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-blue-100 pb-2">
                                <i data-lucide="layout-list" class="w-3 h-3"></i>
                                Contenido Editorial
                            </p>
                            <ul class="text-[11px] space-y-4 text-gray-600 leading-relaxed">
                                <li><strong>• Título Oficial:</strong> Encabezado principal de la noticia.</li>
                                <li><strong>• Fecha/Hora:</strong> Indica la fecha del evento o comunicado para el público.</li>
                                <li><strong>• Resumen Corto:</strong> Texto de 255 caracteres para las tarjetas del feed principal.</li>
                                <li><strong>• Cuerpo de Noticia:</strong> Editor de texto enriquecido para el desarrollo total de la nota.</li>
                            </ul>
                        </div>

                        <!-- Col 2: Material Multimedia -->
                        <div class="bg-emerald-50/20 p-6 rounded-xl border border-emerald-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors border-l-4 border-l-emerald-500">
                            <div class="absolute top-0 right-0 p-3 opacity-5">
                                <i data-lucide="images" class="w-12 h-12 text-emerald-900"></i>
                            </div>
                            <p class="font-bold text-emerald-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-emerald-100 pb-2">
                                <i data-lucide="camera" class="w-3 h-3"></i>
                                Material Multimedia
                            </p>
                            <ul class="text-[11px] space-y-4 text-gray-600 leading-relaxed">
                                <li><strong>• Imagen de Portada:</strong> Foto principal que encabeza la noticia (JPG/PNG).</li>
                                <li><strong>• Galería Interactiva:</strong> Selección múltiple de fotos que formarán un carrusel dinámico en el detalle.</li>
                                <li class="bg-emerald-100/40 p-2 rounded italic text-[10px]">
                                    <strong>Tip:</strong> Puedes seleccionar varias fotos a la vez manteniendo presionado <em>Ctrl</em> al elegir archivos.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Sub-sección: 5.3 Edición -->
                <div id="edicion-noticia" class="space-y-4 pt-8 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-5 h-5 text-indigo-500"></i>
                        5.3 Edición de Noticia
                    </h3>
                    <p>Para modificar una noticia, haz clic en el icono de edición <i data-lucide="edit-2" class="w-4 h-4 text-amber-600 inline mx-1"></i> en el listado principal.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="bg-white p-5 rounded-xl border border-indigo-100 shadow-sm hover:border-indigo-200 transition-colors">
                            <h4 class="font-bold text-indigo-900 mb-2 flex items-center gap-2 text-xs uppercase tracking-tight">
                                <i data-lucide="file-text" class="w-4 h-4 text-indigo-600"></i>
                                Actualización de Contenido
                            </h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Puedes corregir erratas en el título, resumen o cuerpo. El sistema guardará los cambios inmediatamente al actualizar.
                            </p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-emerald-100 shadow-sm">
                            <h4 class="font-bold text-emerald-900 mb-2 flex items-center gap-2 text-xs uppercase tracking-tight">
                                <i data-lucide="image" class="w-4 h-4 text-emerald-600"></i>
                                Conservación Multimedia
                            </h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Si no seleccionas una nueva <strong>Portada</strong> o nuevas fotos para la galería, el sistema mantendrá los archivos actuales automáticamente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 6: Gestión de Líneas de Investigación -->
        <section id="lineas" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 6</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Gestión de Líneas de Investigación</h2>
            </div>
            
            <div class="prose max-w-none text-gray-600 space-y-8">
                <p>Las líneas de investigación son las áreas temáticas que definen el trabajo del CIEEPE. Esta sección permite clasificar tanto a los investigadores como a los proyectos para facilitar su búsqueda en la web pública.</p>

                <!-- Sub-sección: 6.1 Directorio -->
                <div id="directorio-lineas" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="search" class="w-5 h-5 text-blue-500"></i>
                        6.1 Directorio y Acciones
                    </h3>
                    <p>El listado de líneas muestra de forma simplificada las áreas activas en el centro. Desde aquí puedes:</p>
                    
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 font-bold">•</span>
                            <span><strong>Identificar por Color:</strong> Cada línea tiene un color temático asociado para su fácil reconocimiento.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 font-bold">•</span>
                            <span><strong>Acciones Directas:</strong> Utiliza el icono de edición <i data-lucide="edit-2" class="w-3 h-3 text-amber-600 inline mx-1"></i> para cambiar el nombre o el icono encargado de representarla.</span>
                        </li>
                    </ul>
                </div>

                <!-- Sub-sección: 6.2 Registro (2 Columnas) -->
                <div id="registro-linea" class="space-y-4 pt-4 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="book-plus" class="w-5 h-5 text-blue-500"></i>
                        6.2 Registro de Nueva Línea
                    </h3>
                    <p>Al crear una nueva línea de investigación, configurarás su identidad visual y su enfoque académico en dos bloques simples:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                        <!-- Col 1: Identidad Visual -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors">
                            <div class="absolute top-0 right-0 p-3 opacity-5">
                                <i data-lucide="palette" class="w-12 h-12 text-blue-900"></i>
                            </div>
                            <p class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-blue-100 pb-2">
                                <i data-lucide="image" class="w-3 h-3"></i>
                                Identidad Visual
                            </p>
                            <ul class="text-[11px] space-y-4 text-gray-600 leading-relaxed">
                                <li><strong>• Título de la Línea:</strong> Nombre oficial del área de estudio.</li>
                                <li><strong>• Ícono Representativo:</strong> Nombre del icono (ej. <em>book, brain, users</em>).</li>
                                <li><strong>• Color Temático:</strong> Elige el color que distinguirá a esta línea en todo el portal.</li>
                            </ul>
                        </div>

                        <!-- Col 2: Enfoque Académico -->
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm relative overflow-hidden group/card hover:bg-white transition-colors border-l-4 border-l-blue-500">
                            <div class="absolute top-0 right-0 p-3 opacity-5">
                                <i data-lucide="file-text" class="w-12 h-12 text-gray-900"></i>
                            </div>
                            <p class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-gray-100 pb-2">
                                <i data-lucide="align-left" class="w-3 h-3"></i>
                                Enfoque Académico
                            </p>
                            <ul class="text-[11px] space-y-4 text-gray-600 leading-relaxed">
                                <li><strong>• Descripción General:</strong> Redacción detallada que explica el propósito, metas y alcance de este campo de investigación.</li>
                                <li class="bg-blue-100/50 p-2 rounded italic text-[10px]">
                                    <strong>Nota:</strong> Esta descripción será visible en el detalle de la línea al navegar por el sitio público.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Sub-sección: 6.3 Edición -->
                <div id="edicion-linea" class="space-y-4 pt-8 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-5 h-5 text-blue-500"></i>
                        6.3 Edición de Línea
                    </h3>
                    <p>Para modificar una línea, selecciona el icono de edición <i data-lucide="edit-2" class="w-4 h-4 text-amber-600 inline mx-1"></i> en el directorio.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="bg-white p-5 rounded-xl border border-blue-100 shadow-sm hover:border-blue-200 transition-colors">
                            <h4 class="font-bold text-blue-900 mb-2 flex items-center gap-2 text-xs uppercase tracking-tight">
                                <i data-lucide="palette" class="w-4 h-4 text-blue-600"></i>
                                Actualización de Identidad
                            </h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Puedes cambiar el nombre, el icono o el color asignado. Esto redefine la marca visual de la línea en todo el portal.
                            </p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-blue-100 shadow-sm shadow-indigo-50">
                            <h4 class="font-bold text-gray-900 mb-2 flex items-center gap-2 text-xs uppercase tracking-tight">
                                <i data-lucide="refresh-cw" class="w-4 h-4 text-indigo-600"></i>
                                Sincronización Total
                            </h4>
                            <p class="text-[11px] text-gray-600 leading-relaxed">
                                Cualquier cambio en el <strong>Nombre o Color</strong> se reflejará automáticamente en todos los investigadores y proyectos vinculados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 7: Configuración: Inicio -->
        <section id="config-inicio" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-slate-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 7</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Configuración: Inicio</h2>
            </div>
            
            <div class="prose max-w-none text-gray-600 space-y-8">
                <p>Este módulo es el centro de control de la <strong>primera impresión</strong> del portal. Aquí gestionas la identidad visual que define al CIEEPE desde el momento en que un usuario entra al sitio.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-6">
                    <!-- 7.1 Identidad Institucional -->
                    <div id="config-logos" class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                        <h4 class="font-bold text-indigo-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-indigo-100 pb-2 leading-none">
                            <i data-lucide="image-upscale" class="w-4 h-4"></i>
                            Gestión de Logos
                        </h4>
                        <div class="space-y-4 text-[11px] leading-relaxed">
                            <div class="p-3 bg-white rounded border border-gray-200">
                                <p class="font-bold text-gray-800 mb-1">Logo Principal (Color)</p>
                                <p class="text-gray-500">Se utiliza en el menú lateral y en páginas internas con fondo claro.</p>
                            </div>
                            <div class="p-3 bg-gray-800 rounded text-white border border-gray-700">
                                <p class="font-bold text-blue-300 mb-1">Logo de Portada (Blanco)</p>
                                <p class="text-gray-400">Diseñado específicamente para que se vea claro sobre la foto de fondo.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 7.2 Anatomía de la Portada -->
                    <div id="config-hero" class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                        <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-blue-100 pb-2 leading-none">
                            <i data-lucide="type" class="w-4 h-4"></i>
                            Partes de la Cabecera Principal
                        </h4>
                        <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                            <li><strong>• Etiqueta decorativa:</strong> Pequeño texto de impacto arriba del título (Ej: "ENEES" o "2026").</li>
                            <li><strong>• Título Principal:</strong> Es el texto más grande y llamativo de la portada. Sé directo y potente.</li>
                            <li><strong>• Descripción:</strong> Texto secundario que contextualiza la misión del centro.</li>
                            <li class="pt-2">
                                <div class="bg-blue-50 p-3 rounded flex items-center gap-2">
                                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-blue-600"></i>
                                    <div>
                                        <p class="font-bold text-blue-800 uppercase text-[9px]">Estadísticas (Auto)</p>
                                        <p class="text-[9px] text-blue-600 italic">Los contadores se auto-actualizan.</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- 7.3 Fondo y Técnica -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                        <h4 class="font-bold text-purple-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-purple-100 pb-2 leading-none">
                            <i data-lucide="monitor" class="w-4 h-4"></i>
                            Fondo y Técnica
                        </h4>
                        <ul class="text-[11px] space-y-4 text-gray-600 leading-relaxed">
                            <li><strong>• Resolución Ideal:</strong> Se recomienda <strong>1920 x 1080</strong> px en formato JPG o PNG.</li>
                            <li><strong>• Contraste:</strong> El sistema aplica un oscurecimiento automático para que el texto blanco sea legible.</li>
                            <li><strong>• Previsualización:</strong> Usa el botón <strong>"Ver Sitio"</strong> en el encabezado para confirmar el cambio.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 8: Configuración: Nosotros -->
        <section id="config-nosotros" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 8</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Configuración: Nosotros</h2>
            </div>
            
            <div class="prose max-w-none text-gray-600 space-y-8">
                <p>Gestiona la narrativa institucional, permitiendo actualizar la historia, misión y los puntos clave de valor que representan al CIEEPE ante la comunidad académica.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-6">
                    <!-- 8.1 Narrativa Institucional -->
                    <div class="bg-blue-50/30 p-6 rounded-xl border border-blue-100 group hover:bg-white transition-all">
                        <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-blue-100 pb-2 leading-none">
                            <i data-lucide="file-text" class="w-4 h-4 text-blue-600"></i>
                            Narrativa Institucional
                        </h4>
                        <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                            <li><strong>• Título Superior:</strong> El encabezado de la sección (ej: "Quiénes Somos").</li>
                            <li><strong>• Cuerpo de Texto:</strong> Dos bloques de párrafo para detallar misión y visión.</li>
                            <li><strong>• Coherencia:</strong> Se recomienda usar un lenguaje formal y académico.</li>
                        </ul>
                    </div>

                    <!-- 8.2 Imagen del Centro -->
                    <div class="bg-blue-50/30 p-6 rounded-xl border border-blue-100 group hover:bg-white transition-all">
                        <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-blue-100 pb-2 leading-none">
                            <i data-lucide="image" class="w-4 h-4 text-blue-600"></i>
                            Imagen Institucional
                        </h4>
                        <ul class="text-[11px] space-y-3 text-gray-600 leading-relaxed">
                            <li><strong>• Fotografía:</strong> Imagen que acompaña la descripción (ej: campus o equipo).</li>
                            <li><strong>• Recomendación:</strong> Usar formatos horizontales para un mejor ajuste visual.</li>
                            <li><strong>• Calidad:</strong> Asegurar que la imagen sea nítida y profesional.</li>
                        </ul>
                    </div>

                    <!-- 8.3 Puntos de Valor (Checklist) -->
                    <div class="bg-blue-50/30 p-6 rounded-xl border border-blue-100 group hover:bg-white transition-all">
                        <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-widest border-b border-blue-100 pb-2 leading-none">
                            <i data-lucide="check-circle" class="w-4 h-4 text-blue-600"></i>
                            Lista de Valor (✓)
                        </h4>
                        <div class="space-y-4 text-[11px] leading-relaxed">
                            <p>Permite añadir puntos dinámicos que se muestran con una marca de verificación automáticamente.</p>
                            <div class="bg-white p-3 rounded border border-blue-100">
                                <p class="font-bold text-blue-800 mb-1">Tip de Gestión:</p>
                                <p class="text-[10px] text-gray-500 italic">Puedes agregar o quitar puntos según los objetivos anuales del centro.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 9: Configuración: Contacto -->
        <section id="config-contacto" class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 mb-10 group transition-all hover:border-red-200">
            <div class="flex items-center gap-2 mb-6">
                <span class="bg-red-50 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Sección 9</span>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Configuración: Contacto</h2>
            </div>
            
            <div class="prose max-w-none text-gray-600 space-y-8">
                <p>Define los canales oficiales de comunicación y la ubicación geográfica interactiva del centro.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                    <div class="bg-red-50/20 p-6 rounded-xl border border-red-100">
                        <h4 class="font-bold text-red-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-tight">
                            <i data-lucide="map-pin" class="w-4 h-4 text-red-600"></i>
                            Ubicación y Mapa
                        </h4>
                        <ul class="text-[11px] space-y-4 text-gray-600 leading-relaxed">
                            <li><strong>• Dirección:</strong> Domicilio físico desglosado para el pie de página.</li>
                            <li><strong>• Google Maps:</strong> Inserción del mapa dinámico mediante el código compartido por Google.</li>
                        </ul>
                    </div>

                    <div class="bg-red-50/20 p-6 rounded-xl border border-red-100">
                        <h4 class="font-bold text-red-900 mb-4 flex items-center gap-2 text-xs uppercase tracking-tight">
                            <i data-lucide="phone-call" class="w-4 h-4 text-red-600"></i>
                            Canales Directos
                        </h4>
                        <ul class="text-[11px] space-y-4 text-gray-600 leading-relaxed">
                            <li><strong>• Enlaces:</strong> Teléfono institucional, Correo y Horarios de atención.</li>
                            <li class="bg-white p-3 rounded border border-red-100 text-[10px] text-gray-400 italic">
                                <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i> El horario se visualiza con un icono de reloj automático en el sitio web.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección de Soporte WhatsApp -->
        <div class="mt-12 p-8 bg-blue-600 rounded-2xl text-white flex flex-col md:flex-row items-center justify-between shadow-lg shadow-blue-200">
            <div class="mb-6 md:mb-0">
                <h2 class="text-2xl font-bold mb-2">¿Necesitas ayuda adicional?</h2>
                <p class="text-blue-100">Si encuentras algún problema o tienes dudas, contacta a soporte por WhatsApp.</p>
            </div>
            <a href="https://wa.me/526672644610" target="_blank" class="px-8 py-4 bg-white text-blue-600 rounded-xl font-extrabold hover:bg-gray-100 transition-all flex items-center gap-3 shadow-md">
                <i data-lucide="message-circle" class="w-6 h-6"></i>
                Chatear con Soporte
            </a>
        </div>
    </div>

    <!-- ÍNDICE DERECHO (STICKY) -->
    <div class="lg:w-72 flex-shrink-0">
        <div class="sticky top-10 bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex flex-col max-h-[calc(100vh-160px)]">
            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 flex-shrink-0">
                <i data-lucide="list-tree" class="w-5 h-5 text-blue-600"></i>
                <h3 class="font-bold text-gray-900 leading-none">Índice del Manual</h3>
            </div>
            <div class="flex-1 overflow-y-auto pr-2 custom-scroll">
                <nav class="space-y-1">
                    <a href="#introduccion" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-blue-50 border-l-2 border-transparent hover:border-blue-500 group">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-tighter opacity-70 mb-0.5">Inicio</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-900">Introducción al Sistema</span>
                    </a>

                <a href="#acceso" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-indigo-50 border-l-2 border-transparent hover:border-indigo-500 group">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 1</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-900">Acceso al Sistema</span>
                </a>

                <a href="#dashboard" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-emerald-50 border-l-2 border-transparent hover:border-emerald-500 group">
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 2</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-900">Dashboard</span>
                </a>

                <a href="#investigadores" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-gray-50 border-l-2 border-transparent hover:border-blue-500 group">
                    <span class="text-xs font-bold text-blue-500 uppercase tracking-tighter opacity-70 mb-0.5">Sección 3</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-900">Gestión de Investigadores</span>
                </a>
                <div class="ml-4 space-y-1 mb-2 border-l border-blue-50">
                    <a href="#directorio-investigadores" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-blue-600 transition-colors leading-tight">• 3.1 Directorio y Búsqueda</a>
                    <a href="#alta-investigador" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-blue-600 transition-colors leading-tight">• 3.2 Registro de Nuevo Investigador</a>
                    <a href="#edicion-investigador" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-blue-600 transition-colors leading-tight">• 3.3 Edición de Investigador</a>
                    <a href="#investigador-avanzado" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-blue-600 transition-colors leading-tight">• 3.4 Especialidades y Publicaciones</a>
                </div>

                <a href="#proyectos" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-gray-50 border-l-2 border-transparent hover:border-amber-500 group">
                    <span class="text-xs font-bold text-amber-500 uppercase tracking-tighter opacity-70 mb-0.5">Sección 4</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-amber-900">Gestión de Proyectos</span>
                </a>
                <div class="ml-4 space-y-1 mb-2 border-l border-amber-50">
                    <a href="#directorio-proyectos" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-amber-600 transition-colors leading-tight">• 4.1 Directorio y Búsqueda</a>
                    <a href="#registro-proyecto" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-amber-600 transition-colors leading-tight">• 4.2 Registro de Nuevo Proyecto</a>
                    <a href="#edicion-proyecto" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-amber-600 transition-colors leading-tight">• 4.3 Edición de Proyecto</a>
                    <a href="#proyecto-avanzado" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-amber-600 transition-colors leading-tight">• 4.4 Metas y Objetivos Específicos</a>
                </div>

                <a href="#noticias" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-gray-50 border-l-2 border-transparent hover:border-indigo-500 group">
                    <span class="text-xs font-bold text-indigo-500 uppercase tracking-tighter opacity-70 mb-0.5">Sección 5</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-900">Gestión de Noticias</span>
                </a>
                <div class="ml-4 space-y-1 mb-2 border-l border-indigo-50">
                    <a href="#directorio-noticias" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-indigo-600 transition-colors leading-tight">• 5.1 Directorio y Búsqueda</a>
                    <a href="#registro-noticia" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-indigo-600 transition-colors leading-tight">• 5.2 Registro de Nueva Noticia</a>
                    <a href="#edicion-noticia" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-indigo-600 transition-colors leading-tight">• 5.3 Edición de Noticia</a>
                </div>

                <a href="#lineas" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-gray-50 border-l-2 border-transparent hover:border-blue-500 group">
                    <span class="text-xs font-bold text-blue-500 uppercase tracking-tighter opacity-70 mb-0.5">Sección 6</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-900">Gestión de Líneas de Investigación</span>
                </a>
                <div class="ml-4 space-y-1 mb-2 border-l border-blue-50">
                    <a href="#directorio-lineas" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-blue-600 transition-colors leading-tight">• 6.1 Directorio y Acciones</a>
                    <a href="#registro-linea" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-blue-600 transition-colors leading-tight">• 6.2 Registro de Nueva Línea</a>
                    <a href="#edicion-linea" class="block py-1 pl-3 text-[10px] text-gray-400 hover:text-blue-600 transition-colors leading-tight">• 6.3 Edición de Línea</a>
                </div>

                <a href="#config-inicio" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-gray-50 border-l-2 border-transparent hover:border-slate-500 group">
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 7</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-slate-900">Configuración: Inicio</span>
                </a>
                <a href="#config-nosotros" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-gray-50 border-l-2 border-transparent hover:border-blue-500 group">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 8</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-900">Configuración: Nosotros</span>
                </a>

                <a href="#config-contacto" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-gray-50 border-l-2 border-transparent hover:border-red-500 group">
                    <span class="text-xs font-bold text-red-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 9</span>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-red-900">Configuración: Contacto</span>
                </a>
            </nav>
            </div>

            <!-- Soporte Widget -->
            <div class="mt-8 pt-8 border-t border-gray-100 flex-shrink-0">
                <div class="bg-gray-900 rounded-xl p-4 text-white">
                    <p class="text-xs font-bold text-blue-400 uppercase mb-2">Ayuda Directa</p>
                    <p class="text-[10px] text-gray-400 mb-3 leading-relaxed">¿Tienes dudas sobre una función específica?</p>
                    <a href="https://wa.me/526672644610" target="_blank" class="w-full py-2 bg-blue-600 hover:bg-blue-700 transition-colors rounded-lg text-xs font-bold flex items-center justify-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        Contactar Soporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializar iconos de Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
