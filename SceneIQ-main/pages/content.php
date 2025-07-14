<?php
// pages/content.php
$contentId = intval($_GET['id'] ?? 0);

if (!$contentId) {
    header('Location: ../index.php');
    exit;
}

require_once '../includes/header.php';

$content = $sceneiq->getContentById($contentId);

if (!$content) {
    $pageTitle = "Contenido no encontrado";
    echo "<div class='alert alert-error'>El contenido solicitado no existe.</div>";
    require_once '../includes/footer.php';
    exit;
}

$pageTitle = $content['title'];
$pageDescription = $content['synopsis'] ?? "Detalles de " . $content['title'];

// Obtener reseñas
$reviews = $sceneiq->getReviews($contentId, 10);

// Verificar si el usuario ya reseñó este contenido
$userReview = null;
if ($user) {
    foreach ($reviews as $review) {
        if ($review['user_id'] == $user['id']) {
            $userReview = $review;
            break;
        }
    }
}

// Logear actividad de visualización
if ($user) {
    $sceneiq->logActivity($user['id'], 'view', $contentId);
}
?>

<div class="content-detail-container">
    <!-- Hero Section -->
    <section class="content-hero">
        <div class="hero-backdrop">
            <?php if ($content['backdrop']): ?>
                <img src="<?php echo $content['backdrop']; ?>" alt="<?php echo escape($content['title']); ?>" class="backdrop-image">
            <?php endif; ?>
        </div>
        <div class="hero-overlay"></div>
        
        <div class="hero-content">
            <div class="content-poster">
                <img src="<?php echo $content['poster'] ?? '../assets/images/placeholder.jpg'; ?>" 
                     alt="<?php echo escape($content['title']); ?>" 
                     class="poster-image">
            </div>
            
            <div class="content-info">
                <nav class="content-breadcrumb">
                    <a href="../index.php">Inicio</a> › 
                    <a href="<?php echo $content['type']; ?>s.php"><?php echo ucfirst($content['type']); ?>s</a> › 
                    <?php echo escape($content['title']); ?>
                </nav>
                
                <h1 class="content-title"><?php echo escape($content['title']); ?></h1>
                
                <div class="content-meta">
                    <span><?php echo $content['year']; ?></span>
                    <span>•</span>
                    <span><?php echo $content['type'] === 'movie' ? '🎬 Película' : '📺 Serie'; ?></span>
                    <?php if ($content['duration']): ?>
                        <span>•</span>
                        <span><?php echo $content['duration']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="content-rating">
                    <div class="rating-item">
                        <span class="rating-label">IMDb</span>
                        <div class="rating-value">
                            <span>⭐</span>
                            <span><?php echo number_format($content['imdb_rating'], 1); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($content['avg_rating'] && $content['review_count'] > 0): ?>
                        <div class="rating-item">
                            <span class="rating-label">SceneIQ</span>
                            <div class="rating-value">
                                <span>⭐</span>
                                <span><?php echo number_format($content['avg_rating'], 1); ?></span>
                                <span>(<?php echo $content['review_count']; ?>)</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($content['genres']): ?>
                    <div class="content-genres">
                        <?php foreach (explode(',', $content['genres']) as $genre): ?>
                            <span class="genre-pill"><?php echo escape(trim($genre)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="content-actions">
                    <?php if ($user): ?>
                        <button class="btn btn-review" onclick="openReviewModal(<?php echo $contentId; ?>)">
                            <?php echo $userReview ? '✏️ Editar Reseña' : '📝 Escribir Reseña'; ?>
                        </button>
                        <button class="btn btn-wishlist" onclick="addToWatchlist(<?php echo $contentId; ?>)">
                            ➕ Mi Lista
                        </button>
                        <button class="btn btn-secondary" onclick="addToFavorites(<?php echo $contentId; ?>)">
                            ❤️ Favoritos
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Iniciar Sesión para Reseñar</a>
                    <?php endif; ?>
                    
                    <button class="btn btn-secondary" onclick="shareContent()">
                        🔗 Compartir
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Body -->
    <div class="content-body">
        <div class="content-main">
            <!-- Synopsis -->
            <section class="content-section">
                <h2>Sinopsis</h2>
                <div class="synopsis-content">
                    <p><?php echo escape($content['synopsis'] ?? 'Sin sinopsis disponible.'); ?></p>
                </div>
            </section>

            <!-- Trailer -->
            <?php if ($content['trailer_url']): ?>
                <section class="content-section">
                    <h2>Tráiler</h2>
                    <div class="trailer-container">
                        <iframe src="<?php echo $content['trailer_url']; ?>" 
                                frameborder="0" 
                                allowfullscreen>
                        </iframe>
                    </div>
                </section>
            <?php endif; ?>

            <!-- User Review (if exists) -->
            <?php if ($userReview): ?>
                <section class="content-section">
                    <h2>Tu Reseña</h2>
                    <div class="user-review-card">
                        <div class="review-header">
                            <div class="rating-value">
                                <span>⭐</span>
                                <span><?php echo $userReview['rating']; ?>/10</span>
                            </div>
                            <div class="review-actions">
                                <button class="btn-small btn-secondary" onclick="openReviewModal(<?php echo $contentId; ?>)">
                                    Editar
                                </button>
                            </div>
                        </div>
                        
                        <?php if ($userReview['spoiler_alert']): ?>
                            <div class="spoiler-warning">
                                <span>⚠️ Contiene spoilers</span>
                                <button class="spoiler-toggle" onclick="toggleSpoiler(this)">Mostrar</button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="review-text <?php echo $userReview['spoiler_alert'] ? 'spoiler-content' : ''; ?>" 
                             style="<?php echo $userReview['spoiler_alert'] ? 'display: none;' : ''; ?>">
                            <p><?php echo escape($userReview['review_text'] ?: 'Sin comentarios.'); ?></p>
                        </div>
                        
                        <div class="review-meta">
                            Reseñado el <?php echo $sceneiq->formatDate($userReview['created_at']); ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Reviews -->
            <section class="content-section">
                <h2>Reseñas de la Comunidad (<?php echo count($reviews); ?>)</h2>
                
                <?php if (empty($reviews)): ?>
                    <div class="no-reviews">
                        <div class="no-reviews-icon">📝</div>
                        <h3>Aún no hay reseñas</h3>
                        <p>¡Sé el primero en compartir tu opinión sobre este contenido!</p>
                        <?php if ($user): ?>
                            <button class="btn btn-primary" onclick="openReviewModal(<?php echo $contentId; ?>)">
                                Escribir Primera Reseña
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="reviews-container">
                        <?php foreach ($reviews as $review): ?>
                            <?php if ($review['user_id'] == ($user['id'] ?? 0)) continue; // Skip user's own review ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <img src="<?php echo $review['avatar'] ?? '../assets/images/default-avatar.png'; ?>" 
                                             alt="<?php echo escape($review['username']); ?>" 
                                             class="reviewer-avatar">
                                        <div>
                                            <div class="reviewer-name"><?php echo escape($review['full_name'] ?? $review['username']); ?></div>
                                            <div class="review-date"><?php echo $sceneiq->timeAgo($review['created_at']); ?></div>
                                        </div>
                                    </div>
                                    <div class="rating-value">
                                        <span>⭐</span>
                                        <span><?php echo $review['rating']; ?>/10</span>
                                    </div>
                                </div>
                                
                                <?php if ($review['spoiler_alert']): ?>
                                    <div class="spoiler-warning">
                                        <span>⚠️ Contiene spoilers</span>
                                        <button class="spoiler-toggle" onclick="toggleSpoiler(this)">Mostrar</button>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="review-content <?php echo $review['spoiler_alert'] ? 'spoiler-content' : ''; ?>" 
                                     style="<?php echo $review['spoiler_alert'] ? 'display: none;' : ''; ?>">
                                    <p><?php echo escape($review['review_text'] ?: 'Sin comentarios adicionales.'); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Sidebar -->
        <div class="content-sidebar">
            <!-- Information -->
            <div class="sidebar-section">
                <h3>Información</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Año</span>
                        <span class="info-value"><?php echo $content['year']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipo</span>
                        <span class="info-value"><?php echo $content['type'] === 'movie' ? 'Película' : 'Serie'; ?></span>
                    </div>
                    <?php if ($content['duration']): ?>
                        <div class="info-item">
                            <span class="info-label">Duración</span>
                            <span class="info-value"><?php echo $content['duration']; ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($content['tmdb_id']): ?>
                        <div class="info-item">
                            <span class="info-label">TMDB ID</span>
                            <span class="info-value"><?php echo $content['tmdb_id']; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats -->
            <div class="sidebar-section">
                <h3>Estadísticas</h3>
                <div class="stats-list">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $content['review_count'] ?? 0; ?></span>
                        <span class="stat-label">Reseñas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($content['avg_rating'] ?? $content['imdb_rating'], 1); ?></span>
                        <span class="stat-label">Rating</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo rand(50, 500); ?></span>
                        <span class="stat-label">En Listas</span>
                    </div>
                </div>
            </div>

            <!-- Related Content -->
            <div class="sidebar-section">
                <h3>Contenido Relacionado</h3>
                <div class="related-content">
                    <?php 
                    $relatedContent = $sceneiq->getContent(4, 0, $content['type']);
                    foreach ($relatedContent as $related): 
                        if ($related['id'] == $contentId) continue;
                    ?>
                        <a href="content.php?id=<?php echo $related['id']; ?>" class="related-item">
                            <img src="<?php echo $related['poster'] ?? '../assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo escape($related['title']); ?>" 
                                 class="related-poster">
                            <div class="related-info">
                                <div class="related-title"><?php echo escape($related['title']); ?></div>
                                <div class="related-meta">
                                    <span class="related-year"><?php echo $related['year']; ?></span>
                                    <span class="related-rating">⭐ <?php echo $related['imdb_rating']; ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<?php if ($user): ?>
<div class="modal" id="reviewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php echo $userReview ? 'Editar Reseña' : 'Escribir Reseña'; ?></h3>
            <button class="modal-close" onclick="closeReviewModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="reviewForm" method="POST" action="../api/reviews.php">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                <input type="hidden" name="content_id" value="<?php echo $contentId; ?>">
                
                <div class="form-group">
                    <label>Calificación</label>
                    <div class="rating-input">
                        <div class="stars" id="ratingStars">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <span class="star <?php echo $userReview && $userReview['rating'] >= $i ? 'active' : ''; ?>" 
                                      data-rating="<?php echo $i; ?>">⭐</span>
                            <?php endfor; ?>
                        </div>
                        <span id="ratingValue"><?php echo $userReview ? $userReview['rating'] : '0'; ?>/10</span>
                        <input type="hidden" name="rating" id="ratingInput" value="<?php echo $userReview ? $userReview['rating'] : '0'; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reviewText">Tu reseña (opcional)</label>
                    <textarea id="reviewText" name="review_text" rows="6" 
                              placeholder="Comparte tu opinión sobre esta película/serie..."><?php echo $userReview ? escape($userReview['review_text']) : ''; ?></textarea>
                </div>
                
                <div class="form-group form-checkbox">
                    <input type="checkbox" id="spoilerAlert" name="spoiler_alert" 
                           <?php echo $userReview && $userReview['spoiler_alert'] ? 'checked' : ''; ?>>
                    <label for="spoilerAlert">Mi reseña contiene spoilers</label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $userReview ? 'Actualizar Reseña' : 'Publicar Reseña'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Rating system
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('#ratingStars .star');
    const ratingValue = document.getElementById('ratingValue');
    const ratingInput = document.getElementById('ratingInput');
    let currentRating = <?php echo $userReview ? $userReview['rating'] : 0; ?>;

    stars.forEach((star, index) => {
        star.addEventListener('mouseenter', () => {
            highlightStars(index + 1);
        });

        star.addEventListener('mouseleave', () => {
            highlightStars(currentRating);
        });

        star.addEventListener('click', () => {
            currentRating = index + 1;
            highlightStars(currentRating);
            ratingValue.textContent = `${currentRating}/10`;
            ratingInput.value = currentRating;
        });
    });

    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
});

// Modal functions
function openReviewModal() {
    document.getElementById('reviewModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Spoiler toggle
function toggleSpoiler(button) {
    const spoilerContent = button.closest('.review-card, .user-review-card').querySelector('.spoiler-content');
    if (spoilerContent.style.display === 'none') {
        spoilerContent.style.display = 'block';
        button.textContent = 'Ocultar';
    } else {
        spoilerContent.style.display = 'none';
        button.textContent = 'Mostrar';
    }
}

// List management functions
async function addToWatchlist(contentId) {
    if (!window.sceneIQConfig.isLoggedIn) {
        window.location.href = 'login.php';
        return;
    }

    try {
        const response = await fetch('../api/user-lists.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.sceneIQConfig.csrfToken
            },
            body: JSON.stringify({
                csrf_token: window.sceneIQConfig.csrfToken,
                content_id: contentId,
                list_type: 'watchlist',
                action: 'add'
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showNotification('Agregado a tu lista de seguimiento', 'success');
        } else {
            showNotification(data.message || 'Error al agregar a la lista', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al agregar a la lista', 'error');
    }
}

async function addToFavorites(contentId) {
    if (!window.sceneIQConfig.isLoggedIn) {
        window.location.href = 'login.php';
        return;
    }

    try {
        const response = await fetch('../api/user-lists.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.sceneIQConfig.csrfToken
            },
            body: JSON.stringify({
                csrf_token: window.sceneIQConfig.csrfToken,
                content_id: contentId,
                list_type: 'favorites',
                action: 'add'
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showNotification('Agregado a tus favoritos', 'success');
        } else {
            showNotification(data.message || 'Error al agregar a favoritos', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al agregar a favoritos', 'error');
    }
}

function shareContent() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo escape($content['title']); ?>',
            text: '<?php echo escape($content['synopsis'] ?? ''); ?>',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showNotification('Enlace copiado al portapapeles', 'success');
        });
    }
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

// Review form submission
document.getElementById('reviewForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    if (data.rating == 0) {
        showNotification('Por favor, selecciona una calificación', 'error');
        return;
    }
    
    try {
        const response = await fetch('../api/reviews.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.sceneIQConfig.csrfToken
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Reseña guardada exitosamente', 'success');
            closeReviewModal();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(result.message || 'Error al guardar la reseña', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al guardar la reseña', 'error');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>