/**
 * Módulo de Temas - Sistema de cambio entre tema claro y oscuro
 */
export class ThemeManager {
    constructor() {
        this.currentTheme = 'dark';
        this.themes = {
            light: {
                name: 'light',
                label: 'Tema Claro',
                classes: {
                    body: 'bg-gray-50 text-gray-900',
                    container: 'bg-white',
                    card: 'bg-white border border-gray-200 shadow-sm',
                    text: 'text-gray-900',
                    textSecondary: 'text-gray-600',
                    border: 'border-gray-200',
                    input: 'bg-white border-gray-300 text-gray-900',
                    button: 'bg-blue-600 hover:bg-blue-700 text-white',
                    buttonSecondary: 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                }
            },
            dark: {
                name: 'dark',
                label: 'Tema Oscuro',
                classes: {
                    body: 'bg-gray-900 text-white',
                    container: 'bg-gray-800',
                    card: 'bg-gray-800 border border-gray-700',
                    text: 'text-white',
                    textSecondary: 'text-gray-300',
                    border: 'border-gray-700',
                    input: 'bg-gray-700 border-gray-600 text-white',
                    button: 'bg-blue-600 hover:bg-blue-700 text-white',
                    buttonSecondary: 'bg-gray-600 hover:bg-gray-700 text-white'
                }
            }
        };
        
        this.init();
    }

    init() {
        this.loadTheme();
        this.applyTheme();
        this.bindEvents();
        this.initializeAlpine();
    }

    bindEvents() {
        // Escuchar cambios de tema desde otros componentes
        window.addEventListener('themeChanged', (event) => {
            const { theme } = event.detail;
            this.setTheme(theme);
        });

        // Escuchar cambios en localStorage
        window.addEventListener('storage', (event) => {
            if (event.key === 'cursomy-theme') {
                this.setTheme(event.newValue || 'dark');
            }
        });
    }

    initializeAlpine() {
        // Hacer disponible para Alpine.js
        window.themeToggle = () => ({
            isDark: this.currentTheme === 'dark',
            
            toggleTheme() {
                const newTheme = this.isDark ? 'light' : 'dark';
                window.themeManager.setTheme(newTheme);
                this.isDark = newTheme === 'dark';
            }
        });
    }

    loadTheme() {
        // Cargar tema desde localStorage
        const savedTheme = localStorage.getItem('cursomy-theme');
        if (savedTheme && this.themes[savedTheme]) {
            this.currentTheme = savedTheme;
        } else {
            // Detectar preferencia del sistema
            this.currentTheme = this.detectSystemTheme();
        }
    }

    detectSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    setTheme(themeName) {
        if (!this.themes[themeName]) {
            console.error('Tema no válido:', themeName);
            return;
        }

        this.currentTheme = themeName;
        localStorage.setItem('cursomy-theme', themeName);
        
        this.applyTheme();
        this.dispatchThemeChange();
    }

    applyTheme() {
        const theme = this.themes[this.currentTheme];
        const root = document.documentElement;
        
        // Aplicar clases CSS al root
        root.className = root.className.replace(/theme-\w+/g, '');
        root.classList.add(`theme-${theme.name}`);
        
        // Aplicar variables CSS personalizadas
        this.applyCSSVariables(theme);
        
        // Actualizar meta theme-color
        this.updateMetaThemeColor(theme);
        
        // Aplicar tema a elementos específicos
        this.applyThemeToElements(theme);
    }

    applyCSSVariables(theme) {
        const root = document.documentElement;
        
        // Variables para colores principales
        root.style.setProperty('--color-primary', theme.name === 'dark' ? '#3B82F6' : '#2563EB');
        root.style.setProperty('--color-secondary', theme.name === 'dark' ? '#6B7280' : '#4B5563');
        root.style.setProperty('--color-background', theme.name === 'dark' ? '#111827' : '#F9FAFB');
        root.style.setProperty('--color-surface', theme.name === 'dark' ? '#1F2937' : '#FFFFFF');
        root.style.setProperty('--color-text', theme.name === 'dark' ? '#FFFFFF' : '#111827');
        root.style.setProperty('--color-text-secondary', theme.name === 'dark' ? '#D1D5DB' : '#6B7280');
        root.style.setProperty('--color-border', theme.name === 'dark' ? '#374151' : '#E5E7EB');
        
        // Variables para glassmorphism
        if (theme.name === 'dark') {
            root.style.setProperty('--glass-bg', 'rgba(31, 41, 55, 0.8)');
            root.style.setProperty('--glass-border', 'rgba(255, 255, 255, 0.1)');
            root.style.setProperty('--glass-shadow', '0 8px 32px rgba(0, 0, 0, 0.3)');
        } else {
            root.style.setProperty('--glass-bg', 'rgba(255, 255, 255, 0.8)');
            root.style.setProperty('--glass-border', 'rgba(0, 0, 0, 0.1)');
            root.style.setProperty('--glass-shadow', '0 8px 32px rgba(0, 0, 0, 0.1)');
        }
    }

    updateMetaThemeColor(theme) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        
        metaThemeColor.content = theme.name === 'dark' ? '#111827' : '#FFFFFF';
    }

    applyThemeToElements(theme) {
        // Aplicar tema a elementos con data-theme
        const themedElements = document.querySelectorAll('[data-theme]');
        themedElements.forEach(element => {
            this.applyThemeToElement(element, theme);
        });

        // Aplicar tema a componentes específicos
        this.updateComponentThemes(theme);
    }

    applyThemeToElement(element, theme) {
        const themeType = element.dataset.theme;
        const themeClasses = this.getThemeClasses(themeType, theme);
        
        if (themeClasses) {
            // Remover clases de tema anterior
            Object.values(this.themes).forEach(t => {
                if (t.classes[themeType]) {
                    element.classList.remove(...t.classes[themeType].split(' '));
                }
            });
            
            // Aplicar nuevas clases
            element.classList.add(...themeClasses.split(' '));
        }
    }

    getThemeClasses(type, theme) {
        return theme.classes[type] || null;
    }

    updateComponentThemes(theme) {
        // Actualizar componentes específicos
        this.updateSearchBoxTheme(theme);
        this.updateCourseCardsTheme(theme);
        this.updateVideoPlayerTheme(theme);
    }

    updateSearchBoxTheme(theme) {
        const searchBox = document.querySelector('.search-box-component');
        if (searchBox) {
            const input = searchBox.querySelector('input');
            if (input) {
                input.className = input.className.replace(/bg-\w+-\d+/, theme.name === 'dark' ? 'bg-gray-700' : 'bg-white');
                input.className = input.className.replace(/border-\w+-\d+/, theme.name === 'dark' ? 'border-gray-600' : 'border-gray-300');
                input.className = input.className.replace(/text-\w+-\d+/, theme.name === 'dark' ? 'text-white' : 'text-gray-900');
            }
        }
    }

    updateCourseCardsTheme(theme) {
        const courseCards = document.querySelectorAll('.course-card');
        courseCards.forEach(card => {
            card.className = card.className.replace(/bg-\w+-\d+/, theme.name === 'dark' ? 'bg-gray-800' : 'bg-white');
            card.className = card.className.replace(/border-\w+-\d+/, theme.name === 'dark' ? 'border-gray-700' : 'border-gray-200');
        });
    }

    updateVideoPlayerTheme(theme) {
        const videoPlayer = document.querySelector('.video-player-container');
        if (videoPlayer) {
            const controls = videoPlayer.querySelector('.custom-controls');
            if (controls) {
                controls.className = controls.className.replace(/bg-\w+-\d+/, theme.name === 'dark' ? 'bg-gray-900' : 'bg-gray-100');
            }
        }
    }

    dispatchThemeChange() {
        // Disparar evento personalizado
        const event = new CustomEvent('themeChanged', {
            detail: { theme: this.currentTheme }
        });
        window.dispatchEvent(event);
    }

    getCurrentTheme() {
        return this.currentTheme;
    }

    getThemeInfo(themeName) {
        return this.themes[themeName] || null;
    }

    getAllThemes() {
        return Object.keys(this.themes);
    }

    // Métodos estáticos para uso global
    static setTheme(themeName) {
        if (!window.themeManager) {
            window.themeManager = new ThemeManager();
        }
        return window.themeManager.setTheme(themeName);
    }

    static getCurrentTheme() {
        if (!window.themeManager) {
            window.themeManager = new ThemeManager();
        }
        return window.themeManager.getCurrentTheme();
    }

    static toggleTheme() {
        if (!window.themeManager) {
            window.themeManager = new ThemeManager();
        }
        const current = window.themeManager.getCurrentTheme();
        const newTheme = current === 'dark' ? 'light' : 'dark';
        return window.themeManager.setTheme(newTheme);
    }

    destroy() {
        // Limpiar event listeners
        window.removeEventListener('themeChanged', this.handleThemeChange);
        window.removeEventListener('storage', this.handleStorageChange);
    }
}

// Crear instancia global
window.themeManager = new ThemeManager();

// Funciones globales para uso directo
window.setTheme = function(themeName) {
    return ThemeManager.setTheme(themeName);
};

window.toggleTheme = function() {
    return ThemeManager.toggleTheme();
};

window.getCurrentTheme = function() {
    return ThemeManager.getCurrentTheme();
};
