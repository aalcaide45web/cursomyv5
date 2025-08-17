<?php
// La vista del curso recibe $course, $sections y $lessons desde el controlador

// Funciones de utilidad para la vista
function formatDuration(int $seconds): string {
    if ($seconds === 0) {
        return '--:--';
    }
    
    $hours = intval($seconds / 3600);
    $minutes = intval(($seconds % 3600) / 60);
    
    if ($hours > 0) {
        return sprintf('%d:%02d', $hours, $minutes);
    }
    return sprintf('%d:%02d', $minutes, $seconds % 60);
}

function formatRating(float $avgRating, int $count): string {
    if ($count === 0) {
        return 'Sin valoraciones';
    }
    return number_format($avgRating, 1) . " ({$count})";
}

function renderStars(float $rating): string {
    $fullStars = intval($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    
    $stars = '';
    
    // Estrellas llenas
    for ($i = 0; $i < $fullStars; $i++) {
        $stars .= '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
    }
    
    // Media estrella
    if ($hasHalfStar) {
        $stars .= '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
    }
    
    // Estrellas vacías
    for ($i = 0; $i < $emptyStars; $i++) {
        $stars .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674c.3-.922-.755-1.688-1.538-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81H4.82c.969 0 1.371 1.24.588 1.81l3.976 2.888z"/></svg>';
    }
    
    return $stars;
}
?>

<div class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900">
    <!-- Header del Curso -->
    <div class="mb-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/" class="inline-flex items-center text-sm font-medium text-gray-300 hover:text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-300 md:ml-2"><?= htmlspecialchars($course['name']) ?></span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Información del Curso -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white mb-2"><?= htmlspecialchars($course['name']) ?></h1>
                    <div class="flex flex-wrap items-center gap-4 text-gray-300">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                            </svg>
                            <span><?= htmlspecialchars($course['instructor_name'] ?? 'Sin instructor') ?></span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                            </svg>
                            <span><?= htmlspecialchars($course['topic_name'] ?? 'Sin tema') ?></span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span><?= $this->formatDuration($course['total_duration'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Rating del Curso -->
                <div class="mt-4 lg:mt-0 lg:ml-6">
                    <div class="flex items-center">
                        <div class="flex text-yellow-400">
                            <?= $this->renderStars($course['avg_rating'] ?? 0) ?>
                        </div>
                        <span class="ml-2 text-sm text-gray-300">
                            <?= $this->formatRating($course['avg_rating'] ?? 0, $course['ratings_count'] ?? 0) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido del Curso -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar con Secciones -->
        <div class="lg:col-span-1">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 sticky top-4">
                <h3 class="text-lg font-semibold text-white mb-4">Secciones</h3>
                <nav class="space-y-2">
                    <?php foreach ($sections as $section): ?>
                        <div class="section-item">
                            <button onclick="toggleSection(<?= $section['id'] ?>)" 
                                    class="w-full text-left p-3 rounded-lg hover:bg-white/10 transition-colors group">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400 group-hover:text-white transition-colors" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                        </svg>
                                        <span class="text-gray-300 group-hover:text-white transition-colors">
                                            <?= htmlspecialchars($section['name']) ?>
                                        </span>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors section-arrow" 
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </button>
                            
                            <!-- Lista de Lecciones de la Sección -->
                            <div id="section-<?= $section['id'] ?>" class="section-lessons hidden ml-6 mt-2 space-y-1">
                                <?php foreach ($lessons as $lesson): ?>
                                    <?php if ($lesson['section_id'] == $section['id']): ?>
                                        <div class="lesson-item">
                                            <button onclick="selectLesson(<?= $lesson['id'] ?>, '<?= htmlspecialchars($lesson['name']) ?>')" 
                                                    class="w-full text-left p-2 rounded text-sm hover:bg-white/10 transition-colors group">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <svg class="w-3 h-3 mr-2 text-gray-500 group-hover:text-gray-300 transition-colors" 
                                                             fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                                                        </svg>
                                                        <span class="text-gray-400 group-hover:text-gray-200 transition-colors">
                                                            <?= htmlspecialchars($lesson['name']) ?>
                                                        </span>
                                                    </div>
                                                    <span class="text-xs text-gray-500">
                                                        <?= $this->formatDuration($lesson['duration'] ?? 0) ?>
                                                    </span>
                                                </div>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="lg:col-span-3">
            <!-- Área de Reproducción -->
            <div id="player-area" class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 mb-6">
                <div class="text-center py-12">
                    <div class="text-gray-400 text-lg mb-4">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Selecciona una lección para comenzar
                    </div>
                    <p class="text-gray-500">Elige una lección del menú lateral para reproducir el contenido</p>
                </div>
            </div>

            <!-- Información de la Lección Seleccionada -->
            <div id="lesson-info" class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 hidden">
                <h3 id="lesson-title" class="text-xl font-semibold text-white mb-4"></h3>
                <div id="lesson-details" class="text-gray-300">
                    <!-- Los detalles se cargan dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones para manejar la navegación del curso
function toggleSection(sectionId) {
    const sectionLessons = document.getElementById(`section-${sectionId}`);
    const arrow = event.currentTarget.querySelector('.section-arrow');
    
    if (sectionLessons.classList.contains('hidden')) {
        sectionLessons.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        sectionLessons.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}

function selectLesson(lessonId, lessonName) {
    // Ocultar área de player por defecto
    document.getElementById('player-area').classList.add('hidden');
    
    // Mostrar información de la lección
    document.getElementById('lesson-info').classList.remove('hidden');
    document.getElementById('lesson-title').textContent = lessonName;
    
    // TODO: Cargar detalles de la lección y preparar player
    // Esto se implementará en FASE 5
    
    // Marcar lección como activa en el sidebar
    document.querySelectorAll('.lesson-item button').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('text-gray-400');
    });
    
    event.currentTarget.classList.remove('text-gray-400');
    event.currentTarget.classList.add('bg-blue-600', 'text-white');
}
</script>
