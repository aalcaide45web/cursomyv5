<div class="space-y-8">
    <!-- Header del dashboard -->
    <div class="text-center">
        <h2 class="text-4xl font-bold text-glow mb-4">Bienvenido a CursoMy LMS</h2>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto">
            Tu plataforma de aprendizaje personal. Escanea tu carpeta de cursos para comenzar a organizar y aprender.
        </p>
    </div>
    
    <!-- Estado del sistema -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Cursos</h3>
            <p id="courses-count" class="text-3xl font-bold text-blue-400">0</p>
        </div>
        
        <div class="glass rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Lecciones</h3>
            <p id="lessons-count" class="text-3xl font-bold text-purple-400">0</p>
        </div>
        
        <div class="glass rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Horas</h3>
            <p id="total-hours" class="text-3xl font-bold text-green-400">0h</p>
        </div>
    </div>
    
    <!-- Mensaje de bienvenida -->
    <div class="glass rounded-xl p-8 text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <h3 class="text-2xl font-semibold mb-4">¡Comienza ahora!</h3>
        <p class="text-gray-300 mb-6 max-w-2xl mx-auto">
            Para empezar a usar CursoMy LMS, coloca tus cursos en la carpeta <code class="bg-gray-800 px-2 py-1 rounded">/uploads</code> 
            y luego haz clic en "Incremental" para escanear los archivos.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button 
                id="get-started-incremental"
                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-200 font-semibold flex items-center justify-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Escanear Incremental</span>
            </button>
            <button 
                id="get-started-rebuild"
                class="px-6 py-3 glass rounded-lg border border-glass-border hover:bg-white hover:bg-opacity-20 transition-all duration-200 font-semibold flex items-center justify-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Rebuild Completo</span>
            </button>
        </div>
    </div>
    
    <!-- Grid de cursos (inicialmente vacío) -->
    <div id="courses-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Las tarjetas de curso se cargarán aquí dinámicamente -->
    </div>
    
    <!-- Mensaje cuando no hay cursos -->
    <div id="no-courses-message" class="glass rounded-xl p-8 text-center">
        <p class="text-gray-400 text-lg">
            No hay cursos disponibles. Escanea tu carpeta de uploads para comenzar.
        </p>
    </div>
</div>
