<?php
require_once '../includes/auth.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    redirect('/pages/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $result = loginUser($username, $password, $remember);
        
        if ($result['success']) {
            // Guardar última actividad en cookie
            savePreference('last_activity', time());
            redirect('/pages/dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Desafíos Fitness</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="card fade-in">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h1 class="logo">💪 Fitness Challenge</h1>
                    <p style="color: #6b7280; margin-top: 0.5rem;">Bienvenido de vuelta</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="username">
                            <i class="fas fa-user"></i> Usuario o Email
                        </label>
                        <input type="text" id="username" name="username" class="form-control" 
                               required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               placeholder="Ingresa tu usuario o email">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-control" 
                                   required placeholder="Ingresa tu contraseña">
                            <i class="fas fa-eye" onclick="togglePassword('password')" 
                               style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280;"></i>
                        </div>
                    </div>
                    
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="display: flex; align-items: center; margin-bottom: 0; cursor: pointer;">
                            <input type="checkbox" name="remember" id="remember" style="margin-right: 0.5rem;">
                            <span style="color: #6b7280; font-size: 0.875rem;">Recordarme</span>
                        </label>
                        <a href="#" style="color: var(--primary-color); text-decoration: none; font-size: 0.875rem;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <p style="color: #6b7280;">
                        ¿No tienes una cuenta? 
                        <a href="register.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                            Regístrate gratis
                        </a>
                    </p>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
                    <p style="text-align: center; color: #6b7280; font-size: 0.875rem;">
                        O continúa con
                    </p>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button class="btn btn-secondary" style="flex: 1;">
                            <i class="fab fa-google"></i> Google
                        </button>
                        <button class="btn btn-secondary" style="flex: 1;">
                            <i class="fab fa-facebook"></i> Facebook
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>