<?php
// pages/movies.php
$pageTitle = "Películas";
$pageDescription = "Explora nuestra colección de películas con reseñas de la comunidad";

require_once '../includes/header.php';

// Parámetros de filtrado y paginación
$genre = $_GET['genre'] ?? '';
$year = intval($_GET['year'] ?? 0);
$sort = $_GET['sort'] ?? 'popular';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Obtener películas
$movies = $sceneiq->getContent($limit, $offset, 'movie', $genre);
$genres = $sceneiq->getGenres();

// Simular total para paginación
$totalMovies = 100; // En una implementación real, esto vendría de la BD
$totalPages = ceil($totalMovies / $limit);

// Aplicar ordenamiento si es necesario
if ($sort === 'rating') {
    usort($movies, function($a, $b) {
        return ($b['imdb_rating'] ?? 0) <=> ($a['imdb_rating'] ?? 0);
    });
} elseif ($sort === 'year') {
    usort($movies, function($a, $b) {
        return $b['year'] <=> $a['year'];
    });
} elseif ($sort === 'title') {
    usort($movies, function($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    });
}

// Filtrar por año si se especifica
if ($year) {
    $movies = array_filter($movies, function($movie) use ($year) {
        return $movie['year'] == $year;
    });
}
?>

<div class="movies-container">
    <!-- Header -->
    <section class="page-header">
        <div class="header-content">
            <h1>🎬 Películas</h1>
            <p>Descubre increíbles películas con reseñas de nuestra comunidad</p>
        </div>
        
        <div class="header-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo array_sum(array_column($movies, 'review_count')); ?></span>
                <span class="stat-label">Reseñas</span>
            </div>
        </div>
    </section>

    <!-- Filters and Sorting -->
    <section class="filters-section">
        <form method="GET" action="movies.php" class="filters-form">
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
                    <label for="year">Año:</label>
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
            <a href="movies.php" class="genre-tag <?php echo empty($genre) ? 'active' : ''; ?>">Todas</a>
            <?php foreach (array_slice($genres, 0, 8) as $g): ?>
                <a href="movies.php?genre=<?php echo $g['slug']; ?>&sort=<?php echo $sort; ?>&year=<?php echo $year; ?>" 
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
                    <a href="movies.php?year=<?php echo $year; ?>&sort=<?php echo $sort; ?>">&times;</a>
                </span>
            <?php endif; ?>
            
            <?php if ($year): ?>
                <span class="filter-tag">
                    Año: <?php echo $year; ?>
                    <a href="movies.php?genre=<?php echo $genre; ?>&sort=<?php echo $sort; ?>">&times;</a>
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
                    <a href="movies.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>">&times;</a>
                </span>
            <?php endif; ?>
            
            <a href="movies.php" class="clear-all-filters">Limpiar todo</a>
        </section>
    <?php endif; ?>

    <!-- Movies Grid -->
    <section class="movies-content">
        <?php if (empty($movies)): ?>
            <div class="no-content">
                <div class="no-content-icon">🎬</div>
                <h3>No se encontraron películas</h3>
                <p>Intenta ajustar los filtros o explora otros géneros.</p>
                <a href="movies.php" class="btn btn-primary">Ver Todas las Películas</a>
            </div>
        <?php else: ?>
            <div class="content-grid movies-grid">
                <?php foreach ($movies as $content): ?>
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
                    <a href="movies.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>" 
                       class="pagination-btn pagination-prev">← Anterior</a>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    if ($start > 1): ?>
                        <a href="movies.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=1" class="pagination-number">1</a>
                        <?php if ($start > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <a href="movies.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=<?php echo $i; ?>" 
                           class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $totalPages): ?>
                        <?php if ($end < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="movies.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=<?php echo $totalPages; ?>" 
                           class="pagination-number"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                </div>
                
                <?php if ($page < $totalPages): ?>
                    <a href="movies.php?genre=<?php echo $genre; ?>&year=<?php echo $year; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>" 
                       class="pagination-btn pagination-next">Siguiente →</a>
                <?php endif; ?>
            </div>
            
            <div class="pagination-info">
                Página <?php echo $page; ?> de <?php echo $totalPages; ?> 
                (<?php echo count($movies); ?> películas mostradas)
            </div>
        </section>
    <?php endif; ?>

    <!-- Featured Movies Section -->
    <?php if (empty($genre) && empty($year) && $page === 1): ?>
        <section class="featured-section">
            <h2>🌟 Películas Destacadas</h2>
            <div class="featured-grid">
                <?php 
                $featuredMovies = array_slice($movies, 0, 4);
                foreach ($featuredMovies as $movie): 
                ?>
                    <div class="featured-card" onclick="window.location.href='content.php?id=<?php echo $movie['id']; ?>'">
                        <div class="featured-backdrop" style="background-image: url('<?php echo $movie['poster'] ?? '../assets/images/placeholder.jpg'; ?>');">
                            <div class="featured-overlay">
                                <h3><?php echo escape($movie['title']); ?></h3>
                                <p><?php echo $movie['year']; ?> • ⭐ <?php echo $movie['imdb_rating']; ?></p>
                                <p class="featured-synopsis"><?php echo escape($sceneiq->truncateText($movie['synopsis'] ?? '', 100)); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<style>
.movies-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

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

.movies-content {
    margin-bottom: var(--spacing-xl);
}

.movies-grid {
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

.featured-section {
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-xl);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.featured-section h2 {
    font-size: 1.8rem;
    margin-bottom: var(--spacing-lg);
    color: var(--text-primary);
    text-align: center;
}

.featured-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-lg);
}

.featured-card {
    position: relative;
    height: 200px;
    border-radius: var(--border-radius);
    overflow: hidden;
    cursor: pointer;
    transition: var(--transition);
}

.featured-card:hover {
    transform: scale(1.02);
}

.featured-backdrop {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    position: relative;
}

.featured-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    padding: var(--spacing-lg);
    color: white;
}

.featured-overlay h3 {
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

.featured-overlay p {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 0.25rem;
}

.featured-synopsis {
    font-size: 0.8rem !important;
    opacity: 0.7 !important;
    line-height: 1.3;
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

    .featured-grid {
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

// Lazy loading for movie grid
const observerOptions = {
    threshold: 0.1,
    rootMargin: '50px'
};

const movieObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
            movieObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.content-card').forEach(card => {
    movieObserver.observe(card);
});
</script>

<?php require_once '../includes/footer.php'; ?> echo count($movies); ?></span>
                <span class="stat-label">Películas</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count($genres); ?></span>
                <span class="stat-label">Géneros</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $sceneiq->getTotalReviews(); ?></span>
                <span class="stat-label">Reseñas</span>