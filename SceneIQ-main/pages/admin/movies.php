<?php
// pages/admin/movies.php
$pageTitle = "Gestión de Películas - Admin";
require_once '../../includes/header.php';

// Verificar que sea administrador
if (!$sceneiq->isAdmin()) {
    header('Location: ../../index.php');
    exit;
}

// Manejar acciones
$action = $_GET['action'] ?? '';
$movieId = intval($_GET['id'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        // Aquí procesarías la adición/edición de película
        $message = $action === 'add' ? 'Película agregada exitosamente' : 'Película actualizada exitosamente';
    } elseif ($action === 'delete') {
        // Aquí procesarías la eliminación
        $message = 'Película eliminada exitosamente';
    }
}

// Obtener películas
$movies = $sceneiq->getContent(20, 0, 'movie');
$genres = $sceneiq->getGenres();

// Película específica para editar
$editMovie = null;
if ($action === 'edit' && $movieId) {
    $editMovie = $sceneiq->getContentById($movieId);
}
?>

<div class="admin-movies-container">
    <!-- Header -->
    <div class="admin-header">
        <div class="admin-breadcrumb">
            <a href="../../index.php">SceneIQ</a> › 
            <a href="index.php">Admin</a> › 
            <span>Películas</span>
        </div>
        <div class="header-content">
            <h1>🎬 Gestión de Películas</h1>
            <p>Administra el catálogo completo de películas</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showAddMovieModal()">
                ➕ Agregar Película
            </button>
            <button class="btn btn-secondary" onclick="bulkImport()">
                📥 Importar en Lote
            </button>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Stats and Filters -->
    <div class="movies-stats">
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">🎬</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo count($movies); ?></div>
                    <div class="stat-label">Total Películas</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo count($movies) > 0 ? number_format(array_sum(array_column($movies, 'imdb_rating')) / count($movies), 1) : '0.0'; ?></div>
                    <div class="stat-label">Rating Promedio</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo array_sum(array_column($movies, 'review_count')); ?></div>
                    <div class="stat-label">Total Reseñas</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🆕</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo rand(5, 15); ?></div>
                    <div class="stat-label">Agregadas Este Mes</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form class="filters-form" method="GET">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Buscar películas..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="search-input">
                </div>
                
                <div class="filter-group">
                    <select name="genre" class="filter-select">
                        <option value="">Todos los géneros</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo htmlspecialchars($genre['slug']); ?>" 
                                    <?php echo ($_GET['genre'] ?? '') === $genre['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genre['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="year" class="filter-select">
                        <option value="">Cualquier año</option>
                        <?php for ($year = date('Y'); $year >= 1950; $year--): ?>
                            <option value="<?php echo $year; ?>" 
                                    <?php echo ($_GET['year'] ?? '') == $year ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">Todos los estados</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="movies.php" class="btn btn-secondary">Limpiar</a>
            </form>
        </div>
    </div>

    <!-- Movies Table -->
    <div class="movies-table-section">
        <div class="table-header">
            <h2>📋 Lista de Películas</h2>
            <div class="table-actions">
                <button class="btn btn-secondary btn-small" onclick="exportMovies()">
                    📊 Exportar
                </button>
                <div class="view-toggle">
                    <button class="view-btn active" data-view="table">📋</button>
                    <button class="view-btn" data-view="grid">⊞</button>
                </div>
            </div>
        </div>

        <div class="table-container" id="tableView">
            <table class="movies-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Película</th>
                        <th>Año</th>
                        <th>Géneros</th>
                        <th>Rating</th>
                        <th>Reseñas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="movie-checkbox" value="<?php echo $movie['id']; ?>">
                            </td>
                            <td class="movie-cell">
                                <div class="movie-info">
                                    <img src="<?php echo htmlspecialchars($movie['poster'] ?? '../../assets/images/placeholder.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-thumbnail">
                                    <div>
                                        <div class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                                        <div class="movie-meta"><?php echo htmlspecialchars($movie['duration'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($movie['year']); ?></td>
                            <td>
                                <div class="genres-list">
                                    <?php 
                                    if (isset($movie['genres']) && $movie['genres']) {
                                        $movieGenres = explode(',', $movie['genres']);
                                        foreach (array_slice($movieGenres, 0, 2) as $genre): 
                                    ?>
                                        <span class="genre-tag-small"><?php echo htmlspecialchars(trim($genre)); ?></span>
                                    <?php 
                                        endforeach;
                                        if (count($movieGenres) > 2) {
                                            echo '<span class="genre-more">+' . (count($movieGenres) - 2) . '</span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="rating-cell">
                                <div class="rating-display">
                                    <span class="rating-value">⭐ <?php echo htmlspecialchars($movie['imdb_rating']); ?></span>
                                </div>
                            </td>
                            <td class="reviews-cell">
                                <span class="review-count"><?php echo htmlspecialchars($movie['review_count'] ?? 0); ?></span>
                            </td>
                            <td>
                                <span class="status-badge status-active">Activo</span>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="editMovie(<?php echo $movie['id']; ?>)" title="Editar">
                                        ✏️
                                    </button>
                                    <button class="btn-icon" onclick="viewMovie(<?php echo $movie['id']; ?>)" title="Ver">
                                        👁️
                                    </button>
                                    <button class="btn-icon danger" onclick="deleteMovie(<?php echo $movie['id']; ?>)" title="Eliminar">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Grid View (hidden by default) -->
        <div class="grid-container hidden" id="gridView">
            <div class="movies-grid">
                <?php foreach ($movies as $movie): ?>
                    <div class="admin-movie-card">
                        <div class="movie-card-image">
                            <img src="<?php echo htmlspecialchars($movie['poster'] ?? '../../assets/images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <div class="movie-card-overlay">
                                <div class="card-actions">
                                    <button class="btn-icon" onclick="editMovie(<?php echo $movie['id']; ?>)">✏️</button>
                                    <button class="btn-icon" onclick="viewMovie(<?php echo $movie['id']; ?>)">👁️</button>
                                    <button class="btn-icon danger" onclick="deleteMovie(<?php echo $movie['id']; ?>)">🗑️</button>
                                </div>
                            </div>
                        </div>
                        <div class="movie-card-content">
                            <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                            <p><?php echo htmlspecialchars($movie['year']); ?> • ⭐ <?php echo htmlspecialchars($movie['imdb_rating']); ?></p>
                            <div class="card-genres">
                                <?php 
                                if (isset($movie['genres']) && $movie['genres']) {
                                    $movieGenres = array_slice(explode(',', $movie['genres']), 0, 2);
                                    foreach ($movieGenres as $genre): 
                                ?>
                                    <span class="genre-tag-small"><?php echo htmlspecialchars(trim($genre)); ?></span>
                                <?php endforeach; } ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions" id="bulkActions" style="display: none;">
            <div class="bulk-info">
                <span id="selectedCount">0</span> película(s) seleccionada(s)
            </div>
            <div class="bulk-buttons">
                <button class="btn btn-secondary" onclick="bulkActivate()">Activar</button>
                <button class="btn btn-secondary" onclick="bulkDeactivate()">Desactivar</button>
                <button class="btn btn-danger" onclick="bulkDelete()">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-section">
        <div class="pagination-info">
            Mostrando <?php echo count($movies); ?> de <?php echo count($movies); ?> películas
        </div>
        <div class="pagination">
            <button class="pagination-btn" disabled>← Anterior</button>
            <span class="pagination-current">Página 1 de 1</span>
            <button class="pagination-btn" disabled>Siguiente →</button>
        </div>
    </div>
</div>

<!-- Add/Edit Movie Modal -->
<div class="modal" id="movieModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="modalTitle">➕ Agregar Nueva Película</h3>
            <button class="modal-close" onclick="closeMovieModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="movieForm" enctype="multipart/form-data">
                <input type="hidden" id="movieId" name="id" value="">
                <input type="hidden" name="csrf_token" value="<?php 
                    if (is_callable([$sceneiq, 'generateCSRFToken'])) {
                        echo $sceneiq->generateCSRFToken();
                    } elseif (isset($sceneiq->generateCSRFToken)) {
                        echo call_user_func($sceneiq->generateCSRFToken);
                    } else {
                        echo bin2hex(random_bytes(16));
                    }
                ?>">
                
                <div class="form-tabs">
                    <button type="button" class="tab-btn active" data-tab="basic">Información Básica</button>
                    <button type="button" class="tab-btn" data-tab="details">Detalles</button>
                    <button type="button" class="tab-btn" data-tab="media">Multimedia</button>
                </div>

                <!-- Basic Information Tab -->
                <div class="tab-content active" id="basicTab">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="movieTitle">Título *</label>
                            <input type="text" id="movieTitle" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="movieYear">Año *</label>
                            <input type="number" id="movieYear" name="year" min="1900" max="2030" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="movieDuration">Duración</label>
                            <input type="text" id="movieDuration" name="duration" placeholder="ej: 152 min">
                        </div>
                        <div class="form-group">
                            <label for="movieRating">Rating IMDb</label>
                            <input type="number" id="movieRating" name="imdb_rating" min="0" max="10" step="0.1">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="movieSynopsis">Sinopsis</label>
                        <textarea id="movieSynopsis" name="synopsis" rows="4" 
                                  placeholder="Descripción de la película..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Géneros</label>
                        <div class="genres-selection">
                            <?php foreach ($genres as $genre): ?>
                                <label class="genre-checkbox">
                                    <input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>">
                                    <span class="genre-label"><?php echo htmlspecialchars($genre['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Details Tab -->
                <div class="tab-content" id="detailsTab">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="movieDirector">Director</label>
                            <input type="text" id="movieDirector" name="director" placeholder="Nombre del director">
                        </div>
                        <div class="form-group">
                            <label for="movieCountry">País</label>
                            <input type="text" id="movieCountry" name="country" placeholder="País de origen">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="movieCast">Reparto Principal</label>
                        <textarea id="movieCast" name="cast" rows="3" 
                                  placeholder="Actores principales separados por comas..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="movieBudget">Presupuesto</label>
                            <input type="text" id="movieBudget" name="budget" placeholder="ej: $200M">
                        </div>
                        <div class="form-group">
                            <label for="movieBoxOffice">Box Office</label>
                            <input type="text" id="movieBoxOffice" name="box_office" placeholder="ej: $1.2B">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="movieTmdbId">TMDB ID</label>
                        <input type="text" id="movieTmdbId" name="tmdb_id" placeholder="ID de TMDB">
                    </div>
                </div>

                <!-- Media Tab -->
                <div class="tab-content" id="mediaTab">
                    <div class="form-group">
                        <label for="moviePoster">Poster URL</label>
                        <input type="url" id="moviePoster" name="poster" placeholder="https://...">
                        <div class="poster-preview" id="posterPreview" style="display: none;">
                            <img id="posterImage" src="" alt="Preview">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="movieBackdrop">Backdrop URL</label>
                        <input type="url" id="movieBackdrop" name="backdrop" placeholder="https://...">
                    </div>

                    <div class="form-group">
                        <label for="movieTrailer">Trailer URL</label>
                        <input type="url" id="movieTrailer" name="trailer_url" placeholder="https://youtube.com/...">
                    </div>

                    <div class="form-group">
                        <label for="movieStatus">Estado</label>
                        <select id="movieStatus" name="status">
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeMovieModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submitText">Agregar Película</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incluir todos los estilos CSS desde el archivo anterior -->
<style>
/* Incluir todos los estilos del archivo anterior */
.admin-movies-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

.admin-header {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-lg);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--spacing-lg);
}

.admin-breadcrumb {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: var(--spacing-sm);
}

.admin-breadcrumb a {
    color: var(--accent);
    text-decoration: none;
}

.header-content h1 {
    font-size: 2rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.header-content p {
    color: var(--text-secondary);
}

.header-actions {
    display: flex;
    gap: var(--spacing-md);
}

.movies-stats {
    margin-bottom: var(--spacing-lg);
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.stat-card {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.stat-icon {
    font-size: 2rem;
    opacity: 0.8;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.filters-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.filters-form {
    display: grid;
    grid-template-columns: 2fr repeat(3, 1fr) auto auto;
    gap: var(--spacing-md);
    align-items: end;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 0.75rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
}

/* Resto de estilos... */
.hidden { display: none !important; }

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        text-align: center;
    }

    .filters-form {
        grid-template-columns: 1fr;
        gap: var(--spacing-sm);
    }

    .stats-cards {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<script>
// JavaScript functions
function showAddMovieModal() {
    document.getElementById('modalTitle').textContent = '➕ Agregar Nueva Película';
    document.getElementById('submitText').textContent = 'Agregar Película';
    document.getElementById('movieForm').reset();
    document.getElementById('movieId').value = '';
    document.getElementById('movieModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeMovieModal() {
    document.getElementById('movieModal').classList.remove('active');
    document.body.style.overflow = '';
}

function editMovie(movieId) {
    document.getElementById('modalTitle').textContent = '✏️ Editar Película';
    document.getElementById('submitText').textContent = 'Actualizar Película';
    document.getElementById('movieId').value = movieId;
    document.getElementById('movieModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function viewMovie(movieId) {
    window.open('../../pages/content.php?id=' + movieId, '_blank');
}

function deleteMovie(movieId) {
    if (confirm('¿Estás seguro de que quieres eliminar esta película?')) {
        alert('Película eliminada exitosamente (demo)');
        setTimeout(function() {
            window.location.reload();
        }, 1500);
    }
}

function bulkImport() {
    alert('Función de importación en lote próximamente');
}

function exportMovies() {
    alert('Exportando lista de películas...');
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.movie-checkbox');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = selectAll.checked;
    });
}

function bulkActivate() {
    alert('Función de activación en lote próximamente');
}

function bulkDeactivate() {
    alert('Función de desactivación en lote próximamente');
}

function bulkDelete() {
    alert('Función de eliminación en lote próximamente');
}

// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update tab content
            document.querySelectorAll('.tab-content').forEach(function(content) {
                content.classList.remove('active');
            });
            document.getElementById(tabId + 'Tab').classList.add('active');
        });
    });
});

// View toggle
document.querySelectorAll('.view-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const view = this.dataset.view;
        
        document.querySelectorAll('.view-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        this.classList.add('active');
        
        if (view === 'table') {
            document.getElementById('tableView').classList.remove('hidden');
            document.getElementById('gridView').classList.add('hidden');
        } else {
            document.getElementById('tableView').classList.add('hidden');
            document.getElementById('gridView').classList.remove('hidden');
        }
    });
});

// Form submission
document.getElementById('movieForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const movieId = document.getElementById('movieId').value;
    const isEdit = movieId !== '';
    
    alert(isEdit ? 'Película actualizada exitosamente (demo)' : 'Película agregada exitosamente (demo)');
    closeMovieModal();
    
    setTimeout(function() {
        window.location.reload();
    }, 1500);
});

// Poster preview
document.getElementById('moviePoster').addEventListener('input', function() {
    const url = this.value;
    const preview = document.getElementById('posterPreview');
    const img = document.getElementById('posterImage');
    
    if (url) {
        img.src = url;
        preview.style.display = 'block';
        
        img.onerror = function() {
            preview.style.display = 'none';
        };
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>