<?php
require_once __DIR__ . '/../config/config.php';

// ============================================================
// Validaciones de formularios en PHP (lado del servidor)
// Cada función devuelve una lista de errores. Si la lista está vacía,
// los datos son correctos.
// ============================================================

// --- Login ---
function validarLogin($email, $contrasenia) {
    $errores = [];
    if ($email === '' || $contrasenia === '') {
        $errores[] = 'Debe ingresar el email y la contraseña.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no tiene un formato válido.';
    }
    return $errores;
}

// --- Documento ---
// $datos: los campos del formulario. $archivo: lo que viene en $_FILES['archivo'].
// $documento: objeto Documento (se usa para comprobar que existan la categoría y el paciente).
function validarDocumento($datos, $archivo, $documento) {
    $errores = [];

    // Título
    $largo = mb_strlen($datos['titulo']);
    if ($largo < 3 || $largo > 120) {
        $errores[] = 'El título es obligatorio y debe tener entre 3 y 120 caracteres.';
    }

    // Descripción (opcional)
    if (mb_strlen($datos['descripcion']) > 500) {
        $errores[] = 'La descripción no puede superar los 500 caracteres.';
    }

    // Categoría y paciente
    if (!ctype_digit($datos['id_categoria']) || !$documento->categoriaExiste($datos['id_categoria'])) {
        $errores[] = 'Seleccione una categoría válida.';
    }
    if (!ctype_digit($datos['id_paciente']) || !$documento->pacienteExiste($datos['id_paciente'])) {
        $errores[] = 'Seleccione un paciente válido.';
    }

    // Fecha con formato AAAA-MM-DD
    $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha_emision']);
    if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha_emision']) {
        $errores[] = 'La fecha de emisión no es válida.';
    }

    // Archivo (opcional)
    return array_merge($errores, validarArchivo($archivo));
}

// Revisa el archivo subido: tipo permitido y tamaño máximo.
function validarArchivo($archivo) {
    // Si no se eligió ningún archivo, no hay nada que validar.
    if (!$archivo || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return [];
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['No se pudo subir el archivo. Pruebe con otro.'];
    }

    $errores = [];
    if ($archivo['size'] > TAMANIO_MAXIMO_ARCHIVO) {
        $errores[] = 'El archivo no puede pesar más de 2 MB.';
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
        $errores[] = 'Solo se permiten archivos PDF, JPG o PNG.';
    } elseif (function_exists('finfo_open')) {
        // Además de la extensión, se mira el contenido real del archivo.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipo  = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);
        if (!in_array($tipo, ['application/pdf', 'image/jpeg', 'image/png'])) {
            $errores[] = 'El contenido del archivo no corresponde a un PDF, JPG o PNG.';
        }
    }
    return $errores;
}

// --- Encuesta ---
function validarEncuesta($calificacion, $comentario) {
    $errores = [];
    if (!in_array($calificacion, ['1', '2', '3', '4', '5'], true)) {
        $errores[] = 'Seleccione una calificación del 1 al 5.';
    }
    if (mb_strlen($comentario) > 300) {
        $errores[] = 'El comentario no puede superar los 300 caracteres.';
    }
    return $errores;
}
