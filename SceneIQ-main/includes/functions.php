<?php
// SceneIQ Functions - Versión corregida con métodos completos
// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración básica
if (!defined('SITE_NAME')) define('SITE_NAME', 'SceneIQ');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/sceneiq');
if (!defined('SITE_DESCRIPTION')) define('SITE_DESCRIPTION', 'Descubre tu próxima obsesión cinematográfica');

// Configuración de cookies y sesiones
if (!defined('COOKIE_LIFETIME')) define('COOKIE_LIFETIME', 3600 * 24 * 30); // 30 días
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 días

// Clase SceneIQ completa (funciona sin base de datos)
class SceneIQ {
    private $conn = null;

    public function __construct() {
        // Intentar conectar a la base de datos solo si existe la configuración
        $this->attemptDatabaseConnection();
    }

    private function attemptDatabaseConnection() {
        $config_files = [
            'config/database.php',
            '../config/database.php',
            dirname(__DIR__) . '/config/database.php'
        ];
        
        foreach ($config_files as $config_file) {
            if (file_exists($config_file)) {
                try {
                    require_once $config_file;
                    $database = new Database();
                    $this->conn = $database->getConnection();
                    break;
                } catch (Exception $e) {
                    error_log("Database connection failed: " . $e->getMessage());
                    $this->conn = null;
                }
            }
        }
    }

    // ================================
    // FUNCIONES DE SEGURIDAD
    // ================================
    
    public function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // ================================
    // FUNCIONES DE AUTENTICACIÓN
    // ================================
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: pages/login.php');
            exit();
        }
    }

    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: index.php');
            exit();
        }
    }

    // ================================
    // REGISTRO Y LOGIN DE USUARIOS
    // ================================
    
    public function registerUser($username, $email, $password, $fullName) {
        if ($this->conn) {
            return $this->registerUserInDatabase($username, $email, $password, $fullName);
        }
        
        // Simulación sin base de datos
        // Verificar si el usuario ya existe (simulado)
        if ($this->userExists($email, $username)) {
            return false;
        }
        
        // Simular registro exitoso
        error_log("Simulated user registration: $username, $email");
        return true;
    }

    private function registerUserInDatabase($username, $email, $password, $fullName) {
        try {
            // Verificar si el usuario ya existe
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            
            if ($stmt->rowCount() > 0) {
                return false; // Usuario ya existe
            }
            
            // Insertar nuevo usuario
            $hashedPassword = $this->hashPassword($password);
            $stmt = $this->conn->prepare("
                INSERT INTO users (username, email, password, full_name, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([$username, $email, $hashedPassword, $fullName]);
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function loginUser($email, $password) {
        if ($this->conn) {
            return $this->loginUserFromDatabase($email, $password);
        }
        
        // Simulación sin base de datos
        return $this->simulateLogin($email, $password);
    }

    private function loginUserFromDatabase($email, $password) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, username, email, password, full_name, role, theme_preference 
                FROM users 
                WHERE email = ? AND is_active = 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && $this->verifyPassword($password, $user['password'])) {
                // Establecer sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['theme_preference'] = $user['theme_preference'];
                
                // Actualizar último login
                $updateStmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    private function simulateLogin($email, $password) {
        // Cuentas de demostración
        $demoAccounts = [
            'admin@sceneiq.com' => [
                'password' => 'admin123',
                'id' => 1,
                'username' => 'admin',
                'full_name' => 'Administrador SceneIQ',
                'role' => 'admin',
                'theme' => 'dark'
            ],
            'user@sceneiq.com' => [
                'password' => 'user123',
                'id' => 2,
                'username' => 'user_demo',
                'full_name' => 'Usuario Demo',
                'role' => 'user',
                'theme' => 'dark'
            ]
        ];
        
        if (isset($demoAccounts[$email]) && $demoAccounts[$email]['password'] === $password) {
            $userData = $demoAccounts[$email];
            
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['user_email'] = $email;
            $_SESSION['full_name'] = $userData['full_name'];
            $_SESSION['user_role'] = $userData['role'];
            $_SESSION['theme_preference'] = $userData['theme'];
            
            return true;
        }
        
        return false;
    }

    public function logoutUser() {
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
    }

    private function userExists($email, $username) {
        // Simulación - en un entorno real verificarías en la BD
        $existingUsers = ['admin@sceneiq.com', 'user@sceneiq.com', 'test@test.com'];
        return in_array($email, $existingUsers);
    }

    // ================================
    // FUNCIONES DE CONTENIDO (DATOS DE EJEMPLO)
    // ================================
    
    public function getContent($limit = 20, $offset = 0, $type = null, $genre = null) {
        // Si hay conexión a BD, usar datos reales
        if ($this->conn) {
            return $this->getContentFromDatabase($limit, $offset, $type, $genre);
        }
        
        // Datos de ejemplo si no hay BD
        $sampleContent = [
            [
                'id' => 1,
                'title' => 'The Dark Knight',
                'year' => 2008,
                'type' => 'movie',
                'duration' => '152 min',
                'synopsis' => 'Batman debe enfrentar a su mayor enemigo en esta épica historia de heroísmo y caos.',
                'imdb_rating' => 9.0,
                'avg_rating' => 9.0,
                'review_count' => 156,
                'genres' => 'Acción, Drama, Crimen',
                'poster' => 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg'
            ],
            [
                'id' => 2,
                'title' => 'Breaking Bad',
                'year' => 2008,
                'type' => 'series',
                'duration' => '5 temporadas',
                'synopsis' => 'Un profesor de química se convierte en fabricante de metanfetaminas tras descubrir que tiene cáncer.',
                'imdb_rating' => 9.5,
                'avg_rating' => 9.5,
                'review_count' => 234,
                'genres' => 'Drama, Crimen, Thriller',
                'poster' => 'https://image.tmdb.org/t/p/w500/ggFHVNu6YYI5L9pCfOacjizRGt.jpg'
            ],
            [
                'id' => 3,
                'title' => 'Inception',
                'year' => 2010,
                'type' => 'movie',
                'duration' => '148 min',
                'synopsis' => 'Un ladrón que roba secretos corporativos mediante tecnología de sueños compartidos.',
                'imdb_rating' => 8.8,
                'avg_rating' => 8.8,
                'review_count' => 189,
                'genres' => 'Acción, Sci-Fi, Thriller',
                'poster' => 'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg'
            ],
            [
                'id' => 4,
                'title' => 'Stranger Things',
                'year' => 2016,
                'type' => 'series',
                'duration' => '4 temporadas',
                'synopsis' => 'Un grupo de niños descubre fuerzas sobrenaturales y experimentos secretos del gobierno.',
                'imdb_rating' => 8.7,
                'avg_rating' => 8.7,
                'review_count' => 167,
                'genres' => 'Sci-Fi, Horror, Drama',
                'poster' => 'https://image.tmdb.org/t/p/w500/x2LSRK2Cm7MZhjluni1msVJ3wDF.jpg'
            ],
            [
                'id' => 5,
                'title' => 'The Godfather',
                'year' => 1972,
                'type' => 'movie',
                'duration' => '175 min',
                'synopsis' => 'La saga de la familia Corleone bajo el patriarca Vito Corleone.',
                'imdb_rating' => 9.2,
                'avg_rating' => 9.2,
                'review_count' => 298,
                'genres' => 'Drama, Crimen',
                'poster' => 'https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsRolD1fZdja1.jpg'
            ],
            [
                'id' => 6,
                'title' => 'The Office',
                'year' => 2005,
                'type' => 'series',
                'duration' => '9 temporadas',
                'synopsis' => 'Un mockumentary sobre la vida cotidiana de los empleados de oficina.',
                'imdb_rating' => 9.0,
                'avg_rating' => 9.0,
                'review_count' => 145,
                'genres' => 'Comedia, Drama',
                'poster' => 'https://image.tmdb.org/t/p/w500/7DJKHzAi83BmQrWLrYYOqcoKfhR.jpg'
            ]
        ];
        
        // Filtrar por tipo si se especifica
        if ($type) {
            $sampleContent = array_filter($sampleContent, function($item) use ($type) {
                return $item['type'] === $type;
            });
        }
        
        // Aplicar límite
        return array_slice($sampleContent, $offset, $limit);
    }

    private function getContentFromDatabase($limit, $offset, $type, $genre) {
        try {
            $sql = "
                SELECT c.*, 
                       GROUP_CONCAT(g.name) as genres,
                       AVG(r.rating) as avg_rating,
                       COUNT(r.id) as review_count
                FROM content c
                LEFT JOIN content_genres cg ON c.id = cg.content_id
                LEFT JOIN genres g ON cg.genre_id = g.id
                LEFT JOIN reviews r ON c.id = r.content_id
                WHERE c.status = 'active'
            ";

            $params = [];
            
            if ($type) {
                $sql .= " AND c.type = :type";
                $params[':type'] = $type;
            }
            
            if ($genre) {
                $sql .= " AND g.slug = :genre";
                $params[':genre'] = $genre;
            }
            
            $sql .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get content error: " . $e->getMessage());
            return [];
        }
    }

    public function getContentById($contentId) {
        if ($this->conn) {
            return $this->getContentByIdFromDatabase($contentId);
        }
        
        // Buscar en datos de ejemplo
        $content = $this->getContent(100);
        foreach ($content as $item) {
            if ($item['id'] == $contentId) {
                return $item;
            }
        }
        
        return null;
    }

    private function getContentByIdFromDatabase($contentId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, 
                       GROUP_CONCAT(g.name) as genres,
                       AVG(r.rating) as avg_rating,
                       COUNT(r.id) as review_count
                FROM content c
                LEFT JOIN content_genres cg ON c.id = cg.content_id
                LEFT JOIN genres g ON cg.genre_id = g.id
                LEFT JOIN reviews r ON c.id = r.content_id
                WHERE c.id = ? AND c.status = 'active'
                GROUP BY c.id
            ");
            
            $stmt->execute([$contentId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get content by ID error: " . $e->getMessage());
            return null;
        }
    }

    public function getRecommendations($userId, $limit = 10) {
        if ($this->conn) {
            return $this->getRecommendationsFromDatabase($userId, $limit);
        }
        
        // Retornar contenido de ejemplo para recomendaciones
        return array_slice($this->getContent(), 0, $limit);
    }

    private function getRecommendationsFromDatabase($userId, $limit) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                FROM content c
                JOIN content_genres cg ON c.id = cg.content_id
                JOIN user_preferences up ON cg.genre_id = up.genre_id
                LEFT JOIN reviews r ON c.id = r.content_id
                LEFT JOIN user_lists ul ON c.id = ul.content_id AND ul.user_id = :user_id
                WHERE up.user_id = :user_id 
                AND ul.id IS NULL
                AND c.status = 'active'
                GROUP BY c.id
                ORDER BY (AVG(r.rating) * up.preference_weight) DESC, c.imdb_rating DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get recommendations error: " . $e->getMessage());
            return [];
        }
    }

    public function getGenres() {
        if ($this->conn) {
            return $this->getGenresFromDatabase();
        }
        
        // Géneros de ejemplo
        return [
            ['id' => 1, 'name' => 'Acción', 'slug' => 'accion', 'color' => '#ff6b6b'],
            ['id' => 2, 'name' => 'Drama', 'slug' => 'drama', 'color' => '#4ecdc4'],
            ['id' => 3, 'name' => 'Comedia', 'slug' => 'comedia', 'color' => '#45b7d1'],
            ['id' => 4, 'name' => 'Thriller', 'slug' => 'thriller', 'color' => '#96ceb4'],
            ['id' => 5, 'name' => 'Sci-Fi', 'slug' => 'sci-fi', 'color' => '#ffeaa7'],
            ['id' => 6, 'name' => 'Romance', 'slug' => 'romance', 'color' => '#fd79a8'],
            ['id' => 7, 'name' => 'Horror', 'slug' => 'horror', 'color' => '#6c5ce7'],
            ['id' => 8, 'name' => 'Documentales', 'slug' => 'documentales', 'color' => '#a29bfe'],
            ['id' => 9, 'name' => 'Animación', 'slug' => 'animacion', 'color' => '#fd63a2'],
            ['id' => 10, 'name' => 'Crimen', 'slug' => 'crimen', 'color' => '#636e72']
        ];
    }

    private function getGenresFromDatabase() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM genres ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get genres error: " . $e->getMessage());
            return [];
        }
    }

    // ================================
    // FUNCIONES DE USUARIO
    // ================================
    
    public function getUserStats($userId) {
        if ($this->conn) {
            return $this->getUserStatsFromDatabase($userId);
        }
        
        // Estadísticas simuladas
        return [
            'total_reviews' => rand(5, 25),
            'watchlist_count' => rand(10, 50),
            'favorites_count' => rand(5, 20),
            'avg_rating' => round(rand(70, 95) / 10, 1)
        ];
    }

    private function getUserStatsFromDatabase($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(DISTINCT r.id) as total_reviews,
                    AVG(r.rating) as avg_rating,
                    COUNT(DISTINCT CASE WHEN ul.list_type = 'watchlist' THEN ul.id END) as watchlist_count,
                    COUNT(DISTINCT CASE WHEN ul.list_type = 'favorites' THEN ul.id END) as favorites_count
                FROM users u
                LEFT JOIN reviews r ON u.id = r.user_id
                LEFT JOIN user_lists ul ON u.id = ul.user_id
                WHERE u.id = ?
                GROUP BY u.id
            ");
            
            $stmt->execute([$userId]);
            $stats = $stmt->fetch();
            
            return $stats ?: [
                'total_reviews' => 0,
                'watchlist_count' => 0,
                'favorites_count' => 0,
                'avg_rating' => 0.0
            ];
        } catch (PDOException $e) {
            error_log("Get user stats error: " . $e->getMessage());
            return [
                'total_reviews' => 0,
                'watchlist_count' => 0,
                'favorites_count' => 0,
                'avg_rating' => 0.0
            ];
        }
    }

    public function getUserList($userId, $listType, $limit = 10) {
        if ($this->conn) {
            return $this->getUserListFromDatabase($userId, $listType, $limit);
        }
        
        // Simular lista del usuario
        $content = $this->getContent(20);
        return array_slice($content, 0, rand(3, $limit));
    }

    private function getUserListFromDatabase($userId, $listType, $limit) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, ul.added_at
                FROM user_lists ul
                JOIN content c ON ul.content_id = c.id
                WHERE ul.user_id = ? AND ul.list_type = ?
                ORDER BY ul.added_at DESC
                LIMIT ?
            ");
            
            $stmt->execute([$userId, $listType, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get user list error: " . $e->getMessage());
            return [];
        }
    }

    // ================================
    // FUNCIONES DE UTILIDAD
    // ================================
    
    public function formatDate($date, $format = 'd/m/Y H:i') {
        return date($format, strtotime($date));
    }

    public function truncateText($text, $length = 150) {
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    public function generateSlug($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    public function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'hace un momento';
        if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
        if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
        if ($time < 2592000) return 'hace ' . floor($time/86400) . ' días';
        if ($time < 31536000) return 'hace ' . floor($time/2592000) . ' meses';
        
        return 'hace ' . floor($time/31536000) . ' años';
    }

    // ================================
    // FUNCIONES PLACEHOLDER PARA EVITAR ERRORES
    // ================================
    
    public function addReview($userId, $contentId, $rating, $reviewText = null, $spoilerAlert = false) {
        if ($this->conn) {
            return $this->addReviewToDatabase($userId, $contentId, $rating, $reviewText, $spoilerAlert);
        }
        
        error_log("addReview called but no database connection");
        return true; // Simular éxito
    }

    private function addReviewToDatabase($userId, $contentId, $rating, $reviewText, $spoilerAlert) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO reviews (user_id, content_id, rating, review_text, spoiler_alert, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                rating = VALUES(rating),
                review_text = VALUES(review_text),
                spoiler_alert = VALUES(spoiler_alert),
                updated_at = NOW()
            ");
            
            return $stmt->execute([$userId, $contentId, $rating, $reviewText, $spoilerAlert]);
        } catch (PDOException $e) {
            error_log("Add review error: " . $e->getMessage());
            return false;
        }
    }

    public function getReviews($contentId, $limit = 10, $offset = 0) {
        if ($this->conn) {
            return $this->getReviewsFromDatabase($contentId, $limit, $offset);
        }
        
        // Reseñas de ejemplo
        return [];
    }

    private function getReviewsFromDatabase($contentId, $limit, $offset) {
        try {
            $stmt = $this->conn->prepare("
                SELECT r.*, u.username, u.full_name, u.avatar
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.content_id = ? AND r.is_approved = 1
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$contentId, $limit, $offset]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get reviews error: " . $e->getMessage());
            return [];
        }
    }

    public function searchContent($query, $limit = 20) {
        if ($this->conn) {
            return $this->searchContentInDatabase($query, $limit);
        }
        
        // Buscar en datos de ejemplo
        $content = $this->getContent(100);
        $results = [];
        
        foreach ($content as $item) {
            if (stripos($item['title'], $query) !== false || 
                stripos($item['synopsis'], $query) !== false) {
                $results[] = $item;
            }
        }
        
        return array_slice($results, 0, $limit);
    }

    private function searchContentInDatabase($query, $limit) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, 
                       GROUP_CONCAT(g.name) as genres,
                       AVG(r.rating) as avg_rating,
                       COUNT(r.id) as review_count
                FROM content c
                LEFT JOIN content_genres cg ON c.id = cg.content_id
                LEFT JOIN genres g ON cg.genre_id = g.id
                LEFT JOIN reviews r ON c.id = r.content_id
                WHERE c.status = 'active' 
                AND (c.title LIKE ? OR c.synopsis LIKE ?)
                GROUP BY c.id
                ORDER BY 
                    CASE WHEN c.title LIKE ? THEN 1 ELSE 2 END,
                    c.imdb_rating DESC
                LIMIT ?
            ");
            
            $searchTerm = "%$query%";
            $titleTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm, $titleTerm, $limit]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Search content error: " . $e->getMessage());
            return [];
        }
    }

    public function addToUserList($userId, $contentId, $listType) {
        if ($this->conn) {
            return $this->addToUserListInDatabase($userId, $contentId, $listType);
        }
        
        error_log("addToUserList called but no database connection");
        return true; // Simular éxito
    }

    private function addToUserListInDatabase($userId, $contentId, $listType) {
        try {
            $stmt = $this->conn->prepare("
                INSERT IGNORE INTO user_lists (user_id, content_id, list_type, added_at)
                VALUES (?, ?, ?, NOW())
            ");
            
            return $stmt->execute([$userId, $contentId, $listType]);
        } catch (PDOException $e) {
            error_log("Add to user list error: " . $e->getMessage());
            return false;
        }
    }

    public function removeFromUserList($userId, $contentId, $listType) {
        if ($this->conn) {
            return $this->removeFromUserListInDatabase($userId, $contentId, $listType);
        }
        
        error_log("removeFromUserList called but no database connection");
        return true; // Simular éxito
    }

    private function removeFromUserListInDatabase($userId, $contentId, $listType) {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM user_lists 
                WHERE user_id = ? AND content_id = ? AND list_type = ?
            ");
            
            return $stmt->execute([$userId, $contentId, $listType]);
        } catch (PDOException $e) {
            error_log("Remove from user list error: " . $e->getMessage());
            return false;
        }
    }

    public function logActivity($userId, $action, $contentId = null, $metadata = null) {
        if ($this->conn) {
            return $this->logActivityInDatabase($userId, $action, $contentId, $metadata);
        }
        
        error_log("User activity: $action");
        return true; // Simular éxito
    }

    private function logActivityInDatabase($userId, $action, $contentId, $metadata) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO user_activity (user_id, action_type, content_id, metadata, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $metadataJson = $metadata ? json_encode($metadata) : null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            return $stmt->execute([
                $userId, 
                $action, 
                $contentId, 
                $metadataJson, 
                $ipAddress, 
                $userAgent
            ]);
        } catch (PDOException $e) {
            error_log("Log activity error: " . $e->getMessage());
            return false;
        }
    }
}

// Instancia global
$sceneiq = new SceneIQ();

// ================================
// FUNCIONES HELPER GLOBALES
// ================================

function escape($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) return null;
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'Usuario',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'full_name' => $_SESSION['full_name'] ?? 'Usuario',
        'theme' => $_SESSION['theme_preference'] ?? 'dark'
    ];
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

function formatRating($rating) {
    return number_format($rating, 1);
}

function getGenreColor($genreSlug) {
    $colors = [
        'accion' => '#ff6b6b',
        'drama' => '#4ecdc4',
        'comedia' => '#45b7d1',
        'thriller' => '#96ceb4',
        'sci-fi' => '#ffeaa7',
        'romance' => '#fd79a8',
        'horror' => '#6c5ce7',
        'documentales' => '#a29bfe',
        'animacion' => '#fd63a2',
        'crimen' => '#636e72'
    ];
    
    return $colors[$genreSlug] ?? '#667eea';
}
?>