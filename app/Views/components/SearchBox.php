<?php declare(strict_types=1);

/**
 * Componente SearchBox - Búsqueda global con autocompletado
 */
class SearchBox {
    private string $placeholder;
    private string $size;
    private bool $showFilters;
    
    public function __construct(string $placeholder = 'Buscar en todo el sistema...', string $size = 'lg', bool $showFilters = true) {
        $this->placeholder = $placeholder;
        $this->size = $size;
        $this->showFilters = $showFilters;
    }
    
    public function render(): string {
        $sizeClasses = $this->getSizeClasses();
        
        return <<<HTML
        <div class="search-box-component" x-data="searchBox()">
            <!-- Barra de búsqueda principal -->
            <div class="relative">
                <div class="relative">
                    <input type="text" 
                           x-model="query" 
                           @input="handleInput"
                           @keydown.enter="performSearch"
                           @keydown.escape="clearSearch"
                           @focus="showSuggestions = true"
                           placeholder="{$this->placeholder}"
                           class="{$sizeClasses} w-full bg-white/10 backdrop-blur-md border border-white/20 rounded-xl pl-12 pr-4 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    
                    <!-- Icono de búsqueda -->
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    
                    <!-- Botón de búsqueda -->
                    <button @click="performSearch" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg class="w-5 h-5 text-gray-400 hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Indicador de carga -->
                <div x-show="isLoading" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-y-0 right-0 pr-12 flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-400"></div>
                </div>
            </div>
            
            <!-- Filtros de búsqueda -->
            {$this->renderFilters()}
            
            <!-- Sugerencias de autocompletado -->
            <div x-show="showSuggestions && suggestions.length > 0" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="showSuggestions = false"
                 class="absolute z-50 w-full mt-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl shadow-xl overflow-hidden">
                
                <div class="max-h-64 overflow-y-auto">
                    <template x-for="suggestion in suggestions" :key="suggestion.text">
                        <button @click="selectSuggestion(suggestion)"
                                class="w-full px-4 py-3 text-left hover:bg-white/10 transition-colors border-b border-white/10 last:border-b-0">
                            <div class="flex items-center space-x-3">
                                <!-- Icono según tipo -->
                                <div class="flex-shrink-0">
                                    <span x-html="getTypeIcon(suggestion.type)" class="w-5 h-5 text-gray-400"></span>
                                </div>
                                
                                <!-- Texto de sugerencia -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-white font-medium truncate" x-text="suggestion.text"></p>
                                    <p class="text-gray-400 text-sm capitalize" x-text="suggestion.type"></p>
                                </div>
                                
                                <!-- Indicador de tipo -->
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="suggestion.type"></span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
                
                <!-- Footer con información -->
                <div class="px-4 py-2 bg-white/5 border-t border-white/10">
                    <p class="text-xs text-gray-400">
                        Presiona <kbd class="px-1 py-0.5 bg-gray-700 rounded text-xs">Enter</kbd> para buscar o <kbd class="px-1 py-0.5 bg-gray-700 rounded text-xs">Esc</kbd> para limpiar
                    </p>
                </div>
            </div>
            
            <!-- Resultados de búsqueda -->
            <div x-show="showResults && results.length > 0" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute z-50 w-full mt-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl shadow-xl overflow-hidden">
                
                <!-- Header de resultados -->
                <div class="px-4 py-3 bg-white/5 border-b border-white/10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-white font-medium">
                            Resultados de búsqueda (<span x-text="results.length"></span>)
                        </h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-400" x-text="'Método: ' + searchMethod"></span>
                            <button @click="clearSearch" 
                                    class="text-gray-400 hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de resultados -->
                <div class="max-h-96 overflow-y-auto">
                    <template x-for="result in results" :key="result.id">
                        <div class="px-4 py-3 hover:bg-white/10 transition-colors border-b border-white/10 last:border-b-0">
                            <div class="flex items-start space-x-3">
                                <!-- Icono del tipo -->
                                <div class="flex-shrink-0 mt-1">
                                    <span x-html="getTypeIcon(result.type)" class="w-5 h-5 text-gray-400"></span>
                                </div>
                                
                                <!-- Contenido del resultado -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <h4 class="text-white font-medium truncate" x-text="result.data.name || result.data.content"></h4>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize" x-text="result.type"></span>
                                    </div>
                                    
                                    <!-- Información adicional según tipo -->
                                    <div class="text-sm text-gray-400 space-y-1">
                                        <template x-if="result.type === 'course'">
                                            <div>
                                                <p x-text="'Instructor: ' + (result.data.instructor_name || 'N/A')"></p>
                                                <p x-text="'Topic: ' + (result.data.topic_name || 'N/A')"></p>
                                            </div>
                                        </template>
                                        
                                        <template x-if="result.type === 'lesson'">
                                            <div>
                                                <p x-text="'Curso: ' + (result.data.course_name || 'N/A')"></p>
                                                <p x-text="'Sección: ' + (result.data.section_name || 'N/A')"></p>
                                            </div>
                                        </template>
                                        
                                        <template x-if="result.type === 'section'">
                                            <div>
                                                <p x-text="'Curso: ' + (result.data.course_name || 'N/A')"></p>
                                            </div>
                                        </template>
                                        
                                        <template x-if="result.type === 'instructor'">
                                            <div>
                                                <p x-text="'Topic: ' + (result.data.topic_name || 'N/A')"></p>
                                            </div>
                                        </template>
                                        
                                        <!-- Texto coincidente -->
                                        <template x-if="result.matched_text">
                                            <p class="text-gray-300 italic">
                                                <span class="text-blue-400">Coincidencia:</span> 
                                                <span x-text="result.matched_text"></span>
                                            </p>
                                        </template>
                                    </div>
                                </div>
                                
                                <!-- Acciones -->
                                <div class="flex-shrink-0">
                                    <button @click="navigateToResult(result)" 
                                            class="text-blue-400 hover:text-blue-300 transition-colors text-sm">
                                        Ver
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Footer con acciones -->
                <div class="px-4 py-3 bg-white/5 border-t border-white/10">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-400">
                            Mostrando <span x-text="results.length"></span> de <span x-text="totalResults"></span> resultados
                        </p>
                        <div class="flex items-center space-x-2">
                            <button @click="rebuildSearchIndex" 
                                    class="text-xs text-gray-400 hover:text-white transition-colors">
                                Reconstruir índice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
    
    private function renderFilters(): string {
        if (!$this->showFilters) {
            return '';
        }
        
        return <<<HTML
        <div x-show="showFilters" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="mt-3 flex flex-wrap gap-2">
            
            <!-- Filtro por tipo -->
            <div class="flex items-center space-x-2">
                <label class="text-sm text-gray-300">Tipo:</label>
                <select x-model="filters.type" 
                        @change="applyFilters"
                        class="bg-white/10 border border-white/20 rounded-lg px-3 py-1 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="course">Cursos</option>
                    <option value="lesson">Lecciones</option>
                    <option value="section">Secciones</option>
                    <option value="instructor">Instructores</option>
                    <option value="topic">Topics</option>
                    <option value="note">Notas</option>
                    <option value="comment">Comentarios</option>
                </select>
            </div>
            
            <!-- Filtro por fecha -->
            <div class="flex items-center space-x-2">
                <label class="text-sm text-gray-300">Últimos:</label>
                <select x-model="filters.days" 
                        @change="applyFilters"
                        class="bg-white/10 border border-white/20 rounded-lg px-3 py-1 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="1">1 día</option>
                    <option value="7">7 días</option>
                    <option value="30">30 días</option>
                    <option value="90">90 días</option>
                </select>
            </div>
            
            <!-- Botón para mostrar/ocultar filtros -->
            <button @click="showFilters = !showFilters" 
                    class="text-xs text-gray-400 hover:text-white transition-colors">
                <span x-text="showFilters ? 'Ocultar filtros' : 'Mostrar filtros'"></span>
            </button>
        </div>
        HTML;
    }
    
    private function getSizeClasses(): string {
        switch ($this->size) {
            case 'sm':
                return 'text-sm py-2';
            case 'md':
                return 'text-base py-3';
            case 'lg':
                return 'text-lg py-4';
            case 'xl':
                return 'text-xl py-5';
            default:
                return 'text-base py-3';
        }
    }
}
