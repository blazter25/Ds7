<?php
// api/recomendations.php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!$sceneiq->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login requerido']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

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
                    return $item['type'] === $type;
                });
            }
            
            if ($genre) {
                $recommendations = array_filter($recommendations, function($item) use ($genre) {
                    return strpos(strtolower($item['genres'] ?? ''), strtolower($genre)) !== false;
                });
            }
            
            // Agregar información adicional a cada recomendación
            $enrichedRecommendations = array_map(function($item) use ($sceneiq) {
                $item['recommendation_score'] = round(rand(75, 95) / 10, 1);
                $item['reason'] = $sceneiq->getRecommendationReason($item);
                $item['similar_to'] = $sceneiq->getSimilarContent($item['id'], 3);
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
                    $result = $sceneiq->recordRecommendationFeedback($userId, $contentId, $feedback);
                    
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
                    $newRecommendations = $sceneiq->generateFreshRecommendations($userId);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Recomendaciones actualizadas',
                        'recommendations' => $newRecommendations,
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
                    $discoveredContent = $sceneiq->discoverContent([
                        'user_id' => $userId,
                        'genres' => $genres,
                        'year_range' => $yearRange,
                        'rating_min' => $ratingMin,
                        'type' => $contentType,
                        'limit' => intval($discoverParams['limit'] ?? 20)
                    ]);
                    
                    echo json_encode([
                        'success' => true,
                        'discovered' => $discoveredContent,
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

// Funciones auxiliares para el sistema de recomendaciones
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
            "Porque disfrutas del género " . explode(',', $content['genres'] ?? '')[0]
        ];
        
        return $reasons[array_rand($reasons)];
    }
    
    public function calculateSimilarity($content1, $content2) {
        $score = 0;
        
        // Comparar géneros
        $genres1 = explode(',', strtolower($content1['genres'] ?? ''));
        $genres2 = explode(',', strtolower($content2['genres'] ?? ''));
        $commonGenres = array_intersect($genres1, $genres2);
        $score += count($commonGenres) * 0.3;
        
        // Comparar años (proximidad)
        $yearDiff = abs($content1['year'] - $content2['year']);
        if ($yearDiff <= 2) $score += 0.2;
        elseif ($yearDiff <= 5) $score += 0.1;
        
        // Comparar ratings
        $ratingDiff = abs($content1['imdb_rating'] - $content2['imdb_rating']);
        if ($ratingDiff <= 0.5) $score += 0.2;
        elseif ($ratingDiff <= 1.0) $score += 0.1;
        
        // Mismo tipo (película/serie)
        if ($content1['type'] === $content2['type']) {
            $score += 0.3;
        }
        
        return min($score, 1.0);
    }
    
    public function generatePersonalizedScore($userId, $content) {
        // Simular algoritmo de puntuación personalizada
        $baseScore = $content['imdb_rating'] / 10;
        
        // Ajustar según preferencias del usuario (simuladas)
        $userPreferences = $this->getUserPreferences($userId);
        
        foreach ($userPreferences as $genre => $weight) {
            if (strpos(strtolower($content['genres'] ?? ''), strtolower($genre)) !== false) {
                $baseScore += ($weight * 0.1);
            }
        }
        
        // Normalizar entre 0 y 1
        return min(max($baseScore, 0), 1);
    }
    
    private function getUserPreferences($userId) {
        // Simular preferencias del usuario
        return [
            'Drama' => 0.8,
            'Acción' => 0.6,
            'Comedia' => 0.7,
            'Thriller' => 0.5,
            'Sci-Fi' => 0.4
        ];
    }
}

// Extender la clase SceneIQ con métodos de recomendaciones
if (method_exists($sceneiq, 'addRecommendationMethods')) {
    $sceneiq->addRecommendationMethods();
} else {
    // Agregar métodos faltantes a la instancia existente
    $sceneiq->getRecommendationReason = function($content) {
        $engine = new RecommendationEngine($this);
        return $engine->getRecommendationReason($content);
    };
    
    $sceneiq->getSimilarContent = function($contentId, $limit = 3) {
        // Obtener contenido similar basado en el ID
        $allContent = $this->getContent(50, 0);
        $targetContent = null;
        
        foreach ($allContent as $item) {
            if ($item['id'] == $contentId) {
                $targetContent = $item;
                break;
            }
        }
        
        if (!$targetContent) return [];
        
        $engine = new RecommendationEngine($this);
        $similarities = [];
        
        foreach ($allContent as $item) {
            if ($item['id'] != $contentId) {
                $similarity = $engine->calculateSimilarity($targetContent, $item);
                $similarities[] = [
                    'content' => $item,
                    'similarity' => $similarity
                ];
            }
        }
        
        // Ordenar por similitud y tomar los mejores
        usort($similarities, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice(array_column($similarities, 'content'), 0, $limit);
    };
    
    $sceneiq->recordRecommendationFeedback = function($userId, $contentId, $feedback) {
        // En una implementación real, esto se guardaría en la base de datos
        error_log("Recommendation feedback: User $userId, Content $contentId, Feedback: $feedback");
        return true;
    };
    
    $sceneiq->generateFreshRecommendations = function($userId) {
        // Generar nuevas recomendaciones
        $content = $this->getContent(20, rand(0, 50));
        
        // Mezclar aleatoriamente para simular "frescura"
        shuffle($content);
        
        return array_slice($content, 0, 10);
    };
    
    $sceneiq->discoverContent = function($params) {
        $content = $this->getContent(100, 0);
        
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
                return $item['year'] >= $params['year_range'][0] && 
                       $item['year'] <= $params['year_range'][1];
            });
        }
        
        if ($params['rating_min'] > 0) {
            $content = array_filter($content, function($item) use ($params) {
                return $item['imdb_rating'] >= $params['rating_min'];
            });
        }
        
        if ($params['type']) {
            $content = array_filter($content, function($item) use ($params) {
                return $item['type'] === $params['type'];
            });
        }
        
        // Limitar resultados
        return array_slice(array_values($content), 0, $params['limit'] ?? 20);
    };
}
?>