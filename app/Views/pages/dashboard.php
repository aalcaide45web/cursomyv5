<?php
// El dashboard solo debe contener la lógica de presentación
// La configuración y clases se cargan en index.php
?>

<div class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900">
    <!-- Header del Dashboard -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-white mb-4">Bienvenido a CursoMy LMS</h1>
        <p class="text-xl text-gray-300">Tu plataforma de aprendizaje personal</p>
    </div>

    <!-- Buscador Global -->
    <div class="max-w-4xl mx-auto mb-8">
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6">
            <h2 class="text-xl font-semibold text-white mb-4 text-center">Buscador Global</h2>
            <p class="text-gray-300 text-center mb-4">Busca en cursos, lecciones, instructores, notas y comentarios</p>
            <?php echo (new SearchBox('Buscar en todo el sistema...', 'lg', true))->render(); ?>
        </div>
    </div>

    <!-- Tarjetas de Estado -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-blue-400 mb-2" id="courses-count">0</div>
            <div class="text-gray-300">Cursos</div>
        </div>
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-green-400 mb-2" id="lessons-count">0</div>
            <div class="text-gray-300">Lecciones</div>
        </div>
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-purple-400 mb-2" id="total-hours">0h</div>
            <div class="text-gray-300">Horas de Contenido</div>
        </div>
    </div>

    <!-- Sección de Acciones -->
    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 mb-8">
        <h2 class="text-2xl font-semibold text-white mb-4">Comenzar</h2>
        <div class="flex flex-wrap gap-4">
            <button id="incremental-scan" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center"
                    onclick="startScan('incremental')">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Escaneo Incremental
            </button>
            <button id="rebuild-scan" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center"
                    onclick="startScan('rebuild')">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reconstruir Todo
            </button>
        </div>
        
        <!-- Barra de Progreso -->
        <div id="scan-progress" class="hidden mt-4">
            <div class="bg-gray-700 rounded-full h-2 mb-2">
                <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div id="progress-text" class="text-sm text-gray-300">Preparando escaneo...</div>
        </div>
        
        <!-- Logs del Escaneo -->
        <div id="scan-logs" class="hidden mt-4 p-4 bg-black/20 rounded-lg max-h-32 overflow-y-auto">
            <div class="text-sm text-gray-300 font-mono" id="logs-content"></div>
        </div>
    </div>

    <!-- Información del Sistema -->
    <div id="system-info-section" class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 mb-8">
        <h2 class="text-2xl font-semibold text-white mb-4">Información del Sistema</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="text-center">
                <div class="text-lg font-semibold text-gray-300 mb-2">Scanner</div>
                <div id="scanner-status" class="text-sm">Cargando...</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-semibold text-gray-300 mb-2">ffmpeg</div>
                <div id="ffmpeg-status" class="text-sm">Cargando...</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-semibold text-gray-300 mb-2">Cache</div>
                <div id="cache-status" class="text-sm">Cargando...</div>
            </div>
        </div>
        <div class="flex gap-2">
            <button id="refresh-system-info" 
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition-colors">
                Actualizar Info
            </button>
            <button id="view-scan-stats" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">
                Ver Estadísticas
            </button>
        </div>
    </div>

    <!-- Grid de Cursos -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-white mb-6">Mis Cursos</h2>
        
        <?php if (empty($courses)): ?>
            <!-- Mensaje cuando no hay cursos -->
            <div class="text-center py-12">
                <div class="text-gray-400 text-lg mb-4">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    No tienes cursos disponibles
                </div>
                <p class="text-gray-500 mb-6">Comienza haciendo un escaneo de tu directorio de uploads</p>
                <button id="first-scan" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                    Primer Escaneo
                </button>
            </div>
        <?php else: ?>
            <!-- Grid de tarjetas de cursos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($courses as $course): ?>
                    <?php
                    $courseCard = new CourseCard($course, $config['UPLOADS_PATH'] ?? 'uploads');
                    echo $courseCard->render();
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Estadísticas de Escaneo -->
<div id="scan-stats-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 border border-gray-600 rounded-xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-white">Estadísticas de Escaneo</h3>
                <button onclick="closeScanStatsModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div id="scan-stats-content" class="space-y-4">
                <!-- El contenido se carga dinámicamente -->
            </div>
        </div>
    </div>
</div>
