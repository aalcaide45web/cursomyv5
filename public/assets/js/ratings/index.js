/**
 * Módulo de Valoraciones - Maneja el sistema de estrellas 1-5
 */
export class RatingManager {
    constructor() {
        this.currentCourseId = null;
        this.userRating = 0;
        this.courseStats = null;
        this.init();
    }
    
    init() {
        this.bindEvents();
    }
    
    bindEvents() {
        // Eventos para estrellas clickeables
        document.addEventListener('click', (e) => {
            if (e.target.closest('.star-item')) {
                const starItem = e.target.closest('.star-item');
                const rating = parseInt(starItem.dataset.rating);
                const courseId = this.getCourseIdFromStar(starItem);
                
                if (rating && courseId) {
                    this.rateCourse(courseId, rating);
                }
            }
        });
        
        // Eventos para hover en estrellas
        document.addEventListener('mouseover', (e) => {
            if (e.target.closest('.star-item')) {
                this.handleStarHover(e);
            }
        });
        
        document.addEventListener('mouseout', (e) => {
            if (e.target.closest('.star-rating-component')) {
                this.handleStarHoverOut(e);
            }
        });
    }
    
    /**
     * Obtener ID del curso desde el elemento estrella
     */
    getCourseIdFromStar(starElement) {
        const component = starElement.closest('.star-rating-component');
        return component ? parseInt(component.dataset.courseId) : null;
    }
    
    /**
     * Manejar hover sobre estrellas
     */
    handleStarHover(e) {
        const starItem = e.target.closest('.star-item');
        const rating = parseInt(starItem.dataset.rating);
        const component = starItem.closest('.star-rating-component');
        
        if (!component || !rating) return;
        
        // Actualizar estrellas para mostrar preview
        this.updateStarsPreview(component, rating);
    }
    
    /**
     * Manejar salida del hover
     */
    handleStarHoverOut(e) {
        const component = e.target.closest('.star-rating-component');
        if (!component) return;
        
        // Restaurar estado original
        this.restoreStarsState(component);
    }
    
    /**
     * Actualizar preview de estrellas en hover
     */
    updateStarsPreview(component, rating) {
        const stars = component.querySelectorAll('.star-item');
        
        stars.forEach((star, index) => {
            const starRating = index + 1;
            const svg = star.querySelector('svg');
            
            if (starRating <= rating) {
                svg.classList.remove('text-gray-400');
                svg.classList.add('text-yellow-400');
            } else {
                svg.classList.remove('text-yellow-400');
                svg.classList.add('text-gray-400');
            }
        });
    }
    
    /**
     * Restaurar estado original de las estrellas
     */
    restoreStarsState(component) {
        // Esto se implementará cuando se carguen las estadísticas del curso
        if (this.courseStats) {
            this.updateStarsDisplay(component, this.courseStats.avg_rating);
        }
    }
    
    /**
     * Valorar un curso
     */
    async rateCourse(courseId, rating) {
        try {
            const response = await fetch(`/api/courses/${courseId}/rating`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ rating })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Actualizar estadísticas del curso
                await this.loadCourseRatingStats(courseId);
                
                // Mostrar mensaje de éxito
                this.showSuccessMessage('Valoración guardada exitosamente');
                
                // Actualizar UI
                this.updateRatingUI(courseId, result.data);
                
                // Actualizar dashboard si estamos en él
                if (window.dashboard) {
                    window.dashboard.reloadDashboardStats();
                }
            } else {
                this.showErrorMessage('Error al guardar valoración: ' + result.error);
            }
        } catch (error) {
            console.error('Error al valorar curso:', error);
            this.showErrorMessage('Error al guardar valoración');
        }
    }
    
    /**
     * Eliminar valoración de un curso
     */
    async removeRating(courseId) {
        try {
            const response = await fetch(`/api/courses/${courseId}/rating`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Actualizar estadísticas del curso
                await this.loadCourseRatingStats(courseId);
                
                // Mostrar mensaje de éxito
                this.showSuccessMessage('Valoración eliminada exitosamente');
                
                // Actualizar UI
                this.updateRatingUI(courseId, result.data);
                
                // Actualizar dashboard si estamos en él
                if (window.dashboard) {
                    window.dashboard.reloadDashboardStats();
                }
            } else {
                this.showErrorMessage('Error al eliminar valoración: ' + result.error);
            }
        } catch (error) {
            console.error('Error al eliminar valoración:', error);
            this.showErrorMessage('Error al eliminar valoración');
        }
    }
    
    /**
     * Cargar estadísticas de valoración de un curso
     */
    async loadCourseRatingStats(courseId) {
        try {
            const response = await fetch(`/api/courses/${courseId}/rating/stats`);
            const result = await response.json();
            
            if (result.success) {
                this.courseStats = result.data;
                this.updateRatingDistribution(courseId, result.data.distribution);
                return result.data;
            }
        } catch (error) {
            console.error('Error al cargar estadísticas de valoración:', error);
        }
        
        return null;
    }
    
    /**
     * Cargar valoración del usuario para un curso
     */
    async loadUserRating(courseId) {
        try {
            const response = await fetch(`/api/courses/${courseId}/rating`);
            const result = await response.json();
            
            if (result.success) {
                this.userRating = result.data.user_rating;
                this.updateUserRatingUI(courseId, result.data);
                return result.data;
            }
        } catch (error) {
            console.error('Error al cargar valoración del usuario:', error);
        }
        
        return null;
    }
    
    /**
     * Actualizar UI de valoración
     */
    updateRatingUI(courseId, stats) {
        // Actualizar componente de estrellas
        const component = document.querySelector(`[data-course-id="${courseId}"]`);
        if (component) {
            this.updateStarsDisplay(component, stats.avg_rating);
            this.updateRatingText(component, stats.avg_rating, stats.ratings_count);
            this.updateRatingCount(component, stats.ratings_count);
        }
        
        // Actualizar distribución si está visible
        this.updateRatingDistribution(courseId, stats.distribution);
    }
    
    /**
     * Actualizar UI de valoración del usuario
     */
    updateUserRatingUI(courseId, userData) {
        const component = document.querySelector(`[data-course-id="${courseId}"]`);
        if (!component) return;
        
        // Marcar estrellas del usuario
        const stars = component.querySelectorAll('.star-item');
        stars.forEach((star, index) => {
            const starRating = index + 1;
            const svg = star.querySelector('svg');
            
            if (starRating <= userData.user_rating) {
                svg.classList.add('text-yellow-500');
                svg.classList.remove('text-yellow-400');
            } else {
                svg.classList.remove('text-yellow-500');
                svg.classList.add('text-yellow-400');
            }
        });
    }
    
    /**
     * Actualizar display de estrellas
     */
    updateStarsDisplay(component, avgRating) {
        const stars = component.querySelectorAll('.star-item');
        const fullStars = Math.floor(avgRating);
        const hasHalfStar = (avgRating - fullStars) >= 0.5;
        
        stars.forEach((star, index) => {
            const starRating = index + 1;
            const svg = star.querySelector('svg');
            
            if (starRating <= fullStars) {
                svg.classList.remove('text-gray-400');
                svg.classList.add('text-yellow-400');
            } else if (starRating === fullStars + 1 && hasHalfStar) {
                svg.classList.remove('text-gray-400');
                svg.classList.add('text-yellow-400');
            } else {
                svg.classList.remove('text-yellow-400');
                svg.classList.add('text-gray-400');
            }
        });
    }
    
    /**
     * Actualizar texto de valoración
     */
    updateRatingText(component, avgRating, count) {
        const ratingText = component.querySelector('.text-gray-300');
        if (ratingText) {
            if (count === 0) {
                ratingText.textContent = 'Sin valoraciones';
            } else {
                ratingText.textContent = avgRating.toFixed(1);
            }
        }
    }
    
    /**
     * Actualizar contador de valoraciones
     */
    updateRatingCount(component, count) {
        const countElement = component.querySelector('.text-gray-500');
        if (countElement) {
            if (count === 0) {
                countElement.textContent = '';
            } else {
                const countText = count === 1 ? 'valoración' : 'valoraciones';
                countElement.textContent = `${count} ${countText}`;
            }
        }
    }
    
    /**
     * Actualizar distribución de valoraciones
     */
    updateRatingDistribution(courseId, distribution) {
        const component = document.querySelector(`[data-course-id="${courseId}"]`);
        if (!component) return;
        
        const distributionDiv = component.querySelector('.rating-distribution');
        if (!distributionDiv) return;
        
        // Mostrar distribución
        distributionDiv.classList.remove('hidden');
        
        // Actualizar barras de distribución
        const bars = distributionDiv.querySelectorAll('.bg-yellow-400');
        const counts = distributionDiv.querySelectorAll('.text-gray-400:last-child');
        
        bars.forEach((bar, index) => {
            const rating = 5 - index;
            const count = distribution[rating] || 0;
            const total = Object.values(distribution).reduce((a, b) => a + b, 0);
            const percentage = total > 0 ? (count / total) * 100 : 0;
            
            bar.style.width = `${percentage}%`;
        });
        
        counts.forEach((count, index) => {
            const rating = 5 - index;
            const countValue = distribution[rating] || 0;
            count.textContent = countValue;
        });
    }
    
    /**
     * Mostrar mensaje de éxito
     */
    showSuccessMessage(message) {
        // Implementar notificación de éxito
        console.log('✅ ' + message);
        // Aquí se puede integrar con un sistema de notificaciones
    }
    
    /**
     * Mostrar mensaje de error
     */
    showErrorMessage(message) {
        // Implementar notificación de error
        console.error('❌ ' + message);
        // Aquí se puede integrar con un sistema de notificaciones
    }
    
    /**
     * Inicializar valoraciones para un curso específico
     */
    async initCourseRating(courseId) {
        this.currentCourseId = courseId;
        
        // Cargar estadísticas y valoración del usuario
        await Promise.all([
            this.loadCourseRatingStats(courseId),
            this.loadUserRating(courseId)
        ]);
    }
    
    /**
     * Limpiar estado
     */
    destroy() {
        this.currentCourseId = null;
        this.userRating = 0;
        this.courseStats = null;
    }
}

// Función global para valorar cursos
window.rateCourse = function(courseId, rating) {
    if (window.ratingManager) {
        window.ratingManager.rateCourse(courseId, rating);
    }
};

// Crear instancia global
window.ratingManager = new RatingManager();
