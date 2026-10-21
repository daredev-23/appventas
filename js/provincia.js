$(function () {
  const urlListar = '/paginas/controlador/ctr_listar_prov.php';
  const urlGrabar = '/paginas/controlador/ctr_grabar_prov.php';
  const urlBorrar = '/paginas/controlador/ctr_borrar_prov.php';
  const urlDeps   = '/paginas/controlador/ctr_listar_dep.php';

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

  // Llena la lista desplegable con los departamentos (pedido Ajax)
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

  function cargarLista() {
    $.getJSON(urlListar)
      .done(function (resp) {
        const $tbody = $('#tabla').empty();

        if (resp.datos.length === 0) {
          $tbody.append(
            $('<tr>').append(
              $('<td colspan="4" class="text-center text-body-secondary">')
                .text('No hay provincias.')
            )
          );
          return;
        }

        resp.datos.forEach(function (p) {
          const $editar = $('<button type="button" class="btn btn-sm btn-outline-primary btn-editar me-1" title="Editar">')
            .attr('data-codigo', p.codigo_provincia)
            .attr('data-nombre', p.provincia)
            .attr('data-dep', p.codigo_departamento)
            .html('<i class="fa-solid fa-pen"></i>');

          const $eliminar = $('<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar">')
            .attr('data-codigo', p.codigo_provincia)
            .attr('data-nombre', p.provincia)
            .html('<i class="fa-solid fa-trash"></i>');

          $tbody.append(
            $('<tr>').append(
              $('<td>').text(p.codigo_provincia),
              $('<td>').text(p.provincia),
              $('<td>').text(p.departamento),
              $('<td class="text-end">').append($editar, $eliminar)
            )
          );
        });
      })
      .fail(function () {
        mostrarAviso('danger', 'No se pudo cargar la lista de provincias.');
      });
  }

  // ---------- Formulario: abrir en modo "crear" ----------
  $('#btnNuevo').on('click', function () {
    $('#formProv')[0].reset();
    $('#errorForm').text('');
    $('#modo').val('crear');
    $('#departamento').prop('disabled', false);
    $('#codigo').prop('readonly', false);
    $('#tituloForm').text('Nueva provincia');
    modalForm.show();
  });

  // ---------- Formulario: abrir en modo "editar" ----------
  $('#tabla').on('click', '.btn-editar', function () {
    $('#formProv')[0].reset();
    $('#errorForm').text('');
    $('#modo').val('editar');
    $('#departamento').val($(this).attr('data-dep')).prop('disabled', true);
    $('#codigo').val($(this).attr('data-codigo')).prop('readonly', true);
    $('#nombre').val($(this).attr('data-nombre'));
    $('#tituloForm').text('Editar provincia');
    modalForm.show();
  });

  // Al terminar de abrirse, el cursor va al primer campo disponible
  document.getElementById('modalForm').addEventListener('shown.bs.modal', function () {
    $($('#departamento').prop('disabled') ? '#nombre' : '#departamento').trigger('focus');
  });

  // Al elegir un departamento, el código empieza con el suyo
  $('#departamento').on('change', function () {
    const dep = $(this).val();
    if (dep && !$('#codigo').val().startsWith(dep)) {
      $('#codigo').val(dep);
    }
    $('#codigo').trigger('focus');
  });

  // Validación en vivo: el código solo admite dígitos
  $('#codigo').on('input', function () {
    this.value = this.value.replace(/\D/g, '');
  });

  // ---------- Formulario: guardar ----------
  $('#formProv').on('submit', function (e) {
    e.preventDefault();

    const modo   = $('#modo').val();
    const dep    = $('#departamento').val();
    const codigo = $('#codigo').val().trim();
    const nombre = $('#nombre').val().trim();

    if (modo === 'crear' && dep === '') {
      $('#errorForm').text('Elige un departamento.');
      return;
    }
    if (!/^\d{4}$/.test(codigo)) {
      $('#errorForm').text('El código debe tener exactamente 4 dígitos.');
      return;
    }
    if (modo === 'crear' && !codigo.startsWith(dep)) {
      $('#errorForm').text('El código debe empezar con ' + dep + ', el código del departamento.');
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
          || 'No se pudo guardar la provincia.';
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
          || 'No se pudo eliminar la provincia.';
        mostrarAviso('danger', mensaje);
      })
      .always(function () {
        $boton.prop('disabled', false);
      });
  });

  cargarDepartamentos();
  cargarLista();
});
