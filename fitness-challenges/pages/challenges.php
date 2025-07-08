<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar autenticación
requireAuth();

$user = getCurrentUser();
$message = '';
$messageType = '';

// Manejar unirse a un desafío
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_challenge'])) {
    $challengeId = (int)$_POST['challenge_id'];
    $pdo = getConnection();
    
    // Verificar si ya está en este desafío
    $stmt = $pdo->prepare("
        SELECT id FROM user_challenges 
        WHERE user_id = ? AND challenge_id = ? AND status = 'active'
    ");
    $stmt->execute([$user['id'], $challengeId]);
    
    if ($stmt->fetch()) {
        $message = 'Ya estás participando en este desafío';
        $messageType = 'error';
    } else {
        // Unirse al desafío
        $stmt = $pdo->prepare("
            INSERT INTO user_challenges (user_id, challenge_id, start_date)
            VALUES (?, ?, CURDATE())
        ");
        $stmt->execute([$user['id'], $challengeId]);
        
        $message = '¡Te has unido al desafío exitosamente!';
        $messageType = 'success';
        
        // Actualizar estadísticas
        updateUserStatistics($user['id']);
    }
}

// Obtener todos los desafíos disponibles
$pdo = getConnection();
$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(DISTINCT uc.user_id) as participants,
           (SELECT COUNT(*) FROM user_challenges 
            WHERE challenge_id = c.id AND user_id = ? AND status = 'active') as is_joined
    FROM challenges c
    LEFT JOIN user_challenges uc ON c.id = uc.challenge_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute([$user['id']]);
$challenges = $stmt->fetchAll();

// Obtener desafíos del usuario
$stmt = $pdo->prepare("
    SELECT uc.*, c.name, c.description, c.duration
    FROM user_challenges uc
    JOIN challenges c ON uc.challenge_id = c.id
    WHERE uc.user_id = ?
    ORDER BY uc.created_at DESC
");
$stmt->execute([$user['id']]);
$userChallenges = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desafíos - Fitness Challenge</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar container">
            <a href="dashboard.php" class="logo">💪 Fitness Challenge</a>
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="challenges.php" class="active"><i class="fas fa-trophy"></i> Desafíos</a></li>
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
        <!-- Título y descripción -->
        <div class="fade-in">
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">
                <i class="fas fa-trophy"></i> Desafíos Disponibles
            </h1>
            <p style="color: #6b7280; font-size: 1.125rem;">
                Únete a un desafío y transforma tu vida. ¡El momento es ahora!
            </p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-top: 1rem;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div style="margin-top: 2rem; margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem; border-bottom: 2px solid #e5e7eb;">
                <button class="tab-button active" onclick="showTab('available')">
                    <i class="fas fa-list"></i> Desafíos Disponibles
                </button>
                <button class="tab-button" onclick="showTab('my-challenges')">
                    <i class="fas fa-user"></i> Mis Desafíos
                </button>
            </div>
        </div>

        <!-- Tab: Desafíos disponibles -->
        <div id="available-tab" class="tab-content">
            <!-- Filtros -->
            <div style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn btn-secondary filter-btn active" onclick="filterChallenges('all')">
                    Todos
                </button>
                <button class="btn btn-secondary filter-btn" onclick="filterChallenges('cardio')">
                    <i class="fas fa-running"></i> Cardio
                </button>
                <button class="btn btn-secondary filter-btn" onclick="filterChallenges('strength')">
                    <i class="fas fa-dumbbell"></i> Fuerza
                </button>
                <button class="btn btn-secondary filter-btn" onclick="filterChallenges('flexibility')">
                    <i class="fas fa-spa"></i> Flexibilidad
                </button>
                <button class="btn btn-secondary filter-btn" onclick="filterChallenges('mixed')">
                    <i class="fas fa-random"></i> Mixto
                </button>
            </div>

            <!-- Grid de desafíos -->
            <div class="challenges-container grid-view">
                <?php foreach ($challenges as $challenge): 
                    // Determinar categoría basada en el nombre
                    $category = 'mixed';
                    if (stripos($challenge['name'], 'cardio') !== false || stripos($challenge['name'], 'corr') !== false) {
                        $category = 'cardio';
                    } elseif (stripos($challenge['name'], 'fuerza') !== false || stripos($challenge['name'], 'pesa') !== false) {
                        $category = 'strength';
                    } elseif (stripos($challenge['name'], 'yoga') !== false || stripos($challenge['name'], 'flexibilidad') !== false) {
                        $category = 'flexibility';
                    }
                    
                    // Iconos según categoría
                    $icon = 'fas fa-trophy';
                    if ($category === 'cardio') $icon = 'fas fa-running';
                    elseif ($category === 'strength') $icon = 'fas fa-dumbbell';
                    elseif ($category === 'flexibility') $icon = 'fas fa-spa';
                ?>
                    <div class="card challenge-card" data-category="<?php echo $category; ?>" style="border-left-color: 
                        <?php 
                        echo $category === 'cardio' ? '#ef4444' : 
                             ($category === 'strength' ? '#8b5cf6' : 
                             ($category === 'flexibility' ? '#10b981' : '#6366f1'));
                        ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <i class="<?php echo $icon; ?>" style="font-size: 2rem; color: 
                                <?php 
                                echo $category === 'cardio' ? '#ef4444' : 
                                     ($category === 'strength' ? '#8b5cf6' : 
                                     ($category === 'flexibility' ? '#10b981' : '#6366f1'));
                                ?>"></i>
                            <span class="challenge-duration">
                                <i class="fas fa-calendar"></i> <?php echo $challenge['duration']; ?> días
                            </span>
                        </div>
                        
                        <h3 class="challenge-title" style="margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($challenge['name']); ?>
                        </h3>
                        
                        <p style="color: #6b7280; margin-bottom: 1rem; font-size: 0.875rem;">
                            <?php echo htmlspecialchars($challenge['description']); ?>
                        </p>
                        
                        <?php if ($challenge['objectives']): ?>
                            <div style="margin-bottom: 1rem;">
                                <p style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    <i class="fas fa-bullseye"></i> Objetivos:
                                </p>
                                <ul style="list-style: none; padding-left: 1rem; font-size: 0.875rem; color: #6b7280;">
                                    <?php 
                                    $objectives = explode(',', $challenge['objectives']);
                                    foreach ($objectives as $objective): 
                                    ?>
                                        <li style="margin-bottom: 0.25rem;">
                                            <i class="fas fa-check" style="color: #10b981; margin-right: 0.5rem;"></i>
                                            <?php echo trim($objective); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; justify-content: between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                            <div style="flex: 1;">
                                <p style="font-size: 0.875rem; color: #6b7280;">
                                    <i class="fas fa-users"></i> <?php echo $challenge['participants']; ?> participantes
                                </p>
                            </div>
                            
                            <?php if ($challenge['is_joined']): ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-check"></i> Ya estás participando
                                </button>
                            <?php else: ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="challenge_id" value="<?php echo $challenge['id']; ?>">
                                    <button type="submit" name="join_challenge" class="btn btn-primary">
                                        <i class="fas fa-plus-circle"></i> Unirme
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab: Mis desafíos -->
        <div id="my-challenges-tab" class="tab-content" style="display: none;">
            <?php if (empty($userChallenges)): ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-info-circle" style="font-size: 3rem; color: #6b7280; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280; font-size: 1.125rem;">
                        Aún no te has unido a ningún desafío.
                    </p>
                    <button class="btn btn-primary" style="margin-top: 1rem;" onclick="showTab('available')">
                        <i class="fas fa-search"></i> Explorar desafíos
                    </button>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($userChallenges as $userChallenge): 
                        $progress = calculateChallengeProgress($userChallenge['id']);
                        $statusColors = [
                            'active' => '#10b981',
                            'completed' => '#6366f1',
                            'abandoned' => '#ef4444'
                        ];
                        $statusIcons = [
                            'active' => 'play-circle',
                            'completed' => 'check-circle',
                            'abandoned' => 'times-circle'
                        ];
                    ?>
                        <div class="card" style="border-left: 5px solid <?php echo $statusColors[$userChallenge['status']]; ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <h3 style="margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($userChallenge['name']); ?>
                                    </h3>
                                    <p style="color: #6b7280; margin-bottom: 1rem;">
                                        <?php echo htmlspecialchars($userChallenge['description']); ?>
                                    </p>
                                    
                                    <div style="display: flex; gap: 2rem; margin-bottom: 1rem;">
                                        <span style="font-size: 0.875rem;">
                                            <i class="fas fa-calendar-alt"></i> 
                                            Inicio: <?php echo formatDate($userChallenge['start_date']); ?>
                                        </span>
                                        <span style="font-size: 0.875rem;">
                                            <i class="fas fa-hourglass-half"></i> 
                                            Duración: <?php echo $userChallenge['duration']; ?> días
                                        </span>
                                        <span style="font-size: 0.875rem; color: <?php echo $statusColors[$userChallenge['status']]; ?>;">
                                            <i class="fas fa-<?php echo $statusIcons[$userChallenge['status']]; ?>"></i> 
                                            <?php echo ucfirst($userChallenge['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($userChallenge['status'] === 'active'): ?>
                                        <div class="progress-bar">
                                            <div class="progress-fill" data-progress="<?php echo $progress; ?>" style="width: 0%;"></div>
                                        </div>
                                        <p style="text-align: right; margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">
                                            <?php echo $progress; ?>% completado
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <?php if ($userChallenge['status'] === 'active'): ?>
                                        <a href="progress.php?challenge=<?php echo $userChallenge['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Registrar actividad
                                        </a>
                                        <button class="btn btn-danger" onclick="if(confirm('¿Estás seguro de abandonar este desafío?')) abandonChallenge(<?php echo $userChallenge['id']; ?>)">
                                            <i class="fas fa-times"></i> Abandonar
                                        </button>
                                    <?php elseif ($userChallenge['status'] === 'completed'): ?>
                                        <span class="btn btn-success" style="cursor: default;">
                                            <i class="fas fa-trophy"></i> ¡Completado!
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Función para cambiar tabs
        function showTab(tabName) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remover clase active de todos los botones
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar tab seleccionado
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Activar botón correspondiente
            event.target.classList.add('active');
        }
        
        // Función para abandonar desafío
        async function abandonChallenge(challengeId) {
            try {
                const response = await fetch('/api/rest/activities.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'abandon_challenge',
                        challenge_id: challengeId
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al abandonar el desafío');
                }
            } catch (error) {
                alert('Error al procesar la solicitud');
            }
        }
        
        // Estilos para tabs
        const style = document.createElement('style');
        style.textContent = `
            .tab-button {
                background: none;
                border: none;
                padding: 1rem 2rem;
                font-size: 1rem;
                font-weight: 600;
                color: #6b7280;
                cursor: pointer;
                position: relative;
                transition: all 0.3s ease;
            }
            
            .tab-button:hover {
                color: var(--primary-color);
            }
            
            .tab-button.active {
                color: var(--primary-color);
            }
            
            .tab-button.active::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 0;
                right: 0;
                height: 2px;
                background: var(--primary-color);
            }
            
            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
            
            .filter-btn.active {
                background: var(--primary-color);
                color: white;
            }
            
            .challenges-container {
                display: grid;
                gap: 1.5rem;
            }
            
            .challenges-container.grid-view {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
            
            .challenges-container.list-view {
                grid-template-columns: 1fr;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>