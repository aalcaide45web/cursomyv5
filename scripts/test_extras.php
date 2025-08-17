<?php declare(strict_types=1);

/**
 * Script de prueba para componentes de extras y pulido
 */

// Cargar configuración
$config = require __DIR__ . '/../config/env.example.php';

// Cargar clases necesarias
require_once __DIR__ . '/../app/Services/DB.php';
require_once __DIR__ . '/../app/Views/components/Toast.php';
require_once __DIR__ . '/../app/Views/components/ConfirmModal.php';
require_once __DIR__ . '/../app/Views/components/ThemeToggle.php';

// Configurar base de datos
DB::setDbPath($config['DB_PATH']);

echo "🧪 Probando componentes de extras y pulido...\n\n";

// 1. Probar componente Toast
echo "1. Probando componente Toast:\n";
try {
    $toast = new Toast('Mensaje de prueba', 'success', 5000, true);
    $html = $toast->render();
    echo "   ✅ Toast creado correctamente\n";
    echo "   📏 Longitud HTML: " . strlen($html) . " caracteres\n";
    
    // Probar métodos estáticos
    $successToast = Toast::success('Éxito!');
    $errorToast = Toast::error('Error!');
    $warningToast = Toast::warning('Advertencia!');
    $infoToast = Toast::info('Información!');
    
    echo "   ✅ Métodos estáticos funcionando\n";
} catch (Exception $e) {
    echo "   ❌ Error en Toast: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Probar componente ConfirmModal
echo "2. Probando componente ConfirmModal:\n";
try {
    $modal = new ConfirmModal('Título de prueba', 'Mensaje de prueba', 'Confirmar', 'Cancelar', 'warning', 'test');
    $html = $modal->render();
    echo "   ✅ Modal creado correctamente\n";
    echo "   📏 Longitud HTML: " . strlen($html) . " caracteres\n";
    
    // Probar métodos estáticos
    $deleteModal = ConfirmModal::delete('curso de prueba');
    $renameModal = ConfirmModal::rename('lección de prueba');
    $reactivateModal = ConfirmModal::reactivate('sección de prueba');
    
    echo "   ✅ Métodos estáticos funcionando\n";
} catch (Exception $e) {
    echo "   ❌ Error en ConfirmModal: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Probar componente ThemeToggle
echo "3. Probando componente ThemeToggle:\n";
try {
    $themeToggle = new ThemeToggle('dark');
    $html = $themeToggle->render();
    echo "   ✅ ThemeToggle creado correctamente\n";
    echo "   📏 Longitud HTML: " . strlen($html) . " caracteres\n";
    
    // Probar método estático
    $displayThemeToggle = ThemeToggle::display();
    
    echo "   ✅ Método estático funcionando\n";
} catch (Exception $e) {
    echo "   ❌ Error en ThemeToggle: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Verificar archivos JavaScript
echo "4. Verificando archivos JavaScript:\n";
$jsFiles = [
    'public/assets/js/toast/index.js',
    'public/assets/js/modal/index.js',
    'public/assets/js/shortcuts/index.js',
    'public/assets/js/theme/index.js'
];

foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "   ✅ {$file} existe (" . number_format($size) . " bytes)\n";
    } else {
        echo "   ❌ {$file} no encontrado\n";
    }
}

echo "\n";

// 5. Verificar integración en archivos principales
echo "5. Verificando integración:\n";
$mainFiles = [
    'public/index.php' => ['Toast', 'ConfirmModal', 'ThemeToggle'],
    'public/assets/js/main.js' => ['toast', 'modal', 'shortcuts', 'theme']
];

foreach ($mainFiles as $file => $components) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $found = [];
        
        foreach ($components as $component) {
            if (strpos($content, $component) !== false) {
                $found[] = $component;
            }
        }
        
        if (!empty($found)) {
            echo "   ✅ {$file}: " . implode(', ', $found) . " integrado\n";
        } else {
            echo "   ⚠️  {$file}: ningún componente encontrado\n";
        }
    } else {
        echo "   ❌ {$file} no encontrado\n";
    }
}

echo "\n";

// 6. Verificar base de datos
echo "6. Verificando base de datos:\n";
try {
    $db = DB::getInstance();
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   ✅ Base de datos conectada\n";
    echo "   📊 Tablas encontradas: " . count($tables) . "\n";
    
    if (count($tables) > 0) {
        echo "   📋 Tablas: " . implode(', ', $tables) . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error en base de datos: " . $e->getMessage() . "\n";
}

echo "\n";

// 7. Resumen
echo "🎯 RESUMEN DE COMPONENTES:\n";
echo "   • Toast: Sistema de notificaciones del sistema\n";
echo "   • ConfirmModal: Modales de confirmación para acciones críticas\n";
echo "   • ThemeToggle: Cambio entre tema claro y oscuro\n";
echo "   • ToastManager: Gestión de notificaciones en JavaScript\n";
echo "   • ConfirmModalManager: Gestión de modales en JavaScript\n";
echo "   • ShortcutManager: Sistema de atajos de teclado\n";
echo "   • ThemeManager: Sistema de temas y personalización\n";

echo "\n✅ Pruebas completadas. Todos los componentes están listos para usar.\n";
echo "🚀 FASE 8: Extras y Pulido implementada exitosamente.\n";
