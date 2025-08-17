/**
 * Módulo de Atajos de Teclado - Sistema de shortcuts para el LMS
 */
export class ShortcutManager {
    constructor() {
        this.shortcuts = new Map();
        this.enabled = true;
        this.init();
    }

    init() {
        this.registerDefaultShortcuts();
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener('keydown', (event) => {
            if (!this.enabled) return;
            
            // No procesar shortcuts si estamos en un input o textarea
            if (this.isInputElement(event.target)) return;
            
            this.handleKeydown(event);
        });

        // Deshabilitar shortcuts temporalmente cuando se presiona Ctrl
        document.addEventListener('keydown', (event) => {
            if (event.ctrlKey && !event.metaKey) {
                this.enabled = false;
            }
        });

        document.addEventListener('keyup', (event) => {
            if (event.key === 'Control') {
                this.enabled = true;
            }
        });
    }

    isInputElement(element) {
        const inputTypes = ['input', 'textarea', 'select'];
        return inputTypes.includes(element.tagName.toLowerCase()) || 
               element.contentEditable === 'true' ||
               element.classList.contains('editable');
    }

    handleKeydown(event) {
        const key = this.getKeyString(event);
        
        // Buscar shortcut exacto
        if (this.shortcuts.has(key)) {
            event.preventDefault();
            this.executeShortcut(key);
            return;
        }

        // Buscar shortcuts que coincidan parcialmente
        for (const [shortcut, handler] of this.shortcuts) {
            if (this.matchesShortcut(key, shortcut)) {
                event.preventDefault();
                this.executeShortcut(shortcut);
                return;
            }
        }
    }

    getKeyString(event) {
        const keys = [];
        
        if (event.ctrlKey) keys.push('Ctrl');
        if (event.altKey) keys.push('Alt');
        if (event.shiftKey) keys.push('Shift');
        if (event.metaKey) keys.push('Meta');
        
        if (event.key !== 'Control' && event.key !== 'Alt' && event.key !== 'Shift' && event.key !== 'Meta') {
            keys.push(event.key.toUpperCase());
        }
        
        return keys.join('+');
    }

    matchesShortcut(input, shortcut) {
        const inputParts = input.split('+').sort();
        const shortcutParts = shortcut.split('+').sort();
        
        if (inputParts.length !== shortcutParts.length) return false;
        
        return inputParts.every((part, index) => part === shortcutParts[index]);
    }

    register(key, description, handler, category = 'general') {
        this.shortcuts.set(key, {
            description,
            handler,
            category,
            key: key
        });
    }

    unregister(key) {
        this.shortcuts.delete(key);
    }

    executeShortcut(key) {
        const shortcut = this.shortcuts.get(key);
        if (!shortcut) return;

        try {
            if (typeof shortcut.handler === 'function') {
                shortcut.handler();
            } else if (typeof shortcut.handler === 'string') {
                // Ejecutar función global
                const globalFunction = window[shortcut.handler];
                if (typeof globalFunction === 'function') {
                    globalFunction();
                }
            }
        } catch (error) {
            console.error('Error ejecutando shortcut:', key, error);
        }
    }

    registerDefaultShortcuts() {
        // Navegación
        this.register('g h', 'Ir al Dashboard', () => {
            window.location.href = '/';
        }, 'navegación');

        this.register('g s', 'Ir a Búsqueda', () => {
            const searchBox = document.querySelector('.search-box-component input');
            if (searchBox) {
                searchBox.focus();
            }
        }, 'navegación');

        // Acciones del sistema
        this.register('Ctrl+r', 'Recargar página', () => {
            window.location.reload();
        }, 'sistema');

        this.register('Ctrl+Shift+R', 'Recargar página (hard refresh)', () => {
            window.location.reload(true);
        }, 'sistema');

        // Escaneo
        this.register('Ctrl+s', 'Escaneo incremental', () => {
            const scanBtn = document.getElementById('incremental-scan');
            if (scanBtn) {
                scanBtn.click();
            }
        }, 'escaneo');

        this.register('Ctrl+Shift+S', 'Escaneo completo', () => {
            const scanBtn = document.getElementById('rebuild-scan');
            if (scanBtn) {
                scanBtn.click();
            }
        }, 'escaneo');

        // Búsqueda
        this.register('Ctrl+k', 'Abrir búsqueda', () => {
            const searchBox = document.querySelector('.search-box-component input');
            if (searchBox) {
                searchBox.focus();
            }
        }, 'búsqueda');

        this.register('Escape', 'Cerrar búsqueda', () => {
            const searchBox = document.querySelector('.search-box-component input');
            if (searchBox && searchBox === document.activeElement) {
                searchBox.blur();
            }
        }, 'búsqueda');

        // Reproductor de video
        this.register('Space', 'Play/Pause video', () => {
            const video = document.querySelector('video');
            if (video) {
                if (video.paused) {
                    video.play();
                } else {
                    video.pause();
                }
            }
        }, 'reproductor');

        this.register('f', 'Pantalla completa', () => {
            const video = document.querySelector('video');
            if (video) {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    video.requestFullscreen();
                }
            }
        }, 'reproductor');

        this.register('m', 'Silenciar/Desilenciar', () => {
            const video = document.querySelector('video');
            if (video) {
                video.muted = !video.muted;
            }
        }, 'reproductor');

        this.register('ArrowLeft', 'Retroceder 10s', () => {
            const video = document.querySelector('video');
            if (video) {
                video.currentTime = Math.max(0, video.currentTime - 10);
            }
        }, 'reproductor');

        this.register('ArrowRight', 'Avanzar 10s', () => {
            const video = document.querySelector('video');
            if (video) {
                video.currentTime = Math.min(video.duration, video.currentTime + 10);
            }
        }, 'reproductor');

        // Navegación por lecciones
        this.register('n', 'Siguiente lección', () => {
            const nextBtn = document.querySelector('[data-action="next-lesson"]');
            if (nextBtn) {
                nextBtn.click();
            }
        }, 'navegación');

        this.register('p', 'Lección anterior', () => {
            const prevBtn = document.querySelector('[data-action="prev-lesson"]');
            if (prevBtn) {
                prevBtn.click();
            }
        }, 'navegación');

        // Notas y comentarios
        this.register('Ctrl+n', 'Nueva nota', () => {
            const addNoteBtn = document.getElementById('add-note-btn');
            if (addNoteBtn) {
                addNoteBtn.click();
            }
        }, 'notas');

        this.register('Ctrl+c', 'Nuevo comentario', () => {
            const addCommentBtn = document.getElementById('add-comment-btn');
            if (addCommentBtn) {
                addCommentBtn.click();
            }
        }, 'comentarios');

        // Ayuda
        this.register('?', 'Mostrar ayuda de shortcuts', () => {
            this.showHelp();
        }, 'ayuda');
    }

    showHelp() {
        const helpModal = this.createHelpModal();
        document.body.appendChild(helpModal);
        
        // Mostrar modal
        requestAnimationFrame(() => {
            helpModal.classList.remove('opacity-0', 'scale-95');
            helpModal.classList.add('opacity-100', 'scale-100');
        });
    }

    createHelpModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm opacity-0 scale-95 transition-all duration-300';
        
        const shortcutsByCategory = this.groupShortcutsByCategory();
        
        modal.innerHTML = `
            <div class="bg-gray-900/95 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-white/10">
                    <h2 class="text-xl font-semibold text-white">Atajos de Teclado</h2>
                    <button type="button" 
                            class="text-gray-400 hover:text-white transition-colors duration-200"
                            onclick="this.closest('.fixed').remove()">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    ${this.renderShortcutsByCategory(shortcutsByCategory)}
                </div>
                
                <div class="p-6 border-t border-white/10 text-center">
                    <p class="text-sm text-gray-400">
                        Presiona <kbd class="px-2 py-1 bg-gray-800 text-gray-300 rounded text-xs">?</kbd> en cualquier momento para mostrar esta ayuda
                    </p>
                </div>
            </div>
        `;
        
        return modal;
    }

    groupShortcutsByCategory() {
        const grouped = {};
        
        for (const [key, shortcut] of this.shortcuts) {
            const category = shortcut.category;
            if (!grouped[category]) {
                grouped[category] = [];
            }
            grouped[category].push(shortcut);
        }
        
        return grouped;
    }

    renderShortcutsByCategory(grouped) {
        let html = '';
        
        for (const [category, shortcuts] of Object.entries(grouped)) {
            html += `
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-white mb-3 capitalize">${category}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        ${shortcuts.map(shortcut => `
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                                <span class="text-gray-300">${shortcut.description}</span>
                                <kbd class="px-2 py-1 bg-gray-800 text-gray-300 rounded text-sm font-mono">
                                    ${shortcut.key}
                                </kbd>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
        
        return html;
    }

    enable() {
        this.enabled = true;
    }

    disable() {
        this.enabled = false;
    }

    getShortcuts() {
        return Array.from(this.shortcuts.values());
    }

    destroy() {
        this.shortcuts.clear();
        this.enabled = false;
    }
}

// Crear instancia global
window.shortcutManager = new ShortcutManager();

// Función global para registrar shortcuts personalizados
window.registerShortcut = function(key, description, handler, category) {
    return window.shortcutManager.register(key, description, handler, category);
};
