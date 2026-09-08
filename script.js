/* ====== VARIABLES GLOBALES ====== */
let mInst = null;
const panels = { docs: 'panelDocs', enc: 'panelEnc', turnos: 'panelTurnos', farmacia: 'panelFarmacia', qr: 'panelQr' };
var modal;

function buscar(id) {
  return document.getElementById(id);
}

function abrirModal(contenido) {
  buscar('modalContent').innerHTML = contenido;
  if (modal) modal.hide();
  modal = new bootstrap.Modal(buscar('mainModal'));
  modal.show();
}

function mostrarMensaje(texto, tipo, tiempo) {
  var caja = document.createElement('div');
  caja.className = 'position-fixed bottom-0 end-0 p-3';
  caja.style.zIndex = '9999';
  caja.innerHTML = '<div class="toast show text-bg-' + tipo + ' border-0" role="alert">' +
    '<div class="d-flex"><div class="toast-body">' + texto + '</div>' +
    '<button type="button" class="btn-close btn-close-white me-2 m-auto"></button></div></div>';
  document.body.appendChild(caja);
  caja.querySelector('button').onclick = function () { caja.remove(); };
  setTimeout(function () { caja.remove(); }, tiempo || 3000);
}

var paneles = {
  docs: 'panelDocs',
  enc: 'panelEnc',
  turnos: 'panelTurnos',
  farmacia: 'panelFarmacia',
  qr: 'panelQr'
};

function switchTab(nombre, enlace) {
  var secciones = document.querySelectorAll('.panel-section');
  var enlaces = document.querySelectorAll('#mainTabs .nav-link');
  var i;

  for (i = 0; i < secciones.length; i++) {
    secciones[i].style.display = 'none';
    secciones[i].classList.remove('show');
  }
  buscar(paneles[nombre]).style.display = 'block';
  buscar(paneles[nombre]).classList.add('show');

  for (i = 0; i < enlaces.length; i++) {
    enlaces[i].classList.remove('active', 'text-primary', 'fw-medium');
    enlaces[i].classList.add('text-muted');
  }
  if (enlace) {
    enlace.classList.add('active', 'text-primary', 'fw-medium');
    enlace.classList.remove('text-muted');
  }
}

document.addEventListener('DOMContentLoaded', function () {
  switchTab('docs', document.querySelector('#mainTabs .nav-link.active'));
});

function toggleDept(boton) {
  var cuerpo = boton.nextElementSibling;
  var flecha = boton.querySelector('.chev');
  var bloques = document.querySelectorAll('.dept-block');
  var i;

  for (i = 0; i < bloques.length; i++) {
    if (bloques[i] !== boton.parentElement) {
      bloques[i].querySelector('.dept-body').classList.remove('open');
      bloques[i].querySelector('.chev').classList.remove('rot');
    }
  }
  cuerpo.classList.toggle('open');
  flecha.classList.toggle('rot');
}

function toggleSpec(boton) {
  boton.nextElementSibling.classList.toggle('open');
  boton.querySelector('.chev').classList.toggle('rot');
}

function filterDocs() {
  var texto = buscar('searchInput').value.toLowerCase();
  var departamentos = document.querySelectorAll('.dept-block');
  var hayResultados = false;
  var i;

  for (i = 0; i < departamentos.length; i++) {
    var documentos = departamentos[i].querySelectorAll('.doc-item');
    var hayDocumentos = false;
    var j;

    for (j = 0; j < documentos.length; j++) {
      var coincide = (documentos[j].getAttribute('data-s') || '').indexOf(texto) !== -1;
      documentos[j].style.display = coincide ? '' : 'none';
      if (coincide) hayDocumentos = true;
    }
    departamentos[i].style.display = hayDocumentos ? '' : 'none';
    if (hayDocumentos) hayResultados = true;
  }
  buscar('noResults').classList.toggle('d-none', hayResultados);
}

function verDoc(nombre, especialidad, descripcion) {
  abrirModal(
    '<div class="modal-header"><h6 class="modal-title">' + nombre + '</h6>' +
    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
    '<div class="modal-body"><p class="text-muted">' + especialidad + '</p>' +
    '<p>' + descripcion + '</p></div>' +
    '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>' +
    '<button type="button" class="btn btn-primary" onclick="descargar(\'' + nombre.replace(/'/g, "\\'") + '\')">Descargar</button></div>'
  );
}

function descargar(nombre) {
  mostrarMensaje('Descargando: ' + (nombre || 'documento'), 'success');
}

var preguntasEstandar = [
  '¿Cómo califica la atención recibida?',
  '¿El personal fue amable y cortés?',
  '¿Se sintió respetado/a en todo momento?',
  '¿La información recibida fue clara?',
  '¿Cómo valora las instalaciones?',
  '¿El tiempo de espera fue razonable?',
  '¿Recomendaría este servicio?'
];

var preguntasTrasplantado = [
  '¿Cómo califica la atención en Nefrología?',
  '¿El equipo explicó el proceso de trasplante con claridad?',
  '¿Recibió información sobre los medicamentos inmunosupresores?',
  '¿Se siente acompañado/a en el seguimiento?',
  '¿Cómo valora la comunicación con el equipo?',
  '¿Las consultas de control son oportunas?',
  '¿Tiene acceso a los estudios solicitados?',
  '¿Se siente informado/a sobre los signos de alarma?',
  '¿Recomendaría el programa a otros pacientes?'
];

function abrirEncuesta(tipo) {
  var preguntas = tipo === 'estandar' ? preguntasEstandar : preguntasTrasplantado;
  var titulo = tipo === 'estandar' ? 'Encuesta de satisfacción' : 'Encuesta de usuario trasplantado';
  var html = '<div class="modal-header"><h6 class="modal-title">' + titulo + '</h6>' +
    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="encForm">';
  var i;

  for (i = 0; i < preguntas.length; i++) {
    html += '<div class="mb-4"><label class="form-label">' + (i + 1) + '. ' + preguntas[i] + '</label><div class="d-flex gap-2">';
    for (var numero = 1; numero <= 5; numero++) {
      html += '<div class="scale-opt flex-grow-1" data-q="' + i + '" onclick="selScale(this)">' + numero + '</div>';
    }
    html += '</div></div>';
  }
  html += '</form></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" onclick="enviarEncuesta()">Enviar</button></div>';
  abrirModal(html);
}

function selScale(opcion) {
  var opciones = document.querySelectorAll('.scale-opt[data-q="' + opcion.getAttribute('data-q') + '"]');
  var i;
  for (i = 0; i < opciones.length; i++) opciones[i].classList.remove('sel');
  opcion.classList.add('sel');
}

function enviarEncuesta() {
  var total = document.querySelectorAll('#encForm .mb-4').length;
  var respondidas = document.querySelectorAll('#encForm .scale-opt.sel').length;
  if (respondidas < total) {
    mostrarMensaje('Faltan ' + (total - respondidas) + ' pregunta(s) por responder.', 'warning');
    return;
  }
  if (modal) modal.hide();
  mostrarMensaje('Encuesta enviada correctamente. Gracias por tu respuesta.', 'success', 4000);
}

function sacarTurno() {
  var especialidad = buscar('turnoEsp').value;
  var datos = consultorios[especialidad];
  buscar('turnoEspError').textContent = '';
  if (!especialidad) {
    buscar('turnoEspError').textContent = 'Seleccioná una especialidad.';
    return;
  }

  var atendiendo = datos ? datos[0] : Math.floor(Math.random() * 30) + 1;
  var miTurno = datos ? datos[0] + datos[1] + Math.floor(Math.random() * 3) : Math.floor(Math.random() * 50) + 1;
  var adelante = miTurno - atendiendo;
  if (adelante < 0) adelante = 0;

  buscar('miTurnoNum').textContent = miTurno;
  buscar('miTurnoEsp').textContent = especialidad;
  buscar('miTurnoDept').textContent = 'Dpto. ' + (datos ? datos[2] : '—') + ' — Consultorio ' + (datos ? datos[3] : '');
  buscar('turnoAtendido').textContent = atendiendo;
  buscar('personasAdelante').textContent = adelante;
  buscar('turnoForm').classList.add('d-none');
  buscar('turnoTicket').classList.remove('d-none');
}

function volverTurnos() {
  buscar('turnoTicket').classList.add('d-none');
  buscar('turnoForm').classList.remove('d-none');
  buscar('turnoEsp').value = '';
  buscar('turnoEspError').textContent = '';
}

function filterMed() {
  var texto = buscar('searchMed').value.toLowerCase();
  var grupos = document.querySelectorAll('.med-group');
  var hayResultados = false;
  var i;
  for (i = 0; i < grupos.length; i++) {
    var medicamentos = grupos[i].querySelectorAll('.med-item');
    var hayMedicamentos = false;
    var j;
    for (j = 0; j < medicamentos.length; j++) {
      var coincide = (medicamentos[j].getAttribute('data-m') || '').indexOf(texto) !== -1;
      medicamentos[j].style.display = coincide ? '' : 'none';
      if (coincide) hayMedicamentos = true;
    }
    grupos[i].style.display = hayMedicamentos ? '' : 'none';
    if (hayMedicamentos) hayResultados = true;
  }
  buscar('noMed').classList.toggle('d-none', hayResultados);
}

function generarQR() {
  var ci = buscar('ciInput').value.replace(/\D/g, '');
  buscar('ciError').textContent = '';
  if (ci.length < 6 || ci.length > 8) {
    buscar('ciError').textContent = 'Ingresá una cédula válida de 6 a 8 dígitos.';
    return;
  }

  var fecha = new Date();
  fecha.setHours(fecha.getHours() + 24);
  var fechaTexto = fecha.toLocaleDateString('es-UY') + ' ' + fecha.toLocaleTimeString('es-UY', { hour: '2-digit', minute: '2-digit' });
  buscar('qrCode').innerHTML = '';
  new QRCode(buscar('qrCode'), { text: 'HC-RESULTADO|CI:' + ci + '|FECHA:' + fechaTexto, width: 200, height: 200 });
  buscar('qrCi').textContent = ci;
  buscar('qrFecha').textContent = fechaTexto;
  buscar('qrResult').classList.remove('d-none');
}

function descargarQR() {
  var imagen = buscar('qrCode').querySelector('canvas') || buscar('qrCode').querySelector('img');
  if (!imagen) return;
  var enlace = document.createElement('a');
  enlace.download = 'qr-resultado-' + buscar('ciInput').value + '.png';
  enlace.href = imagen.tagName === 'CANVAS' ? imagen.toDataURL('image/png') : imagen.src;
  enlace.click();
}

/* ====== CAMBIO DE PESTAÑAS ====== */
function switchTab(t, el) {
  document.querySelectorAll('.panel-section').forEach(function (section) {
    section.classList.remove('show');
    section.style.display = 'none';
  });

  const selected = document.getElementById(panels[t]);
  if (selected) {
    selected.classList.add('show');
    selected.style.display = 'block';
  }

  document.querySelectorAll('#mainTabs .nav-link').forEach(function (l) {
    l.classList.remove('active', 'text-primary', 'fw-medium');
    l.classList.add('text-muted');
  });

  if (el) {
    el.classList.add('active', 'text-primary', 'fw-medium');
    el.classList.remove('text-muted');
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const docsTab = document.querySelector('#mainTabs .nav-link.active');
  switchTab('docs', docsTab || document.querySelector('#mainTabs .nav-link'));
});

function toggleDept(btn) {
  const c = btn.nextElementSibling;
  const ch = btn.querySelector('.chev');

  document.querySelectorAll('.dept-block').forEach(function (b) {
    if (b !== btn.parentElement) {
      const body = b.querySelector('.dept-body');
      if (body) body.classList.remove('open');
      const chev = b.querySelector(':scope > button .chev');
      if (chev) chev.classList.remove('rot');
    }
  });

  if (c) c.classList.toggle('open');
  if (ch) ch.classList.toggle('rot');
}

function toggleSpec(btn) {
  if (btn.nextElementSibling) {
    btn.nextElementSibling.classList.toggle('open');
  }
  const chev = btn.querySelector('.chev');
  if (chev) chev.classList.toggle('rot');
}

/* ====== DOCUMENTOS: BUSCAR ====== */
function filterDocs() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  let any = false;

  document.querySelectorAll('.dept-block').forEach(function (dept) {
    let dm = false;

    dept.querySelectorAll('.doc-item').forEach(function (di) {
      const m = (di.getAttribute('data-s') || '').includes(q);
      di.style.display = m ? '' : 'none';
      if (m) dm = true;

      if (m && q) {
        const parentBody = di.closest('.dept-body');
        if (parentBody) parentBody.classList.add('open');

        const specBlock = di.closest('.spec-block');
        if (specBlock) {
          const specBody = specBlock.querySelector('.dept-body');
          if (specBody) specBody.classList.add('open');

          const specChev = specBlock.querySelector('.chev');
          if (specChev) specChev.classList.add('rot');
        }
      }
    });

    dept.style.display = dm ? '' : 'none';

    if (dm) {
      any = true;
      if (q) {
        const deptBody = dept.querySelector(':scope > .dept-body');
        if (deptBody) deptBody.classList.add('open');

        const deptChev = dept.querySelector(':scope > button .chev');
        if (deptChev) deptChev.classList.add('rot');
      }
    }
  });

  document.getElementById('noResults').classList.toggle('d-none', any);
}

/* ====== DOCUMENTOS: VER EN MODAL ====== */
function verDoc(nombre, especialidad, descripcion) {
  let html = '<div class="modal-header"><h6 class="modal-title fw-semibold" style="font-size:15px;">' + nombre + '</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>';
  html += '<div class="modal-body"><p class="text-muted mb-2" style="font-size:13px;"><i class="bi bi-folder2-open me-1"></i>' + especialidad + '</p>';
  html += '<p style="font-size:14px;">' + descripcion + '</p><hr><p class="text-muted mb-0" style="font-size:12px;">Documento informativo de uso interno del Hospital de Clínicas.</p></div>';
  html += '<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button><button type="button" class="btn btn-primary" onclick="descargar(\'' + nombre.replace(/'/g, "\\'") + '\')"><i class="bi bi-download me-1"></i>Descargar</button></div>';
  document.getElementById('modalContent').innerHTML = html;

  if (mInst) mInst.hide();
  mInst = new bootstrap.Modal(document.getElementById('mainModal'));
  mInst.show();
}

/* ====== DOCUMENTOS: DESCARGAR ====== */
function descargar(nombre) {
  const toast = document.createElement('div');
  toast.className = 'position-fixed bottom-0 end-0 p-3';
  toast.style.zIndex = '9999';
  toast.innerHTML = '<div class="toast show align-items-center text-bg-success border-0" role="alert"><div class="d-flex"><div class="toast-body"><i class="bi bi-check-circle me-2"></i>Descargando: ' + (nombre || 'documento') + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest(\'.position-fixed\').remove()"></button></div></div>';
  document.body.appendChild(toast);
  setTimeout(function () { toast.remove(); }, 3000);
}

/* ====== ENCUESTAS ====== */
function abrirEncuesta(tipo) {
  let preguntas = [];

  if (tipo === 'estandar') {
    preguntas = [
      '¿Cómo califica la atención recibida?',
      '¿El personal fue amable y cortés?',
      '¿Se sintió respetado/a en todo momento?',
      '¿La información recibida fue clara?',
      '¿Cómo valora las instalaciones?',
      '¿El tiempo de espera fue razonable?',
      '¿Recomendaría este servicio?'
    ];
  } else {
    preguntas = [
      '¿Cómo califica la atención en Nefrología?',
      '¿El equipo explicó el proceso de trasplante con claridad?',
      '¿Recibió información sobre los medicamentos inmunosupresores?',
      '¿Se siente acompañado/a en el seguimiento?',
      '¿Cómo valora la comunicación con el equipo?',
      '¿Las consultas de control son oportunas?',
      '¿Tiene acceso a los estudios solicitados?',
      '¿Se siente informado/a sobre los signos de alarma?',
      '¿Recomendaría el programa a otros pacientes?'
    ];
  }

  let html = '<div class="modal-header"><h6 class="modal-title fw-semibold" style="font-size:15px;">' + (tipo === 'estandar' ? 'Encuesta de satisfacción' : 'Encuesta — Usuario trasplantado') + '</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>';
  html += '<div class="modal-body"><form id="encForm">';

  preguntas.forEach(function (p, i) {
    html += '<div class="mb-4"><label class="form-label fw-medium" style="font-size:14px;">' + (i + 1) + '. ' + p + '</label><div class="d-flex gap-2 flex-wrap">';
    ['1', '2', '3', '4', '5'].forEach(function (op) {
      html += '<div class="scale-opt flex-grow-1" style="min-width:44px;" onclick="selScale(this)" data-q="' + i + '"><div style="font-size:16px;font-weight:600;">' + op + '</div></div>';
    });
    html += '</div></div>';
  });

  html += '</form></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" onclick="enviarEncuesta()"><i class="bi bi-send me-1"></i>Enviar</button></div>';
  document.getElementById('modalContent').innerHTML = html;

  if (mInst) mInst.hide();
  mInst = new bootstrap.Modal(document.getElementById('mainModal'));
  mInst.show();
}

function selScale(el) {
  const q = el.getAttribute('data-q');
  document.querySelectorAll('.scale-opt[data-q="' + q + '"]').forEach(function (o) {
    o.classList.remove('sel');
  });
  el.classList.add('sel');
}

function enviarEncuesta() {
  const totalPreguntas = document.querySelectorAll('#encForm .mb-4').length;
  const respondidas = document.querySelectorAll('#encForm .scale-opt.sel').length;

  if (respondidas < totalPreguntas) {
    const faltan = totalPreguntas - respondidas;
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = '<div class="toast show align-items-center text-bg-warning border-0" role="alert"><div class="d-flex"><div class="toast-body"><i class="bi bi-exclamation-triangle me-2"></i>Faltan ' + faltan + ' pregunta(s) por responder.</div><button type="button" class="btn-close btn-close-dark me-2 m-auto" onclick="this.closest(\'.position-fixed\').remove()"></button></div></div>';
    document.body.appendChild(toast);
    setTimeout(function () { toast.remove(); }, 3000);
    return;
  }

  if (mInst) mInst.hide();

  const toast = document.createElement('div');
  toast.className = 'position-fixed bottom-0 end-0 p-3';
  toast.style.zIndex = '9999';
  toast.innerHTML = '<div class="toast show align-items-center text-bg-success border-0" role="alert"><div class="d-flex"><div class="toast-body"><i class="bi bi-check-circle me-2"></i>Encuesta enviada correctamente. Gracias por tu respuesta.</div><button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest(\'.position-fixed\').remove()"></button></div></div>';
  document.body.appendChild(toast);
  setTimeout(function () { toast.remove(); }, 4000);
}

/* ====== TURNOS ====== */
function sacarTurno() {
  const esp = document.getElementById('turnoEsp').value;
  const errEl = document.getElementById('turnoEspError');
  errEl.textContent = '';

  if (!esp) {
    errEl.textContent = 'Seleccioná una especialidad.';
    return;
  }

  const consultorios = [
    { esp: 'Cardiología', dept: 'Medicina', turno: 42, espera: 5 },
    { esp: 'Gastroenterología', dept: 'Medicina', turno: 18, espera: 3 },
    { esp: 'Nefrología', dept: 'Medicina', turno: 7, espera: 8 },
    { esp: 'Clínica médica A', dept: 'Medicina', turno: 25, espera: 2 },
    { esp: 'Clínica médica B', dept: 'Medicina', turno: 31, espera: 4 },
    { esp: 'Dermatología', dept: 'Medicina', turno: 14, espera: 1 },
    { esp: 'Endocrinología', dept: 'Medicina', turno: 0, espera: 0 },
    { esp: 'Neurología', dept: 'Medicina', turno: 9, espera: 6 },
    { esp: 'Oncología', dept: 'Medicina', turno: 52, espera: 7 },
    { esp: 'Traumatología', dept: 'Cirugía', turno: 55, espera: 6 },
    { esp: 'Urología', dept: 'Cirugía', turno: 11, espera: 4 },
    { esp: 'Oftalmología', dept: 'Cirugía', turno: 38, espera: 3 },
    { esp: 'Imagenología', dept: 'Diagnósticos', turno: 33, espera: 2 }
  ];

  let found = null;
  let idx = 0;

  for (let i = 0; i < consultorios.length; i++) {
    if (consultorios[i].esp === esp) {
      found = consultorios[i];
      idx = i + 1;
      break;
    }
  }

  const miTurno = found ? found.turno + found.espera + Math.floor(Math.random() * 3) : Math.floor(Math.random() * 50) + 1;
  const atendiendo = found ? found.turno : Math.floor(Math.random() * 30) + 1;
  let adelante = miTurno - atendiendo;

  if (adelante < 0) adelante = 0;

  document.getElementById('miTurnoNum').textContent = miTurno;
  document.getElementById('miTurnoEsp').textContent = esp;
  document.getElementById('miTurnoDept').textContent = 'Dpto. ' + (found ? found.dept : '—') + ' — Consultorio ' + idx;
  document.getElementById('turnoAtendido').textContent = atendiendo;
  document.getElementById('personasAdelante').textContent = adelante;
  document.getElementById('turnoForm').classList.add('d-none');
  document.getElementById('turnoTicket').classList.remove('d-none');
}

function volverTurnos() {
  document.getElementById('turnoTicket').classList.add('d-none');
  document.getElementById('turnoForm').classList.remove('d-none');
  document.getElementById('turnoEsp').value = '';
  document.getElementById('turnoEspError').textContent = '';
}

/* ====== FARMACIA: BUSCAR ====== */
function filterMed() {
  const q = document.getElementById('searchMed').value.toLowerCase();
  let any = false;

  document.querySelectorAll('.med-group').forEach(function (g) {
    let gm = false;
    g.querySelectorAll('.med-item').forEach(function (m) {
      const match = (m.getAttribute('data-m') || '').includes(q);
      m.style.display = match ? '' : 'none';
      if (match) gm = true;
    });
    g.style.display = gm ? '' : 'none';
    if (gm) any = true;
  }); 

  document.getElementById('noMed').classList.toggle('d-none', any);
}

/* ====== QR: GENERAR ====== */
function generarQR() {
  const ci = document.getElementById('ciInput').value.replace(/\D/g, '');
  const errEl = document.getElementById('ciError');
  errEl.textContent = '';

  if (!ci || ci.length < 6 || ci.length > 8) {
    errEl.textContent = 'Ingresá un número de cédula válido (6 a 8 dígitos).';
    return;
  }

  const qrContainer = document.getElementById('qrCode');
  qrContainer.innerHTML = '';

  const ahora = new Date();
  ahora.setHours(ahora.getHours() + 24);
  const fechaStr = ahora.getDate().toString().padStart(2, '0') + '/' + (ahora.getMonth() + 1).toString().padStart(2, '0') + '/' + ahora.getFullYear() + ' ' + ahora.getHours().toString().padStart(2, '0') + ':' + ahora.getMinutes().toString().padStart(2, '0');

  new QRCode(qrContainer, {
    text: 'HC-RESULTADO|CI:' + ci + '|FECHA:' + fechaStr + '|VALIDO:24H',
    width: 200,
    height: 200,
    colorDark: '#1B3A5C',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
  });

  document.getElementById('qrCi').textContent = ci;
  document.getElementById('qrFecha').textContent = fechaStr;
  document.getElementById('qrResult').classList.remove('d-none');
}

/* ====== QR: DESCARGAR ====== */
function descargarQR() {
  const canvas = document.querySelector('#qrCode canvas');

  if (!canvas) {
    const img = document.querySelector('#qrCode img');
    if (img) {
      const link = document.createElement('a');
      link.download = 'qr-resultado-' + document.getElementById('ciInput').value + '.png';
      link.href = img.src;
      link.click();
    }
    return;
  }

  const link = document.createElement('a');
  link.download = 'qr-resultado-' + document.getElementById('ciInput').value + '.png';
  link.href = canvas.toDataURL('image/png');
  link.click();
}
