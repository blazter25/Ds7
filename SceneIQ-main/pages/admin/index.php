<?php
// pages/admin/index.php
$pageTitle = "Panel de Administración";
require_once '../../includes/header.php';

// Verificar que sea administrador
if (!$sceneiq->isAdmin()) {
    redirect('../../index.php');
}

// Obtener estadísticas del sistema
$stats = [
    'total_users' => rand(150, 500),
    'total_content' => count($sceneiq->getContent(100)),
    'total_reviews' => rand(800, 2000),
    'total_genres' => count($sceneiq->getGenres()),
    'active_users_today' => rand(20, 80),
    'new_reviews_today' => rand(5, 25),
    'new_users_this_week' => rand(10, 40)
];

// Contenido reciente
$recentContent = $sceneiq->getContent(5);
$topGenres = array_slice($sceneiq->getGenres(), 0, 5);
?>

<div class="admin-container">
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-breadcrumb">
            <a href="../../index.php">SceneIQ</a> › 
            <span>Panel de Administración</span>
        </div>
        <h1>🛠️ Panel de Administración</h1>
        <p>Gestiona todo el contenido y usuarios de SceneIQ</p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Usuarios Totales</div>
                <div class="stat-change positive">+<?php echo $stats['new_users_this_week']; ?> esta semana</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🎬</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $stats['total_content']; ?></div>
                <div class="stat-label">Contenido Total</div>
                <div class="stat-change neutral">Películas y Series</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($stats['total_reviews']); ?></div>
                <div class="stat-label">Reseñas Totales</div>
                <div class="stat-change positive">+<?php echo $stats['new_reviews_today']; ?> hoy</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🎭</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $stats['total_genres']; ?></div>
                <div class="stat-label">Géneros</div>
                <div class="stat-change neutral">Categorías disponibles</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="admin-actions">
        <h2>Acciones Rápidas</h2>
        <div class="actions-grid">
            <a href="movies.php" class="action-card">
                <div class="action-icon">🎬</div>
                <h3>Gestionar Películas</h3>
                <p>Agregar, editar y eliminar películas</p>
            </a>
            
            <a href="series.php" class="action-card">
                <div class="action-icon">📺</div>
                <h3>Gestionar Series</h3>
                <p>Administrar contenido de series</p>
            </a>
            
            <a href="users.php" class="action-card">
                <div class="action-icon">👥</div>
                <h3>Gestionar Usuarios</h3>
                <p>Administrar cuentas de usuario</p>
            </a>
            
            <a href="analytics.php" class="action-card">
                <div class="action-icon">📊</div>
                <h3>Ver Analytics</h3>
                <p>Estadísticas y reportes detallados</p>
            </a>
            
            <a href="#" onclick="showAddContentModal()" class="action-card featured">
                <div class="action-icon">➕</div>
                <h3>Agregar Contenido</h3>
                <p>Añadir nueva película o serie</p>
            </a>
            
            <a href="#" onclick="showSystemSettings()" class="action-card">
                <div class="action-icon">⚙️</div>
                <h3>Configuración</h3>
                <p>Ajustes del sistema</p>
            </a>
        </div>
    </div>

    <!-- Dashboard Sections -->
    <div class="admin-dashboard">
        <!-- Recent Activity -->
        <div class="dashboard-section">
            <h2>📊 Actividad Reciente</h2>
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon">👤</div>
                    <div class="activity-content">
                        <div class="activity-text">Nuevo usuario registrado: <strong>usuario_demo</strong></div>
                        <div class="activity-time">Hace 2 horas</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">📝</div>
                    <div class="activity-content">
                        <div class="activity-text">Nueva reseña para <strong>The Dark Knight</strong></div>
                        <div class="activity-time">Hace 4 horas</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">🎬</div>
                    <div class="activity-content">
                        <div class="activity-text">Contenido actualizado: <strong>Breaking Bad</strong></div>
                        <div class="activity-time">Hace 6 horas</div>
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon">🔍</div>
                    <div class="activity-content">
                        <div class="activity-text">Búsqueda popular: <strong>"thriller 2024"</strong></div>
                        <div class="activity-time">Hace 8 horas</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="dashboard-section">
            <h2>🔧 Estado del Sistema</h2>
            <div class="system-status">
                <div class="status-item">
                    <div class="status-indicator green"></div>
                    <span>Base de Datos</span>
                    <span class="status-value">Conectada</span>
                </div>
                
                <div class="status-item">
                    <div class="status-indicator green"></div>
                    <span>API Externa</span>
                    <span class="status-value">Funcionando</span>
                </div>
                
                <div class="status-item">
                    <div class="status-indicator yellow"></div>
                    <span>Cache</span>
                    <span class="status-value">Optimizando</span>
                </div>
                
                <div class="status-item">
                    <div class="status-indicator green"></div>
                    <span>Servidor</span>
                    <span class="status-value">Online</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Overview -->
    <div class="content-overview">
        <div class="overview-section">
            <h2>🎬 Contenido Reciente</h2>
            <div class="content-list">
                <?php foreach ($recentContent as $content): ?>
                    <div class="content-item">
                        <img src="<?php echo $content['poster'] ?? '../../assets/images/placeholder.jpg'; ?>" 
                             alt="<?php echo escape($content['title']); ?>" class="content-thumbnail">
                        <div class="content-info">
                            <h4><?php echo escape($content['title']); ?></h4>
                            <p><?php echo $content['year']; ?> • <?php echo ucfirst($content['type']); ?></p>
                            <div class="content-rating">⭐ <?php echo $content['imdb_rating']; ?></div>
                        </div>
                        <div class="content-actions">
                            <button class="btn-small btn-secondary" onclick="editContent(<?php echo $content['id']; ?>)">
                                Editar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="overview-section">
            <h2>📊 Géneros Populares</h2>
            <div class="genres-chart">
                <?php foreach ($topGenres as $genre): ?>
                    <div class="genre-bar">
                        <div class="genre-info">
                            <span class="genre-name"><?php echo escape($genre['name']); ?></span>
                            <span class="genre-count"><?php echo rand(10, 50); ?> películas</span>
                        </div>
                        <div class="genre-progress">
                            <div class="genre-fill" style="width: <?php echo rand(30, 100); ?>%; background: <?php echo $genre['color']; ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Content Modal -->
<div class="modal" id="addContentModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>➕ Agregar Nuevo Contenido</h3>
            <button class="modal-close" onclick="closeAddContentModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addContentForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="contentTitle">Título</label>
                        <input type="text" id="contentTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="contentType">Tipo</label>
                        <select id="contentType" name="type" required>
                            <option value="movie">Película</option>
                            <option value="series">Serie</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="contentYear">Año</label>
                        <input type="number" id="contentYear" name="year" min="1900" max="2030" required>
                    </div>
                    <div class="form-group">
                        <label for="contentRating">Rating IMDb</label>
                        <input type="number" id="contentRating" name="imdb_rating" min="0" max="10" step="0.1">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contentSynopsis">Sinopsis</label>
                    <textarea id="contentSynopsis" name="synopsis" rows="4"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddContentModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Contenido</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

.admin-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
}

.admin-breadcrumb {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-sm);
    font-size: 0.9rem;
}

.admin-breadcrumb a {
    color: var(--accent);
    text-decoration: none;
}

.admin-header h1 {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
    color: var(--text-primary);
}

.admin-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
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
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.stat-change {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    display: inline-block;
}

.stat-change.positive {
    background: rgba(0, 210, 255, 0.2);
    color: var(--success);
}

.stat-change.neutral {
    background: var(--glass-bg);
    color: var(--text-secondary);
}

.admin-actions {
    margin-bottom: var(--spacing-xl);
}

.admin-actions h2 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-lg);
    font-size: 1.5rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
}

.action-card {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    text-decoration: none;
    color: var(--text-primary);
    text-align: center;
    transition: var(--transition);
    cursor: pointer;
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
    border-color: var(--accent);
}

.action-card.featured {
    background: linear-gradient(135deg, var(--accent) 0%, #ff5252 100%);
    color: white;
}

.action-icon {
    font-size: 2rem;
    margin-bottom: var(--spacing-md);
}

.action-card h3 {
    font-size: 1.1rem;
    margin-bottom: var(--spacing-sm);
}

.action-card p {
    font-size: 0.9rem;
    opacity: 0.8;
}

.admin-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.dashboard-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.dashboard-section h2 {
    color: var(--text-primary);
    font-size: 1.2rem;
    margin-bottom: var(--spacing-md);
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.activity-item {
    display: flex;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
}

.activity-icon {
    font-size: 1.2rem;
    opacity: 0.7;
}

.activity-content {
    flex: 1;
}

.activity-text {
    color: var(--text-primary);
    font-size: 0.9rem;
}

.activity-time {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.system-status {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.status-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.status-indicator.green {
    background: var(--success);
}

.status-indicator.yellow {
    background: var(--warning);
}

.status-indicator.red {
    background: var(--error);
}

.status-value {
    margin-left: auto;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.content-overview {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--spacing-lg);
}

.overview-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.overview-section h2 {
    color: var(--text-primary);
    font-size: 1.2rem;
    margin-bottom: var(--spacing-md);
}

.content-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.content-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-sm);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    align-items: center;
}

.content-thumbnail {
    width: 50px;
    height: 75px;
    object-fit: cover;
    border-radius: var(--border-radius-small);
}

.content-info {
    flex: 1;
}

.content-info h4 {
    color: var(--text-primary);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.content-info p {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

.content-rating {
    font-size: 0.8rem;
    color: var(--warning);
}

.genres-chart {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.genre-bar {
    padding: var(--spacing-sm);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
}

.genre-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.genre-name {
    color: var(--text-primary);
    font-size: 0.9rem;
    font-weight: 500;
}

.genre-count {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.genre-progress {
    width: 100%;
    height: 6px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
    overflow: hidden;
}

.genre-fill {
    height: 100%;
    transition: var(--transition);
}

@media (max-width: 768px) {
    .admin-dashboard {
        grid-template-columns: 1fr;
    }
    
    .content-overview {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<script>
function showAddContentModal() {
    document.getElementById('addContentModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeAddContentModal() {
    document.getElementById('addContentModal').classList.remove('active');
    document.body.style.overflow = '';
}

function editContent(contentId) {
    window.location.href = `movies.php?edit=${contentId}`;
}

function showSystemSettings() {
    alert('Configuración del sistema próximamente disponible');
}

// Add content form submission
document.getElementById('addContentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Simular guardado
    setTimeout(() => {
        alert('Contenido agregado exitosamente (demo)');
        closeAddContentModal();
        this.reset();
    }, 1000);
});

// Real-time stats update (demo)
setInterval(() => {
    const activeUsersElement = document.querySelector('.stat-card:first-child .stat-number');
    if (activeUsersElement) {
        const currentValue = parseInt(activeUsersElement.textContent.replace(/,/g, ''));
        const newValue = currentValue + Math.floor(Math.random() * 3);
        activeUsersElement.textContent = newValue.toLocaleString();
    }
}, 30000); // Update every 30 seconds
</script>

<?php require_once '../../includes/footer.php'; ?>