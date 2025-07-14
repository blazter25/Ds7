<?php
// pages/my-reviews.php - Enhanced with Add Review functionality
$pageTitle = "Mis Reseñas";
require_once '../includes/header.php';

// Verificar que el usuario esté logueado
if (!$user) {
    redirect('login.php');
}

$success = '';
$error = '';

// Manejar adición/edición de reseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_review']) || isset($_POST['edit_review'])) {
        if (!$sceneiq->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = 'Token de seguridad inválido.';
        } else {
            $contentId = intval($_POST['content_id']);
            $rating = floatval($_POST['rating']);
            $reviewText = trim($_POST['review_text'] ?? '');
            $spoilerAlert = isset($_POST['spoiler_alert']);
            
            if (!$contentId || $rating < 0.5 || $rating > 10) {
                $error = 'Datos inválidos. Verifica el contenido y la calificación.';
            } else {
                // Aquí procesarías la adición/edición en la BD
                $action = isset($_POST['add_review']) ? 'agregada' : 'actualizada';
                $success = "Reseña {$action} exitosamente.";
            }
        }
    } elseif (isset($_POST['delete_review'])) {
        if (!$sceneiq->validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = 'Token de seguridad inválido.';
        } else {
            $reviewId = intval($_POST['review_id']);
            // Aquí eliminarías la reseña de la BD
            $success = 'Reseña eliminada exitosamente.';
        }
    }
}

// Obtener estadísticas del usuario
$userStats = [
    'total_reviews' => rand(5, 25),
    'avg_rating' => round(rand(70, 95) / 10, 1),
    'total_likes' => rand(50, 200),
    'genres_reviewed' => rand(5, 10)
];

// Generar reseñas de ejemplo
$myReviews = [
    [
        'id' => 1,
        'content_id' => 1,
        'title' => 'The Dark Knight',
        'year' => 2008,
        'type' => 'movie',
        'poster' => 'https://image.tmdb.org/t/p/w300/qJ2tW6WMUDux911r6m7haRef0WH.jpg',
        'rating' => 9.0,
        'review_text' => 'Una obra maestra del cine de superhéroes. Christopher Nolan logró crear una película que trasciende el género, con actuaciones excepcionales especialmente de Heath Ledger como el Joker. La cinematografía, el guión y la dirección son impecables. Una experiencia cinematográfica que permanece contigo mucho después de salir del cine.',
        'spoiler_alert' => false,
        'created_at' => '2025-01-10 20:30:00',
        'likes' => rand(10, 50),
        'comments' => rand(2, 15)
    ],
    [
        'id' => 2,
        'content_id' => 2,
        'title' => 'Breaking Bad',
        'year' => 2008,
        'type' => 'series',
        'poster' => 'https://image.tmdb.org/t/p/w300/ggFHVNu6YYI5L9pCfOacjizRGt.jpg',
        'rating' => 9.5,
        'review_text' => 'Sin duda una de las mejores series jamás creadas. La transformación de Walter White es fascinante y aterradora a la vez. Cada episodio te mantiene al borde del asiento. La actuación de Bryan Cranston es simplemente espectacular.',
        'spoiler_alert' => true,
        'created_at' => '2025-01-05 14:15:00',
        'likes' => rand(10, 50),
        'comments' => rand(2, 15)
    ],
    [
        'id' => 3,
        'content_id' => 3,
        'title' => 'Inception',
        'year' => 2010,
        'type' => 'movie',
        'poster' => 'https://image.tmdb.org/t/p/w300/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
        'rating' => 8.5,
        'review_text' => 'Una película compleja y visualmente impresionante. Nolan nos presenta un concepto original sobre los sueños que te hace pensar durante días. La banda sonora de Hans Zimmer es perfecta.',
        'spoiler_alert' => false,
        'created_at' => '2024-12-28 16:45:00',
        'likes' => rand(10, 50),
        'comments' => rand(2, 15)
    ]
];

// Filtros
$filterType = $_GET['type'] ?? 'all';
$sortBy = $_GET['sort'] ?? 'recent';

// Aplicar filtros
if ($filterType !== 'all') {
    $myReviews = array_filter($myReviews, function($review) use ($filterType) {
        return $review['type'] === $filterType;
    });
}

// Aplicar ordenamiento
switch ($sortBy) {
    case 'rating_high':
        usort($myReviews, function($a, $b) { return $b['rating'] <=> $a['rating']; });
        break;
    case 'rating_low':
        usort($myReviews, function($a, $b) { return $a['rating'] <=> $b['rating']; });
        break;
    case 'popular':
        usort($myReviews, function($a, $b) { return $b['likes'] <=> $a['likes']; });
        break;
    case 'oldest':
        usort($myReviews, function($a, $b) { return strtotime($a['created_at']) <=> strtotime($b['created_at']); });
        break;
    default: // recent
        usort($myReviews, function($a, $b) { return strtotime($b['created_at']) <=> strtotime($a['created_at']); });
        break;
}
?>

<div class="my-reviews-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>📝 Mis Reseñas</h1>
            <p>Todas las reseñas que has escrito en SceneIQ</p>
        </div>
        
        <div class="header-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $userStats['total_reviews']; ?></div>
                <div class="stat-label">Reseñas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $userStats['avg_rating']; ?></div>
                <div class="stat-label">Rating Promedio</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $userStats['total_likes']; ?></div>
                <div class="stat-label">Likes Recibidos</div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo escape($success); ?>
            <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo escape($error); ?>
            <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
    <?php endif; ?>

    <!-- Filters and Actions -->
    <div class="controls-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <select name="type" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>Todos los tipos</option>
                    <option value="movie" <?php echo $filterType === 'movie' ? 'selected' : ''; ?>>🎬 Películas</option>
                    <option value="series" <?php echo $filterType === 'series' ? 'selected' : ''; ?>>📺 Series</option>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="recent" <?php echo $sortBy === 'recent' ? 'selected' : ''; ?>>Más recientes</option>
                    <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Más antiguas</option>
                    <option value="rating_high" <?php echo $sortBy === 'rating_high' ? 'selected' : ''; ?>>Rating más alto</option>
                    <option value="rating_low" <?php echo $sortBy === 'rating_low' ? 'selected' : ''; ?>>Rating más bajo</option>
                    <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Más populares</option>
                </select>
            </div>
        </form>

        <div class="action-buttons">
            <button class="btn btn-primary" onclick="showWriteReviewModal()">
                ✏️ Escribir Nueva Reseña
            </button>
            <button class="btn btn-secondary" onclick="exportReviews()">
                📊 Exportar Reseñas
            </button>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="reviews-section">
        <?php if (empty($myReviews)): ?>
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>Aún no has escrito reseñas</h3>
                <p>Comparte tu opinión sobre las películas y series que has visto</p>
                <button class="btn btn-primary" onclick="showWriteReviewModal()">
                    Escribir Primera Reseña
                </button>
            </div>
        <?php else: ?>
            <div class="reviews-count">
                Mostrando <?php echo count($myReviews); ?> reseña<?php echo count($myReviews) !== 1 ? 's' : ''; ?>
            </div>

            <div class="reviews-list">
                <?php foreach ($myReviews as $review): ?>
                    <div class="review-card" data-review-id="<?php echo $review['id']; ?>">
                        <div class="review-header">
                            <div class="content-info">
                                <img src="<?php echo $review['poster']; ?>" 
                                     alt="<?php echo escape($review['title']); ?>" 
                                     class="content-poster">
                                <div class="content-details">
                                    <h3 class="content-title">
                                        <a href="content.php?id=<?php echo $review['content_id']; ?>">
                                            <?php echo escape($review['title']); ?>
                                        </a>
                                    </h3>
                                    <div class="content-meta">
                                        <span class="content-year"><?php echo $review['year']; ?></span>
                                        <span class="content-type">
                                            <?php echo $review['type'] === 'movie' ? '🎬 Película' : '📺 Serie'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="review-rating">
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo ($i <= $review['rating'] / 2) ? 'active' : ''; ?>">⭐</span>
                                    <?php endfor; ?>
                                </div>
                                <div class="rating-value"><?php echo $review['rating']; ?>/10</div>
                            </div>
                        </div>

                        <?php if ($review['spoiler_alert']): ?>
                            <div class="spoiler-warning">
                                <span class="spoiler-icon">⚠️</span>
                                <span>Contiene spoilers</span>
                                <button class="spoiler-toggle" onclick="toggleSpoiler(this)">Mostrar</button>
                            </div>
                        <?php endif; ?>

                        <div class="review-content <?php echo $review['spoiler_alert'] ? 'spoiler-hidden' : ''; ?>">
                            <p><?php echo escape($review['review_text']); ?></p>
                        </div>

                        <div class="review-footer">
                            <div class="review-meta">
                                <span class="review-date">
                                    📅 <?php echo $sceneiq->formatDate($review['created_at'], 'd/m/Y'); ?>
                                </span>
                                <span class="review-stats">
                                    👍 <?php echo $review['likes']; ?> likes
                                </span>
                                <span class="review-stats">
                                    💬 <?php echo $review['comments']; ?> comentarios
                                </span>
                            </div>
                            
                            <div class="review-actions">
                                <button class="action-btn" onclick="editReview(<?php echo $review['id']; ?>)" title="Editar">
                                    ✏️ Editar
                                </button>
                                <button class="action-btn" onclick="shareReview(<?php echo $review['id']; ?>)" title="Compartir">
                                    🔗 Compartir
                                </button>
                                <button class="action-btn danger" onclick="deleteReview(<?php echo $review['id']; ?>)" title="Eliminar">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Load More Button -->
    <?php if (count($myReviews) >= 10): ?>
        <div class="load-more-section">
            <button class="btn btn-secondary" onclick="loadMoreReviews()">
                Cargar más reseñas
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Write/Edit Review Modal -->
<div class="modal" id="reviewModal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="reviewModalTitle">✏️ Escribir Nueva Reseña</h3>
            <button class="modal-close" onclick="closeReviewModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Content Search Section -->
            <div id="contentSearchSection">
                <div class="search-section">
                    <h4>🔍 Buscar contenido para reseñar</h4>
                    <div class="content-search">
                        <input type="text" id="contentSearchInput" 
                               placeholder="Buscar películas o series..." 
                               class="search-input">
                        <div id="searchResults" class="search-results"></div>
                    </div>
                </div>
            </div>

            <!-- Selected Content Display -->
            <div id="selectedContentSection" style="display: none;">
                <div class="selected-content">
                    <div class="selected-content-preview" id="selectedContentPreview">
                        <!-- Content will be populated here -->
                    </div>
                    <button type="button" class="btn-small btn-secondary" onclick="changeSelectedContent()">
                        Cambiar contenido
                    </button>
                </div>
            </div>

            <!-- Review Form -->
            <form id="reviewForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                <input type="hidden" name="content_id" id="selectedContentId">
                <input type="hidden" name="review_id" id="editingReviewId">
                
                <div class="form-group">
                    <label>⭐ Tu calificación *</label>
                    <div class="rating-input">
                        <div class="rating-stars" id="ratingStars">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <span class="star" data-rating="<?php echo $i; ?>">⭐</span>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-display">
                            <span id="ratingValue">0/10</span>
                            <span class="rating-text" id="ratingText">Selecciona una calificación</span>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reviewText">📝 Tu reseña</label>
                    <textarea id="reviewText" name="review_text" rows="6" 
                              placeholder="Comparte tu opinión sobre esta película/serie. ¿Qué te gustó? ¿Qué no te convenció? ¿La recomendarías?"></textarea>
                    <div class="character-count">
                        <span id="charCount">0</span>/1000 caracteres
                    </div>
                </div>
                
                <div class="form-group form-checkbox">
                    <input type="checkbox" id="spoilerAlert" name="spoiler_alert">
                    <label for="spoilerAlert">⚠️ Mi reseña contiene spoilers</label>
                    <small>Marca esta opción si tu reseña revela información importante de la trama</small>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Cancelar</button>
                    <button type="submit" name="add_review" class="btn btn-primary" id="submitReviewBtn">
                        Publicar Reseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Review Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🗑️ Eliminar Reseña</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que quieres eliminar esta reseña? Esta acción no se puede deshacer.</p>
            <form id="deleteForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                <input type="hidden" name="delete_review" value="1">
                <input type="hidden" name="review_id" id="deleteReviewId" value="">
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar Reseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Previous styles remain the same... */
.my-reviews-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-lg);
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

.stat-card {
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
    margin-top: 0.25rem;
}

.controls-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.filters-form {
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
}

.filter-select {
    padding: 0.5rem 1rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
    font-size: 0.9rem;
}

.action-buttons {
    display: flex;
    gap: var(--spacing-md);
}

.reviews-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.reviews-count {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
    font-size: 0.9rem;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.review-card {
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    transition: var(--transition);
}

.review-card:hover {
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.content-info {
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
}

.content-poster {
    width: 60px;
    height: 90px;
    object-fit: cover;
    border-radius: var(--border-radius-small);
}

.content-title {
    color: var(--text-primary);
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

.content-title a {
    color: inherit;
    text-decoration: none;
    transition: var(--transition);
}

.content-title a:hover {
    color: var(--accent);
}

.content-meta {
    display: flex;
    gap: var(--spacing-sm);
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.review-rating {
    text-align: center;
}

.rating-stars {
    margin-bottom: 0.25rem;
}

.star {
    color: #666;
    font-size: 0.9rem;
}

.star.active {
    color: #ffd700;
}

.rating-value {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.1rem;
}

.spoiler-warning {
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
    border-radius: var(--border-radius-small);
    padding: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.spoiler-toggle {
    background: var(--warning);
    border: none;
    border-radius: var(--border-radius-small);
    padding: 0.3rem 0.6rem;
    color: #333;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
}

.review-content {
    margin-bottom: var(--spacing-lg);
    transition: var(--transition);
}

.spoiler-hidden {
    display: none;
}

.review-content p {
    color: var(--text-primary);
    line-height: 1.6;
    font-size: 1rem;
}

.review-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: var(--spacing-md);
}

.review-meta {
    display: flex;
    gap: var(--spacing-md);
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.review-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.action-btn {
    padding: 0.4rem 0.8rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.8rem;
}

.action-btn:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.action-btn.danger:hover {
    background: var(--error);
    border-color: var(--error);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-lg);
    opacity: 0.5;
}

.empty-state h3 {
    color: var(--text-primary);
    font-size: 1.5rem;
    margin-bottom: var(--spacing-md);
}

.empty-state p {
    color: var(--text-secondary);
    font-size: 1.1rem;
    margin-bottom: var(--spacing-lg);
}

.alert {
    padding: var(--spacing-md);
    border-radius: var(--border-radius-small);
    margin-bottom: var(--spacing-lg);
    border: 1px solid;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.alert-success {
    background: rgba(0, 210, 255, 0.1);
    border-color: rgba(0, 210, 255, 0.3);
    color: var(--success);
}

.alert-error {
    background: rgba(255, 107, 107, 0.1);
    border-color: rgba(255, 107, 107, 0.3);
    color: var(--error);
}

.alert-close {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 1.2rem;
    padding: 0.25rem;
}

.load-more-section {
    text-align: center;
    margin-top: var(--spacing-lg);
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
}

.modal.active {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.8);
    transition: var(--transition);
}

.modal-content.large {
    max-width: 800px;
}

.modal.active .modal-content {
    transform: scale(1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h3 {
    color: var(--text-primary);
    font-size: 1.3rem;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 1.5rem;
    cursor: pointer;
    transition: var(--transition);
    padding: 0.5rem;
}

.modal-close:hover {
    color: var(--text-primary);
}

.modal-body {
    padding: var(--spacing-lg);
}

.modal-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: flex-end;
    margin-top: var(--spacing-lg);
}

/* Review Modal Specific Styles */
.search-section {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-lg);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
}

.search-section h4 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
    font-size: 1.1rem;
}

.content-search {
    position: relative;
}

.search-input {
    width: 100%;
    padding: 0.75rem;
    background: var(--card-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
    font-size: 1rem;
}

.search-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--card-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1001;
    display: none;
    margin-top: 0.5rem;
}

.search-results.active {
    display: block;
}

.search-result-item {
    padding: var(--spacing-sm);
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    display: flex;
    gap: var(--spacing-sm);
    align-items: center;
}

.search-result-item:hover {
    background: var(--glass-bg);
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-poster {
    width: 40px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}

.search-result-info {
    flex: 1;
}

.search-result-title {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.search-result-meta {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.selected-content {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-lg);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    border: 2px solid var(--accent);
}

.selected-content-preview {
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.selected-poster {
    width: 80px;
    height: 120px;
    object-fit: cover;
    border-radius: var(--border-radius-small);
}

.selected-info h4 {
    color: var(--text-primary);
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

.selected-info p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.selected-synopsis {
    color: var(--text-secondary);
    font-size: 0.8rem;
    line-height: 1.4;
    font-style: italic;
}

/* Rating Input Styles */
.rating-input {
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    padding: var(--spacing-lg);
}

.rating-stars {
    display: flex;
    gap: 0.25rem;
    margin-bottom: var(--spacing-md);
    justify-content: center;
}

.rating-stars .star {
    font-size: 2rem;
    cursor: pointer;
    transition: var(--transition);
    color: #666;
    text-shadow: 0 0 5px rgba(0,0,0,0.3);
}

.rating-stars .star:hover,
.rating-stars .star.active {
    color: #ffd700;
    transform: scale(1.1);
}

.rating-display {
    text-align: center;
}

.rating-display #ratingValue {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-right: var(--spacing-sm);
}

.rating-text {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* Form Styles */
.form-group {
    margin-bottom: var(--spacing-lg);
}

.form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    color: var(--text-primary);
    font-weight: 500;
    font-size: 1rem;
}

.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
    font-size: 1rem;
    resize: vertical;
    min-height: 120px;
}

.form-group textarea:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
}

.character-count {
    text-align: right;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.character-count.over-limit {
    color: var(--error);
}

.form-checkbox {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-sm);
}

.form-checkbox input {
    width: auto;
    margin-top: 0.25rem;
}

.form-checkbox label {
    margin: 0;
    color: var(--text-primary);
    font-size: 0.9rem;
    font-weight: normal;
}

.form-checkbox small {
    display: block;
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-top: 0.25rem;
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

    .controls-section {
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .review-header {
        flex-direction: column;
        gap: var(--spacing-md);
        text-align: center;
    }

    .review-footer {
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .review-meta {
        justify-content: center;
        flex-wrap: wrap;
    }

    .modal-content {
        width: 95%;
        margin: 1rem;
    }

    .selected-content-preview {
        flex-direction: column;
        text-align: center;
    }

    .rating-stars {
        flex-wrap: wrap;
    }

    .rating-stars .star {
        font-size: 1.5rem;
    }
}
</style>

<script>
// Global variables
let currentRating = 0;
let selectedContent = null;
let editingReview = null;
let searchTimeout;

// Modal functions
function showWriteReviewModal() {
    resetReviewModal();
    document.getElementById('reviewModalTitle').textContent = '✏️ Escribir Nueva Reseña';
    document.getElementById('submitReviewBtn').textContent = 'Publicar Reseña';
    document.getElementById('reviewModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    document.getElementById('contentSearchInput').focus();
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('active');
    document.body.style.overflow = '';
    resetReviewModal();
}

function resetReviewModal() {
    document.getElementById('reviewForm').reset();
    document.getElementById('contentSearchSection').style.display = 'block';
    document.getElementById('selectedContentSection').style.display = 'none';
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('searchResults').classList.remove('active');
    
    // Reset rating
    currentRating = 0;
    updateRatingDisplay();
    
    // Reset character count
    updateCharacterCount();
    
    selectedContent = null;
    editingReview = null;
}

function editReview(reviewId) {
    // Find the review data (in a real app, you'd fetch from server)
    const reviewCard = document.querySelector(`[data-review-id="${reviewId}"]`);
    const title = reviewCard.querySelector('.content-title a').textContent;
    const poster = reviewCard.querySelector('.content-poster').src;
    const rating = parseFloat(reviewCard.querySelector('.rating-value').textContent);
    const reviewText = reviewCard.querySelector('.review-content p').textContent;
    const hasSpoilers = reviewCard.querySelector('.spoiler-warning') !== null;
    
    // Populate modal for editing
    document.getElementById('reviewModalTitle').textContent = '✏️ Editar Reseña';
    document.getElementById('submitReviewBtn').textContent = 'Actualizar Reseña';
    document.getElementById('submitReviewBtn').name = 'edit_review';
    document.getElementById('editingReviewId').value = reviewId;
    
    // Set selected content
    selectedContent = {
        id: reviewId, // Using review ID as content ID for demo
        title: title,
        poster: poster,
        year: 2024,
        type: 'movie'
    };
    
    showSelectedContent();
    
    // Set form values
    setRating(rating);
    document.getElementById('reviewText').value = reviewText;
    document.getElementById('spoilerAlert').checked = hasSpoilers;
    updateCharacterCount();
    
    document.getElementById('reviewModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Content search functions
document.getElementById('contentSearchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
            searchContent(query);
        }, 300);
    } else {
        hideSearchResults();
    }
});

function searchContent(query) {
    // Simulate search results (in a real app, this would be an AJAX call)
    const mockResults = [
        {
            id: 1,
            title: 'The Dark Knight',
            year: 2008,
            type: 'movie',
            poster: 'https://image.tmdb.org/t/p/w200/qJ2tW6WMUDux911r6m7haRef0WH.jpg',
            synopsis: 'Batman debe enfrentar a su mayor enemigo...'
        },
        {
            id: 2,
            title: 'Breaking Bad',
            year: 2008,
            type: 'series',
            poster: 'https://image.tmdb.org/t/p/w200/ggFHVNu6YYI5L9pCfOacjizRGt.jpg',
            synopsis: 'Un profesor de química se convierte...'
        },
        {
            id: 3,
            title: 'Inception',
            year: 2010,
            type: 'movie',
            poster: 'https://image.tmdb.org/t/p/w200/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
            synopsis: 'Un ladrón que roba secretos corporativos...'
        }
    ];
    
    // Filter results based on query
    const filteredResults = mockResults.filter(item => 
        item.title.toLowerCase().includes(query.toLowerCase())
    );
    
    showSearchResults(filteredResults);
}

function showSearchResults(results) {
    const resultsContainer = document.getElementById('searchResults');
    
    if (results.length === 0) {
        resultsContainer.innerHTML = `
            <div class="search-result-item">
                <div class="search-result-info">
                    <div class="search-result-title">No se encontraron resultados</div>
                    <div class="search-result-meta">Intenta con otros términos de búsqueda</div>
                </div>
            </div>
        `;
    } else {
        resultsContainer.innerHTML = results.map(item => `
            <div class="search-result-item" onclick="selectContent(${JSON.stringify(item).replace(/"/g, '&quot;')})">
                <img src="${item.poster}" alt="${item.title}" class="search-result-poster">
                <div class="search-result-info">
                    <div class="search-result-title">${item.title}</div>
                    <div class="search-result-meta">
                        ${item.year} • ${item.type === 'movie' ? '🎬 Película' : '📺 Serie'}
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    resultsContainer.classList.add('active');
}

function hideSearchResults() {
    document.getElementById('searchResults').classList.remove('active');
}

function selectContent(content) {
    selectedContent = content;
    document.getElementById('selectedContentId').value = content.id;
    showSelectedContent();
    hideSearchResults();
}

function showSelectedContent() {
    if (!selectedContent) return;
    
    document.getElementById('selectedContentPreview').innerHTML = `
        <img src="${selectedContent.poster}" alt="${selectedContent.title}" class="selected-poster">
        <div class="selected-info">
            <h4>${selectedContent.title}</h4>
            <p>${selectedContent.year} • ${selectedContent.type === 'movie' ? '🎬 Película' : '📺 Serie'}</p>
            ${selectedContent.synopsis ? `<p class="selected-synopsis">${selectedContent.synopsis}</p>` : ''}
        </div>
    `;
    
    document.getElementById('contentSearchSection').style.display = 'none';
    document.getElementById('selectedContentSection').style.display = 'block';
}

function changeSelectedContent() {
    document.getElementById('contentSearchSection').style.display = 'block';
    document.getElementById('selectedContentSection').style.display = 'none';
    document.getElementById('contentSearchInput').value = '';
    document.getElementById('contentSearchInput').focus();
    selectedContent = null;
    document.getElementById('selectedContentId').value = '';
}

// Rating system
document.querySelectorAll('#ratingStars .star').forEach((star, index) => {
    star.addEventListener('mouseenter', () => {
        highlightStars(index + 1);
    });

    star.addEventListener('mouseleave', () => {
        highlightStars(currentRating);
    });

    star.addEventListener('click', () => {
        setRating(index + 1);
    });
});

function setRating(rating) {
    currentRating = rating;
    document.getElementById('ratingInput').value = rating;
    highlightStars(rating);
    updateRatingDisplay();
}

function highlightStars(rating) {
    document.querySelectorAll('#ratingStars .star').forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function updateRatingDisplay() {
    const ratingTexts = {
        0: 'Selecciona una calificación',
        1: 'Terrible',
        2: 'Muy malo',
        3: 'Malo',
        4: 'Regular',
        5: 'No está mal',
        6: 'Bien',
        7: 'Muy bien',
        8: 'Excelente',
        9: 'Increíble',
        10: 'Obra maestra'
    };
    
    document.getElementById('ratingValue').textContent = currentRating > 0 ? `${currentRating}/10` : '0/10';
    document.getElementById('ratingText').textContent = ratingTexts[currentRating] || 'Selecciona una calificación';
}

// Character count
document.getElementById('reviewText').addEventListener('input', updateCharacterCount);

function updateCharacterCount() {
    const textarea = document.getElementById('reviewText');
    const charCount = document.getElementById('charCount');
    const currentLength = textarea.value.length;
    const maxLength = 1000;
    
    charCount.textContent = currentLength;
    
    if (currentLength > maxLength) {
        charCount.parentElement.classList.add('over-limit');
    } else {
        charCount.parentElement.classList.remove('over-limit');
    }
}

// Form submission
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    if (!selectedContent && !editingReview) {
        e.preventDefault();
        showNotification('Por favor, selecciona un contenido para reseñar', 'error');
        return;
    }
    
    if (currentRating === 0) {
        e.preventDefault();
        showNotification('Por favor, selecciona una calificación', 'error');
        return;
    }
    
    const reviewText = document.getElementById('reviewText').value;
    if (reviewText.length > 1000) {
        e.preventDefault();
        showNotification('La reseña no puede exceder 1000 caracteres', 'error');
        return;
    }
    
    // Form is valid, let it submit
    showNotification('Procesando reseña...', 'info');
});

// Other functions
function deleteReview(reviewId) {
    document.getElementById('deleteReviewId').value = reviewId;
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
}

function shareReview(reviewId) {
    const reviewCard = document.querySelector(`[data-review-id="${reviewId}"]`);
    const title = reviewCard.querySelector('.content-title a').textContent;
    
    if (navigator.share) {
        navigator.share({
            title: `Mi reseña de ${title}`,
            text: 'Mira mi reseña en SceneIQ',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            showNotification('Enlace copiado al portapapeles', 'success');
        });
    }
}

function toggleSpoiler(button) {
    const reviewCard = button.closest('.review-card');
    const spoilerContent = reviewCard.querySelector('.review-content');
    
    if (spoilerContent.classList.contains('spoiler-hidden')) {
        spoilerContent.classList.remove('spoiler-hidden');
        button.textContent = 'Ocultar';
    } else {
        spoilerContent.classList.add('spoiler-hidden');
        button.textContent = 'Mostrar';
    }
}

function exportReviews() {
    showNotification('Exportando tus reseñas...', 'info');
    setTimeout(() => {
        showNotification('Reseñas exportadas exitosamente', 'success');
    }, 2000);
}

function loadMoreReviews() {
    showNotification('Cargando más reseñas...', 'info');
    setTimeout(() => {
        showNotification('No hay más reseñas para mostrar', 'info');
    }, 1000);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    notification.style.position = 'fixed';
    notification.style.top = '100px';
    notification.style.right = '20px';
    notification.style.zIndex = '1001';
    notification.style.maxWidth = '400px';
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 4000);
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        if (e.target.id === 'reviewModal') {
            closeReviewModal();
        } else if (e.target.id === 'deleteModal') {
            closeDeleteModal();
        }
    }
});

// Close modals with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReviewModal();
        closeDeleteModal();
    }
});

// Hide search results when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.content-search')) {
        hideSearchResults();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateRatingDisplay();
    updateCharacterCount();
});
</script>

<?php require_once '../includes/footer.php'; ?>