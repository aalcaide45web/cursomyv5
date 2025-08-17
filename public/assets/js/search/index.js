/**
 * Módulo de Búsqueda Global - Maneja búsquedas en todo el sistema
 */
export class SearchManager {
    constructor() {
        this.currentQuery = '';
        this.currentResults = [];
        this.searchMethod = 'composite';
        this.isIndexing = false;
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeAlpine();
    }
    
    bindEvents() {
        // Eventos globales de búsqueda
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K para abrir búsqueda
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.focusSearchBox();
            }
        });
    }
    
    /**
     * Inicializar Alpine.js para el componente de búsqueda
     */
    initializeAlpine() {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('searchBox', () => ({
                query: '',
                suggestions: [],
                results: [],
                isLoading: false,
                showSuggestions: false,
                showResults: false,
                showFilters: false,
                searchMethod: 'composite',
                totalResults: 0,
                filters: {
                    type: '',
                    days: '',
                    instructor_id: '',
                    topic_id: ''
                },
                
                // Métodos de Alpine
                async handleInput() {
                    if (this.query.length < 2) {
                        this.suggestions = [];
                        this.showSuggestions = false;
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(this.query)}&limit=10`);
                        const result = await response.json();
                        
                        if (result.success) {
                            this.suggestions = result.data;
                            this.showSuggestions = true;
                        }
                    } catch (error) {
                        console.error('Error obteniendo sugerencias:', error);
                    }
                },
                
                async performSearch() {
                    if (this.query.trim().length < 2) return;
                    
                    this.isLoading = true;
                    this.showSuggestions = false;
                    
                    try {
                        const response = await fetch(`/api/search?q=${encodeURIComponent(this.query)}&limit=50`);
                        const result = await response.json();
                        
                        if (result.success) {
                            this.results = result.data;
                            this.searchMethod = result.method;
                            this.totalResults = result.count;
                            this.showResults = true;
                            
                            // Actualizar método de búsqueda global
                            if (window.searchManager) {
                                window.searchManager.searchMethod = result.method;
                            }
                        } else {
                            console.error('Error en búsqueda:', result.error);
                        }
                    } catch (error) {
                        console.error('Error en búsqueda:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                async applyFilters() {
                    if (this.query.trim().length < 2) return;
                    
                    this.isLoading = true;
                    
                    try {
                        const filterParams = new URLSearchParams();
                        filterParams.append('q', this.query);
                        filterParams.append('limit', '50');
                        
                        // Agregar filtros activos
                        Object.entries(this.filters).forEach(([key, value]) => {
                            if (value && value !== '') {
                                filterParams.append(`filters[${key}]`, value);
                            }
                        });
                        
                        const response = await fetch(`/api/search/filters?${filterParams.toString()}`);
                        const result = await response.json();
                        
                        if (result.success) {
                            this.results = result.data;
                            this.totalResults = result.count;
                            this.showResults = true;
                        } else {
                            console.error('Error aplicando filtros:', result.error);
                        }
                    } catch (error) {
                        console.error('Error aplicando filtros:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                selectSuggestion(suggestion) {
                    this.query = suggestion.text;
                    this.showSuggestions = false;
                    
                    // Navegar directamente si es un curso o lección
                    if (suggestion.type === 'course' && suggestion.link) {
                        window.location.href = `/course/${suggestion.link}`;
                    } else if (suggestion.type === 'lesson' && suggestion.link) {
                        window.location.href = `/${suggestion.link}`;
                    } else {
                        // Realizar búsqueda con la sugerencia
                        this.performSearch();
                    }
                },
                
                clearSearch() {
                    this.query = '';
                    this.suggestions = [];
                    this.results = [];
                    this.showSuggestions = false;
                    this.showResults = false;
                    this.filters = {
                        type: '',
                        days: '',
                        instructor_id: '',
                        topic_id: ''
                    };
                },
                
                navigateToResult(result) {
                    let url = '';
                    
                    switch (result.type) {
                        case 'course':
                            url = `/course/${result.data.slug}`;
                            break;
                        case 'lesson':
                            if (result.data.course_slug) {
                                url = `/course/${result.data.course_slug}#lesson-${result.data.id}`;
                            }
                            break;
                        case 'section':
                            if (result.data.course_slug) {
                                url = `/course/${result.data.course_slug}#section-${result.data.id}`;
                            }
                            break;
                        case 'instructor':
                            url = `/instructor/${result.data.id}`;
                            break;
                        case 'topic':
                            url = `/topic/${result.data.id}`;
                            break;
                        case 'note':
                            if (result.data.lesson_id) {
                                // Navegar a la lección y saltar al timestamp
                                url = `/lesson/${result.data.lesson_id}?note=${result.data.id}`;
                            }
                            break;
                        case 'comment':
                            if (result.data.lesson_id) {
                                // Navegar a la lección y mostrar comentario
                                url = `/lesson/${result.data.lesson_id}?comment=${result.data.id}`;
                            }
                            break;
                    }
                    
                    if (url) {
                        window.location.href = url;
                    }
                },
                
                async rebuildSearchIndex() {
                    if (this.isIndexing) return;
                    
                    this.isIndexing = true;
                    
                    try {
                        const response = await fetch('/api/search/rebuild-index', {
                            method: 'POST'
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Mostrar mensaje de éxito
                            this.showNotification('Índice de búsqueda reconstruido exitosamente', 'success');
                            
                            // Realizar búsqueda nuevamente si hay resultados
                            if (this.results.length > 0) {
                                this.performSearch();
                            }
                        } else {
                            this.showNotification('Error reconstruyendo índice: ' + result.error, 'error');
                        }
                    } catch (error) {
                        console.error('Error reconstruyendo índice:', error);
                        this.showNotification('Error reconstruyendo índice', 'error');
                    } finally {
                        this.isIndexing = false;
                    }
                },
                
                getTypeIcon(type) {
                    const icons = {
                        course: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
                        lesson: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
                        section: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>',
                        instructor: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                        topic: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>',
                        note: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                        comment: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>'
                    };
                    
                    return icons[type] || icons['topic'];
                },
                
                showNotification(message, type = 'info') {
                    // Implementar sistema de notificaciones
                    console.log(`${type.toUpperCase()}: ${message}`);
                    
                    // Aquí se puede integrar con un sistema de notificaciones
                    // como Toastify, SweetAlert2, o un sistema personalizado
                }
            }));
        }
    }
    
    /**
     * Enfocar la caja de búsqueda
     */
    focusSearchBox() {
        const searchBox = document.querySelector('.search-box-component input');
        if (searchBox) {
            searchBox.focus();
        }
    }
    
    /**
     * Realizar búsqueda programática
     */
    async search(query, filters = {}) {
        try {
            const params = new URLSearchParams();
            params.append('q', query);
            params.append('limit', '50');
            
            if (Object.keys(filters).length > 0) {
                params.append('filters', JSON.stringify(filters));
            }
            
            const response = await fetch(`/api/search?${params.toString()}`);
            const result = await response.json();
            
            if (result.success) {
                this.currentQuery = query;
                this.currentResults = result.data;
                this.searchMethod = result.method;
                return result;
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error en búsqueda:', error);
            throw error;
        }
    }
    
    /**
     * Obtener sugerencias
     */
    async getSuggestions(query, limit = 10) {
        try {
            const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}&limit=${limit}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error obteniendo sugerencias:', error);
            throw error;
        }
    }
    
    /**
     * Reconstruir índice de búsqueda
     */
    async rebuildIndex() {
        try {
            const response = await fetch('/api/search/rebuild-index', {
                method: 'POST'
            });
            
            const result = await response.json();
            
            if (result.success) {
                return result;
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error reconstruyendo índice:', error);
            throw error;
        }
    }
    
    /**
     * Obtener estadísticas de búsqueda
     */
    async getStats() {
        try {
            const response = await fetch('/api/search/stats');
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error obteniendo estadísticas:', error);
            throw error;
        }
    }
    
    /**
     * Limpiar estado
     */
    destroy() {
        this.currentQuery = '';
        this.currentResults = [];
        this.searchMethod = 'composite';
        this.isIndexing = false;
    }
}

// Función global para búsqueda
window.performGlobalSearch = function(query, filters = {}) {
    if (window.searchManager) {
        return window.searchManager.search(query, filters);
    }
};

// Crear instancia global
window.searchManager = new SearchManager();
