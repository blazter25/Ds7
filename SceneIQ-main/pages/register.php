<?php
$pageTitle = "Registro";
require_once '../includes/header.php';

// Si ya está logueado, redirigir
if ($user) {
    redirect('../index.php');
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
    if (!$sceneiq->validateCSRFToken($_POST['csrf_token'] ?? '')) {
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
        try {
            if (method_exists($sceneiq, 'registerUser')) {
                $result = $sceneiq->registerUser($username, $email, $password, $fullName);
                
                if ($result) {
                    $success = 'Registro exitoso. Ya puedes iniciar sesión.';
                    // Limpiar formulario
                    $_POST = [];
                } else {
                    $error = 'Error al registrar. El email o nombre de usuario ya existe.';
                }
            } else {
                // Si no existe el método, simular registro exitoso
                $success = 'Registro simulado exitoso. Ya puedes iniciar sesión con las cuentas demo.';
                $_POST = [];
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = 'Error interno del servidor. Por favor, intenta más tarde.';
        }
    }
}

$genres = $sceneiq->getGenres();
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
            <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
            
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
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.nextElementSibling.nextElementSibling; // Skip the form-icon span
    
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
</script>

<?php require_once '../includes/footer.php'; ?>