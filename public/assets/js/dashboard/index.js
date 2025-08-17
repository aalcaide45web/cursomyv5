// Dashboard Module - CursoMy LMS Lite
console.log('📊 Inicializando módulo del dashboard...');

document.addEventListener('DOMContentLoaded', function() {
    initDashboardCounters();
    initDashboardFeatures();
});

async function initDashboardCounters() {
    try {
        console.log('🔄 Cargando estadísticas del dashboard...');
        
        const response = await fetch('/api/dashboard/stats');
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            const stats = result.data;
            console.log('📊 Estadísticas cargadas:', stats);
            
            // Actualizar contadores
            updateCounter('courses-count', stats.courses_count.toString());
            updateCounter('lessons-count', stats.lessons_count.toString());
            updateCounter('total-hours', `${stats.total_hours}h`);
            
            // Ocultar mensaje de "no hay cursos" si hay cursos
            if (stats.courses_count > 0) {
                toggleNoCoursesMessage(false);
            }
            
        } else {
            console.error('❌ Error en respuesta de API:', result.message);
            // Mostrar valores por defecto
            updateCounter('courses-count', '0');
            updateCounter('lessons-count', '0');
            updateCounter('total-hours', '0h');
        }
        
    } catch (error) {
        console.error('❌ Error al cargar estadísticas:', error);
        // Mostrar valores por defecto en caso de error
        updateCounter('courses-count', '0');
        updateCounter('lessons-count', '0');
        updateCounter('total-hours', '0h');
    }
}

function updateCounter(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

function initDashboardFeatures() {
    // Agregar información del sistema de escaneo
    addSystemInfoSection();
    
    // TODO: Implementar funcionalidades adicionales en fases posteriores
    console.log('Funcionalidades del dashboard inicializadas');
}

// Agregar sección de información del sistema
function addSystemInfoSection() {
    const dashboardContainer = document.querySelector('.space-y-8');
    if (!dashboardContainer) return;
    
    const systemInfoDiv = document.createElement('div');
    systemInfoDiv.className = 'glass rounded-xl p-6';
    systemInfoDiv.innerHTML = `
        <h3 class="text-xl font-semibold mb-4 text-glow">Información del Sistema</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-300">Estado del Escáner:</span>
                    <span id="scanner-status" class="text-green-400">Verificando...</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-300">ffmpeg:</span>
                    <span id="ffmpeg-status" class="text-yellow-400">Verificando...</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-300">Cache de Hashes:</span>
                    <span id="hash-cache-status" class="text-blue-400">Verificando...</span>
                </div>
            </div>
            <div class="space-y-2">
                <button id="refresh-system-info" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-white transition-colors w-full">
                    🔄 Actualizar Info
                </button>
                <button id="view-scan-stats" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-white transition-colors w-full">
                    📊 Ver Estadísticas
                </button>
            </div>
        </div>
    `;
    
    // Insertar después de la sección "Get Started"
    const getStartedSection = dashboardContainer.querySelector('.glass.rounded-xl.p-8.text-center');
    if (getStartedSection) {
        getStartedSection.parentNode.insertBefore(systemInfoDiv, getStartedSection.nextSibling);
    }
    
    // Agregar event listeners
    const refreshBtn = document.getElementById('refresh-system-info');
    const statsBtn = document.getElementById('view-scan-stats');
    
    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadSystemInfo);
    }
    
    if (statsBtn) {
        statsBtn.addEventListener('click', showScanStats);
    }
    
    // Cargar información inicial
    loadSystemInfo();
}

// Cargar información del sistema
async function loadSystemInfo() {
    try {
        const response = await fetch('/api/scan/system-info');
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const result = await response.json();
        if (result.status === 'success') {
            updateSystemInfo(result.data);
        }
    } catch (error) {
        console.error('Error cargando información del sistema:', error);
        updateSystemInfoError();
    }
}

// Actualizar información del sistema en la UI
function updateSystemInfo(data) {
    const scannerStatus = document.getElementById('scanner-status');
    const ffmpegStatus = document.getElementById('ffmpeg-status');
    const hashCacheStatus = document.getElementById('hash-cache-status');
    
    if (scannerStatus) {
        scannerStatus.textContent = '✅ Activo';
        scannerStatus.className = 'text-green-400';
    }
    
    if (ffmpegStatus) {
        if (data.media_probe.available) {
            ffmpegStatus.textContent = '✅ Disponible';
            ffmpegStatus.className = 'text-green-400';
        } else {
            ffmpegStatus.textContent = '⚠️ No disponible';
            ffmpegStatus.className = 'text-yellow-400';
        }
    }
    
    if (hashCacheStatus) {
        const cacheSize = data.hasher.cache_file_size;
        if (cacheSize > 0) {
            hashCacheStatus.textContent = `${data.hasher.total_cached_files} archivos`;
            hashCacheStatus.className = 'text-blue-400';
        } else {
            hashCacheStatus.textContent = 'Vacío';
            hashCacheStatus.className = 'text-blue-400';
        }
    }
}

// Mostrar error en información del sistema
function updateSystemInfoError() {
    const scannerStatus = document.getElementById('scanner-status');
    const ffmpegStatus = document.getElementById('ffmpeg-status');
    const hashCacheStatus = document.getElementById('hash-cache-status');
    
    if (scannerStatus) {
        scannerStatus.textContent = '❌ Error';
        scannerStatus.className = 'text-red-400';
    }
    
    if (ffmpegStatus) {
        ffmpegStatus.textContent = '❌ Error';
        ffmpegStatus.className = 'text-red-400';
    }
    
    if (hashCacheStatus) {
        hashCacheStatus.textContent = '❌ Error';
        hashCacheStatus.className = 'text-red-400';
    }
}

// Mostrar estadísticas de escaneo
async function showScanStats() {
    try {
        const response = await fetch('/api/scan/stats');
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const result = await response.json();
        if (result.status === 'success') {
            showScanStatsModal(result.data);
        }
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
        alert('Error al cargar estadísticas de escaneo');
    }
}

// Mostrar modal con estadísticas
function showScanStatsModal(stats) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="glass rounded-xl p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-glow">Estadísticas de Escaneo</h3>
                <button class="text-gray-400 hover:text-white text-2xl" onclick="this.closest('.fixed').remove()">&times;</button>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-glass-dark rounded-lg">
                        <div class="text-2xl font-bold text-green-400">${stats.total_files}</div>
                        <div class="text-sm text-gray-300">Archivos</div>
                    </div>
                    <div class="text-center p-3 bg-glass-dark rounded-lg">
                        <div class="text-2xl font-bold text-blue-400">${stats.total_size_formatted}</div>
                        <div class="text-sm text-gray-300">Tamaño Total</div>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Topics:</span>
                        <span class="font-semibold">${stats.topics_count}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Instructores:</span>
                        <span class="font-semibold">${stats.instructors_count}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Cursos:</span>
                        <span class="font-semibold">${stats.courses_count}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Errores:</span>
                        <span class="text-red-400 font-semibold">${stats.errors_count}</span>
                    </div>
                </div>
                <div class="border-t border-glass-border pt-4">
                    <h4 class="font-semibold mb-2">Extensiones:</h4>
                    <div class="flex flex-wrap gap-2">
                        ${Object.entries(stats.extensions).map(([ext, count]) => 
                            `<span class="px-2 py-1 bg-glass-dark rounded text-sm">${ext}: ${count}</span>`
                        ).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// Función para actualizar estadísticas del dashboard
export function updateDashboardStats(stats) {
    if (stats.courses_count !== undefined) {
        updateCounter('courses-count', stats.courses_count.toString());
    }
    
    if (stats.lessons_count !== undefined) {
        updateCounter('lessons_count', stats.lessons_count.toString());
    }
    
    if (stats.total_hours !== undefined) {
        updateCounter('total-hours', `${stats.total_hours}h`);
    }
}

// Función para mostrar/ocultar mensaje de "no hay cursos"
export function toggleNoCoursesMessage(show) {
    const messageDiv = document.getElementById('no-courses-message');
    if (messageDiv) {
        messageDiv.style.display = show ? 'block' : 'none';
    }
}

// Función para limpiar grid de cursos
export function clearCoursesGrid() {
    const grid = document.getElementById('courses-grid');
    if (grid) {
        grid.innerHTML = '';
    }
}

// Función para agregar curso al grid
export function addCourseToGrid(courseData) {
    const grid = document.getElementById('courses-grid');
    if (!grid) return;
    
    // TODO: Implementar en FASE 3 cuando tengamos el componente CourseCard
    console.log('Agregando curso al grid:', courseData);
    
    // Por ahora, crear un elemento temporal
    const courseElement = document.createElement('div');
    courseElement.className = 'glass rounded-xl p-6 border border-glass-border';
    courseElement.innerHTML = `
        <h3 class="text-lg font-semibold mb-2">${courseData.name}</h3>
        <p class="text-gray-400 text-sm">Instructor: ${courseData.instructor_name}</p>
        <p class="text-gray-400 text-sm">Temática: ${courseData.topic_name}</p>
        <div class="mt-4 flex space-x-2">
            <button class="px-3 py-1 bg-blue-500 rounded text-sm">Ver</button>
            <button class="px-3 py-1 glass rounded text-sm">Reanudar</button>
        </div>
    `;
    
    grid.appendChild(courseElement);
}

// Función para recargar estadísticas del dashboard
export async function reloadDashboardStats() {
    await initDashboardCounters();
}
