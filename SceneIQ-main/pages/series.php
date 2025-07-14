<?php
// pages/series.php
$pageTitle = "Series";
$pageDescription = "Explora nuestra colección de series con reseñas de la comunidad";

require_once '../includes/header.php';

// Parámetros de filtrado y paginación
$genre = $_GET['genre'] ?? '';
$year = intval($_GET['year'] ?? 0);
$sort = $_GET['sort'] ?? 'popular';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Obtener series
$series = $sceneiq->getContent($limit, $offset, 'series', $genre);
$genres = $sceneiq->getGenres();

// Simular total para paginación
$totalSeries = 80; // En una implementación real, esto vendría de la BD
$totalPages = ceil($totalSeries / $limit);

// Aplicar ordenamiento si es necesario
if ($sort === 'rating') {
    usort($series, function($a, $b) {
        return ($b['imdb_rating'] ?? 0) <=> ($a['imdb_rating'] ?? 0);
    });
} elseif ($sort === 'year') {
    usort($series, function($a, $b) {
        return $b['year'] <=> $a['year'];
    });
} elseif ($sort === 'title') {
    usort($series, function($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    });
}

// Filtrar por año si se especifica
if ($year) {
    $series = array_filter($series, function($show) use ($year) {
        return $show['year'] == $year;
    });
}
?>

<div class="series-container">
    <!-- Header -->
    <section class="page-header">
        <div class="header-content">
            <h1>📺 Series</h1>
            <p>Sumérgete en las mejores series con opiniones de nuestra comunidad</p>
        </div>
        
        <div class="header-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($series); ?></span>
                <span class="stat-label">Series</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count($genres); ?></span>
                <span class="stat-label">Géneros</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo array_sum(array_column($series, 'review_count')); ?></span>
                <span class="stat-label">Reseñas</span>
            </div>
        </div>
    </section>

    <!-- Filters and Sorting -->
    <section class="filters-section">
        <form method="GET" action="series.php" class="filters-form">
            <div class="filters-row">
                <div class="filter-group">
                    <label for="genre">Género:</label>
                    <select name="genre" id="genre" class="filter-select">
                        <option value="">Todos los géneros</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?php echo $g['slug']; ?>" <?php echo $genre === $g['slug'] ? 'selected' : ''; ?>>
                                <?php echo escape($g['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="year">Año de estreno:</label>
                    <select name="year" id="year" class="filter-select">
                        <option value="">Cualquier año</option>
                        <?php for ($y = date('Y'); $y >= 1950; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $year === $y ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="sort">Ordenar por:</label>
                    <select name="sort" id="sort" class="filter-select">
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Más populares</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Mejor valoradas</option>
                        <option value="year" <?php echo $sort === 'year' ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>A-Z</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary filter-btn">Aplicar</button>
            </div>
        </form>

        <!-- Quick Genre Filter -->
        <div class="quick-genres">
            <a href="series.php" class="genre-tag <?php echo empty($genre) ? 'active' : ''; ?>">Todas</a>
            <?php foreach (array_slice($genres, 0, 8) as $g): ?>
                <a href="series.php?genre=<?php echo $g['slug']; ?>&sort=<?php echo $sort; ?>&year=<?php echo $year; ?>" 
                   class="genre-tag <?php echo $genre === $g['slug'] ? 'active' : ''; ?>"
                   style="--genre-color: <?php echo $g['color']; ?>">
                    <?php echo escape($g['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Active Filters Display -->
    <?php if ($genre || $year || $sort !== 'popular'): ?>
        <section class="active-filters">
            <span class="filters-label">Filtros activos:</span>
            
            <?php if ($genre): ?>
                <span class="filter-tag">
                    <?php 
                    foreach ($genres as $g) {
                        if ($g['slug'] === $genre) {
                            echo escape($g['name']);
                            break;
                        }
                    }
                    ?>
                    <a href="series.php?year=<?php echo $year; ?>&sort=<?php echo $sort; ?>">&times;</a>
                </span>
            <?php endif; ?>
            
            <?php if ($year): ?>
                <span class="filter-tag">
                    Año: <?php echo $year; ?>
                    <a href="series.php?genre=<?php echo $genre; ?>&sort=<?php echo $sort; ?>">&times;</a>
                </span>
            <?php endif; ?>
            
            <?php if ($sort !== 'popular'): ?>
                <span class="filter-tag">
                    <?php 
                    $sortLabels = [
                        'rating' => 'Mejor valoradas',
                        'year' => 'Más recientes',
                        'title' => 'A-Z'
                    ];
                    echo $sortLabels[$sort] ?? $sort;
                    ?>
                    <a href="series.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>">&times;</a>
                </span>
            <?php endif; ?>
            
            <a href="series.php" class="clear-all-filters">Limpiar todo</a>
        </section>
    <?php endif; ?>

    <!-- Series Grid -->
    <section class="series-content">
        <?php if (empty($series)): ?>
            <div class="no-content">
                <div class="no-content-icon">📺</div>
                <h3>No se encontraron series</h3>
                <p>Intenta ajustar los filtros o explora otros géneros.</p>
                <a href="series.php" class="btn btn-primary">Ver Todas las Series</a>
            </div>
        <?php else: ?>
            <div class="content-grid series-grid">
                <?php foreach ($series as $content): ?>
                    <?php include '../includes/content-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <section class="pagination-section">
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="series.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>" 
                       class="pagination-btn pagination-prev">← Anterior</a>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    if ($start > 1): ?>
                        <a href="series.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=1" class="pagination-number">1</a>
                        <?php if ($start > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <a href="series.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=<?php echo $i; ?>" 
                           class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $totalPages): ?>
                        <?php if ($end < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="series.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=<?php echo $totalPages; ?>" 
                           class="pagination-number"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                    <a href="series.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>" 
                       class="pagination-btn pagination-next">Siguiente →</a>
                <?php endif; ?>
            </div>
            
            <div class="pagination-info">
                Página <?php echo $page; ?> de <?php echo $totalPages; ?> 
                (<?php echo count($series); ?> series mostradas)
            </div>
        </section>
    <?php endif; ?>

    <!-- Trending Series Section -->
    <?php if (empty($genre) && empty($year) && $page === 1): ?>
        <section class="trending-section">
            <h2>🔥 Series en Tendencia</h2>
            <div class="trending-grid">
                <?php 
                $trendingSeries = array_slice($series, 0, 6);
                foreach ($trendingSeries as $show): 
                ?>
                    <div class="trending-card" onclick="window.location.href='content.php?id=<?php echo $show['id']; ?>'">
                        <div class="trending-poster">
                            <img src="<?php echo $show['poster'] ?? '../assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo escape($show['title']); ?>">
                            <div class="trending-overlay">
                                <div class="trending-rating">⭐ <?php echo $show['imdb_rating']; ?></div>
                            </div>
                        </div>
                        <div class="trending-info">
                            <h3><?php echo escape($show['title']); ?></h3>
                            <p><?php echo $show['year']; ?> • <?php echo $show['duration'] ?? 'En emisión'; ?></p>
                            <p class="trending-synopsis"><?php echo escape($sceneiq->truncateText($show['synopsis'] ?? '', 80)); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Series Categories -->
        <section class="categories-section">
            <h2>📊 Explorar por Categorías</h2>
            <div class="categories-grid">
                <div class="category-card" onclick="window.location.href='series.php?sort=rating'">
                    <div class="category-icon">🏆</div>
                    <h3>Mejor Valoradas</h3>
                    <p>Las series con las mejores puntuaciones de nuestra comunidad</p>
                </div>
                
                <div class="category-card" onclick="window.location.href='series.php?sort=year'">
                    <div class="category-icon">🆕</div>
                    <h3>Recién Estrenadas</h3>
                    <p>Las series más nuevas que están causando sensación</p>
                </div>
                
                <div class="category-card" onclick="window.location.href='series.php?genre=drama'">
                    <div class="category-icon">🎭</div>
                    <h3>Drama</h3>
                    <p>Historias profundas que te mantendrán enganchado</p>
                </div>
                
                <div class="category-card" onclick="window.location.href='series.php?genre=comedia'">
                    <div class="category-icon">😄</div>
                    <h3>Comedia</h3>
                    <p>Para esos momentos en que necesitas reír</p>
                </div>
                
                <div class="category-card" onclick="window.location.href='series.php?genre=sci-fi'">
                    <div class="category-icon">🚀</div>
                    <h3>Sci-Fi</h3>
                    <p>Explora mundos futuristas y tecnologías increíbles</p>
                </div>
                
                <div class="category-card" onclick="window.location.href='series.php?genre=thriller'">
                    <div class="category-icon">🔥</div>
                    <h3>Thriller</h3>
                    <p>Suspenso y adrenalina en cada episodio</p>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>

<style>
.series-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

/* Reutilizar la mayoría de estilos de movies.php */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xl);
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
}

.header-content h1 {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
    color: var(--text-primary);
}

.header-content p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.header-stats {
    display: flex;
    gap: var(--spacing-lg);
}

.stat-item {
    text-align: center;
    padding: var(--spacing-md);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    min-width: 80px;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--accent);
}

.stat-label {
    font-size: 0.8rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filters-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.filters-form {
    margin-bottom: var(--spacing-lg);
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) auto;
    gap: var(--spacing-md);
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.filter-group label {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
}

.filter-select {
    padding: 0.75rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
    font-size: 0.9rem;
}

.filter-select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
}

.filter-btn {
    padding: 0.75rem 1.5rem;
    white-space: nowrap;
}

.quick-genres {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: var(--spacing-lg);
}

.active-filters {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-md);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    flex-wrap: wrap;
}

.filters-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
}

.filter-tag {
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-tag a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
}

.clear-all-filters {
    color: var(--accent);
    text-decoration: none;
    font-size: 0.9rem;
    margin-left: auto;
}

.series-content {
    margin-bottom: var(--spacing-xl);
}

.series-grid {
    margin-bottom: var(--spacing-xl);
}

.no-content {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--card-bg);
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.no-content-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-lg);
    opacity: 0.5;
}

.no-content h3 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
    font-size: 1.5rem;
}

.no-content p {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
    font-size: 1.1rem;
}

/* Estilos específicos para series */
.trending-section {
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-xl);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.trending-section h2 {
    font-size: 1.8rem;
    margin-bottom: var(--spacing-lg);
    color: var(--text-primary);
    text-align: center;
}

.trending-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
}

.trending-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    cursor: pointer;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.trending-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.trending-poster {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.trending-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.trending-card:hover .trending-poster img {
    transform: scale(1.05);
}

.trending-overlay {
    position: absolute;
    top: var(--spacing-sm);
    right: var(--spacing-sm);
    background: rgba(0, 0, 0, 0.8);
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius-small);
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
}

.trending-info {
    padding: var(--spacing-md);
}

.trending-info h3 {
    color: var(--text-primary);
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.trending-info p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.trending-synopsis {
    font-size: 0.8rem !important;
    line-height: 1.3;
    opacity: 0.8;
}

.categories-section {
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-xl);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.categories-section h2 {
    font-size: 1.8rem;
    margin-bottom: var(--spacing-lg);
    color: var(--text-primary);
    text-align: center;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
}

.category-card {
    background: var(--card-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
    border-color: var(--accent);
}

.category-icon {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-md);
}

.category-card h3 {
    color: var(--text-primary);
    font-size: 1.2rem;
    margin-bottom: var(--spacing-sm);
    font-weight: 600;
}

.category-card p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.4;
}

.pagination-section {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
}

.pagination-btn, .pagination-number {
    padding: 0.5rem 1rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-secondary);
    text-decoration: none;
    transition: var(--transition);
}

.pagination-btn:hover, .pagination-number:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.pagination-number.active {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.pagination-numbers {
    display: flex;
    gap: var(--spacing-xs);
    align-items: center;
}

.pagination-dots {
    color: var(--text-secondary);
    padding: 0 0.5rem;
}

.pagination-info {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-lg);
    }

    .header-stats {
        width: 100%;
        justify-content: center;
    }

    .filters-row {
        grid-template-columns: 1fr;
        gap: var(--spacing-sm);
    }

    .quick-genres {
        justify-content: center;
    }

    .pagination {
        flex-wrap: wrap;
        gap: var(--spacing-xs);
    }

    .trending-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .categories-grid {
        grid-template-columns: 1fr;
    }

    .active-filters {
        justify-content: center;
        text-align: center;
    }
}
</style>

<script>
// Auto-submit form when filters change (with debounce)
let filterTimeout;
document.querySelectorAll('.filter-select').forEach(select => {
    select.addEventListener('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            this.form.submit();
        }, 300);
    });
});

// Smooth scroll for pagination
document.querySelectorAll('.pagination a').forEach(link => {
    link.addEventListener('click', function(e) {
        if (this.href) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
});

// Lazy loading for series grid
const observerOptions = {
    threshold: 0.1,
    rootMargin: '50px'
};

const seriesObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
            seriesObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.content-card, .trending-card').forEach(card => {
    seriesObserver.observe(card);
});

// Category card hover effects
document.querySelectorAll('.category-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>