<?php declare(strict_types=1);

/**
 * Componente StarRating - Sistema de estrellas 1-5 para valoraciones
 */
class StarRating {
    private float $rating;
    private int $count;
    private ?int $userRating;
    private int $courseId;
    private bool $interactive;
    private string $size;
    
    public function __construct(
        float $rating = 0.0, 
        int $count = 0, 
        ?int $userRating = null, 
        int $courseId = 0, 
        bool $interactive = false,
        string $size = 'md'
    ) {
        $this->rating = $rating;
        $this->count = $count;
        $this->userRating = $userRating;
        $this->courseId = $courseId;
        $this->interactive = $interactive;
        $this->size = $size;
    }
    
    public function render(): string {
        $sizeClasses = $this->getSizeClasses();
        $interactiveClass = $this->interactive ? 'cursor-pointer hover:scale-110 transition-transform' : '';
        $ratingDisplay = $this->formatRating();
        
        return <<<HTML
        <div class="star-rating-component" data-course-id="{$this->courseId}">
            <!-- Estrellas -->
            <div class="flex items-center space-x-1">
                {$this->renderStars()}
                {$this->renderRatingText()}
            </div>
            
            <!-- Contador de valoraciones -->
            {$this->renderRatingCount()}
            
            <!-- Distribución de valoraciones (solo si hay valoraciones) -->
            {$this->renderRatingDistribution()}
        </div>
        HTML;
    }
    
    private function renderStars(): string {
        $stars = '';
        $fullStars = intval($this->rating);
        $hasHalfStar = ($this->rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
        
        $sizeClasses = $this->getSizeClasses();
        $interactiveClass = $this->interactive ? 'cursor-pointer hover:scale-110 transition-transform' : '';
        
        // Estrellas llenas
        for ($i = 1; $i <= $fullStars; $i++) {
            $stars .= $this->renderStar($i, 'full', $sizeClasses, $interactiveClass);
        }
        
        // Media estrella
        if ($hasHalfStar) {
            $stars .= $this->renderStar($fullStars + 1, 'half', $sizeClasses, $interactiveClass);
        }
        
        // Estrellas vacías
        for ($i = $fullStars + ($hasHalfStar ? 2 : 1); $i <= 5; $i++) {
            $stars .= $this->renderStar($i, 'empty', $sizeClasses, $interactiveClass);
        }
        
        return $stars;
    }
    
    private function renderStar(int $position, string $type, string $sizeClasses, string $interactiveClass): string {
        $starClass = $this->getStarClass($type);
        $onClick = $this->interactive ? "onclick=\"rateCourse({$this->courseId}, {$position})\"" : '';
        
        return <<<HTML
        <div class="star-item {$interactiveClass}" data-rating="{$position}" {$onClick}>
            <svg class="{$sizeClasses} {$starClass}" fill="currentColor" viewBox="0 0 20 20">
                {$this->getStarPath($type)}
            </svg>
        </div>
        HTML;
    }
    
    private function getStarClass(string $type): string {
        switch ($type) {
            case 'full':
                return 'text-yellow-400';
            case 'half':
                return 'text-yellow-400';
            case 'empty':
                return 'text-gray-400';
            default:
                return 'text-gray-400';
        }
    }
    
    private function getStarPath(string $type): string {
        switch ($type) {
            case 'full':
                return '<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>';
            case 'half':
                return '<defs><linearGradient id="half-star-{$this->courseId}"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#9CA3AF"/></linearGradient></defs><path fill="url(#half-star-{$this->courseId})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>';
            case 'empty':
                return '<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>';
            default:
                return '<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>';
        }
    }
    
    private function renderRatingText(): string {
        if ($this->count === 0) {
            return '<span class="text-gray-500 text-sm ml-2">Sin valoraciones</span>';
        }
        
        $ratingText = number_format($this->rating, 1);
        return <<<HTML
        <span class="text-gray-300 text-sm ml-2 font-medium">{$ratingText}</span>
        HTML;
    }
    
    private function renderRatingCount(): string {
        if ($this->count === 0) {
            return '';
        }
        
        $countText = $this->count === 1 ? 'valoración' : 'valoraciones';
        return <<<HTML
        <div class="mt-1">
            <span class="text-gray-500 text-xs">{$this->count} {$countText}</span>
        </div>
        HTML;
    }
    
    private function renderRatingDistribution(): string {
        if ($this->count === 0) {
            return '';
        }
        
        // Obtener distribución (esto se cargará dinámicamente via JavaScript)
        return <<<HTML
        <div class="rating-distribution mt-3 hidden">
            <div class="text-xs text-gray-500 mb-2">Distribución de valoraciones:</div>
            <div class="space-y-1">
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-400 w-8">5★</span>
                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8">0</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-400 w-8">4★</span>
                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8">0</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-400 w-8">3★</span>
                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8">0</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-400 w-8">2★</span>
                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8">0</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-400 w-8">1★</span>
                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8">0</span>
                </div>
            </div>
        </div>
        HTML;
    }
    
    private function getSizeClasses(): string {
        switch ($this->size) {
            case 'sm':
                return 'w-4 h-4';
            case 'md':
                return 'w-5 h-5';
            case 'lg':
                return 'w-6 h-6';
            case 'xl':
                return 'w-8 h-8';
            default:
                return 'w-5 h-5';
        }
    }
    
    private function formatRating(): string {
        if ($this->count === 0) {
            return 'Sin valoraciones';
        }
        return number_format($this->rating, 1) . " ({$this->count})";
    }
    
    /**
     * Renderizar solo para mostrar (no interactivo)
     */
    public static function display(float $rating, int $count, string $size = 'md'): string {
        $component = new self($rating, $count, null, 0, false, $size);
        return $component->render();
    }
    
    /**
     * Renderizar interactivo para valorar
     */
    public static function interactive(float $rating, int $count, ?int $userRating, int $courseId, string $size = 'md'): string {
        $component = new self($rating, $count, $userRating, $courseId, true, $size);
        return $component->render();
    }
}
