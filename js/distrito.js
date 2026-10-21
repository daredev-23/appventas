$(function () {
  const urlListar = '/paginas/controlador/ctr_listar_dist.php';
  const urlGrabar = '/paginas/controlador/ctr_grabar_dist.php';
  const urlBorrar = '/paginas/controlador/ctr_borrar_dist.php';
  const urlDeps   = '/paginas/controlador/ctr_listar_dep.php';
  const urlProvs  = '/paginas/controlador/ctr_listar_prov_dep.php';

  const modalForm     = new bootstrap.Modal(document.getElementById('modalForm'));
  const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));
  let codigoPorEliminar = null;

  function mostrarAviso(tipo, texto) {
    $('#aviso').empty().append(
      $('<div class="alert alert-dismissible fade show" role="alert">')
        .addClass('alert-' + tipo)
        .append($('<span>').text(texto))
        .append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>')
    );
  }

  // ---------- Primera lista: departamentos ----------
  function cargarDepartamentos() {
    $.getJSON(urlDeps)
      .done(function (resp) {
        const $sel = $('#departamento').empty()
          .append($('<option value="">').text('Elige un departamento...'));
        resp.datos.forEach(function (d) {
          $sel.append($('<option>').val(d.codigo_departamento).text(d.departamento));
        });
      })
      .fail(function () {
        mostrarAviso('danger', 'No se pudo cargar la lista de departamentos.');
      });
  }

  // ---------- Segunda lista: provincias del departamento elegido ----------
  function reiniciarProvincias() {
    $('#provincia').empty()
      .append($('<option value="">').text('Primero elige un departamento'))
      .prop('disabled', true);
  }

  function cargarProvincias(dep) {
    if (dep === '') {
      reiniciarProvincias();
      return;
    }

    $('#provincia').prop('disabled', true).empty()
      .append($('<option value="">').text('Cargando...'));

    $.getJSON(urlProvs, { departamento: dep })
      .done(function (resp) {
        // Si mientras tanto elegiste otro departamento, esta respuesta ya no sirve
        if ($('#departamento').val() !== dep) { return; }

        const $sel = $('#provincia').empty()
          .append($('<option value="">').text('Elige una provincia...'));
        resp.datos.forEach(function (p) {
          $sel.append($('<option>').val(p.codigo_provincia).text(p.provincia));
        });
        $sel.prop('disabled', false);
      })
      .fail(function () {
        $('#errorForm').text('No se pudo cargar la lista de provincias.');
      });
  }

  // ---------- Tabla ----------
  function cargarLista() {
    $.getJSON(urlListar)
      .done(function (resp) {
        const $tbody = $('#tabla').empty();

        if (resp.datos.length === 0) {
          $tbody.append(
            $('<tr>').append(
              $('<td colspan="5" class="text-center text-body-secondary">')
                .text('No hay distritos.')
            )
          );
          return;
        }

        resp.datos.forEach(function (d) {
          const $editar = $('<button type="button" class="btn btn-sm btn-outline-primary btn-editar me-1" title="Editar">')
            .attr('data-codigo', d.codigo_distrito)
            .attr('data-nombre', d.distrito)
            .attr('data-prov', d.codigo_provincia)
            .attr('data-provnombre', d.provincia)
            .attr('data-dep', d.codigo_departamento)
            .html('<i class="fa-solid fa-pen"></i>');

          const $eliminar = $('<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar">')
            .attr('data-codigo', d.codigo_distrito)
            .attr('data-nombre', d.distrito)
            .html('<i class="fa-solid fa-trash"></i>');

          $tbody.append(
            $('<tr>').append(
              $('<td>').text(d.codigo_distrito),
              $('<td>').text(d.distrito),
              $('<td>').text(d.provincia),
              $('<td>').text(d.departamento),
              $('<td class="text-end">').append($editar, $eliminar)
            )
          );
        });
      })
      .fail(function () {
        mostrarAviso('danger', 'No se pudo cargar la lista de distritos.');
      });
  }

  // ---------- Formulario: abrir en modo "crear" ----------
  $('#btnNuevo').on('click', function () {
    $('#formDist')[0].reset();
    $('#errorForm').text('');
    $('#modo').val('crear');
    $('#departamento').prop('disabled', false);
    reiniciarProvincias();
    $('#codigo').prop('readonly', false);
    $('#tituloForm').text('Nuevo distrito');
    modalForm.show();
  });

  // ---------- Formulario: abrir en modo "editar" ----------
  $('#tabla').on('click', '.btn-editar', function () {
    $('#formDist')[0].reset();
    $('#errorForm').text('');
    $('#modo').val('editar');

    $('#departamento').val($(this).attr('data-dep')).prop('disabled', true);
    $('#provincia').empty()
      .append($('<option>').val($(this).attr('data-prov')).text($(this).attr('data-provnombre')))
      .prop('disabled', true);
    $('#codigo').val($(this).attr('data-codigo')).prop('readonly', true);
    $('#nombre').val($(this).attr('data-nombre'));

    $('#tituloForm').text('Editar distrito');
    modalForm.show();
  });

  // Al terminar de abrirse, el cursor va al primer campo disponible
  document.getElementById('modalForm').addEventListener('shown.bs.modal', function () {
    $($('#departamento').prop('disabled') ? '#nombre' : '#departamento').trigger('focus');
  });

  // ---------- Las listas encadenadas ----------
  $('#departamento').on('change', function () {
    $('#codigo').val('');
    cargarProvincias($(this).val());
  });

  $('#provincia').on('change', function () {
    const prov = $(this).val();
    if (prov && !$('#codigo').val().startsWith(prov)) {
      $('#codigo').val(prov);
    }
    if (prov) { $('#codigo').trigger('focus'); }
  });

  // Validación en vivo: el código solo admite dígitos
  $('#codigo').on('input', function () {
    this.value = this.value.replace(/\D/g, '');
  });

  // ---------- Formulario: guardar ----------
  $('#formDist').on('submit', function (e) {
    e.preventDefault();

    const modo   = $('#modo').val();
    const prov   = $('#provincia').val();
    const codigo = $('#codigo').val().trim();
    const nombre = $('#nombre').val().trim();

    if (modo === 'crear' && prov === '') {
      $('#errorForm').text('Elige un departamento y una provincia.');
      return;
    }
    if (!/^\d{6}$/.test(codigo)) {
      $('#errorForm').text('El código debe tener exactamente 6 dígitos.');
      return;
    }
    if (modo === 'crear' && !codigo.startsWith(prov)) {
      $('#errorForm').text('El código debe empezar con ' + prov + ', el código de la provincia.');
      return;
    }
    if (nombre === '') {
      $('#errorForm').text('El nombre es obligatorio.');
      return;
    }
    $('#errorForm').text('');

    const $boton = $('#btnGuardar').prop('disabled', true);

    $.ajax({
      url: urlGrabar,
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json'
    })
      .done(function (resp) {
        modalForm.hide();
        mostrarAviso('success', resp.mensaje);
        cargarLista();
      })
      .fail(function (xhr) {
        const mensaje = (xhr.responseJSON && xhr.responseJSON.mensaje)
          || 'No se pudo guardar el distrito.';
        $('#errorForm').text(mensaje);
      })
      .always(function () {
        $boton.prop('disabled', false);
      });
  });

  // ---------- Eliminar ----------
  $('#tabla').on('click', '.btn-eliminar', function () {
    codigoPorEliminar = $(this).attr('data-codigo');
    $('#nombreEliminar').text($(this).attr('data-nombre'));
    modalEliminar.show();
  });

  $('#confirmarEliminar').on('click', function () {
    const $boton = $(this).prop('disabled', true);

    $.ajax({
      url: urlBorrar,
      method: 'POST',
      data: { codigo: codigoPorEliminar },
      dataType: 'json'
    })
      .done(function (resp) {
        modalEliminar.hide();
        mostrarAviso('success', resp.mensaje);
        cargarLista();
      })
      .fail(function (xhr) {
        modalEliminar.hide();
        const mensaje = (xhr.responseJSON && xhr.responseJSON.mensaje)
          || 'No se pudo eliminar el distrito.';
        mostrarAviso('danger', mensaje);
      })
      .always(function () {
        $boton.prop('disabled', false);
      });
  });

  cargarDepartamentos();
  cargarLista();
});
