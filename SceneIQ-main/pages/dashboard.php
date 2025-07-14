<?php
// pages/dashboard.php
$pageTitle = "Dashboard";
require_once '../includes/header.php';

// Verificar que el usuario esté logueado
if (!$user) {
    redirect('login.php');
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
                <button class="action-btn" onclick="openReviewModal()">
                    📝 Escribir Reseña
                </button>
                <button class="action-btn" onclick="getRandomRecommendation()">
                    🎲 Sorpréndeme
                </button>
                <a href="search.php" class="action-btn">
                    🔍 Buscar Contenido
                </a>
                <a href="profile.php" class="action-btn">
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
                        <?php include '../includes/content-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Continue Watching / Recently Viewed -->
        <?php if (!empty($recentlyViewed)): ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">📚 Visto Recientemente</h2>
                <a href="profile.php?tab=watched" class="view-all">Ver todo</a>
            </div>
            <div class="content-grid">
                <?php foreach ($recentlyViewed as $content): ?>
                    <?php include '../includes/content-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Watchlist Preview -->
        <?php if (!empty($watchlist)): ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">📋 Mi Lista de Seguimiento</h2>
                <a href="profile.php?tab=watchlist" class="view-all">Ver todo (<?php echo $userStats['watchlist_count']; ?>)</a>
            </div>
            <div class="content-grid">
                <?php foreach ($watchlist as $content): ?>
                    <?php include '../includes/content-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Favorites Preview -->
        <?php if (!empty($favorites)): ?>
        <section class="content-section">
            <div class="section-header">
                <h2 class="section-title">❤️ Mis Favoritos</h2>
                <a href="profile.php?tab=favorites" class="view-all">Ver todo (<?php echo $userStats['favorites_count']; ?>)</a>
            </div>
            <div class="content-grid">
                <?php foreach ($favorites as $content): ?>
                    <?php include '../includes/content-card.php'; ?>
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
                        <a href="movies.php" class="btn btn-secondary">Ver Películas</a>
                        <a href="series.php" class="btn btn-secondary">Ver Series</a>
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
                <a href="preferences.php" class="btn-small btn-secondary" style="margin-top: 1rem; width: 100%;">
                    Configurar Preferencias
                </a>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="sidebar-section">
            <h3>Actividad Reciente</h3>
            <div class="activity-feed">
                <!-- Simulated activity items -->
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

<!-- Review Modal (Quick Review) -->
<div class="modal" id="quickReviewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✏️ Escribir Reseña Rápida</h3>
            <button class="modal-close" onclick="closeQuickReviewModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="contentSearch" style="margin-bottom: 1rem;">
                <input type="text" id="searchInput" placeholder="Buscar película o serie..." 
                       class="search-input" style="width: 100%;">
                <div id="searchResults" class="search-dropdown"></div>
            </div>
            
            <div id="selectedContent" style="display: none;">
                <div class="selected-content">
                    <div class="content-preview"></div>
                </div>
            </div>
            
            <form id="quickReviewForm">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                <input type="hidden" name="content_id" id="selectedContentId">
                
                <div class="form-group">
                    <label>Calificación</label>
                    <div class="rating-input">
                        <div class="stars" id="quickRatingStars">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <span class="star" data-rating="<?php echo $i; ?>">⭐</span>
                            <?php endfor; ?>
                        </div>
                        <span id="quickRatingValue">0/10</span>
                        <input type="hidden" name="rating" id="quickRatingInput" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="quickReviewText">Tu reseña (opcional)</label>
                    <textarea id="quickReviewText" name="review_text" rows="4" 
                              placeholder="¿Qué te pareció?"></textarea>
                </div>
                
                <div class="form-group form-checkbox">
                    <input type="checkbox" id="quickSpoilerAlert" name="spoiler_alert">
                    <label for="quickSpoilerAlert">Contiene spoilers</label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeQuickReviewModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Publicar Reseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Quick Review Modal
function openReviewModal() {
    document.getElementById('quickReviewModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    document.getElementById('searchInput').focus();
}

function closeQuickReviewModal() {
    document.getElementById('quickReviewModal').classList.remove('active');
    document.body.style.overflow = '';
    // Reset form
    document.getElementById('quickReviewForm').reset();
    document.getElementById('selectedContent').style.display = 'none';
    document.getElementById('contentSearch').style.display = 'block';
    document.getElementById('quickRatingValue').textContent = '0/10';
    document.querySelectorAll('#quickRatingStars .star').forEach(star => star.classList.remove('active'));
}

// Search functionality for quick review
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
            searchContent(query);
        }, 300);
    } else {
        document.getElementById('searchResults').style.display = 'none';
    }
});

async function searchContent(query) {
    try {
        const response = await fetch('../api/search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.sceneIQConfig.csrfToken
            }
        });

        const data = await response.json();
        
        if (data.success && data.content) {
            showRandomRecommendationModal(data.content);
        } else {
            showNotification('No se pudo obtener una recomendación', 'warning');
        }
    } catch (error) {
        console.error('Error getting random recommendation:', error);
        showNotification('Error al obtener recomendación', 'error');
    }
}

function showRandomRecommendationModal(content) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>🎲 Tu Recomendación Sorpresa</h3>
                <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                    <img src="${content.poster || '../assets/images/placeholder.jpg'}" 
                         style="width: 150px; height: 225px; object-fit: cover; border-radius: 12px; flex-shrink: 0;">
                    <div style="flex: 1;">
                        <h4 style="color: var(--text-primary); font-size: 1.5rem; margin-bottom: 0.5rem;">${content.title}</h4>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                            ${content.year} • ${content.type === 'movie' ? 'Película' : 'Serie'}
                            ${content.duration ? ' • ' + content.duration : ''}
                        </p>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span style="color: #ffd700;">⭐</span>
                            <span style="color: var(--text-primary); font-weight: 600;">${content.imdb_rating}</span>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.5rem;">
                            ${content.synopsis || 'Sin descripción disponible.'}
                        </p>
                        <div style="display: flex; gap: 1rem;">
                            <a href="content.php?id=${content.id}" class="btn btn-primary">Ver Detalles</a>
                            <button class="btn btn-secondary" onclick="addToWatchlist(${content.id}); this.closest('.modal').remove();">+ Mi Lista</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

// Refresh recommendations
async function refreshRecommendations() {
    try {
        const response = await fetch('../api/refresh-recommendations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.sceneIQConfig.csrfToken
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showNotification('Recomendaciones actualizadas', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    } catch (error) {
        console.error('Error refreshing recommendations:', error);
        // Fallback: just reload the page
        window.location.reload();
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

// Quick review form submission
document.getElementById('quickReviewForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    if (!data.content_id) {
        showNotification('Por favor, selecciona un contenido', 'error');
        return;
    }
    
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
            showNotification('Reseña publicada exitosamente', 'success');
            closeQuickReviewModal();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(result.message || 'Error al publicar la reseña', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al publicar la reseña', 'error');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.sceneIQConfig.csrfToken
            },
            body: JSON.stringify({ query: query })
        });
        
        const data = await response.json();
        
        if (data.success && data.results.length > 0) {
            showSearchResults(data.results.slice(0, 5));
        } else {
            document.getElementById('searchResults').style.display = 'none';
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}

function showSearchResults(results) {
    const resultsDiv = document.getElementById('searchResults');
    resultsDiv.innerHTML = results.map(item => `
        <div class="search-result-item" onclick="selectContent(${item.id}, '${item.title}', '${item.year}', '${item.type}', '${item.poster || ''}')">
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <img src="${item.poster || '../assets/images/placeholder.jpg'}" 
                     style="width: 40px; height: 60px; object-fit: cover; border-radius: 4px;">
                <div>
                    <div style="color: var(--text-primary); font-weight: 600; font-size: 0.9rem;">${item.title}</div>
                    <div style="color: var(--text-secondary); font-size: 0.8rem;">${item.year} • ${item.type === 'movie' ? 'Película' : 'Serie'}</div>
                </div>
            </div>
        </div>
    `).join('');
    resultsDiv.style.display = 'block';
}

function selectContent(id, title, year, type, poster) {
    document.getElementById('selectedContentId').value = id;
    document.getElementById('contentSearch').style.display = 'none';
    document.getElementById('selectedContent').style.display = 'block';
    
    document.querySelector('.content-preview').innerHTML = `
        <div style="display: flex; gap: 1rem; align-items: center;">
            <img src="${poster || '../assets/images/placeholder.jpg'}" 
                 style="width: 60px; height: 90px; object-fit: cover; border-radius: 8px;">
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 0.5rem;">${title}</h4>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">${year} • ${type === 'movie' ? 'Película' : 'Serie'}</p>
            </div>
        </div>
    `;
    
    document.getElementById('searchResults').style.display = 'none';
}

// Quick rating system
document.addEventListener('DOMContentLoaded', function() {
    const quickStars = document.querySelectorAll('#quickRatingStars .star');
    const quickRatingValue = document.getElementById('quickRatingValue');
    const quickRatingInput = document.getElementById('quickRatingInput');
    let currentQuickRating = 0;

    quickStars.forEach((star, index) => {
        star.addEventListener('mouseenter', () => {
            highlightQuickStars(index + 1);
        });

        star.addEventListener('mouseleave', () => {
            highlightQuickStars(currentQuickRating);
        });

        star.addEventListener('click', () => {
            currentQuickRating = index + 1;
            highlightQuickStars(currentQuickRating);
            quickRatingValue.textContent = `${currentQuickRating}/10`;
            quickRatingInput.value = currentQuickRating;
        });
    });

    function highlightQuickStars(rating) {
        quickStars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
});

// Random recommendation
async function getRandomRecommendation() {
    try {
        const response = await fetch('../api/random-recommendation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.sceneIQConfig.csrfToken
            }  