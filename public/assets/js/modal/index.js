/**
 * Módulo de Modales de Confirmación - Sistema de confirmaciones para acciones críticas
 */
export class ConfirmModalManager {
    constructor() {
        this.activeModal = null;
        this.confirmCallbacks = new Map();
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Escuchar eventos de teclado
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.activeModal) {
                this.close(this.activeModal);
            }
        });

        // Escuchar clics en overlay
        document.addEventListener('click', (event) => {
            if (event.target.classList.contains('confirm-modal')) {
                this.close(this.activeModal);
            }
        });
    }

    show(modalId, confirmCallback = null) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error('Modal no encontrado:', modalId);
            return false;
        }

        // Cerrar modal activo si existe
        if (this.activeModal) {
            this.close(this.activeModal);
        }

        // Mostrar modal
        modal.classList.remove('hidden');
        
        // Animar entrada
        requestAnimationFrame(() => {
            const modalContent = modal.querySelector('.bg-gray-900\\/95');
            if (modalContent) {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }
        });

        // Guardar referencia
        this.activeModal = modalId;
        
        // Guardar callback si existe
        if (confirmCallback) {
            this.confirmCallbacks.set(modalId, confirmCallback);
        }

        // Bloquear scroll del body
        document.body.style.overflow = 'hidden';

        return true;
    }

    close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Animar salida
        const modalContent = modal.querySelector('.bg-gray-900\\/95');
        if (modalContent) {
            modalContent.classList.add('scale-95', 'opacity-0');
            modalContent.classList.remove('scale-100', 'opacity-100');
        }

        // Ocultar después de la animación
        setTimeout(() => {
            modal.classList.add('hidden');
            
            // Limpiar estado
            if (this.activeModal === modalId) {
                this.activeModal = null;
            }
            
            // Remover callback
            this.confirmCallbacks.delete(modalId);
            
            // Restaurar scroll del body
            document.body.style.overflow = '';
        }, 300);
    }

    confirm(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Ejecutar callback si existe
        const callback = this.confirmCallbacks.get(modalId);
        if (callback && typeof callback === 'function') {
            try {
                callback();
            } catch (error) {
                console.error('Error en callback de confirmación:', error);
            }
        }

        // Cerrar modal
        this.close(modalId);
    }

    // Métodos estáticos para uso global
    static show(modalId, confirmCallback) {
        if (!window.confirmModalManager) {
            window.confirmModalManager = new ConfirmModalManager();
        }
        return window.confirmModalManager.show(modalId, confirmCallback);
    }

    static close(modalId) {
        if (window.confirmModalManager) {
            window.confirmModalManager.close(modalId);
        }
    }

    static confirm(modalId) {
        if (window.confirmModalManager) {
            window.confirmModalManager.confirm(modalId);
        }
    }

    // Métodos de conveniencia para acciones comunes
    static confirmDelete(itemName, onConfirm) {
        const modalId = this.createDeleteModal(itemName);
        this.show(modalId, onConfirm);
    }

    static confirmRename(itemName, onConfirm) {
        const modalId = this.createRenameModal(itemName);
        this.show(modalId, onConfirm);
    }

    static confirmReactivate(itemName, onConfirm) {
        const modalId = this.createReactivateModal(itemName);
        this.show(modalId, onConfirm);
    }

    // Crear modales dinámicamente
    static createDeleteModal(itemName) {
        const modalId = 'confirm-delete-' + Date.now();
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'confirm-modal fixed inset-0 z-50';
        modal.innerHTML = `
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300"></div>
            
            <!-- Modal -->
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-gray-900/95 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300">
                    
                    <!-- Header -->
                    <div class="flex items-center space-x-3 p-6 border-b border-white/10">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 text-red-400">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Confirmar Eliminación</h3>
                        </div>
                        <button type="button" 
                                class="modal-close-btn text-gray-400 hover:text-white transition-colors duration-200"
                                onclick="ConfirmModalManager.close('${modalId}')">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <p class="text-gray-300 leading-relaxed">
                            ¿Estás seguro de que quieres eliminar <strong>${itemName}</strong>? Esta acción no se puede deshacer.
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 p-6 border-t border-white/10">
                        <button type="button" 
                                class="modal-cancel-btn px-4 py-2 text-gray-300 hover:text-white border border-gray-600 hover:border-gray-500 rounded-lg transition-all duration-200"
                                onclick="ConfirmModalManager.close('${modalId}')">
                            Cancelar
                        </button>
                        <button type="button" 
                                class="modal-confirm-btn px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-all duration-200"
                                onclick="ConfirmModalManager.confirm('${modalId}')">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        return modalId;
    }

    static createRenameModal(itemName) {
        const modalId = 'confirm-rename-' + Date.now();
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'confirm-modal fixed inset-0 z-50';
        modal.innerHTML = `
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300"></div>
            
            <!-- Modal -->
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-gray-900/95 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300">
                    
                    <!-- Header -->
                    <div class="flex items-center space-x-3 p-6 border-b border-white/10">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 text-yellow-400">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Confirmar Renombrado</h3>
                        </div>
                        <button type="button" 
                                class="modal-close-btn text-gray-400 hover:text-white transition-colors duration-200"
                                onclick="ConfirmModalManager.close('${modalId}')">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <p class="text-gray-300 leading-relaxed">
                            ¿Estás seguro de que quieres renombrar <strong>${itemName}</strong>?
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 p-6 border-t border-white/10">
                        <button type="button" 
                                class="modal-cancel-btn px-4 py-2 text-gray-300 hover:text-white border border-gray-600 hover:border-gray-500 rounded-lg transition-all duration-200"
                                onclick="ConfirmModalManager.close('${modalId}')">
                            Cancelar
                        </button>
                        <button type="button" 
                                class="modal-confirm-btn px-4 py-2 text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-all duration-200"
                                onclick="ConfirmModalManager.confirm('${modalId}')">
                            Renombrar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        return modalId;
    }

    static createReactivateModal(itemName) {
        const modalId = 'confirm-reactivate-' + Date.now();
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'confirm-modal fixed inset-0 z-50';
        modal.innerHTML = `
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300"></div>
            
            <!-- Modal -->
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-gray-900/95 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300">
                    
                    <!-- Header -->
                    <div class="flex items-center space-x-3 p-6 border-b border-white/10">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 text-green-400">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Confirmar Reactivación</h3>
                        </div>
                        <button type="button" 
                                class="modal-close-btn text-gray-400 hover:text-white transition-colors duration-200"
                                onclick="ConfirmModalManager.close('${modalId}')">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <p class="text-gray-300 leading-relaxed">
                            ¿Estás seguro de que quieres reactivar <strong>${itemName}</strong>?
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 p-6 border-t border-white/10">
                        <button type="button" 
                                class="modal-cancel-btn px-4 py-2 text-gray-300 hover:text-white border border-gray-600 hover:border-gray-500 rounded-lg transition-all duration-200"
                                onclick="ConfirmModalManager.close('${modalId}')">
                            Cancelar
                        </button>
                        <button type="button" 
                                class="modal-confirm-btn px-4 py-2 text-white bg-green-600 hover:bg-green-700 rounded-lg transition-all duration-200"
                                onclick="ConfirmModalManager.confirm('${modalId}')">
                            Reactivar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        return modalId;
    }

    destroy() {
        if (this.activeModal) {
            this.close(this.activeModal);
        }
        this.confirmCallbacks.clear();
    }
}

// Crear instancia global
window.confirmModalManager = new ConfirmModalManager();

// Funciones globales para uso directo
window.confirmDelete = function(itemName, onConfirm) {
    return ConfirmModalManager.confirmDelete(itemName, onConfirm);
};

window.confirmRename = function(itemName, onConfirm) {
    return ConfirmModalManager.confirmRename(itemName, onConfirm);
};

window.confirmReactivate = function(itemName, onConfirm) {
    return ConfirmModalManager.confirmReactivate(itemName, onConfirm);
};
