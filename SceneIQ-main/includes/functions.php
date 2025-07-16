<?php
// SceneIQ Functions - Versión completa sin errores
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración básica
if (!defined('SITE_NAME')) define('SITE_NAME', 'SceneIQ');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/sceneiq');
if (!defined('SITE_DESCRIPTION')) define('SITE_DESCRIPTION', 'Descubre tu próxima obsesión cinematográfica');

// Clase SceneIQ completa
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
            ['id' => 7, 'name' => 'Horror', 'slug' => 'horror', 'color' => '#6c5ce7'],
            ['id' => 8, 'name' => 'Documentales', 'slug' => 'documentales', 'color' => '#a29bfe'],
            ['id' => 9, 'name' => 'Animación', 'slug' => 'animacion', 'color' => '#fd63a2'],
            ['id' => 10, 'name' => 'Crimen', 'slug' => 'crimen', 'color' => '#636e72']
        ];
    }

    public function getRecommendations($userId, $limit = 10) {
        // Retornar contenido de ejemplo para recomendaciones
        $content = $this->getContent();
        shuffle($content);
        return array_slice($content, 0, $limit);
    }

    // MÉTODO AGREGADO: getUserStats - Para estadísticas del usuario
    public function getUserStats($userId) {
        // Como no tienes base de datos, devuelve datos simulados realistas
        return [
            'total_reviews' => rand(5, 25),
            'watchlist_count' => rand(8, 30),
            'favorites_count' => rand(3, 15),
            'avg_rating' => round(rand(60, 95) / 10, 1) // Rating entre 6.0 y 9.5
        ];
    }

    // MÉTODO AGREGADO: getUserList - Para listas del usuario (watchlist, favorites, watched)
    public function getUserList($userId, $listType, $limit = 10) {
        // Datos simulados para diferentes tipos de listas
        $sampleContent = $this->getContent();
        
        switch ($listType) {
            case 'watchlist':
                // Para la lista de seguimiento, mostrar diferentes contenidos
                return array_slice($sampleContent, 0, $limit);
            case 'favorites':
                // Para favoritos, mostrar otros contenidos
                return array_slice($sampleContent, 2, $limit);
            case 'watched':
                // Para visto recientemente
                return array_slice($sampleContent, 1, $limit);
            default:
                return array_slice($sampleContent, 0, $limit);
        }
    }

    public function truncateText($text, $length = 150) {
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    public function registerUser($username, $email, $password, $fullName) {
        // Para desarrollo: guardar en archivos de texto
        $usersFile = 'data/users.txt';
        
        // Crear directorio si no existe
        if (!is_dir('data')) {
            mkdir('data', 0777, true);
        }
        
        // Verificar si el usuario ya existe
        if (file_exists($usersFile)) {
            $users = file($usersFile, FILE_IGNORE_NEW_LINES);
            foreach ($users as $userLine) {
                $userData = explode('|', $userLine);
                if ($userData[1] === $email || $userData[0] === $username) {
                    return false; // Usuario ya existe
                }
            }
        }
        
        // Agregar nuevo usuario
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $newUser = "$username|$email|$hashedPassword|$fullName|user|dark\n";
        file_put_contents($usersFile, $newUser, FILE_APPEND | LOCK_EX);
        
        return true;
    }

    public function loginUser($email, $password) {
        $usersFile = 'data/users.txt';
        
        // Verificar usuarios registrados
        if (file_exists($usersFile)) {
            $users = file($usersFile, FILE_IGNORE_NEW_LINES);
            foreach ($users as $index => $userLine) {
                $userData = explode('|', $userLine);
                if (count($userData) >= 6 && $userData[1] === $email) {
                    if (password_verify($password, $userData[2])) {
                        $_SESSION['user_id'] = $index + 100; // ID único
                        $_SESSION['username'] = $userData[0];
                        $_SESSION['user_email'] = $userData[1];
                        $_SESSION['full_name'] = $userData[3];
                        $_SESSION['user_role'] = $userData[4];
                        $_SESSION['theme_preference'] = $userData[5];
                        
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Instancia global
$sceneiq = new SceneIQ();

// Funciones helper básicas
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

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
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

// FUNCIÓN AGREGADA: redirect - Para redirecciones
function redirect($url) {
    header("Location: $url");
    exit;
}

// FUNCIÓN AGREGADA: showAlert - Para mostrar alertas
function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

// Obtener usuario actual
$user = getCurrentUser();
?>