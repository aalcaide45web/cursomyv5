<?php declare(strict_types=1);

class DB
{
    private static ?PDO $instance = null;
    private static string $dbPath;
    
    private function __construct() {}
    private function __clone() {}
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::initialize();
        }
        
        return self::$instance;
    }
    
    public static function setDbPath(string $path): void
    {
        self::$dbPath = $path;
    }
    
    private static function initialize(): void
    {
        if (!isset(self::$dbPath)) {
            throw new RuntimeException('DB_PATH no configurado. Llama a DB::setDbPath() primero.');
        }
        
        try {
            $pdo = new PDO("sqlite:" . self::$dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Configurar pragmas para optimización
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA synchronous=NORMAL');
            $pdo->exec('PRAGMA cache_size=10000');
            $pdo->exec('PRAGMA temp_store=MEMORY');
            $pdo->exec('PRAGMA mmap_size=268435456'); // 256MB
            
            self::$instance = $pdo;
            
        } catch (PDOException $e) {
            throw new RuntimeException("Error al conectar con la base de datos: " . $e->getMessage());
        }
    }
    
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }
    
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }
    
    public static function rollback(): bool
    {
        return self::getInstance()->rollback();
    }
    
    public static function inTransaction(): bool
    {
        return self::getInstance()->inTransaction();
    }
    
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
    
    public static function prepare(string $sql): PDOStatement
    {
        return self::getInstance()->prepare($sql);
    }
    
    public static function exec(string $sql): int
    {
        return self::getInstance()->exec($sql);
    }
    
    public static function query(string $sql): PDOStatement
    {
        return self::getInstance()->query($sql);
    }
}
