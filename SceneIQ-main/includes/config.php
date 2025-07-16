<?php
// includes/config.php
// Configuración básica para SceneIQ

// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración del sitio (solo si no están definidas)
if (!defined('SITE_NAME')) define('SITE_NAME', 'SceneIQ');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/SceneIQ-main');
if (!defined('SITE_DESCRIPTION')) define('SITE_DESCRIPTION', 'Descubre tu próxima obsesión cinematográfica');

// Funciones básicas (solo si no existen)
if (!function_exists('escape')) {
    function escape($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('getCurrentUser')) {
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
}

if (!function_exists('getAlert')) {
    function getAlert() {
        if (isset($_SESSION['alert'])) {
            $alert = $_SESSION['alert'];
            unset($_SESSION['alert']);
            return $alert;
        }
        return null;
    }
}

if (!function_exists('showAlert')) {
    function showAlert($message, $type = 'info') {
        $_SESSION['alert'] = ['message' => $message, 'type' => $type];
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

// Clase SceneIQ - Agregar métodos faltantes a la clase existente
if (!class_exists('SceneIQ')) {
    // Tu clase SceneIQ del functions.php ya existe, solo agregamos métodos faltantes
    class SceneIQ {
        private $conn = null;

        public function __construct() {
            // No intentar conexión a BD por ahora
        }

        public function generateCSRFToken() {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }

        public function getContent($limit = 20, $offset = 0, $type = null, $genre = null) {
            // Datos de ejemplo
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

        public function getGenres() {
            return [
                ['id' => 1, 'name' => 'Acción', 'slug' => 'accion', 'color' => '#ff6b6b'],
                ['id' => 2, 'name' => 'Drama', 'slug' => 'drama', 'color' => '#4ecdc4'],
                ['id' => 3, 'name' => 'Comedia', 'slug' => 'comedia', 'color' => '#45b7d1'],
                ['id' => 4, 'name' => 'Thriller', 'slug' => 'thriller', 'color' => '#96ceb4'],
                ['id' => 5, 'name' => 'Sci-Fi', 'slug' => 'sci-fi', 'color' => '#ffeaa7'],
                ['id' => 6, 'name' => 'Romance', 'slug' => 'romance', 'color' => '#fd79a8'],
                ['id' => 7, 'name' => 'Horror', 'slug' => 'horror', 'color' => '#6c5ce7']
            ];
        }

        public function getRecommendations($userId, $limit = 10) {
            // Retornar contenido de ejemplo para recomendaciones
            $content = $this->getContent();
            shuffle($content);
            return array_slice($content, 0, $limit);
        }

        // MÉTODOS FALTANTES QUE NECESITA EL DASHBOARD
        public function getUserStats($userId) {
            // Como no tienes base de datos, devuelve datos simulados
            return [
                'total_reviews' => rand(5, 25),
                'watchlist_count' => rand(8, 30),
                'favorites_count' => rand(3, 15),
                'avg_rating' => round(rand(60, 95) / 10, 1)
            ];
        }

        public function getUserList($userId, $listType, $limit = 10) {
            // Datos simulados para diferentes tipos de listas
            $sampleContent = $this->getContent();
            
            switch ($listType) {
                case 'watchlist':
                    return array_slice($sampleContent, 0, $limit);
                case 'favorites':
                    return array_slice($sampleContent, 2, $limit);
                case 'watched':
                    return array_slice($sampleContent, 1, $limit);
                default:
                    return array_slice($sampleContent, 0, $limit);
            }
        }

        public function truncateText($text, $length = 150) {
            return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
        }

        public function validateCSRFToken($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
    }
} else {
    // La clase SceneIQ ya existe, intentar agregar métodos faltantes
    // Esto es un hack temporal
    global $sceneiq;
    if (isset($sceneiq) && is_object($sceneiq)) {
        // Agregar métodos faltantes si no existen
        if (!method_exists($sceneiq, 'getUserStats')) {
            // Crear una clase extendida temporalmente
            class SceneIQExtended extends SceneIQ {
                public function getUserStats($userId) {
                    return [
                        'total_reviews' => rand(5, 25),
                        'watchlist_count' => rand(8, 30),
                        'favorites_count' => rand(3, 15),
                        'avg_rating' => round(rand(60, 95) / 10, 1)
                    ];
                }

                public function getUserList($userId, $listType, $limit = 10) {
                    $sampleContent = $this->getContent();
                    
                    switch ($listType) {
                        case 'watchlist':
                            return array_slice($sampleContent, 0, $limit);
                        case 'favorites':
                            return array_slice($sampleContent, 2, $limit);
                        case 'watched':
                            return array_slice($sampleContent, 1, $limit);
                        default:
                            return array_slice($sampleContent, 0, $limit);
                    }
                }
            }
            
            // Reemplazar la instancia global
            $sceneiq = new SceneIQExtended();
        }
    }
}

// Instancia global si no existe
if (!isset($sceneiq)) {
    $sceneiq = new SceneIQ();
}

// TEMPORAL - Para pruebas sin sistema de login
// Simular usuario logueado para que funcione el dashboard
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'TestUser';
    $_SESSION['full_name'] = 'Usuario de Prueba';
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_email'] = 'test@sceneiq.com';
}

// Obtener usuario actual
$user = getCurrentUser();
?>