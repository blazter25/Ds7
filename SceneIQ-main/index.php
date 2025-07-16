<?php
$pageTitle = "Inicio";
$pageDescription = "Descubre tu próxima obsesión cinematográfica en SceneIQ";

require_once 'includes/header.php';

// Obtener contenido para mostrar
$movies = $sceneiq->getContent(8, 0, 'movie');
$series = $sceneiq->getContent(8, 0, 'series');
$allContent = $sceneiq->getContent(12, 0); // Todo el contenido mezclado
$genres = $sceneiq->getGenres();

// Si el usuario está logueado, obtener recomendaciones
$recommendations = [];
if ($user) {
    $recommendations = $sceneiq->getRecommendations($user['id'], 8);
}
?>

<div class="home-container">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                Descubre tu próxima <span class="highlight">obsesión cinematográfica</span>
            </h1>
            <p class="hero-subtitle">
                Explora miles de películas y series, lee reseñas de la comunidad y encuentra exactamente lo que buscas.
            </p>
            
            <?php if (!$user): ?>
                <div class="hero-actions">
                    <a href="pages/register.php" class="btn btn-primary btn-large">
                        ✨ Únete Gratis
                    </a>
                    <a href="pages/movies.php" class="btn btn-secondary btn-large">
                        🎬 Explorar Películas
                    </a>
                </div>
            <?php else: ?>
                <div class="hero-actions">
                    <a href="pages/dashboard.php" class="btn btn-primary btn-large">
                        🎯 Mis Recomendaciones
                    </a>
                    <a href="pages/profile.php" class="btn btn-secondary btn-large">
                        📋 Mi Lista
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($allContent); ?>+</span>
                <span class="stat-label">Títulos</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count($genres); ?></span>
                <span class="stat-label">Géneros</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">500+</span>
                <span class="stat-label">Reseñas</span>
            </div>
        </div>
    </section>

    <?php if ($user && !empty($recommendations)): ?>
        <!-- Recommendations Section -->
        <section class="content-section">
            <div class="section-header">
                <h2>🎯 Recomendado para ti</h2>
                <p>Basado en tus gustos y preferencias</p>
            </div>
            
            <div class="content-grid">
                <?php foreach ($recommendations as $content): ?>
                    <?php include 'includes/content-card.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="pages/dashboard.php" class="btn btn-secondary">Ver Todas las Recomendaciones</a>
            </div>
        </section>
    <?php endif; ?>

    <!-- Trending Section -->
    <section class="content-section">
        <div class="section-header">
            <h2>🔥 Tendencias</h2>
            <p>Lo más popular ahora mismo</p>
        </div>
        
        <div class="content-grid">
            <?php foreach ($allContent as $content): ?>
                <?php include 'includes/content-card.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer">
            <a href="pages/movies.php" class="btn btn-secondary">Explorar Todo el Catálogo</a>
        </div>
    </section>

    <!-- Movies Section -->
    <section class="content-section">
        <div class="section-header">
            <h2>🎬 Películas Populares</h2>
            <p>Las películas más valoradas y comentadas</p>
        </div>
        
        <div class="content-grid">
            <?php foreach ($movies as $content): ?>
                <?php include 'includes/content-card.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer">
            <a href="pages/movies.php" class="btn btn-secondary">Ver Todas las Películas</a>
        </div>
    </section>

    <!-- Series Section -->
    <section class="content-section">
        <div class="section-header">
            <h2>📺 Series Destacadas</h2>
            <p>Las series que no puedes dejar de ver</p>
        </div>
        
        <div class="content-grid">
            <?php foreach ($series as $content): ?>
                <?php include 'includes/content-card.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer">
            <a href="pages/series.php" class="btn btn-secondary">Ver Todas las Series</a>
        </div>
    </section>

    <!-- Genres Section -->
    <section class="genres-section">
        <div class="section-header">
            <h2>🎭 Explora por Género</h2>
            <p>Encuentra exactamente lo que te gusta</p>
        </div>
        
        <div class="genres-grid">
            <?php foreach ($genres as $genre): ?>
                <a href="pages/movies.php?genre=<?php echo $genre['slug']; ?>" 
                   class="genre-card"
                   style="--genre-color: <?php echo $genre['color']; ?>">
                    <span class="genre-name"><?php echo escape($genre['name']); ?></span>
                    <span class="genre-icon">🎬</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!$user): ?>
        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-content">
                <h2>¿Listo para comenzar?</h2>
                <p>Únete a SceneIQ y descubre un mundo de entretenimiento personalizado</p>
                
                <div class="cta-features">
                    <div class="feature-item">
                        <span class="feature-icon">🎯</span>
                        <span class="feature-text">Recomendaciones personalizadas</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">📋</span>
                        <span class="feature-text">Listas personalizadas</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">💬</span>
                        <span class="feature-text">Reseñas de la comunidad</span>
                    </div>
                </div>
                
                <a href="pages/register.php" class="btn btn-primary btn-large">
                    Crear Cuenta Gratis
                </a>
            </div>
        </section>
    <?php endif; ?>
</div>

<style>
.home-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

.hero-section {
    text-align: center;
    padding: 4rem 0;
    margin-bottom: var(--spacing-xl);
    background: linear-gradient(135deg, var(--card-bg) 0%, rgba(255, 255, 255, 0.05) 100%);
    border-radius: var(--border-radius-large);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.hero-title {
    font-size: 3rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: var(--spacing-lg);
    line-height: 1.2;
}

.highlight {
    background: linear-gradient(45deg, var(--accent), #ff8a80);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.2rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.hero-actions {
    display: flex;
    gap: var(--spacing-lg);
    justify-content: center;
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent);
    margin-bottom: var(--spacing-xs);
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.content-section {
    margin-bottom: 4rem;
}

.section-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.section-header h2 {
    font-size: 2rem;
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.section-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.section-footer {
    text-align: center;
    margin-top: var(--spacing-xl);
}

.genres-section {
    margin-bottom: 4rem;
}

.genres-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-xl);
}

.genre-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-lg);
    background: var(--card-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    text-decoration: none;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.genre-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, var(--genre-color), transparent);
    opacity: 0.1;
    transition: var(--transition);
}

.genre-card:hover {
    transform: translateY(-2px);
    border-color: var(--genre-color);
}

.genre-card:hover::before {
    opacity: 0.2;
}

.genre-name {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.1rem;
}

.genre-icon {
    font-size: 1.5rem;
    opacity: 0.7;
}

.cta-section {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--card-bg);
    border-radius: var(--border-radius-large);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.cta-content h2 {
    font-size: 2.5rem;
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
}

.cta-content p {
    font-size: 1.2rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
}

.cta-features {
    display: flex;
    justify-content: center;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    color: var(--text-secondary);
}

.feature-icon {
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-large {
        width: 100%;
        max-width: 300px;
    }
    
    .hero-stats {
        gap: var(--spacing-lg);
    }
    
    .cta-features {
        flex-direction: column;
        align-items: center;
    }
    
    .genres-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>