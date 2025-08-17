<?php declare(strict_types=1);

/**
 * Componente ThemeToggle - Cambio entre tema claro y oscuro
 */
class ThemeToggle {
    private string $id;
    private string $currentTheme;

    public function __construct(string $currentTheme = 'dark') {
        $this->id = 'theme-toggle-' . uniqid();
        $this->currentTheme = $currentTheme;
    }

    public function render(): string {
        $isDark = $this->currentTheme === 'dark';
        
        return <<<HTML
        <div class="theme-toggle-component" x-data="themeToggle()">
            <button type="button" 
                    id="{$this->id}"
                    class="theme-toggle-btn relative inline-flex h-10 w-20 items-center rounded-full bg-gray-700 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                    :class="isDark ? 'bg-blue-600' : 'bg-gray-300'"
                    @click="toggleTheme()"
                    :aria-label="isDark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'">
                
                <!-- Sol (tema claro) -->
                <svg class="absolute left-1 h-8 w-8 text-yellow-500 transition-all duration-300"
                     :class="isDark ? 'opacity-0 scale-75 rotate-90' : 'opacity-100 scale-100 rotate-0'"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                </svg>
                
                <!-- Luna (tema oscuro) -->
                <svg class="absolute right-1 h-8 w-8 text-blue-400 transition-all duration-300"
                     :class="isDark ? 'opacity-100 scale-100 rotate-0' : 'opacity-0 scale-75 -rotate-90'"
                     fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                </svg>
                
                <!-- Círculo deslizante -->
                <span class="inline-block h-6 w-6 transform rounded-full bg-white shadow-lg transition-all duration-300"
                      :class="isDark ? 'translate-x-10' : 'translate-x-1'"></span>
            </button>
            
            <!-- Indicador de tema actual -->
            <div class="mt-2 text-center">
                <span class="text-xs text-gray-400" x-text="isDark ? 'Tema Oscuro' : 'Tema Claro'"></span>
            </div>
        </div>
        HTML;
    }

    public static function display(): string {
        return (new self())->render();
    }
}
