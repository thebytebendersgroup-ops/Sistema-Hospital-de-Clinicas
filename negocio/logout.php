<?php
// ============================================================
// Cierre de sesión: destruye la sesión y vuelve al login.
// ============================================================

require_once __DIR__ . '/sesion.php';

// 1) Vaciar los datos de la sesión
$_SESSION = [];

// 2) Borrar la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $parametros["path"], $parametros["domain"],
        $parametros["secure"], $parametros["httponly"]
    );
}

// 3) Destruir la sesión en el servidor
session_destroy();

redirigir('../presentacion/login.php');
