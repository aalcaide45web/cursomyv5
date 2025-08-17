<?php declare(strict_types=1);

/**
 * Componente ConfirmModal - Modal de confirmación para acciones críticas
 */
class ConfirmModal {
    private string $id;
    private string $title;
    private string $message;
    private string $confirmText;
    private string $cancelText;
    private string $type;
    private string $confirmAction;

    public function __construct(
        string $title = 'Confirmar Acción',
        string $message = '¿Estás seguro de que quieres realizar esta acción?',
        string $confirmText = 'Confirmar',
        string $cancelText = 'Cancelar',
        string $type = 'warning',
        string $confirmAction = ''
    ) {
        $this->id = 'confirm-modal-' . uniqid();
        $this->title = $title;
        $this->message = $message;
        $this->confirmText = $confirmText;
        $this->cancelText = $cancelText;
        $this->type = $type;
        $this->confirmAction = $confirmAction;
    }

    public function render(): string {
        $typeClasses = $this->getTypeClasses();
        $icon = $this->getIcon();
        
        return <<<HTML
        <div id="{$this->id}" 
             class="confirm-modal fixed inset-0 z-50 hidden"
             data-confirm-action="{$this->escape($this->confirmAction)}">
            
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300"></div>
            
            <!-- Modal -->
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-gray-900/95 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300">
                    
                    <!-- Header -->
                    <div class="flex items-center space-x-3 p-6 border-b border-white/10">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 {$typeClasses['icon']}">
                                {$icon}
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">
                                {$this->escape($this->title)}
                            </h3>
                        </div>
                        <button type="button" 
                                class="modal-close-btn text-gray-400 hover:text-white transition-colors duration-200"
                                onclick="ConfirmModalManager.close('{$this->id}')">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <p class="text-gray-300 leading-relaxed">
                            {$this->escape($this->message)}
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 p-6 border-t border-white/10">
                        <button type="button" 
                                class="modal-cancel-btn px-4 py-2 text-gray-300 hover:text-white border border-gray-600 hover:border-gray-500 rounded-lg transition-all duration-200"
                                onclick="ConfirmModalManager.close('{$this->id}')">
                            {$this->escape($this->cancelText)}
                        </button>
                        <button type="button" 
                                class="modal-confirm-btn px-4 py-2 text-white {$typeClasses['button']} rounded-lg transition-all duration-200"
                                onclick="ConfirmModalManager.confirm('{$this->id}')">
                            {$this->escape($this->confirmText)}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    private function getTypeClasses(): array {
        $classes = [
            'success' => [
                'icon' => 'text-green-400',
                'button' => 'bg-green-600 hover:bg-green-700'
            ],
            'error' => [
                'icon' => 'text-red-400',
                'button' => 'bg-red-600 hover:bg-red-700'
            ],
            'warning' => [
                'icon' => 'text-yellow-400',
                'button' => 'bg-yellow-600 hover:bg-yellow-700'
            ],
            'info' => [
                'icon' => 'text-blue-400',
                'button' => 'bg-blue-600 hover:bg-blue-700'
            ]
        ];

        return $classes[$this->type] ?? $classes['warning'];
    }

    private function getIcon(): string {
        $icons = [
            'success' => '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            'error' => '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
            'warning' => '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            'info' => '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
        ];

        return $icons[$this->type] ?? $icons['warning'];
    }

    private function escape(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function delete(string $itemName = 'este elemento'): string {
        return (new self(
            'Confirmar Eliminación',
            "¿Estás seguro de que quieres eliminar {$itemName}? Esta acción no se puede deshacer.",
            'Eliminar',
            'Cancelar',
            'error',
            'delete'
        ))->render();
    }

    public static function rename(string $itemName = 'este elemento'): string {
        return (new self(
            'Confirmar Renombrado',
            "¿Estás seguro de que quieres renombrar {$itemName}?",
            'Renombrar',
            'Cancelar',
            'warning',
            'rename'
        ))->render();
    }

    public static function reactivate(string $itemName = 'este elemento'): string {
        return (new self(
            'Confirmar Reactivación',
            "¿Estás seguro de que quieres reactivar {$itemName}?",
            'Reactivar',
            'Cancelar',
            'success',
            'reactivate'
        ))->render();
    }
}
