<?php
$pageTitle = "Iniciar Sesión";
require_once '../includes/header.php';

// Definir constantes si no existen
if (!defined('COOKIE_LIFETIME')) define('COOKIE_LIFETIME', 3600 * 24 * 30); // 30 días

// Si ya está logueado, redirigir
if ($user) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validar CSRF
    if (!$sceneiq->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';
    } else {
        // Intentar hacer login
        try {
            if (method_exists($sceneiq, 'loginUser')) {
                $loginResult = $sceneiq->loginUser($email, $password);
                
                if ($loginResult) {
                    // Si marcó "recordarme", establecer cookie
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + COOKIE_LIFETIME, '/');
                        // En una implementación real, guardarías el token en la BD asociado al usuario
                    }
                    
                    $redirectUrl = $_GET['redirect'] ?? '../index.php';
                    redirect($redirectUrl);
                } else {
                    $error = 'Email o contraseña incorrectos.';
                }
            } else {
                $error = 'Sistema de login no disponible. Usa las cuentas demo.';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Error interno del servidor. Por favor, intenta más tarde.';
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>¡Bienvenido de vuelta!</h1>
            <p>Inicia sesión para continuar descubriendo contenido increíble</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo escape($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo escape($_POST['email'] ?? ''); ?>" 
                       required autocomplete="email">
                <span class="form-icon">📧</span>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <span class="form-icon">🔒</span>
                <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
            </div>
            
            <div class="form-group form-checkbox">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordarme por 30 días</label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                Iniciar Sesión
            </button>
        </form>
        
        <div class="auth-footer">
            <p><a href="forgot-password.php">¿Olvidaste tu contraseña?</a></p>
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
        
        <!-- Demo credentials -->
        <div class="demo-section">
            <h4>🎭 Cuentas de prueba:</h4>
            <div class="demo-accounts">
                <button type="button" class="demo-btn" onclick="fillDemo('admin@sceneiq.com', 'admin123')">
                    👑 Admin Demo
                </button>
                <button type="button" class="demo-btn" onclick="fillDemo('user@sceneiq.com', 'user123')">
                    👤 Usuario Demo
                </button>
            </div>
            <p class="demo-note">
                Haz clic en los botones para llenar automáticamente los campos de login
            </p>
        </div>
    </div>
</div>

<style>
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

.demo-accounts {
    display: flex;
    gap: var(--spacing-sm);
    justify-content: center;
    margin-bottom: var(--spacing-sm);
}

.demo-btn {
    padding: 0.5rem 1rem;
    background: var(--glass-bg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius-small);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.8rem;
}

.demo-btn:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.demo-note {
    color: var(--text-secondary);
    font-size: 0.75rem;
    font-style: italic;
    margin-top: var(--spacing-xs);
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
    color: var(--error);
}

.alert-success {
    background: rgba(0, 210, 255, 0.1);
    border-color: rgba(0, 210, 255, 0.3);
    color: var(--success);
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
}

.form-group input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
}

.form-group input::placeholder {
    color: var(--text-secondary);
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

.form-checkbox {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.form-checkbox input {
    width: auto;
    margin: 0;
    padding: 0;
}

.form-checkbox label {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: normal;
}

.btn-full {
    width: 100%;
    margin: var(--spacing-lg) 0;
}

.auth-footer {
    text-align: center;
    color: var(--text-secondary);
}

.auth-footer p {
    margin-bottom: var(--spacing-sm);
}

.auth-footer a {
    color: var(--accent);
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.field-error {
    color: var(--error);
    font-size: 0.8rem;
    margin-top: var(--spacing-xs);
}

.field-success {
    color: var(--success);
    font-size: 0.8rem;
    margin-top: var(--spacing-xs);
}

.form-group input.error {
    border-color: var(--error);
}

.form-group input.success {
    border-color: var(--success);
}

@media (max-width: 768px) {
    .demo-accounts {
        flex-direction: column;
        align-items: center;
    }
    
    .demo-btn {
        width: 200px;
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

function fillDemo(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    
    // Efecto visual para mostrar que se llenaron los campos
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    
    emailField.style.backgroundColor = 'rgba(0, 210, 255, 0.1)';
    passwordField.style.backgroundColor = 'rgba(0, 210, 255, 0.1)';
    
    setTimeout(() => {
        emailField.style.backgroundColor = '';
        passwordField.style.backgroundColor = '';
    }, 1000);
}

// Validación en tiempo real
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value;
    if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        this.classList.add('error');
        showFieldError(this, 'Email no válido');
    } else {
        this.classList.remove('error');
        hideFieldError(this);
    }
});

function showFieldError(field, message) {
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
}

// Focus automático en el primer campo
document.addEventListener('DOMContentLoaded', function() {
    const emailField = document.getElementById('email');
    if (emailField && !emailField.value) {
        emailField.focus();
    }
});

// Envío del formulario con validación
document.querySelector('.auth-form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Por favor, completa todos los campos');
        return;
    }
    
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        e.preventDefault();
        alert('Por favor, ingresa un email válido');
        return;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>