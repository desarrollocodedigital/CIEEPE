<?php
// Este archivo es una sección incluida (modulo=manual) en admin_biblioteca.php
// No requiere etiquetas html, head o body ya que se renderiza dentro del layout principal
?><style>
    .manual-scroll-container { scroll-behavior: smooth; }
    .manual-content::-webkit-scrollbar { width: 6px; }
    .manual-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .manual-content::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<div class="flex flex-col lg:flex-row gap-8 max-w-7xl mx-auto">
    
    <!-- ÁREA DE CONTENIDO (IZQUIERDA) -->
    <div class="flex-1 manual-content manual-scroll-container overflow-y-auto pr-2 pb-20 scroll-mt-20">
        
        <!-- Header del Manual -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-12 mb-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i data-lucide="book-open" class="w-6 h-6"></i>
                    </div>
                    <span class="text-emerald-600 font-bold tracking-widest text-xs uppercase">Documentación Técnica</span>
                </div>
                <h1 class="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Manual de Gestión Bibliotecaria</h1>
                <p class="text-lg text-slate-500 leading-relaxed max-w-2xl font-medium">
                    Guía completa para la administración del acervo digital, gestión de investigadores y supervisión académica del CIATA.
                </p>
            </div>
        </div>

        <!-- SECCIÓN: Introducción -->
        <section id="introduccion" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-emerald-200">
            <div class="flex items-center gap-2 mb-8">
                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Inicio</span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Introducción al sistema</h2>
            </div>
            <div class="prose prose-slate max-w-none text-slate-600 space-y-6">
                <p class="leading-relaxed">El Sistema de Gestión de la Biblioteca CIATA es una plataforma especializada diseñada para la administración segura del acervo académico y la supervisión de la comunidad de investigadores.</p>
                
                <!-- Tabla de Roles -->
                <div class="mt-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600"></i>
                        Estructura de Roles y Permisos
                    </h3>
                    <div class="overflow-hidden border border-slate-200 rounded-xl shadow-sm">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Rol</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Permisos</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Acceso a PDF</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-[9px] font-bold leading-5 text-emerald-800 bg-emerald-100 rounded-full italic uppercase">Administrador</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-600">Control total del sistema: gestión de usuarios, documentos y configuración global.</td>
                                    <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-tighter">Acceso Total</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-[9px] font-bold leading-5 text-blue-800 bg-blue-100 rounded-full italic uppercase">Investigador</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-600">Subida de tesis/artículos y consulta de documentos protegidos.</td>
                                    <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-tighter">Visualizar y subir pdfs</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-[9px] font-bold leading-5 text-slate-800 bg-slate-100 rounded-full italic uppercase">Usuario</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-600">Consulta de documentos públicos y perfil básico de usuario.</td>
                                    <td class="px-6 py-4 text-xs text-slate-500 font-bold uppercase tracking-tighter">Solo puede visualizar</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-6 rounded-r-2xl mt-8">
                    <p class="text-sm text-emerald-700 font-medium">
                        <strong>Nota de seguridad:</strong> El acceso a los documentos PDF originales está protegido mediante un visor cifrado que impide la descarga no autorizada.
                    </p>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 1: Acceso -->
        <section id="acceso" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-indigo-200">
            <div class="flex items-center gap-2 mb-8">
                <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 1</span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Acceso al sistema</h2>
            </div>
            
            <div class="space-y-12">
                <!-- Admin Access -->
                <div class="relative pl-8 border-l-2 border-emerald-500">
                    <div class="absolute -left-[11px] top-0 w-5 h-5 bg-emerald-500 rounded-full border-4 border-white shadow-sm"></div>
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2 italic uppercase text-xs tracking-widest">
                        <i data-lucide="user-cog" class="w-4 h-4 text-emerald-600"></i> Acceso Administrador
                    </h3>
                    <ol class="space-y-4 text-slate-600 text-sm list-decimal list-inside font-medium border-l-2 border-emerald-100 pl-4 ml-2 mt-4">
                        <li class="pl-2">Navegar a la ruta institucional <code class="bg-slate-100 px-2 py-0.5 rounded text-emerald-600 uppercase text-[10px] font-bold tracking-widest">DominioPrincipal.com/login_biblioteca.php</code>.</li>
                        <li class="pl-2">Ingresar las credenciales de superusuario proporcionadas por el departamento de sistemas.</li>
                        <li class="pl-2">Acceder directamente al panel de control central para supervisar el acervo y la comunidad.</li>
                    </ol>
                </div>

                <!-- Investigador Access -->
                <div class="relative pl-8 border-l-2 border-blue-500">
                    <div class="absolute -left-[11px] top-0 w-5 h-5 bg-blue-500 rounded-full border-4 border-white shadow-sm"></div>
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2 italic uppercase text-xs tracking-widest">
                        <i data-lucide="microscope" class="w-4 h-4 text-blue-600"></i> Acceso Investigador
                    </h3>
                    <ol class="space-y-4 text-slate-600 text-sm list-decimal list-inside font-medium border-l-2 border-blue-100 pl-4 ml-2 mt-4">
                        <li class="pl-2">Acceder al portal de registro: <code class="bg-slate-100 px-2 py-0.5 rounded text-blue-600 uppercase text-[10px] font-bold tracking-widest">DominioPrincipal.com/registro_biblioteca.php</code>.</li>
                        <li class="pl-2">Proporcionar su <strong>Nombre Completo</strong>, <strong>Correo Institucional</strong> y una contraseña segura.</li>
                        <li class="pl-2">En el selector de "Tipo de Usuario", elegir específicamente la opción: <span class="text-blue-600 font-bold tracking-tight">Investigador</span>.</li>
                        <li class="pl-2">Describir de forma exhaustiva su <strong>Motivo de acceso</strong> institucional y su línea de investigación.</li>
                        <li class="pl-2">Tras enviar la solicitud, el comité CIATA procederá a la <strong>validación de identidad</strong> (Ver Sección 3).</li>
                        <li class="pl-2">Una vez aprobada la cuenta, podrá ingresar en <code class="bg-slate-100 px-2 py-0.5 rounded text-blue-600 uppercase text-[10px] font-bold tracking-widest">DominioPrincipal.com/login_biblioteca.php</code> para gestionar recursos y consultar el catálogo protegido.</li>
                    </ol>
                </div>

                <!-- Usuario Access -->
                <div class="relative pl-8 border-l-2 border-slate-400">
                    <div class="absolute -left-[11px] top-0 w-5 h-5 bg-slate-400 rounded-full border-4 border-white shadow-sm"></div>
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2 italic uppercase text-xs tracking-widest">
                        <i data-lucide="user-search" class="w-4 h-4 text-slate-500"></i> Acceso Usuario
                    </h3>
                    <ol class="space-y-4 text-slate-600 text-sm list-decimal list-inside font-medium border-l-2 border-slate-100 pl-4 ml-2 mt-4">
                        <li class="pl-2">Ingresar al portal de registro institucional: <code class="bg-slate-100 px-2 py-0.5 rounded text-slate-600 uppercase text-[10px] font-bold tracking-widest">DominioPrincipal.com/registro_biblioteca.php</code>.</li>
                        <li class="pl-2">Completar el formulario con su <strong>Nombre Completo</strong>, <strong>Correo Electrónico</strong> y una contraseña segura.</li>
                        <li class="pl-2">En el campo "Tipo de Usuario", seleccionar la opción: <span class="text-blue-600 font-bold">Membresía de Biblioteca</span>.</li>
                        <li class="pl-2">Describir de forma breve el <strong>Motivo de acceso</strong> (ej: Consulta académica, interés de investigación, etc.).</li>
                        <li class="pl-2">Una vez enviado, su solicitud entrará en un <strong>periodo de revisión</strong> por parte del comité CIATA para validar su perfil.</li>
                        <li class="pl-2">Tras recibir la notificación de activación, podrá iniciar sesión en <code class="bg-slate-100 px-2 py-0.5 rounded text-slate-600 uppercase text-[10px] font-bold tracking-widest">DominioPrincipal.com/login_biblioteca.php</code> para visualizar los documentos públicos autorizados.</li>
                    </ol>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 2: Dashboard -->
        <section id="dashboard" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-8">
                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 2</span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Dashboard</h2>
            </div>
            <div class="prose prose-slate max-w-none text-slate-600 space-y-6">
                <p class="leading-relaxed">El Dashboard es el centro de mando donde obtendrás una visión rápida del estado general de la biblioteca. Desde aquí puedes monitorear la actividad de los investigadores y acceder a todas las herramientas de gestión académica.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-md transition-all">
                        <span class="block font-bold text-slate-900 mb-2 uppercase text-[10px] tracking-widest text-blue-600">Indicadores Críticos</span>
                        <p class="text-sm">Presenta cifras exactas de <strong>Libros y Documentos</strong> activos, además del conteo de investigadores validados.</p>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-md transition-all">
                        <span class="block font-bold text-slate-900 mb-2 uppercase text-[10px] tracking-widest text-emerald-600">Acciones Inmediatas</span>
                        <p class="text-sm">Botones dinámicos para procesar registros pendientes sin necesidad de navegar por los menús laterales.</p>
                    </div>
                </div>

                <!-- Detalle del Menú Lateral -->
                <div class="mt-12">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                        <i data-lucide="layout-list" class="w-5 h-5 text-blue-600"></i>
                        Estructura del Menú Lateral
                    </h3>
                    <div class="overflow-hidden border border-slate-200 rounded-2xl shadow-sm">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Apartado</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Módulo</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Propósito Operativo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-[10px] font-bold text-slate-400 uppercase tracking-widest">Principal</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800 italic">Dashboard</td>
                                    <td class="px-6 py-4 text-slate-600">Monitoreo de métricas y resumen de actividad diaria.</td>
                                </tr>
                                <tr class="bg-blue-50/20">
                                    <td rowspan="2" class="px-6 py-4 whitespace-nowrap text-[10px] font-bold text-blue-400 uppercase tracking-widest border-r border-slate-100">Biblioteca</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">Biblioteca General</td>
                                    <td class="px-6 py-4 text-slate-600">Catálogo total de libros y recursos digitales.</td>
                                </tr>
                                <tr class="bg-blue-50/20">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">Revisión de Documentos</td>
                                    <td class="px-6 py-4 text-slate-600">Aprobación de nuevos documentos enviados por investigadores.</td>
                                </tr>
                                <tr class="bg-indigo-50/20">
                                    <td rowspan="2" class="px-6 py-4 whitespace-nowrap text-[10px] font-bold text-indigo-400 uppercase tracking-widest border-r border-slate-100">Comunidad</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">Solicitudes</td>
                                    <td class="px-6 py-4 text-slate-600">Validación de nuevos investigadores y perfiles de usuario.</td>
                                </tr>
                                <tr class="bg-indigo-50/20">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">Gestión de Usuarios</td>
                                    <td class="px-6 py-4 text-slate-600">Directorio y control de accesos de la comunidad académica.</td>
                                </tr>
                                <tr class="bg-emerald-50/20">
                                    <td class="px-6 py-4 whitespace-nowrap text-[10px] font-bold text-emerald-400 uppercase tracking-widest italic">Sistema</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-700">Configuración</td>
                                    <td class="px-6 py-4 text-slate-600">Manejo del Asistente Virtual y parámetros del sitio bibliotecario.</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Ayuda</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">Manual</td>
                                    <td class="px-6 py-4 text-slate-600">Acceso a esta guía interactiva.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Elementos Globales -->
                <div class="mt-12 pt-10 border-t border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                        <i data-lucide="monitor" class="w-5 h-5 text-emerald-600"></i>
                        Interfaz Global y Barra Superior
                    </h3>
                    <p class="text-sm text-slate-600 mb-8">El sistema mantiene una estructura persistente para agilizar el trabajo operativo sin importar el módulo en el que se encuentre.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-2 h-full bg-blue-500"></div>
                            <p class="font-bold text-slate-900 text-[11px] uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i data-lucide="layout-template" class="w-4 h-4 text-blue-600"></i> Barra Superior (Header)
                            </p>
                            <ul class="space-y-3 text-sm text-slate-600 font-medium">
                                <li class="flex items-center gap-3">
                                    <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                                    <span><strong>Título de Sección:</strong> Indica el módulo activo actual.</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                                    <span><strong>Ver Biblioteca:</strong> Enlace para previsualizar el portal público.</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>
                                    <span><strong>ID Usuario:</strong> Muestra el correo del administrador logueado.</span>
                                </li>
                            </ul>
                        </div>

                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-2 h-full bg-red-500"></div>
                            <p class="font-bold text-slate-900 text-[11px] uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i data-lucide="shield-alert" class="w-4 h-4 text-red-600"></i> Seguridad de Sesión
                            </p>
                            <p class="text-xs leading-relaxed text-slate-500 mb-4">Las sesiones inactivas caducarán automáticamente por políticas de protección de datos académicos.</p>
                            <div class="flex items-center gap-2 p-3 bg-red-50 text-red-700 rounded-xl border border-red-100">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                <span class="text-[10px] font-black uppercase tracking-tight">Cerrar Sesión se encuentra en el Sidebar inferior.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 3: Biblioteca General -->
        <section id="biblioteca-general" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-emerald-200">
            <div class="flex items-center gap-2 mb-8">
                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 3</span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Biblioteca General</h2>
            </div>
            <div class="prose max-w-none text-slate-600 space-y-6">
                <p>El módulo de <strong>Biblioteca General</strong> te permite visualizar y administrar el catálogo integral del CIATA. Como administrador, tu principal función en esta área es la gestión y supervisión del acervo, ya que la subida de nuevos documentos corresponde exclusivamente a los investigadores.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Tarjeta 1 -->
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-blue-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-blue-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="search" class="w-5 h-5 text-blue-600"></i> Búsqueda Dinámica
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Motor de búsqueda en tiempo real para localizar rápidamente el material en el catálogo masivo.</p>
                        <ul class="mt-3 space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Búsqueda por Título:</strong> Encuentra documentos específicos al instante.</li>
                            <li><strong>Búsqueda por Autor:</strong> Permite localizar todos los trabajos subidos por un investigador.</li>
                        </ul>
                    </div>

                    <!-- Tarjeta 2 -->
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-indigo-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-indigo-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="filter" class="w-5 h-5 text-indigo-600"></i> Filtrado Estructural
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Aísla los resultados de acuerdo a clasificaciones técnicas y su estatus de publicación.</p>
                        <ul class="mt-3 space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Filtros de Estado:</strong> Separa entre Publicados, Pendientes, Suspendidos, Rechazados y Borradores.</li>
                            <li><strong>Filtros por Tipo:</strong> Muestra únicamente Artículos, Tesis o Acervos de forma categorizada.</li>
                        </ul>
                    </div>

                    <!-- Tarjeta 3 -->
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-amber-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-amber-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="edit-3" class="w-5 h-5 text-amber-600"></i> Modificación de Documentos
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Acceso a la vista de edición técnica para ajustar y corregir los datos de cualquier recurso.</p>
                        <ul class="mt-3 space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Edición de Metadatos:</strong> Actualiza títulos, portadas, resúmenes e información institucional desde el panel.</li>
                            <li><strong>Cambio de Estatus:</strong> Capacidad para cambiar la disponibilidad pública o suspender temporalmente el material.</li>
                        </ul>
                    </div>
                    
                    <!-- Tarjeta 4 -->
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-emerald-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-emerald-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="external-link" class="w-5 h-5 text-emerald-600"></i> Visor Seguro
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Visualización del contenido original del archivo en un entorno encapsulado.</p>
                        <ul class="mt-3 space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Pre-visualización:</strong> Acceso inmediato al PDF enviado por los investigadores.</li>
                            <li><strong>Descarga:</strong> Posibilidad de descargar el archivo original para su revisión local o resguardo.</li>
                            <li><strong>Auditoría:</strong> Corrobora la calidad del contenido para la aprobación de divulgación en el portal principal.</li>
                        </ul>
                    </div>
                    
                    <!-- Tarjeta 5: Paginación -->
                    <div class="col-span-1 md:col-span-2 p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-slate-300 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-slate-400"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="layers" class="w-5 h-5 text-slate-500"></i> Navegación y Paginación Inteligente
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Para mantener la velocidad del sistema, el catálogo muestra los documentos en bloques de 10 registros por página. Puedes navegar usando los siguientes controles situados al final de la tabla:</p>
                        <ul class="mt-3 space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Botones de Navegación:</strong> Usa los botones « Anterior y Siguiente » para moverte entre las páginas de resultados.</li>
                            <li><strong>Números de Página:</strong> Haz clic directamente en un número para saltar a esa página específica.</li>
                            <li><strong>Resumen de Registros:</strong> El sistema indica claramente cuántos registros estás visualizando y el total disponible (ej. "Mostrando 1 a 10 de 30").</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 4: Revisión de Documentos -->
        <section id="documentos" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-emerald-200">
            <div class="flex items-center gap-2 mb-8">
                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 4</span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Revisión de Documentos</h2>
            </div>
            <div class="prose max-w-none text-slate-600 space-y-6">
                <p>Este módulo es la sala de control para gobernar las nuevas aportaciones a la biblioteca. Cada vez que un investigador suba un nuevo documento, ingresará a esta cola de revisión con estatus de <strong>Pendiente</strong>.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-amber-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-amber-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="file-search" class="w-5 h-5 text-amber-600"></i> Auditoría Técnica
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Antes de publicar el documento en el portal principal, al administrador le corresponde:</p>
                        <ul class="mt-3 space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Pre-visualizar el PDF:</strong> Acceso rápido al archivo original haciendo clic en "REVISAR CONTENIDO".</li>
                            <li><strong>Control de Metadatos:</strong> Validar que el título y autor reportados correspondan efectivamente con la primera hoja del PDF.</li>
                        </ul>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden flex flex-col justify-center">
                        <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5 text-slate-600"></i> Decisiones de Gobernanza
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-600 mt-0.5"></i>
                                <div>
                                    <strong class="text-xs text-emerald-800 block uppercase tracking-widest leading-none mb-1">Aprobar</strong>
                                    <span class="text-[10px] text-emerald-600 leading-tight">El documento obtiene el sello de "Publicado" y es indexado en la biblioteca principal de forma inmediata.</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-3 bg-red-50 rounded-xl border border-red-100">
                                <i data-lucide="x" class="w-4 h-4 text-red-600 mt-0.5"></i>
                                <div>
                                    <strong class="text-xs text-red-800 block uppercase tracking-widest leading-none mb-1">Rechazar</strong>
                                    <span class="text-[10px] text-red-600 leading-tight">Deniega su publicación. Su estatus cambia a "Rechazado" y no se admitirá en el acervo digital público.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 5: Solicitudes -->
        <section id="solicitudes" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-indigo-200">
            <div class="flex items-center gap-2 mb-8">
                <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 5</span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Solicitudes de Acceso</h2>
            </div>
            <div class="prose max-w-none text-slate-600 space-y-6">
                <p>Este apartado funciona como el filtro principal de la comunidad académica. Aquí se revisan las peticiones de estudiantes, profesores o externos que desean obtener una cuenta de usuario.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-indigo-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-indigo-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="user-search" class="w-5 h-5 text-indigo-600"></i> Auditoría de Perfil
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Cada solicitud se presenta en una cola de revisión en la que se analiza:</p>
                        <ul class="mt-3 space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Identidad y Rol:</strong> Verifica el nombre, correo y el perfil institucional solicitado.</li>
                            <li><strong>Motivo de Acceso:</strong> Justificativa escrita por el usuario para obtener entrada a la biblioteca.</li>
                        </ul>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-emerald-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-emerald-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i data-lucide="shield-alert" class="w-5 h-5 text-emerald-600"></i> Toma de Decisiones
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Gobernanza de identidades mediante dos acciones concluyentes:</p>
                        <ul class="mt-3 space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Aceptar:</strong> El usuario cambia a estatus "activo" y se le concede acceso a la plataforma con los privilegios de su rol.</li>
                            <li><strong>Rechazar:</strong> Deniega de forma definitiva la solicitud (requiere usar un cuadro de confirmación).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 6: Usuarios -->
        <section id="usuarios" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-blue-200">
            <div class="flex items-center gap-2 mb-8">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 6</span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Gestión de Usuarios</h2>
            </div>
            <div class="prose max-w-none text-slate-600 space-y-6">
                <p>Directorio exhaustivo desde donde el administrador posee el control maestro de todas las cuentas en la plataforma. Utilizando las herramientas de edición, es posible intervenir en la seguridad, autenticación y jerarquía de cualquier usuario.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-blue-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-blue-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2 text-sm">
                            <i data-lucide="layers" class="w-5 h-5 text-blue-600"></i> Paginación y Edición
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">Explora el universo de usuarios interactuando con controles numéricos y filtros rápidos:</p>
                        <ul class="space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Filtro en Vivo:</strong> Aísla perfiles Pendientes, Activos o Rechazados instantáneamente.</li>
                            <li><strong>Identidad:</strong> Edita manualmente correos o nombres para resolver bugs de inicio de sesión de los estudiantes.</li>
                        </ul>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-amber-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-amber-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2 text-sm">
                            <i data-lucide="award" class="w-5 h-5 text-amber-600"></i> Membresías y Roles
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">Poder absoluto sobre la escala de privilegios que define cuánto puede hacer una persona:</p>
                        <ul class="space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Ascenso Perfil:</strong> Elevar de un simple miembro a un 'Investigador' avalado.</li>
                            <li><strong>Roles VIP:</strong> Capacidad para delegar cuentas ordinarias a roles de Administración General suprema.</li>
                        </ul>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-red-100 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-red-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2 text-sm">
                            <i data-lucide="shield-off" class="w-5 h-5 text-red-600"></i> Estados y Bloqueos
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">Gobernabilidad coercitiva para imponer el orden revirtiendo accesos por mal uso:</p>
                        <ul class="space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Destrucción:</strong> Revoca permisos a cuentas problemáticas pasándolas al estatus 'Rechazado'.</li>
                            <li><strong>Suspensión:</strong> Coloca cuentas en 'Pendiente' paralizando su entrada temporalmente.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 7: Sistema -->
        <section id="configuracion" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mb-10 group transition-all hover:border-slate-300">
            <div class="flex items-center gap-2 mb-8">
                <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight">Sección 7</span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Configuración del Sistema</h2>
            </div>
            <div class="prose max-w-none text-slate-600 space-y-6">
                <p>Módulo de ajustes globales que impactan instantáneamente el comportamiento de la biblioteca y la experiencia de los usuarios visitantes.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-slate-400 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-slate-700"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2 text-sm">
                            <i data-lucide="bot" class="w-5 h-5 text-slate-700"></i> Asistente CIATA (Botón AI)
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">Control de visibilidad del chatbot algorítmico "Pregúntale al CIATA":</p>
                        <ul class="space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Desactivación Segura:</strong> El conmutador permite remover los scripts de inteligencia artificial de la interfaz pública para ahorrar recursos del servidor.</li>
                            <li><strong>Aplicación Inmediata:</strong> Al presionar Guardar, las reglas se aplican en tiempo real descartando memorias caché.</li>
                        </ul>
                    </div>
                    
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group-hover:border-blue-200 transition-colors">
                        <div class="absolute top-0 right-0 w-2 h-full bg-blue-500"></div>
                        <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2 text-sm">
                            <i data-lucide="hard-drive" class="w-5 h-5 text-blue-600"></i> Escalabilidad Global
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">El sistema previene saturaciones conectándose a la tabla base <code>site_config</code>:</p>
                        <ul class="space-y-2 text-xs text-slate-500 list-disc list-inside">
                            <li><strong>Interconexión Central:</strong> Esta sección nutre a todas las ramificaciones del proyecto CIEEPE.</li>
                            <li><strong>Proyección a Futuro:</strong> Preparado para integrar permisos, control de almacenamiento y cuotas.</li>
                        </ul>
                    </div>
                </div>
            </div>
        <!-- Sección de Soporte WhatsApp -->
        <div class="mt-12 p-8 bg-emerald-600 rounded-2xl text-white flex flex-col md:flex-row items-center justify-between shadow-lg shadow-emerald-200">
            <div class="mb-6 md:mb-0 text-center md:text-left">
                <h2 class="text-2xl font-bold mb-2">¿Necesitas ayuda con la gestión?</h2>
                <p class="text-emerald-100 italic">Si tienes problemas con la aprobación de documentos o la administración de usuarios, estamos para apoyarte.</p>
            </div>
            <a href="https://wa.me/526672644610?text=Hola%20buenas,%20te%20hablo%20desde%20el%20sistema%20de%20CIEEPE%20por%20qu%C3%A9%20necesito%20soporte" target="_blank"
                class="px-8 py-4 bg-white text-emerald-600 rounded-xl font-extrabold hover:bg-gray-100 transition-all flex items-center gap-3 shadow-md">
                <i data-lucide="message-circle" class="w-6 h-6"></i>
                Chatear con Soporte
            </a>
        </div>
    </div>

    <!-- ÍNDICE DERECHO (STICKY) -->
    <div class="hidden xl:block lg:w-72 flex-shrink-0">
        <div class="sticky top-10 bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex flex-col max-h-[calc(100vh-160px)]">
            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 flex-shrink-0">
                <i data-lucide="list-tree" class="w-5 h-5 text-emerald-600"></i>
                <h3 class="font-bold text-gray-900 leading-none">Índice del Manual</h3>
            </div>
            <div class="flex-1 overflow-y-auto pr-2 custom-scroll">
                <nav class="space-y-1">
                    <a href="#introduccion" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-emerald-50 border-l-2 border-transparent hover:border-emerald-500 group">
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-tighter opacity-70 mb-0.5">Inicio</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-900">Introducción al sistema</span>
                    </a>

                    <a href="#acceso" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-indigo-50 border-l-2 border-transparent hover:border-indigo-500 group">
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 1</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-900">Acceso al sistema</span>
                    </a>

                    <a href="#dashboard" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-blue-50 border-l-2 border-transparent hover:border-blue-500 group">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 2</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-900">Dashboard</span>
                    </a>

                    <a href="#biblioteca-general" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-emerald-50 border-l-2 border-transparent hover:border-emerald-500 group">
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 3</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-emerald-900">Biblioteca General</span>
                    </a>

                    <a href="#documentos" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-amber-50 border-l-2 border-transparent hover:border-amber-500 group">
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 4</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-amber-900">Revisión de Documentos</span>
                    </a>

                    <a href="#solicitudes" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-indigo-50 border-l-2 border-transparent hover:border-indigo-500 group">
                        <span class="text-xs font-bold text-indigo-500 uppercase tracking-tighter opacity-70 mb-0.5">Sección 5</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-900">Solicitudes</span>
                    </a>

                    <a href="#usuarios" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-blue-50 border-l-2 border-transparent hover:border-blue-500 group">
                        <span class="text-xs font-bold text-blue-500 uppercase tracking-tighter opacity-70 mb-0.5">Sección 6</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-900">Gestión de Usuarios</span>
                    </a>

                    <a href="#configuracion" class="flex flex-col px-4 py-3 rounded-xl transition-all hover:bg-slate-50 border-l-2 border-transparent hover:border-slate-500 group">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-tighter opacity-70 mb-0.5">Sección 7</span>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-slate-900">Configuración del Sistema</span>
                    </a>
                </nav>
            </div>

            <!-- Soporte Widget -->
            <div class="mt-8 pt-8 border-t border-gray-100 flex-shrink-0">
                <div class="bg-gray-900 rounded-xl p-4 text-white">
                    <p class="text-xs font-bold text-emerald-400 uppercase mb-2">Ayuda Directa</p>
                    <p class="text-[10px] text-gray-400 mb-3 leading-relaxed">¿Dudas sobre la gestión académica?</p>
                    <a href="https://wa.me/526672644610?text=Hola%20buenas,%20te%20hablo%20desde%20el%20sistema%20de%20CIEEPE%20por%20qu%C3%A9%20necesito%20soporte" target="_blank" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 transition-colors rounded-lg text-xs font-bold flex items-center justify-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        Contactar Soporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Scrollspy Logic
    document.addEventListener('DOMContentLoaded', () => {
        const sections = document.querySelectorAll('section[id], div[id^="header-"]'); // En este archivo el header es un div
        // Nota: En admin_manual_biblioteca.php el header principal no tiene id="inicio", 
        // pero las secciones sí tienen sus IDs. Vamos a observar las áreas principales.
        const navLinks = document.querySelectorAll('nav a[href^="#"]');
        
        const observerOptions = {
            rootMargin: '-10% 0px -80% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    navLinks.forEach(link => {
                        // Limpiar estados activos
                        link.classList.remove('bg-emerald-50', 'bg-indigo-50', 'bg-blue-50', 'bg-amber-50', 'bg-slate-50');
                        link.classList.remove('border-emerald-500', 'border-indigo-500', 'border-blue-500', 'border-amber-500', 'border-slate-500');
                        link.classList.add('border-transparent');

                        if (link.getAttribute('href') === `#${id}`) {
                            link.classList.remove('border-transparent');
                            // Aplicar color según el href definido en el HTML original
                            if(id === 'introduccion') link.classList.add('bg-emerald-50', 'border-emerald-500');
                            if(id === 'acceso') link.classList.add('bg-indigo-50', 'border-indigo-500');
                            if(id === 'dashboard') link.classList.add('bg-blue-50', 'border-blue-500');
                            if(id === 'biblioteca-general') link.classList.add('bg-emerald-50', 'border-emerald-500');
                            if(id === 'documentos') link.classList.add('bg-amber-50', 'border-amber-500');
                            if(id === 'solicitudes') link.classList.add('bg-indigo-50', 'border-indigo-500');
                            if(id === 'usuarios') link.classList.add('bg-blue-50', 'border-blue-500');
                            if(id === 'configuracion') link.classList.add('bg-slate-50', 'border-slate-500');
                        }
                    });
                }
            });
        }, observerOptions);

        const targetSections = document.querySelectorAll('section[id]');
        targetSections.forEach(section => observer.observe(section));
    });
</script>
