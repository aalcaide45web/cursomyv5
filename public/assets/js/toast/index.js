/**
 * Módulo de Notificaciones Toast - Sistema de notificaciones del sistema
 */
export class ToastManager {
    constructor() {
        this.toasts = new Map();
        this.container = null;
        this.init();
    }

    init() {
        this.createContainer();
        this.bindEvents();
    }

    createContainer() {
        // Crear contenedor para toasts si no existe
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'fixed top-4 right-4 z-50 space-y-3';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toast-container');
        }
    }

    bindEvents() {
        // Escuchar eventos de toast desde otros módulos
        window.addEventListener('showToast', (event) => {
            const { type, message, duration, dismissible } = event.detail;
            this.show(type, message, duration, dismissible);
        });
    }

    show(type = 'info', message = '', duration = 5000, dismissible = true) {
        const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        
        // Crear elemento toast
        const toast = this.createToastElement(toastId, type, message, duration, dismissible);
        
        // Agregar al contenedor
        this.container.appendChild(toast);
        
        // Animar entrada
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        });

        // Guardar referencia
        this.toasts.set(toastId, {
            element: toast,
            type,
            message,
            duration,
            dismissible,
            startTime: Date.now()
        });

        // Configurar auto-dismiss
        if (duration > 0) {
            this.setupAutoDismiss(toastId, duration);
        }

        // Configurar barra de progreso
        if (duration > 0) {
            this.setupProgressBar(toastId, duration);
        }

        return toastId;
    }

    createToastElement(toastId, type, message, duration, dismissible) {
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast-notification bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 shadow-2xl transform translate-x-full transition-all duration-300 ease-in-out max-w-sm w-full`;
        
        const typeClasses = this.getTypeClasses(type);
        const icon = this.getIcon(type);

        toast.innerHTML = `
            <div class="flex items-start space-x-3">
                <!-- Icono -->
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 ${typeClasses.icon}">
                        ${icon}
                    </div>
                </div>
                
                <!-- Mensaje -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white">
                        ${this.escapeHtml(message)}
                    </p>
                </div>
                
                <!-- Botón cerrar -->
                ${dismissible ? this.renderCloseButton(toastId) : ''}
            </div>
            
            <!-- Barra de progreso -->
            ${duration > 0 ? this.renderProgressBar(typeClasses.progress) : ''}
        `;

        return toast;
    }

    getTypeClasses(type) {
        const classes = {
            'success': {
                'bg': 'bg-green-500/20 border-green-400/30',
                'icon': 'text-green-400',
                'progress': 'bg-green-400'
            },
            'error': {
                'bg': 'bg-red-500/20 border-red-400/30',
                'icon': 'text-red-400',
                'progress': 'bg-red-400'
            },
            'warning': {
                'bg': 'bg-yellow-500/20 border-yellow-400/30',
                'icon': 'text-yellow-400',
                'progress': 'bg-yellow-400'
            },
            'info': {
                'bg': 'bg-blue-500/20 border-blue-400/30',
                'icon': 'text-blue-400',
                'progress': 'bg-blue-400'
            }
        };

        return classes[type] || classes['info'];
    }

    getIcon(type) {
        const icons = {
            'success': '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            'error': '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
            'warning': '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            'info': '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
        };

        return icons[type] || icons['info'];
    }

    renderCloseButton(toastId) {
        return `
            <button type="button" 
                    class="toast-close-btn flex-shrink-0 text-gray-400 hover:text-white transition-colors duration-200"
                    onclick="ToastManager.dismiss('${toastId}')">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        `;
    }

    renderProgressBar(progressColor) {
        return `
            <div class="mt-3 w-full bg-gray-700 rounded-full h-1">
                <div class="toast-progress-bar h-1 rounded-full transition-all duration-100 ease-linear ${progressColor}" style="width: 100%"></div>
            </div>
        `;
    }

    setupAutoDismiss(toastId, duration) {
        setTimeout(() => {
            this.dismiss(toastId);
        }, duration);
    }

    setupProgressBar(toastId, duration) {
        const toast = this.toasts.get(toastId);
        if (!toast) return;

        const progressBar = toast.element.querySelector('.toast-progress-bar');
        if (!progressBar) return;

        const startTime = Date.now();
        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.max(0, 100 - (elapsed / duration) * 100);
            
            progressBar.style.width = `${progress}%`;
            
            if (progress > 0 && this.toasts.has(toastId)) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    dismiss(toastId) {
        const toast = this.toasts.get(toastId);
        if (!toast) return;

        // Animar salida
        toast.element.classList.add('translate-x-full');
        toast.element.classList.remove('translate-x-0');

        // Remover después de la animación
        setTimeout(() => {
            if (toast.element.parentNode) {
                toast.element.parentNode.removeChild(toast.element);
            }
            this.toasts.delete(toastId);
        }, 300);
    }

    dismissAll() {
        this.toasts.forEach((toast, toastId) => {
            this.dismiss(toastId);
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Métodos estáticos para uso global
    static show(type, message, duration, dismissible) {
        if (!window.toastManager) {
            window.toastManager = new ToastManager();
        }
        return window.toastManager.show(type, message, duration, dismissible);
    }

    static success(message, duration = 5000) {
        return this.show('success', message, duration);
    }

    static error(message, duration = 7000) {
        return this.show('error', message, duration);
    }

    static warning(message, duration = 6000) {
        return this.show('warning', message, duration);
    }

    static info(message, duration = 5000) {
        return this.show('info', message, duration);
    }

    static dismiss(toastId) {
        if (window.toastManager) {
            window.toastManager.dismiss(toastId);
        }
    }

    static dismissAll() {
        if (window.toastManager) {
            window.toastManager.dismissAll();
        }
    }

    destroy() {
        this.dismissAll();
        if (this.container && this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
        this.toasts.clear();
    }
}

// Crear instancia global
window.toastManager = new ToastManager();

// Función global para mostrar toasts
window.showToast = function(type, message, duration, dismissible) {
    return ToastManager.show(type, message, duration, dismissible);
};
