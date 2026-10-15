<?php
// ============================================================
// Portal del paciente.
// El paciente solo ve SUS documentos (agrupados por categoría)
// y puede abrir el código QR de cada uno para verlo desde el celular.
// ============================================================
require_once __DIR__ . '/../negocio/sesion.php';
require_once __DIR__ . '/../datos/conexion.php';
require_once __DIR__ . '/../datos/Documento.php';

exigirRol('paciente');   // solo pacientes; el admin va a index.php y sin sesión se va al login

$modeloDoc  = new Documento((new Conexion())->getConexion());
$documentos = $modeloDoc->listar($_SESSION['usuario']['id_paciente']);

// Se agrupan los documentos por categoría: ['Cardiología' => [doc1, doc2], ...]
$porCategoria = [];
foreach ($documentos as $d) {
    $porCategoria[$d['categoria']][] = $d;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Paciente — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        .bg-hc { background-color: #1B3A5C; }
        .btn-hc {
            --bs-btn-bg: #2563EB; --bs-btn-border-color: #2563EB; --bs-btn-color: #fff;
            --bs-btn-hover-bg: #1D4ED8; --bs-btn-hover-border-color: #1D4ED8; --bs-btn-hover-color: #fff;
        }
        .panel-section { display: none; }
        .panel-section.show { display: block; }
        .doc-item { padding: 6px 4px; border-bottom: 1px solid #F3F4F6; }
        .doc-item:last-child { border-bottom: none; }
        .doc-btn { font-size: 13px; padding: 4px 10px; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100" style="background:#F5F6F8;">

    <nav class="navbar navbar-expand-sm bg-hc text-white px-3" aria-label="Navegación principal">
        <div class="container-fluid px-0" style="max-width:820px;">
            <a class="navbar-brand text-white me-4" href="portal-paciente.php">
                <span class="fw-semibold d-block" style="font-size:15px;">Hospital de Clínicas</span>
                <span class="opacity-50 d-block" style="font-size:11px;">Portal del Paciente</span>
            </a>
            <button class="navbar-toggler border-light" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Abrir menú">
                <i class="bi bi-list text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <div class="navbar-nav ms-auto align-items-sm-center gap-sm-2">
                    <span class="navbar-text text-white-50" style="font-size:13px;"><?= e($_SESSION['usuario']['nombre']) ?></span>
                    <a class="nav-link text-white" href="../negocio/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Salir</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 px-3 py-4" style="max-width:820px; margin:0 auto; width:100%;">

        <?php mostrarMensaje(); ?>

        <h5 class="fw-semibold text-dark mb-1">Mis documentos</h5>
        <p class="text-secondary mb-4" style="font-size:14px;">Tus documentos médicos. Con el código QR podés abrirlos desde el celular.</p>

        <ul class="nav nav-tabs border-bottom mb-4" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link active text-primary fw-medium" href="#" onclick="switchTab('docs',this);return false;" role="tab"><i class="bi bi-file-earmark-text me-1"></i>Documentos</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link text-muted" href="#" onclick="switchTab('enc',this);return false;" role="tab"><i class="bi bi-clipboard-data me-1"></i>Encuesta</a></li>
        </ul>

        <!-- ===== Pestaña DOCUMENTOS ===== -->
        <div id="panelDocs" class="panel-section show" role="tabpanel">
            <input type="text" class="form-control mb-3" placeholder="Buscar documento o categoría..." id="searchInput" oninput="filterDocs()" style="font-size:15px;padding:10px 14px;">

            <div id="deptList" class="d-flex flex-column gap-2">
                <?php $numero = 0; ?>
                <?php foreach ($porCategoria as $categoria => $lista): ?>
                    <?php $numero++; ?>
                    <div class="dept-block">
                        <button class="btn w-100 text-start d-flex justify-content-between align-items-center py-2 px-3 shadow-sm bg-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#cat<?= $numero ?>" style="font-size:15px;">
                            <span><i class="bi bi-folder2-open text-primary me-2"></i><?= e($categoria) ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size:11px;"><?= count($lista) ?></span>
                            </span>
                            <i class="bi bi-chevron-down text-muted" style="font-size:12px;"></i>
                        </button>
                        <div class="collapse <?= $numero === 1 ? 'show' : '' ?>" id="cat<?= $numero ?>">
                            <div class="ps-3 pe-1 py-2">
                                <?php foreach ($lista as $d): ?>
                                    <?php
                                    $urlDoc = URL_BASE . '/presentacion/ver_documento_mobile.php?t=' . $d['token'];
                                    $fecha  = date('d/m/Y', strtotime($d['fecha_emision']));
                                    ?>
                                    <div class="doc-item d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2"
                                         data-s="<?= e(mb_strtolower($d['titulo'] . ' ' . $categoria)) ?>">
                                        <div class="d-flex align-items-center gap-2" style="min-width:0;">
                                            <i class="bi bi-file-earmark-text text-danger flex-shrink-0" style="font-size:18px;"></i>
                                            <div style="min-width:0;">
                                                <span class="d-block text-truncate" style="font-size:14px;"><?= e($d['titulo']) ?></span>
                                                <span class="text-muted" style="font-size:12px;">Emitido el <?= e($fecha) ?></span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-shrink-0">
                                            <a class="btn btn-outline-primary doc-btn" href="ver_documento_mobile.php?t=<?= e($d['token']) ?>"><i class="bi bi-eye me-1"></i>Ver</a>
                                            <button type="button" class="btn btn-outline-secondary doc-btn"
                                                    data-url="<?= e($urlDoc) ?>" data-titulo="<?= e($d['titulo']) ?>"
                                                    onclick="verQR(this)"><i class="bi bi-qr-code me-1"></i>QR</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div id="noResults" class="text-center text-muted py-4 <?= $documentos ? 'd-none' : '' ?>" style="font-size:14px;">
                    <?= $documentos ? 'No se encontraron documentos.' : 'Todavía no tenés documentos cargados.' ?>
                </div>
            </div>
        </div>

        <!-- ===== Pestaña ENCUESTA ===== -->
        <div id="panelEnc" class="panel-section" role="tabpanel">
            <p class="text-secondary mb-3" style="font-size:14px;">La encuesta es anónima y voluntaria.</p>
            <div class="card border shadow-sm"><div class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-emoji-smile text-primary" style="font-size:18px;"></i>
                        <span class="fw-semibold" style="font-size:15px;">Encuesta de satisfacción</span>
                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size:11px;">Disponible</span>
                    </div>
                    <p class="text-secondary mb-0" style="font-size:13px;">Una pregunta y un comentario opcional — 1 min</p>
                </div>
                <a class="btn btn-hc flex-shrink-0" style="padding:10px 24px;font-size:15px;" href="encuesta.php">Responder encuesta</a>
            </div></div>
        </div>

    </main>

    <footer class="border-top mt-auto py-3 px-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-1" style="max-width:820px;margin:0 auto;">
            <span class="text-muted" style="font-size:12px;">Hospital de Clínicas — DTI</span>
            <span class="text-muted" style="font-size:12px;">S.I.G.S.M.</span>
        </div>
    </footer>

    <!-- Ventana con el código QR -->
    <div class="modal fade" id="modalQR" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center">
                <div class="modal-header">
                    <h6 class="modal-title" id="tituloQR">Código QR</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="qrCode" class="d-flex justify-content-center mb-3"></div>
                    <p class="small text-muted mb-1">Escaneá el código con el celular o abrí el enlace:</p>
                    <a id="enlaceQR" href="#" target="_blank" class="small text-break">Abrir documento</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
