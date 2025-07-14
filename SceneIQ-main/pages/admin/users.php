<?php
// pages/admin/users.php
$pageTitle = "Gestión de Usuarios - Admin";
require_once '../../includes/header.php';

// Verificar que sea administrador
if (!$sceneiq->isAdmin()) {
    redirect('../../index.php');
}

// Generar datos de ejemplo para usuarios
$users = [
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@sceneiq.com',
        'full_name' => 'Administrador SceneIQ',
        'role' => 'admin',
        'is_active' => true,
        'created_at' => '2024-01-15 10:30:00',
        'last_login' => '2025-01-20 14:22:00',
        'total_reviews' => 25,
        'avg_rating' => 8.5
    ],
    [
        'id' => 2,
        'username' => 'user_demo',
        'email' => 'user@sceneiq.com',
        'full_name' => 'Usuario Demo',
        'role' => 'user',
        'is_active' => true,
        'created_at' => '2024-02-20 16:45:00',
        'last_login' => '2025-01-19 20:15:00',
        'total_reviews' => 12,
        'avg_rating' => 7.8
    ],
    [
        'id' => 3,
        'username' => 'moviefan',
        'email' => 'fan@movies.com',
        'full_name' => 'María González',
        'role' => 'user',
        'is_active' => true,
        'created_at' => '2024-03-10 09:20:00',
        'last_login' => '2025-01-18 18:30:00',
        'total_reviews' => 45,
        'avg_rating' => 9.2
    ],
    [
        'id' => 4,
        'username' => 'cinephile',
        'email' => 'cinephile@example.com',
        'full_name' => 'Carlos Rodríguez',
        'role' => 'user',
        'is_active' => false,
        'created_at' => '2024-04-05 11:10:00',
        'last_login' => '2024-12-15 22:45:00',
        'total_reviews' => 8,
        'avg_rating' => 6.5
    ],
    [
        'id' => 5,
        'username' => 'seriesfan',
        'email' => 'series@fan.com',
        'full_name' => 'Ana Martín',
        'role' => 'user',
        'is_active' => true,
        'created_at' => '2024-05-12 14:25:00',
        'last_login' => '2025-01-20 12:10:00',
        'total_reviews' => 33,
        'avg_rating' => 8.1
    ]
];

// Estadísticas
$stats = [
    'total_users' => count($users),
    'active_users' => count(array_filter($users, fn($u) => $u['is_active'])),
    'admin_users' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
    'new_users_month' => rand(15, 35)
];
?>

<div class="admin-users-container">
    <!-- Header -->
    <div class="admin-header">
        <div class="admin-breadcrumb">
            <a href="../../index.php">SceneIQ</a> › 
            <a href="index.php">Admin</a> › 
            <span>Usuarios</span>
        </div>
        <div class="header-content">
            <h1>👥 Gestión de Usuarios</h1>
            <p>Administra todas las cuentas de usuario del sistema</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showAddUserModal()">
                ➕ Agregar Usuario
            </button>
            <button class="btn btn-secondary" onclick="exportUsers()">
                📊 Exportar Datos
            </button>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="users-stats">
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Usuarios</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['active_users']; ?></div>
                    <div class="stat-label">Usuarios Activos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👑</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['admin_users']; ?></div>
                    <div class="stat-label">Administradores</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🆕</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['new_users_month']; ?></div>
                    <div class="stat-label">Nuevos Este Mes</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form class="filters-form" method="GET">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Buscar usuarios..." 
                           value="<?php echo escape($_GET['search'] ?? ''); ?>" class="search-input">
                </div>
                
                <div class="filter-group">
                    <select name="role" class="filter-select">
                        <option value="">Todos los roles</option>
                        <option value="admin" <?php echo ($_GET['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="user" <?php echo ($_GET['role'] ?? '') === 'user' ? 'selected' : ''; ?>>Usuario</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">Todos los estados</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="sort" class="filter-select">
                        <option value="created_desc" <?php echo ($_GET['sort'] ?? '') === 'created_desc' ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="created_asc" <?php echo ($_GET['sort'] ?? '') === 'created_asc' ? 'selected' : ''; ?>>Más antiguos</option>
                        <option value="name_asc" <?php echo ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : ''; ?>>Nombre A-Z</option>
                        <option value="reviews_desc" <?php echo ($_GET['sort'] ?? '') === 'reviews_desc' ? 'selected' : ''; ?>>Más reseñas</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="users.php" class="btn btn-secondary">Limpiar</a>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="users-table-section">
        <div class="table-header">
            <h2>📋 Lista de Usuarios</h2>
            <div class="table-actions">
                <button class="btn btn-secondary btn-small" onclick="exportUsers()">
                    📊 Exportar
                </button>
                <div class="view-toggle">
                    <button class="view-btn active" data-view="table">📋</button>
                    <button class="view-btn" data-view="cards">⊞</button>
                </div>
            </div>
        </div>

        <!-- Table View -->
        <div class="table-container" id="tableView">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Reseñas</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user_item): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="user-checkbox" value="<?php echo $user_item['id']; ?>">
                            </td>
                            <td class="user-cell">
                                <div class="user-info">
                                    <img src="../../assets/images/default-avatar.png" 
                                         alt="<?php echo escape($user_item['username']); ?>" class="user-avatar">
                                    <div>
                                        <div class="user-name"><?php echo escape($user_item['full_name']); ?></div>
                                        <div class="user-username">@<?php echo escape($user_item['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="email-cell">
                                <a href="mailto:<?php echo escape($user_item['email']); ?>" class="user-email">
                                    <?php echo escape($user_item['email']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo $user_item['role']; ?>">
                                    <?php echo $user_item['role'] === 'admin' ? '👑 Admin' : '👤 Usuario'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $user_item['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $user_item['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="reviews-cell">
                                <div class="review-stats">
                                    <span class="review-count"><?php echo $user_item['total_reviews']; ?></span>
                                    <span class="avg-rating">⭐ <?php echo $user_item['avg_rating']; ?></span>
                                </div>
                            </td>
                            <td class="date-cell">
                                <div class="last-login">
                                    <?php echo $sceneiq->timeAgo($user_item['last_login']); ?>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <div class="action-buttons">
                                    <button class="btn-icon" onclick="viewUser(<?php echo $user_item['id']; ?>)" title="Ver perfil">
                                        👁️
                                    </button>
                                    <button class="btn-icon" onclick="editUser(<?php echo $user_item['id']; ?>)" title="Editar">
                                        ✏️
                                    </button>
                                    <?php if ($user_item['id'] !== $user['id']): ?>
                                        <button class="btn-icon" onclick="toggleUserStatus(<?php echo $user_item['id']; ?>, <?php echo $user_item['is_active'] ? 'false' : 'true'; ?>)" 
                                                title="<?php echo $user_item['is_active'] ? 'Desactivar' : 'Activar'; ?>">
                                            <?php echo $user_item['is_active'] ? '🚫' : '✅'; ?>
                                        </button>
                                        <?php if ($user_item['role'] !== 'admin'): ?>
                                            <button class="btn-icon danger" onclick="deleteUser(<?php echo $user_item['id']; ?>)" title="Eliminar">
                                                🗑️
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Cards View -->
        <div class="cards-container hidden" id="cardsView">
            <div class="users-grid">
                <?php foreach ($users as $user_item): ?>
                    <div class="user-card">
                        <div class="user-card-header">
                            <img src="../../assets/images/default-avatar.png" 
                                 alt="<?php echo escape($user_item['username']); ?>" class="user-card-avatar">
                            <div class="user-card-status">
                                <span class="status-indicator status-<?php echo $user_item['is_active'] ? 'active' : 'inactive'; ?>"></span>
                            </div>
                        </div>
                        
                        <div class="user-card-content">
                            <h4 class="user-card-name"><?php echo escape($user_item['full_name']); ?></h4>
                            <p class="user-card-username">@<?php echo escape($user_item['username']); ?></p>
                            <p class="user-card-email"><?php echo escape($user_item['email']); ?></p>
                            
                            <div class="user-card-stats">
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $user_item['total_reviews']; ?></span>
                                    <span class="stat-label">Reseñas</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value">⭐ <?php echo $user_item['avg_rating']; ?></span>
                                    <span class="stat-label">Rating</span>
                                </div>
                            </div>
                            
                            <div class="user-card-meta">
                                <span class="role-badge role-<?php echo $user_item['role']; ?>">
                                    <?php echo $user_item['role'] === 'admin' ? '👑 Admin' : '👤 Usuario'; ?>
                                </span>
                                <span class="join-date">
                                    Se unió <?php echo date('M Y', strtotime($user_item['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="user-card-actions">
                            <button class="btn-small btn-secondary" onclick="viewUser(<?php echo $user_item['id']; ?>)">
                                Ver Perfil
                            </button>
                            <button class="btn-small btn-primary" onclick="editUser(<?php echo $user_item['id']; ?>)">
                                Editar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions" id="bulkActions" style="display: none;">
            <div class="bulk-info">
                <span id="selectedCount">0</span> usuario(s) seleccionado(s)
            </div>
            <div class="bulk-buttons">
                <button class="btn btn-secondary" onclick="bulkActivate()">Activar</button>
                <button class="btn btn-secondary" onclick="bulkDeactivate()">Desactivar</button>
                <button class="btn btn-warning" onclick="bulkSendEmail()">Enviar Email</button>
                <button class="btn btn-danger" onclick="bulkDelete()">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-section">
        <div class="pagination-info">
            Mostrando <?php echo count($users); ?> de <?php echo count($users); ?> usuarios
        </div>
        <div class="pagination">
            <button class="pagination-btn" disabled>← Anterior</button>
            <span class="pagination-current">Página 1 de 1</span>
            <button class="pagination-btn" disabled>Siguiente →</button>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">➕ Agregar Nuevo Usuario</h3>
            <button class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="userForm">
                <input type="hidden" id="userId" name="id" value="">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="userName">Nombre Completo *</label>
                        <input type="text" id="userName" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="userUsername">Nombre de Usuario *</label>
                        <input type="text" id="userUsername" name="username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="userEmail">Email *</label>
                    <input type="email" id="userEmail" name="email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="userRole">Rol</label>
                        <select id="userRole" name="role">
                            <option value="user">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userStatus">Estado</label>
                        <select id="userStatus" name="is_active">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="passwordGroup">
                    <label for="userPassword">Contraseña *</label>
                    <input type="password" id="userPassword" name="password">
                    <small class="form-help">Dejar en blanco para mantener la contraseña actual (solo al editar)</small>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submitText">Agregar Usuario</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal" id="emailModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📧 Enviar Email a Usuarios</h3>
            <button class="modal-close" onclick="closeEmailModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="emailForm">
                <div class="form-group">
                    <label for="emailSubject">Asunto *</label>
                    <input type="text" id="emailSubject" name="subject" required 
                           placeholder="Ej: Nuevas funciones en SceneIQ">
                </div>

                <div class="form-group">
                    <label for="emailMessage">Mensaje *</label>
                    <textarea id="emailMessage" name="message" rows="8" required
                              placeholder="Escribe tu mensaje aquí..."></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="send_copy" checked>
                        Enviarme una copia
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEmailModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">📧 Enviar Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Reutilizar estilos base de movies.php y agregar específicos */
.admin-users-container {
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

.users-stats {
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

.users-table-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.table-header h2 {
    color: var(--text-primary);
    font-size: 1.3rem;
}

.table-actions {
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
}

.view-toggle {
    display: flex;
    background: var(--glass-bg);
    border-radius: var(--border-radius-small);
    padding: 0.25rem;
}

.view-btn {
    padding: 0.5rem;
    background: transparent;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    border-radius: var(--border-radius-small);
    transition: var(--transition);
}

.view-btn.active {
    background: var(--accent);
    color: white;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    background: var(--glass-bg);
    color: var(--text-secondary);
    padding: var(--spacing-md);
    text-align: left;
    font-weight: 500;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.users-table td {
    padding: var(--spacing-md);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    vertical-align: middle;
}

.user-cell {
    min-width: 200px;
}

.user-info {
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.user-name {
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.user-username {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.email-cell {
    max-width: 200px;
}

.user-email {
    color: var(--text-secondary);
    text-decoration: none;
    transition: var(--transition);
}

.user-email:hover {
    color: var(--accent);
}

.role-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.role-badge.role-admin {
    background: rgba(255, 193, 7, 0.2);
    color: var(--warning);
}

.role-badge.role-user {
    background: rgba(102, 126, 234, 0.2);
    color: #667eea;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.status-active {
    background: rgba(0, 210, 255, 0.2);
    color: var(--success);
}

.status-badge.status-inactive {
    background: rgba(255, 107, 107, 0.2);
    color: var(--error);
}

.reviews-cell {
    text-align: center;
}

.review-stats {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.review-count {
    color: var(--text-primary);
    font-weight: 600;
}

.avg-rating {
    color: var(--warning);
    font-size: 0.8rem;
}

.date-cell {
    text-align: center;
}

.last-login {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.actions-cell {
    text-align: center;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.btn-icon {
    padding: 0.5rem;
    background: var(--glass-bg);
    border: none;
    border-radius: var(--border-radius-small);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
}

.btn-icon:hover {
    background: var(--accent);
    color: white;
}

.btn-icon.danger:hover {
    background: var(--error);
}

/* Cards View */
.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-lg);
}

.user-card {
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    transition: var(--transition);
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.user-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.user-card-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-indicator.status-active {
    background: var(--success);
}

.status-indicator.status-inactive {
    background: var(--error);
}

.user-card-content {
    text-align: center;
    margin-bottom: var(--spacing-lg);
}

.user-card-name {
    color: var(--text-primary);
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.user-card-username {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.user-card-email {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-bottom: var(--spacing-md);
}

.user-card-stats {
    display: flex;
    justify-content: space-around;
    margin: var(--spacing-md) 0;
    padding: var(--spacing-sm) 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-card-stats .stat-item {
    text-align: center;
}

.user-card-stats .stat-value {
    display: block;
    color: var(--text-primary);
    font-weight: 600;
    font-size: 0.9rem;
}

.user-card-stats .stat-label {
    color: var(--text-secondary);
    font-size: 0.7rem;
    margin-top: 0.25rem;
}

.user-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-md);
}

.join-date {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.user-card-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.bulk-actions {
    background: var(--accent);
    color: white;
    padding: var(--spacing-md);
    border-radius: var(--border-radius);
    margin-top: var(--spacing-md);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bulk-info {
    font-weight: 500;
}

.bulk-buttons {
    display: flex;
    gap: var(--spacing-sm);
}

.bulk-buttons .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-md);
}

.pagination-info {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.pagination {
    display: flex;
    gap: var(--spacing-sm);
    align-items: center;
}

.pagination-btn {
    padding: 0.5rem 1rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
}

.pagination-btn:hover:not(:disabled) {
    background: var(--accent);
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-current {
    color: var(--text-primary);
    font-weight: 500;
}

/* Form Styles */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.form-group {
    margin-bottom: var(--spacing-md);
}

.form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    color: var(--text-primary);
    font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
}

.form-help {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    cursor: pointer;
}

.checkbox-label input {
    width: auto;
    margin: 0;
}

.hidden {
    display: none !important;
}

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

    .table-header {
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .pagination-section {
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .users-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Global variables
let selectedUsers = [];

// Modal functions
function showAddUserModal() {
    document.getElementById('modalTitle').textContent = '➕ Agregar Nuevo Usuario';
    document.getElementById('submitText').textContent = 'Agregar Usuario';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('userPassword').required = true;
    document.getElementById('userModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
    document.body.style.overflow = '';
}

function editUser(userId) {
    document.getElementById('modalTitle').textContent = '✏️ Editar Usuario';
    document.getElementById('submitText').textContent = 'Actualizar Usuario';
    document.getElementById('userId').value = userId;
    document.getElementById('userPassword').required = false;
    
    // Aquí cargarías los datos del usuario para editar
    // En una implementación real, harías una llamada AJAX
    
    document.getElementById('userModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function viewUser(userId) {
    window.open(`../../pages/profile.php?user=${userId}`, '_blank');
}

function deleteUser(userId) {
    if (confirm('¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.')) {
        // Aquí procesarías la eliminación
        showNotification('Usuario eliminado exitosamente', 'success');
        // Recargar la página o actualizar la tabla
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

function toggleUserStatus(userId, newStatus) {
    const action = newStatus === 'true' ? 'activar' : 'desactivar';
    
    if (confirm(`¿Estás seguro de que quieres ${action} este usuario?`)) {
        // Aquí procesarías el cambio de estado
        showNotification(`Usuario ${action === 'activar' ? 'activado' : 'desactivado'} exitosamente`, 'success');
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

// View toggle
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const view = this.dataset.view;
        
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        if (view === 'table') {
            document.getElementById('tableView').classList.remove('hidden');
            document.getElementById('cardsView').classList.add('hidden');
        } else {
            document.getElementById('tableView').classList.add('hidden');
            document.getElementById('cardsView').classList.remove('hidden');
        }
    });
});

// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedUsers = Array.from(checkboxes).map(cb => cb.value);
    selectedCount.textContent = selectedUsers.length;
    
    if (selectedUsers.length > 0) {
        bulkActions.style.display = 'flex';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
});

// Bulk actions
function bulkActivate() {
    if (selectedUsers.length === 0) return;
    
    if (confirm(`¿Activar ${selectedUsers.length} usuario(s)?`)) {
        showNotification(`${selectedUsers.length} usuario(s) activado(s)`, 'success');
        clearSelection();
    }
}

function bulkDeactivate() {
    if (selectedUsers.length === 0) return;
    
    if (confirm(`¿Desactivar ${selectedUsers.length} usuario(s)?`)) {
        showNotification(`${selectedUsers.length} usuario(s) desactivado(s)`, 'success');
        clearSelection();
    }
}

function bulkSendEmail() {
    if (selectedUsers.length === 0) return;
    
    document.getElementById('emailModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeEmailModal() {
    document.getElementById('emailModal').classList.remove('active');
    document.body.style.overflow = '';
}

function bulkDelete() {
    if (selectedUsers.length === 0) return;
    
    if (confirm(`¿Eliminar permanentemente ${selectedUsers.length} usuario(s)? Esta acción no se puede deshacer.`)) {
        showNotification(`${selectedUsers.length} usuario(s) eliminado(s)`, 'success');
        clearSelection();
    }
}

function clearSelection() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

// Export function
function exportUsers() {
    showNotification('Exportando datos de usuarios...', 'info');
    
    // Simular exportación
    setTimeout(() => {
        showNotification('Datos de usuarios exportados exitosamente', 'success');
    }, 2000);
}

// Form submissions
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const userId = document.getElementById('userId').value;
    const isEdit = userId !== '';
    
    // Simular guardado
    showNotification(isEdit ? 'Usuario actualizado exitosamente' : 'Usuario agregado exitosamente', 'success');
    
    closeUserModal();
    
    setTimeout(() => {
        window.location.reload();
    }, 1500);
});

document.getElementById('emailForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const subject = formData.get('subject');
    const message = formData.get('message');
    
    if (!subject || !message) {
        showNotification('Por favor completa todos los campos', 'error');
        return;
    }
    
    // Simular envío de email
    showNotification(`Email enviado a ${selectedUsers.length} usuario(s)`, 'success');
    
    closeEmailModal();
    clearSelection();
    this.reset();
});

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
</script>

<?php require_once '../../includes/footer.php'; ?>