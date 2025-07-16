<?php
// includes/content-card.php
// Tarjeta para mostrar contenido (películas/series)

if (!isset($content)) return;
?>

<div class="content-card" data-id="<?php echo $content['id']; ?>">
    <div class="content-poster">
        <img src="<?php echo $content['poster'] ?? 'assets/images/placeholder.jpg'; ?>" 
             alt="<?php echo escape($content['title']); ?>"
             onerror="this.src='assets/images/placeholder.jpg'">
        
        <div class="content-overlay">
            <div class="content-actions">
                <button class="action-btn" onclick="addToWatchlist(<?php echo $content['id']; ?>)" title="Agregar a mi lista">
                    ➕
                </button>
                <button class="action-btn" onclick="addToFavorites(<?php echo $content['id']; ?>)" title="Agregar a favoritos">
                    ❤️
                </button>
                <a href="content.php?id=<?php echo $content['id']; ?>" class="action-btn" title="Ver detalles">
                    👁️
                </a>
            </div>
        </div>
    </div>
    
    <div class="content-info">
        <h3 class="content-title">
            <a href="content.php?id=<?php echo $content['id']; ?>">
                <?php echo escape($content['title']); ?>
            </a>
        </h3>
        
        <div class="content-meta">
            <span class="content-year"><?php echo $content['year']; ?></span>
            <span class="content-type"><?php echo $content['type'] === 'movie' ? 'Película' : 'Serie'; ?></span>
            <?php if (isset($content['duration'])): ?>
                <span class="content-duration"><?php echo $content['duration']; ?></span>
            <?php endif; ?>
        </div>
        
        <?php if (isset($content['imdb_rating']) && $content['imdb_rating'] > 0): ?>
            <div class="content-rating">
                <span class="rating-icon">⭐</span>
                <span class="rating-value"><?php echo number_format($content['imdb_rating'], 1); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($content['genres'])): ?>
            <div class="content-genres">
                <?php echo escape($content['genres']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($content['synopsis'])): ?>
            <div class="content-synopsis">
                <?php echo escape(substr($content['synopsis'], 0, 100)) . (strlen($content['synopsis']) > 100 ? '...' : ''); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.content-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 255, 255, 0.1);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.content-poster {
    position: relative;
    aspect-ratio: 2/3;
    overflow: hidden;
}

.content-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.content-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.8) 100%);
    opacity: 0;
    transition: var(--transition);
    display: flex;
    align-items: flex-end;
    padding: 1rem;
}

.content-card:hover .content-overlay {
    opacity: 1;
}

.content-actions {
    display: flex;
    gap: 0.5rem;
    width: 100%;
    justify-content: center;
}

.action-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
    transition: var(--transition);
    backdrop-filter: blur(10px);
}

.action-btn:hover {
    background: var(--accent);
    transform: scale(1.1);
}

.content-info {
    padding: 1rem;
}

.content-title {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
}

.content-title a {
    color: var(--text-primary);
    text-decoration: none;
    transition: var(--transition);
}

.content-title a:hover {
    color: var(--accent);
}

.content-meta {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.content-meta span:not(:last-child)::after {
    content: '•';
    margin-left: 0.5rem;
    opacity: 0.5;
}

.content-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
}

.rating-icon {
    color: #ffd700;
    font-size: 0.9rem;
}

.rating-value {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 0.9rem;
}

.content-genres {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.content-synopsis {
    font-size: 0.8rem;
    color: var(--text-secondary);
    line-height: 1.4;
}

/* Grid responsive */
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .content-info {
        padding: 0.75rem;
    }
    
    .content-title {
        font-size: 0.9rem;
    }
}
</style>

<script>
// Funciones para las acciones de la tarjeta
function addToWatchlist(contentId) {
    // Simulación - en una app real harías una petición AJAX
    console.log('Agregando a watchlist:', contentId);
    showNotification('Agregado a tu lista de seguimiento', 'success');
}

function addToFavorites(contentId) {
    // Simulación - en una app real harías una petición AJAX
    console.log('Agregando a favoritos:', contentId);
    showNotification('Agregado a tus favoritos', 'success');
}

function showNotification(message, type = 'info') {
    // Crear notificación si no existe la función
    if (typeof window.showNotification === 'undefined') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
            color: white;
            padding: 1rem;
            border-radius: 8px;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 3000);
    }
}
</script>