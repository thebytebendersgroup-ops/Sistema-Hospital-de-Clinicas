<?php
// ============================================================
// Eliminación (baja lógica) de un documento.
// La confirmación "¿Está seguro?" se pide en index.php antes de enviar.
// ============================================================

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../datos/conexion.php';
require_once __DIR__ . '/../datos/Documento.php';

exigirRol('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../presentacion/index.php?sec=documentos');
}

$id = trim($_POST['id'] ?? '');

try {
    $documento = new Documento((new Conexion())->getConexion());

    if (ctype_digit($id) && $documento->buscarPorId($id)) {
        $documento->darDeBaja($id);
        guardarMensaje('Documento eliminado correctamente.');
    } else {
        guardarMensaje('El documento no existe.', 'danger');
    }
} catch (PDOException $e) {
    error_log('Error al eliminar el documento: ' . $e->getMessage());
    guardarMensaje('No se pudo eliminar el documento.', 'danger');
}

redirigir('../presentacion/index.php?sec=documentos');
