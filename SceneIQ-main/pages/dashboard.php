<?php
// pages/dashboard.php
$pageTitle = "Dashboard";

// Incluir functions.php que ya tiene todo lo necesario
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Verificar que el usuario esté logueado
if (!$user) {
    redirect('../pages/login.php');
}

// Obtener datos del usuario
$userStats = $sceneiq->getUserStats($user['id']);
$recommendations = $sceneiq->getRecommendations($user['id'], 8);
$watchlist = $sceneiq->getUserList($user['id'], 'watchlist', 6);
$favorites = $sceneiq->getUserList($user['id'], 'favorites', 6);
$recentlyViewed = $sceneiq->getUserList($user['id'], 'watched', 4);

// Contenido trending para recomendaciones generales si no hay suficientes personalizadas
$trendingContent = $sceneiq->getContent(6, 0);
?>

<style>
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --accent-color: #f093fb;
    --bg-primary: #0f0f23;
    --bg-secondary: #1a1a2e;
    --card-bg: #16213e;
    --text-primary: #ffffff;
    --text-secondary: #a0a9c0;
    --border-color: rgba(255, 255, 255, 0.1);
    --shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    --border-radius: 12px;
    --transition: all 0.3s ease;
}

body {
    background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
    color: var(--text-primary);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
}

.dashboard-main {
    min-height: 100vh;
}

.welcome-section {
    background: linear-gradient(135deg, var(--card-bg) 0%, rgba(102, 126, 234, 0.1) 100%);
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
}

.welcome-content h1 {
    font-size: 2.5rem;
    margin: 0 0 0.5rem 0;
    background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.welcome-content p {
    color: var(--text-secondary);
    font-size: 1.1rem;
    margin: 0;
}

.user-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
    border: 1px solid var(--border-color);
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.08);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent-color);
    display: block;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quick-actions {
    margin-bottom: 2rem;
}

.quick-actions h2 {
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-btn {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    text-decoration: none;
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius);
    border: none;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.content-section {
    margin-bottom: 3rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: 1.5rem;
    color: var(--text-primary);
    margin: 0;
}

.view-all {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: var(--transition);
}

.view-all:hover {
    background: rgba(102, 126, 234, 0.1);
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1.5rem;
}

.content-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    overflow: hidden;
    border: 1px solid var(--border-color);
    transition: var(--transition);
    position: relative;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow);
}

.content-poster {
    aspect-ratio: 2/3;
    overflow: hidden;
    position: relative;
}

.content-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.content-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%);
    display: flex;
    align-items: flex-end;
    padding: 1rem;
    opacity: 0;
    transition: var(--transition);
}

.content-card:hover .content-overlay {
    opacity: 1;
}

.content-actions {
    display: flex;
    gap: 0.5rem;
    width: 100%;
    justify-content: center;
}

.content-action-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    transition: var(--transition);
    backdrop-filter: blur(10px);
}

.content-action-btn:hover {
    background: var(--primary-color);
    transform: scale(1.1);
}

.content-info {
    padding: 1rem;
}

.content-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.content-meta {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

.content-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.rating-star {
    color: #ffd700;
}

.no-content {
    text-align: center;
    padding: 3rem 2rem;
    background: var(--card-bg);
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
}

.no-content-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.no-content h3 {
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.no-content p {
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.dashboard-sidebar {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: 2rem;
    border: 1px solid var(--border-color);
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.sidebar-section {
    margin-bottom: 2rem;
}

.sidebar-section:last-child {
    margin-bottom: 0;
}

.sidebar-section h3 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.genre-preference-item {
    margin-bottom: 1rem;
}

.genre-name {
    display: block;
    color: var(--text-primary);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.preference-bar {
    height: 6px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
    overflow: hidden;
}

.preference-fill {
    height: 100%;
    border-radius: 3px;
    transition: var(--transition);
}

.activity-item {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.activity-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.activity-icon {
    font-size: 1.2rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-text {
    color: var(--text-primary);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.activity-time {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.progress-item {
    margin-bottom: 1.5rem;
}

.progress-label {
    color: var(--text-primary);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.progress-bar {
    height: 8px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    border-radius: 4px;
    transition: var(--transition);
}

.progress-text {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.cta-section {
    background: linear-gradient(135deg, var(--card-bg) 0%, rgba(102, 126, 234, 0.1) 100%);
    border-radius: var(--border-radius);
    padding: 3rem 2rem;
    text-align: center;
    border: 1px solid var(--border-color);
}

.cta-content h2 {
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.cta-content p {
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

@media (max-width: 968px) {
    .dashboard-container {
        grid-template-columns: 1fr;
        padding: 1rem;
    }
    
    .dashboard-sidebar {
        position: static;
    }
    
    .content-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .user-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .action-buttons {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-container">
    <div class="dashboard-main">
        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-content">
                <h1>¡Hola, <?php echo escape($user['full_name'] ?? $user['username']); ?>! 👋</h1>
                <p>Descubre nuevo contenido personalizado para ti</p>
            </div>
            <div class="user-stats">
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
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
            <h2>Acciones Rápidas</h2>
            <div class="action-buttons">
                <button class="action-btn" onclick="showNotification('¡Función próximamente!', 'info')">
                    📝 Escribir Reseña
                </button>
                <button class="action-btn" onclick="getRandomRecommendation()">
                    🎲 Sorpréndeme
                </button>
                <a href="../pages/search.php" class="action-btn">
                    🔍 Buscar Contenido
                </a>
                <a href="../pages/profile.php" class="action-btn">
                    ⚙️ Mi Perfil
                </a>
            </div>
        </section>

        <!-- Recommendations -->
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">🎯 Recomendado para Ti</h2>
                <a href="#" class="view-all" onclick="refreshRecommendations()">🔄 Actualizar</a>
            </div>
            
            <?php if (empty($recommendations) && empty($trendingContent)): ?>
                <div class="no-content">
                    <div class="no-content-icon">🎬</div>
                    <h3>¡Comenzemos!</h3>
                    <p>Agrega algunas películas o series a tu lista para recibir recomendaciones personalizadas.</p>
                    <a href="../index.php" class="btn btn-primary">Explorar Contenido</a>
                </div>
            <?php else: ?>
                <div class="content-grid" id="recommendationsGrid">
                    <?php 
                    $displayContent = !empty($recommendations) ? $recommendations : array_slice($trendingContent, 0, 8);
                    foreach ($displayContent as $content): 
                    ?>
                        <div class="content-card" data-id="<?php echo $content['id']; ?>">
                            <div class="content-poster">
                                <img src="<?php echo $content['poster'] ?? '../assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo escape($content['title']); ?>"
                                     onerror="this.src='../assets/images/placeholder.jpg'">
                                
                                <div class="content-overlay">
                                    <div class="content-actions">
                                        <button class="content-action-btn" onclick="addToWatchlist(<?php echo $content['id']; ?>)" title="Agregar a mi lista">
                                            ➕
                                        </button>
                                        <button class="content-action-btn" onclick="addToFavorites(<?php echo $content['id']; ?>)" title="Agregar a favoritos">
                                            ❤️
                                        </button>
                                        <button class="content-action-btn" onclick="viewContent(<?php echo $content['id']; ?>)" title="Ver detalles">
                                            👁️
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content-info">
                                <h3 class="content-title"><?php echo escape($content['title']); ?></h3>
                                
                                <div class="content-meta">
                                    <span><?php echo $content['year']; ?></span>
                                    <span>•</span>
                                    <span><?php echo $content['type'] === 'movie' ? 'Película' : 'Serie'; ?></span>
                                    <?php if (isset($content['duration'])): ?>
                                        <span>•</span>
                                        <span><?php echo $content['duration']; ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($content['imdb_rating']) && $content['imdb_rating'] > 0): ?>
                                    <div class="content-rating">
                                        <span class="rating-star">⭐</span>
                                        <span><?php echo number_format($content['imdb_rating'], 1); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Continue Watching / Recently Viewed -->
        <?php if (!empty($recentlyViewed)): ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">📚 Visto Recientemente</h2>
                <a href="../pages/profile.php?tab=watched" class="view-all">Ver todo</a>
            </div>
            <div class="content-grid">
                <?php foreach ($recentlyViewed as $content): ?>
                    <div class="content-card" data-id="<?php echo $content['id']; ?>">
                        <div class="content-poster">
                            <img src="<?php echo $content['poster'] ?? '../assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo escape($content['title']); ?>"
                                 onerror="this.src='../assets/images/placeholder.jpg'">
                        </div>
                        <div class="content-info">
                            <h3 class="content-title"><?php echo escape($content['title']); ?></h3>
                            <div class="content-meta">
                                <span><?php echo $content['year']; ?></span>
                                <span>•</span>
                                <span><?php echo $content['type'] === 'movie' ? 'Película' : 'Serie'; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Watchlist Preview -->
        <?php if (!empty($watchlist)): ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">📋 Mi Lista de Seguimiento</h2>
                <a href="../pages/profile.php?tab=watchlist" class="view-all">Ver todo (<?php echo $userStats['watchlist_count']; ?>)</a>
            </div>
            <div class="content-grid">
                <?php foreach ($watchlist as $content): ?>
                    <div class="content-card" data-id="<?php echo $content['id']; ?>">
                        <div class="content-poster">
                            <img src="<?php echo $content['poster'] ?? '../assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo escape($content['title']); ?>"
                                 onerror="this.src='../assets/images/placeholder.jpg'">
                        </div>
                        <div class="content-info">
                            <h3 class="content-title"><?php echo escape($content['title']); ?></h3>
                            <div class="content-meta">
                                <span><?php echo $content['year']; ?></span>
                                <span>•</span>
                                <span><?php echo $content['type'] === 'movie' ? 'Película' : 'Serie'; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Favorites Preview -->
        <?php if (!empty($favorites)): ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">❤️ Mis Favoritos</h2>
                <a href="../pages/profile.php?tab=favorites" class="view-all">Ver todo (<?php echo $userStats['favorites_count']; ?>)</a>
            </div>
            <div class="content-grid">
                <?php foreach ($favorites as $content): ?>
                    <div class="content-card" data-id="<?php echo $content['id']; ?>">
                        <div class="content-poster">
                            <img src="<?php echo $content['poster'] ?? '../assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo escape($content['title']); ?>"
                                 onerror="this.src='../assets/images/placeholder.jpg'">
                        </div>
                        <div class="content-info">
                            <h3 class="content-title"><?php echo escape($content['title']); ?></h3>
                            <div class="content-meta">
                                <span><?php echo $content['year']; ?></span>
                                <span>•</span>
                                <span><?php echo $content['type'] === 'movie' ? 'Película' : 'Serie'; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Empty State for New Users -->
        <?php if (empty($watchlist) && empty($favorites) && ($userStats['total_reviews'] ?? 0) == 0): ?>
        <section class="content-section">
            <div class="cta-section">
                <div class="cta-content">
                    <h2>🚀 ¡Comienza tu viaje cinematográfico!</h2>
                    <p>Para obtener recomendaciones personalizadas, interactúa con el contenido:</p>
                    <div class="cta-buttons">
                        <a href="../index.php" class="btn btn-primary">Explorar Contenido</a>
                        <a href="../pages/movies.php" class="btn btn-secondary">Ver Películas</a>
                        <a href="../pages/series.php" class="btn btn-secondary">Ver Series</a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <!-- Genre Preferences -->
        <div class="sidebar-section">
            <h3>Preferencias de Género</h3>
            <div class="genre-preferences">
                <?php 
                $genres = $sceneiq->getGenres();
                foreach (array_slice($genres, 0, 6) as $genre): 
                    $preference = rand(20, 100); // Simulated preference
                ?>
                    <div class="genre-preference-item">
                        <span class="genre-name"><?php echo escape($genre['name']); ?></span>
                        <div class="preference-bar">
                            <div class="preference-fill" style="width: <?php echo $preference; ?>%; background: <?php echo $genre['color']; ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="sidebar-section">
            <h3>Actividad Reciente</h3>
            <div class="activity-feed">
                <?php if (($userStats['total_reviews'] ?? 0) > 0): ?>
                    <div class="activity-item">
                        <span class="activity-icon">📝</span>
                        <div class="activity-content">
                            <div class="activity-text">Escribiste una reseña</div>
                            <div class="activity-time">Hace 2 días</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (($userStats['watchlist_count'] ?? 0) > 0): ?>
                    <div class="activity-item">
                        <span class="activity-icon">➕</span>
                        <div class="activity-content">
                            <div class="activity-text">Agregaste contenido a tu lista</div>
                            <div class="activity-time">Hace 1 semana</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="activity-item">
                    <span class="activity-icon">👀</span>
                    <div class="activity-content">
                        <div class="activity-text">Viste detalles de contenido</div>
                        <div class="activity-time">Hoy</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <span class="activity-icon">🔍</span>
                    <div class="activity-content">
                        <div class="activity-text">Realizaste una búsqueda</div>
                        <div class="activity-time">Hace 3 días</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Tracking -->
        <div class="sidebar-section">
            <h3>Tu Progreso</h3>
            <div class="progress-items">
                <div class="progress-item">
                    <div class="progress-label">Explorador Novato</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, ($userStats['total_reviews'] ?? 0) * 20); ?>%;"></div>
                    </div>
                    <div class="progress-text"><?php echo $userStats['total_reviews'] ?? 0; ?>/5 reseñas</div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">Coleccionista</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, ($userStats['watchlist_count'] ?? 0) * 10); ?>%;"></div>
                    </div>
                    <div class="progress-text"><?php echo $userStats['watchlist_count'] ?? 0; ?>/10 en lista</div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">Crítico</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, ($userStats['avg_rating'] ?? 0) * 10); ?>%;"></div>
                    </div>
                    <div class="progress-text">Rating promedio: <?php echo number_format($userStats['avg_rating'] ?? 0, 1); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones JavaScript para el dashboard

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Crear el elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        min-width: 300px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        backdrop-filter: blur(10px);
        animation: slideInRight 0.3s ease;
        ${type === 'success' ? 'background: linear-gradient(135deg, #4CAF50, #45a049);' : 
          type === 'error' ? 'background: linear-gradient(135deg, #f44336, #d32f2f);' : 
          type === 'warning' ? 'background: linear-gradient(135deg, #ff9800, #f57c00);' :
          'background: linear-gradient(135deg, #2196F3, #1976D2);'}
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" 
                    style="background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; margin-left: 1rem;">×</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove después de 5 segundos
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Agregar animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Funciones para las acciones de contenido
function addToWatchlist(contentId) {
    console.log('Agregando a watchlist:', contentId);
    showNotification('¡Agregado a tu lista de seguimiento!', 'success');
}

function addToFavorites(contentId) {
    console.log('Agregando a favoritos:', contentId);
    showNotification('¡Agregado a tus favoritos!', 'success');
}

function viewContent(contentId) {
    console.log('Viendo contenido:', contentId);
    showNotification('Redirigiendo a detalles...', 'info');
    // En una app real, redirigirías a la página de detalles
    // window.location.href = `../pages/content.php?id=${contentId}`;
}

// Función para obtener recomendación aleatoria
function getRandomRecommendation() {
    const recommendations = [
        {
            id: 7,
            title: 'Parasite',
            year: 2019,
            type: 'movie',
            duration: '132 min',
            synopsis: 'Una familia pobre se infiltra en la vida de una familia rica con consecuencias inesperadas.',
            imdb_rating: 8.6,
            poster: 'https://image.tmdb.org/t/p/w500/7IiTTgloJzvGI1TAYymCfbfl3vT.jpg'
        },
        {
            id: 8,
            title: 'The Crown',
            year: 2016,
            type: 'series',
            duration: '6 temporadas',
            synopsis: 'La historia de la Reina Isabel II y la familia real británica a lo largo de las décadas.',
            imdb_rating: 8.7,
            poster: 'https://image.tmdb.org/t/p/w500/4Sp6HPMfJykwTC4f0VHGE0GDkL8.jpg'
        },
        {
            id: 9,
            title: 'Dune',
            year: 2021,
            type: 'movie',
            duration: '155 min',
            synopsis: 'Paul Atreides lidera una rebelión para liberar su planeta natal del control de fuerzas malignas.',
            imdb_rating: 8.0,
            poster: 'https://image.tmdb.org/t/p/w500/d5NXSklXo0qyIYkgV94XAgMIckC.jpg'
        }
    ];
    
    const randomContent = recommendations[Math.floor(Math.random() * recommendations.length)];
    showRandomRecommendationModal(randomContent);
}

function showRandomRecommendationModal(content) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(5px);
    `;
    
    modal.innerHTML = `
        <div style="
            background: var(--card-bg);
            border-radius: var(--border-radius);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
        ">
            <div style="
                padding: 1.5rem;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <h3 style="margin: 0; color: var(--text-primary); font-size: 1.5rem;">🎲 Tu Recomendación Sorpresa</h3>
                <button onclick="this.closest('.modal').remove(); document.body.style.overflow = '';" 
                        style="
                            background: none;
                            border: none;
                            color: var(--text-secondary);
                            font-size: 1.5rem;
                            cursor: pointer;
                            padding: 0.5rem;
                            border-radius: 50%;
                            transition: var(--transition);
                        "
                        onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                        onmouseout="this.style.background='none'">×</button>
            </div>
            <div style="padding: 2rem;">
                <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                    <img src="${content.poster}" 
                         style="width: 150px; height: 225px; object-fit: cover; border-radius: 12px; flex-shrink: 0;"
                         onerror="this.src='../assets/images/placeholder.jpg'">
                    <div style="flex: 1;">
                        <h4 style="color: var(--text-primary); font-size: 1.5rem; margin-bottom: 0.5rem;">${content.title}</h4>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                            ${content.year} • ${content.type === 'movie' ? 'Película' : 'Serie'}
                            ${content.duration ? ' • ' + content.duration : ''}
                        </p>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span style="color: #ffd700; font-size: 1.2rem;">⭐</span>
                            <span style="color: var(--text-primary); font-weight: 600; font-size: 1.1rem;">${content.imdb_rating}</span>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.5rem;">
                            ${content.synopsis || 'Sin descripción disponible.'}
                        </p>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <button onclick="viewContent(${content.id}); this.closest('.modal').remove(); document.body.style.overflow = '';" 
                                    class="btn btn-primary">Ver Detalles</button>
                            <button onclick="addToWatchlist(${content.id}); this.closest('.modal').remove(); document.body.style.overflow = '';" 
                                    class="btn btn-secondary">+ Mi Lista</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    });
}

// Función para actualizar recomendaciones
function refreshRecommendations() {
    showNotification('Actualizando recomendaciones...', 'info');
    
    // Simular carga
    setTimeout(() => {
        showNotification('¡Recomendaciones actualizadas!', 'success');
        // En una app real, recargarías la página o harías una petición AJAX
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }, 1500);
}

// Función para manejar errores de imágenes
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = '../assets/images/placeholder.jpg';
        });
    });
});

// Añadir efecto de hover mejorado a las tarjetas
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.content-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});

console.log('Dashboard cargado correctamente ✅');
</script>

<?php require_once '../includes/footer.php'; ?>