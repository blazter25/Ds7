<?php
// pages/admin/index.php
$pageTitle = "Panel de Administración";
require_once '../../includes/header.php';

// Verificar que sea administrador
if (!$sceneiq->isAdmin()) {
    redirect('../../index.php');
}

// Obtener estadísticas del sistema
$systemStats = [
    'total_users' => rand(500, 2000),
    'total_content' => rand(200, 800),
    'total_reviews' => rand(1000, 5000),
    'active_today' => rand(50, 200),
    'new_users_week' => rand(20, 80),
    'reviews_week' => rand(100, 400),
    'popular_genre' => 'Drama',
    'avg_rating' => round(rand(75, 90) / 10, 1)
];

// Actividad reciente
$recentActivity = [
    ['type' => 'user', 'action' => 'Nuevo usuario registrado', 'details' => 'moviefan2025', 'time' => '5 min'],
    ['type' => 'review', 'action' => 'Nueva reseña publicada', 'details' => 'The Dark Knight', 'time' => '12 min'],
    ['type' => 'content', 'action' => 'Contenido agregado', 'details' => 'Dune: Part Two', 'time' => '1 hora'],
    ['type' => 'user', 'action' => 'Usuario suspendido', 'details' => 'spammer123', 'time' => '2 horas'],
    ['type' => 'review', 'action' => 'Reseña reportada', 'details' => 'Breaking Bad', 'time' => '3 horas']
];

// Contenido más popular
$topContent = [
    ['title' => 'The Dark Knight', 'type' => 'movie', 'views' => 1250, 'rating' => 9.0],
    ['title' => 'Breaking Bad', 'type' => 'series', 'views' => 1100, 'rating' => 9.5],
    ['title' => 'Inception', 'type' => 'movie', 'views' => 980, 'rating' => 8.8],
    ['title' => 'Stranger Things', 'type' => 'series', 'views' => 870, 'rating' => 8.7],
    ['title' => 'The Godfather', 'type' => 'movie', 'views' => 750, 'rating' => 9.2]
];
?>

<div class="admin-dashboard">
    <!-- Header -->
    <div class="admin-header">
        <div class="admin-breadcrumb">
            <a href="../../index.php">SceneIQ</a> › 
            <span>Panel de Administración</span>
        </div>
        <div class="header-content">
            <h1>🛠️ Panel de Administración</h1>
            <p>Gestiona y monitorea la plataforma SceneIQ</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showQuickAddModal()">
                ➕ Agregar Contenido
            </button>
            <a href="analytics.php" class="btn btn-secondary">
                📊 Ver Analytics
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($systemStats['total_users']); ?></div>
                <div class="stat-label">Usuarios Totales</div>
                <div class="stat-change positive">+<?php echo $systemStats['new_users_week']; ?> esta semana</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎬</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($systemStats['total_content']); ?></div>
                <div class="stat-label">Contenido Total</div>
                <div class="stat-change positive">+5 este mes</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($systemStats['total_reviews']); ?></div>
                <div class="stat-label">Reseñas Totales</div>
                <div class="stat-change positive">+<?php echo $systemStats['reviews_week']; ?> esta semana</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔥</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $systemStats['active_today']; ?></div>
                <div class="stat-label">Activos Hoy</div>
                <div class="stat-change positive">+15% vs ayer</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Quick Actions -->
        <div class="admin-section">
            <h2>🚀 Acciones Rápidas</h2>
            <div class="quick-actions">
                <div class="action-card" onclick="window.location.href='movies.php'">
                    <div class="action-icon">🎬</div>
                    <h3>Gestionar Películas</h3>
                    <p>Agregar, editar y administrar películas</p>
                </div>

                <div class="action-card" onclick="window.location.href='users.php'">
                    <div class="action-icon">👥</div>
                    <h3>Gestionar Usuarios</h3>
                    <p>Administrar cuentas y permisos</p>
                </div>

                <div class="action-card" onclick="showModerationModal()">
                    <div class="action-icon">🛡️</div>
                    <h3>Moderación</h3>
                    <p>Revisar reportes y contenido</p>
                </div>

                <div class="action-card" onclick="window.location.href='analytics.php'">
                    <div class="action-icon">📊</div>
                    <h3>Analytics</h3>
                    <p>Estadísticas y métricas detalladas</p>
                </div>

                <div class="action-card" onclick="showBackupModal()">
                    <div class="action-icon">💾</div>
                    <h3>Respaldo</h3>
                    <p>Crear y gestionar respaldos</p>
                </div>

                <div class="action-card" onclick="showSettingsModal()">
                    <div class="action-icon">⚙️</div>
                    <h3>Configuración</h3>
                    <p>Configurar parámetros del sistema</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="admin-section">
            <div class="section-header">
                <h2>📈 Actividad Reciente</h2>
                <a href="#" class="view-all">Ver todo</a>
            </div>
            <div class="activity-feed">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php 
                            $icons = [
                                'user' => '👤',
                                'review' => '📝',
                                'content' => '🎬',
                                'system' => '⚙️'
                            ];
                            echo $icons[$activity['type']] ?? '📋';
                            ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text"><?php echo escape($activity['action']); ?></div>
                            <div class="activity-details"><?php echo escape($activity['details']); ?></div>
                        </div>
                        <div class="activity-time"><?php echo $activity['time']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Content -->
        <div class="admin-section">
            <div class="section-header">
                <h2>🏆 Contenido Más Popular</h2>
                <a href="analytics.php" class="view-all">Ver analytics</a>
            </div>
            <div class="top-content-table">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Vistas</th>
                            <th>Rating</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topContent as $content): ?>
                            <tr>
                                <td>
                                    <strong><?php echo escape($content['title']); ?></strong>
                                </td>
                                <td>
                                    <span class="type-badge type-<?php echo $content['type']; ?>">
                                        <?php echo $content['type'] === 'movie' ? '🎬 Película' : '📺 Serie'; ?>
                                    </span>
                                </td>
                                <td class="number-cell"><?php echo number_format($content['views']); ?></td>
                                <td class="rating-cell">
                                    <span class="rating-value">⭐ <?php echo $content['rating']; ?></span>
                                </td>
                                <td class="actions-cell">
                                    <button class="btn-icon" title="Ver">👁️</button>
                                    <button class="btn-icon" title="Editar">✏️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="system-status">
        <h2>🖥️ Estado del Sistema</h2>
        <div class="status-grid">
            <div class="status-item">
                <div class="status-indicator status-good"></div>
                <div class="status-label">Base de Datos</div>
                <div class="status-value">Operacional</div>
            </div>
            
            <div class="status-item">
                <div class="status-indicator status-good"></div>
                <div class="status-label">Servidor Web</div>
                <div class="status-value">99.9% Uptime</div>
            </div>
            
            <div class="status-item">
                <div class="status-indicator status-warning"></div>
                <div class="status-label">Almacenamiento</div>
                <div class="status-value">78% Usado</div>
            </div>
            
            <div class="status-item">
                <div class="status-indicator status-good"></div>
                <div class="status-label">API Externa</div>
                <div class="status-value">Conectada</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Modal -->
<div class="modal" id="quickAddModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>➕ Agregar Contenido Rápido</h3>
            <button class="modal-close" onclick="closeQuickAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="quick-add-options">
                <div class="add-option" onclick="window.location.href='movies.php?action=add'">
                    <div class="add-icon">🎬</div>
                    <h4>Agregar Película</h4>
                    <p>Añadir nueva película al catálogo</p>
                </div>
                
                <div class="add-option" onclick="addSeries()">
                    <div class="add-icon">📺</div>
                    <h4>Agregar Serie</h4>
                    <p>Añadir nueva serie al catálogo</p>
                </div>
                
                <div class="add-option" onclick="importFromTMDB()">
                    <div class="add-icon">📥</div>
                    <h4>Importar de TMDB</h4>
                    <p>Importar desde The Movie Database</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
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

.quick-stats {
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
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.stat-number {
    font-size: 1.8rem;
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
}

.stat-change.positive {
    color: var(--success);
}

.admin-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
}

.admin-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.admin-section h2 {
    color: var(--text-primary);
    font-size: 1.3rem;
    margin-bottom: var(--spacing-md);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.view-all {
    color: var(--accent);
    text-decoration: none;
    font-size: 0.9rem;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.action-card {
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
    border-color: var(--accent);
}

.action-icon {
    font-size: 2rem;
    margin-bottom: var(--spacing-sm);
}

.action-card h3 {
    color: var(--text-primary);
    font-size: 1rem;
    margin-bottom: var(--spacing-xs);
}

.action-card p {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.activity-feed {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.activity-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-sm);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    align-items: center;
}

.activity-icon {
    font-size: 1.2rem;
    opacity: 0.8;
}

.activity-content {
    flex: 1;
}

.activity-text {
    color: var(--text-primary);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.activity-details {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.activity-time {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    background: var(--glass-bg);
    color: var(--text-secondary);
    padding: var(--spacing-sm);
    text-align: left;
    font-size: 0.9rem;
    font-weight: 500;
}

.admin-table td {
    padding: var(--spacing-sm);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    font-size: 0.9rem;
}

.type-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 500;
}

.type-badge.type-movie {
    background: rgba(255, 107, 107, 0.2);
    color: var(--accent);
}

.type-badge.type-series {
    background: rgba(102, 126, 234, 0.2);
    color: #667eea;
}

.number-cell {
    text-align: right;
    font-weight: 500;
}

.rating-cell {
    text-align: center;
}

.rating-value {
    color: var(--warning);
    font-weight: 500;
}

.actions-cell {
    text-align: center;
}

.btn-icon {
    padding: 0.3rem;
    background: var(--glass-bg);
    border: none;
    border-radius: var(--border-radius-small);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
    margin: 0 0.25rem;
}

.btn-icon:hover {
    background: var(--accent);
    color: white;
}

.system-status {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.system-status h2 {
    color: var(--text-primary);
    font-size: 1.3rem;
    margin-bottom: var(--spacing-md);
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.status-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-md);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.status-indicator.status-good {
    background: var(--success);
}

.status-indicator.status-warning {
    background: var(--warning);
}

.status-indicator.status-error {
    background: var(--error);
}

.status-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.status-value {
    color: var(--text-primary);
    font-weight: 500;
    margin-left: auto;
    font-size: 0.9rem;
}

.quick-add-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
}

.add-option {
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
}

.add-option:hover {
    transform: translateY(-5px);
    border-color: var(--accent);
}

.add-icon {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-md);
}

.add-option h4 {
    color: var(--text-primary);
    font-size: 1.1rem;
    margin-bottom: var(--spacing-sm);
}

.add-option p {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

@media (max-width: 1200px) {
    .admin-content {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        text-align: center;
    }

    .quick-stats {
        grid-template-columns: 1fr 1fr;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function showQuickAddModal() {
    document.getElementById('quickAddModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeQuickAddModal() {
    document.getElementById('quickAddModal').classList.remove('active');
    document.body.style.overflow = '';
}

function addSeries() {
    showNotification('Función de agregar series próximamente', 'info');
}

function importFromTMDB() {
    showNotification('Función de importación desde TMDB próximamente', 'info');
}

function showModerationModal() {
    showNotification('Panel de moderación próximamente', 'info');
}

function showBackupModal() {
    showNotification('Sistema de respaldos próximamente', 'info');
}

function showSettingsModal() {
    showNotification('Configuración del sistema próximamente', 'info');
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

// Actualizar estadísticas en tiempo real (simulado)
setInterval(() => {
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const currentValue = parseInt(stat.textContent.replace(/,/g, ''));
        const change = Math.floor(Math.random() * 5) - 2;
        const newValue = Math.max(0, currentValue + change);
        stat.textContent = newValue.toLocaleString();
    });
}, 30000); // Actualizar cada 30 segundos

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeQuickAddModal();
    }
});

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuickAddModal();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>