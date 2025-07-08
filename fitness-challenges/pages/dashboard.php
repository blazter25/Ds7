<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar autenticación
requireAuth();

$user = getCurrentUser();
$stats = getUserStatistics($user['id']);
$activeChallenges = getUserActiveChallenges($user['id']);

// Obtener actividades recientes
$pdo = getConnection();
$stmt = $pdo->prepare("
    SELECT a.*, c.name as challenge_name
    FROM activities a
    JOIN user_challenges uc ON a.user_challenge_id = uc.id
    JOIN challenges c ON uc.challenge_id = c.id
    WHERE uc.user_id = ?
    ORDER BY a.activity_date DESC, a.created_at DESC
    LIMIT 5
");
$stmt->execute([$user['id']]);
$recentActivities = $stmt->fetchAll();

// Actualizar estadísticas
updateUserStatistics($user['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Desafíos Fitness</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar container">
            <a href="dashboard.php" class="logo">💪 Fitness Challenge</a>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="challenges.php"><i class="fas fa-trophy"></i> Desafíos</a></li>
                <li><a href="progress.php"><i class="fas fa-chart-line"></i> Progreso</a></li>
                <li><a href="statistics.php"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
                <li>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="color: #6b7280;">Hola, <?php echo htmlspecialchars($user['username']); ?></span>
                        <a href="../logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main class="container" style="margin-top: 2rem;">
        <!-- Mensaje de bienvenida -->
        <div class="fade-in">
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">¡Bienvenido de vuelta, <?php echo htmlspecialchars($user['username']); ?>! 👋</h1>
            <p style="color: #6b7280; font-size: 1.125rem;">
                <?php
                $hora = date('H');
                if ($hora < 12) {
                    echo "Buenos días, ¿listo para entrenar?";
                } elseif ($hora < 18) {
                    echo "Buenas tardes, ¡mantén el ritmo!";
                } else {
                    echo "Buenas noches, ¿ya completaste tu entrenamiento de hoy?";
                }
                ?>
            </p>
        </div>

        <!-- Estadísticas principales -->
        <div class="dashboard-grid" style="margin-top: 2rem;">
            <div class="stat-card">
                <i class="fas fa-trophy" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                <div class="stat-number"><?php echo $stats['total_challenges']; ?></div>
                <div class="stat-label">Desafíos Totales</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-check-circle" style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 1rem;"></i>
                <div class="stat-number"><?php echo $stats['completed_challenges']; ?></div>
                <div class="stat-label">Desafíos Completados</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-fire" style="font-size: 2.5rem; color: var(--danger-color); margin-bottom: 1rem;"></i>
                <div class="stat-number"><?php echo number_format($stats['total_calories_burned']); ?></div>
                <div class="stat-label">Calorías Quemadas</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-clock" style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
                <div class="stat-number"><?php echo round($stats['total_workout_time'] / 60); ?>h</div>
                <div class="stat-label">Tiempo Total de Ejercicio</div>
            </div>
        </div>

        <!-- Desafíos activos y actividades recientes -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
            <!-- Desafíos activos -->
            <div class="card">
                <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                    <span><i class="fas fa-running"></i> Desafíos Activos</span>
                    <a href="challenges.php" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-plus"></i> Nuevo
                    </a>
                </h2>
                
                <?php if (empty($activeChallenges)): ?>
                    <p style="text-align: center; color: #6b7280; padding: 2rem 0;">
                        <i class="fas fa-info-circle"></i> No tienes desafíos activos. 
                        <a href="challenges.php" style="color: var(--primary-color);">¡Comienza uno ahora!</a>
                    </p>
                <?php else: ?>
                    <?php foreach ($activeChallenges as $challenge): 
                        $progress = calculateChallengeProgress($challenge['id']);
                    ?>
                        <div class="challenge-card" style="margin-bottom: 1rem;">
                            <div class="challenge-header">
                                <h3 class="challenge-title"><?php echo htmlspecialchars($challenge['name']); ?></h3>
                                <span class="challenge-duration">
                                    <i class="fas fa-calendar"></i> <?php echo $challenge['duration']; ?> días
                                </span>
                            </div>
                            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">
                                Iniciado el <?php echo formatDate($challenge['start_date']); ?>
                            </p>
                            <div class="progress-bar">
                                <div class="progress-fill" data-progress="<?php echo $progress; ?>" style="width: 0%;"></div>
                            </div>
                            <p style="text-align: right; color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">
                                <?php echo $progress; ?>% completado
                            </p>
                            <a href="progress.php?challenge=<?php echo $challenge['id']; ?>" class="btn btn-secondary btn-block" style="margin-top: 1rem;">
                                <i class="fas fa-plus-circle"></i> Registrar actividad
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Actividades recientes -->
            <div class="card">
                <h2 style="margin-bottom: 1.5rem;">
                    <i class="fas fa-history"></i> Actividades Recientes
                </h2>
                
                <?php if (empty($recentActivities)): ?>
                    <p style="text-align: center; color: #6b7280; padding: 2rem 0;">
                        <i class="fas fa-info-circle"></i> No hay actividades registradas aún.
                    </p>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-info">
                                    <div>
                                        <div class="activity-type">
                                            <i class="fas fa-dumbbell"></i> <?php echo htmlspecialchars($activity['activity_type']); ?>
                                        </div>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            <?php echo htmlspecialchars($activity['challenge_name']); ?>
                                        </div>
                                    </div>
                                    <div class="activity-stats">
                                        <span><i class="fas fa-clock"></i> <?php echo $activity['duration']; ?> min</span>
                                        <span><i class="fas fa-fire"></i> <?php echo $activity['calories_burned']; ?> cal</span>
                                        <span><i class="fas fa-calendar"></i> <?php echo formatDate($activity['activity_date']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <a href="progress.php" class="btn btn-secondary btn-block" style="margin-top: 1rem;">
                        Ver todas las actividades
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráfico de progreso semanal -->
        <div class="card" style="margin-top: 2rem;">
            <h2 style="margin-bottom: 1.5rem;">
                <i class="fas fa-chart-line"></i> Progreso Semanal
            </h2>
            <div style="height: 300px;">
                <canvas id="caloriesChart"></canvas>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="dashboard-grid" style="margin-top: 2rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <a href="challenges.php" class="btn btn-primary" style="text-align: center; padding: 1.5rem;">
                <i class="fas fa-plus-circle" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                Nuevo Desafío
            </a>
            <a href="progress.php" class="btn btn-success" style="text-align: center; padding: 1.5rem;">
                <i class="fas fa-dumbbell" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                Registrar Actividad
            </a>
            <a href="statistics.php" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                <i class="fas fa-chart-bar" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                Ver Estadísticas
            </a>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Datos para el gráfico (aquí podrías cargar datos reales desde PHP)
        <?php
        // Obtener datos de los últimos 7 días
        $stmt = $pdo->prepare("
            SELECT DATE(activity_date) as date, SUM(calories_burned) as calories
            FROM activities a
            JOIN user_challenges uc ON a.user_challenge_id = uc.id
            WHERE uc.user_id = ? AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(activity_date)
            ORDER BY date
        ");
        $stmt->execute([$user['id']]);
        $weekData = $stmt->fetchAll();
        
        // Preparar datos para el gráfico
        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('D', strtotime($date));
            $calories = 0;
            foreach ($weekData as $day) {
                if ($day['date'] == $date) {
                    $calories = $day['calories'];
                    break;
                }
            }
            $data[] = $calories;
        }
        ?>
        
        // Sobrescribir la función de inicialización de gráficos con datos reales
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('caloriesChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [{
                            label: 'Calorías quemadas',
                            data: <?php echo json_encode($data); ?>,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value + ' cal';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>