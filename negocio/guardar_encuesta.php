<?php
// ============================================================
// CAPA DE NEGOCIO - Guarda una respuesta de la encuesta (anónima)
// Cualquier persona puede responderla: no hace falta iniciar sesión
// y no se guarda quién la respondió.
// ============================================================

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/validaciones.php';
require_once __DIR__ . '/../datos/conexion.php';
require_once __DIR__ . '/../datos/Encuesta.php';

// Solo se puede llegar acá enviando el formulario (POST).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../presentacion/encuesta.php');
}

$calificacion = $_POST['calificacion'] ?? '';
$comentarios  = trim($_POST['comentarios'] ?? '');

// Validar en PHP (calificación de 1 a 5, comentario de hasta 300 caracteres)
$errores = validarEncuesta($calificacion, $comentarios);
if ($errores) {
    guardarMensaje($errores[0], 'danger');
    redirigir('../presentacion/encuesta.php');
}

try {
    $encuesta = new Encuesta((new Conexion())->getConexion());
    $encuesta->guardar((int) $calificacion, $comentarios);
    guardarMensaje('¡Gracias por tu respuesta! Tu opinión es totalmente anónima y nos ayuda a mejorar.');
} catch (PDOException $e) {
    error_log('Error al guardar la encuesta: ' . $e->getMessage());
    guardarMensaje('No se pudo guardar la respuesta. Intente nuevamente.', 'danger');
}

redirigir('../presentacion/encuesta.php');
