$(function () {
  const urlListar  = '/paginas/controlador/ctr_listar_venta.php';
  const urlDetalle = '/paginas/controlador/ctr_detalle_venta.php';
  const urlBorrar  = '/paginas/controlador/ctr_borrar_venta.php';

  const modalVer      = new bootstrap.Modal(document.getElementById('modalVer'));
  const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));

  let idPorEliminar = null;
  let pedidoLista   = 0;
  let pedidoVer     = 0;
  let temporizador  = null;

  // 2398.4 -> "S/ 2,398.40"
  function dinero(valor) {
    return 'S/ ' + Number(valor).toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  // "2026-10-17 10:20:00" -> "17/10/2026 10:20"
  function fechaHora(texto) {
    const partes = texto.split(' ');
    const f = partes[0].split('-');
    return f[2] + '/' + f[1] + '/' + f[0] + ' ' + partes[1].slice(0, 5);
  }

  function mostrarAviso(tipo, texto) {
    $('#aviso').empty().append(
      $('<div class="alert alert-dismissible fade show" role="alert">')
        .addClass('alert-' + tipo)
        .append($('<span>').text(texto))
        .append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>')
    );
  }

  // ---------- Tabla (con búsqueda) ----------
  function cargarLista() {
    const buscar   = $('#buscar').val().trim();
    const miPedido = ++pedidoLista;

    $.getJSON(urlListar, { buscar: buscar })
      .done(function (resp) {
        // Si ya hiciste otra búsqueda mientras esta viajaba, esta respuesta ya no sirve
        if (miPedido !== pedidoLista) { return; }

        const $tbody = $('#tabla').empty();

        if (resp.datos.length === 0) {
          $tbody.append(
            $('<tr>').append(
              $('<td colspan="5" class="text-center text-body-secondary">')
                .text(buscar === '' ? 'Aún no hay ventas.' : 'No hay ventas que coincidan con la búsqueda.')
            )
          );
          return;
        }

        resp.datos.forEach(function (v) {
          const $ver = $('<button type="button" class="btn btn-sm btn-outline-secondary btn-ver me-1" title="Ver detalle">')
            .attr('data-id', v.id_venta)
            .html('<i class="fa-solid fa-eye"></i>');

          const $eliminar = $('<button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar">')
            .attr('data-id', v.id_venta)
            .html('<i class="fa-solid fa-trash"></i>');

          $tbody.append(
            $('<tr>').append(
              $('<td>').text(v.id_venta),
              $('<td>').text(fechaHora(v.fecha_venta)),
              $('<td>').text(v.cliente),
              $('<td class="text-end">').text(dinero(v.total)),
              $('<td class="text-end">').append($ver, $eliminar)
            )
          );
        });
      })
      .fail(function () {
        mostrarAviso('danger', 'No se pudo cargar la lista de ventas.');
      });
  }

  // Busca 300 milisegundos después de que dejas de escribir
  $('#buscar').on('input', function () {
    clearTimeout(temporizador);
    temporizador = setTimeout(cargarLista, 300);
  });

  // ---------- Botón "Ver": el detalle se pide solo ahora ----------
  $('#tabla').on('click', '.btn-ver', function () {
    const id       = $(this).attr('data-id');
    const miPedido = ++pedidoVer;

    $('#tituloVer').text('Detalle de la venta N° ' + id);
    $('#cargandoVer').removeClass('d-none');
    $('#errorVer').text('');
    $('#contenidoVer').addClass('d-none');
    modalVer.show();

    $.getJSON(urlDetalle, { id: id })
      .done(function (resp) {
        if (miPedido !== pedidoVer) { return; }

        const v = resp.datos.venta;
        $('#verFecha').text(fechaHora(v.fecha_venta));
        $('#verCliente').text(v.cliente);
        $('#verUsuario').text(v.usuario);
        $('#verTotal').text(dinero(v.total));

        const $lineas = $('#verLineas').empty();
        resp.datos.lineas.forEach(function (l) {
          $lineas.append(
            $('<tr>').append(
              $('<td>').text(l.producto),
              $('<td class="text-end">').text(l.cantidad),
              $('<td class="text-end">').text(dinero(l.precio_unitario)),
              $('<td class="text-end">').text(dinero(l.subtotal))
            )
          );
        });

        $('#cargandoVer').addClass('d-none');
        $('#contenidoVer').removeClass('d-none');
      })
      .fail(function (xhr) {
        if (miPedido !== pedidoVer) { return; }
        $('#cargandoVer').addClass('d-none');
        $('#errorVer').text(
          (xhr.responseJSON && xhr.responseJSON.mensaje) || 'No se pudo cargar el detalle de la venta.'
        );
      });
  });

  // ---------- Eliminar ----------
  $('#tabla').on('click', '.btn-eliminar', function () {
    idPorEliminar = $(this).attr('data-id');
    $('#numeroEliminar').text(idPorEliminar);
    modalEliminar.show();
  });

  $('#confirmarEliminar').on('click', function () {
    const $boton = $(this).prop('disabled', true);

    $.ajax({
      url: urlBorrar,
      method: 'POST',
      data: { id: idPorEliminar },
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
          || 'No se pudo eliminar la venta.';
        mostrarAviso('danger', mensaje);
      })
      .always(function () {
        $boton.prop('disabled', false);
      });
  });

  cargarLista();
});
