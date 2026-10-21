$(function () {
  const urlListar = '/paginas/controlador/ctr_listar_dep.php';
  const urlGrabar = '/paginas/controlador/ctr_grabar_dep.php';
  const urlBorrar = '/paginas/controlador/ctr_borrar_dep.php';

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

  function cargarLista() {
    $.getJSON(urlListar)
      .done(function (resp) {
        const $tbody = $('#tabla').empty();

        if (resp.datos.length === 0) {
          $tbody.append(
            $('<tr>').append(
              $('<td colspan="3" class="text-center text-body-secondary">')
                .text('No hay departamentos.')
            )
          );
          return;
        }

        resp.datos.forEach(function (d) {
          const $editar = $('<button type="button" class="btn btn-sm btn-outline-primary btn-editar me-1" title="Editar">')
            .attr('data-codigo', d.codigo_departamento)
            .attr('data-nombre', d.departamento)
            .html('<i class="fa-solid fa-pen"></i>');

          const $eliminar = $('<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar">')
            .attr('data-codigo', d.codigo_departamento)
            .attr('data-nombre', d.departamento)
            .html('<i class="fa-solid fa-trash"></i>');

          $tbody.append(
            $('<tr>').append(
              $('<td>').text(d.codigo_departamento),
              $('<td>').text(d.departamento),
              $('<td class="text-end">').append($editar, $eliminar)
            )
          );
        });
      })
      .fail(function () {
        mostrarAviso('danger', 'No se pudo cargar la lista de departamentos.');
      });
  }

  // ---------- Formulario: abrir en modo "crear" ----------
  $('#btnNuevo').on('click', function () {
    $('#formDep')[0].reset();
    $('#errorForm').text('');
    $('#modo').val('crear');
    $('#codigo').prop('readonly', false);
    $('#tituloForm').text('Nuevo departamento');
    modalForm.show();
  });

  // ---------- Formulario: abrir en modo "editar" ----------
  $('#tabla').on('click', '.btn-editar', function () {
    $('#formDep')[0].reset();
    $('#errorForm').text('');
    $('#modo').val('editar');
    $('#codigo').val($(this).attr('data-codigo')).prop('readonly', true);
    $('#nombre').val($(this).attr('data-nombre'));
    $('#tituloForm').text('Editar departamento');
    modalForm.show();
  });

  // Al terminar de abrirse, el cursor va al primer campo editable
  document.getElementById('modalForm').addEventListener('shown.bs.modal', function () {
    $($('#codigo').prop('readonly') ? '#nombre' : '#codigo').trigger('focus');
  });

  // Validación en vivo: el código solo admite dígitos
  $('#codigo').on('input', function () {
    this.value = this.value.replace(/\D/g, '');
  });

  // ---------- Formulario: guardar ----------
  $('#formDep').on('submit', function (e) {
    e.preventDefault();

    const codigo = $('#codigo').val().trim();
    const nombre = $('#nombre').val().trim();

    if (!/^\d{2}$/.test(codigo)) {
      $('#errorForm').text('El código debe tener exactamente 2 dígitos.');
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
          || 'No se pudo guardar el departamento.';
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
          || 'No se pudo eliminar el departamento.';
        mostrarAviso('danger', mensaje);
      })
      .always(function () {
        $boton.prop('disabled', false);
      });
  });

  cargarLista();
});
