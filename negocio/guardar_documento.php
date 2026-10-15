<?php
// ============================================================
// Alta y edición de documentos.
// Recibe el formulario de la ventana "Agregar / Editar" de index.php.
// Si viene un "id" edita el documento; si no, crea uno nuevo.
// ============================================================

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/validaciones.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../datos/conexion.php';
require_once __DIR__ . '/../datos/Documento.php';

exigirRol('admin');   // solo el administrador puede entrar

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../presentacion/index.php?sec=documentos');
}

// --- 1) Leer lo que mandó el formulario ---
$id = trim($_POST['id'] ?? '');
$datos = [
    'id'            => $id,
    'titulo'        => trim($_POST['titulo'] ?? ''),
    'descripcion'   => trim($_POST['descripcion'] ?? ''),
    'id_categoria'  => trim($_POST['id_categoria'] ?? ''),
    'id_paciente'   => trim($_POST['id_paciente'] ?? ''),
    'fecha_emision' => trim($_POST['fecha_emision'] ?? ''),
];
$archivo = $_FILES['archivo'] ?? null;

try {
    $pdo       = (new Conexion())->getConexion();
    $documento = new Documento($pdo);

    // Si es una edición, comprobar que el documento exista
    $existente = false;
    if ($id !== '') {
        $existente = ctype_digit($id) ? $documento->buscarPorId($id) : false;
        if (!$existente) {
            guardarMensaje('El documento que quiere editar no existe.', 'danger');
            redirigir('../presentacion/index.php?sec=documentos');
        }
    }

    // --- 2) Validar en PHP ---
    $errores = validarDocumento($datos, $archivo, $documento);
    if ($errores) {
        // Se guardan los errores y lo escrito; index.php vuelve a abrir el formulario con ellos
        $_SESSION['errores_form'] = $errores;
        $_SESSION['datos_form']   = $datos;
        redirigir('../presentacion/index.php?sec=documentos');
    }

    // --- 3) Subir el archivo (si se eligió uno) ---
    $nombreArchivo = $existente ? $existente['archivo'] : null;   // por defecto se mantiene el anterior
    $archivoViejo  = null;

    if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
        $extension   = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $nuevoNombre = bin2hex(random_bytes(8)) . '.' . $extension;   // nombre al azar

        if (!move_uploaded_file($archivo['tmp_name'], CARPETA_SUBIDAS . $nuevoNombre)) {
            $_SESSION['errores_form'] = ['No se pudo guardar el archivo en el servidor.'];
            $_SESSION['datos_form']   = $datos;
            redirigir('../presentacion/index.php?sec=documentos');
        }
        $archivoViejo  = $nombreArchivo;
        $nombreArchivo = $nuevoNombre;
    }

    // --- 4) Guardar en la base de datos ---
    $documento->id            = $existente ? $existente['id'] : null;
    $documento->titulo        = $datos['titulo'];
    $documento->descripcion   = $datos['descripcion'] !== '' ? $datos['descripcion'] : null;
    $documento->archivo       = $nombreArchivo;
    $documento->fecha_emision = $datos['fecha_emision'];
    $documento->id_categoria  = $datos['id_categoria'];
    $documento->id_paciente   = $datos['id_paciente'];
    $documento->guardar();

    // Si se reemplazó el archivo, se borra el anterior
    if ($archivoViejo && is_file(CARPETA_SUBIDAS . basename($archivoViejo))) {
        unlink(CARPETA_SUBIDAS . basename($archivoViejo));
    }

    guardarMensaje($existente ? 'Documento actualizado correctamente.' : 'Documento creado correctamente.');
    redirigir('../presentacion/index.php?sec=documentos');

} catch (PDOException $e) {
    error_log('Error al guardar el documento: ' . $e->getMessage());
    $_SESSION['errores_form'] = ['Ocurrió un error al guardar en la base de datos.'];
    $_SESSION['datos_form']   = $datos;
    redirigir('../presentacion/index.php?sec=documentos');
}
