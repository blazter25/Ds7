<?php
$pageTitle = "Inicio";
$pageDescription = "Descubre películas y series increíbles con reseñas de nuestra comunidad";

// Incluir funciones
require_once 'includes/functions.php';

// Incluir header
require_once 'includes/header.php';

// Obtener datos
$featuredContent = $sceneiq->getContent(8, 0);
$recentMovies = $sceneiq->getContent(4, 0, 'movie');
$recentSeries = $sceneiq->getContent(4, 0, 'series');
$genres = $sceneiq->getGenres();

// Si el usuario está logueado, obtener recomendaciones
$recommendations = [];
if ($user) {
    $recommendations = $sceneiq->getRecommendations($user['id'], 6);
}
?>

<section class="hero">
    <h1>Descubre tu próxima obsesión</h1>
    <p>Explora miles de reseñas y encuentra las mejores películas y series según tus gustos</p>
    <div class="search-bar">
        <form action="pages/search.php" method="GET" class="search-form">
            <input type="text" name="q" class="search-input" placeholder="Buscar películas, series, actores..." required>
            <button type="submit" class="search-btn">🔍</button>
        </form>
    </div>
</section>

<section class="genre-filter">
    <a href="index.php" class="genre-tag active">Todos</a>
    <?php foreach ($genres as $genre): ?>
        <a href="?genre=<?php echo $genre['slug']; ?>" class="genre-tag">
            <?php echo escape($genre['name']); ?>
        </a>
    <?php endforeach; ?>
</section>

<!-- Trending Section -->
<section class="trending-bar">
    <div class="section-header">
        <h2 class="section-title">📈 Trending Ahora</h2>
    </div>
    <div class="trending-content">
        <?php foreach (array_slice($featuredContent, 0, 5) as $index => $content): ?>
            <div class="trending-item">
                <div class="trending-number">#<?php echo $index + 1; ?></div>
                <div class="trending-title"><?php echo escape($content['title']); ?></div>
                <div class="trending-rating">⭐ <?php echo $content['imdb_rating']; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Recomendaciones personalizadas -->
<?php if ($user && !empty($recommendations)): ?>
<section class="content-section">
    <div class="section-header">
        <h2 class="section-title">🎯 Recomendado para ti</h2>
        <a href="pages/dashboard.php" class="view-all">Ver más</a>
    </div>
    <div class="content-grid">
        <?php foreach ($recommendations as $content): ?>
            <?php include 'includes/content-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Películas Destacadas -->
<section class="content-section">
    <div class="section-header">
        <h2 class="section-title">🎬 Películas Destacadas</h2>
        <a href="pages/movies.php" class="view-all">Ver todas</a>
    </div>
    <div class="content-grid">
        <?php foreach ($recentMovies as $content): ?>
            <?php include 'includes/content-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Series Populares -->
<section class="content-section">
    <div class="section-header">
        <h2 class="section-title">📺 Series Populares</h2>
        <a href="pages/series.php" class="view-all">Ver todas</a>
    </div>
    <div class="content-grid">
        <?php foreach ($recentSeries as $content): ?>
            <?php include 'includes/content-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Call to Action para usuarios no registrados -->
<?php if (!$user): ?>
<section class="cta-section">
    <div class="cta-content">
        <h2>¡Únete a SceneIQ hoy!</h2>
        <p>Recibe recomendaciones personalizadas, escribe reseñas y conecta con otros cinéfilos.</p>
        <div class="cta-buttons">
            <a href="pages/register.php" class="btn btn-primary btn-large">Registrarse Gratis</a>
            <a href="pages/login.php" class="btn btn-secondary btn-large">Iniciar Sesión</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>