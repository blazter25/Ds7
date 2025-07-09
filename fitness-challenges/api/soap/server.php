<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Definir la clase del servicio SOAP
class ExerciseService {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getConnection();
    }
    
    /**
     * Obtener información sobre ejercicios desde la base de datos
     * @param string $type Tipo de ejercicio (cardio, strength, flexibility, all)
     * @return array
     */
    public function getExerciseInfo($type = 'all') {
        // Información estática de ejercicios
        $exercises = [
            'cardio' => [
                ['name' => 'Correr', 'calories_per_minute' => 10, 'benefits' => 'Mejora la salud cardiovascular, quema calorías, fortalece las piernas', 'recommendations' => 'Comenzar con 20-30 minutos, 3 veces por semana', 'equipment' => 'Zapatillas deportivas'],
                ['name' => 'Ciclismo', 'calories_per_minute' => 8, 'benefits' => 'Bajo impacto, mejora resistencia, tonifica piernas', 'recommendations' => 'Sesiones de 45-60 minutos, mantener cadencia de 80-100 rpm', 'equipment' => 'Bicicleta, casco'],
                ['name' => 'Natación', 'calories_per_minute' => 11, 'benefits' => 'Ejercicio de cuerpo completo, sin impacto articular', 'recommendations' => 'Alternar estilos, descansar entre series', 'equipment' => 'Traje de baño, gafas']
            ],
            'strength' => [
                ['name' => 'Pesas', 'calories_per_minute' => 6, 'benefits' => 'Aumento de masa muscular, mejora metabolismo, fortalece huesos', 'recommendations' => 'Trabajar grupos musculares alternados, descanso de 48h entre sesiones del mismo grupo', 'equipment' => 'Mancuernas, barras, banco'],
                ['name' => 'CrossFit', 'calories_per_minute' => 12, 'benefits' => 'Acondicionamiento físico completo, mejora fuerza y resistencia', 'recommendations' => 'Supervisión inicial recomendada, calentar adecuadamente', 'equipment' => 'Variado: kettlebells, cuerdas, cajones']
            ],
            'flexibility' => [
                ['name' => 'Yoga', 'calories_per_minute' => 3, 'benefits' => 'Mejora flexibilidad, reduce estrés, fortalece core', 'recommendations' => 'Práctica regular, respiración consciente', 'equipment' => 'Mat de yoga, bloques opcionales'],
                ['name' => 'Pilates', 'calories_per_minute' => 4, 'benefits' => 'Fortalece core, mejora postura, tonifica', 'recommendations' => 'Concentración en movimientos controlados', 'equipment' => 'Mat, pelota opcional']
            ]
        ];
        
        if ($type === 'all') {
            return array_merge($exercises['cardio'], $exercises['strength'], $exercises['flexibility']);
        } elseif (isset($exercises[$type])) {
            return $exercises[$type];
        } else {
            return ['error' => 'Tipo de ejercicio no válido'];
        }
    }
    
    /**
     * Obtener desafíos desde la base de datos
     * @param int $userId ID del usuario (opcional)
     * @return array
     */
    public function getChallengesFromDB($userId = null) {
        try {
            if ($userId) {
                // Obtener desafíos del usuario específico
                $stmt = $this->pdo->prepare("
                    SELECT c.*, uc.status, uc.progress, uc.start_date,
                           COUNT(DISTINCT a.id) as activity_count,
                           SUM(a.calories_burned) as total_calories
                    FROM challenges c
                    JOIN user_challenges uc ON c.id = uc.challenge_id
                    LEFT JOIN activities a ON a.user_challenge_id = uc.id
                    WHERE uc.user_id = ?
                    GROUP BY c.id, uc.id
                ");
                $stmt->execute([$userId]);
            } else {
                // Obtener todos los desafíos
                $stmt = $this->pdo->prepare("
                    SELECT c.*, COUNT(DISTINCT uc.user_id) as participants
                    FROM challenges c
                    LEFT JOIN user_challenges uc ON c.id = uc.challenge_id
                    GROUP BY c.id
                ");
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ['error' => 'Error al obtener desafíos: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener estadísticas de usuario
     * @param int $userId ID del usuario
     * @return array
     */
    public function getUserStats($userId) {
        try {
            // Estadísticas generales
            $stmt = $this->pdo->prepare("SELECT * FROM statistics WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Actividades por tipo
            $stmt = $this->pdo->prepare("
                SELECT activity_type, COUNT(*) as count, 
                       SUM(duration) as total_duration,
                       SUM(calories_burned) as total_calories
                FROM activities a
                JOIN user_challenges uc ON a.user_challenge_id = uc.id
                WHERE uc.user_id = ?
                GROUP BY activity_type
            ");
            $stmt->execute([$userId]);
            $activityTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Progreso mensual
            $stmt = $this->pdo->prepare("
                SELECT DATE_FORMAT(activity_date, '%Y-%m') as month,
                       COUNT(*) as activities,
                       SUM(calories_burned) as calories
                FROM activities a
                JOIN user_challenges uc ON a.user_challenge_id = uc.id
                WHERE uc.user_id = ?
                GROUP BY DATE_FORMAT(activity_date, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12
            ");
            $stmt->execute([$userId]);
            $monthlyProgress = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'general' => $stats,
                'by_activity_type' => $activityTypes,
                'monthly_progress' => $monthlyProgress
            ];
        } catch (Exception $e) {
            return ['error' => 'Error al obtener estadísticas: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener actividades del usuario
     * @param int $userId ID del usuario
     * @param string $startDate Fecha inicio (opcional)
     * @param string $endDate Fecha fin (opcional)
     * @return array
     */
    public function getUserActivities($userId, $startDate = null, $endDate = null) {
        try {
            $query = "
                SELECT a.*, c.name as challenge_name
                FROM activities a
                JOIN user_challenges uc ON a.user_challenge_id = uc.id
                JOIN challenges c ON uc.challenge_id = c.id
                WHERE uc.user_id = ?
            ";
            $params = [$userId];
            
            if ($startDate && $endDate) {
                $query .= " AND a.activity_date BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }
            
            $query .= " ORDER BY a.activity_date DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ['error' => 'Error al obtener actividades: ' . $e->getMessage()];
        }
    }
    
    /**
     * Registrar nueva actividad
     * @param int $userId ID del usuario
     * @param int $challengeId ID del desafío
     * @param string $activityType Tipo de actividad
     * @param int $duration Duración en minutos
     * @param int $calories Calorías quemadas
     * @param string $activityDate Fecha de la actividad
     * @param string $notes Notas opcionales
     * @return array
     */
    public function registerActivity($userId, $challengeId, $activityType, $duration, $calories, $activityDate, $notes = '') {
        try {
            // Verificar que el usuario esté en el desafío
            $stmt = $this->pdo->prepare("
                SELECT id FROM user_challenges 
                WHERE user_id = ? AND challenge_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId, $challengeId]);
            $userChallenge = $stmt->fetch();
            
            if (!$userChallenge) {
                return ['success' => false, 'message' => 'Usuario no está en este desafío'];
            }
            
            // Insertar actividad
            $stmt = $this->pdo->prepare("
                INSERT INTO activities (user_challenge_id, activity_type, duration, calories_burned, activity_date, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userChallenge['id'],
                $activityType,
                $duration,
                $calories,
                $activityDate,
                $notes
            ]);
            
            // Actualizar progreso y estadísticas
            calculateChallengeProgress($userChallenge['id']);
            updateUserStatistics($userId);
            
            return [
                'success' => true,
                'message' => 'Actividad registrada exitosamente',
                'activity_id' => $this->pdo->lastInsertId()
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al registrar actividad: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener rutinas de entrenamiento recomendadas
     * @param string $goal Objetivo (weight_loss, muscle_gain, endurance, general)
     * @param string $level Nivel (beginner, intermediate, advanced)
     * @return array
     */
    public function getWorkoutRoutines($goal = 'general', $level = 'beginner') {
        $routines = [
            'weight_loss' => [
                'beginner' => [
                    'name' => 'Rutina Quema Grasa Principiante',
                    'duration' => '4 semanas',
                    'frequency' => '4 días/semana',
                    'exercises' => [
                        'Lunes' => ['Caminar 30 min', 'Abdominales 3x10', 'Plancha 3x30seg'],
                        'Martes' => ['Pesas ligeras cuerpo completo', '20 min'],
                        'Miércoles' => ['Descanso activo - Yoga 20 min'],
                        'Jueves' => ['Intervalos caminar/trotar 25 min'],
                        'Viernes' => ['Circuito de fuerza 3 rondas'],
                        'Sábado' => ['Actividad recreativa 45 min'],
                        'Domingo' => ['Descanso']
                    ]
                ],
                'intermediate' => [
                    'name' => 'Rutina Quema Grasa Intermedio',
                    'duration' => '6 semanas',
                    'frequency' => '5 días/semana',
                    'exercises' => [
                        'Lunes' => ['HIIT 30 min', 'Core 15 min'],
                        'Martes' => ['Pesas - Tren superior'],
                        'Miércoles' => ['Correr 40 min ritmo moderado'],
                        'Jueves' => ['Pesas - Tren inferior'],
                        'Viernes' => ['Circuito metabólico 45 min'],
                        'Sábado' => ['Actividad larga 60+ min'],
                        'Domingo' => ['Yoga restaurativo']
                    ]
                ]
            ],
            'muscle_gain' => [
                'beginner' => [
                    'name' => 'Rutina Ganancia Muscular Principiante',
                    'duration' => '8 semanas',
                    'frequency' => '3 días/semana',
                    'exercises' => [
                        'Lunes' => ['Pecho y Tríceps', 'Press banca 3x10', 'Fondos asistidos 3x8'],
                        'Miércoles' => ['Espalda y Bíceps', 'Dominadas asistidas 3x8', 'Remo 3x10'],
                        'Viernes' => ['Piernas y Hombros', 'Sentadillas 3x10', 'Press militar 3x10']
                    ]
                ]
            ]
        ];
        
        if (isset($routines[$goal][$level])) {
            return $routines[$goal][$level];
        } else {
            return [
                'name' => 'Rutina General',
                'duration' => '4 semanas',
                'frequency' => '3 días/semana',
                'exercises' => [
                    'Día 1' => ['Cardio 30 min', 'Fuerza cuerpo completo'],
                    'Día 2' => ['Descanso activo'],
                    'Día 3' => ['HIIT 20 min', 'Core'],
                    'Día 4' => ['Descanso'],
                    'Día 5' => ['Actividad favorita 45 min']
                ]
            ];
        }
    }
    
    /**
     * Calcular calorías quemadas
     * @param string $activity Tipo de actividad
     * @param int $duration Duración en minutos
     * @param float $weight Peso en kg (opcional)
     * @return array
     */
    public function calculateCalories($activity, $duration, $weight = 70) {
        // Calorías base por minuto para persona de 70kg
        $caloriesPerMinute = [
            'Correr' => 10,
            'Caminar' => 4,
            'Ciclismo' => 8,
            'Natación' => 11,
            'Pesas' => 6,
            'Yoga' => 3,
            'CrossFit' => 12,
            'Pilates' => 4,
            'Baile' => 7,
            'Boxeo' => 13
        ];
        
        $baseCalories = isset($caloriesPerMinute[$activity]) ? $caloriesPerMinute[$activity] : 7;
        
        // Ajustar por peso
        $weightFactor = $weight / 70;
        $totalCalories = round($baseCalories * $duration * $weightFactor);
        
        return [
            'activity' => $activity,
            'duration' => $duration,
            'weight' => $weight,
            'calories_burned' => $totalCalories,
            'calories_per_minute' => round($baseCalories * $weightFactor, 1)
        ];
    }
    
    /**
     * Obtener recomendaciones nutricionales
     * @param string $goal Objetivo
     * @param float $weight Peso en kg
     * @param string $activity_level Nivel de actividad (sedentary, moderate, active, very_active)
     * @return array
     */
    public function getNutritionRecommendations($goal, $weight, $activity_level = 'moderate') {
        // Multiplicadores de actividad
        $activityMultipliers = [
            'sedentary' => 1.2,
            'moderate' => 1.5,
            'active' => 1.7,
            'very_active' => 1.9
        ];
        
        $multiplier = $activityMultipliers[$activity_level] ?? 1.5;
        
        // Calcular calorías base (aproximación)
        $basalMetabolicRate = $weight * 24; // Simplificado
        $dailyCalories = round($basalMetabolicRate * $multiplier);
        
        // Ajustar según objetivo
        $recommendations = [
            'weight_loss' => [
                'calories' => $dailyCalories - 500,
                'protein_g' => round($weight * 1.6),
                'carbs_g' => round(($dailyCalories - 500) * 0.4 / 4),
                'fats_g' => round(($dailyCalories - 500) * 0.3 / 9),
                'water_ml' => round($weight * 35),
                'tips' => [
                    'Déficit calórico moderado de 500 cal/día',
                    'Priorizar proteínas para mantener masa muscular',
                    'Carbohidratos complejos antes del ejercicio',
                    'Grasas saludables para saciedad'
                ]
            ],
            'muscle_gain' => [
                'calories' => $dailyCalories + 300,
                'protein_g' => round($weight * 2),
                'carbs_g' => round(($dailyCalories + 300) * 0.45 / 4),
                'fats_g' => round(($dailyCalories + 300) * 0.25 / 9),
                'water_ml' => round($weight * 40),
                'tips' => [
                    'Superávit calórico de 300-500 cal/día',
                    'Alta ingesta de proteínas',
                    'Carbohidratos para energía y recuperación',
                    'Comidas frecuentes cada 3-4 horas'
                ]
            ],
            'maintenance' => [
                'calories' => $dailyCalories,
                'protein_g' => round($weight * 1.4),
                'carbs_g' => round($dailyCalories * 0.45 / 4),
                'fats_g' => round($dailyCalories * 0.3 / 9),
                'water_ml' => round($weight * 35),
                'tips' => [
                    'Balance energético equilibrado',
                    'Variedad en la alimentación',
                    'Hidratación constante',
                    'Comidas regulares'
                ]
            ]
        ];
        
        return $recommendations[$goal] ?? $recommendations['maintenance'];
    }
}

// Configurar el servidor SOAP
$options = [
    'uri' => 'http://localhost/fitness-challenges/api/soap/',
    'encoding' => 'UTF-8'
];

// Crear el servidor SOAP
$server = new SoapServer(null, $options);
$server->setClass('ExerciseService');

// Manejar la petición SOAP
try {
    $server->handle();
} catch (Exception $e) {
    $server->fault('Server', $e->getMessage());
}
?>