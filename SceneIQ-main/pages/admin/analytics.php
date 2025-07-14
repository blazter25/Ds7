<?php
// pages/admin/analytics.php
$pageTitle = "Analytics - Panel de Administración";
require_once '../../includes/header.php';

// Verificar que sea administrador
if (!$sceneiq->isAdmin()) {
    redirect('../../index.php');
}

// Generar datos de ejemplo para analytics
$analytics = [
    'user_growth' => [
        ['month' => 'Ene', 'users' => 45],
        ['month' => 'Feb', 'users' => 78],
        ['month' => 'Mar', 'users' => 102],
        ['month' => 'Abr', 'users' => 134],
        ['month' => 'May', 'users' => 167],
        ['month' => 'Jun', 'users' => 201]
    ],
    'content_stats' => [
        'total_movies' => rand(80, 120),
        'total_series' => rand(40, 80),
        'total_reviews' => rand(800, 1500),
        'avg_rating' => round(rand(75, 90) / 10, 1)
    ],
    'popular_genres' => [
        ['name' => 'Drama', 'count' => rand(80, 150), 'color' => '#4ecdc4'],
        ['name' => 'Acción', 'count' => rand(70, 140), 'color' => '#ff6b6b'],
        ['name' => 'Comedia', 'count' => rand(60, 130), 'color' => '#45b7d1'],
        ['name' => 'Thriller', 'count' => rand(50, 120), 'color' => '#96ceb4'],
        ['name' => 'Sci-Fi', 'count' => rand(40, 110), 'color' => '#ffeaa7']
    ],
    'activity_today' => [
        ['hour' => '00:00', 'users' => rand(5, 15)],
        ['hour' => '06:00', 'users' => rand(10, 25)],
        ['hour' => '12:00', 'users' => rand(30, 60)],
        ['hour' => '18:00', 'users' => rand(40, 80)],
        ['hour' => '21:00', 'users' => rand(35, 70)]
    ]
];
?>

<div class="analytics-container">
    <!-- Header -->
    <div class="analytics-header">
        <div class="admin-breadcrumb">
            <a href="../../index.php">SceneIQ</a> › 
            <a href="index.php">Admin</a> › 
            <span>Analytics</span>
        </div>
        <h1>📊 Analytics Dashboard</h1>
        <p>Análisis detallado de métricas y estadísticas del sistema</p>
    </div>

    <!-- Time Range Selector -->
    <div class="time-selector">
        <div class="time-buttons">
            <button class="time-btn active" data-range="7d">7 días</button>
            <button class="time-btn" data-range="30d">30 días</button>
            <button class="time-btn" data-range="90d">90 días</button>
            <button class="time-btn" data-range="1y">1 año</button>
        </div>
        <div class="date-range">
            <input type="date" id="startDate" class="date-input">
            <span>hasta</span>
            <input type="date" id="endDate" class="date-input">
            <button class="btn btn-primary btn-small">Aplicar</button>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-header">
                <h3>👥 Usuarios Activos</h3>
                <div class="metric-period">Últimos 30 días</div>
            </div>
            <div class="metric-value"><?php echo number_format(rand(800, 1500)); ?></div>
            <div class="metric-change positive">+12.5% vs mes anterior</div>
            <div class="metric-chart">
                <canvas id="usersChart" width="300" height="60"></canvas>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <h3>📝 Nuevas Reseñas</h3>
                <div class="metric-period">Esta semana</div>
            </div>
            <div class="metric-value"><?php echo rand(150, 300); ?></div>
            <div class="metric-change positive">+8.2% vs semana anterior</div>
            <div class="metric-chart">
                <canvas id="reviewsChart" width="300" height="60"></canvas>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <h3>🎬 Contenido Visto</h3>
                <div class="metric-period">Hoy</div>
            </div>
            <div class="metric-value"><?php echo number_format(rand(2000, 5000)); ?></div>
            <div class="metric-change neutral">+2.1% vs ayer</div>
            <div class="metric-chart">
                <canvas id="viewsChart" width="300" height="60"></canvas>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <h3>⭐ Rating Promedio</h3>
                <div class="metric-period">General</div>
            </div>
            <div class="metric-value"><?php echo $analytics['content_stats']['avg_rating']; ?></div>
            <div class="metric-change positive">+0.3 vs mes anterior</div>
            <div class="metric-chart">
                <canvas id="ratingChart" width="300" height="60"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <!-- User Growth Chart -->
        <div class="chart-card large">
            <div class="chart-header">
                <h3>📈 Crecimiento de Usuarios</h3>
                <div class="chart-controls">
                    <select class="chart-select">
                        <option>Usuarios registrados</option>
                        <option>Usuarios activos</option>
                        <option>Usuarios premium</option>
                    </select>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="userGrowthChart" width="800" height="300"></canvas>
            </div>
        </div>

        <!-- Genre Popularity -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>🎭 Popularidad por Género</h3>
            </div>
            <div class="chart-container">
                <canvas id="genreChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Daily Activity -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>🕐 Actividad Diaria</h3>
            </div>
            <div class="chart-container">
                <canvas id="activityChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="tables-section">
        <!-- Top Content -->
        <div class="table-card">
            <div class="table-header">
                <h3>🏆 Contenido Más Popular</h3>
                <button class="btn btn-secondary btn-small">Exportar</button>
            </div>
            <div class="table-container">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Vistas</th>
                            <th>Rating</th>
                            <th>Reseñas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $topContent = [
                            ['title' => 'The Dark Knight', 'type' => 'Película', 'views' => 1250, 'rating' => 9.0, 'reviews' => 156],
                            ['title' => 'Breaking Bad', 'type' => 'Serie', 'views' => 1100, 'rating' => 9.5, 'reviews' => 234],
                            ['title' => 'Inception', 'type' => 'Película', 'views' => 980, 'rating' => 8.8, 'reviews' => 189],
                            ['title' => 'Stranger Things', 'type' => 'Serie', 'views' => 870, 'rating' => 8.7, 'reviews' => 167],
                            ['title' => 'The Godfather', 'type' => 'Película', 'views' => 750, 'rating' => 9.2, 'reviews' => 298]
                        ];
                        foreach ($topContent as $content): 
                        ?>
                            <tr>
                                <td class="title-cell">
                                    <strong><?php echo $content['title']; ?></strong>
                                </td>
                                <td>
                                    <span class="type-badge type-<?php echo strtolower($content['type']); ?>">
                                        <?php echo $content['type']; ?>
                                    </span>
                                </td>
                                <td class="number-cell"><?php echo number_format($content['views']); ?></td>
                                <td class="rating-cell">
                                    <span class="rating-value">⭐ <?php echo $content['rating']; ?></span>
                                </td>
                                <td class="number-cell"><?php echo $content['reviews']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="table-card">
            <div class="table-header">
                <h3>👥 Estadísticas de Usuarios</h3>
                <button class="btn btn-secondary btn-small">Ver Detalles</button>
            </div>
            <div class="stats-grid-small">
                <div class="stat-item-small">
                    <div class="stat-label">Usuarios Totales</div>
                    <div class="stat-value"><?php echo number_format(rand(1000, 5000)); ?></div>
                </div>
                <div class="stat-item-small">
                    <div class="stat-label">Usuarios Activos (30d)</div>
                    <div class="stat-value"><?php echo number_format(rand(500, 2000)); ?></div>
                </div>
                <div class="stat-item-small">
                    <div class="stat-label">Nuevos Hoy</div>
                    <div class="stat-value"><?php echo rand(10, 50); ?></div>
                </div>
                <div class="stat-item-small">
                    <div class="stat-label">Retención (7d)</div>
                    <div class="stat-value"><?php echo rand(65, 85); ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="export-section">
        <div class="export-card">
            <h3>📊 Exportar Reportes</h3>
            <p>Genera reportes detallados en diferentes formatos</p>
            <div class="export-buttons">
                <button class="btn btn-primary" onclick="exportReport('pdf')">
                    📄 Exportar PDF
                </button>
                <button class="btn btn-secondary" onclick="exportReport('excel')">
                    📊 Exportar Excel
                </button>
                <button class="btn btn-secondary" onclick="exportReport('csv')">
                    📋 Exportar CSV
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

.analytics-header {
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

.analytics-header h1 {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
    color: var(--text-primary);
}

.analytics-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.time-selector {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.time-buttons {
    display: flex;
    gap: var(--spacing-sm);
}

.time-btn {
    padding: 0.5rem 1rem;
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
}

.time-btn:hover,
.time-btn.active {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.date-range {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.date-input {
    padding: 0.5rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.metric-card {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    transition: var(--transition);
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-sm);
}

.metric-header h3 {
    color: var(--text-primary);
    font-size: 0.9rem;
    margin: 0;
}

.metric-period {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.metric-change {
    font-size: 0.8rem;
    margin-bottom: var(--spacing-md);
}

.metric-change.positive {
    color: var(--success);
}

.metric-change.neutral {
    color: var(--text-secondary);
}

.metric-chart {
    height: 60px;
}

.charts-section {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.chart-card {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.chart-card.large {
    grid-column: span 3;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.chart-header h3 {
    color: var(--text-primary);
    font-size: 1.1rem;
    margin: 0;
}

.chart-select {
    padding: 0.5rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
    font-size: 0.8rem;
}

.chart-container {
    position: relative;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
}

.tables-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.table-card {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.table-header h3 {
    color: var(--text-primary);
    font-size: 1.1rem;
    margin: 0;
}

.table-container {
    overflow-x: auto;
}

.analytics-table {
    width: 100%;
    border-collapse: collapse;
}

.analytics-table th {
    background: var(--glass-bg);
    color: var(--text-secondary);
    padding: var(--spacing-sm);
    text-align: left;
    font-size: 0.9rem;
    font-weight: 500;
}

.analytics-table td {
    padding: var(--spacing-sm);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    font-size: 0.9rem;
}

.title-cell strong {
    color: var(--text-primary);
}

.type-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 500;
}

.type-badge.type-película {
    background: rgba(255, 107, 107, 0.2);
    color: var(--accent);
}

.type-badge.type-serie {
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

.stats-grid-small {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.stat-item-small {
    text-align: center;
    padding: var(--spacing-md);
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
}

.stat-item-small .stat-label {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
}

.stat-item-small .stat-value {
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 600;
}

.export-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
    text-align: center;
}

.export-card h3 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.export-card p {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
}

.export-buttons {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 1200px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .chart-card.large {
        grid-column: span 1;
    }
}

@media (max-width: 768px) {
    .time-selector {
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .tables-section {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .export-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
// Simulación de gráficos (en una implementación real usarías Chart.js o similar)
function initializeCharts() {
    // Canvas elements
    const canvases = document.querySelectorAll('canvas');
    
    canvases.forEach(canvas => {
        const ctx = canvas.getContext('2d');
        
        // Dibujar gráfico simple de ejemplo
        ctx.fillStyle = 'rgba(255, 107, 107, 0.2)';
        ctx.fillRect(0, canvas.height * 0.6, canvas.width, canvas.height * 0.4);
        
        ctx.fillStyle = 'var(--accent)';
        ctx.font = '12px Arial';
        ctx.fillText('Gráfico de ejemplo', 10, 20);
    });
}

// Time range selector
document.querySelectorAll('.time-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Aquí cargarías los datos para el rango seleccionado
        updateCharts(this.dataset.range);
    });
});

function updateCharts(range) {
    console.log('Actualizando gráficos para:', range);
    // Simular carga de datos
    showNotification(`Datos actualizados para ${range}`, 'info');
}

function exportReport(format) {
    showNotification(`Exportando reporte en formato ${format.toUpperCase()}...`, 'info');
    
    // Simular exportación
    setTimeout(() => {
        showNotification(`Reporte ${format.toUpperCase()} generado exitosamente`, 'success');
    }, 2000);
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

// Inicializar gráficos cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    
    // Actualizar métricas en tiempo real (demo)
    setInterval(() => {
        const metricValues = document.querySelectorAll('.metric-value');
        metricValues.forEach(value => {
            const currentValue = parseInt(value.textContent.replace(/,/g, ''));
            const change = Math.floor(Math.random() * 10) - 5;
            const newValue = Math.max(0, currentValue + change);
            value.textContent = newValue.toLocaleString();
        });
    }, 10000); // Actualizar cada 10 segundos
});
</script>

<?php require_once '../../includes/footer.php'; ?>