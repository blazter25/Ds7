<?php
// api/logout.php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir cualquier cookie de "recordarme"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al inicio
header('Location: ../index.php');
exit();
?>