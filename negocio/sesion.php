<?php
// ============================================================
// Sesiones, control de acceso por rol y funciones de ayuda.
// Se incluye al principio de TODAS las páginas:
//     require_once __DIR__ . '/../negocio/sesion.php';
// ============================================================

require_once __DIR__ . '/../config/config.php';   // constantes del sistema (URL_BASE, carpeta de subidas...)

// Inicia la sesión (con la cookie protegida contra JavaScript).
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ¿Hay alguien con la sesión iniciada?
function hayUsuarioLogueado() {
    return isset($_SESSION['usuario']);
}

// Página de inicio según el rol del usuario.
// Las rutas empiezan con ../presentacion/ porque sirven tanto desde presentacion/ como desde negocio/.
function paginaDeInicio($rol) {
    return $rol === 'admin' ? '../presentacion/index.php' : '../presentacion/portal-paciente.php';
}

// Protege una página. Ejemplo: exigirRol('admin');
//  - Si no hay sesión, manda al login.
//  - Si el rol no corresponde, manda a la página de inicio de ese usuario.
function exigirRol($rolPermitido) {
    if (!hayUsuarioLogueado()) {
        redirigir('../presentacion/login.php');
    }
    if ($_SESSION['usuario']['rol'] !== $rolPermitido) {
        redirigir(paginaDeInicio($_SESSION['usuario']['rol']));
    }
}

// Escapa un texto antes de mostrarlo en pantalla (evita que se ejecute HTML/JavaScript metido por alguien).
// Se usa dentro del HTML así:  echo e($variable);
// (OJO: en un comentario de una línea nunca escribir la etiqueta de cierre de PHP, porque corta el código)
function e($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// Redirige a otra página y corta la ejecución.
function redirigir($destino) {
    header("Location: $destino");
    exit;
}

// Guarda un mensaje para mostrarlo en la próxima página.
// $tipo: success (verde), danger (rojo) o warning (amarillo)
function guardarMensaje($texto, $tipo = 'success') {
    $_SESSION['mensaje'] = ['texto' => $texto, 'tipo' => $tipo];
}

// Muestra el mensaje guardado (si hay) y lo borra para que no se repita.
function mostrarMensaje() {
    if (!isset($_SESSION['mensaje'])) {
        return;
    }
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
    echo '<div class="alert alert-' . e($mensaje['tipo']) . ' alert-dismissible fade show" role="alert">'
       . e($mensaje['texto'])
       . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
}
