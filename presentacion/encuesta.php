<?php
// ============================================================
// Encuesta de satisfacción ANÓNIMA.
// Es pública (no hace falta iniciar sesión) y no guarda quién la responde.
// ============================================================
require_once __DIR__ . '/../negocio/sesion.php';

// Mensaje que dejó negocio/guardar_encuesta.php (si hay)
$mensaje = '';
$tipoMensaje = 'success';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje']['texto'];
    $tipoMensaje = $_SESSION['mensaje']['tipo'] === 'danger' ? 'danger' : 'success';
    unset($_SESSION['mensaje']);   // para que no se repita al recargar
}

// A dónde vuelve el botón "Volver": a su página si hay sesión, al login si no.
$paginaVolver = hayUsuarioLogueado() ? paginaDeInicio($_SESSION['usuario']['rol']) : 'login.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta de Satisfacción — SIGSM</title>
    <style>
        body { font-family: sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #0284c7; font-size: 1.2rem; }
        label { display: block; margin-top: 10px; font-size: 0.9rem; color: #333; }
        select, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
        button { background: #0284c7; color: white; border: none; padding: 10px; width: 100%; margin-top: 15px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .msg { margin-top: 15px; font-size: 0.9rem; text-align: center; }
        .msg.success { color: green; }
        .msg.danger { color: #b91c1c; }
        .volver { display: block; text-align: center; margin-top: 15px; font-size: 0.9rem; color: #0284c7; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>Encuesta de Satisfacción</h2>
    <form action="../negocio/guardar_encuesta.php" method="POST" id="formEncuesta">
        <label for="calificacion">¿Cómo calificarías la atención recibida?</label>
        <select name="calificacion" id="calificacion" required>
            <option value="">Selecciona una opción</option>
            <option value="5"> Excelente</option>
            <option value="4">Muy Bueno</option>
            <option value="3">Bueno</option>
            <option value="2">Regular</option>
            <option value="1">Malo</option>
        </select>

        <label for="comentarios">Comentarios adicionales (Opcional y anónimo):</label>
        <textarea name="comentarios" id="comentarios" rows="4" maxlength="300"></textarea>

        <button type="submit">Enviar Opinión</button>
    </form>

    <?php if ($mensaje): ?>
        <div class="msg <?= e($tipoMensaje) ?>"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <a class="volver" href="<?= e($paginaVolver) ?>">Volver</a>
</div>

<script>
// Validación en JavaScript (PHP vuelve a validar al recibir los datos)
document.getElementById('formEncuesta').addEventListener('submit', function (ev) {
    if (!this.calificacion.value) {
        ev.preventDefault();
        alert('Por favor, selecciona una calificación.');
    }
});
</script>
</body>
</html>
