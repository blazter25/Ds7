<?php
// pages/search.php
$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';
$genre = $_GET['genre'] ?? '';
$year = intval($_GET['year'] ?? 0);
$sort = $_GET['sort'] ?? 'relevance';

$pageTitle = $query ? "Resultados para: " . $query : "Búsqueda";
require_once '../includes/header.php';

$results = [];
$totalResults = 0;

if (!empty($query)) {
    $results = $sceneiq->searchContent($query, 20);
    $totalResults = count($results);
    
    // Aplicar filtros adicionales
    if ($type) {
        $results = array_filter($results, function($item) use ($type) {
            return $item['type'] === $type;
        });
    }
    
    if ($year) {
        $results = array_filter($results, function($item) use ($year) {
            return $item['year'] == $year;
        });
    }
    
    // Aplicar ordenamiento
    switch ($sort) {
        case 'rating':
            usort($results, function($a, $b) {
                return ($b['imdb_rating'] ?? 0) <=> ($a['imdb_rating'] ?? 0);
            });
            break;
        case 'year':
            usort($results, function($a, $b) {
                return $b['year'] <=> $a['year'];
            });
            break;
        case 'title':
            usort($results, function($a, $b) {
                return strcasecmp($a['title'], $b['title']);
            });
            break;
        // 'relevance' es el orden por defecto
    }
    
    // Registrar búsqueda si el usuario está logueado
    if ($user) {
        $sceneiq->logActivity($user['id'], 'search', null, ['query' => $query, 'results' => count($results)]);
    }
}

$genres = $sceneiq->getGenres();
?>

<div class="search-container">
    <!-- Search Header -->
    <section class="search-header">
        <h1>
            <?php if ($query): ?>
                Resultados para "<span style="color: var(--accent);"><?php echo escape($query); ?></span>"
            <?php else: ?>
                🔍 Búsqueda Avanzada
            <?php endif; ?>
        </h1>
        
        <?php if ($query): ?>
            <p><?php echo count($results); ?> resultado<?php echo count($results) != 1 ? 's' : ''; ?> encontrado<?php echo count($results) != 1 ? 's' : ''; ?></p>
        <?php endif; ?>
    </section>

    <!-- Advanced Search Form -->
    <section class="search-form-section">
        <form method="GET" action="search.php" class="advanced-search-form">
            <div class="search-row">
                <div class="form-group">
                    <input type="text" name="q" value="<?php echo escape($query); ?>" 
                           placeholder="Buscar películas, series, actores..." 
                           class="search-input" required>
                </div>
                <button type="submit" class="btn btn-primary search-btn">🔍 Buscar</button>
            </div>
            
            <div class="filters-row">
                <div class="form-group">
                    <select name="type" class="filter-select">
                        <option value="">Todos los tipos</option>
                        <option value="movie" <?php echo $type === 'movie' ? 'selected' : ''; ?>>🎬 Películas</option>
                        <option value="series" <?php echo $type === 'series' ? 'selected' : ''; ?>>📺 Series</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="genre" class="filter-select">
                        <option value="">Todos los géneros</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?php echo $g['slug']; ?>" <?php echo $genre === $g['slug'] ? 'selected' : ''; ?>>
                                <?php echo escape($g['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="year" class="filter-select">
                        <option value="">Cualquier año</option>
                        <?php for ($y = date('Y'); $y >= 1950; $y -= 5): ?>
                            <optgroup label="<?php echo $y; ?>s">
                                <?php for ($i = 0; $i < 5 && ($y - $i) >= 1950; $i++): ?>
                                    <option value="<?php echo $y - $i; ?>" <?php echo $year === ($y - $i) ? 'selected' : ''; ?>>
                                        <?php echo $y - $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </optgroup>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="sort" class="filter-select">
                        <option value="relevance" <?php echo $sort === 'relevance' ? 'selected' : ''; ?>>Relevancia</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Mejor valorados</option>
                        <option value="year" <?php echo $sort === 'year' ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>A-Z</option>
                    </select>
                </div>
            </div>
        </form>
    </section>

    <!-- Results -->
    <?php if (!empty($query)): ?>
        <section class="search-results">
            <?php if (empty($results)): ?>
                <div class="no-results">
                    <div class="no-results-icon">🔍</div>
                    <h3>No encontramos resultados</h3>
                    <p>Intenta con términos diferentes o ajusta los filtros.</p>
                    
                    <div class="search-suggestions">
                        <h4>Sugerencias:</h4>
                        <ul>
                            <li>Verifica la ortografía</li>
                            <li>Usa palabras más generales</li>
                            <li>Prueba con el nombre original en inglés</li>
                            <li>Busca por actor o director</li>
                        </ul>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <a href="../index.php" class="btn btn-primary">Explorar Contenido Popular</a>
                        <button class="btn btn-secondary" onclick="clearSearch()">Nueva Búsqueda</button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Active Filters Display -->
                <?php if ($type || $genre || $year || $sort !== 'relevance'): ?>
                    <div class="active-filters">
                        <span>Filtros activos:</span>
                        <?php if ($type): ?>
                            <span class="filter-tag">
                                <?php echo $type === 'movie' ? '🎬 Películas' : '📺 Series'; ?>
                                <a href="<?php echo '?q=' . urlencode($query) . '&genre=' . $genre . '&year=' . $year . '&sort=' . $sort; ?>">&times;</a>
                            </span>
                        <?php endif; ?>
                        
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
                                <a href="<?php echo '?q=' . urlencode($query) . '&type=' . $type . '&year=' . $year . '&sort=' . $sort; ?>">&times;</a>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($year): ?>
                            <span class="filter-tag">
                                <?php echo $year; ?>
                                <a href="<?php echo '?q=' . urlencode($query) . '&type=' . $type . '&genre=' . $genre . '&sort=' . $sort; ?>">&times;</a>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($sort !== 'relevance'): ?>
                            <span class="filter-tag">
                                <?php 
                                $sortLabels = [
                                    'rating' => 'Mejor valorados',
                                    'year' => 'Más recientes', 
                                    'title' => 'A-Z'
                                ];
                                echo $sortLabels[$sort] ?? $sort;
                                ?>
                                <a href="<?php echo '?q=' . urlencode($query) . '&type=' . $type . '&genre=' . $genre . '&year=' . $year; ?>">&times;</a>
                            </span>
                        <?php endif; ?>
                        
                        <a href="?q=<?php echo urlencode($query); ?>" class="clear-filters">Limpiar todos</a>
                    </div>
                <?php endif; ?>

                <!-- Results Grid -->
                <div class="results-header">
                    <span class="results-count">
                        Mostrando <?php echo count($results); ?> de <?php echo $totalResults; ?> resultados
                    </span>
                </div>
                
                <div class="content-grid search-results-grid">
                    <?php foreach ($results as $content): ?>
                        <?php include '../includes/content-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Load More / Pagination -->
                <div class="load-more-container">
                    <button class="btn btn-secondary" onclick="loadMoreResults()" id="loadMoreBtn" style="display: none;">
                        Cargar Más Resultados
                    </button>
                </div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <!-- Popular Content when no search -->
        <section class="popular-content">
            <h2>🔥 Contenido Popular</h2>
            <p>Descubre qué está viendo la comunidad</p>
            
            <div class="content-grid">
                <?php 
                $popularContent = $sceneiq->getContent(12, 0);
                foreach ($popularContent as $content): 
                ?>
                    <?php include '../includes/content-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Search Tips -->
        <section class="search-tips">
            <h3>💡 Tips de Búsqueda</h3>
            <div class="tips-grid">
                <div class="tip-card">
                    <div class="tip-icon">🎯</div>
                    <h4>Búsqueda Específica</h4>
                    <p>Usa comillas para buscar frases exactas: "El Padrino"</p>
                </div>
                <div class="tip-card">
                    <div class="tip-icon">🎭</div>
                    <h4>Por Actor</h4>
                    <p>Busca por el nombre del actor para encontrar todas sus películas</p>
                </div>
                <div class="tip-card">
                    <div class="tip-icon">📅</div>
                    <h4>Por Año</h4>
                    <p>Combina título y año para resultados más precisos</p>
                </div>
                <div class="tip-card">
                    <div class="tip-icon">🎪</div>
                    <h4>Por Género</h4>
                    <p>Usa los filtros de género para descubrir contenido similar</p>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>

<style>
.search-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

.search-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.search-header h1 {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
    color: var(--text-primary);
}

.search-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.search-form-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.advanced-search-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.search-row {
    display: flex;
    gap: var(--spacing-md);
    align-items: flex-end;
}

.search-row .form-group {
    flex: 1;
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--spacing-md);
}

.filter-select, .search-input {
    width: 100%;
    padding: 0.75rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
    font-size: 0.9rem;
}

.filter-select:focus, .search-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
}

.search-btn {
    padding: 0.75rem 1.5rem;
    white-space: nowrap;
}

.active-filters {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
    flex-wrap: wrap;
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

.clear-filters {
    color: var(--accent);
    text-decoration: none;
    font-size: 0.9rem;
    margin-left: auto;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.results-count {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.search-results-grid {
    margin-bottom: var(--spacing-xl);
}

.no-results {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--card-bg);
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.no-results-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-lg);
    opacity: 0.5;
}

.no-results h3 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
    font-size: 1.5rem;
}

.no-results p {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
    font-size: 1.1rem;
}

.search-suggestions {
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    padding: var(--spacing-lg);
    margin: var(--spacing-lg) 0;
    text-align: left;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.search-suggestions h4 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.search-suggestions ul {
    color: var(--text-secondary);
    padding-left: 1.5rem;
}

.search-suggestions li {
    margin-bottom: 0.25rem;
}

.popular-content {
    margin-bottom: var(--spacing-xl);
}

.popular-content h2 {
    font-size: 1.8rem;
    margin-bottom: var(--spacing-sm);
    color: var(--text-primary);
}

.popular-content p {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
    font-size: 1.1rem;
}

.search-tips {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
    text-align: center;
}

.search-tips h3 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-lg);
    font-size: 1.5rem;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
}

.tip-card {
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    padding: var(--spacing-lg);
    text-align: center;
}

.tip-icon {
    font-size: 2rem;
    margin-bottom: var(--spacing-md);
}

.tip-card h4 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
    font-size: 1.1rem;
}

.tip-card p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.5;
}

.load-more-container {
    text-align: center;
    margin-top: var(--spacing-xl);
}

@media (max-width: 768px) {
    .search-row {
        flex-direction: column;
    }
    
    .filters-row {
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-sm);
    }
    
    .active-filters {
        font-size: 0.8rem;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }
}
</style>

<script>
function clearSearch() {
    document.querySelector('input[name="q"]').value = '';
    document.querySelector('select[name="type"]').value = '';
    document.querySelector('select[name="genre"]').value = '';
    document.querySelector('select[name="year"]').value = '';
    document.querySelector('select[name="sort"]').value = 'relevance';
    document.querySelector('input[name="q"]').focus();
}

function loadMoreResults() {
    // Placeholder for pagination
    showNotification('Función de paginación próximamente', 'info');
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Auto-submit form when filters change
document.querySelectorAll('.filter-select').forEach(select => {
    select.addEventListener('change', function() {
        // Auto-submit después de una pausa
        setTimeout(() => {
            this.form.submit();
        }, 100);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>