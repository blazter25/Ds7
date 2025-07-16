<?php
// Iniciar output buffering ANTES de cualquier otra cosa
ob_start();

$pageTitle = "Iniciar Sesión";
require_once '../includes/functions.php';

// Definir constantes si no existen
if (!defined('COOKIE_LIFETIME')) define('COOKIE_LIFETIME', 3600 * 24 * 30);

// Si ya está logueado, redirigir
if ($user) {
    // Limpiar buffer y redirigir
    ob_end_clean();
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validar CSRF
    $csrf_valid = true;
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token'])) {
        $csrf_valid = $sceneiq->validateCSRFToken($_POST['csrf_token']);
    }
    
    if (!$csrf_valid) {
        $error = 'Token de seguridad inválido.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';
    } else {
        // Intentar hacer login
        if ($sceneiq->loginUser($email, $password)) {
            // Si marcó "recordarme", establecer cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + COOKIE_LIFETIME, '/');
            }
            
            // Limpiar buffer completamente y redirigir
            ob_end_clean();
            $redirectUrl = $_GET['redirect'] ?? '../index.php';
            header('Location: ' . $redirectUrl);
            exit();
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Limpiar el buffer antes de mostrar contenido
ob_end_clean();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SceneIQ</title>
    <style>
        :root {
            --dark-bg: #0a0e27;
            --text-primary: #ffffff;
            --text-secondary: #b8c6db;
            --card-bg: rgba(255, 255, 255, 0.03);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --accent: #ff6b6b;
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --border-radius: 12px;
            --border-radius-small: 6px;
            --border-radius-large: 20px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1a1a2e 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: var(--spacing-lg);
        }

        .auth-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius-large);
            padding: 2rem;
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

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }

        .form-checkbox input {
            width: auto;
            margin: 0;
        }

        .form-checkbox label {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: normal;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-small);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-1px);
        }

        .auth-footer {
            text-align: center;
            color: var(--text-secondary);
            margin-top: var(--spacing-lg);
        }

        .auth-footer a {
            color: var(--accent);
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .logo {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }

        .logo a {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            text-decoration: none;
        }

        .logo span {
            color: var(--accent);
        }

        .info-section {
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-lg);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .info-section p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <a href="../index.php">Scene<span>IQ</span></a>
        </div>
        
        <div class="auth-card">
            <div class="auth-header">
                <h1>¡Bienvenido de vuelta!</h1>
                <p>Inicia sesión para continuar descubriendo contenido increíble</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <div class="form-checkbox">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Recordarme por 30 días</label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
            </div>
            
            <div class="info-section">
                <p>Usa tu cuenta registrada para acceder al sistema.</p>
            </div>
        </div>
    </div>
</body>
</html>