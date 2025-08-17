<?php declare(strict_types=1);

require_once __DIR__ . '/../config/env.example.php';

$config = require __DIR__ . '/../config/env.example.php';
$dbPath = $config['DB_PATH'];

// Crear directorio de base de datos si no existe
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Leer y ejecutar el schema
    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
    $pdo->exec($schema);
    
    echo "✅ Base de datos inicializada correctamente en: $dbPath\n";
    echo "📊 Tablas creadas:\n";
    
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error al inicializar la base de datos: " . $e->getMessage() . "\n";
    exit(1);
}
