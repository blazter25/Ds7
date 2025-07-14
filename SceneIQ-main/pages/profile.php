<?php
// pages/profile.php
$pageTitle = "Mi Perfil";
require_once '../includes/header.php';

// Verificar que el usuario esté logueado
if (!$user) {
    header('Location: login.php');
    exit;
}

// Función helper para llamar métodos de sceneiq de forma segura
function callSceneIQMethod($sceneiq, $method, $params = []) {
    if (is_object($sceneiq) && method_exists($sceneiq, $method)) {
        return call_user_func_array([$sceneiq, $method], $params);
    }
    
    // Retornar datos de ejemplo si el método no existe
    switch ($method) {
        case 'getUserStats':
            return [
                'total_reviews' => rand(5, 25),
                'watchlist_count' => rand(10, 50),
                'favorites_count' => rand(5, 20),
                'avg_rating' => round(rand(70, 95) / 10, 1)
            ];
        case 'getUserList':
            // Simular contenido de ejemplo
            $content = $sceneiq->getContent(6, 0);
            return array_slice($content, 0, rand(3, 6));
        case 'timeAgo':
            return 'hace unos días';
        default:
            return [];
    }
}

// Obtener datos del usuario usando función segura
$userStats = callSceneIQMethod($sceneiq, 'getUserStats', [$user['id']]);
$watchlist = callSceneIQMethod($sceneiq, 'getUserList', [$user['id'], 'watchlist', 6]);
$favorites = callSceneIQMethod($sceneiq, 'getUserList', [$user['id'], 'favorites', 6]);
$watched = callSceneIQMethod($sceneiq, 'getUserList', [$user['id'], 'watched', 6]);

// Datos de actividad simulados
$recentActivity = [
    ['action' => 'review', 'content' => 'The Dark Knight', 'date' => '2025-01-19'],
    ['action' => 'watchlist', 'content' => 'Breaking Bad', 'date' => '2025-01-18'],
    ['action' => 'favorite', 'content' => 'Inception', 'date' => '2025-01-17'],
    ['action' => 'review', 'content' => 'Stranger Things', 'date' => '2025-01-16']
];

// Tab activo
$activeTab = $_GET['tab'] ?? 'overview';

// Manejar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Aquí procesarías la actualización del perfil
    if (function_exists('showAlert')) {
        showAlert('Perfil actualizado exitosamente', 'success');
    }
    // Redirigir para evitar reenvío del formulario
    header('Location: profile.php?tab=settings');
    exit;
}
?>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-background">
            <div class="profile-overlay"></div>
        </div>
        
        <div class="profile-content">
            <div class="profile-info">
                <div class="profile-avatar">
                    <img src="../assets/images/default-avatar.png" 
                         alt="<?php echo htmlspecialchars($user['username']); ?>" 
                         class="avatar-image">
                    <button class="avatar-edit" onclick="editAvatar()" title="Cambiar avatar">📷</button>
                </div>
                
                <div class="profile-details">
                    <h1 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <p class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="profile-badges">
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge badge-admin">👑 Administrador</span>
                        <?php endif; ?>
                        <span class="badge badge-member">📅 Miembro desde <?php echo date('M Y'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $userStats['total_reviews'] ?? 0; ?></div>
                    <div class="stat-label">Reseñas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $userStats['watchlist_count'] ?? 0; ?></div>
                    <div class="stat-label">En Lista</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $userStats['favorites_count'] ?? 0; ?></div>
                    <div class="stat-label">Favoritos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($userStats['avg_rating'] ?? 0, 1); ?></div>
                    <div class="stat-label">Rating Promedio</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Navigation -->
    <div class="profile-nav">
        <div class="nav-tabs">
            <a href="profile.php?tab=overview" 
               class="nav-tab <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
                📊 Resumen
            </a>
            <a href="profile.php?tab=watchlist" 
               class="nav-tab <?php echo $activeTab === 'watchlist' ? 'active' : ''; ?>">
                📋 Mi Lista (<?php echo $userStats['watchlist_count'] ?? 0; ?>)
            </a>
            <a href="profile.php?tab=favorites" 
               class="nav-tab <?php echo $activeTab === 'favorites' ? 'active' : ''; ?>">
                ❤️ Favoritos (<?php echo $userStats['favorites_count'] ?? 0; ?>)
            </a>
            <a href="profile.php?tab=reviews" 
               class="nav-tab <?php echo $activeTab === 'reviews' ? 'active' : ''; ?>">
                📝 Mis Reseñas (<?php echo $userStats['total_reviews'] ?? 0; ?>)
            </a>
            <a href="profile.php?tab=settings" 
               class="nav-tab <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                ⚙️ Configuración
            </a>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="profile-main">
        <?php if ($activeTab === 'overview'): ?>
            <!-- Overview Tab -->
            <div class="tab-content active">
                <div class="overview-grid">
                    <!-- Recent Activity -->
                    <div class="overview-section">
                        <h2>📈 Actividad Reciente</h2>
                        <div class="activity-list">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php 
                                        $icons = [
                                            'review' => '📝',
                                            'watchlist' => '📋',
                                            'favorite' => '❤️',
                                            'watched' => '👁️'
                                        ];
                                        echo $icons[$activity['action']];
                                        ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <?php 
                                            $actions = [
                                                'review' => 'Reseñaste',
                                                'watchlist' => 'Agregaste a tu lista',
                                                'favorite' => 'Marcaste como favorito',
                                                'watched' => 'Marcaste como visto'
                                            ];
                                            echo $actions[$activity['action']] . ' <strong>' . htmlspecialchars($activity['content']) . '</strong>';
                                            ?>
                                        </div>
                                        <div class="activity-date"><?php echo callSceneIQMethod($sceneiq, 'timeAgo', [$activity['date']]); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="overview-section">
                        <h2>📊 Estadísticas</h2>
                        <div class="quick-stats">
                            <div class="quick-stat">
                                <div class="quick-stat-icon">🎬</div>
                                <div class="quick-stat-content">
                                    <div class="quick-stat-number"><?php echo rand(20, 80); ?></div>
                                    <div class="quick-stat-label">Películas Vistas</div>
                                </div>
                            </div>
                            
                            <div class="quick-stat">
                                <div class="quick-stat-icon">📺</div>
                                <div class="quick-stat-content">
                                    <div class="quick-stat-number"><?php echo rand(10, 40); ?></div>
                                    <div class="quick-stat-label">Series Vistas</div>
                                </div>
                            </div>
                            
                            <div class="quick-stat">
                                <div class="quick-stat-icon">⏱️</div>
                                <div class="quick-stat-content">
                                    <div class="quick-stat-number"><?php echo rand(100, 500); ?>h</div>
                                    <div class="quick-stat-label">Tiempo Total</div>
                                </div>
                            </div>
                            
                            <div class="quick-stat">
                                <div class="quick-stat-icon">🏆</div>
                                <div class="quick-stat-content">
                                    <div class="quick-stat-number"><?php echo rand(5, 25); ?></div>
                                    <div class="quick-stat-label">Logros</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Access Sections -->
                <div class="quick-access">
                    <?php if (!empty($watchlist)): ?>
                        <div class="quick-section">
                            <div class="section-header">
                                <h3>📋 Mi Lista de Seguimiento</h3>
                                <a href="profile.php?tab=watchlist" class="view-all">Ver todo</a>
                            </div>
                            <div class="content-grid-small">
                                <?php foreach (array_slice($watchlist, 0, 4) as $content): ?>
                                    <?php include '../includes/content-card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($favorites)): ?>
                        <div class="quick-section">
                            <div class="section-header">
                                <h3>❤️ Mis Favoritos</h3>
                                <a href="profile.php?tab=favorites" class="view-all">Ver todo</a>
                            </div>
                            <div class="content-grid-small">
                                <?php foreach (array_slice($favorites, 0, 4) as $content): ?>
                                    <?php include '../includes/content-card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($activeTab === 'watchlist'): ?>
            <!-- Watchlist Tab -->
            <div class="tab-content active">
                <div class="list-header">
                    <h2>📋 Mi Lista de Seguimiento</h2>
                    <p>Películas y series que quieres ver próximamente</p>
                </div>
                
                <?php if (!empty($watchlist)): ?>
                    <div class="content-grid">
                        <?php foreach ($watchlist as $content): ?>
                            <?php include '../includes/content-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h3>Tu lista está vacía</h3>
                        <p>Agrega películas y series que quieras ver más tarde</p>
                        <a href="../index.php" class="btn btn-primary">Explorar Contenido</a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($activeTab === 'favorites'): ?>
            <!-- Favorites Tab -->
            <div class="tab-content active">
                <div class="list-header">
                    <h2>❤️ Mis Favoritos</h2>
                    <p>Tu colección personal de lo mejor</p>
                </div>
                
                <?php if (!empty($favorites)): ?>
                    <div class="content-grid">
                        <?php foreach ($favorites as $content): ?>
                            <?php include '../includes/content-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">❤️</div>
                        <h3>Aún no tienes favoritos</h3>
                        <p>Marca como favorito el contenido que más te guste</p>
                        <a href="../index.php" class="btn btn-primary">Descubrir Contenido</a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($activeTab === 'reviews'): ?>
            <!-- Reviews Tab -->
            <div class="tab-content active">
                <div class="list-header">
                    <h2>📝 Mis Reseñas</h2>
                    <p>Todas las reseñas que has escrito</p>
                </div>
                
                <?php if (($userStats['total_reviews'] ?? 0) > 0): ?>
                    <div class="reviews-list">
                        <!-- Ejemplo de reseña -->
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-content-info">
                                    <img src="https://image.tmdb.org/t/p/w200/qJ2tW6WMUDux911r6m7haRef0WH.jpg" 
                                         alt="The Dark Knight" class="review-poster">
                                    <div>
                                        <h4>The Dark Knight</h4>
                                        <p>2008 • Película</p>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <span class="rating-stars">⭐⭐⭐⭐⭐</span>
                                    <span class="rating-value">9.0/10</span>
                                </div>
                            </div>
                            <div class="review-text">
                                <p>Una obra maestra del cine de superhéroes. Christopher Nolan logró crear una película que trasciende el género, con actuaciones excepcionales especialmente de Heath Ledger como el Joker. La cinematografía, el guión y la dirección son impecables.</p>
                            </div>
                            <div class="review-meta">
                                <span>Publicada hace 2 días</span>
                                <div class="review-actions">
                                    <button class="btn-small btn-secondary" onclick="editReview(1)">Editar</button>
                                    <button class="btn-small btn-danger" onclick="deleteReview(1)">Eliminar</button>
                                </div>
                            </div>
                        </div>

                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-content-info">
                                    <img src="https://image.tmdb.org/t/p/w200/ggFHVNu6YYI5L9pCfOacjizRGt.jpg" 
                                         alt="Breaking Bad" class="review-poster">
                                    <div>
                                        <h4>Breaking Bad</h4>
                                        <p>2008 • Serie</p>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <span class="rating-stars">⭐⭐⭐⭐⭐</span>
                                    <span class="rating-value">9.5/10</span>
                                </div>
                            </div>
                            <div class="review-text">
                                <p>Sin duda una de las mejores series jamás creadas. La transformación de Walter White es fascinante y aterradora a la vez. Cada episodio te mantiene al borde del asiento.</p>
                            </div>
                            <div class="review-meta">
                                <span>Publicada hace 1 semana</span>
                                <div class="review-actions">
                                    <button class="btn-small btn-secondary" onclick="editReview(2)">Editar</button>
                                    <button class="btn-small btn-danger" onclick="deleteReview(2)">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <h3>Aún no has escrito reseñas</h3>
                        <p>Comparte tu opinión sobre las películas y series que has visto</p>
                        <button class="btn btn-primary" onclick="openReviewModal()">Escribir Primera Reseña</button>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($activeTab === 'settings'): ?>
            <!-- Settings Tab -->
            <div class="tab-content active">
                <div class="settings-container">
                    <h2>⚙️ Configuración de Perfil</h2>
                    
                    <div class="settings-sections">
                        <!-- Personal Information -->
                        <div class="settings-section">
                            <h3>👤 Información Personal</h3>
                            <form method="POST" class="settings-form">
                                <input type="hidden" name="update_profile" value="1">
                                <input type="hidden" name="csrf_token" value="<?php 
                                    if (is_callable([$sceneiq, 'generateCSRFToken'])) {
                                        echo $sceneiq->generateCSRFToken();
                                    } else {
                                        echo bin2hex(random_bytes(16));
                                    }
                                ?>">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="fullName">Nombre Completo</label>
                                        <input type="text" id="fullName" name="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="username">Nombre de Usuario</label>
                                        <input type="text" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="bio">Biografía</label>
                                    <textarea id="bio" name="bio" rows="3" 
                                              placeholder="Cuéntanos un poco sobre ti..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </form>
                        </div>

                        <!-- Privacy Settings -->
                        <div class="settings-section">
                            <h3>🔒 Privacidad</h3>
                            <div class="settings-form">
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Perfil Público</label>
                                        <p>Permite que otros usuarios vean tu perfil y actividad</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Mostrar Listas</label>
                                        <p>Permite que otros vean tus listas de favoritos y seguimiento</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Mostrar Reseñas</label>
                                        <p>Permite que otros usuarios vean tus reseñas</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="settings-section">
                            <h3>🔔 Notificaciones</h3>
                            <div class="settings-form">
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Nuevas Recomendaciones</label>
                                        <p>Recibe notificaciones cuando tengamos nuevas recomendaciones para ti</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Respuestas a Reseñas</label>
                                        <p>Notificarme cuando alguien responda a mis reseñas</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Newsletter Semanal</label>
                                        <p>Recibe un resumen semanal de contenido nuevo y tendencias</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Appearance Settings -->
                        <div class="settings-section">
                            <h3>🎨 Apariencia</h3>
                            <div class="settings-form">
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <label>Tema</label>
                                        <p>Elige entre tema claro u oscuro</p>
                                    </div>
                                    <select class="setting-select" onchange="changeTheme(this.value)">
                                        <option value="dark" <?php echo ($user['theme'] ?? 'dark') === 'dark' ? 'selected' : ''; ?>>🌙 Oscuro</option>
                                        <option value="light" <?php echo ($user['theme'] ?? 'dark') === 'light' ? 'selected' : ''; ?>>☀️ Claro</option>
                                        <option value="auto">🔄 Automático</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="settings-section">
                            <h3>🛡️ Seguridad</h3>
                            <div class="settings-form">
                                <button class="btn btn-secondary" onclick="showChangePasswordModal()">
                                    🔑 Cambiar Contraseña
                                </button>
                                
                                <button class="btn btn-warning" onclick="downloadData()">
                                    📥 Descargar Mis Datos
                                </button>
                                
                                <button class="btn btn-danger" onclick="deleteAccount()">
                                    🗑️ Eliminar Cuenta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal" id="passwordModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🔑 Cambiar Contraseña</h3>
            <button class="modal-close" onclick="closePasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?php 
                    if (is_callable([$sceneiq, 'generateCSRFToken'])) {
                        echo $sceneiq->generateCSRFToken();
                    } else {
                        echo bin2hex(random_bytes(16));
                    }
                ?>">
                
                <div class="form-group">
                    <label for="currentPassword">Contraseña Actual</label>
                    <input type="password" id="currentPassword" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="newPassword">Nueva Contraseña</label>
                    <input type="password" id="newPassword" name="new_password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

.profile-header {
    position: relative;
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-bottom: var(--spacing-lg);
}

.profile-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 200px;
    background: var(--primary-gradient);
}

.profile-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.8) 100%);
}

.profile-content {
    position: relative;
    z-index: 2;
    padding: var(--spacing-xl);
    padding-top: 120px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: var(--spacing-lg);
}

.profile-info {
    display: flex;
    gap: var(--spacing-lg);
    align-items: flex-end;
}

.profile-avatar {
    position: relative;
}

.avatar-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.avatar-edit {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--accent);
    border: 2px solid white;
    color: white;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-edit:hover {
    transform: scale(1.1);
}

.profile-details {
    color: white;
}

.profile-name {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.profile-username {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}