<?php declare(strict_types=1);

/**
 * Script de prueba para el sistema de escaneo
 * Ejecutar: php scripts/test_scan_system.php
 */

echo "🧪 Probando sistema de escaneo de CursoMy LMS...\n\n";

// Cargar configuración
$config = require __DIR__ . '/../config/env.example.php';

// Cargar clases necesarias
require_once __DIR__ . '/../app/Services/Scanner/FilesystemScanner.php';
require_once __DIR__ . '/../app/Services/Scanner/Hasher.php';
require_once __DIR__ . '/../app/Services/Media/MediaProbe.php';

// Cargar repositorios
require_once __DIR__ . '/../app/Repositories/BaseRepository.php';
require_once __DIR__ . '/../app/Repositories/TopicRepository.php';
require_once __DIR__ . '/../app/Repositories/InstructorRepository.php';
require_once __DIR__ . '/../app/Repositories/CourseRepository.php';
require_once __DIR__ . '/../app/Repositories/SectionRepository.php';
require_once __DIR__ . '/../app/Repositories/LessonRepository.php';

// Cargar servicios base
require_once __DIR__ . '/../app/Services/DB.php';
require_once __DIR__ . '/../app/Lib/Str.php';
require_once __DIR__ . '/../app/Lib/Time.php';

// Configurar base de datos
DB::setDbPath($config['DB_PATH']);

require_once __DIR__ . '/../app/Services/Scanner/Importer.php';

try {
    echo "📁 Configuración:\n";
    echo "  - Uploads: {$config['UPLOADS_PATH']}\n";
    echo "  - Cache: {$config['CACHE_PATH']}\n";
    echo "  - ffmpeg: " . ($config['USE_FFMPEG'] ? 'Habilitado' : 'Deshabilitado') . "\n\n";
    
    // Probar FilesystemScanner
    echo "🔍 Probando FilesystemScanner...\n";
    $scanner = new FilesystemScanner($config['UPLOADS_PATH']);
    $scanResult = $scanner->scan();
    
    echo "  - Archivos encontrados: {$scanResult['total_files']}\n";
    echo "  - Errores: {$scanResult['total_errors']}\n";
    
    if (!empty($scanResult['errors'])) {
        echo "  - Errores específicos:\n";
        foreach ($scanResult['errors'] as $error) {
            echo "    * {$error}\n";
        }
    }
    
    if (!empty($scanResult['files'])) {
        echo "  - Primeros archivos:\n";
        $count = 0;
        foreach ($scanResult['files'] as $file) {
            if ($count >= 3) break;
            echo "    * {$file['relative_path']} ({$file['size']} bytes)\n";
            $count++;
        }
    }
    
    // Probar Hasher
    echo "\n🔐 Probando Hasher...\n";
    $hasher = new Hasher($config['CACHE_PATH']);
    $hashStats = $hasher->getCacheStats();
    
    echo "  - Archivos en cache: {$hashStats['total_cached_files']}\n";
    echo "  - Tamaño del cache: {$hashStats['cache_file_size']} bytes\n";
    
    // Probar MediaProbe
    echo "\n🎬 Probando MediaProbe...\n";
    $mediaProbe = new MediaProbe($config['CACHE_PATH'], $config['USE_FFMPEG']);
    $ffmpegInfo = $mediaProbe->getFfmpegInfo();
    
    echo "  - ffmpeg disponible: " . ($ffmpegInfo['available'] ? 'Sí' : 'No') . "\n";
    echo "  - Ruta ffmpeg: {$ffmpegInfo['ffmpeg_path']}\n";
    echo "  - Ruta ffprobe: {$ffmpegInfo['ffprobe_path']}\n";
    echo "  - Versión: {$ffmpegInfo['version']}\n";
    
    // Probar Importer
    echo "\n📥 Probando Importer...\n";
    $importer = new Importer($scanner, $hasher, $mediaProbe);
    $systemInfo = $importer->getSystemInfo();
    
    echo "  - Topics en BD: {$systemInfo['database']['topics_count']}\n";
    echo "  - Instructores en BD: {$systemInfo['database']['instructors_count']}\n";
    echo "  - Cursos en BD: {$systemInfo['database']['courses_count']}\n";
    echo "  - Secciones en BD: {$systemInfo['database']['sections_count']}\n";
    echo "  - Lecciones en BD: {$systemInfo['database']['lessons_count']}\n";
    
    // Probar escaneo solo (sin importar)
    echo "\n🔍 Probando escaneo de diagnóstico...\n";
    $diagnosticScan = $scanner->scan();
    $scanStats = $scanner->getScanStats();
    
    echo "  - Total archivos: {$scanStats['total_files']}\n";
    echo "  - Tamaño total: {$scanStats['total_size_formatted']}\n";
    echo "  - Topics detectados: {$scanStats['topics_count']}\n";
    echo "  - Instructores detectados: {$scanStats['instructors_count']}\n";
    echo "  - Cursos detectados: {$scanStats['courses_count']}\n";
    
    if (!empty($scanStats['extensions'])) {
        echo "  - Extensiones encontradas:\n";
        foreach ($scanStats['extensions'] as $ext => $count) {
            echo "    * .{$ext}: {$count} archivos\n";
        }
    }
    
    echo "\n✅ Pruebas completadas exitosamente!\n";
    
    // Mostrar recomendaciones
    echo "\n💡 Recomendaciones:\n";
    if ($scanResult['total_files'] === 0) {
        echo "  - No se encontraron archivos de video. Verifica la estructura de carpetas.\n";
        echo "  - Asegúrate de que los archivos tengan extensiones válidas (.mp4, .mkv, .webm, .mov)\n";
    }
    
    if (!$ffmpegInfo['available']) {
        echo "  - ffmpeg no está disponible. Las miniaturas y metadatos serán limitados.\n";
        echo "  - Considera instalar ffmpeg para mejor funcionalidad.\n";
    }
    
    if ($scanResult['total_errors'] > 0) {
        echo "  - Hay errores en el escaneo. Revisa los permisos de archivos y carpetas.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error durante las pruebas: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
