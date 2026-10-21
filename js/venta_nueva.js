$(function () {
  const urlListas = '/paginas/controlador/ctr_listas_venta.php';
  const urlGrabar = '/paginas/controlador/ctr_grabar_venta.php';

  const modalNueva = new bootstrap.Modal(document.getElementById('modalNueva'));

  let productos    = {};  // codigo -> { producto, precio (en céntimos), stock }
  let lineas       = [];  // [{ codigo, cantidad }, ...]
  let pedidoListas = 0;

  // Los importes se manejan en CÉNTIMOS (enteros) para evitar errores de decimales
  // 3780 -> "S/ 37.80"
  function dinero(centimos) {
    return 'S/ ' + (centimos / 100).toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function mostrarAviso(tipo, texto) {
    $('#aviso').empty().append(
      $('<div class="alert alert-dismissible fade show" role="alert">')
        .addClass('alert-' + tipo)
        .append($('<span>').text(texto))
        .append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>')
    );
  }

  // Dibuja la tabla de líneas y recalcula el total
  function pintarLineas() {
    const $tbody = $('#lineasNueva').empty();
    let total = 0;

    if (lineas.length === 0) {
      $tbody.append(
        $('<tr>').append(
          $('<td colspan="5" class="text-center text-body-secondary">')
            .text('Aún no agregaste productos.')
        )
      );
    }

    lineas.forEach(function (l, i) {
      const p        = productos[l.codigo];
      const subtotal = p.precio * l.cantidad;
      total += subtotal;

      $tbody.append(
        $('<tr>').append(
          $('<td>').text(p.producto),
          $('<td class="text-end">').text(l.cantidad),
          $('<td class="text-end">').text(dinero(p.precio)),
          $('<td class="text-end">').text(dinero(subtotal)),
          $('<td class="text-end">').append(
            $('<button type="button" class="btn btn-sm btn-outline-danger btn-quitar" title="Quitar">')
              .attr('data-indice', i)
              .html('<i class="fa-solid fa-xmark"></i>')
          )
        )
      );
    });

    $('#totalNueva').text(dinero(total));
    $('#btnGuardarVenta').prop('disabled', lineas.length === 0);
  }

  // ---------- Abrir la ventana: pide clientes y productos actuales ----------
  $('#btnNueva').on('click', function () {
    lineas = [];
    $('#errorCarga, #errorNueva').text('');
    $('#cantidad').val(1);
    pintarLineas();
    $('#cargandoNueva').removeClass('d-none');
    $('#contenidoNueva').addClass('d-none');
    modalNueva.show();

    const miPedido = ++pedidoListas;

    $.getJSON(urlListas)
      .done(function (resp) {
        if (miPedido !== pedidoListas) { return; }

        const $cli = $('#cliente').empty()
          .append($('<option value="">').text('Elige un cliente...'));
        resp.clientes.forEach(function (c) {
          $cli.append($('<option>').val(c.codigo_cliente).text(c.nombre));
        });

        productos = {};
        const $pro = $('#producto').empty()
          .append($('<option value="">').text('Elige un producto...'));
        resp.productos.forEach(function (p) {
          const precio = Math.round(parseFloat(p.precio) * 100);
          const stock  = parseInt(p.stock_disponible, 10);
          productos[p.codigo_producto] = { producto: p.producto, precio: precio, stock: stock };

          $pro.append(
            $('<option>')
              .val(p.codigo_producto)
              .prop('disabled', stock <= 0)
              .text(p.producto + ' — ' + dinero(precio) + (stock > 0 ? ' (stock ' + stock + ')' : ' (sin stock)'))
          );
        });

        $('#cargandoNueva').addClass('d-none');
        $('#contenidoNueva').removeClass('d-none');
      })
      .fail(function () {
        if (miPedido !== pedidoListas) { return; }
        $('#cargandoNueva').addClass('d-none');
        $('#errorCarga').text('No se pudieron cargar los clientes y productos.');
      });
  });

  // Al terminar de abrirse, el cursor va a la lista de clientes
  document.getElementById('modalNueva').addEventListener('shown.bs.modal', function () {
    $('#cliente').trigger('focus');
  });

  // ---------- Agregar un producto a la venta ----------
  function agregar() {
    $('#errorNueva').text('');

    const codigo   = $('#producto').val();
    const cantidad = Number($('#cantidad').val());

    if (codigo === '') {
      $('#errorNueva').text('Elige un producto.');
      return;
    }
    if (!Number.isInteger(cantidad) || cantidad < 1 || cantidad > 9999) {
      $('#errorNueva').text('La cantidad debe ser un número entero de 1 a 9999.');
      return;
    }

    const p          = productos[codigo];
    const existente  = lineas.find(function (l) { return l.codigo === codigo; });
    const yaAgregado = existente ? existente.cantidad : 0;

    if (yaAgregado + cantidad > p.stock) {
      $('#errorNueva').text(
        'No hay stock suficiente de ' + p.producto + ': hay ' + p.stock +
        ' y ya agregaste ' + yaAgregado + '.'
      );
      return;
    }

    if (existente) {
      existente.cantidad += cantidad;
    } else {
      lineas.push({ codigo: codigo, cantidad: cantidad });
    }

    pintarLineas();
    $('#producto').val('');
    $('#cantidad').val(1);
    $('#producto').trigger('focus');
  }

  $('#btnAgregar').on('click', agregar);

  // Enter en la cantidad también agrega (y no hace nada más)
  $('#cantidad').on('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      agregar();
    }
  });

  // ---------- Quitar una línea ----------
  $('#lineasNueva').on('click', '.btn-quitar', function () {
    lineas.splice(Number($(this).attr('data-indice')), 1);
    pintarLineas();
  });

  // ---------- Guardar la venta ----------
  $('#btnGuardarVenta').on('click', function () {
    const cliente = $('#cliente').val();

    if (cliente === '') {
      $('#errorNueva').text('Elige un cliente.');
      return;
    }
    if (lineas.length === 0) {
      $('#errorNueva').text('Agrega al menos un producto.');
      return;
    }
    $('#errorNueva').text('');

    const $boton = $(this).prop('disabled', true);

    $.ajax({
      url: urlGrabar,
      method: 'POST',
      data: { cliente: cliente, lineas: lineas },
      dataType: 'json'
    })
      .done(function (resp) {
        modalNueva.hide();
        mostrarAviso('success', resp.mensaje);
        // Limpia la búsqueda y recarga la lista (lo hace venta.js al oír este evento)
        $('#buscar').val('').trigger('input');
      })
      .fail(function (xhr) {
        const mensaje = (xhr.responseJSON && xhr.responseJSON.mensaje)
          || 'No se pudo registrar la venta.';
        $('#errorNueva').text(mensaje);
        $boton.prop('disabled', lineas.length === 0);
      });
  });
});
