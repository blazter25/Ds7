<?php
// pages/my-reviews.php
$pageTitle = "Mis Reseñas";
require_once '../includes/header.php';

// Verificar que el usuario esté logueado
if (!$user) {
    redirect('login.php');
}

$success = '';
$error = '';

// Manejar eliminación de reseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    if (!$sceneiq->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        $reviewId = intval($_POST['review_id']);
        // Aquí eliminarías la reseña de la BD
        $success = 'Reseña eliminada exitosamente.';
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
    ],
    [
        'id' => 4,
        'content_id' => 4,
        'title' => 'Stranger Things',
        'year' => 2016,
        'type' => 'series',
        'poster' => 'https://image.tmdb.org/t/p/w300/x2LSRK2Cm7MZhjluni1msVJ3wDF.jpg',
        'rating' => 8.0,
        'review_text' => 'Nostalgia pura de los 80s con una historia original y personajes entrañables. La primera temporada es perfecta, aunque las siguientes no mantienen del todo el nivel inicial.',
        'spoiler_alert' => false,
        'created_at' => '2024-12-20 19:20:00',
        'likes' => rand(10, 50),
        'comments' => rand(2, 15)
    ],
    [
        'id' => 5,
        'content_id' => 5,
        'title' => 'The Godfather',
        'year' => 1972,
        'type' => 'movie',
        'poster' => 'https://image.tmdb.org/t/p/w300/3bhkrj58Vtu7enYsRolD1fZdja1.jpg',
        'rating' => 9.5,
        'review_text' => 'Un clásico absoluto que define el género del crimen. La actuación de Marlon Brando es legendaria. Una historia familiar épica contada con maestría.',
        'spoiler_alert' => false,
        'created_at' => '2024-12-15 21:10:00',
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
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo escape($error); ?>
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

    <!-- Pagination -->
    <?php if (count($myReviews) > 0): ?>
        <div class="pagination-section">
            <div class="pagination-info">
                Mostrando todas tus reseñas
            </div>
        </div>
    <?php endif; ?>
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

.pagination-section {
    text-align: center;
    margin-top: var(--spacing-lg);
    color: var(--text-secondary);
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
}
</style>

<script>
function editReview(reviewId) {
    showNotification('Función de edición próximamente', 'info');
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

function deleteReview(reviewId) {
    document.getElementById('deleteReviewId').value = reviewId;
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
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

function showWriteReviewModal() {
    showNotification('Función de escritura de reseñas próximamente', 'info');
}

function exportReviews() {
    showNotification('Exportando tus reseñas...', 'info');
    setTimeout(() => {
        showNotification('Reseñas exportadas exitosamente', 'success');
    }, 2000);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer; margin-left: 1rem;">&times;</button>
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

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeDeleteModal();
    }
});

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>