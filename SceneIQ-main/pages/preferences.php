<?php
// pages/preferences.php
$pageTitle = "Preferencias";
require_once '../includes/header.php';

// Verificar que el usuario esté logueado
if (!$user) {
    redirect('login.php');
}

$success = '';
$error = '';

// Manejar actualización de preferencias
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sceneiq->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        // Procesar diferentes tipos de actualización
        if (isset($_POST['update_theme'])) {
            $theme = $_POST['theme'] ?? 'dark';
            if (in_array($theme, ['dark', 'light', 'auto'])) {
                $_SESSION['theme_preference'] = $theme;
                $success = 'Tema actualizado correctamente.';
            }
        } elseif (isset($_POST['update_genres'])) {
            // Procesar géneros favoritos
            $favoriteGenres = $_POST['favorite_genres'] ?? [];
            // Aquí guardarías en la BD las preferencias de géneros
            $success = 'Preferencias de géneros actualizadas.';
        } elseif (isset($_POST['update_notifications'])) {
            // Procesar configuración de notificaciones
            $notifications = [
                'recommendations' => isset($_POST['notify_recommendations']),
                'reviews' => isset($_POST['notify_reviews']),
                'newsletter' => isset($_POST['notify_newsletter'])
            ];
            $_SESSION['notification_preferences'] = $notifications;
            $success = 'Configuración de notificaciones actualizada.';
        } elseif (isset($_POST['update_privacy'])) {
            // Procesar configuración de privacidad
            $privacy = [
                'public_profile' => isset($_POST['public_profile']),
                'show_lists' => isset($_POST['show_lists']),
                'show_reviews' => isset($_POST['show_reviews'])
            ];
            $_SESSION['privacy_preferences'] = $privacy;
            $success = 'Configuración de privacidad actualizada.';
        }
    }
}

// Obtener preferencias actuales
$currentTheme = $_SESSION['theme_preference'] ?? 'dark';
$notificationPrefs = $_SESSION['notification_preferences'] ?? [
    'recommendations' => true,
    'reviews' => true,
    'newsletter' => false
];
$privacyPrefs = $_SESSION['privacy_preferences'] ?? [
    'public_profile' => true,
    'show_lists' => true,
    'show_reviews' => true
];

$genres = $sceneiq->getGenres();
?>

<div class="preferences-container">
    <!-- Header -->
    <div class="preferences-header">
        <h1>⚙️ Preferencias</h1>
        <p>Personaliza tu experiencia en SceneIQ</p>
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

    <div class="preferences-content">
        <!-- Theme Preferences -->
        <div class="preference-section">
            <div class="section-header">
                <h2>🎨 Apariencia</h2>
                <p>Personaliza cómo se ve SceneIQ</p>
            </div>
            
            <form method="POST" class="preference-form">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                <input type="hidden" name="update_theme" value="1">
                
                <div class="theme-options">
                    <label class="theme-option <?php echo $currentTheme === 'dark' ? 'active' : ''; ?>">
                        <input type="radio" name="theme" value="dark" <?php echo $currentTheme === 'dark' ? 'checked' : ''; ?>>
                        <div class="theme-preview dark-theme">
                            <div class="theme-icon">🌙</div>
                            <div class="theme-name">Oscuro</div>
                            <div class="theme-description">Perfecto para ver contenido por la noche</div>
                        </div>
                    </label>
                    
                    <label class="theme-option <?php echo $currentTheme === 'light' ? 'active' : ''; ?>">
                        <input type="radio" name="theme" value="light" <?php echo $currentTheme === 'light' ? 'checked' : ''; ?>>
                        <div class="theme-preview light-theme">
                            <div class="theme-icon">☀️</div>
                            <div class="theme-name">Claro</div>
                            <div class="theme-description">Ideal para uso durante el día</div>
                        </div>
                    </label>
                    
                    <label class="theme-option <?php echo $currentTheme === 'auto' ? 'active' : ''; ?>">
                        <input type="radio" name="theme" value="auto" <?php echo $currentTheme === 'auto' ? 'checked' : ''; ?>>
                        <div class="theme-preview auto-theme">
                            <div class="theme-icon">🔄</div>
                            <div class="theme-name">Automático</div>
                            <div class="theme-description">Se ajusta según tu sistema</div>
                        </div>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar Tema</button>
            </form>
        </div>

        <!-- Genre Preferences -->
        <div class="preference-section">
            <div class="section-header">
                <h2>🎭 Géneros Favoritos</h2>
                <p>Selecciona tus géneros preferidos para mejores recomendaciones</p>
            </div>
            
            <form method="POST" class="preference-form">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                <input type="hidden" name="update_genres" value="1">
                
                <div class="genres-grid">
                    <?php foreach ($genres as $genre): ?>
                        <label class="genre-preference">
                            <input type="checkbox" name="favorite_genres[]" value="<?php echo $genre['id']; ?>" 
                                   <?php echo rand(0, 1) ? 'checked' : ''; ?>>
                            <div class="genre-card" style="--genre-color: <?php echo $genre['color']; ?>">
                                <div class="genre-name"><?php echo escape($genre['name']); ?></div>
                                <div class="genre-indicator"></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar Géneros</button>
            </form>
        </div>

        <!-- Notification Preferences -->
        <div class="preference-section">
            <div class="section-header">
                <h2>🔔 Notificaciones</h2>
                <p>Controla qué notificaciones quieres recibir</p>
            </div>
            
            <form method="POST" class="preference-form">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                <input type="hidden" name="update_notifications" value="1">
                
                <div class="notification-options">
                    <div class="notification-item">
                        <div class="notification-info">
                            <h3>🎯 Nuevas Recomendaciones</h3>
                            <p>Recibe notificaciones cuando tengamos nuevas recomendaciones personalizadas para ti</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_recommendations" 
                                   <?php echo $notificationPrefs['recommendations'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <h3>💬 Respuestas a Reseñas</h3>
                            <p>Notificarme cuando otros usuarios respondan o reaccionen a mis reseñas</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_reviews" 
                                   <?php echo $notificationPrefs['reviews'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <h3>📧 Newsletter Semanal</h3>
                            <p>Recibe un resumen semanal con contenido nuevo, tendencias y recomendaciones</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="notify_newsletter" 
                                   <?php echo $notificationPrefs['newsletter'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar Notificaciones</button>
            </form>
        </div>

        <!-- Privacy Preferences -->
        <div class="preference-section">
            <div class="section-header">
                <h2>🔒 Privacidad</h2>
                <p>Controla qué información es visible para otros usuarios</p>
            </div>
            
            <form method="POST" class="preference-form">
                <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
                <input type="hidden" name="update_privacy" value="1">
                
                <div class="privacy-options">
                    <div class="privacy-item">
                        <div class="privacy-info">
                            <h3>👥 Perfil Público</h3>
                            <p>Permite que otros usuarios vean tu perfil, actividad y estadísticas</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="public_profile" 
                                   <?php echo $privacyPrefs['public_profile'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="privacy-item">
                        <div class="privacy-info">
                            <h3>📋 Mostrar Listas</h3>
                            <p>Permite que otros usuarios vean tus listas de favoritos y seguimiento</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="show_lists" 
                                   <?php echo $privacyPrefs['show_lists'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="privacy-item">
                        <div class="privacy-info">
                            <h3>📝 Mostrar Reseñas</h3>
                            <p>Permite que otros usuarios vean las reseñas que has escrito</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="show_reviews" 
                                   <?php echo $privacyPrefs['show_reviews'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar Privacidad</button>
            </form>
        </div>

        <!-- Advanced Settings -->
        <div class="preference-section">
            <div class="section-header">
                <h2>🛠️ Configuración Avanzada</h2>
                <p>Opciones adicionales para personalizar tu experiencia</p>
            </div>
            
            <div class="advanced-options">
                <div class="advanced-item">
                    <div class="advanced-info">
                        <h3>📊 Exportar Mis Datos</h3>
                        <p>Descarga una copia de toda tu actividad e información en SceneIQ</p>
                    </div>
                    <button class="btn btn-secondary" onclick="exportUserData()">Descargar Datos</button>
                </div>
                
                <div class="advanced-item">
                    <div class="advanced-info">
                        <h3>🔄 Resetear Recomendaciones</h3>
                        <p>Reinicia el algoritmo de recomendaciones para empezar desde cero</p>
                    </div>
                    <button class="btn btn-warning" onclick="resetRecommendations()">Resetear</button>
                </div>
                
                <div class="advanced-item">
                    <div class="advanced-info">
                        <h3>🗑️ Eliminar Cuenta</h3>
                        <p>Elimina permanentemente tu cuenta y todos tus datos</p>
                    </div>
                    <button class="btn btn-danger" onclick="deleteAccount()">Eliminar Cuenta</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.preferences-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

.preferences-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.preferences-header h1 {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-sm);
    color: var(--text-primary);
}

.preferences-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.preferences-content {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xl);
}

.preference-section {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
}

.section-header {
    margin-bottom: var(--spacing-lg);
}

.section-header h2 {
    color: var(--text-primary);
    font-size: 1.5rem;
    margin-bottom: var(--spacing-sm);
}

.section-header p {
    color: var(--text-secondary);
    font-size: 1rem;
}

.preference-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

/* Theme Options */
.theme-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-lg);
}

.theme-option {
    cursor: pointer;
    transition: var(--transition);
}

.theme-option input {
    display: none;
}

.theme-preview {
    background: var(--glass-bg);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    text-align: center;
    transition: var(--transition);
}

.theme-option.active .theme-preview,
.theme-option:hover .theme-preview {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.theme-icon {
    font-size: 2rem;
    margin-bottom: var(--spacing-sm);
}

.theme-name {
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: var(--spacing-xs);
}

.theme-description {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* Genre Preferences */
.genres-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--spacing-md);
}

.genre-preference {
    cursor: pointer;
}

.genre-preference input {
    display: none;
}

.genre-card {
    background: var(--glass-bg);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: var(--spacing-md);
    text-align: center;
    transition: var(--transition);
    position: relative;
}

.genre-preference input:checked + .genre-card {
    border-color: var(--genre-color);
    background: rgba(var(--genre-color), 0.1);
}

.genre-card:hover {
    transform: translateY(-2px);
    border-color: var(--genre-color);
}

.genre-name {
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: var(--spacing-sm);
}

.genre-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--genre-color);
    margin: 0 auto;
    opacity: 0;
    transition: var(--transition);
}

.genre-preference input:checked + .genre-card .genre-indicator {
    opacity: 1;
}

/* Notification Options */
.notification-options,
.privacy-options {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.notification-item,
.privacy-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--glass-bg);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.notification-info h3,
.privacy-info h3 {
    color: var(--text-primary);
    font-size: 1.1rem;
    margin-bottom: var(--spacing-xs);
}

.notification-info p,
.privacy-info p {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    border-radius: 30px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 24px;
    width: 24px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: var(--transition);
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: var(--accent);
}

input:checked + .toggle-slider:before {
    transform: translateX(30px);
}

/* Advanced Options */
.advanced-options {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.advanced-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--glass-bg);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.advanced-info h3 {
    color: var(--text-primary);
    font-size: 1.1rem;
    margin-bottom: var(--spacing-xs);
}

.advanced-info p {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* Alerts */
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

@media (max-width: 768px) {
    .notification-item,
    .privacy-item,
    .advanced-item {
        flex-direction: column;
        gap: var(--spacing-md);
        text-align: center;
    }
    
    .theme-options {
        grid-template-columns: 1fr;
    }
    
    .genres-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
}
</style>

<script>
// Aplicar tema inmediatamente cuando se selecciona
document.querySelectorAll('input[name="theme"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.body.setAttribute('data-theme', this.value);
        
        // Actualizar opciones visuales
        document.querySelectorAll('.theme-option').forEach(option => {
            option.classList.remove('active');
        });
        this.closest('.theme-option').classList.add('active');
        
        showNotification('Tema aplicado: ' + this.value, 'success');
    });
});

// Toggle switches con feedback visual
document.querySelectorAll('.toggle-switch input').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const label = this.closest('.notification-item, .privacy-item').querySelector('h3').textContent;
        const status = this.checked ? 'activada' : 'desactivada';
        showNotification(label + ' ' + status, 'info');
    });
});

// Género preferences con feedback
document.querySelectorAll('.genre-preference input').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const genreName = this.nextElementSibling.querySelector('.genre-name').textContent;
        const action = this.checked ? 'agregado a' : 'removido de';
        showNotification(genreName + ' ' + action + ' tus favoritos', 'info');
    });
});

// Funciones avanzadas
function exportUserData() {
    if (confirm('¿Descargar todos tus datos de SceneIQ?')) {
        showNotification('Preparando descarga de datos...', 'info');
        setTimeout(() => {
            showNotification('Descarga iniciada', 'success');
        }, 2000);
    }
}

function resetRecommendations() {
    if (confirm('¿Resetear todas tus recomendaciones? Esto reiniciará el algoritmo desde cero.')) {
        showNotification('Recomendaciones reseteadas exitosamente', 'success');
    }
}

function deleteAccount() {
    if (confirm('⚠️ ¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.')) {
        if (confirm('Esta acción eliminará permanentemente todos tus datos. ¿Continuar?')) {
            showNotification('Procesando eliminación de cuenta...', 'warning');
        }
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer; margin-left: 1rem;">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    // Posicionar en la parte superior
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

// Auto-save al cambiar configuraciones
document.querySelectorAll('.preference-form').forEach(form => {
    const inputs = form.querySelectorAll('input[type="checkbox"], input[type="radio"]');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // Auto-guardar después de 1 segundo de inactividad
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                // Aquí podrías hacer una llamada AJAX para guardar automáticamente
                console.log('Auto-saving preferences...');
            }, 1000);
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>