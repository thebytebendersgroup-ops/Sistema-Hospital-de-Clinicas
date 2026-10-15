// ============================================================
// JavaScript del portal del paciente (portal-paciente.php)
// (El panel de administración tiene su propio JavaScript dentro de index.php)
// ============================================================

// Atajo para buscar un elemento por su id
function buscar(id) {
  return document.getElementById(id);
}

// ----- Cambio de pestañas (Documentos / Encuesta) -----
var paneles = {
  docs: 'panelDocs',
  enc: 'panelEnc'
};

function switchTab(nombre, enlace) {
  var secciones = document.querySelectorAll('.panel-section');
  var enlaces = document.querySelectorAll('#mainTabs .nav-link');
  var i;

  // Se esconden todos los paneles y se muestra solo el elegido
  for (i = 0; i < secciones.length; i++) {
    secciones[i].classList.remove('show');
  }
  buscar(paneles[nombre]).classList.add('show');

  // La pestaña elegida se marca como activa
  for (i = 0; i < enlaces.length; i++) {
    enlaces[i].classList.remove('active', 'text-primary', 'fw-medium');
    enlaces[i].classList.add('text-muted');
  }
  if (enlace) {
    enlace.classList.add('active', 'text-primary', 'fw-medium');
    enlace.classList.remove('text-muted');
  }
}

// ----- Buscador de documentos -----
// Cada documento tiene un atributo data-s con su título y su categoría en minúsculas.
function filterDocs() {
  var texto = buscar('searchInput').value.toLowerCase().trim();
  var categorias = document.querySelectorAll('.dept-block');
  var hayResultados = false;
  var i, j;

  for (i = 0; i < categorias.length; i++) {
    var documentos = categorias[i].querySelectorAll('.doc-item');
    var hayEnCategoria = false;

    for (j = 0; j < documentos.length; j++) {
      var coincide = (documentos[j].getAttribute('data-s') || '').indexOf(texto) !== -1;
      documentos[j].style.display = coincide ? '' : 'none';
      if (coincide) hayEnCategoria = true;
    }

    // Se esconde la categoría si no tiene coincidencias, y se abre si buscó algo y hay resultados
    categorias[i].style.display = hayEnCategoria ? '' : 'none';
    if (hayEnCategoria && texto !== '') {
      categorias[i].querySelector('.collapse').classList.add('show');
    }
    if (hayEnCategoria) hayResultados = true;
  }

  var aviso = buscar('noResults');
  aviso.textContent = 'No se encontraron documentos.';
  aviso.classList.toggle('d-none', hayResultados);
}

// ----- Código QR de un documento -----
// El botón trae la dirección en data-url y el título en data-titulo.
function verQR(boton) {
  var contenedor = buscar('qrCode');
  contenedor.innerHTML = '';
  new QRCode(contenedor, { text: boton.dataset.url, width: 200, height: 200 });

  buscar('tituloQR').textContent = boton.dataset.titulo;
  buscar('enlaceQR').href = boton.dataset.url;

  bootstrap.Modal.getOrCreateInstance(buscar('modalQR')).show();
}
