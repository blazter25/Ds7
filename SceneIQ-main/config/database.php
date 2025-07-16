<?php
class DatabaseConfig {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->initializeConnection();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeConnection() {
        $config = $this->getConfig();
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
            
            $this->connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new DatabaseException("No se pudo conectar a la base de datos");
        }
    }
    
    private function getConfig() {
        // Priorizar variables de entorno
        return [
            'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
            'database' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'sceneiq_db',
            'username' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
            'password' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: ''
        ];
    }
    
    public function getConnection() {
        if ($this->connection === null) {
            $this->initializeConnection();
        }
        return $this->connection;
    }
    
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }
    
    public function commit() {
        return $this->getConnection()->commit();
    }
    
    public function rollback() {
        return $this->getConnection()->rollback();
    }
    
    public function prepare($sql) {
        return $this->getConnection()->prepare($sql);
    }
    
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }
}

// Excepciones personalizadas
class DatabaseException extends Exception {}
class ValidationException extends Exception {}

// Mejora en la clase SceneIQ
class SceneIQ {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getInstance();
    }
    
    public function addReview($userId, $contentId, $rating, $reviewText, $spoilerAlert) {
        $this->db->beginTransaction();
        
        try {
            // Verificar si ya existe una reseña
            $checkStmt = $this->db->prepare("
                SELECT id FROM reviews 
                WHERE user_id = ? AND content_id = ?
            ");
            $checkStmt->execute([$userId, $contentId]);
            
            if ($checkStmt->rowCount() > 0) {
                // Actualizar reseña existente
                $stmt = $this->db->prepare("
                    UPDATE reviews 
                    SET rating = ?, review_text = ?, spoiler_alert = ?, updated_at = NOW()
                    WHERE user_id = ? AND content_id = ?
                ");
                $result = $stmt->execute([$rating, $reviewText, $spoilerAlert, $userId, $contentId]);
            } else {
                // Crear nueva reseña
                $stmt = $this->db->prepare("
                    INSERT INTO reviews (user_id, content_id, rating, review_text, spoiler_alert, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $result = $stmt->execute([$userId, $contentId, $rating, $reviewText, $spoilerAlert]);
            }
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollback();
                return false;
            }
            
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Database error in addReview: " . $e->getMessage());
            throw new DatabaseException("Error al guardar la reseña");
        }
    }
    
    public function getReviews($contentId, $limit = 10, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username, u.full_name, u.avatar,
                       (SELECT COUNT(*) FROM review_likes rl WHERE rl.review_id = r.id) as likes_count
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.content_id = ? AND r.is_approved = 1
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->bindValue(1, $contentId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Database error in getReviews: " . $e->getMessage());
            throw new DatabaseException("Error al obtener las reseñas");
        }
    }
}