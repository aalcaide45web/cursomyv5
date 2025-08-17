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
function initDashboardCounters() {
    // TODO: Implementar en FASE 1 cuando tengamos la base de datos
    console.log('Contadores del dashboard pendientes de implementación en FASE 1');
    
    // Por ahora, mostrar valores por defecto
    updateCounter('courses-count', '0');
    updateCounter('lessons-count', '0');
    updateCounter('total-hours', '0h');
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
    if (stats.courses !== undefined) {
        updateCounter('courses-count', stats.courses.toString());
    }
    
    if (stats.lessons !== undefined) {
        updateCounter('lessons-count', stats.lessons.toString());
    }
    
    if (stats.totalHours !== undefined) {
        updateCounter('total-hours', `${stats.totalHours}h`);
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
        <p class="text-gray-400 text-sm">Instructor: ${courseData.instructor}</p>
        <p class="text-gray-400 text-sm">Temática: ${courseData.topic}</p>
        <div class="mt-4 flex space-x-2">
            <button class="px-3 py-1 bg-blue-500 rounded text-sm">Ver</button>
            <button class="px-3 py-1 glass rounded text-sm">Reanudar</button>
        </div>
    `;
    
    grid.appendChild(courseElement);
}
