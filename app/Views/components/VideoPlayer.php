<?php declare(strict_types=1);

/**
 * Componente VideoPlayer - Reproductor de video HTML5 con controles avanzados
 */
class VideoPlayer {
    private array $lesson;
    private ?array $progress;
    private string $uploadsPath;
    
    public function __construct(array $lesson, ?array $progress, string $uploadsPath) {
        $this->lesson = $lesson;
        $this->progress = $progress;
        $this->uploadsPath = $uploadsPath;
    }
    
    public function render(): string {
        $lesson = $this->lesson;
        $progress = $this->progress;
        $videoPath = $this->getVideoPath($lesson);
        $resumeTime = $progress ? $progress['position'] : 0;
        $duration = $lesson['duration_seconds'] ?? 0;
        
        return <<<HTML
        <div class="video-player-container bg-black rounded-xl overflow-hidden">
            <!-- Video Element -->
            <video id="lesson-video" 
                   class="w-full h-auto" 
                   controls 
                   preload="metadata"
                   data-lesson-id="{$lesson['id']}"
                   data-course-id="{$lesson['course_id']}"
                   data-resume-time="{$resumeTime}"
                   data-duration="{$duration}">
                <source src="{$videoPath}" type="video/mp4">
                <source src="{$videoPath}" type="video/webm">
                <source src="{$videoPath}" type="video/mkv">
                Tu navegador no soporta el elemento de video.
            </video>
            
            <!-- Controles Personalizados -->
            <div class="custom-controls bg-gray-900 p-4 space-y-4">
                <!-- Barra de Progreso -->
                <div class="progress-container">
                    <div class="flex items-center justify-between text-sm text-gray-300 mb-2">
                        <span id="current-time">0:00</span>
                        <span id="total-time">{$this->formatDuration($duration)}</span>
                    </div>
                    <div class="relative">
                        <div class="bg-gray-700 rounded-full h-2 cursor-pointer" id="progress-bar">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-150" id="progress-fill" style="width: 0%"></div>
                        </div>
                        <div class="absolute top-0 left-0 bg-blue-400 h-2 rounded-full opacity-0 transition-opacity duration-150" id="progress-hover" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Controles de Reproducción -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Botón Play/Pause -->
                        <button id="play-pause-btn" class="text-white hover:text-blue-400 transition-colors">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        
                        <!-- Controles de Velocidad -->
                        <div class="flex items-center space-x-2">
                            <label for="playback-speed" class="text-sm text-gray-300">Velocidad:</label>
                            <select id="playback-speed" class="bg-gray-800 text-white text-sm rounded px-2 py-1 border border-gray-600">
                                <option value="0.50">0.50x</option>
                                <option value="0.75">0.75x</option>
                                <option value="1.00" selected>1.00x</option>
                                <option value="1.25">1.25x</option>
                                <option value="1.50">1.50x</option>
                                <option value="1.75">1.75x</option>
                                <option value="2.00">2.00x</option>
                                <option value="2.50">2.50x</option>
                                <option value="3.00">3.00x</option>
                                <option value="4.00">4.00x</option>
                                <option value="5.00">5.00x</option>
                                <option value="6.00">6.00x</option>
                                <option value="7.00">7.00x</option>
                                <option value="8.00">8.00x</option>
                                <option value="9.00">9.00x</option>
                                <option value="10.00">10.00x</option>
                            </select>
                        </div>
                        
                        <!-- Botón de Pantalla Completa -->
                        <button id="fullscreen-btn" class="text-white hover:text-blue-400 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Indicador de Progreso -->
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-pulse" id="progress-indicator"></div>
                        <span class="text-sm text-gray-300">Guardando progreso...</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panel de Notas y Comentarios -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Panel de Notas -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Notas</h3>
                    <button id="add-note-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors">
                        + Agregar Nota
                    </button>
                </div>
                
                <!-- Formulario de Nueva Nota -->
                <div id="note-form" class="hidden mb-4">
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <input type="number" id="note-timestamp" placeholder="Tiempo (seg)" 
                                   class="bg-gray-800 text-white text-sm rounded px-3 py-2 border border-gray-600 w-24">
                            <span class="text-gray-300 text-sm">segundos</span>
                        </div>
                        <textarea id="note-content" placeholder="Escribe tu nota aquí..." rows="3"
                                  class="w-full bg-gray-800 text-white text-sm rounded px-3 py-2 border border-gray-600 resize-none"></textarea>
                        <div class="flex space-x-2">
                            <button id="save-note-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition-colors">
                                Guardar
                            </button>
                            <button id="cancel-note-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Notas -->
                <div id="notes-list" class="space-y-3">
                    <!-- Las notas se cargan dinámicamente -->
                </div>
            </div>
            
            <!-- Panel de Comentarios -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Comentarios</h3>
                    <button id="add-comment-btn" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                        + Comentar
                    </button>
                </div>
                
                <!-- Formulario de Nuevo Comentario -->
                <div id="comment-form" class="hidden mb-4">
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="comment-timestamp-check" class="rounded border-gray-600">
                            <label for="comment-timestamp-check" class="text-gray-300 text-sm">Incluir timestamp actual</label>
                        </div>
                        <textarea id="comment-content" placeholder="Escribe tu comentario aquí..." rows="3"
                                  class="w-full bg-gray-800 text-white text-sm rounded px-3 py-2 border border-gray-600 resize-none"></textarea>
                        <div class="flex space-x-2">
                            <button id="save-comment-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition-colors">
                                Comentar
                            </button>
                            <button id="cancel-comment-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Comentarios -->
                <div id="comments-list" class="space-y-3">
                    <!-- Los comentarios se cargan dinámicamente -->
                </div>
            </div>
        </div>
        HTML;
    }
    
    private function getVideoPath(array $lesson): string {
        $filePath = $lesson['file_path'] ?? '';
        if (empty($filePath)) {
            return '';
        }
        
        // Construir ruta completa al video
        return $this->uploadsPath . '/' . ltrim($filePath, '/');
    }
    
    private function formatDuration(int $seconds): string {
        if ($seconds === 0) {
            return '0:00';
        }
        
        $hours = intval($seconds / 3600);
        $minutes = intval(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%d:%02d', $minutes, $secs);
    }
}
