<?php
// ============================================================
// Panel de gestión (solo administradores).
// Muestra el resumen, los documentos (alta, edición, baja, QR)
// y los resultados de la encuesta, todo leído de la base de datos.
// ============================================================
require_once __DIR__ . '/../negocio/sesion.php';
require_once __DIR__ . '/../datos/conexion.php';
require_once __DIR__ . '/../datos/Documento.php';
require_once __DIR__ . '/../datos/Encuesta.php';

exigirRol('admin');   // si no hay sesión va al login; si es paciente va a su portal

$pdo        = (new Conexion())->getConexion();
$modeloDoc  = new Documento($pdo);
$modeloEnc  = new Encuesta($pdo);

$documentos  = $modeloDoc->listar();
$categorias  = $modeloDoc->listarCategorias();
$pacientes   = $modeloDoc->listarPacientes();
$resumen     = $modeloEnc->resumen();
$comentarios = $modeloEnc->ultimosComentarios(8);

// Si el formulario de documento tuvo errores, guardar_documento.php dejó los errores y lo escrito
$erroresForm = $_SESSION['errores_form'] ?? [];
$datosForm   = $_SESSION['datos_form'] ?? null;
unset($_SESSION['errores_form'], $_SESSION['datos_form']);

// Sección que se abre al cargar (inicio, documentos o encuestas)
$seccionInicial = $_GET['sec'] ?? 'inicio';
if (!in_array($seccionInicial, ['inicio', 'documentos', 'encuestas'])) {
    $seccionInicial = 'inicio';
}
if ($erroresForm) {
    $seccionInicial = 'documentos';
}

// Iniciales para el circulito de arriba (ej: "Administrador SIGSM" -> "AD")
$iniciales = mb_strtoupper(mb_substr($_SESSION['usuario']['nombre'], 0, 2));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gestión — SIGSM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #F5F6F8; }
        .bg-hc { background-color: #1B3A5C; }
        .btn-hc {
            --bs-btn-bg: #2563EB; --bs-btn-border-color: #2563EB;
            --bs-btn-hover-bg: #1D4ED8; --bs-btn-hover-border-color: #1D4ED8;
        }
        .sidebar-link {
            color: #6B7280; font-size:14px; padding:8px 12px; border-radius:6px;
            display:flex; align-items:center; gap:8px; text-decoration:none; transition: background 0.15s;
        }
        .sidebar-link:hover { background-color:#F3F4F6; color:#374151; }
        .sidebar-link.active { background-color:#EFF6FF; color:#2563EB; font-weight:600; }
        .form-control:focus { border-color:#2563EB; box-shadow:0 0 0 0.2rem rgba(37,99,235,0.15); }
        .table th { font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6B7280; font-weight:500; }
        .table td { font-size:14px; vertical-align:middle; }
        .badge-pub { background-color:#ECFDF5; color:#047857; font-size:11px; font-weight:500; }
        .offcanvas-lg { width:220px !important; border-right:1px solid #E5E7EB !important; }
        .stat-card { transition: box-shadow 0.2s; }
        .stat-card:hover { box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    </style>
</head>
<body>

    <!-- Barra superior -->
    <nav class="navbar bg-hc text-white border-bottom px-3 sticky-top" style="z-index:1040;">
        <div class="container-fluid d-flex align-items-center">
            <button class="btn btn-sm text-white me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Abrir menú">
                <i class="bi bi-list" style="font-size:20px;"></i>
            </button>
            <span class="fw-semibold text-white me-3" style="font-size:14px;">Hospital de Clínicas</span>
            <span class="text-white-50 d-none d-sm-inline" style="font-size:13px;">S.I.G.S.M.</span>
            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-white d-flex align-items-center justify-content-center fw-semibold" style="width:30px;height:30px;font-size:12px;"><?= e($iniciales) ?></div>
                    <span class="d-none d-sm-inline text-white" style="font-size:13px;"><?= e($_SESSION['usuario']['nombre']) ?></span>
                </div>
                <a href="../negocio/logout.php" class="text-white text-decoration-none" style="font-size:13px;" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right" style="font-size:16px;"></i> <span class="d-none d-sm-inline">Salir</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="d-flex">

        <!-- Menú lateral -->
        <aside class="offcanvas offcanvas-start offcanvas-lg bg-white" id="sidebar" data-bs-backdrop="false" tabindex="-1">
            <div class="offcanvas-header d-lg-none border-bottom">
                <span class="fw-semibold" style="font-size:14px;">Menú</span>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column gap-1 p-3">
                <a href="#" class="sidebar-link" data-seccion="inicio" onclick="goTo('inicio',this);return false;" data-bs-dismiss="offcanvas">
                    <i class="bi bi-grid-1x2"></i> Inicio
                </a>
                <a href="#" class="sidebar-link" data-seccion="documentos" onclick="goTo('documentos',this);return false;" data-bs-dismiss="offcanvas">
                    <i class="bi bi-file-earmark-text"></i> Documentos
                </a>
                <a href="#" class="sidebar-link" data-seccion="encuestas" onclick="goTo('encuestas',this);return false;" data-bs-dismiss="offcanvas">
                    <i class="bi bi-clipboard-data"></i> Encuestas
                </a>
            </div>
        </aside>

        <main class="flex-grow-1 p-3 p-lg-4" style="max-width:960px;">

            <?php mostrarMensaje(); ?>

            <!-- ===== INICIO: resumen con datos reales ===== -->
            <section id="secInicio">
                <h5 class="fw-semibold text-dark mb-3">Resumen</h5>
                <div class="row g-3 mb-2">
                    <div class="col-6 col-lg-3">
                        <div class="card border stat-card">
                            <div class="card-body p-3">
                                <p class="text-muted mb-1" style="font-size:12px;">Documentos</p>
                                <p class="fw-bold text-dark mb-0" style="font-size:24px;"><?= count($documentos) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card border stat-card">
                            <div class="card-body p-3">
                                <p class="text-muted mb-1" style="font-size:12px;">Categorías</p>
                                <p class="fw-bold text-dark mb-0" style="font-size:24px;"><?= count($categorias) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card border stat-card">
                            <div class="card-body p-3">
                                <p class="text-muted mb-1" style="font-size:12px;">Pacientes</p>
                                <p class="fw-bold text-dark mb-0" style="font-size:24px;"><?= count($pacientes) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card border stat-card">
                            <div class="card-body p-3">
                                <p class="text-muted mb-1" style="font-size:12px;">Respuestas de encuesta</p>
                                <p class="fw-bold text-primary mb-0" style="font-size:24px;"><?= e($resumen['total']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== DOCUMENTOS ===== -->
            <section id="secDocumentos" class="d-none">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                    <h5 class="fw-semibold text-dark mb-0">Documentos</h5>
                    <div class="d-flex flex-wrap gap-2 w-100 w-sm-auto justify-content-sm-end">
                        <input type="text" class="form-control form-control-sm" placeholder="Buscar..." id="searchDocs" oninput="filterTable()" style="max-width:200px;">
                        <select class="form-select form-select-sm" id="filtroCategoria" onchange="filterTable()" style="max-width:180px;">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= e($c['id']) ?>"><?= e($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-hc btn-primary btn-sm fw-medium text-nowrap" onclick="abrirFormDoc()">
                            <i class="bi bi-plus-lg me-1"></i> Agregar
                        </button>
                    </div>
                </div>
                <div class="card border">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Documento</th>
                                    <th class="d-none d-md-table-cell">Categoría</th>
                                    <th class="d-none d-lg-table-cell">Paciente</th>
                                    <th class="d-none d-lg-table-cell">Fecha</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="docsBody">
                            <?php foreach ($documentos as $d): ?>
                                <?php
                                $fecha  = date('d/m/Y', strtotime($d['fecha_emision']));
                                $urlDoc = URL_BASE . '/presentacion/ver_documento_mobile.php?t=' . $d['token'];
                                $busqueda = mb_strtolower($d['titulo'] . ' ' . $d['categoria'] . ' ' . $d['nombre_paciente'] . ' ' . $d['ci']);

                                // Ícono según el tipo de archivo adjunto
                                $extension = strtolower(pathinfo((string) $d['archivo'], PATHINFO_EXTENSION));
                                if ($extension === 'pdf')                          { $icono = 'bi-file-earmark-pdf text-danger'; }
                                elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) { $icono = 'bi-file-earmark-image text-primary'; }
                                else                                               { $icono = 'bi-file-earmark-text text-secondary'; }
                                ?>
                                <tr class="doc-row" data-s="<?= e($busqueda) ?>" data-cat="<?= e($d['id_categoria']) ?>">
                                    <td><span class="me-1"><i class="bi <?= $icono ?>"></i></span> <?= e($d['titulo']) ?></td>
                                    <td class="d-none d-md-table-cell text-secondary"><?= e($d['categoria']) ?></td>
                                    <td class="d-none d-lg-table-cell text-secondary"><?= e($d['nombre_paciente']) ?></td>
                                    <td class="d-none d-lg-table-cell text-muted"><?= e($fecha) ?></td>
                                    <td class="text-end text-nowrap">
                                        <!-- Ver (con QR) -->
                                        <button class="btn btn-sm btn-outline-secondary border-0" title="Ver"
                                            data-titulo="<?= e($d['titulo']) ?>"
                                            data-categoria="<?= e($d['categoria']) ?>"
                                            data-paciente="<?= e($d['nombre_paciente']) ?> (<?= e($d['ci']) ?>)"
                                            data-fecha="<?= e($fecha) ?>"
                                            data-descripcion="<?= e($d['descripcion']) ?>"
                                            data-archivo="<?= e($d['archivo']) ?>"
                                            data-url="<?= e($urlDoc) ?>"
                                            onclick="verDoc(this)"><i class="bi bi-eye"></i></button>
                                        <!-- Editar -->
                                        <button class="btn btn-sm btn-outline-secondary border-0" title="Editar"
                                            data-id="<?= e($d['id']) ?>"
                                            data-titulo="<?= e($d['titulo']) ?>"
                                            data-id_categoria="<?= e($d['id_categoria']) ?>"
                                            data-id_paciente="<?= e($d['id_paciente']) ?>"
                                            data-fecha_emision="<?= e($d['fecha_emision']) ?>"
                                            data-descripcion="<?= e($d['descripcion']) ?>"
                                            data-archivo="<?= e($d['archivo']) ?>"
                                            onclick="editarDoc(this)"><i class="bi bi-pencil"></i></button>
                                        <!-- Eliminar (pide confirmación) -->
                                        <button class="btn btn-sm btn-outline-secondary border-0 text-danger" title="Eliminar"
                                            data-id="<?= e($d['id']) ?>"
                                            data-titulo="<?= e($d['titulo']) ?>"
                                            onclick="confirmarBaja(this)"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="noDocs" class="text-center text-muted py-4 <?= $documentos ? 'd-none' : '' ?>" style="font-size:14px;">No se encontraron documentos.</div>
                </div>
            </section>

            <!-- ===== ENCUESTAS: resultados reales ===== -->
            <section id="secEncuestas" class="d-none">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold text-dark mb-0">Encuesta de satisfacción</h5>
                    <a href="encuesta.php" target="_blank" class="btn btn-sm btn-outline-primary">Abrir formulario</a>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="card border h-100">
                            <div class="card-body p-4 text-center">
                                <p class="fw-bold text-dark mb-0" style="font-size:36px;"><?= e($resumen['promedio']) ?></p>
                                <p class="text-muted mb-1" style="font-size:13px;">Promedio (de 5)</p>
                                <span class="badge badge-pub"><?= e($resumen['total']) ?> respuestas — anónimas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="card border h-100">
                            <div class="card-body p-4">
                                <p class="text-muted mb-3" style="font-size:12px;">Respuestas por calificación</p>
                                <?php for ($nota = 5; $nota >= 1; $nota--): ?>
                                    <?php
                                    $cantidad   = $resumen['por_nota'][$nota];
                                    $porcentaje = $resumen['total'] > 0 ? round($cantidad * 100 / $resumen['total']) : 0;
                                    ?>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="text-dark" style="font-size:13px;width:28px;"><?= $nota ?> ★</span>
                                        <div class="progress flex-grow-1" style="height:8px;"><div class="progress-bar bg-primary" style="width:<?= $porcentaje ?>%"></div></div>
                                        <span class="text-dark fw-medium" style="font-size:13px;width:28px;text-align:right;"><?= $cantidad ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border">
                    <div class="card-body p-4">
                        <p class="text-muted mb-2" style="font-size:12px;">Últimos comentarios</p>
                        <?php if (!$comentarios): ?>
                            <p class="text-muted mb-0" style="font-size:14px;">Todavía no hay comentarios.</p>
                        <?php endif; ?>
                        <?php foreach ($comentarios as $c): ?>
                            <div class="border-bottom py-2" style="font-size:14px;">
                                <strong><?= e($c['calificacion']) ?> ★</strong> — <?= e($c['comentarios']) ?>
                                <span class="text-muted d-block" style="font-size:12px;"><?= e(date('d/m/Y H:i', strtotime($c['fecha']))) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- ===== Ventana: VER documento (con QR) ===== -->
    <div class="modal fade" id="modalVer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-semibold" id="verTitulo"></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6"><p class="text-muted mb-0" style="font-size:11px;text-transform:uppercase;">Categoría</p><p class="mb-0" style="font-size:14px;" id="verCategoria"></p></div>
                        <div class="col-6"><p class="text-muted mb-0" style="font-size:11px;text-transform:uppercase;">Fecha</p><p class="mb-0" style="font-size:14px;" id="verFecha"></p></div>
                        <div class="col-12"><p class="text-muted mb-0" style="font-size:11px;text-transform:uppercase;">Paciente</p><p class="mb-0" style="font-size:14px;" id="verPaciente"></p></div>
                    </div>
                    <p class="text-muted mb-0" style="font-size:11px;text-transform:uppercase;">Descripción</p>
                    <p class="text-secondary" style="font-size:14px;" id="verDescripcion"></p>
                    <a id="verArchivo" href="#" target="_blank" class="btn btn-sm btn-outline-primary d-none mb-3"><i class="bi bi-paperclip"></i> Abrir archivo adjunto</a>

                    <div class="text-center border-top pt-3">
                        <p class="text-muted mb-2" style="font-size:12px;">Código QR para abrir el documento desde el celular</p>
                        <div id="verQR" class="d-flex justify-content-center mb-2"></div>
                        <a id="verEnlace" href="#" target="_blank" class="small text-break">Abrir vista del celular</a>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Ventana: AGREGAR / EDITAR documento ===== -->
    <div class="modal fade" id="modalDoc" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-semibold" id="docTituloVentana">Agregar documento</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDoc" action="../negocio/guardar_documento.php" method="POST" enctype="multipart/form-data" novalidate>
                    <div class="modal-body">

                        <!-- Errores que vienen del servidor (PHP) -->
                        <?php if ($erroresForm): ?>
                            <div class="alert alert-danger" style="font-size:13px;">
                                <strong>Revise los datos:</strong>
                                <ul class="mb-0">
                                    <?php foreach ($erroresForm as $error): ?>
                                        <li><?= e($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <!-- Errores que detecta JavaScript antes de enviar -->
                        <div id="erroresDocJs" class="alert alert-danger d-none" style="font-size:13px;"></div>

                        <input type="hidden" name="id" id="docId">

                        <div class="mb-3">
                            <label class="form-label fw-medium" style="font-size:14px;">Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" id="docTitulo" class="form-control form-control-sm" maxlength="120">
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-3">
                                <label class="form-label fw-medium" style="font-size:14px;">Categoría <span class="text-danger">*</span></label>
                                <select name="id_categoria" id="docCategoria" class="form-select form-select-sm">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= e($c['id']) ?>"><?= e($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <label class="form-label fw-medium" style="font-size:14px;">Paciente <span class="text-danger">*</span></label>
                                <select name="id_paciente" id="docPaciente" class="form-select form-select-sm">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($pacientes as $p): ?>
                                        <option value="<?= e($p['id']) ?>"><?= e($p['nombre']) ?> (<?= e($p['ci']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium" style="font-size:14px;">Fecha de emisión <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_emision" id="docFecha" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium" style="font-size:14px;">Descripción</label>
                            <textarea name="descripcion" id="docDescripcion" rows="2" maxlength="500" class="form-control form-control-sm"></textarea>
                        </div>
                        <div>
                            <label class="form-label fw-medium" style="font-size:14px;">Archivo (PDF, JPG o PNG, máximo 2 MB)</label>
                            <input type="file" name="archivo" id="docArchivo" accept=".pdf,.jpg,.jpeg,.png" class="form-control form-control-sm">
                            <div class="form-text" id="docArchivoActual"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-hc btn-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== Ventana: confirmar ELIMINAR ===== -->
    <div class="modal fade" id="modalBaja" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-semibold">Eliminar documento</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../negocio/eliminar_documento.php" method="POST">
                    <div class="modal-body text-center py-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size:40px;"></i>
                        <p class="mt-3 mb-0" style="font-size:14px;">¿Está seguro de eliminar <strong id="bajaTitulo"></strong>?</p>
                        <input type="hidden" name="id" id="bajaId">
                    </div>
                    <div class="modal-footer border-top justify-content-center">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
    // ----- Cambio de sección del menú lateral -----
    const sections = { inicio:'secInicio', documentos:'secDocumentos', encuestas:'secEncuestas' };

    function goTo(seccion, linkEl) {
        Object.values(sections).forEach(id => document.getElementById(id).classList.add('d-none'));
        document.getElementById(sections[seccion]).classList.remove('d-none');
        document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
        if (linkEl) linkEl.classList.add('active');
    }

    // ----- Filtro de la tabla (por texto y por categoría) -----
    function filterTable() {
        const q   = document.getElementById('searchDocs').value.toLowerCase();
        const cat = document.getElementById('filtroCategoria').value;
        let hayAlguno = false;
        document.querySelectorAll('.doc-row').forEach(fila => {
            const coincideTexto = (fila.getAttribute('data-s') || '').includes(q);
            const coincideCat   = cat === '' || fila.getAttribute('data-cat') === cat;
            const mostrar = coincideTexto && coincideCat;
            fila.style.display = mostrar ? '' : 'none';
            if (mostrar) hayAlguno = true;
        });
        document.getElementById('noDocs').classList.toggle('d-none', hayAlguno);
    }

    // Abre una ventana (modal) de Bootstrap por su id
    function abrirVentana(id) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
    }

    // ----- VER documento -----
    // Los datos vienen de los atributos data-* del botón. Se muestran con textContent
    // (no con innerHTML) para que nadie pueda meter código HTML/JavaScript en un título.
    function verDoc(boton) {
        document.getElementById('verTitulo').textContent      = boton.dataset.titulo;
        document.getElementById('verCategoria').textContent   = boton.dataset.categoria;
        document.getElementById('verFecha').textContent       = boton.dataset.fecha;
        document.getElementById('verPaciente').textContent    = boton.dataset.paciente;
        document.getElementById('verDescripcion').textContent = boton.dataset.descripcion || 'Sin descripción.';

        const enlaceArchivo = document.getElementById('verArchivo');
        if (boton.dataset.archivo) {
            enlaceArchivo.href = 'uploads/' + encodeURIComponent(boton.dataset.archivo);
            enlaceArchivo.classList.remove('d-none');
        } else {
            enlaceArchivo.classList.add('d-none');
        }

        // Dibuja el QR con la dirección del documento
        const contenedorQR = document.getElementById('verQR');
        contenedorQR.innerHTML = '';
        new QRCode(contenedorQR, { text: boton.dataset.url, width: 180, height: 180 });
        document.getElementById('verEnlace').href = boton.dataset.url;

        abrirVentana('modalVer');
    }

    // ----- AGREGAR / EDITAR documento (usan la misma ventana) -----
    function llenarFormDoc(datos) {
        document.getElementById('docId').value          = datos.id || '';
        document.getElementById('docTitulo').value      = datos.titulo || '';
        document.getElementById('docCategoria').value   = datos.id_categoria || '';
        document.getElementById('docPaciente').value    = datos.id_paciente || '';
        document.getElementById('docFecha').value       = datos.fecha_emision || '';
        document.getElementById('docDescripcion').value = datos.descripcion || '';
        document.getElementById('docArchivo').value     = '';
        document.getElementById('erroresDocJs').classList.add('d-none');
        document.getElementById('docArchivoActual').textContent =
            datos.archivo ? 'Archivo actual: ' + datos.archivo + '. Si sube otro, lo reemplaza.' : '';
    }

    function abrirFormDoc() {
        document.getElementById('docTituloVentana').textContent = 'Agregar documento';
        llenarFormDoc({ fecha_emision: new Date().toISOString().slice(0, 10) });
        abrirVentana('modalDoc');
    }

    function editarDoc(boton) {
        document.getElementById('docTituloVentana').textContent = 'Editar documento';
        llenarFormDoc(boton.dataset);
        abrirVentana('modalDoc');
    }

    // Validación en JavaScript (antes de enviar). PHP vuelve a validar todo del lado del servidor.
    document.getElementById('formDoc').addEventListener('submit', function (ev) {
        const errores = [];
        const titulo  = this.titulo.value.trim();
        const archivo = this.archivo.files[0];

        if (titulo.length < 3 || titulo.length > 120) errores.push('El título debe tener entre 3 y 120 caracteres.');
        if (!this.id_categoria.value) errores.push('Seleccione una categoría.');
        if (!this.id_paciente.value)  errores.push('Seleccione un paciente.');
        if (!this.fecha_emision.value) errores.push('Ingrese la fecha de emisión.');
        if (this.descripcion.value.length > 500) errores.push('La descripción no puede superar los 500 caracteres.');
        if (archivo) {
            if (!/\.(pdf|jpe?g|png)$/i.test(archivo.name)) errores.push('El archivo debe ser PDF, JPG o PNG.');
            if (archivo.size > 2 * 1024 * 1024) errores.push('El archivo no puede pesar más de 2 MB.');
        }

        const caja = document.getElementById('erroresDocJs');
        if (errores.length > 0) {
            ev.preventDefault();   // no se envía el formulario
            caja.innerHTML = '';
            const lista = document.createElement('ul');
            lista.className = 'mb-0';
            errores.forEach(texto => { const li = document.createElement('li'); li.textContent = texto; lista.appendChild(li); });
            caja.appendChild(lista);
            caja.classList.remove('d-none');
        }
    });

    // ----- ELIMINAR documento (pide confirmación) -----
    function confirmarBaja(boton) {
        document.getElementById('bajaTitulo').textContent = '"' + boton.dataset.titulo + '"';
        document.getElementById('bajaId').value = boton.dataset.id;
        abrirVentana('modalBaja');
    }

    // ----- Al cargar la página -----
    goTo('<?= $seccionInicial ?>', document.querySelector('.sidebar-link[data-seccion="<?= $seccionInicial ?>"]'));

    <?php if ($erroresForm): ?>
    // El formulario tuvo errores en el servidor: se vuelve a abrir con lo que el usuario había escrito
    document.getElementById('docTituloVentana').textContent =
        <?= json_encode(!empty($datosForm['id']) ? 'Editar documento' : 'Agregar documento') ?>;
    llenarFormDoc(<?= json_encode($datosForm ?: new stdClass(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
    abrirVentana('modalDoc');
    <?php endif; ?>
    </script>
</body>
</html>
