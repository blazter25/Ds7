<?php
// api/recommendations.php
session_start();

// Incluir configuración
$config_paths = [
    '../includes/functions.php',
    '../config/simple_config.php',
    '../config/config.php'
];

$config_loaded = false;
foreach ($config_paths as $config_path) {
    if (file_exists($config_path)) {
        require_once $config_path;
        $config_loaded = true;
        break;
    }
}

// Si no se cargó configuración, crear clase básica
if (!$config_loaded || !isset($sceneiq)) {
    class BasicSceneIQ {
        public function isLoggedIn() {
            return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
        }
        
        public function validateCSRFToken($token) {
            return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
        }
        
        public function getContent($limit = 10, $offset = 0) {
            // Datos de ejemplo
            $movies = [
                ['id' => 1, 'title' => 'The Dark Knight', 'type' => 'movie', 'year' => 2008, 'genres' => 'Action, Crime, Drama', 'imdb_rating' => 9.0],
                ['id' => 2, 'title' => 'Inception', 'type' => 'movie', 'year' => 2010, 'genres' => 'Action, Sci-Fi, Thriller', 'imdb_rating' => 8.8],
                ['id' => 3, 'title' => 'Breaking Bad', 'type' => 'series', 'year' => 2008, 'genres' => 'Crime, Drama, Thriller', 'imdb_rating' => 9.5],
                ['id' => 4, 'title' => 'Stranger Things', 'type' => 'series', 'year' => 2016, 'genres' => 'Drama, Fantasy, Horror', 'imdb_rating' => 8.7],
                ['id' => 5, 'title' => 'The Matrix', 'type' => 'movie', 'year' => 1999, 'genres' => 'Action, Sci-Fi', 'imdb_rating' => 8.7],
                ['id' => 6, 'title' => 'Pulp Fiction', 'type' => 'movie', 'year' => 1994, 'genres' => 'Crime, Drama', 'imdb_rating' => 8.9],
                ['id' => 7, 'title' => 'The Shawshank Redemption', 'type' => 'movie', 'year' => 1994, 'genres' => 'Drama', 'imdb_rating' => 9.3],
                ['id' => 8, 'title' => 'Game of Thrones', 'type' => 'series', 'year' => 2011, 'genres' => 'Action, Adventure, Drama', 'imdb_rating' => 9.3],
                ['id' => 9, 'title' => 'The Godfather', 'type' => 'movie', 'year' => 1972, 'genres' => 'Crime, Drama', 'imdb_rating' => 9.2],
                ['id' => 10, 'title' => 'Forrest Gump', 'type' => 'movie', 'year' => 1994, 'genres' => 'Drama, Romance', 'imdb_rating' => 8.8]
            ];
            
            return array_slice($movies, $offset, $limit);
        }
        
        public function getRecommendations($userId, $limit = 10) {
            // Obtener contenido aleatorio como recomendaciones
            $content = $this->getContent(50, 0);
            shuffle($content);
            return array_slice($content, 0, $limit);
        }
    }
    
    $sceneiq = new BasicSceneIQ();
}

header('Content-Type: application/json');

if (!$sceneiq->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login requerido']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Clase de motor de recomendaciones
class RecommendationEngine {
    private $sceneiq;
    
    public function __construct($sceneiq) {
        $this->sceneiq = $sceneiq;
    }
    
    public function getRecommendationReason($content) {
        $reasons = [
            "Porque te gustó contenido similar",
            "Basado en tus géneros favoritos",
            "Popular entre usuarios con gustos similares",
            "Altamente valorado por la comunidad",
            "Nuevo contenido que podría interesarte",
            "Recomendado por el género " . explode(',', $content['genres'] ?? '')[0]
        ];
        
        return $reasons[array_rand($reasons)];
    }
    
    public function calculateSimilarity($content1, $content2) {
        $score = 0;
        
        // Comparar géneros
        $genres1 = array_map('trim', explode(',', strtolower($content1['genres'] ?? '')));
        $genres2 = array_map('trim', explode(',', strtolower($content2['genres'] ?? '')));
        $commonGenres = array_intersect($genres1, $genres2);
        $score += count($commonGenres) * 0.3;
        
        // Comparar años (proximidad)
        $yearDiff = abs(($content1['year'] ?? 0) - ($content2['year'] ?? 0));
        if ($yearDiff <= 2) $score += 0.2;
        elseif ($yearDiff <= 5) $score += 0.1;
        
        // Comparar ratings
        $ratingDiff = abs(($content1['imdb_rating'] ?? 0) - ($content2['imdb_rating'] ?? 0));
        if ($ratingDiff <= 0.5) $score += 0.2;
        elseif ($ratingDiff <= 1.0) $score += 0.1;
        
        // Mismo tipo (película/serie)
        if (($content1['type'] ?? '') === ($content2['type'] ?? '')) {
            $score += 0.3;
        }
        
        return min($score, 1.0);
    }
    
    public function getSimilarContent($contentId, $limit = 3) {
        $allContent = $this->sceneiq->getContent(50, 0);
        $targetContent = null;
        
        foreach ($allContent as $item) {
            if ($item['id'] == $contentId) {
                $targetContent = $item;
                break;
            }
        }
        
        if (!$targetContent) return [];
        
        $similarities = [];
        
        foreach ($allContent as $item) {
            if ($item['id'] != $contentId) {
                $similarity = $this->calculateSimilarity($targetContent, $item);
                $similarities[] = [
                    'content' => $item,
                    'similarity' => $similarity
                ];
            }
        }
        
        // Ordenar por similitud
        usort($similarities, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        // Retornar solo el contenido
        $result = [];
        foreach (array_slice($similarities, 0, $limit) as $item) {
            $result[] = $item['content'];
        }
        
        return $result;
    }
}

// Funciones auxiliares
function recordRecommendationFeedback($userId, $contentId, $feedback) {
    // En una implementación real, esto se guardaría en la base de datos
    error_log("Recommendation feedback: User $userId, Content $contentId, Feedback: $feedback");
    return true;
}

function generateFreshRecommendations($sceneiq, $userId) {
    $content = $sceneiq->getContent(20, rand(0, 30));
    shuffle($content);
    return array_slice($content, 0, 10);
}

function discoverContent($sceneiq, $params) {
    $content = $sceneiq->getContent(100, 0);
    
    // Aplicar filtros
    if (!empty($params['genres'])) {
        $content = array_filter($content, function($item) use ($params) {
            foreach ($params['genres'] as $genre) {
                if (stripos($item['genres'] ?? '', $genre) !== false) {
                    return true;
                }
            }
            return false;
        });
    }
    
    if (!empty($params['year_range']) && count($params['year_range']) == 2) {
        $content = array_filter($content, function($item) use ($params) {
            return ($item['year'] ?? 0) >= $params['year_range'][0] && 
                   ($item['year'] ?? 0) <= $params['year_range'][1];
        });
    }
    
    if (($params['rating_min'] ?? 0) > 0) {
        $content = array_filter($content, function($item) use ($params) {
            return ($item['imdb_rating'] ?? 0) >= $params['rating_min'];
        });
    }
    
    if (!empty($params['type'])) {
        $content = array_filter($content, function($item) use ($params) {
            return ($item['type'] ?? '') === $params['type'];
        });
    }
    
    // Limitar resultados
    return array_slice(array_values($content), 0, $params['limit'] ?? 20);
}

// Instanciar el motor de recomendaciones
$engine = new RecommendationEngine($sceneiq);

// Procesar la petición
switch ($method) {
    case 'GET':
        // Obtener recomendaciones
        $userId = $_SESSION['user_id'];
        $limit = intval($_GET['limit'] ?? 10);
        $type = $_GET['type'] ?? ''; // movie, series
        $genre = $_GET['genre'] ?? '';
        
        try {
            $recommendations = $sceneiq->getRecommendations($userId, $limit);
            
            // Aplicar filtros si se especifican
            if ($type) {
                $recommendations = array_filter($recommendations, function($item) use ($type) {
                    return ($item['type'] ?? '') === $type;
                });
            }
            
            if ($genre) {
                $recommendations = array_filter($recommendations, function($item) use ($genre) {
                    return strpos(strtolower($item['genres'] ?? ''), strtolower($genre)) !== false;
                });
            }
            
            // Agregar información adicional a cada recomendación
            $enrichedRecommendations = array_map(function($item) use ($engine) {
                $item['recommendation_score'] = round(rand(75, 95) / 10, 1);
                $item['reason'] = $engine->getRecommendationReason($item);
                $item['similar_to'] = $engine->getSimilarContent($item['id'], 3);
                return $item;
            }, $recommendations);
            
            echo json_encode([
                'success' => true,
                'recommendations' => array_values($enrichedRecommendations),
                'total' => count($enrichedRecommendations),
                'generated_at' => date('c')
            ]);
            
        } catch (Exception $e) {
            error_log("Recommendations API error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener recomendaciones']);
        }
        break;
        
    case 'POST':
        // Actualizar algoritmo de recomendaciones o marcar feedback
        if (!$sceneiq->validateCSRFToken($input['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        $action = $input['action'] ?? '';
        $userId = $_SESSION['user_id'];
        
        switch ($action) {
            case 'feedback':
                $contentId = intval($input['content_id'] ?? 0);
                $feedback = $input['feedback'] ?? ''; // liked, disliked, not_interested
                
                if (!$contentId || !in_array($feedback, ['liked', 'disliked', 'not_interested'])) {
                    echo json_encode(['success' => false, 'message' => 'Datos de feedback inválidos']);
                    exit;
                }
                
                try {
                    $result = recordRecommendationFeedback($userId, $contentId, $feedback);
                    
                    if ($result) {
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Feedback registrado exitosamente',
                            'updated_recommendations' => $sceneiq->getRecommendations($userId, 5)
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al registrar feedback']);
                    }
                } catch (Exception $e) {
                    error_log("Recommendation feedback error: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Error interno']);
                }
                break;
                
            case 'refresh':
                // Refrescar recomendaciones del usuario
                try {
                    $newRecommendations = generateFreshRecommendations($sceneiq, $userId);
                    
                    // Enriquecer las nuevas recomendaciones
                    $enrichedRecommendations = array_map(function($item) use ($engine) {
                        $item['recommendation_score'] = round(rand(75, 95) / 10, 1);
                        $item['reason'] = $engine->getRecommendationReason($item);
                        $item['similar_to'] = $engine->getSimilarContent($item['id'], 3);
                        return $item;
                    }, $newRecommendations);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Recomendaciones actualizadas',
                        'recommendations' => $enrichedRecommendations,
                        'refreshed_at' => date('c')
                    ]);
                } catch (Exception $e) {
                    error_log("Refresh recommendations error: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar recomendaciones']);
                }
                break;
                
            case 'discover':
                // Descubrir contenido basado en parámetros específicos
                $discoverParams = $input['params'] ?? [];
                $genres = $discoverParams['genres'] ?? [];
                $yearRange = $discoverParams['year_range'] ?? [];
                $ratingMin = floatval($discoverParams['rating_min'] ?? 0);
                $contentType = $discoverParams['type'] ?? '';
                
                try {
                    $discoveredContent = discoverContent($sceneiq, [
                        'user_id' => $userId,
                        'genres' => $genres,
                        'year_range' => $yearRange,
                        'rating_min' => $ratingMin,
                        'type' => $contentType,
                        'limit' => intval($discoverParams['limit'] ?? 20)
                    ]);
                    
                    // Enriquecer el contenido descubierto
                    $enrichedContent = array_map(function($item) use ($engine) {
                        $item['discovery_score'] = round(rand(70, 90) / 10, 1);
                        $item['reason'] = $engine->getRecommendationReason($item);
                        return $item;
                    }, $discoveredContent);
                    
                    echo json_encode([
                        'success' => true,
                        'discovered' => $enrichedContent,
                        'params_used' => $discoverParams
                    ]);
                } catch (Exception $e) {
                    error_log("Discover content error: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Error en descubrimiento']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>