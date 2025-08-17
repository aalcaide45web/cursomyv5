<?php declare(strict_types=1);

/**
 * Componente Toast - Sistema de notificaciones del sistema
 */
class Toast {
    private string $id;
    private string $type;
    private string $message;
    private int $duration;
    private bool $dismissible;

    public function __construct(string $message, string $type = 'info', int $duration = 5000, bool $dismissible = true) {
        $this->id = 'toast-' . uniqid();
        $this->type = $type;
        $this->message = $message;
        $this->duration = $duration;
        $this->dismissible = $dismissible;
    }

    public function render(): string {
        $typeClasses = $this->getTypeClasses();
        $icon = $this->getIcon();
        
        return <<<HTML
        <div id="{$this->id}" 
             class="toast-notification fixed top-4 right-4 z-50 max-w-sm w-full bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 shadow-2xl transform translate-x-full transition-all duration-300 ease-in-out"
             data-duration="{$this->duration}"
             data-dismissible="{$this->getDismissibleValue()}">
            
            <div class="flex items-start space-x-3">
                <!-- Icono -->
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 {$typeClasses['icon']}">
                        {$icon}
                    </div>
                </div>
                
                <!-- Mensaje -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white">
                        {$this->escape($this->message)}
                    </p>
                </div>
                
                <!-- Botón cerrar -->
                {$this->renderCloseButton()}
            </div>
            
            <!-- Barra de progreso -->
            {$this->renderProgressBar()}
        </div>
        HTML;
    }

    private function getTypeClasses(): array {
        $classes = [
            'success' => [
                'bg' => 'bg-green-500/20 border-green-400/30',
                'icon' => 'text-green-400',
                'progress' => 'bg-green-400'
            ],
            'error' => [
                'bg' => 'bg-red-500/20 border-red-400/30',
                'icon' => 'text-red-400',
                'progress' => 'bg-red-400'
            ],
            'warning' => [
                'bg' => 'bg-yellow-500/20 border-yellow-400/30',
                'icon' => 'text-yellow-400',
                'progress' => 'bg-yellow-400'
            ],
            'info' => [
                'bg' => 'bg-blue-500/20 border-blue-400/30',
                'icon' => 'text-blue-400',
                'progress' => 'bg-blue-400'
            ]
        ];

        return $classes[$this->type] ?? $classes['info'];
    }

    private function getIcon(): string {
        $icons = [
            'success' => '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            'error' => '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
            'warning' => '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            'info' => '<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
        ];

        return $icons[$this->type] ?? $icons['info'];
    }

    private function renderCloseButton(): string {
        if (!$this->dismissible) {
            return '';
        }

        return <<<HTML
        <button type="button" 
                class="toast-close-btn flex-shrink-0 text-gray-400 hover:text-white transition-colors duration-200"
                onclick="ToastManager.dismiss('{$this->id}')">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
        HTML;
    }

    private function renderProgressBar(): string {
        if ($this->duration <= 0) {
            return '';
        }

        return <<<HTML
        <div class="mt-3 w-full bg-gray-700 rounded-full h-1">
            <div class="toast-progress-bar h-1 rounded-full transition-all duration-100 ease-linear {$this->getTypeClasses()['progress']}" style="width: 100%"></div>
        </div>
        HTML;
    }

    private function escape(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    private function getDismissibleValue(): string {
        return $this->dismissible ? 'true' : 'false';
    }

    public static function success(string $message, int $duration = 5000): string {
        return (new self($message, 'success', $duration))->render();
    }

    public static function error(string $message, int $duration = 7000): string {
        return (new self($message, 'error', $duration))->render();
    }

    public static function warning(string $message, int $duration = 6000): string {
        return (new self($message, 'warning', $duration))->render();
    }

    public static function info(string $message, int $duration = 5000): string {
        return (new self($message, 'info', $duration))->render();
    }
}
