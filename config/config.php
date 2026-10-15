<?php
// ============================================================
// Configuración general del sistema.
// (Los datos de la base de datos están en datos/conexion.php)
// ============================================================

// Dirección con la que el CELULAR puede abrir el sistema (se escribe dentro del QR).
// En la PC alcanza con localhost, pero el celular NO puede abrir "localhost".
// Para probar con el celular (misma red wifi) poner la IP de la PC, por ejemplo:
//   http://192.168.1.20/sigsm
define('URL_BASE', 'http://localhost/sigsm');

// Archivos que se suben desde el panel de administración
define('CARPETA_SUBIDAS', __DIR__ . '/../uploads/');
define('TAMANIO_MAXIMO_ARCHIVO', 2 * 1024 * 1024);   // 2 MB
