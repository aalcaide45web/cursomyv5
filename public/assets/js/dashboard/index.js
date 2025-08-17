// Dashboard Module
export class Dashboard {
    constructor() {
        this.initDashboardCounters();
        this.initDashboardFeatures();
        this.initCourseActions();
    }
    
    initDashboardCounters() {
        // Cargar estadísticas iniciales
        this.reloadDashboardStats();
    }
    
    async reloadDashboardStats() {
        try {
            const response = await fetch('/api/dashboard/stats');
            const data = await response.json();
            
            if (data.success) {
                this.updateDashboardStats(data.data);
            }
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }
    
    updateDashboardStats(stats) {
        const coursesCount = document.getElementById('courses-count');
        const lessonsCount = document.getElementById('lessons-count');
        const totalHours = document.getElementById('total-hours');
        
        if (coursesCount) coursesCount.textContent = stats.courses_count || 0;
        if (lessonsCount) lessonsCount.textContent = stats.lessons_count || 0;
        if (totalHours) totalHours.textContent = (stats.total_hours || 0) + 'h';
    }
    
    initDashboardFeatures() {
        this.addSystemInfoSection();
        this.loadSystemInfo();
        this.initScanStatsModal();
    }
    
    addSystemInfoSection() {
        // La sección ya está en el HTML, solo inicializar eventos
        const refreshBtn = document.getElementById('refresh-system-info');
        const viewStatsBtn = document.getElementById('view-scan-stats');
        
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadSystemInfo());
        }
        
        if (viewStatsBtn) {
            viewStatsBtn.addEventListener('click', () => this.showScanStatsModal());
        }
    }
    
    async loadSystemInfo() {
        try {
            const response = await fetch('/api/scan/system-info');
            const data = await response.json();
            
            if (data.success) {
                this.updateSystemInfo(data.data);
            } else {
                this.updateSystemInfoError(data.error);
            }
        } catch (error) {
            this.updateSystemInfoError('Error de conexión');
        }
    }
    
    updateSystemInfo(info) {
        const scannerStatus = document.getElementById('scanner-status');
        const ffmpegStatus = document.getElementById('ffmpeg-status');
        const cacheStatus = document.getElementById('cache-status');
        
        if (scannerStatus) {
            scannerStatus.textContent = info.scanner.available ? 'Disponible' : 'No disponible';
            scannerStatus.className = info.scanner.available ? 'text-sm text-green-400' : 'text-sm text-red-400';
        }
        
        if (ffmpegStatus) {
            ffmpegStatus.textContent = info.ffmpeg.available ? 'Disponible' : 'No disponible';
            ffmpegStatus.className = info.ffmpeg.available ? 'text-sm text-green-400' : 'text-sm text-red-400';
        }
        
        if (cacheStatus) {
            cacheStatus.textContent = info.hash_cache.status;
            cacheStatus.className = info.hash_cache.status === 'OK' ? 'text-sm text-green-400' : 'text-sm text-yellow-400';
        }
    }
    
    updateSystemInfoError(error) {
        const scannerStatus = document.getElementById('scanner-status');
        const ffmpegStatus = document.getElementById('ffmpeg-status');
        const cacheStatus = document.getElementById('cache-status');
        
        if (scannerStatus) {
            scannerStatus.textContent = 'Error';
            scannerStatus.className = 'text-sm text-red-400';
        }
        
        if (ffmpegStatus) {
            ffmpegStatus.textContent = 'Error';
            ffmpegStatus.className = 'text-sm text-red-400';
        }
        
        if (cacheStatus) {
            cacheStatus.textContent = 'Error';
            cacheStatus.className = 'text-sm text-red-400';
        }
        
        console.error('Error al cargar información del sistema:', error);
    }
    
    initScanStatsModal() {
        const modal = document.getElementById('scan-stats-modal');
        const closeBtn = document.getElementById('close-scan-stats');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                if (modal) modal.classList.add('hidden');
            });
        }
        
        // Cerrar modal al hacer clic fuera
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }
    }
    
    async showScanStatsModal() {
        const modal = document.getElementById('scan-stats-modal');
        const content = document.getElementById('scan-stats-content');
        
        if (!modal || !content) return;
        
        modal.classList.remove('hidden');
        content.innerHTML = 'Cargando estadísticas...';
        
        try {
            const response = await fetch('/api/scan/stats');
            const data = await response.json();
            
            if (data.success) {
                this.showScanStats(data.data);
            } else {
                content.innerHTML = `<div class="text-red-400">Error: ${data.error}</div>`;
            }
        } catch (error) {
            content.innerHTML = '<div class="text-red-400">Error de conexión</div>';
        }
    }
    
    showScanStats(stats) {
        const content = document.getElementById('scan-stats-content');
        if (!content) return;
        
        content.innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-700 p-3 rounded">
                        <div class="text-sm text-gray-400">Archivos Escaneados</div>
                        <div class="text-xl font-bold text-white">${stats.total_files || 0}</div>
                    </div>
                    <div class="bg-gray-700 p-3 rounded">
                        <div class="text-sm text-gray-400">Archivos Importados</div>
                        <div class="text-xl font-bold text-white">${stats.imported_files || 0}</div>
                    </div>
                    <div class="bg-gray-700 p-3 rounded">
                        <div class="text-sm text-gray-400">Lecciones Creadas</div>
                        <div class="text-xl font-bold text-white">${stats.lessons_created || 0}</div>
                    </div>
                    <div class="bg-gray-700 p-3 rounded">
                        <div class="text-sm text-gray-400">Lecciones Actualizadas</div>
                        <div class="text-xl font-bold text-white">${stats.lessons_updated || 0}</div>
                    </div>
                </div>
                
                <div class="bg-gray-700 p-3 rounded">
                    <div class="text-sm text-gray-400 mb-2">Detalles del Escaneo</div>
                    <div class="text-sm space-y-1">
                        <div>Topics detectados: <span class="text-white">${stats.topics_count || 0}</span></div>
                        <div>Instructores detectados: <span class="text-white">${stats.instructors_count || 0}</span></div>
                        <div>Cursos detectados: <span class="text-white">${stats.courses_count || 0}</span></div>
                        <div>Secciones detectadas: <span class="text-white">${stats.sections_count || 0}</span></div>
                    </div>
                </div>
                
                ${stats.errors && stats.errors.length > 0 ? `
                    <div class="bg-red-900/20 border border-red-700 p-3 rounded">
                        <div class="text-sm text-red-400 mb-2">Errores encontrados:</div>
                        <div class="text-xs text-red-300 space-y-1">
                            ${stats.errors.map(error => `<div>• ${error}</div>`).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    initCourseActions() {
        // Los botones de acción ya están en el HTML con onclick
        // Solo necesitamos definir las funciones globales
        window.viewCourse = this.viewCourse.bind(this);
        window.resumeCourse = this.resumeCourse.bind(this);
        window.renameCourse = this.renameCourse.bind(this);
        window.deleteCourse = this.deleteCourse.bind(this);
    }
    
    async viewCourse(slug) {
        try {
            window.location.href = `/course/${slug}`;
        } catch (error) {
            console.error('Error al navegar al curso:', error);
            alert('Error al abrir el curso');
        }
    }
    
    async resumeCourse(slug) {
        try {
            // Obtener progreso del curso
            const response = await fetch(`/api/courses/${slug}/progress`);
            const data = await response.json();
            
            if (data.success && data.data) {
                // Navegar a la lección con el progreso guardado
                window.location.href = `/course/${slug}?resume=${data.data.lesson_id}&time=${data.data.position}`;
            } else {
                // Si no hay progreso, ir al inicio del curso
                window.location.href = `/course/${slug}`;
            }
        } catch (error) {
            console.error('Error al reanudar curso:', error);
            // Fallback: ir al curso
            window.location.href = `/course/${slug}`;
        }
    }
    
    async renameCourse(slug, currentName) {
        const newName = prompt('Nuevo nombre del curso:', currentName);
        
        if (!newName || newName.trim() === '') return;
        
        try {
            const response = await fetch(`/api/courses/${slug}/rename`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ new_name: newName.trim() })
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Curso renombrado exitosamente');
                // Recargar la página para mostrar el nuevo nombre
                window.location.reload();
            } else {
                alert(`Error: ${data.error}`);
            }
        } catch (error) {
            console.error('Error al renombrar curso:', error);
            alert('Error al renombrar el curso');
        }
    }
    
    async deleteCourse(slug, courseName) {
        const confirmed = confirm(`¿Estás seguro de que quieres eliminar el curso "${courseName}"?\n\nEsta acción no se puede deshacer.`);
        
        if (!confirmed) return;
        
        try {
            const response = await fetch(`/api/courses/${slug}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Curso eliminado exitosamente');
                // Recargar la página para actualizar la lista
                window.location.reload();
            } else {
                alert(`Error: ${data.error}`);
            }
        } catch (error) {
            console.error('Error al eliminar curso:', error);
            alert('Error al eliminar el curso');
        }
    }
    
    // Funciones para mostrar/ocultar mensajes
    showNoCoursesMessage() {
        const noCourses = document.getElementById('no-courses');
        const coursesGrid = document.getElementById('courses-grid');
        
        if (noCourses) noCourses.classList.remove('hidden');
        if (coursesGrid) coursesGrid.classList.add('hidden');
    }
    
    hideNoCoursesMessage() {
        const noCourses = document.getElementById('no-courses');
        const coursesGrid = document.getElementById('courses-grid');
        
        if (noCourses) noCourses.classList.add('hidden');
        if (coursesGrid) coursesGrid.classList.remove('hidden');
    }
    
    clearCoursesGrid() {
        const coursesGrid = document.getElementById('courses-grid');
        if (coursesGrid) {
            coursesGrid.innerHTML = '';
        }
    }
    
    addCourseToGrid(courseData) {
        const coursesGrid = document.getElementById('courses-grid');
        if (!coursesGrid) return;
        
        // Crear elemento del curso usando el componente CourseCard
        const courseElement = document.createElement('div');
        courseElement.className = 'course-card bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 hover:bg-white/15 transition-all duration-300 group';
        
        // Aquí se renderizaría el CourseCard
        // Por ahora, crear una estructura básica
        courseElement.innerHTML = `
            <div class="relative mb-4 overflow-hidden rounded-lg">
                <div class="w-full h-32 bg-gray-700 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="space-y-2">
                <h3 class="font-semibold text-white text-lg">${courseData.name}</h3>
                <div class="text-gray-300 text-sm">${courseData.instructor_name || 'Sin instructor'}</div>
                <div class="text-gray-300 text-sm">${courseData.topic_name || 'Sin tema'}</div>
            </div>
            <div class="mt-4 flex gap-2">
                <button onclick="viewCourse('${courseData.slug}')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                    Ver
                </button>
                <button onclick="resumeCourse('${courseData.slug}')" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm py-2 px-3 rounded-lg transition-colors">
                    Reanudar
                </button>
            </div>
        `;
        
        coursesGrid.appendChild(courseElement);
    }
}

// Inicializar dashboard cuando se cargue el módulo
const dashboard = new Dashboard();
