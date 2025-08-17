<header class="glass sticky top-0 z-50 border-b border-glass-border">
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo y título -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-xl">C</span>
                </div>
                <h1 class="text-2xl font-bold text-glow">CursoMy LMS</h1>
            </div>
            
            <!-- Buscador global -->
            <div class="flex-1 max-w-2xl mx-8">
                <div class="relative">
                    <input 
                        type="text" 
                        id="global-search"
                        placeholder="Buscar por temática, instructor, curso, sección, clase, nota, comentario..."
                        class="w-full px-4 py-2 pl-10 glass-dark rounded-lg border border-glass-border focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200"
                    >
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Botones de escaneo -->
            <div class="flex items-center space-x-3">
                <!-- Botón Escaneo Incremental -->
                <button 
                    id="scan-incremental"
                    class="px-4 py-2 glass rounded-lg border border-glass-border hover:bg-white hover:bg-opacity-20 transition-all duration-200 flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Incremental</span>
                </button>
                
                <!-- Botón Rebuild Total -->
                <button 
                    id="scan-rebuild"
                    class="px-4 py-2 glass rounded-lg border border-glass-border hover:bg-white hover:bg-opacity-20 transition-all duration-200 flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Rebuild</span>
                </button>
                
                <!-- Toggle de Tema -->
                <div class="ml-4">
                    <?php echo (new ThemeToggle())->render(); ?>
                </div>
            </div>
        </div>
        
        <!-- Barra de progreso del escaneo (oculta por defecto) -->
        <div id="scan-progress" class="hidden mt-4">
            <div class="flex items-center justify-between text-sm text-gray-300 mb-2">
                <span id="scan-status">Escaneando archivos...</span>
                <span id="scan-percentage">0%</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2">
                <div id="scan-progress-bar" class="bg-gradient-to-r from-blue-400 to-purple-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div id="scan-logs" class="mt-2 text-xs text-gray-400 max-h-20 overflow-y-auto"></div>
        </div>
    </div>
</header>
