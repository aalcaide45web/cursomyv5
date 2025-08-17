<?php declare(strict_types=1);

abstract class BaseRepository
{
    protected string $table;
    protected string $primaryKey = 'id';
    
    /**
     * Obtiene todos los registros
     */
    public function all(): array
    {
        $stmt = DB::query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un registro por ID
     */
    public function find(int $id): ?array
    {
        $stmt = DB::prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Obtiene un registro por campo específico
     */
    public function findBy(string $field, $value): ?array
    {
        $stmt = DB::prepare("SELECT * FROM {$this->table} WHERE {$field} = ?");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Obtiene múltiples registros por campo específico
     */
    public function findAllBy(string $field, $value): array
    {
        $stmt = DB::prepare("SELECT * FROM {$this->table} WHERE {$field} = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Crea un nuevo registro
     */
    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
        $stmt = DB::prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int) DB::lastInsertId();
    }
    
    /**
     * Actualiza un registro existente
     */
    public function update(int $id, array $data): bool
    {
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        $stmt = DB::prepare($sql);
        
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }
    
    /**
     * Elimina un registro
     */
    public function delete(int $id): bool
    {
        $stmt = DB::prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Cuenta el total de registros
     */
    public function count(): int
    {
        $stmt = DB::query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Cuenta registros por condición
     */
    public function countBy(string $field, $value): int
    {
        $stmt = DB::prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$field} = ?");
        $stmt->execute([$value]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Obtiene registros con paginación
     */
    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = DB::query("SELECT * FROM {$this->table} LIMIT {$perPage} OFFSET {$offset}");
        $items = $stmt->fetchAll();
        
        $total = $this->count();
        
        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => ($page * $perPage) < $total,
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Ejecuta una consulta SQL personalizada
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = DB::prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Ejecuta una consulta SQL personalizada y retorna todos los resultados
     */
    protected function queryAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Ejecuta una consulta SQL personalizada y retorna un resultado
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }
}
