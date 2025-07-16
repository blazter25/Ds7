<?php
// Configuración básica para SceneIQ
// Usar este archivo si no tienes base de datos configurada aún

// Configuración del sitio
define('SITE_NAME', 'SceneIQ');
define('SITE_URL', 'http://localhost/sceneiq');
define('SITE_DESCRIPTION', 'Descubre tu próxima obsesión cinematográfica');

// Inicializar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funciones básicas
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

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// Clase SceneIQ completa
class SceneIQ {
    
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Método que faltaba: getUserStats
    public function getUserStats($userId) {
        // Como no tienes base de datos, devuelve datos simulados
        // Puedes ajustar estos valores según tu necesidad
        return [
            'total_reviews' => rand(5, 25),
            'watchlist_count' => rand(8, 30),
            'favorites_count' => rand(3, 15),
            'avg_rating' => round(rand(60, 95) / 10, 1) // Rating entre 6.0 y 9.5
        ];
    }
    
    // Método que faltaba: getUserList
    public function getUserList($userId, $listType, $limit = 10) {
        // Datos simulados para diferentes tipos de listas
        $sampleContent = $this->getSampleContent();
        
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
    
    // Método auxiliar para obtener contenido de muestra
    private function getSampleContent() {
        return [
            [
                'id' => 1,
                'title' => 'The Dark Knight',
                'year' => 2008,
                'type' => 'movie',
                'synopsis' => 'Batman debe enfrentar a su mayor enemigo en esta épica historia de heroísmo y caos.',
                'imdb_rating' => 9.0,
                'avg_rating' => 9.0,
                'review_count' => 156,
                'genres' => 'Acción, Drama, Crimen',
                'poster' => 'assets/images/dark-knight.jpg',
                'duration' => '152 min'
            ],
            [
                'id' => 2,
                'title' => 'Breaking Bad',
                'year' => 2008,
                'type' => 'series',
                'synopsis' => 'Un profesor de química se convierte en fabricante de metanfetaminas tras descubrir que tiene cáncer.',
                'imdb_rating' => 9.5,
                'avg_rating' => 9.5,
                'review_count' => 234,
                'genres' => 'Drama, Crimen, Thriller',
                'poster' => 'assets/images/breaking-bad.jpg',
                'duration' => '5 temporadas'
            ],
            [
                'id' => 3,
                'title' => 'Inception',
                'year' => 2010,
                'type' => 'movie',
                'synopsis' => 'Un ladrón que roba secretos corporativos mediante tecnología de sueños compartidos.',
                'imdb_rating' => 8.8,
                'avg_rating' => 8.8,
                'review_count' => 189,
                'genres' => 'Acción, Sci-Fi, Thriller',
                'poster' => 'assets/images/inception.jpg',
                'duration' => '148 min'
            ],
            [
                'id' => 4,
                'title' => 'Stranger Things',
                'year' => 2016,
                'type' => 'series',
                'synopsis' => 'Un grupo de niños se enfrenta a fuerzas sobrenaturales en los años 80.',
                'imdb_rating' => 8.7,
                'avg_rating' => 8.7,
                'review_count' => 298,
                'genres' => 'Drama, Fantasy, Horror',
                'poster' => 'assets/images/stranger-things.jpg',
                'duration' => '4 temporadas'
            ],
            [
                'id' => 5,
                'title' => 'Interstellar',
                'year' => 2014,
                'type' => 'movie',
                'synopsis' => 'Un grupo de exploradores viaja a través de un agujero de gusano cerca de Saturno.',
                'imdb_rating' => 8.6,
                'avg_rating' => 8.6,
                'review_count' => 145,
                'genres' => 'Adventure, Drama, Sci-Fi',
                'poster' => 'assets/images/interstellar.jpg',
                'duration' => '169 min'
            ],
            [
                'id' => 6,
                'title' => 'The Mandalorian',
                'year' => 2019,
                'type' => 'series',
                'synopsis' => 'Un cazarrecompensas mandaloriano navega por los confines de la galaxia.',
                'imdb_rating' => 8.7,
                'avg_rating' => 8.7,
                'review_count' => 187,
                'genres' => 'Action, Adventure, Fantasy',
                'poster' => 'assets/images/mandalorian.jpg',
                'duration' => '3 temporadas'
            ]
        ];
    }
    
    public function getGenres() {
        // Géneros de ejemplo con colores
        return [
            ['id' => 1, 'name' => 'Acción', 'slug' => 'accion', 'color' => '#ff6b6b'],
            ['id' => 2, 'name' => 'Drama', 'slug' => 'drama', 'color' => '#4ecdc4'],
            ['id' => 3, 'name' => 'Comedia', 'slug' => 'comedia', 'color' => '#45b7d1'],
            ['id' => 4, 'name' => 'Thriller', 'slug' => 'thriller', 'color' => '#f39c12'],
            ['id' => 5, 'name' => 'Sci-Fi', 'slug' => 'sci-fi', 'color' => '#9b59b6'],
            ['id' => 6, 'name' => 'Romance', 'slug' => 'romance', 'color' => '#e91e63'],
            ['id' => 7, 'name' => 'Horror', 'slug' => 'horror', 'color' => '#34495e']
        ];
    }
    
    public function getContent($limit = 20, $offset = 0, $type = null, $genre = null) {
        $allContent = $this->getSampleContent();
        
        // Filtrar por tipo si se especifica
        if ($type) {
            $allContent = array_filter($allContent, function($content) use ($type) {
                return $content['type'] === $type;
            });
        }
        
        // Simular más contenido duplicando y modificando
        $expandedContent = [];
        for ($i = 0; $i < 3; $i++) {
            foreach ($allContent as $content) {
                $newContent = $content;
                $newContent['id'] = $content['id'] + ($i * 100);
                $expandedContent[] = $newContent;
            }
        }
        
        return array_slice($expandedContent, $offset, $limit);
    }
    
    public function getRecommendations($userId, $limit = 10) {
        // Para recomendaciones, mezclamos el contenido
        $content = $this->getSampleContent();
        shuffle($content);
        return array_slice($content, 0, $limit);
    }
    
    public function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return $this->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }
}

// Instancia global
$sceneiq = new SceneIQ();

// Obtener usuario actual
$user = getCurrentUser();
?>