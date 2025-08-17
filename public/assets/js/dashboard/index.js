// Dashboard Module - CursoMy LMS Lite
console.log('📊 Inicializando módulo del dashboard...');

// Funcionalidades específicas del dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Dashboard DOM cargado');
    
    // Inicializar contadores del dashboard
    initDashboardCounters();
    
    // Inicializar funcionalidades específicas
    initDashboardFeatures();
});

// Inicializar contadores del dashboard
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

// Actualizar contador específico
function updateCounter(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

// Inicializar funcionalidades del dashboard
function initDashboardFeatures() {
    // TODO: Implementar funcionalidades adicionales en fases posteriores
    console.log('Funcionalidades del dashboard pendientes de implementación');
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
