<?php
$pageTitle = "Registro";
require_once '../includes/header.php';

// Si ya está logueado, redirigir
if ($user) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $acceptTerms = isset($_POST['accept_terms']);
    
    // Validar CSRF
    $csrf_valid = true;
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token'])) {
        $csrf_valid = $sceneiq->validateCSRFToken($_POST['csrf_token']);
    }
    
    if (!$csrf_valid) {
        $error = 'Token de seguridad inválido.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = 'El nombre de usuario debe tener entre 3 y 20 caracteres.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'El nombre de usuario solo puede contener letras, números y guiones bajos.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!$acceptTerms) {
        $error = 'Debes aceptar los términos y condiciones.';
    } else {
        // Intentar registrar al usuario
        if ($sceneiq->registerUser($username, $email, $password, $fullName)) {
            $success = 'Registro exitoso. Ya puedes iniciar sesión.';
            $_POST = [];
        } else {
            $error = 'Error al registrar. El email o nombre de usuario ya existe.';
        }
    }
}

// Obtener géneros
$genres = $sceneiq->getGenres();

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>¡Únete a SceneIQ!</h1>
            <p>Crea tu cuenta y descubre tu próxima obsesión cinematográfica</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo escape($success); ?>
                <a href="login.php" style="color: white; text-decoration: underline; margin-left: 10px;">Iniciar sesión ahora</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Nombre completo</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo escape($_POST['full_name'] ?? ''); ?>" 
                           required autocomplete="name">
                    <span class="form-icon">👤</span>
                </div>
                
                <div class="form-group">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo escape($_POST['username'] ?? ''); ?>" 
                           required autocomplete="username">
                    <span class="form-icon">@</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo escape($_POST['email'] ?? ''); ?>" 
                       required autocomplete="email">
                <span class="form-icon">📧</span>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    <span class="form-icon">🔒</span>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    <span class="form-icon">🔒</span>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>
            
            <!-- Selección de géneros favoritos -->
            <div class="form-group">
                <label>Selecciona tus géneros favoritos (opcional)</label>
                <div class="genre-selection">
                    <?php foreach ($genres as $genre): ?>
                        <label class="genre-checkbox">
                            <input type="checkbox" name="favorite_genres[]" value="<?php echo $genre['id']; ?>">
                            <span class="genre-label" style="--genre-color: <?php echo $genre['color']; ?>">
                                <?php echo escape($genre['name']); ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group form-checkbox">
                <input type="checkbox" id="accept_terms" name="accept_terms" required>
                <label for="accept_terms">
                    Acepto los <a href="terms.php" target="_blank">términos y condiciones</a> 
                    y la <a href="privacy.php" target="_blank">política de privacidad</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                Crear Cuenta
            </button>
        </form>
        
        <div class="auth-footer">
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
        
        <!-- Demo info -->
        <div class="demo-section">
            <h4>💡 Para probar SceneIQ:</h4>
            <p>Puedes usar las cuentas demo en la página de <a href="login.php">inicio de sesión</a></p>
            <div class="demo-accounts">
                <span class="demo-info">👑 Admin: admin@sceneiq.com / admin123</span>
                <span class="demo-info">👤 Usuario: user@sceneiq.com / user123</span>
            </div>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-lg);
    background: linear-gradient(135deg, var(--dark-bg) 0%, #1a1a2e 100%);
}

.auth-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius-large);
    padding: 2rem;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.auth-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.auth-header h1 {
    color: var(--text-primary);
    font-size: 1.8rem;
    margin-bottom: var(--spacing-sm);
}

.auth-header p {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.form-group {
    position: relative;
    margin-bottom: var(--spacing-md);
}

.form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    color: var(--text-primary);
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 0.8rem;
    padding-right: 3rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-primary);
    transition: var(--transition);
    font-size: 1rem;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
}

.form-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    pointer-events: none;
    z-index: 1;
}

.password-toggle {
    position: absolute;
    right: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 1rem;
    padding: 0.2rem;
    z-index: 2;
}

.password-toggle:hover {
    color: var(--text-primary);
}

.genre-selection {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: var(--spacing-sm);
    margin-top: var(--spacing-sm);
}

.genre-checkbox {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.genre-checkbox input {
    display: none;
}

.genre-label {
    padding: 0.5rem 1rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    color: var(--text-secondary);
    font-size: 0.8rem;
    transition: var(--transition);
    text-align: center;
    width: 100%;
}

.genre-checkbox input:checked + .genre-label {
    background: var(--genre-color);
    color: white;
    border-color: var(--genre-color);
}

.form-checkbox {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-sm);
}

.form-checkbox input {
    width: auto;
    margin: 0;
    padding: 0;
    margin-top: 0.2rem;
}

.form-checkbox label {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: normal;
    line-height: 1.4;
}

.form-checkbox a {
    color: var(--accent);
    text-decoration: none;
}

.form-checkbox a:hover {
    text-decoration: underline;
}

.btn-full {
    width: 100%;
    margin: var(--spacing-lg) 0;
}

.auth-footer {
    text-align: center;
    color: var(--text-secondary);
}

.auth-footer a {
    color: var(--accent);
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    margin-bottom: var(--spacing-md);
    padding: var(--spacing-md);
    border-radius: var(--border-radius-small);
    border: 1px solid;
}

.alert-error {
    background: rgba(255, 107, 107, 0.1);
    border-color: rgba(255, 107, 107, 0.3);
    color: #ff6b6b;
}

.alert-success {
    background: rgba(0, 210, 255, 0.1);
    border-color: rgba(0, 210, 255, 0.3);
    color: #00d2ff;
}

.demo-section {
    margin-top: var(--spacing-lg);
    padding-top: var(--spacing-lg);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.demo-section h4 {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-sm);
    font-size: 0.9rem;
}

.demo-section p {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-bottom: var(--spacing-md);
}

.demo-accounts {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.demo-info {
    font-size: 0.8rem;
    color: var(--text-secondary);
    background: var(--glass-bg);
    padding: 0.5rem;
    border-radius: var(--border-radius-small);
    font-family: monospace;
}

.demo-accounts a {
    color: var(--accent);
    text-decoration: none;
}

.demo-accounts a:hover {
    text-decoration: underline;
}

.field-error {
    color: #ff6b6b;
    font-size: 0.8rem;
    margin-top: var(--spacing-xs);
}

.field-success {
    color: #00d2ff;
    font-size: 0.8rem;
    margin-top: var(--spacing-xs);
}

.form-group input.error {
    border-color: #ff6b6b;
}

.form-group input.success {
    border-color: #00d2ff;
}

@media (max-width: 768px) {
    .auth-container {
        padding: var(--spacing-md);
    }
    
    .auth-card {
        padding: 1.5rem;
        max-width: 100%;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: var(--spacing-sm);
    }
    
    .genre-selection {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    }
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.parentNode.querySelector('.password-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.textContent = '🙈';
    } else {
        field.type = 'password';
        toggle.textContent = '👁️';
    }
}

// Validación en tiempo real
document.getElementById('username').addEventListener('input', function() {
    const username = this.value;
    if (username.length > 0 && username.length < 3) {
        showFieldError(this, 'Mínimo 3 caracteres');
    } else if (username.length > 20) {
        showFieldError(this, 'Máximo 20 caracteres');
    } else if (username && !username.match(/^[a-zA-Z0-9_]+$/)) {
        showFieldError(this, 'Solo letras, números y guiones bajos');
    } else {
        hideFieldError(this);
        // Verificar disponibilidad (simulado)
        if (username.length >= 3) {
            checkUsernameAvailability(username);
        }
    }
});

document.getElementById('email').addEventListener('blur', function() {
    const email = this.value;
    if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        showFieldError(this, 'Email no válido');
    } else {
        hideFieldError(this);
    }
});

document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        showFieldError(this, 'Las contraseñas no coinciden');
    } else {
        hideFieldError(this);
    }
});

function checkUsernameAvailability(username) {
    // Simulación de verificación de disponibilidad
    const unavailableUsernames = ['admin', 'user', 'test', 'sceneiq'];
    const field = document.getElementById('username');
    
    setTimeout(() => {
        if (unavailableUsernames.includes(username.toLowerCase())) {
            field.classList.add('error');
            field.classList.remove('success');
            showFieldError(field, 'No disponible');
        } else {
            field.classList.add('success');
            field.classList.remove('error');
            showFieldSuccess(field, 'Disponible ✓');
        }
    }, 500);
}

function showFieldSuccess(field, message) {
    hideFieldError(field);
    let successDiv = field.parentNode.querySelector('.field-success');
    if (!successDiv) {
        successDiv = document.createElement('div');
        successDiv.className = 'field-success';
        field.parentNode.appendChild(successDiv);
    }
    successDiv.textContent = message;
}

function showFieldError(field, message) {
    const successDiv = field.parentNode.querySelector('.field-success');
    if (successDiv) successDiv.remove();
    
    let errorDiv = field.parentNode.querySelector('.field-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

function hideFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
    const successDiv = field.parentNode.querySelector('.field-success');
    if (successDiv) {
        successDiv.remove();
    }
}

// Validación del formulario antes del envío
document.querySelector('.auth-form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const acceptTerms = document.getElementById('accept_terms').checked;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return;
    }
    
    if (!acceptTerms) {
        e.preventDefault();
        alert('Debes aceptar los términos y condiciones');
        return;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return;
    }
});

// Focus en el primer campo
document.addEventListener('DOMContentLoaded', function() {
    const firstField = document.getElementById('full_name');
    if (firstField && !firstField.value) {
        firstField.focus();
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>