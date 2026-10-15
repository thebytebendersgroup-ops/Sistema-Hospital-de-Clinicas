<?php
// ============================================================
// CAPA DE NEGOCIO - Procesa el formulario de login (presentacion/login.php)
// Comprueba el email y la contraseña y guarda al usuario en la sesión.
// ============================================================

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../datos/conexion.php';
require_once __DIR__ . '/../datos/Usuario.php';

// Solo se puede llegar acá enviando el formulario (POST).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('../presentacion/login.php');
}

$email       = trim($_POST['email'] ?? '');
$contrasenia = $_POST['contrasenia'] ?? '';

// 1) Validar los datos
if ($email === '' || $contrasenia === '') {
    redirigir('../presentacion/login.php?error=campos_vacios');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirigir('../presentacion/login.php?error=email_invalido');
}

// 2) Comprobar usuario y contraseña (la consulta preparada está en datos/Usuario.php)
try {
    $usuario = new Usuario((new Conexion())->getConexion());

    // Si el usuario no existe o la contraseña está mal se da el MISMO mensaje,
    // así nadie puede descubrir qué emails están registrados.
    if (!$usuario->iniciarSesion($email, $contrasenia)) {
        redirigir('../presentacion/login.php?error=credenciales');
    }

    // 3) Login correcto: se crea una sesión nueva (evita el robo de sesión)
    session_regenerate_id(true);

    // Se guardan en la sesión los datos que se necesitan en las otras páginas
    $_SESSION['usuario'] = [
        'id'          => $usuario->id,
        'nombre'      => $usuario->nombre,
        'email'       => $usuario->email,
        'rol'         => $usuario->rol,
        'id_paciente' => $usuario->buscarIdPaciente(),   // null si es administrador
    ];

    // 4) Cada rol va a su página
    redirigir(paginaDeInicio($usuario->rol));

} catch (PDOException $e) {
    error_log('Error en el login: ' . $e->getMessage());   // el detalle va al log, no a la pantalla
    redirigir('../presentacion/login.php?error=servidor');
}
