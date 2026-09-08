<?php
session_start();

require_once "Conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

 $email      = trim($_POST['email']      ?? '');
 $contrasenia = $_POST['contrasenia']    ?? '';

if (empty($email) || empty($contrasenia)) {
    header("Location: login.php?error=campos_vacios");
    exit;
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->getConexion();

    $sql = "SELECT id, nombre, email, contrasenia, rol, activo 
            FROM usuario 
            WHERE email = :email 
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario || (int)$usuario['activo'] !== 1) {
        header("Location: login.php?error=no_existe");
        exit;
    }

    if (password_verify($contrasenia, $usuario['contrasenia'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'     => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'email'  => $usuario['email'],
            'rol'    => $usuario['rol']
        ];

        if ($usuario['rol'] === 'admin') {
            header("Location: index.html");
        } else {
            header("Location: portal-paciente.html");
        }
        exit;
    } else {
        header("Location: login.php?error=contrasenia_incorrecta");
        exit;
    }

} catch (PDOException $e) {
    header("Location: login.php?error=servidor");
    exit;
}