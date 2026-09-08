<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$mensajeError = '';
$errores = [
    'campos_vacios' => 'Debe ingresar email y contraseña.',
    'no_existe' => 'El usuario no existe o está inactivo.',
    'contrasenia_incorrecta' => 'La contraseña ingresada es incorrecta.',
    'servidor' => 'Ocurrió un error en el servidor. Intente nuevamente.',
];
          
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $contrasenia = $_POST['contrasenia'] ?? '';

    if ($email === '' || $contrasenia === '') {
        $mensajeError = $errores['campos_vacios'];
    } else {
        try {
            require_once 'Conexion.php';

            $conexion = new Conexion();
            $pdo = $conexion->getConexion();
            $stmt = $pdo->prepare(
                'SELECT id, nombre, email, contrasenia, rol, activo
                 FROM usuario
                 WHERE email = :email
                 LIMIT 1'
            );
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            if (!$usuario || (int) $usuario['activo'] !== 1) {
                $mensajeError = $errores['no_existe'];
            } elseif (!password_verify($contrasenia, $usuario['contrasenia'])) {
                $mensajeError = $errores['contrasenia_incorrecta'];
            } else {
                session_regenerate_id(true);
                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email'],
                    'rol' => $usuario['rol'],
                ];

                header('Location: ' . ($usuario['rol'] === 'admin'
                    ? 'index.html'
                    : 'portal-paciente.html'));
                exit;
            }
        } catch (PDOException $e) {
            $mensajeError = $errores['servidor'];
        } 
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — SIGSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #F5F6F8; }
        .bg-hc { background-color: #1B3A5C; }
        .btn-hc {
            --bs-btn-bg: #2563EB;
            --bs-btn-border-color: #2563EB;
            --bs-btn-hover-bg: #1D4ED8;
            --bs-btn-hover-border-color: #1D4ED8;
            --bs-btn-active-bg: #1D4ED8;
            --bs-btn-active-border-color: #1D4ED8;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.15);
        }
    </style>
</head>
<body class="d-flex min-vh-100">

    <!-- Lado izquierdo -->
    <div class="bg-hc text-white d-none d-lg-flex flex-column justify-content-between p-5" style="width:420px; min-height:100vh;">
        <div>
            <p class="fw-semibold mb-0" style="font-size:15px;">Hospital de Clínicas</p>
            <div class="mt-4">
                <h1 class="fw-bold mb-2" style="font-size:26px;">S.I.G.S.M.</h1>
                <p class="opacity-70" style="font-size:14px; line-height:1.6;">Sistema Informático de Gestión de Servicios Médicos.</p>
            </div>
        </div>
        <footer class="mt-auto">
            <p class="opacity-30 mb-0" style="font-size:13px; line-height:1.5;">Departamento Técnico de Informática — 2026</p>
        </footer>
    </div>

    <!-- Lado derecho -->
    <div class="flex-grow-1 d-flex align-items-center justify-content-right p-5">
        <div style="width:100%; max-width:380px;">

            <p class="fw-semibold text-dark d-lg-none mb-0" style="font-size:15px;">Hospital de Clínicas</p>
            <p class="text-muted d-lg-none mb-4" style="font-size:12px;">S.I.G.S.M.</p>

            <h2 class="fw-semibold text-dark mb-1" style="font-size:20px;">Iniciar sesión</h2>
            <p class="text-muted mb-4" style="font-size:14px;">Ingrese sus credenciales para acceder al sistema</p>

            <form id="loginForm" method="POST" action="login.php" autocomplete="off" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-medium" style="font-size:14px;">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="funcionario@hc.edu.uy">
                    <div class="text-danger" style="font-size:12px;" id="emailError"></div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium" style="font-size:14px;">Contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="contrasenia" placeholder="••••••••">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass()" id="btnEye">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="text-danger" style="font-size:12px;" id="passwordError"></div>
                </div>
                <button type="submit" class="btn btn-hc btn-primary w-full fw-medium" id="btnSubmit" style="font-size:14px;">
                    Ingresar
                </button>
                <?php if ($mensajeError): ?>
                    <div class="text-danger text-center mt-2" style="font-size:12px;" id="serverError">
                        <?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
            </form>

            <hr class="my-4">
            <p class="text-center mb-0">
                <a href="portal-paciente.html" class="text-decoration-none" style="font-size:13px; color:#2563EB;">
                    ¿Es paciente? Acceda al portal del paciente <i class="bi bi-arrow-right"></i>
                </a>
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePass() {
        const p = document.getElementById('password');
        const i = document.querySelector('#btnEye i');
        if (p.type === 'password') { p.type = 'text'; i.className = 'bi bi-eye-slash'; }
        else { p.type = 'password'; i.className = 'bi bi-eye'; }
    }

    function showErr(id, msg) {
        const el = document.getElementById(id);
        el.textContent = msg;
        el.previousElementSibling.querySelector('input')?.classList.add('is-invalid');
    }
    function clearErr(id) {
        const el = document.getElementById(id);
        el.textContent = '';
        el.previousElementSibling?.querySelector('input')?.classList.remove('is-invalid');
    }

    document.getElementById('email').addEventListener('input', () => clearErr('emailError'));
    document.getElementById('password').addEventListener('input', () => clearErr('passwordError'));

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let ok = true;
        const email = document.getElementById('email').value.trim();
        const pass = document.getElementById('password').value;

        if (!email) { showErr('emailError', 'El correo es obligatorio'); ok = false; }
        else clearErr('emailError');

        if (!pass) { showErr('passwordError', 'La contraseña es obligatoria'); ok = false; }
        else clearErr('passwordError');

        if (!ok) return;

        this.querySelector('button[type="submit"]').textContent = 'Verificando...';
        this.querySelector('button[type="submit"]').disabled = true;
        this.submit();
    });
    </script>
</body>
</html>